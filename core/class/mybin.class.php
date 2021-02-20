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

    /*     * ***********************Methode static*************************** */

    public static function cron() {
        $eqLogics = self::byType(__CLASS__, true);

        foreach ($eqLogics as $eqLogic)
        {
            $eqLogic->checkBins();
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function notifYellowBin() {
        $this->notifBin('yellowbin');
    }
    
    public function notifGreenBin() {
        $this->notifBin('greenbin');
    }
    
    public function notifBin($mybin) {
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(1);
    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {

    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {

    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement

    public function postUpdate() {
        $cmd = $this->getCmd(null, 'ackgreen');
        if (!is_object($cmd))
        {
            $cmd = new linksysCmd();
            $cmd->setLogicalId('ackgreen');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Poubelle verte');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'ackyellow');
        if (!is_object($cmd))
        {
            $cmd = new linksysCmd();
            $cmd->setLogicalId('ackyellow');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Poubelle jaune');
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
        
        $this->configureCron('greenbin', 'notifGreenBin');
        $this->configureCron('yellowbin', 'notifYellowBin');

    }
    
    public function configureCron($bin, $method) {
        $days = '';
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration($bin.'_'.$i) == 1) {
                $myday = $i;
                if ($this->getConfiguration($bin.'_notif_veille') == 1) {
                    $myday = $myday - 1;
                    if ($myday == -1) {
                        $myday = 6;
                    }
                }
                $days = $days . $myday . ',';
            }
        }
        if ($days <> '') {
            $cron = cron::byClassAndFunction('mybin', $method);
            if ( ! is_object($cron)) {
                $cron = new cron();
                $cron->setClass('mybin');
                $cron->setFunction($method);
                $cron->setEnable(1);
                $cron->setDeamon(0);
            }
            $cronExpr = $this->getConfiguration($bin.'_notif_minute') . ' ' . $this->getConfiguration($bin.'_notif_hour') . ' * * '.substr($days, 0, -1);        
            $cron->setSchedule($cronExpr);
            $cron->save();
        }
    }
}

class mybinCmd extends cmd {

    public function execute( $_options = array() ) {

    }
}
