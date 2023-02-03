
configureUploads();

$('.pluginAction[data-action=saveNewType]').on('click', function () {
    $.ajax({
        type: "POST",
        url: "plugins/mybin/core/ajax/mybin.ajax.php",
        data: {
            action: "saveNewType",
            name: $('.newTypeName').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#modal_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            $('.newTypeName').val("");
            $('#modal_alert').showAlert({ message: 'Type "' + data.result.name + '" créé', level: 'success' });
            tr = '<tr id="myColors_' + data.result.id + '" style="background-color: #CCFFCC !important">';
            tr += '<td style="text-align: center; vertical-align:middle; font-weight: bold;">' + data.result.name;
            tr += '<br>';
            tr += '<a class="btn btn-danger pluginAction" data-action="deleteType" color-id="' + data.result.id + '"><i class="icon divers-slightly"></i> {{Supprimer}}</a>';
            tr += '</td>';
            tr += '<td align="center">';
            tr += '<div class="col-xs-9">';
            tr += '<img src="plugins/mybin/data/images/grey.png" class="img-responsive" color-id="' + data.result.id + '" color-type="on" style="max-height : 80px;" >';
            tr += '</div>';
            tr += '<div class="col-xs-3">';
            tr += '<span class="btn btn-default btn-file" style="margin-bottom:10px;">';
            tr += '<i class="fas fa-cloud-upload-alt"></i> {{Nouvelle image}}<input class="pluginAction" data-action="uploadImage" color-id="' + data.result.id + '" color-type="on" type="file" name="file" style="display: inline-block;">';
            tr += '</span>';
            tr += '</div>';
            tr += '</td>';
            tr += '<td align="center">';
            tr += '<div class="col-xs-9">';
            tr += '<img src="plugins/mybin/data/images/none2.png" class="img-responsive" color-id="' + data.result.id + '" color-type="off" style="max-height : 80px;" >';
            tr += '</div>';
            tr += '<div class="col-xs-3">';
            tr += '<span class="btn btn-default btn-file" style="margin-bottom:10px;">';
            tr += '<i class="fas fa-cloud-upload-alt"></i> {{Nouvelle image}}<input class="pluginAction" data-action="uploadImage" color-id="' + data.result.id + '" color-type="off" type="file" name="file" style="display: inline-block;">';
            tr += '</span>';
            tr += '</div>';
            tr += '</td>';
            tr += '</tr>';
            $('#myColors tr:first').before(tr);
            configureUploads();
        }
    });
});

$('#myColors').on('click', '.pluginAction[data-action=deleteType]', function () {
    $.ajax({
        type: "POST",
        url: "plugins/mybin/core/ajax/mybin.ajax.php",
        data: {
            action: "deleteType",
            id: $(this).attr("color-id")
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#modal_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            $('#modal_alert').showAlert({ message: 'Type "' + data.result.id + '" supprimé', level: 'success' });
            $('#myColors_' + data.result.id).remove();
        }
    });
});

function configureUploads() {
    $('.pluginAction[data-action=uploadImage]').each(function () {
        $(this).fileupload({
            replaceFileInput: false,
            url: 'plugins/mybin/core/ajax/mybin.ajax.php?action=uploadCustomImg&id=' + $(this).attr("color-id") + '&icon=' + $(this).attr("color-type") + '&jeedom_token=' + JEEDOM_AJAX_TOKEN,
            dataType: 'json',
            done: function (e, data) {
                if (data.result.state != 'ok') {
                    $('#modal_alert').showAlert({ message: data.result.result, level: 'danger' });
                    return;
                }
                console.log(data);
                console.log(data.result.result.id);
                console.log(data.result.result.icon);
                console.log(data.result.result.url);
                console.log($('.img-responsive[color-id="' + data.result.result.id + '"][color-type="' + data.result.result.icon + '"]'));
                $('.img-responsive[color-id="' + data.result.result.id + '"][color-type="' + data.result.result.icon + '"]').attr('src', data.result.result.url + '?' + new Date().getTime());
            }
        });
    });
}

$('.pluginAction[data-action=deleteImage]').on('click', function () {
    $.ajax({
        type: "POST",
        url: "plugins/mybin/core/ajax/mybin.ajax.php",
        data: {
            action: "deleteCustomImg",
            id: $(this).attr("color-id"),
            icon: $(this).attr("color-type")
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#modal_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            console.log(data.result.id);
            console.log(data.result.icon);
            console.log(data.result.url);
            console.log($('.img-responsive[color-id="' + data.result.id + '"][color-type="' + data.result.icon + '"]'));
            $('.img-responsive[color-id="' + data.result.id + '"][color-type="' + data.result.icon + '"]').attr('src', data.result.url);
        }
    });
});