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
    config::save('colors', mybin_createColors(), 'mybin');
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function mybin_update() {

    $colors = config::byKey('colors', 'mybin', 'unset', true);
    if ($colors == 'unset') {
        config::save('colors', mybin_createColors(), 'mybin');
    }

    $calendarType = config::byKey('calendarType', 'mybin', 'unset', true);
    if ($calendarType == 'unset') {
        config::save('calendarType', 'collect', 'mybin');
    }

    $notifs = config::byKey('notifs', 'mybin', 'unset', true);
    if ($notifs == 'unset') {
        config::save('notifs', 1, 'mybin');
    }

    $calendar = config::byKey('calendar', 'mybin', 'unset', true);
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
                if ($eqLogic->getConfiguration('month_' . $i, 'unset') === 'unset') {
                    $eqLogic->setConfiguration('month_' . $i, 1);
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

            if ($eqLogic->getConfiguration('Occm_0', 'unset') === 'unset') {
                log::add('mybin', 'debug', $eqLogic->getHumanName() . ' occm_0 unset');
                $eqLogic->setConfiguration('Occm_0', 1);
            }

            if ($eqLogic->getConfiguration('collect_time', 'unset') === 'unset') {
                $hour = $eqLogic->getConfiguration('hour');
                if (intval($hour) < 10) {
                    $hour = '0' . $hour;
                }
                $minute = $eqLogic->getConfiguration('minute');
                if (intval($minute) < 10) {
                    $minute = '0' . $minute;
                }
                $eqLogic->setConfiguration('collect_time', $hour . ':' . $minute);
            }

            if ($eqLogic->getConfiguration('notif_time', 'unset') === 'unset') {
                $hour = $eqLogic->getConfiguration('notif_hour');
                if (intval($hour) < 10) {
                    $hour = '0' . $hour;
                }
                $minute = $eqLogic->getConfiguration('notif_minute');
                if (intval($minute) < 10) {
                    $minute = '0' . $minute;
                }
                $eqLogic->setConfiguration('notif_time', $hour . ':' . $minute);
            }

            $cmd = $eqLogic->getCmd(null, 'counter');
            if (!is_object($cmd)) {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('counter');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Compteur');
                $cmd->setType('info');
                $cmd->setSubType('numeric');
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
            if (!is_object($cmd)) {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('resetcounter');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Reset Compteur');
                $cmd->setType('action');
                $cmd->setSubType('other');
                $cmd->save();
            }
            $cmd = $eqLogic->getCmd(null, 'nextcollect');
            if (!is_object($cmd)) {
                $cmd = new mybinCmd();
                $cmd->setLogicalId('nextcollect');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName('Prochain ramassage');
                $cmd->setType('info');
                $cmd->setSubType('string');
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

function mybin_createColors() {
    $colors = array();

    $color1['id'] = "black";
    $color1['name'] = "Noire";
    $color1['builtin'] = true;
    $color1['icon_on'] = "black.png";
    $color1['icon_off'] = "none2.png";
    $color1['default_on'] = "black.png";
    $color1['default_off'] = "none2.png";

    array_push($colors, $color1);

    $color2['id'] = "blue";
    $color2['name'] = "Bleue";
    $color2['builtin'] = true;
    $color2['icon_on'] = "blue.png";
    $color2['icon_off'] = "none2.png";
    $color2['default_on'] = "blue.png";
    $color2['default_off'] = "none2.png";

    array_push($colors, $color2);

    $color3['id'] = "brown";
    $color3['name'] = "Marron";
    $color3['builtin'] = true;
    $color3['icon_on'] = "brown.png";
    $color3['icon_off'] = "none2.png";
    $color3['default_on'] = "brown.png";
    $color3['default_off'] = "none2.png";

    array_push($colors, $color3);

    $color4['id'] = "bulky";
    $color4['name'] = "Encombrants";
    $color4['builtin'] = true;
    $color4['icon_on'] = "bulky.png";
    $color4['icon_off'] = "none2_bulky.png";
    $color4['default_on'] = "bulky.png";
    $color4['default_off'] = "none2_bulky.png";

    array_push($colors, $color4);

    $color5['id'] = "green";
    $color5['name'] = "Verte";
    $color5['builtin'] = true;
    $color5['icon_on'] = "green.png";
    $color5['icon_off'] = "none2.png";
    $color5['default_on'] = "green.png";
    $color5['default_off'] = "none2.png";

    array_push($colors, $color5);

    $color6['id'] = "grey";
    $color6['name'] = "Grise";
    $color6['builtin'] = true;
    $color6['icon_on'] = "grey.png";
    $color6['icon_off'] = "none2.png";
    $color6['default_on'] = "grey.png";
    $color6['default_off'] = "none2.png";

    array_push($colors, $color6);

    $color7['id'] = "plants";
    $color7['name'] = "Végétaux";
    $color7['builtin'] = true;
    $color7['icon_on'] = "plants.png";
    $color7['icon_off'] = "none2_plants.png";
    $color7['default_on'] = "plants.png";
    $color7['default_off'] = "none2_plants.png";

    array_push($colors, $color7);

    $color8['id'] = "violet";
    $color8['name'] = "Violette";
    $color8['builtin'] = true;
    $color8['icon_on'] = "violet.png";
    $color8['icon_off'] = "none2.png";
    $color8['default_on'] = "violet.png";
    $color8['default_off'] = "none2.png";

    array_push($colors, $color8);

    $color9['id'] = "yellow";
    $color9['name'] = "Jaune";
    $color9['builtin'] = true;
    $color9['icon_on'] = "yellow.png";
    $color9['icon_off'] = "none2.png";
    $color9['default_on'] = "yellow.png";
    $color9['default_off'] = "none2.png";

    array_push($colors, $color9);

    return $colors;
}

// Fonction exécutée automatiquement après la suppression du plugin
function mybin_remove() {
}
