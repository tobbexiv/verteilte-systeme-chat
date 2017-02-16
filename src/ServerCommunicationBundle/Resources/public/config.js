$(function () {
    var _checkForRedirect = function(data) {
        if (data.redirectRoute) {
            $(location).attr('href', Routing.generate(data.redirectRoute));
        }
        return false;
    };
    
    $('#serverConfig').on('submit', function (e) {
        e.preventDefault();
        
        var data = {
            ownUri:               $('#ownUri').val(),
            secureToken:          $('#secureToken').val(),
            secureTokenConfirm:   $('#secureTokenConfirm').val(),
            identifyToken:        $('#identifyToken').val(),
            identifyTokenConfirm: $('#identifyTokenConfirm').val()
        };
        
        if(data.ownUri && data.secureToken && data.secureTokenConfirm && data.identifyToken && data.identifyTokenConfirm) {
            $.ajax({
                url:         Routing.generate('server_api_put_config'),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                data:        JSON.stringify(data),
                method:      'put'
            }).done(function(data) {
                _checkForRedirect(data);
            }).fail(function(error) {
                UIkit.notify({
                    message : error.responseJSON.message,
                    status  : 'danger',
                    timeout : 5000,
                    pos     : 'top-center'
                });
            });
        } else {
            UIkit.notify({
                message : 'Please enter secure token and identify token and confirm both',
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        }
    });
})