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
        $change = 0;
        for ($i = 1; $i <= 4; $i++) {
            $change = $change + $this->checkNotifBin('bin'.$i, $week, $day, $hour, $minute);
            $change = $change + $this->checkAckBin('bin'.$i, $week, $day, $hour, $minute);
        }
        if ($change > 0 || ($hour == 0 && $minute == 5)) {
            $this->refreshWidget();
        }
    }
    
    public function checkNotifBin($bin, $week, $day, $hour, $minute) {
        if ($this->getConfiguration($bin.'_active') != 1) {
            return 0;
        }
        $isweek = false;
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
                if ($myweek > $this->lastWeekNumberOfYear()) {
                    $myweek = 1;
                }
            }
        }
        if (($myweek%2 == 0 && $this->getConfiguration($bin.'_paire') == 1) || ($myweek%2 != 0 && $this->getConfiguration($bin.'_impaire') == 1)) {
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
            return 1;
        } else {
            return 0;
        }
    }
    
    public function checkAckBin($bin, $week, $day, $hour, $minute) {
        if ($this->getConfiguration($bin.'_active') != 1) {
            return 0;
        }
        $isweek = false;
        $isday = false;
        $ishour = false;
        $isminute = false;
        if (($week%2 == 0 && $this->getConfiguration($bin.'_paire') == 1) || ($week%2 != 0 && $this->getConfiguration($bin.'_impaire') == 1)) {
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
            return 1;
        } else {
            return 0;
        }
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

    public function lastWeekNumberOfYear() {
        $year = date('Y');
        $week_count = date('W', strtotime($year . '-12-31'));
        if ($week_count == '01'){
            $week_count = date('W', strtotime($year . '-12-24'));
        }
        return intval($week_count);
    }

    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {
        $this->setDisplay('height','200px');
        $this->setDisplay('width', '372px');
        $this->setConfiguration('widgetTemplate', 1);
        for ($i = 1; $i <= 4; $i++) {
            $this->setConfiguration('bin'.$i.'_hour', 8);
            $this->setConfiguration('bin'.$i.'_minute', 0);
            $this->setConfiguration('bin'.$i.'_notif_veille', 1);
            $this->setConfiguration('bin'.$i.'_notif_hour', 20);
            $this->setConfiguration('bin'.$i.'_notif_minute', 0);
            $this->setConfiguration('bin'.$i.'_paire', 1);
            $this->setConfiguration('bin'.$i.'_impaire', 1);
        }
        $this->setConfiguration('bin1_color', 'braun');
        $this->setConfiguration('bin2_color', 'yellow');
        $this->setConfiguration('bin3_color', 'green');
        $this->setConfiguration('bin4_color', 'blue');
        
        $this->setConfiguration('bin1_active', 1);
        
        $this->setIsEnable(1);
        $this->setIsVisible(1);
    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
        for ($i = 1; $i <= 4; $i++) {
            if ($this->getConfiguration('bin'.$i.'_notif_veille') == 0) {
                if ($this->getConfiguration('bin'.$i.'_notif_hour') > $this->getConfiguration('bin'.$i.'_hour')) {
                    throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle ',__FILE__) . $i);
                }
                if ($this->getConfiguration('bin'.$i.'_notif_hour') == $this->getConfiguration('bin'.$i.'_hour')) {
                    if ($this->getConfiguration('bin'.$i.'_notif_minute') > $this->getConfiguration('bin'.$i.'_minute')) {
                        throw new Exception(__('L\'heure de notification est après l\'heure de collecte pour la poublle verte ',__FILE__) . $i);
                    }
                }
            }
        }
    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement

    public function postUpdate() {
        $cmd = $this->getCmd(null, 'bin1');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('bin1');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Déchêts ménagers');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'bin2');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('bin2');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Déchêts recyclables');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'bin3');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('bin3');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Déchêts végétaux');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'bin4');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('bin4');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Déchêts en verre');
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setEventOnly(1);
            $cmd->setIsHistorized(0);
            $cmd->setTemplate('mobile', 'line');
            $cmd->setTemplate('dashboard', 'line');
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'ack1');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('ack1');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Déchêts ménagers');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'ack2');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('ack2');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Déchêts recyclables');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'ack3');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('ack3');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Déchêts végétaux');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->save();
        }
        $cmd = $this->getCmd(null, 'ack4');
        if (!is_object($cmd))
        {
            $cmd = new mybinCmd();
            $cmd->setLogicalId('ack4');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName('Ack Déchêts en verre');
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
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
        
        //Status
        for ($i = 1; $i <= 4; $i++) {
            $binCmd = $this->getCmd(null, 'bin'.$i);
            $binStatus = $binCmd->execCmd();
            if ($this->getConfiguration('bin'.$i.'_active') == 1 && $binStatus == 1) {
                $binimg = $this->getConfiguration('bin'.$i.'_color');
                $replace['#bin'.$i.'img#'] = $binimg;
                $ackCmd = $this->getCmd(null, 'ack'.$i);
                $replace['#ack'.$i.'_id#'] = $ackCmd->getId();
            } else {
                $replace['#ack'.$i.'_id#'] = '';
            }
        }
        
        // calendar
        $dt = new DateTime("now");
        for ($i = 1; $i <= 7; $i++) {
            $day = 1 * $dt->format('w');
            $week = 1 * $dt->format('W');
            $dateD = $dt->format('d');
            $dateM = $dt->format('m');
            $replace['#day'.$i.'#'] = $this->getDayLetter($day);
            $replace['#date'.$i.'#'] = $dateD . '/' . $dateM;
            $display = "";
            for ($j = 1; $j <= 4; $j++) {
                if ($this->checkIfBin('bin'.$j, $week, $day)) {
                    $color = $this->getConfiguration('bin'.$j.'_color');
                    $display = $display . '<img src="plugins/mybin/data/images/'.$color.'.png" width="20px">';
                }
            }
            $replace['#binimg_day'.$i.'#'] = $display;
            $dt->modify('+1 day');
        }

        $html = template_replace($replace, getTemplate('core', $version, 'mybin.template', __CLASS__));
        cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        return $html;
        
    }
    
    public function checkIfBin($bin, $week, $day) {
        if ($this->getConfiguration($bin.'_active') != 1) {
            return false;
        }
        $isweek = false;
        $isday = false;
        if (($week%2 == 0 && $this->getConfiguration($bin.'_paire') == 1) || ($week%2 != 0 && $this->getConfiguration($bin.'_impaire') == 1)) {
            $isweek = true;
        }
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration($bin.'_'.$i) == 1 && $i == $day) {
                $isday = true;
                break;
            }
        }
        if ($isweek && $isday) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getDayLetter($dayNb) {
        $day = '';
        switch ($dayNb) {
            case 0:
                $day = __('D',__FILE__);
                break;
            case 1:
                $day = __('L',__FILE__);
                break;
            case 2:
                $day = __('Ma',__FILE__);
                break;
            case 3:
                $day = __('Me',__FILE__);
                break;
            case 4:
                $day = __('J',__FILE__);
                break;
            case 5:
                $day = __('V',__FILE__);
                break;
            case 6:
                $day = __('S',__FILE__);
                break;
        }
        return $day;
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
            case "ack1":
                $eqLogic->ackBin('bin1');
                $eqLogic->refreshWidget();
                break;
            case "ack2":
                $eqLogic->ackBin('bin2');
                $eqLogic->refreshWidget();
                break;
            case "ack3":
                $eqLogic->ackBin('bin3');
                $eqLogic->refreshWidget();
                break;
            case "ack4":
                $eqLogic->ackBin('bin4');
                $eqLogic->refreshWidget();
                break;
            case "refresh":
                $eqLogic->refreshWidget();
                break;
        }
    }
}
