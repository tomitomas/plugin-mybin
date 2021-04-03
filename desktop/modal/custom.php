<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div id='modal_alert'></div>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active">
        <legend><i class="fas fa-plus-circle"></i> {{Créer un nouveau type de poubelle}}</legend>
        <div class="row">
            <div class="col-sm-3">
                <center>
                    <label>{{Nom}} : </label><input type="text" name="name"/>
                </center>
            </div>
            <div class="col-sm-3">
                <center>
                    <label>{{Icône ON}} : </label><input color-type="on" type="file" name="fileon" style="display: inline-block;">
                </center>
            </div>
            <div class="col-sm-3">
                <center>
                    <label>{{Icône OFF}} : </label><input color-type="off" type="file" name="fileoff" style="display: inline-block;">
                </center>
            </div>
            <div class="col-sm-3">
                <center>
                    <label><a class="btn btn-success btn-sm addAction" data-type="action_collect" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un type}}</a></label>
                </center>
            </div>
        </div>
        <br>
        <legend><i class="icon divers-slightly"></i> {{Mes types de poubelle}}</legend>
        <div>
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th style="text-align: center;" class="col-sm-2">{{Nom}}</th>
                        <th style="text-align: center;" class="col-sm-4">{{Icône ON}}</th>
                        <th style="text-align: center;" class="col-sm-4">{{Icône OFF}}</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach (config::byKey('colors','mybin',array(),true) as $color) {
                        echo('<tr>');
                        echo('<td style="text-align: center; vertical-align:middle; font-weight: bold;">'.__($color["name"], __FILE__).'</td>');
                        echo('<td align="center">');
                        echo('<div class="col-xs-9">');
                        echo('<img src="plugins/mybin/data/images/'.$color["icon_on"].'" class="img-responsive" color-id="'.$color["id"].'" color-type="on" style="max-height : 80px;" >');
                        echo('</div>');
                        echo('<div class="col-xs-3">');
                        echo('<span class="btn btn-default btn-file" style="margin-bottom:10px;">');
                        echo('<i class="fas fa-cloud-upload-alt"></i> {{Nouvelle image}}<input class="pluginAction" data-action="uploadImage" color-id="'.$color["id"].'" color-type="on" type="file" name="file" style="display: inline-block;">');
                        echo('</span>');
                        echo('<a class="btn btn-danger pluginAction" data-action="deleteImage" color-id="'.$color["id"].'" color-type="on"><i class="fas fa-undo"></i> {{Réinitialiser}}</a>');
                        echo('</div>');
                        echo('</td>');
                        echo('<td align="center">');
                        echo('<div class="col-xs-9">');
                        echo('<img src="plugins/mybin/data/images/'.$color["icon_off"].'" class="img-responsive" color-id="'.$color["id"].'" color-type="off" style="max-height : 80px;" >');
                        echo('</div>');
                        echo('<div class="col-xs-3">');
                        echo('<span class="btn btn-default btn-file" style="margin-bottom:10px;">');
                        echo('<i class="fas fa-cloud-upload-alt"></i> {{Nouvelle image}}<input class="pluginAction" data-action="uploadImage" color-id="'.$color["id"].'" color-type="off" type="file" name="file" style="display: inline-block;">');
                        echo('</span>');
                        echo('<a class="btn btn-danger pluginAction" data-action="deleteImage" color-id="'.$color["id"].'" color-type="off"><i class="fas fa-undo"></i> {{Réinitialiser}}</a>');
                        echo('</div>');
                        echo('</td>');
                        echo('</tr>');
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include_file('desktop', 'custom', 'js', 'mybin');
?>