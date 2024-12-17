/**
 * Copyright (c) 2024. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

require([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($, confirmation) {
    // Event listener for cache cleaning button
    $("body").on('click', '#smart-cache-clean', function (event) {
        event.preventDefault();

        const element = $(this);

        // Display confirmation modal
        confirmation({
            title: $.mage.__('Are you sure?'),
            content: $.mage.__('After refreshing the cache, the next page load will be slow.'),
            actions: {
                confirm: function () {
                    startProcess(element);
                },
                cancel: function () {
                    return false;
                }
            }
        });
    });

    /**
     * Starts the cache cleaning process with AJAX request.
     *
     * @param {jQuery} element
     */
    function startProcess(element) {
        $('body').trigger('processStart');

        $.ajax({
            url: element.attr("href"),
            type: 'GET'
        })
            .done(function (response) {
                handleResponse(response, element);
            })
            .always(function () {
                $('body').trigger('processStop');
            });
    }

    /**
     * Handles the response from the AJAX request.
     *
     * @param {Object} response
     * @param {jQuery} element
     */
    function handleResponse(response, element) {
        const responseText = JSON.parse(JSON.stringify(response));
        const parentWrapper = element.parent();

        parentWrapper.removeClass('message-warning message-error message-success');
        parentWrapper.html(responseText.message);

        if (responseText.error) {
            parentWrapper.addClass('message-error');
        } else {
            parentWrapper.addClass('message-success');
        }
    }
});
