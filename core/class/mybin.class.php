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
        $week = 1 * date('W');
        $day = 1 * date('w');
        $hour = 1 * date('G');
        $minute = 1 * date('i');
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkbins: day ' . $day . ', hour ' . $hour . ', minute ' . $minute);
        $this->checkNotifBin('yellowbin', $week, $day, $hour, $minute);
        $this->checkNotifBin('greenbin', $week, $day, $hour, $minute);
        $this->checkAckBin('yellowbin', $week, $day, $hour, $minute);
        $this->checkAckBin('greenbin', $week, $day, $hour, $minute);
    }
    
    public function checkNotifBin($bin, $week, $day, $hour, $minute) {
        $isday = false;
        $ishour = false;
        $isminute = false;
        $myday = $day;
        $myweek = $week;
        if ($this->getConfiguration($bin.'_notif_veille') == 1) {
            $myday = $myday + 1;
            if ($myday == 7) {
                $myday = 0;
            }
            // attention aux semaines paires/impaires
            // si myday == 1, cad Lundi, ca veut dire qu'on est aujourd'hui dimanche, dernier jour de la semaine ==> week +1
            if ($myday == 1) {
                $myweek = $myweek + 1;
                if ($myweek == 53) {
                    $myweek = 1;
                }
            }
        }
        if (($myweek%2 == 1 && $this->getConfiguration($bin.'_paire') == 1) || ($myweek%2 == 0 && $this->getConfiguration($bin.'_impaire') == 1)) {
            $isweek = true;
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
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkNotifBin ' . $bin . ': week '. $isweek . ', day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ($isweek && $isday && $ishour && $isminute) {
            $this->notifBin($bin);
        }
    }
    
    public function checkAckBin($bin, $week, $day, $hour, $minute) {
        $isweek = false;
        $isday = false;
        $ishour = false;
        $isminute = false;
        if (($week%2 == 1 && $this->getConfiguration($bin.'_paire') == 1) || ($week%2 == 0 && $this->getConfiguration($bin.'_impaire') == 1)) {
            $isweek = true;
        }
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
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkAckBin ' . $bin . ': week '. $isweek . ', day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ($isweek && $isday && $ishour && $isminute) {
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
        $this->setGlobalStatus();
    }
    
    public function ackBin($mybin) {
        log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . $mybin . ' acknowledged');
        $cmd = $this->getCmd(null, $mybin);
        $cmd->event(0);
        $this->setGlobalStatus();
    }
    
    public function setGlobalStatus() {
        $cmd = $this->getCmd(null, 'greenbin');
        $greenbin = $cmd->execCmd();
        $cmd = $this->getCmd(null, 'yellowbin');
        $yellowbin = $cmd->execCmd();
        $globalstatus = 'N';
        $message = '';
        if ($greenbin && $yellowbin) {
            $globalstatus = 'B';
            $message = __('Il faut sortir les deux poubelles', __FILE__);
        } elseif ($greenbin) {
            $globalstatus = 'G';
            $message = __('Il faut sortir la poubelle verte', __FILE__);
        } elseif ($yellowbin) {
            $globalstatus = 'Y';
            $message = __('Il faut sortir la poubelle jaune', __FILE__);
        }
        log::add(__CLASS__, 'info', $this->getHumanName() . ' Set global status: ' . $globalstatus);
        $cmd = $this->getCmd(null, 'globalstatus');
        $currentStatus = $cmd->execCmd();
        if ($currentStatus <> $globalstatus) {
            $cmd->event($globalstatus);
            $this->refreshWidget();
            /*
            $ttsid = str_replace("#", "", $this->getConfiguration('ttscmd'));
            if ($ttsid <> '' && $globalstatus <> 'N') {
                $ttscmd = cmd::byId($ttsid);
                if (!is_object($ttscmd)) {
                    log::add(__CLASS__, 'error', $this->getHumanName() . ' TTS Command '.$ttsid.' does not exist');
                } else {
                    $options = array('message'=> $message);
                    $ttscmd->execCmd($options);
                }
            }
            */
        }
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
        $this->setConfiguration('greenbin_paire', 1);
        $this->setConfiguration('greenbin_impaire', 1);
        $this->setConfiguration('yellowbin_paire', 1);
        $this->setConfiguration('yellowbin_impaire', 1);
    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
        if ($this->getConfiguration('greenbin_notif_veille') == 0) {
            if ($this->getConfiguration('greenbin_notif_hour') > $this->getConfiguration('greenbin_hour')) {
                throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle verte',__FILE__));
            }
            if ($this->getConfiguration('greenbin_notif_hour') == $this->getConfiguration('greenbin_hour')) {
                if ($this->getConfiguration('greenbin_notif_minute') > $this->getConfiguration('greenbin_minute')) {
                    throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle verte',__FILE__));
                }
            }
        }
        if ($this->getConfiguration('yellowbin_notif_veille') == 0) {
            if ($this->getConfiguration('yellowbin_notif_hour') > $this->getConfiguration('yellowbin_hour')) {
                throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle jaune',__FILE__));
            }
            if ($this->getConfiguration('yellowbin_notif_hour') == $this->getConfiguration('yellowbin_hour')) {
                if ($this->getConfiguration('greenbin_notif_minute') > $this->getConfiguration('greenbin_minute')) {
                    throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle jaune',__FILE__));
                }
            }
        }
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
        $cmd = $this->getCmd(null, 'refresh');
        if (!is_object($cmd)) {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Rafraichir');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
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

    /*
    public function dontRemoveCmd() {
		return true;
	}
    */
    
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
            case "refresh":
                $eqLogic->checkBins();
                break;
        }
    }
}
