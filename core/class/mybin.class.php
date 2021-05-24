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
        if (!$this->getIsEnable()) {
            return;
        }

        $change = false;
        $nextRunRecorded = false;

        $dtNow = new DateTime("now");
        $todayStr = $dtNow->format("Y-m-d H:i");
        $hour = 1 * date('G');
        $minute = 1 * date('i');

        $nextOne = $this->getNextCollectsAndNotifs(2);
        if (is_array($nextOne)) {
            foreach ($nextOne as $collect => $notif) {
                if ($notif == $todayStr) {
                    $notifCondition = $this->getConfiguration('notifCondition');
                    $condition = true; 
                    if ($notifCondition <> '') {
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' condition raw: ' . $notifCondition);
                        $scenario = null;
                        $notifCondition = scenarioExpression::setTags($notifCondition, $scenario, true);
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' condition after tags: ' . $notifCondition);
                        $expression = jeedom::fromHumanReadable($notifCondition);
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' condition from readable: ' . $notifCondition);
                        $return = evaluate($expression);
                        if ($return === true) {
                            log::add(__CLASS__, 'debug', $this->getHumanName() . ' Condition returned TRUE, notification triggered');
                            $condition = true;
                        } else if ($return === false) {
                            log::add(__CLASS__, 'debug', $this->getHumanName() . ' Condition returned FALSE, notification skipped');
                            $condition = false;
                        } else {
                            log::add(__CLASS__, 'warning', $this->getHumanName() . ' Condition failed to be evaluated, notification skipped');
                            $condition = false;
                        }
                    }
                    if ($condition) {
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Notif: ' . $notif . ' true');
                        $this->notifBin();
                        $change = true;
                    }
                }
                if ($collect == $todayStr) {
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' Ack: ' . $collect . ' true');
                    $this->ackBin(true);
                    $change = true;
                }
                if ($nextRunRecorded == false && DateTime::createFromFormat("Y-m-d H:i", $collect) > $dtNow) {
                    $cmd = $this->getCmd(null, 'nextcollect');
                    $cmd->event($collect);
                    $nextRunRecorded = true;
                }
            }
        }

        $this->cleanSpecificDates();
        if ($change || ($hour == 0 && $minute == 5)) {
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
            $this->setConfiguration('collect_time', '08:00');
            $this->setConfiguration('notif_days', 1);
            $this->setConfiguration('notif_time', '20:00');
            $this->setConfiguration('paire', 1);
            $this->setConfiguration('impaire', 1);
            $this->setConfiguration('color', 'green');   
            $this->setConfiguration('counter', 'auto');
            for ($i = 1; $i <= 12; $i++) {
                $this->setConfiguration('month_'.$i, 1);
            } 
            $this->setIsEnable(1);
            $this->setIsVisible(0);
        }
    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
        if ($this->getConfiguration('type','') <> 'whole') {
            if ($this->getConfiguration('notif_days', '') <> '') {
                $options = array('options' => array('min_range' => 0));
                if (filter_var($this->getConfiguration('notif_days'), FILTER_VALIDATE_INT, $options) === false) {
                    throw new Exception($this->getHumanName() . ": " . __('Le nombre de jours pour la notification doit être un entier positif ou 0 ou être laissé vide',__FILE__));
                }
            } else {
                $this->setConfiguration('notif_days', 0);
            }
            if ($this->getConfiguration('seuil', '') <> '') {
                $options = array('options' => array('min_range' => 0));
                if (!filter_var($this->getConfiguration('seuil'), FILTER_VALIDATE_INT, $options)) {
                    throw new Exception($this->getHumanName() . ": " . __('Le seuil doit être un entier positif ou être laissé vide',__FILE__));
                }
            }
            if ($this->getConfiguration('notif_time') == '') {
                throw new Exception($this->getHumanName() . ": " . __('L\'heure de notification ne peut pas être vide',__FILE__));
            }
            if ($this->getConfiguration('collect_time') == '') {
                throw new Exception($this->getHumanName() . ": " . __('L\'heure de ramassage ne peut pas être vide',__FILE__));
            }
        }
        
        $specificCrons = $this->getConfiguration('specific_cron');
        if (is_array($specificCrons)) {
            foreach ($specificCrons as $specificCron) {
                if (isset($specificCron['mycron'])) {
                    $cron = new cron();
                    $cron->setSchedule($specificCron['mycron']);
                    if (!$cron->getNextRunDate()) {
                        throw new Exception($this->getHumanName() . ": " . __('L\'expression cron n\'est pas valide :',__FILE__) . $specificCron['mycron']);
                    }
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
            $cmd = $this->getCmd(null, 'nextcollect');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('nextcollect');
                $cmd->setEqLogic_id($this->getId());
                $cmd->setName('Prochain ramassage');
                $cmd->setType('info');
                $cmd->setSubType('string');
                $cmd->setEventOnly(1);
                $cmd->setIsHistorized(0);
                $cmd->setTemplate('mobile', 'line');
                $cmd->setTemplate('dashboard', 'line');       
                $cmd->save();
            }
            $dtNow = new DateTime("now");
            $nextOne = $this->getNextCollectsAndNotifs(1);
            if (is_array($nextOne)) {
                foreach ($nextOne as $collect => $notif) {
                    if (DateTime::createFromFormat("Y-m-d H:i", $collect) > $dtNow) {
                        $cmd = $this->getCmd(null, 'nextcollect');
                        $cmd->event($collect);
                    }
                }
            }
        }
        $this->emptyCacheWidget();
        $this->refreshWhole();

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

            $binnotifs = "";
            $binscript = "";
            $bincalendar = "";
            //Status
            if (config::byKey('notifs','mybin','',true) == 1) {
                foreach ($eqLogics as $eqLogic) {
                    if ($eqLogic->getConfiguration('type') == 'whole') {
                        continue;
                    }
                    $binCmd = $eqLogic->getCmd(null, 'bin');
                    $binStatus = $binCmd->execCmd();
                    if ($eqLogic->getIsEnable() == 1 && $binStatus == 1) {
                        $binimg = $this->getColorAttr($eqLogic->getConfiguration('color'), 'icon_on');
                        $ackCmd = $eqLogic->getCmd(null, 'ack');
                        $counterCmd = $eqLogic->getCmd(null, 'counter');
                        $binnotifs = $binnotifs . '<div style="display: inline-block;" class="cmd ack'.$ackCmd->getId().' cursor" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/'.$binimg.'" width="80px"/>';
                        $binnotifs = $binnotifs . '<br/><i class="fas fa-tachometer-alt"></i> ' . $counterCmd->execCmd();
                        $binnotifs = $binnotifs . '</div>';
                        $binscript = $binscript . "$('.eqLogic[data-eqLogic_uid=".$replace['#uid#']."] .ack".$ackCmd->getId()."').on('click', function () {jeedom.cmd.execute({id: '".$ackCmd->getId()."'});});";
                    }
                }

                if ($binnotifs == "") {
                    $binnotifs = 'none';
                }
            }
            $replace['#binmsg#'] = __('Il n\'y a (plus) aucune poubelle à sortir',__FILE__);
            $replace['#binscript#'] = $binscript;
            $replace['#binnotifs#'] = $binnotifs;

            // calendar
            if (config::byKey('calendar','mybin','',true) == 1) {
                $bincalendar = "calendar";
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
                        if ($eqLogic->getConfiguration('notif_days', 0) > 0 && $calendarType == 'notif') {
                            $dtCheck->modify('+'.$eqLogic->getConfiguration('notif_days').' day');
                        }
                        if ($eqLogic->checkIfBin($dtCheck)) {
                            $color = $this->getColorAttr($eqLogic->getConfiguration('color'), 'icon_on');
                            $display = $display . '<img src="plugins/mybin/data/images/'.$color.'" width="20px">';
                        }
                    }
                    if ($display == "") {
                        $display = '<img src="plugins/mybin/data/images/nothing.png" width="20px">';
                    }
                    $replace['#binimg_day'.$i.'#'] = $display;
                    $dtDisplay->modify('+1 day');
                }
            }
            $replace['#bincalendar#'] = $bincalendar;

            $html = template_replace($replace, getTemplate('core', $version, 'mybin.template', __CLASS__));
            cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        }
        
        // single bin widget 
        else {
            $binCmd = $this->getCmd(null, 'bin');
            $binnotifs = "";
            $binscript = "";
            if ($binCmd->getIsVisible() == 1) {
                $iconOn = $this->getColorAttr($this->getConfiguration('color'), 'icon_on');
                $iconOff = $this->getColorAttr($this->getConfiguration('color'), 'icon_off');
                $binnotifs = '<span class="cmd" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/'.$iconOff.'" width="70px"></span>';
                $binscript = "";
                $binStatus = $binCmd->execCmd();
                if ($binStatus == 1 && $binCmd->getIsVisible() == 1) {
                    $bining = $this->getColorAttr($this->getConfiguration('color'), 'icon_on');
                    $ackCmd = $this->getCmd(null, 'ack');
                    $binnotifs = '<span class="cmd ack';
                    if ($ackCmd->getIsVisible() == 1) {
                        $binnotifs = $binnotifs.$ackCmd->getId().' cursor';
                        $binscript = "$('.eqLogic[data-eqLogic_uid=".$replace['#uid#']."] .ack".$ackCmd->getId()."').on('click', function () {jeedom.cmd.execute({id: '".$ackCmd->getId()."'});});";
                    }
                    $binnotifs = $binnotifs.'" data-type="info" data-subtype="binary"><img src="plugins/mybin/data/images/'.$iconOn.'" width="70px"></span>';
                }
            }
            $replace['#binscript#'] = $binscript;
            $replace['#binnotifs#'] = $binnotifs;
            
            $counterCmd = $this->getCmd(null, 'counter');
            if ($counterCmd->getIsVisible() == 1) {
                $replace['#counter_id#'] = $counterCmd->getId();
                $replace['#counter_uid#'] = $counterCmd->getId();
                $replace['#counter_eqLogic_id#'] = $replace['#uid#'];
                $replace['#counter_collectDate#'] = $counterCmd->getCollectDate();
                $replace['#counter_valueDate#'] = $counterCmd->getValueDate();
                $replace['#counter_minValue#'] = $counterCmd->getConfiguration('minValue', 0);
                $replace['#counter_maxValue#'] = $counterCmd->getConfiguration('maxValue');
                $replace['#counter_state#'] = $counterCmd->execCmd();
                $replace['#counter_unite#'] = $counterCmd->getUnite();
            } else {
                $replace['#counter_id#'] = '';
            }
            
            
            $resetCmd = $this->getCmd(null, 'resetcounter');
            if ($resetCmd->getIsVisible() == 1) {
                $replace['#reset_id#'] = $resetCmd->getId();
                $replace['#reset_uid#'] = $resetCmd->getId();
            } else {
                $replace['#reset_id#'] = '';
            }
            
            $nextCollectCmd = $this->getCmd(null, 'nextcollect');
            if ($nextCollectCmd->getIsVisible() == 1) {
                $replace['#nextcollectname#'] = $nextCollectCmd->getName();
                $replace['#nextcollectdate#'] = $nextCollectCmd->execCmd();
            } else {
                $replace['#nextcollectname#'] = '';
            }
            
            $html = template_replace($replace, getTemplate('core', $version, 'singlebin.template', __CLASS__));
            cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        }
        return $html;
        
    }
    
    public function checkIfBin($dt) {
        if ($this->getIsEnable() != 1) {
            return false;
        }
        
        $isSpecificCron = false;
        $isSpecificDay = false;
        $ismonth = false;
        $isweek = false;
        $isday = false;

        $specificCrons = $this->getConfiguration('specific_cron');
        if (is_array($specificCrons)) {
            foreach ($specificCrons as $specificCron) {
                $todayStr = $dt->format("Y-m-d");
                if (isset($specificCron['mycron'])) {
                    $cron = new cron();
                    $cron->setSchedule($specificCron['mycron']);
                    $nextRunCron = $this->getNextRunDate($cron, $todayStr);
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' $todayStr: ' . $todayStr);
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' $nextRunCron: ' . $nextRunCron->format("Y-m-d"));
                    if ($nextRunCron != false) {
                        if ($todayStr == $nextRunCron->format("Y-m-d")) {
                            $isSpecificCron = true;
                            break;
                        }
                    }
                }
            }
        }

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
        $month = 1 * $dt->format('n');
        
        if ($this->getConfiguration('month_'.$month) == 1) {
            $isMonth = true;
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
        if ($isSpecificCron || $isSpecificDay || ($isMonth && $isweek && $isday)) {
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
            return 'plugins/mybin/data/images/'.$this->getColorAttr($color, 'icon_on');
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

    public function cleanSpecificDates() {
        $change = false;
        $specificDays = $this->getConfiguration('specific_day');
        if (is_array($specificDays)) {
            $dtNow = new DateTime("today");
            foreach ($specificDays as $key => $specificDay) {
                if (isset($specificDay['myday'])) {
                    $dtSpec = DateTime::createFromFormat("Y-m-d", $specificDay['myday']);
                    if ($dtSpec < $dtNow) {
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Removal of specific date ' . $specificDay['myday']);
                        unset($specificDays[$key]);
                        $change = true;
                    }
                }
            }
        }
        if ($change) {
            $this->setConfiguration('specific_day', $specificDays);
            $this->save(true);
        }
    }

    public function getNextCollectsAndNotifs($max, $displayOnly = false) {
        $dtNow = new DateTime("now");
        $dtNow->setTime(1 * date('G'), 1 * date('i'), 0, 0);

        $datesArr = array();

        $nbDates = 0;
        $dtCheck = new DateTime("now");
        $pieces = explode(":", $this->getConfiguration('collect_time'));
        $dtCheck->setTime(intval($pieces[0]), intval($pieces[1]));
        for ($i = 0; $i <= 365; $i++) {
            $month = 1 * $dtCheck->format('n');
            $week = 1 * $dtCheck->format('W');
            $day = 1 * $dtCheck->format('w');
            if ($this->getConfiguration('month_'.$month) == 1 && (($week%2 == 0 && $this->getConfiguration('paire') == 1) || ($week%2 != 0 && $this->getConfiguration('impaire') == 1)) && $this->getConfiguration('day_'.$day) == 1) {
                if ($dtCheck >= $dtNow) {
                    $dtNotif = DateTime::createFromFormat("Y-m-d H:i", $dtCheck->format("Y-m-d H:i"));
                    $dtNotif->modify('-'.$this->getConfiguration('notif_days', 0).' day');
                    $pieces = explode(":", $this->getConfiguration('notif_time'));
                    $dtNotif->setTime(intval($pieces[0]), intval($pieces[1]));
                    $datesArr[$dtCheck->format('Y-m-d H:i')] = $dtNotif->format('Y-m-d H:i');
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' add from dates ' . $dtCheck->format('Y-m-d H:i'));
                    $nbDates++;
                    if ($nbDates == $max) {
                        break;
                    }
                }
            }
            $dtCheck->modify('+1 day');
        }
        
        $nbCrons = 0;
        $specificCrons = $this->getConfiguration('specific_cron');
        $skipped = 0;
        if (is_array($specificCrons)) {
            foreach ($specificCrons as $specificCron) {
                if (isset($specificCron['mycron'])) {
                    $nbRuns = 0;
                    $cron = new cron();
                    $cron->setSchedule($specificCron['mycron']);
                    $nextRunCrons = $this->getNextRunDates($cron, $dtNow);
                    foreach ($nextRunCrons as $nextrun) {
                        if ($skipped == 20) {
                            break;
                        }
                        if (substr($nextrun->format('Y-m-d H:i'), -1) <> '0' && substr($nextrun->format('Y-m-d H:i'), -1) <> '5' && !$displayOnly) {
                            log::add(__CLASS__, 'warning', $this->getHumanName() . ' Date from cron skipped because invalid: ' . $nextrun->format('Y-m-d H:i'));
                            $skipped++;
                            continue;
                        }
                        if ($nextrun >= $dtNow) {
                            $dtNotif = DateTime::createFromFormat("Y-m-d H:i", $nextrun->format("Y-m-d H:i"));
                            $dtNotif->modify('-'.$this->getConfiguration('notif_days', 0).' day');
                            $pieces = explode(":", $this->getConfiguration('notif_time'));
                            $dtNotif->setTime(intval($pieces[0]), intval($pieces[1]));
                            $datesArr[$nextrun->format('Y-m-d H:i')] = $dtNotif->format('Y-m-d H:i');
                            log::add(__CLASS__, 'debug', $this->getHumanName() . ' add from crons ' . $nextrun->format('Y-m-d H:i'));
                            $nbRuns++;
                            if ($nbRuns == $max) {
                                break;
                            }
                        }
                    }
                }
                $nbCrons++;
                if ($nbCrons == 10) {
                    break;
                }
            }
        }

        $nbDays = 0;
        $specificDays = $this->getConfiguration('specific_day');
        if (is_array($specificDays)) {
            foreach ($specificDays as $specificDay) {
                if (isset($specificDay['myday'])) {
                    $dtCheck = DateTime::createFromFormat("Y-m-d", $specificDay['myday']);
                    $pieces = explode(":", $this->getConfiguration('collect_time'));
                    $dtCheck->setTime(intval($pieces[0]), intval($pieces[1]));
                    if ($dtCheck >= $dtNow) {
                        $dtNotif = DateTime::createFromFormat("Y-m-d H:i", $dtCheck->format("Y-m-d H:i"));
                        $dtNotif->modify('-'.$this->getConfiguration('notif_days', 0).' day');
                        $pieces = explode(":", $this->getConfiguration('notif_time'));
                        $dtNotif->setTime(intval($pieces[0]), intval($pieces[1]));
                        $datesArr[$dtCheck->format('Y-m-d H:i')] = $dtNotif->format('Y-m-d H:i');
                        log::add(__CLASS__, 'debug', $this->getHumanName() . ' add from days ' . $dtCheck->format('Y-m-d H:i'));
                        $nbDays++;
                        if ($nbDays == $max) {
                            break;
                        }
                    }
                }
            }
        }

        ksort($datesArr);
        array_splice($datesArr, $max, count($datesArr));

        return $datesArr;

    }

    public function getNextRunDates($cron, $start) {
		try {
			$c = new Cron\CronExpression(checkAndFixCron($cron->getSchedule()), new Cron\FieldFactory);
			return $c->getMultipleRunDates(10, $start, false, true);
		} catch (Exception $e) {
			
		} catch (Error $e) {
			
		}
		return false;
	}

    public function getNextRunDate($cron, $start) {
		try {
			$c = new Cron\CronExpression(checkAndFixCron($cron->getSchedule()), new Cron\FieldFactory);
			return $c->getNextRunDate($start, 0, true);
		} catch (Exception $e) {
			
		} catch (Error $e) {
			
		}
		return false;
	}

    public function getColorAttr($id, $attr) {
        $value = "";
        foreach (config::byKey('colors','mybin',array(),true) as $color) {
            if ($color["id"] == $id) {
                $value =  $color[$attr];
                break;
            }
        }
        if ($value == "") {
            log::add('mybin', 'warning', 'Unable to find color attribute ' . $attr . ' for id ' . $id);
        }
        return $value;
    }

    public static function setCustomIcon($id, $type, $file) {
        $colors = config::byKey('colors','mybin',array(),true);
        foreach ($colors as &$color) {
            if ($color["id"] == $id) {
                $color["icon_".$type] = $file;
                break;
            }
        }
        config::save('colors', $colors, 'mybin');
    }

    public static function setDefaultIcon($id, $type) {
        $colors = config::byKey('colors','mybin',array(),true);
        $name = "";
        foreach ($colors as &$color) {
            if ($color["id"] == $id) {
                $color["icon_".$type] = $color["default_".$type];
                $name = $color["icon_".$type];
                break;
            }
        }
        config::save('colors', $colors, 'mybin');
        return $name;
    }

    public static function doesColorNameExist($name) {
        $exist = false;
        $colors = config::byKey('colors','mybin',array(),true);
        foreach ($colors as $color) {
            if (strtolower($color["name"]) == strtolower($name)) {
                $exist = true;
                break;
            }
        }
        return $exist;
    }

    public static function setNewType($name) {
        $colors = config::byKey('colors','mybin',array(),true);
        $color['id'] = str_replace(" ", "_", strtolower($name));
        $color['name'] = $name;
        $color['builtin'] = false;
        $color['icon_on'] = "grey.png";
        $color['icon_off'] = "none2.png";
        array_push($colors, $color);
        config::save('colors', $colors, 'mybin');
        return $color['id'];
    }

    public static function deleteType($id) {
        $deleted = false;
        $colors = config::byKey('colors','mybin',array(),true);
        foreach ($colors as $key => $color) {
            if ($color["id"] == $id) {
                unset($colors[$key]);
                $deleted = true;
                break;
            }
        }
        config::save('colors', $colors, 'mybin');
        return $deleted;
    }
}

class mybinCmd extends cmd {

    public function preSave(){
        if ($this->getLogicalId() == 'counter') {
            $eqLogic = $this->getEqLogic();
            $threshold = $eqLogic->getConfiguration('seuil','');
            $this->setConfiguration('minValue', 0); 
            $this->setConfiguration('maxValue', $threshold);
            log::add('mybin', 'debug', 'Threshold changed to ' . $threshold . ' : ' . $this->getChanged());
        }
    }
    
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
