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

    public static function cron5() {

    }

    /*     * *********************Méthodes d'instance************************* */

    public function pullLinksys() {

    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {

    }

 
    //Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {

    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement

    public function postUpdate() {

    }
}

class mybinCmd extends cmd {

    public function execute( $_options = array() ) {

    }
}
