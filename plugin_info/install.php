<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
  function mybin_install() {
      mybin::createWhole();
      config::save('calendarType', 'collect', 'mybin');
      config::save('notifs', 1, 'mybin');
      config::save('calendar', 1, 'mybin');
  }

// Fonction exécutée automatiquement après la mise à jour du plugin
  function mybin_update() {
      
      $calendarType = config::byKey('calendarType','mybin','unset',true);
      if ($calendarType == 'unset') {
          config::save('calendarType', 'collect', 'mybin');
      }

      $notifs = config::byKey('notifs','mybin','unset',true);
      if ($notifs == 'unset') {
          config::save('notifs', 1, 'mybin');
      }

      $calendar = config::byKey('calendar','mybin','unset',true);
      if ($calendar == 'unset') {
          config::save('calendar', 1, 'mybin');
      }
      
      $wholeFound = false;
      foreach (eqLogic::byType('mybin') as $eqLogic) {
        if ($eqLogic->getConfiguration('type') == 'whole') {
            $wholeFound = true;
        } else {
            if ($eqLogic->getConfiguration('widgetTemplate', 'unset') === 'unset') {
                $eqLogic->setConfiguration('widgetTemplate', 1);
            }
            if (empty($eqLogic->getConfiguration('counter'))) {
                $eqLogic->setConfiguration('counter', 'auto');
            }
            for ($i = 1; $i <= 12; $i++) {
                if ($eqLogic->getConfiguration('month_'.$i, 'unset') === 'unset') {
                    $eqLogic->setConfiguration('month_'.$i, 1);
                }
            }
            if ($eqLogic->getConfiguration('color') === 'braun') {
                $eqLogic->setConfiguration('color', 'brown');
            }
            if ($eqLogic->getConfiguration('notif_veille') === "1") {
                log::add('mybin', 'debug', $eqLogic->getHumanName() . ' notif veille 1');
                $eqLogic->setConfiguration('notif_days', 1);
            } 
            if ($eqLogic->getConfiguration('notif_veille') === "0") {
                log::add('mybin', 'debug', $eqLogic->getHumanName() . ' notif veille 0');
                $eqLogic->setConfiguration('notif_days', 0);
            }
            $eqLogic->setConfiguration('notif_veille', 'unused');
            
            $cmd = $eqLogic->getCmd(null, 'counter');
            if (!is_object($cmd)) {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('counter');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Compteur');
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setEventOnly(1);
                $cmd->setIsHistorized(1);
                $cmd->setTemplate('mobile', 'line');
                $cmd->setTemplate('dashboard', 'line');
                $cmd->save();
            }
            $value = $cmd->execCmd();
            if ($value == '') {
               $cmd->event(0); 
            }
            $cmd = $eqLogic->getCmd(null, 'resetcounter');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('resetcounter');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Reset Compteur');
                $cmd->setType('action');
                $cmd->setSubType('other');
                $cmd->setEventOnly(1);
                $cmd->save();
            }
            $cmd = $eqLogic->getCmd(null, 'nextcollect');
            if (!is_object($cmd))
            {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('nextcollect');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Prochain ramassage');
                $cmd->setType('info');
                $cmd->setSubType('string');
                $cmd->setEventOnly(1);
                $cmd->setIsHistorized(0);
                $cmd->setTemplate('mobile', 'line');
                $cmd->setTemplate('dashboard', 'line');       
                $cmd->save();
            }

            /*
            // transform specific dates in crons
            $specificDays = $eqLogic->getConfiguration('specific_day');
            $specificCrons = $eqLogic->getConfiguration('specific_cron');
            if (is_array($specificDays)) {
                if (count($specificDays) > 0) {
                    if (!is_array($specificCrons)) {
                        $specificCrons = array();
                    }
                    foreach ($specificDays as $key => $specificDay) {
                        if (isset($specificDay['myday'])) {
                            $dtCheck = DateTime::createFromFormat("Y-m-d", $specificDay['myday']);
                            $dtCheck->setTime(intval($eqLogic->getConfiguration('hour')), intval($eqLogic->getConfiguration('minute')));
                            $cron = $dtCheck->format('i H d m N Y');
                            $specificCron['mycron'] = $cron;
                            array_push($specificCrons, $specificCron);
                            unset($specificDays[$key]);
                        }
                    }
                    $eqLogic->setConfiguration('specific_day', $specificDays);
                    $eqLogic->setConfiguration('specific_cron', $specificCrons);
                }
            }
            */           

            $eqLogic->save();
        }
      }
      if (!wholeFound) {
          mybin::createWhole();
      }
  }

// Fonction exécutée automatiquement après la suppression du plugin
  function mybin_remove() {

  }

?>
