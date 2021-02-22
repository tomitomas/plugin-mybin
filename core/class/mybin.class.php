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
    
    public static function cron5() {
        $eqLogics = self::byType(__CLASS__, true);

        foreach ($eqLogics as $eqLogic)
        {
            $eqLogic->checkBins();
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function checkBins() {
        $day = 1 * date('w');
        $hour = 1 * date('G');
        $minute = 1 * date('i');
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkbins: day ' . $day . ', hour ' . $hour . ', minute ' . $minute);
        $yellowbin = $this->checkNotifBin('yellowbin', $day, $hour, $minute);
        $greenbin = $this->checkNotifBin('greenbin', $day, $hour, $minute);
        $this->setGlobalStatusAndTTS($greenbin, $yellowbin);
        $this->checkAckBin('yellowbin', $day, $hour, $minute);
        $this->checkAckBin('greenbin', $day, $hour, $minute);
    }
    
    public function checkNotifBin($bin, $day, $hour, $minute) {
        $isday = false;
        $ishour = false;
        $isminute = false;
        $myday = $day;
        if ($this->getConfiguration($bin.'_notif_veille') == 1) {
            $myday = $myday + 1;
            if ($myday == 7) {
                $myday = 0;
            }                
        }
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration($bin.'_'.$i) == 1 && $i == $myday) {
                $isday = true;
                break;
            }
        }
        if ($this->getConfiguration($bin.'_notif_minute') == $minute) {
            $isminute = true;
        }
        if ($this->getConfiguration($bin.'_notif_hour') == $hour) {
            $ishour = true;
        }
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkNotifBin ' . $bin . ': day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ($isday && $ishour && $isminute) {
            $this->notifBin($bin);
            return true;
        } else {
            return false;
        }
    }
    
    public function checkAckBin($bin, $day, $hour, $minute) {
        $isday = false;
        $ishour = false;
        $isminute = false;
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration($bin.'_'.$i) == 1 && $i == $day) {
                $isday = true;
                break;
            }
        }
        if ($this->getConfiguration($bin.'_minute') == $minute) {
            $isminute = true;
        }
        if ($this->getConfiguration($bin.'_hour') == $hour) {
            $ishour = true;
        }
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkAckBin ' . $bin . ': day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ($isday && $ishour && $isminute) {
            $this->ackBin($bin);
        }
    }
    
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
        log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . $mybin . ' notification on');
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(1);
    }
    
    public function ackBin($mybin) {
        log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . $mybin . ' acknowledged');
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(0);
    }
    
    public function setGlobalStatusAndTTS($greenbin, $yellowbin) {
        $globalstatus = 'N';
        if ($greenbin && $yellowbin) {
            $globalstatus = 'B';
        } elseif ($greenbin) {
            $globalstatus = 'G';
        } elseif ($yellowbin) {
           $globalstatus = 'Y'; 
        }
        log::add(__CLASS__, 'info', $this->getHumanName() . ' Set global status: ' . $globalstatus);
        $cmd = $this->getCmd(null, 'globalstatus');
        $cmd->event($globalstatus);
    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {
        $this->setDisplay('height','100px');
        $this->setDisplay('width', '180px');
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
            $cmd = new mybinCmd();
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
            $cmd = new mybinCmd();
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
            $cmd = new mybinCmd();
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
        $cmd = $this->getCmd(null, 'globalstatus');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('globalstatus');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Statut global');
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->setGeneric_type('GENERIC_INFO');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
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
        
        $greenBinCmd = $this->getCmd(null, 'greenbin');
        $yellowBinCmd = $this->getCmd(null, 'yellowbin');
        $greenbin = $greenBinCmd->execCmd();
        $yellowbin = $yellowBinCmd->execCmd();
        
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
        
        $globalstatusCmd = $this->getCmd(null, 'globalstatus');
        $replace['#globalstatus_id#'] = $globalstatusCmd->getId();

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
