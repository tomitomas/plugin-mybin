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

  }

// Fonction exécutée automatiquement après la mise à jour du plugin
  function mybin_update() {
      foreach (eqLogic::byType('mybin') as $eqLogic) {
        if ($eqLogic->getConfiguration('greenbin_paire', 'unset') === 'unset') {
            $eqLogic->setConfiguration('greenbin_paire', 1);
        }
        if ($eqLogic->getConfiguration('greenbin_impaire', 'unset') === 'unset') {
            $eqLogic->setConfiguration('greenbin_impaire', 1);
        }
        if ($eqLogic->getConfiguration('yellowbin_paire', 'unset') === 'unset') {
            $eqLogic->setConfiguration('yellowbin_paire', 1);
        }
        if ($eqLogic->getConfiguration('yellowbin_impaire', 'unset') === 'unset') {
            $eqLogic->setConfiguration('yellowbin_impaire', 1);
        }
        $eqLogic->save();
      }
  }

// Fonction exécutée automatiquement après la suppression du plugin
  function mybin_remove() {

  }

?>
