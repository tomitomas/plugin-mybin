
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

$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });
$("#div_action_collect").sortable({ axis: "y", cursor: "move", items: ".action_collect", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });
$("#div_action_notif").sortable({ axis: "y", cursor: "move", items: ".action_notif", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });
$("#div_specific_day").sortable({ axis: "y", cursor: "move", items: ".specific_day", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });
$("#div_specific_cron").sortable({ axis: "y", cursor: "move", items: ".specific_cron", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });

// tous les boutons d'action regroupés !
$('.addAction').off('click').on('click', function () {
  addAction({}, $(this).attr('data-type'));
});

// tous les boutons de jours spécifiques regroupés !
$('.addDay').off('click').on('click', function () {
  addDay({});
});

// tous les boutons de cron spécifiques regroupés !
$('.addCron').off('click').on('click', function () {
  addCron({});
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "action"
$("body").off('click', '.listCmdAction').on('click', '.listCmdAction', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({ cmd: { type: 'action' } }, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
    });

  });
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "info"
$("body").off('click', '.listCmdInfo').on('click', '.listCmdInfo', function () {
  //var type = $(this).attr('data-type');
  //var el = $(this).closest('.' + type).find('.expressionAttr[data-l2key=notifCondition]');
  var el = $("body").find('.expressionAttr[data-l2key=notifCondition]');
  jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
    el.value(el.value() + result.human);
  });
});

// copier/coller du core (cmd.configure.php), permet de choisir la liste des actions (scenario, attendre, ...)
$("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

//sert à charger les champs quand on clique dehors -> A garder !!!
$('body').off('focusout', '.cmdAction.expressionAttr[data-l1key=cmd]').on('focusout', '.cmdAction.expressionAttr[data-l1key=cmd]', function (event) {
  var type = $(this).attr('data-type');
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
  });

});

$("body").off('click', '.bt_removeDay').on('click', '.bt_removeDay', function () {
  $(this).closest('.specific_day').remove();
});

$("body").off('click', '.bt_removeCron').on('click', '.bt_removeCron', function () {
  $(this).closest('.specific_cron').remove();
});

// tous les - qui permettent de supprimer la ligne
$("body").off('click', '.bt_removeAction').on('click', '.bt_removeAction', function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

$('.timepicker').datetimepicker({
  datepicker: false,
  step: 5,
  format: 'H:i'
});

$('#bt_configImages').on('click', function () {
  $('#md_modal').dialog({ title: "{{Personnalisation des images}}" });
  $('#md_modal').load('index.php?v=d&plugin=mybin&modal=custom').dialog('open');
});

/*
 * Fonction permettant l'affichage des commandes dans l'équipement
 */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} };
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="id"></span>';
  tr += '</td>';
  tr += '<td>';
  tr += '<div class="row">';
  tr += '<div class="col-sm-6">';
  tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> Icône</a>';
  tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
  tr += '</div>';
  tr += '<div class="col-sm-6">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
  tr += '</div>';
  tr += '</div>';
  tr += '</td>';
  tr += '<td>';
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += '</td>';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;">';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;display:inline-block;margin-left:2px;">';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
  tr += '</td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  if (isset(_cmd.type)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
  }
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

function addAction(_action, _type) {
  var div = '<div class="' + _type + '">';
  div += '<div class="form-group ">';

  div += '<label class="col-sm-3 control-label">Action</label>';
  div += '<div class="col-sm-7">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
  div += '<a class="btn btn-default listCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner une commande}}"><i class="fas fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '<div class="actionOptions">';
  div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
  div += '</div>';
  div += '</div>';
  div += '</div>';

  div += '</div>';

  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
}

function addDay(_day) {
  var div = '<div class="specific_day">';
  div += '<div class="form-group ">';

  div += '<div class="col-sm-6">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeDay roundedLeft" data-l1key="specific_day" data-type="specific_day"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="eqLogicAttr form-control datetimepicker myday" type="text" data-type="specific_day" data-l1key="myday">'
  div += '</div>';
  div += '</div>';

  div += '</div>';

  div += '</div>';

  $('#div_specific_day').append(div);

  $('.datetimepicker').datetimepicker({
    lang: 'fr',
    dayOfWeekStart: 1,
    i18n: {
      fr: {
        months: [
          'Janvier', 'Février', 'Mars', 'Avril',
          'Mai', 'Juin', 'Juillet', 'Aout',
          'Septembre', 'Octobre', 'Novembre', 'Décembre',
        ],
        dayOfWeek: [
          "Di", "Lu", "Ma", "Me",
          "Je", "Ve", "Sa",
        ]
      }
    },
    timepicker: false,
    format: 'Y-m-d'
  });

  $('#div_specific_day .specific_day').last().setValues(_day, '.myday');
}

function addCron(_cron) {
  var div = '<div class="specific_cron">';
  div += '<div class="form-group">';
  div += '<div class="col-sm-7">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeCron roundedLeft" data-l1key="specific_cron" data-type="specific_cron"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input type="text" class="form-control value execute eqLogicAttr mycron" data-type="specific_cron" data-l1key="mycron"/>';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default cursor jeeHelper" data-helper="cron">';
  div += '<i class="fas fa-question-circle"></i>';
  div += '</a>';
  div += '</span>';
  div += '</div>';
  div += '<span class="input-group-btn" >';
  div += '<input type="text" class="eqLogicAttr mycron" data-l1key="add_remove" style="width:50px" title="jour à ajouter/supprimer" value="0" />';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '</div>';

  $('#div_specific_cron').append(div);
  $('#div_specific_cron .specific_cron').last().setValues(_cron, '.mycron');
}

// Fct core permettant de sauvegarder
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.action_collect = $('#div_action_collect .action_collect').getValues('.expressionAttr');
  _eqLogic.configuration.action_notif = $('#div_action_notif .action_notif').getValues('.expressionAttr');
  data = $('#div_specific_day .specific_day').getValues('.myday');

  data.sort(function (a, b) {
    var x = a.myday, y = b.myday;
    return x < y ? -1 : x > y ? 1 : 0;
  });

  _eqLogic.configuration.specific_day = data;

  _eqLogic.configuration.specific_cron = $('#div_specific_cron .specific_cron').getValues('.mycron');

  return _eqLogic;
}

// fct core permettant de restituer les cmd declarées
function printEqLogic(_eqLogic) {

  $('#div_action_collect').empty();
  $('#div_action_notif').empty();
  $('#div_specific_day').empty();
  $('#div_specific_cron').empty();

  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.action_collect)) {
      for (var i in _eqLogic.configuration.action_collect) {
        addAction(_eqLogic.configuration.action_collect[i], 'action_collect');
      }
    }
    if (isset(_eqLogic.configuration.action_notif)) {
      for (var i in _eqLogic.configuration.action_notif) {
        addAction(_eqLogic.configuration.action_notif[i], 'action_notif');
      }
    }
    if (isset(_eqLogic.configuration.specific_day)) {
      for (var i in _eqLogic.configuration.specific_day) {
        addDay(_eqLogic.configuration.specific_day[i]);
      }
    }
    if (isset(_eqLogic.configuration.specific_cron)) {
      for (var i in _eqLogic.configuration.specific_cron) {
        addCron(_eqLogic.configuration.specific_cron[i]);
      }
    }
  }

  $('.allDates').hide();
  $('.dates-' + _eqLogic.id).show();
}