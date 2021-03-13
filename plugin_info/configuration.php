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
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<form class="form-horizontal">
	<fieldset>
		<legend>
			<i class="fa fa-list-alt"></i> {{Paramètres du widget global}}
		</legend>
        <div class="form-group">
			<label class="col-sm-4 control-label">{{Afficher un widget global pour toutes les poubelles}}</label>
			<div class="col-sm-2">
                <input type="checkbox" class="configKey form-control" data-l1key="globalWidget"/>
			</div>
		</div>
        <div class="form-group">
			<label class="col-sm-4 control-label">{{Eléments à afficher sur le widget global}}</label>
			<div class="col-sm-2">
				<label class="checkbox-inline"><input type="checkbox" class="configKey form-control" data-l1key="notifs"/>{{Notifications}}</label>
				<label class="checkbox-inline"><input type="checkbox" class="configKey form-control" data-l1key="calendar"/>{{Calendrier}}</label>
			</div>
		</div>
        <div class="form-group">
			<label class="col-sm-4 control-label">{{Jours que le calendrier doit utiliser}}</label>
			<div class="col-lg-3">
                <select id="sel_calendar" class="configKey form-control" data-l1key="calendarType">
                    <option value="collect">{{Jours de ramassage}}</option>
                    <option value="notif">{{Jours de notification}}</option>
                </select>
			</div>
		</div>
        <div class="form-group">
		  <label class="col-lg-4 control-label" >{{Pièce pour le widget global}}</label>
		  <div class="col-lg-3">
			<select id="sel_object" class="configKey form-control" data-l1key="parentObject">
			  <option value="">{{Aucune}}</option>
			  <?php
				foreach (jeeObject::all() as $object) {
				  echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
				}
			  ?>
			</select>
		  </div>
		</div>
	</fieldset>
</form>
