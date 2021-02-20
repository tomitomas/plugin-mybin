<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* ( at your option ) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY;
without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class mybin extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*
    * Permet de définir les possibilités de personnalisation du widget ( en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

    /*     * *********************Méthodes d'instance************************* */

    public function notifYellowBin() {
        $this->notifBin('yellowbin');
    }
    
    public function notifGreenBin() {
        $this->notifBin('greenbin');
    }
    
    public function ackYellowBin() {
        $this->ackBin('yellowbin');
    }
    
    public function ackGreenBin() {
        $this->ackBin('greenbin');
    }
    
    public function notifBin($mybin) {
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(1);
    }
    
    public function ackBin($mybin) {
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(0);
    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {
        $this->setDisplay('height','200px');
        $this->setDisplay('width', '200px');
        $this->setConfiguration('widgetTemplate', 1);
        $this->setConfiguration('greenbin_hour', 8);
        $this->setConfiguration('greenbin_minute', 0);
        $this->setConfiguration('greenbin_notif_veille', 1);
        $this->setConfiguration('greenbin_notif_hour', 20);
        $this->setConfiguration('greenbin_notif_minute', 0);
        $this->setConfiguration('yellowbin_hour', 8);
        $this->setConfiguration('yellowbin_minute', 0);
        $this->setConfiguration('yellowbin_notif_veille', 1);
        $this->setConfiguration('yellowbin_notif_hour', 20);
        $this->setConfiguration('yellowbin_notif_minute', 0);
    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {

    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement

    public function postUpdate() {
        $cmd = $this->getCmd(null, 'ack');
        if (!is_object($cmd))
        {
            $cmd = new linksysCmd();
            $cmd->setLogicalId('ack');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->save();
        }

        $cmd = $this->getCmd(null, 'greenbin');
        if (!is_object($cmd))
        {
            $cmd = new linksysCmd();
            $cmd->setLogicalId('greenbin');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Poubelle verte');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'yellowbin');
        if (!is_object($cmd))
        {
            $cmd = new linksysCmd();
            $cmd->setLogicalId('yellowbin');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Poubelle jaune');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        
        if ($this->getIsEnable() == 1) {
            $this->configureCrons('greenbin', 'notifGreenBin', 'ackGreenBin');
            $this->configureCrons('yellowbin', 'notifYellowBin', 'ackYellowBin');
        }

    }
    
    public function configureCrons($bin, $notif, $ack) {
        $daysack = '';
        $daysnotif = '';
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration($bin.'_'.$i) == 1) {
                $daysack = $daysack . $i . ',';
                $myday = $i;
                if ($this->getConfiguration($bin.'_notif_veille') == 1) {
                    $myday = $myday - 1;
                    if ($myday == -1) {
                        $myday = 6;
                    }
                }
                $daysnotif = $daysnotif . $myday . ',';
            }
        }
        $daysack = substr($daysack, 0, -1);
        $daysnotif = substr($daysnotif, 0, -1);
        
        if ($daysnotif <> '') {
            $cron = cron::byClassAndFunction('mybin', $notif);
            if ( ! is_object($cron)) {
                $cron = new cron();
                $cron->setClass('mybin');
                $cron->setFunction($notif);
                $cron->setEnable(1);
                $cron->setDeamon(0);
            }
            $cronExpr = $this->getConfiguration($bin.'_notif_minute') . ' ' . $this->getConfiguration($bin.'_notif_hour') . ' * * '.$daysnotif;        
            $cron->setSchedule($cronExpr);
            $cron->save();
        }
        
        if ($daysack <> '') {
            $cron = cron::byClassAndFunction('mybin', $ack);
            if ( ! is_object($cron)) {
                $cron = new cron();
                $cron->setClass('mybin');
                $cron->setFunction($ack);
                $cron->setEnable(1);
                $cron->setDeamon(0);
            }
            $cronExpr = $this->getConfiguration($bin.'_minute') . ' ' . $this->getConfiguration($bin.'_hour') . ' * * '.$daysack;        
            $cron->setSchedule($cronExpr);
            $cron->save();
        }
    }
    
    public function toHtml($_version = 'dashboard') {
        if ($this->getConfiguration('widgetTemplate') != 1) {
    		return parent::toHtml($_version);
    	}
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        
        $binimg = "none";
        if ($greenbin == 1) {
            $binimg = "green";
        }
        if ($yellowbin == 1) {
            $binimg = "yellow";
        }
        if ($greenbin == 1 && $yellowbin == 1) {
            $binimg = "both";
        }
        $replace['#binimg#'] = $binimg;
        
        $ackCmd = $this->getCmd(null, 'ack');
        $replace['#ack_id#'] = $ackCmd->getId();

        $html = template_replace($replace, getTemplate('core', $version, 'mybin.template', __CLASS__));
        cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        return $html;
    }
}

class mybinCmd extends cmd {

    public function dontRemoveCmd() {
		return true;
	}
    
	public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
        log::add('mybin', 'debug', 'Execution de la commande ' . $this->getLogicalId());
        switch ($this->getLogicalId()) {
            case "ack":
                $eqLogic->ackGreenBin();
                $eqLogic->ackYellowBin();
                break;
        }
    }
}
