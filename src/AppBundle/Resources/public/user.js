$(function () {
    var _checkForRedirect = function(data) {
        if (data.redirectRoute) {
            $(location).attr('href', Routing.generate(data.redirectRoute));
        }
        return false;
    };
    
    $('#login').on('submit', function (e) {
        e.preventDefault();
        
        var data = {
            username: $('#loginUsername').val(),
            password: $('#loginPassword').val()
        };
        
        if(data.username && data.password) {
            $.ajax({
                url:         Routing.generate('user_api_post_login'),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                data:        JSON.stringify(data),
                method:      'post'
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
                message : 'Please enter password and username',
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        }
    });
    
    $('#register').on('submit', function (e) {
        e.preventDefault();
        
        var data = {
            username:        $('#registerUsername').val(),
            password:        $('#registerPassword').val(),
            confirmPassword: $('#registerConfirmPassword').val()
        };
        
        if(data.username && data.password && data.confirmPassword) {
            $.ajax({
                url:         Routing.generate('user_api_put_user'),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                data:        JSON.stringify(data),
                method:      'put',
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
                message : 'Please enter username and password and confirm your password',
                status  : 'danger',
                timeout : 5000,
                pos     : 'top-center'
            });
        }
    });
})