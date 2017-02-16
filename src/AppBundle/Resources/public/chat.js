$(function () {
    var $chatText = $('#chatText');
    var $chatAction = $('#chatAction');
    var $chatWrapper = $('#chatWrapper');
    var $lastLoadedId = 0;
    var $displayedMessages = new Map();
    
    var $messageTemplate = '<div id="msg%id%" class="uk-comment uk-width-1-1 uk-margin-bottom"><div class="uk-comment-header"><span class="uk-text-bold">%user%</span> <span class="uk-text-muted">%sent%</span><div class="uk-comment-body uk-text-break">%text%</div></div></div>';
    
    var _checkForRedirect = function(data) {
        if (data.redirectRoute) {
            $(location).attr('href', Routing.generate(data.redirectRoute));
        }
        return false;
    };
    
    var _createNewMessageNode = function (data) {
        if(!$displayedMessages.has(data.id)) {
            var sent = new Date(data.sent);
            var sentTimeInMillis = sent.getTime();
            var newerMessage = {
                id: 0,
                sentTime: 0
            };
            
            for(var [id, sentTime] of $displayedMessages) {
                if ((sentTime < newerMessage.sentTime || newerMessage.sentTime === 0) && sentTimeInMillis < sentTime) {
                    newerMessage.id = id;
                    newerMessage.sentTime = sentTime;
                }
            }
            
            var message = $messageTemplate.replace(/%id%/gi, data.id)
                                          .replace(/%user%/gi, data.username)
                                          .replace(/%sent%/gi, sent.toLocaleString())
                                          .replace(/%text%/gi, data.text);
            
            if(newerMessage.id) {
                $(message).insertBefore('#msg' + newerMessage.id);
            } else {
                $(message).appendTo($chatWrapper);
            }
            
            if(data.id > $lastLoadedId) {
                $lastLoadedId = data.id;
            }
            
            
            $displayedMessages.set(data.id, sentTimeInMillis);
            $chatWrapper.scrollTop($chatWrapper.prop('scrollHeight') - $chatWrapper.prop('clientHeight'));
        }
    };
    
    var _toggleChatTextStatus = function () {
        $chatText.prop('disabled', function(input, value) { return !value; });
        $chatAction.toggleClass('uk-icon-spinner uk-icon-spin uk-icon-pencil');
    };
    
    $('#chatInput').on('submit', function (e) {
        e.preventDefault();
        
        var text = $chatText.val();
        
        if(text) {
            _toggleChatTextStatus();
            
            $.ajax({
                url:         Routing.generate('chat_api_post_message'),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                data:        JSON.stringify({ text: text }),
                method:      'post'
            }).done(function(data) {
                _checkForRedirect(data);
                _createNewMessageNode(data);
                $chatText.val('');
            }).fail(function(error) {
                UIkit.notify({
                    message : error.responseJSON.message,
                    status  : 'danger',
                    timeout : 5000,
                    pos     : 'top-center'
                });
            }).always(function() {
                _toggleChatTextStatus();
            });
        }
    });
    
    var _setCheckForNew = function() {
        setTimeout(function () {
            $.ajax({
                url:         Routing.generate('chat_api_get_messages_from_id', { id: $lastLoadedId }),
                contentType: 'application/json; charset=utf-8',
                dataType:    'json',
                method:      'get'
            }).done(function(data) {
                _checkForRedirect(data);
                $(data).each(function (index, message) {
                    _createNewMessageNode(message);
                });
            }).always(function() {
                _setCheckForNew();
            });
        }, 1000);
    }
    
    $.ajax({
        url:         Routing.generate('chat_api_get_messages'),
        contentType: 'application/json; charset=utf-8',
        dataType:    'json',
        method:      'get'
    }).done(function(data) {
        _checkForRedirect(data);
        $(data).each(function (index, message) {
            _createNewMessageNode(message);
        });
        
        _setCheckForNew();
    });
    
    $chatText.focus();
})