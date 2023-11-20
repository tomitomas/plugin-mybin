<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('desktop', 'mybin', 'css', 'mybin');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'mybin');
$plugin = plugin::byId('mybin');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

$allDates = array();
/** @var mybinb $eqLogic */
foreach ($eqLogics as $eqLogic) {
	if ($eqLogic->getConfiguration('type', '') <> 'whole') {
		$allDates[$eqLogic->getId()] = $eqLogic->getNextCollectsAndNotifs(10, true);
	}
}
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="row">
			<div class="col-sm-10">
				<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
				<div class="eqLogicThumbnailContainer">
					<div class="cursor eqLogicAction logoPrimary" data-action="add" style="color:var(--main-color);">
						<i class="fas fa-plus-circle"></i>
						<br>
						<span>{{Ajouter}}</span>
					</div>
					<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf" style="color:var(--main-color);">
						<i class="fas fa-wrench"></i>
						<br>
						<span>{{Configuration}}</span>
					</div>
					<div class="cursor eqLogicAction logoSecondary" id="bt_configImages" style="color:var(--main-color);">
						<i class="fas fa-images"></i>
						<br>
						<span>{{Personnalisation}}</span>
					</div>
				</div>
			</div>

			<?php
			// uniquement si on est en version 4.4 ou supérieur
			$jeedomVersion  = jeedom::version() ?? '0';
			$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
			if ($displayInfoValue) {
			?>
				<div class="col-sm-2">
					<legend><i class=" fas fa-comments"></i> {{Community}}</legend>
					<div class="eqLogicThumbnailContainer">
						<div class="cursor eqLogicAction logoSecondary" data-action="createCommunityPost" style="color:var(--main-color);">
							<i class="fas fa-ambulance"></i>
							<br>
							<span style="color:var(--txt-color)">{{Créer un post Community}}</span>
						</div>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<legend><i class="icon divers-slightly"></i> {{Mes poubelles}}</legend>
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type', '') == 'whole') {
					continue;
				}
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div id="customBin" class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img id="customBinImg" src="' . $eqLogic->getImage() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}
				</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<form class="form-horizontal">
					<fieldset>
						<div class="row">
							<div class=" col-lg-6">
								<legend><i class="icon divers-slightly"></i> {{Général}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
									<div class="col-sm-5">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement My Bin}}" />
									</div>
									<div class="col-sm-2 tagColor">
										tag <strong>#bin_name#</strong>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Objet parent}}</label>
									<div class="col-sm-7">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php $options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Catégorie}}</label>
									<div class="col-sm-9">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Options}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" />{{Visible}}</label>
									</div>
								</div>
								<br>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Couleur de la poubelle}}</label>
									<div class="col-sm-7">
										<span class="col-sm-4">
											<select id="sel_color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="color">
												<?php
												$colors = config::byKey('colors', 'mybin', array(), true);
												usort($colors, function ($a, $b) {
													return strtolower($a['name']) <=> strtolower($b['name']);
												});
												foreach ($colors as $color) {
													echo '<option value="' . $color["id"] . '">{{' . $color["name"] . '}}</option>';
												}
												?>
											</select>
										</span>
										<span class="col-sm-4 tagColor">
											tag <strong>#bin_color#</strong>
										</span>
									</div>
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-6">
								<legend><i class="fas fa-truck"></i> {{Ramassage de la poubelle}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Heure de ramassage}}</label>
									<div class="col-sm-2">
										<input class="eqLogicAttr timepicker" type="text" data-l1key="configuration" data-l2key="collect_time" style="width:100%">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Mois de ramassage}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_1" />{{Janvier}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_2" />{{Février}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_3" />{{Mars}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_4" />{{Avril}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_5" />{{Mai}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_6" />{{Juin}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_7" />{{Juillet}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_8" />{{Août}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_9" />{{Septembre}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_10" />{{Octobre}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_11" />{{Novembre}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="month_12" />{{Décembre}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Semaine(s) de ramassage}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="paire" />{{Semaines paires}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="impaire" />{{Semaines impaires}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Jour(s) de ramassage}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_1" />{{Lundi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_2" />{{Mardi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_3" />{{Mercredi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_4" />{{Jeudi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_5" />{{Vendredi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_6" />{{Samedi}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="day_0" />{{Dimanche}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Occurrence(s) du mois}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_0" />{{Tous}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_1" />{{1er du mois}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_2" />{{2ème}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_3" />{{3ème}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_4" />{{4ème}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Occm_5" />{{Dernier du mois}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Action si jour férié}}</label>
									<div class="col-sm-4">
										<select id="sel_ferie_action" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="public_holiday_action">
											<option value="nothing">{{Aucune}}</option>
											<option value="nextDay">{{Décalage au jour suivant}}</option>
											<option value="nextDayWithoutSunday">{{Décalage au jour suivant sauf dimanche}}</option>
											<option value="nextDayWithoutWeekEnd">{{Décalage au jour suivant sauf weekend}}</option>
											<option value="remove">{{Supprimer l'occurence}}</option>
										</select>
									</div>
									<div class="col-sm-4">
										<label class="control-label">
											<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="withAlsace" />
											{{avec Alsace-Moselle}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Date(s) particulière(s) de ramassage}}</label>
									<div class="col-sm-7">
										<a class="btn btn-success btn-sm addDay" data-type="specific_day" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une date}}</a>
										<div id="div_specific_day"></div>
									</div>
								</div>
								<br />
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{Permet de calculer automatiquement des prochaines dates : toutes les 3 semaines, tous les 11 jours, ...<br>Indiquez la date du dernier ramassage connu et la fréquence (en jours), le plugin s'occupera d'ajouter les bonnes dates au bon moment.}}">{{Calcul auto date(s) particulière(s)}}</label>
									<div class="col-sm-7" id="div_specific_day_auto">
										<div class="col-sm-6 noPadLeft">
											<div class="input-group">
												<span class="input-group-btn">
													<a class="btn btn-default bt_removeDayAuto roundedLeft" data-l1key="specific_day_auto" data-type="specific_day_auto"><i class="fas fa-minus-circle"></i></a>
												</span>
												<input class="eqLogicAttr form-control datetimepicker" type="text" data-type="specific_day_auto" data-l1key="last_day" placeholder="dernier passage connu">
											</div>
										</div>
										<div class="col-sm-6">
											<input class="eqLogicAttr form-control" type="number" data-l1key="recurrence" placeholder="nb de jours">
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{Vous pouvez ajouter des expressions cron pour gérer des fréquences de ramassage particulières}}">{{Mode expert}}</label>
									<div class="col-sm-7">
										<a class="btn btn-success btn-sm addCron" data-type="specific_cron" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un cron}}</a>
										<div id="div_specific_cron"></div>
									</div>
								</div>
							</div>

							<div class="col-lg-6">
								<legend><i class="icon jeedomapp-preset"></i> {{Options}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Template de widget}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour utiliser le template de widget}}"></i></sup>
									</label>
									<div class="col-sm-1">
										<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widgetTemplate" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Affichage du format date}}</label>
									<div class="col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="dateFormat">
											<option value="Y-m-d H:i">2022-10-16 16:30</option>
											<option value="d/m/Y H:i">16/10/2022 16:30</option>
											<option value="d/m/y H:i">16/10/22 16:30</option>
											<option value="d/m/y">16/10/22</option>
											<option value="D d/m/Y">Lun 16/10/2022</option>
											<option value="l d F">Lundi 16 Octobre</option>
											<option value="custom">Personnalisé</option>
											<!-- <option value="U">1665763502 (timestamp)</option> -->
										</select>
									</div>
								</div>

								<div class="form-group" style="padding-top:2px;">
									<div class="dateFormatCustomDiv" style="display:none;">
										<label class="col-sm-3 control-label">Format de date personnalisé
											<sup>
												<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Cf <a href='https://www.php.net/manual/en/datetime.format.php'>doc PHP</a> pour définir un format correct"></i>
											</sup>
										</label>
										<div class="col-sm-7">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="dateFormatCustom" placeholder="{{Date au format PHP}}" />
										</div>
									</div>
								</div>
							</div>

							<div class="col-lg-6">
								<legend><i class="fas fa-info-circle"></i> {{Informations}}</legend>
								{{Avec votre configuration, voici les 10 prochaines dates de ramassage et de notification :}}
								<br>
								<div class="form-group">
									<br>
									<?php
									foreach ($allDates as $key => $value) {
										echo '<div class="allDates dates-' . $key . '" style="display: none;">';
										foreach ($value as $collect => $notif) {
											$colorCollect = 'primary';
											$helpCollect = '';
											if (substr($collect, -1) <> '0' && substr($collect, -1) <> '5') {
												$colorCollect = 'warning';
												$helpCollect = 'help';
											}
											$dtCollect = DateTime::createFromFormat("Y-m-d H:i", $collect);
											$dtNotif = DateTime::createFromFormat("Y-m-d H:i", $notif);
											$colorNotif = 'info';
											$helpNotif = '';
											if ($dtNotif > $dtCollect) {
												$colorNotif = 'warning';
												$helpNotif = 'help';
											}
											echo '<div class="col-sm-12">';
											echo '<label class="col-sm-2 control-label ' . $helpCollect . '" data-help="{{Le plugin ne fonctionne que toutes les 5min. Cette date de ramassage sera ignorée. Changez votre cron.}}">{{Ramassage}}</label>';
											echo '<div class="col-sm-4" ><span class="label label-' . $colorCollect . '">' . date_fr($dtCollect->format('l')) . ' ' . $dtCollect->format('j') . ' ' . date_fr($dtCollect->format('F')) . ' ' . $dtCollect->format('Y') . ' {{à}} ' . $dtCollect->format('G:i') . '</span></div>';
											echo '<label class="col-sm-2 control-label ' . $helpNotif . '" data-help="{{Cette date de notification est après la date de ramassage. Vérifiez vos paramètres.}}">{{Notification}}</label>';
											echo '<div class="col-sm-4"><span class="label label-' . $colorNotif . '">' . date_fr($dtNotif->format('l')) . ' ' . $dtNotif->format('j') . ' ' . date_fr($dtNotif->format('F')) . ' ' . $dtNotif->format('Y') . ' {{à}} ' . $dtNotif->format('G:i') . '</span></div>';
											echo '</div>';
										}
										echo '</div>';
									}
									?>
								</div>
							</div>
						</div>

						<div class="row">

							<div class="col-lg-6">
								<legend><i class="icon jeedom-alerte2"></i> {{Notification}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{Pour être notifié le jour même du ramassage, laissez le champ vide. Attention à l'heure dans ce cas.}}">{{Notification}}</label>
									<div class="col-sm-7">
										<span class="col-sm-2 noPadLeft">
											<input type=" number" min="0" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="notif_days" />
										</span>
										<span class="col-sm-4">
											<label>{{jour(s) avant à}}</label>
										</span>
										<span class="col-sm-3">
											<input class="eqLogicAttr timepicker" type="text" data-l1key="configuration" data-l2key="notif_time" style="width:100%">
										</span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{Cette expression binaire sera évaluée au moment de la notification}}">{{Condition de notification}}</label>
									<div class="col-sm-6 input-group">
										<input class="eqLogicAttr expressionAttr form-control roundedLeft ui-autocomplete-input notifCondition" data-l1key="configuration" data-l2key="notifCondition">
										<span class="input-group-btn">
											<button type="button" class="btn btn-default cursor listCmdInfoCond tooltipstered" tooltip="Rechercher une commande"><i class="fas fa-list-alt"></i></button>
										</span>
									</div>
								</div>
							</div>

							<div class="col-lg-6">
								<legend><i class="fas fa-tachometer-alt"></i> {{Compteur}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{En automatique, le compteur s'incrémentera à chaque ramassage ou lorsque la commande 'ack' est exécutée. En manuel, il ne s'incrémentera que si la commande 'ack' est exécutée.}}">{{Type}}</label>
									<div class="col-sm-7">
										<select id="sel_counter" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="counter">
											<option value="auto">{{Automatique}}</option>
											<option value="manu">{{Manuel}}</option>
										</select>

									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label help" data-help="{{Seuil au-delà duquel les notifications seront suspendues. Laissez le champ vide pour aucun seuil.}}">{{Seuil}}</label>
									<div class="col-sm-3">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="seuil" />
									</div>
									<div class="col-sm-4 tagColor">
										tag <strong>#bin_threshold#</strong>
									</div>
								</div>
							</div>

						</div>

						<div class="col-lg-12">
							<div class="col-lg-12">
								<legend><i class="fas fa-sign-out-alt"></i> {{Action(s) sur ramassage}}</legend><label><a class="btn btn-success btn-sm addAction" data-type="action_collect" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a></label>
								<div id="div_action_collect"></div>
							</div>
							<br>
							<div class="col-lg-12">
								<legend><i class="fas fa-sign-in-alt"></i> {{Action(s) sur notification}}</legend><label><a class="btn btn-success btn-sm addAction" data-type="action_notif" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a></label>
								<div id="div_action_notif"></div>
							</div>
						</div>
					</fieldset>
				</form>
				<hr>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;">
			<i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/> -->
				<br />
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width:50px;">{{Id}}</th>
							<th style="width:300px;">{{Nom}}</th>
							<th>{{Type}}</th>
							<th>{{Etat}}</th>
							<th class="col-xs-3">{{Options}}</th>
							<th class="col-xs-2">{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>

<?php include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'mybin'); ?>
<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, nom_du_plugin) -->
<?php include_file('desktop', 'mybin', 'js', 'mybin'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>