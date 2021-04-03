
$('.pluginAction[data-action=uploadImage]').each(function () {
    $(this).fileupload({
        replaceFileInput: false,
        url: 'plugins/mybin/core/ajax/mybin.ajax.php?action=uploadCustomImg&id='+$(this).attr("color-id")+'&icon='+$(this).attr("color-type")+'&jeedom_token='+JEEDOM_AJAX_TOKEN,
        dataType: 'json',
        done: function (e, data) {
            if (data.result.state != 'ok') {
                $('#modal_alert').showAlert({message: data.result.result, level: 'danger'});
                return;
            }
            console.log(data);
            console.log(data.result.result.id);
            console.log(data.result.result.icon);
            console.log(data.result.result.url);
            console.log($('.img-responsive[color-id="'+data.result.result.id+'"][color-type="'+data.result.result.icon+'"]'));
            $('.img-responsive[color-id="'+data.result.result.id+'"][color-type="'+data.result.result.icon+'"]').attr('src', data.result.result.url);
        }
    });
});

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
                $('#modal_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            console.log(data.result.id);
            console.log(data.result.icon);
            console.log(data.result.url);
            console.log($('.img-responsive[color-id="'+data.result.id+'"][color-type="'+data.result.icon+'"]'));
            $('.img-responsive[color-id="'+data.result.id+'"][color-type="'+data.result.result.icon+'"]').attr('src', data.result.url);
        }
    });
});