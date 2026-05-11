(function ($) {
    'use strict';

    function initChat(chat) {
        var activeConversation = parseInt(chat.data('active-conversation'), 10) || 0;
        var lastMessageId = 0;
        var pollTimer = null;

        function appendMessages(messages) {
            var box = chat.find('[data-wcs-messages]');

            messages.forEach(function (message) {
                lastMessageId = Math.max(lastMessageId, message.id);

                var side = message.is_admin ? 'admin' : 'user';
                var bubble = $('<div/>', { class: 'wcs-message wcs-message--' + side, 'data-message-id': message.id });

                bubble.append($('<div/>', { class: 'wcs-message__bubble', html: message.message }));
                bubble.append($('<time/>', { text: message.created_at }));
                box.append(bubble);
            });

            if (box.length) {
                box.scrollTop(box[0].scrollHeight);
            }
        }

        function loadMessages(reset) {
            if (!activeConversation) {
                return;
            }

            if (reset) {
                lastMessageId = 0;
                chat.find('[data-wcs-messages]').empty();
            }

            $.post(wcsPublic.ajaxUrl, {
                action: 'wcs_load_messages',
                nonce: wcsPublic.nonce,
                conversation_id: activeConversation,
                after_id: lastMessageId
            }).done(function (response) {
                if (response.success) {
                    appendMessages(response.data.messages);
                }
            });
        }

        function startPolling() {
            clearInterval(pollTimer);
            loadMessages(true);
            pollTimer = setInterval(function () {
                loadMessages(false);
            }, wcsPublic.pollInterval);
        }

        chat.find('[data-wcs-new-conversation]').on('click', function () {
            chat.find('.wcs-chat-box').prop('hidden', true);
            chat.find('.wcs-new-conversation').prop('hidden', false);
            chat.find('.wcs-conversation-item').removeClass('is-active');
            activeConversation = 0;
            clearInterval(pollTimer);
        });

        chat.find('[data-wcs-create-conversation]').on('click', function () {
            var subject = chat.find('[data-wcs-subject]').val().trim();
            var message = chat.find('[data-wcs-first-message]').val().trim();

            if (!subject || !message) {
                alert(!subject ? wcsPublic.i18n.emptySubject : wcsPublic.i18n.emptyMessage);
                return;
            }

            $.post(wcsPublic.ajaxUrl, {
                action: 'wcs_create_conversation',
                nonce: wcsPublic.nonce,
                subject: subject,
                message: message
            }).done(function (response) {
                if (response.success) {
                    activeConversation = parseInt(response.data.conversation_id, 10);
                    lastMessageId = 0;

                    var item = $('<button/>', {
                        type: 'button',
                        class: 'wcs-conversation-item is-active',
                        'data-conversation-id': activeConversation
                    });

                    item.append($('<strong/>', { text: response.data.subject }));
                    item.append($('<span/>', { text: response.data.updated_at }));
                    chat.find('.wcs-conversation-item').removeClass('is-active');
                    chat.find('.wcs-empty').remove();
                    chat.find('.wcs-conversation-list').prepend(item);
                    chat.find('.wcs-new-conversation').prop('hidden', true);
                    chat.find('.wcs-chat-box').prop('hidden', false);
                    chat.find('[data-wcs-subject], [data-wcs-first-message]').val('');
                    startPolling();
                }
            });
        });

        chat.find('.wcs-conversation-list').on('click', '.wcs-conversation-item', function () {
            activeConversation = parseInt($(this).data('conversation-id'), 10);
            chat.attr('data-active-conversation', activeConversation);
            chat.find('.wcs-conversation-item').removeClass('is-active');
            $(this).addClass('is-active');
            chat.find('.wcs-new-conversation').prop('hidden', true);
            chat.find('.wcs-chat-box').prop('hidden', false);
            startPolling();
        });

        chat.find('[data-wcs-reply-form]').on('submit', function (event) {
            event.preventDefault();

            var textarea = chat.find('[data-wcs-message]');
            var message = textarea.val().trim();

            if (!message) {
                alert(wcsPublic.i18n.emptyMessage);
                return;
            }

            $.post(wcsPublic.ajaxUrl, {
                action: 'wcs_send_message',
                nonce: wcsPublic.nonce,
                conversation_id: activeConversation,
                message: message
            }).done(function (response) {
                if (response.success) {
                    textarea.val('');
                    loadMessages(false);
                }
            });
        });

        if (activeConversation) {
            startPolling();
        }
    }

    $('[data-wcs-floating]').each(function () {

        var widget = $(this);
        var toggle = widget.find('[data-wcs-floating-toggle]');
        var panel = widget.find('[data-wcs-floating-panel]');

        function setOpen(open) {
            panel.prop('hidden', !open);
            toggle.attr('aria-expanded', open ? 'true' : 'false');
            widget.toggleClass('is-open', open);
        }

        toggle.on('click', function () {
            setOpen(panel.prop('hidden'));
        });

        widget.find('[data-wcs-floating-close]').on('click', function () {
            setOpen(false);
        });
    });

    $('.wcs-chat').each(function () {
        initChat($(this));
    });
})(jQuery);
jQuery(function ($) {

    function toggleChatWidget() {

        var chatWidget = $('[data-wcs-floating]');
        var miniCartDrawer = $('.wc-block-components-drawer');

        if (!chatWidget.length || !miniCartDrawer.length) {
            return;
        }

        // Cart open
        if (miniCartDrawer.attr('aria-hidden') === 'false') {
            chatWidget.hide();
        } else {
            chatWidget.show();
        }
    }

    // First run
    toggleChatWidget();

    // Observe attribute changes
    const target = document.querySelector('.wc-block-components-drawer');

    if (target) {

        const observer = new MutationObserver(function () {
            toggleChatWidget();
        });

        observer.observe(target, {
            attributes: true,
            attributeFilter: ['aria-hidden']
        });
    }
});