(function ($) {
    'use strict';

    var conversationId = parseInt(wcsAdmin.conversationId, 10) || 0;
    var lastMessageId = 0;
    var pollTimer = null;

    if (!conversationId) {
        return;
    }

    function appendMessages(messages) {
        var box = $('[data-wcs-admin-messages]');

        messages.forEach(function (message) {
            lastMessageId = Math.max(lastMessageId, message.id);
            var side = message.is_admin ? 'admin' : 'user';
            var bubble = $('<div/>', { class: 'wcs-message wcs-message--' + side, 'data-message-id': message.id });
            bubble.append($('<div/>', { class: 'wcs-message__bubble', html: message.message }));
            bubble.append($('<time/>', { text: message.created_at }));
            box.append(bubble);
        });

        box.scrollTop(box[0].scrollHeight);
    }

    function loadMessages(reset) {
        if (reset) {
            lastMessageId = 0;
            $('[data-wcs-admin-messages]').empty();
        }

        $.post(wcsAdmin.ajaxUrl, {
            action: 'wcs_admin_load_messages',
            nonce: wcsAdmin.nonce,
            conversation_id: conversationId,
            after_id: lastMessageId
        }).done(function (response) {
            if (response.success) {
                appendMessages(response.data.messages);
            }
        });
    }

    $('[data-wcs-admin-reply-form]').on('submit', function (event) {
        event.preventDefault();

        var textarea = $('[data-wcs-admin-message]');
        var message = textarea.val().trim();

        if (!message) {
            alert(wcsAdmin.i18n.emptyMessage);
            return;
        }

        $.post(wcsAdmin.ajaxUrl, {
            action: 'wcs_admin_send_message',
            nonce: wcsAdmin.nonce,
            conversation_id: conversationId,
            message: message
        }).done(function (response) {
            if (response.success) {
                textarea.val('');
                loadMessages(false);
            }
        });
    });

    loadMessages(true);
    pollTimer = setInterval(function () {
        loadMessages(false);
    }, wcsAdmin.pollInterval);
})(jQuery);
