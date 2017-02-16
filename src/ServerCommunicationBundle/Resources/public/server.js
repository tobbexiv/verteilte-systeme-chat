$(function () {
    var $secureToken = $('#secureToken');
    var $serverWrapper = $('#serverWrapper');
    var $newServerInputs = $('#newServer input');
    
    var $serverTemplate = '<li id="server%id%"><span>%uri%</span><a class="uk-close uk-float-right" href="#"></a></li>';
    
    var _checkForRedirect = function(data) {
        if (data.redirectRoute) {
            $(location).attr('href', Routing.generate(data.redirectRoute));
        }
        return false;
    };
    
    var _clickDeleteServer = function (e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var data = {
            secureToken: $secureToken.val()
        };
        
        $.ajax({
            url:         Routing.generate('server_api_delete_server', { id: id }),
            contentType: 'application/json; charset=utf-8',
            dataType:    'json',
            data:        JSON.stringify(data),
            method:      'delete'
        }).done(function(data) {
            $('#server' + id).remove();
        }).fail(function(error) {
            UIkit.notify({
                message : error.responseJSON.message,
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        });
    };
    
    var _addNewServer = function(data) {
        if(data.id) {
            var server = $serverTemplate.replace(/%id%/gi, data.id)
                                    .replace(/%uri%/gi, data.uri);
            $(server).appendTo($serverWrapper)
                     .find('a.uk-close')
                         .data('id', data.id)
                         .on('click', _clickDeleteServer);
        }
    };
    
    var _toggleNewServerInputsStatus = function () {
        $newServerInputs.prop('disabled', function(input, value) { return !value; });
    };
    
    $('#refreshList').on('click', function (e) {
        e.preventDefault();
        
        _toggleNewServerInputsStatus();
        
        var data = {
            secureToken: $secureToken.val()
        };
        
        $.ajax({
            url:         Routing.generate('server_api_post_servers'),
            contentType: 'application/json; charset=utf-8',
            dataType:    'json',
            data:        JSON.stringify(data),
            method:      'post',
            processData: false
        }).done(function(data) {
            $serverWrapper.empty();
            $.each(data, function(index, server) {
                _addNewServer(server);
            })
        }).fail(function(error) {
            UIkit.notify({
                message : error.responseJSON.message,
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        }).always(function() {
            _toggleNewServerInputsStatus();
        });
    });
    
    $('#newServer').on('submit', function (e) {
        e.preventDefault();
        
        _toggleNewServerInputsStatus();
        
        var data = {
            secureToken:        $secureToken.val(),
            uri:                $('#uri').val(),
            serverToken:        $('#serverToken').val(),
            serverTokenConfirm: $('#serverTokenConfirm').val()
        };
        
        if(data.uri && data.serverToken && data.serverTokenConfirm) {
            $.ajax({
                url:         Routing.generate('server_api_put_server'),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                data:        JSON.stringify(data),
                method:      'put'
            }).done(function(data) {
                _addNewServer(data);
                $newServerInputs.val('');
            }).fail(function(error) {
                UIkit.notify({
                    message : error.responseJSON.message,
                    status  : 'danger',
                    timeout : 5000,
                    pos     : 'top-center'
                });
            }).always(function() {
                _toggleNewServerInputsStatus();
            });
        } else {
            UIkit.notify({
                message : 'Please enter uri and server token and confirm the server token',
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        }
    });
})