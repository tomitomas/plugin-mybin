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
            $eqLogic->checkBin();
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function checkBin() {
        if ($this->getConfiguration('type', '') == 'whole') {
            return;
        }

        /************************************ Hack degueu ***************************************************************/
        // Devrait etre fait dans le postUpdate mais, pour une raison que j'ignore, la DB ne se met pas à jour
        $threshold = $this->getConfiguration('seuil','');
        $cmd = $this->getCmd(null, 'counter');
        $cmd->setConfiguration('minValue', 0); 
        $cmd->setConfiguration('maxValue', $threshold); 
        $cmd->save();
        /****************************************************************************************************************/

        $week = 1 * date('W');
        $day = 1 * date('w');
        $hour = 1 * date('G');
        $minute = 1 * date('i');
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkbin: day ' . $day . ', hour ' . $hour . ', minute ' . $minute);
        $change = 0;
        $change = $change + $this->checkNotifBin($week, $day, $hour, $minute);
        $change = $change + $this->checkAckBin($week, $day, $hour, $minute);
        if ($change > 0 || ($hour == 0 && $minute == 5)) {
            $this->refreshWhole();
        }
    }
    
    public function refreshWhole() {
        $this->refreshWidget();
        $eqLogics = self::byType(__CLASS__, true);
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'whole') {
                $eqLogic->refreshWidget();
                break;
            }
        }
    }
    
    public function checkNotifBin($week, $day, $hour, $minute) {
        if (!$this->getIsEnable()) {
            return 0;
        }
        
        $dt = new DateTime("now");
        
        $isSpecificDay = false;
        $isweek = false;
        $isday = false;
        $ishour = false;
        $isminute = false;
        $myday = $day;
        $myweek = $week;
        if ($this->getConfiguration('notif_veille') == 1) {
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
            $dt->modify('+1 day');
        }
        $specificDays = $this->getConfiguration('specific_day');
        if (is_array($specificDays)) {
            foreach ($specificDays as $specificDay) {
                $todayStr = $dt->format("Y-m-d");
                if (isset($specificDay['myday'])) {
                    if ($todayStr == $specificDay['myday']) {
                        $isSpecificDay = true;
                        break;
                    }
                }
            }
        }
        if (($myweek%2 == 0 && $this->getConfiguration('paire') == 1) || ($myweek%2 != 0 && $this->getConfiguration('impaire') == 1)) {
            $isweek = true;
        }
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration('day_'.$i) == 1 && $i == $myday) {
                $isday = true;
                break;
            }
        }
        if ($this->getConfiguration('notif_minute') == $minute) {
            $isminute = true;
        }
        if ($this->getConfiguration('notif_hour') == $hour) {
            $ishour = true;
        }
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkNotifBin: week '. $isweek . ', day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ((($isweek && $isday) || $isSpecificDay) && $ishour && $isminute) {
            $this->notifBin();
            return 1;
        } else {
            return 0;
        }
    }
    
    public function checkAckBin($week, $day, $hour, $minute) {
        if (!$this->getIsEnable()) {
            return 0;
        }
        
        $dt = new DateTime("now");
        
        $isSpecificDay = false;
        $isweek = false;
        $isday = false;
        $ishour = false;
        $isminute = false;
        
        $specificDays = $this->getConfiguration('specific_day');
        if (is_array($specificDays)) {
            foreach ($specificDays as $specificDay) {
                $todayStr = $dt->format("Y-m-d");
                if (isset($specificDay['myday'])) {
                    if ($todayStr == $specificDay['myday']) {
                        $isSpecificDay = true;
                        break;
                    }
                }
            }
        }
        
        if (($week%2 == 0 && $this->getConfiguration('paire') == 1) || ($week%2 != 0 && $this->getConfiguration('impaire') == 1)) {
            $isweek = true;
        }
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration('day_'.$i) == 1 && $i == $day) {
                $isday = true;
                break;
            }
        }
        if ($this->getConfiguration('minute') == $minute) {
            $isminute = true;
        }
        if ($this->getConfiguration('hour') == $hour) {
            $ishour = true;
        }
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' checkAckBin: week '. $isweek . ', day ' . $isday . ', hour ' . $ishour . ', minute ' . $isminute);
        if ((($isweek && $isday) || $isSpecificDay) && $ishour && $isminute) {
            $this->ackBin(true);
            return 1;
        } else {
            return 0;
        }
    }
    
    public function notifBin() {
        $seuil = $this->getConfiguration('seuil', '');
        if ($seuil <> '') {
            $cmd = $this->getCmd(null, 'counter');
            $counter = $cmd->execCmd();
            if ($counter >= $seuil) {
                log::add(__CLASS__, 'info', $this->getHumanName() . ' notification skipped because threshold reached');
                return;
            }
        }
        $cmd = $this->getCmd(null, 'bin');
        $cmd->event(1);
        log::add(__CLASS__, 'info', $this->getHumanName() . ' notification on');
        $action_notif = $this->getConfiguration('action_notif');
        if (is_array($action_notif)) {
            foreach ($action_notif as $action) {
                $this->execAction($action);
            }
        }
    }
    
    public function ackBin($auto) {
        $cmd = $this->getCmd(null, 'bin');
        $value = $cmd->execCmd();
        if ($value == 1) {
            $cmd->event(0);
            log::add(__CLASS__, 'info', $this->getHumanName() . ' acknowledged');
            $counterType = $this->getConfiguration('counter', 'auto');
            if (($counterType == 'auto') || ($counterType == 'manu' && !$auto)) {
                $cmd = $this->getCmd(null, 'counter');
                $value = $cmd->execCmd();
                $cmd->event($value + 1);
                log::add(__CLASS__, 'info', $this->getHumanName() . ' counter incremented to ' . $value + 1);
            }
            $action_collect = $this->getConfiguration('action_collect');
            if (is_array($action_collect)) {
                foreach ($action_collect as $action) {
                    $this->execAction($action);
                }
            }
        }
    }
    
    public function resetCounter() {
        log::add(__CLASS__, 'info', $this->getHumanName() . ' counter reset');
        $cmdCounter = $this->getCmd(null, 'counter');
        $cmdCounter->event(0);
        $this->refreshWidget();
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
        if ($this->getConfiguration('type','') == 'whole') {
            $this->setDisplay('height','200px');
            $this->setDisplay('width', '372px');
            $this->setIsEnable(1);
            $this->setIsVisible(1);
        } else {
            $this->setDisplay('height','140px');
            $this->setDisplay('width', '260px');
            $this->setConfiguration('hour', 8);
            $this->setConfiguration('minute', 0);
            $this->setConfiguration('notif_veille', 1);
            $this->setConfiguration('notif_hour', 20);
            $this->setConfiguration('notif_minute', 0);
            $this->setConfiguration('paire', 1);
            $this->setConfiguration('impaire', 1);
            $this->setConfiguration('color', 'green');   
            $this->setConfiguration('counter', 'auto');  
            $this->setIsEnable(1);
            $this->setIsVisible(0);
        }
    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
        if ($this->getConfiguration('type','') <> 'whole') {
            if ($this->getConfiguration('notif_veille') == 0) {
                if ($this->getConfiguration('notif_hour') > $this->getConfiguration('hour')) {
                    throw new Exception(__('L\'heure de notification est après l\'heure de collecte',__FILE__));
                }
                if ($this->getConfiguration('notif_hour') == $this->getConfiguration('hour')) {
                    if ($this->getConfiguration('notif_minute') > $this->getConfiguration('minute')) {
                        throw new Exception(__('L\'heure de notification est après l\'heure de collecte',__FILE__));
                    }
                }
            }
            if ($this->getConfiguration('seuil', '') <> '') {
                $options = array('options' => array('min_range' => 0));
                if (!filter_var($this->getConfiguration('seuil'), FILTER_VALIDATE_INT, $options)) {
                    throw new Exception(__('Le seuil doit être un entier positif ou être laissé vide',__FILE__));
                }
            }
        }
        $this->setConfiguration('image',$this->getImage());
    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement

    public function postUpdate() {
        if ($this->getConfiguration('type','') <> 'whole') {
            $cmd = $this->getCmd(null, 'bin');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('bin');
                $cmd->setEqLogic_id($this->getId());
                $cmd->setName('Poubelle à sortir');
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setIsHistorized(0);
                $cmd->setTemplate('mobile', 'line');
                $cmd->setTemplate('dashboard', 'line');
                $cmd->save();
            }
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
            $cmd = $this->getCmd(null, 'counter');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('counter');
                $cmd->setEqLogic_id($this->getId());
                $cmd->setName('Compteur');
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setEventOnly(1);
                $cmd->setIsHistorized(1);
                $cmd->setTemplate('mobile', 'line');
                $cmd->setTemplate('dashboard', 'line');       
                $cmd->save();
                $cmd->event(0);
            }
            $cmd = $this->getCmd(null, 'resetcounter');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('resetcounter');
                $cmd->setEqLogic_id($this->getId());
                $cmd->setName('Reset Compteur');
                $cmd->setType('action');
                $cmd->setSubType('other');
                $cmd->setEventOnly(1);
                $cmd->save();
            }
        }
        $this->emptyCacheWidget();

    }
    
    public function toHtml($_version = 'dashboard') {
        if (($this->getConfiguration('type') <> 'whole' && $this->getConfiguration('widgetTemplate') == 0) || $this->getIsEnable() == 0) {
    		return parent::toHtml($_version);
    	}
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        
        // global widget
        if ($this->getConfiguration('type') == 'whole') {
            $eqLogics = self::byType(__CLASS__, true);

            //Status
            $binnotifs = "";
            $binscript = "";
            foreach ($eqLogics as $eqLogic) {
                if ($eqLogic->getConfiguration('type') == 'whole') {
                    continue;
                }
                $binCmd = $eqLogic->getCmd(null, 'bin');
                $binStatus = $binCmd->execCmd();
                if ($eqLogic->getIsEnable() == 1 && $binStatus == 1) {
                    $binimg = $eqLogic->getConfiguration('color');
                    $ackCmd = $eqLogic->getCmd(null, 'ack');
                    $counterCmd = $eqLogic->getCmd(null, 'counter');
                    $binnotifs = $binnotifs . '<div style="display: inline-block;" class="cmd ack'.$ackCmd->getId().' cursor" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/'.$binimg.'.png" width="80px"/><br/>'.__('Compteur :',__FILE__).' '. $counterCmd->execCmd() . '</div>';
                    $binscript = $binscript . "$('.eqLogic[data-eqLogic_uid=".$replace['#uid#']."] .ack".$ackCmd->getId()."').on('click', function () {jeedom.cmd.execute({id: '".$ackCmd->getId()."'});});";
                }
            }
            $replace['#binscript#'] = $binscript;
            if ($binnotifs == "") {
                $binnotifs = '<span class="nobin"><br/><i>'.__('Il n\'y a (plus) aucune poubelle à sortir',__FILE__).'</i></span>';
            }
            $replace['#binnotifs#'] = $binnotifs;

            // calendar
            $dtDisplay = new DateTime("now");
            $calendarType = config::byKey('calendarType','mybin','',true);
            for ($i = 1; $i <= 7; $i++) {                         
                $day = 1 * $dtDisplay->format('w');
                $dateD = $dtDisplay->format('d');
                $dateM = $dtDisplay->format('m');
                $replace['#day'.$i.'#'] = $this->getDayLetter($day);
                $replace['#date'.$i.'#'] = $dateD . '/' . $dateM;
                $display = "";
                foreach ($eqLogics as $eqLogic) {
                    if ($eqLogic->getConfiguration('type') == 'whole') {
                        continue;
                    }
                    $dtCheck = DateTime::createFromFormat("Y-m-d", $dtDisplay->format("Y-m-d"));
                    if ($eqLogic->getConfiguration('notif_veille') == 1 && $calendarType == 'notif') {
                        $dtCheck->modify('+1 day');
                    }
                    if ($eqLogic->checkIfBin($dtCheck)) {
                        $color = $eqLogic->getConfiguration('color');
                        $display = $display . '<img src="plugins/mybin/data/images/'.$color.'.png" width="20px">';
                    }
                }
                $replace['#binimg_day'.$i.'#'] = $display;
                $dtDisplay->modify('+1 day');
            }

            $html = template_replace($replace, getTemplate('core', $version, 'mybin.template', __CLASS__));
            cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        }
        
        // single bin widget 
        else {
            $binnotifs = '<span class="cmd" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/none2.png" width="70px"></span>';
            $binscript = "";
            $binCmd = $this->getCmd(null, 'bin');
            $binStatus = $binCmd->execCmd();
            if ($binStatus == 1) {
                $binimg = $this->getConfiguration('color');
                $ackCmd = $this->getCmd(null, 'ack');
                $binnotifs = '<span class="cmd ack'.$ackCmd->getId().' cursor" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/'.$binimg.'.png" width="70px"></span>';
                $binscript = "$('.eqLogic[data-eqLogic_uid=".$replace['#uid#']."] .ack".$ackCmd->getId()."').on('click', function () {jeedom.cmd.execute({id: '".$ackCmd->getId()."'});});";
            }
            $replace['#binscript#'] = $binscript;
            $replace['#binnotifs#'] = $binnotifs;
            
            $counterCmd = $this->getCmd(null, 'counter');
            $replace['#counter_id#'] = $counterCmd->getId();
            $replace['#counter_uid#'] = $counterCmd->getId();
            $replace['#counter_eqLogic_id#'] = $replace['#uid#'];
            $replace['#counter_collectDate#'] = $counterCmd->getCollectDate();
            $replace['#counter_valueDate#'] = $counterCmd->getValueDate();
            $replace['#counter_minValue#'] = $counterCmd->getConfiguration('minValue', 0);
            $replace['#counter_maxValue#'] = $counterCmd->getConfiguration('maxValue');
            $replace['#counter_state#'] = $counterCmd->execCmd();
            $replace['#counter_unite#'] = $counterCmd->getUnite();
            
            $resetCmd = $this->getCmd(null, 'resetcounter');
            $replace['#reset_id#'] = $resetCmd->getId();
            $replace['#reset_uid#'] = $resetCmd->getId();
            
            $html = template_replace($replace, getTemplate('core', $version, 'singlebin.template', __CLASS__));
            cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        }
        return $html;
        
    }
    
    public function checkIfBin($dt) {
        if ($this->getIsEnable() != 1) {
            return false;
        }
        
        $isSpecificDay = false;
        $isweek = false;
        $isday = false;

        $specificDays = $this->getConfiguration('specific_day');
        if (is_array($specificDays)) {
            foreach ($specificDays as $specificDay) {
                $todayStr = $dt->format("Y-m-d");
                if (isset($specificDay['myday'])) {
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' $todayStr: ' . $todayStr . ', $specificDay: ' . $specificDay['myday']);
                    if ($todayStr == $specificDay['myday']) {
                        $isSpecificDay = true;
                        break;
                    }
                }
            }
        }
        
        $day = 1 * $dt->format('w');
        $week = 1 * $dt->format('W');
        
        if (($week%2 == 0 && $this->getConfiguration('paire') == 1) || ($week%2 != 0 && $this->getConfiguration('impaire') == 1)) {
            $isweek = true;
        }
        for ($i = 0; $i <= 6; $i++) {
            if ($this->getConfiguration('day_'.$i) == 1 && $i == $day) {
                $isday = true;
                break;
            }
        }
        if ($isSpecificDay || ($isweek && $isday)) {
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
    
    public static function createWhole() {
		$eqLogicClient = new mybin();
        $defaultRoom = intval(config::byKey('parentObject','mybin','',true));
		$eqLogicClient->setName(__('Mes poubelles', __FILE__));
		$eqLogicClient->setIsEnable(1);
		$eqLogicClient->setIsVisible(1);
		$eqLogicClient->setLogicalId(__('Mes poubelles', __FILE__));
		$eqLogicClient->setEqType_name('mybin');
		if($defaultRoom) $eqLogicClient->setObject_id($defaultRoom);
		$eqLogicClient->setConfiguration('type', 'whole');
		$eqLogicClient->save();
        log::add('mybin', 'info', "Ensemble créé");
	}
    
    public static function postConfig_globalWidget($value) {
        $eqLogics = self::byType(__CLASS__, true);
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'whole') {
                if ($value == 1) {
                    $eqLogic->setIsVisible(1);
                } else {
                    $eqLogic->setIsVisible(0);
                } 
                $eqLogic->save();
                break;
            }
        }
    }
    
    public static function postConfig_parentObject($value) {
        $eqLogics = self::byType(__CLASS__, true);
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'whole') {
                $defaultRoom = intval($value);
                if($defaultRoom) $eqLogic->setObject_id($defaultRoom);
                $eqLogic->save();
                break;
            }
        }
    }
    
    public function getImage() {
        $color = $this->getConfiguration('color','');
        if ($color == '') {
            return 'plugins/mybin/plugin_info/mybin_icon.png';
        } else {
            return 'plugins/mybin/core/assets/'.$color.'_icon.png';
        }
	}
    
    public function execAction($action) {
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Execution de l\'action ' . $action['cmd']);
        try {
            $options = array();
            if (isset($action['options'])) {
                $options = $action['options'];
                foreach ($options as $key => $value) {
                    $value = str_replace('#bin_color#', $this->getConfiguration('color'), $value);
                    $value = str_replace('#bin_name#', $this->getName(), $value);
                    $value = str_replace('#bin_threshold#', $this->getConfiguration('seuil'), $value);
                    $options[$key] = $value;
                }
            }
            scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
            log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur lors de l\'execution de l\'action ' . $action['cmd'] . ': ' . $e->getMessage());
        }
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
                $eqLogic->ackBin(false);
                $eqLogic->refreshWhole();
                break;
            case "resetcounter":
                $eqLogic->resetCounter();
                break;
        }
    }
}
