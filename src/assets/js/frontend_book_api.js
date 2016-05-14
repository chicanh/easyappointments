/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

window.FrontendBookApi = window.FrontendBookApi || {};

/**
 * Frontend Book API
 *
 * This module serves as the API consumer for the booking wizard of the app.
 *
 * @module FrontendBookApi
 */
(function(exports) {

    'use strict';

    /**
     * This function makes an ajax call and returns the available
     * hours for the selected service, provider and date.
     *
     * @param {string} selDate The selected date of which the available
     * hours we need to receive.
     */
    exports.getAvailableHours = function(selDate) {
        $('#available-hours').empty();

        // Find the selected service duration (it is going to be send within the "postData" object).
        var selServiceDuration = 15; // Default value of duration (in minutes).
        $.each(GlobalVariables.availableServices, function(index, service) {
            if (service['id'] == $('#select-service').val()) {
                selServiceDuration = service['duration'];
            }
        });

        // If the manage mode is true then the appointment's start
        // date should return as available too.
        var appointmentId = (FrontendBook.manageMode) ? GlobalVariables.appointmentData['id'] : undefined;

        // Make ajax post request and get the available hours.
        var postUrl = GlobalVariables.baseUrl + '/index.php/appointments/ajax_get_available_hours',
            postData = {
                csrfToken: GlobalVariables.csrfToken,
                service_id: $('#select-service').val(),
                provider_id: $('#select-provider').val(),
                selected_date: selDate,
                service_duration: selServiceDuration,
                manage_mode: FrontendBook.manageMode,
                appointment_id: appointmentId
            };

        $.post(postUrl, postData, function(response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            // The response contains the available hours for the selected provider and
            // service. Fill the available hours div with response data.
            if (response.length > 0) {
                var currColumn = 1;
                $('#available-hours').html('<div style="width:50px; float:left;"></div>');

                $.each(response, function(index, availableHour) {
                    if ((currColumn * 10) < (index + 1)) {
                        currColumn++;
                        $('#available-hours').append('<div style="width:50px; float:left;"></div>');
                    }

                    $('#available-hours div:eq(' + (currColumn - 1) + ')').append(
                            '<span class="available-hour">' + availableHour + '</span><br/>');
                });

                if (FrontendBook.manageMode) {
                    // Set the appointment's start time as the default selection.
                    $('.available-hour').removeClass('selected-hour');
                    $('.available-hour').filter(function() {
                        return $(this).text() === Date.parseExact(
                                GlobalVariables.appointmentData['start_datetime'],
                                'yyyy-MM-dd HH:mm:ss').toString('HH:mm');
                    }).addClass('selected-hour');
                } else {
                    // Set the first available hour as the default selection.
                    $('.available-hour:eq(0)').addClass('selected-hour');
                }

                FrontendBook.updateConfirmFrame();

            } else {
                $('#available-hours').text(EALang['no_available_hours']);
            }
        }, 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };

    /**
     * Register an appointment to the database.
     *
     * This method will make an ajax call to the appointments controller that will register
     * the appointment to the database.
     */
    exports.registerAppointment = function() {
        var $captchaText = $('.captcha-text');

        if ($captchaText.length > 0) {
            $captchaText.css('border', '');
            if ($captchaText.val() === '') {
                $captchaText.css('border', '1px solid #dc3b40');
                return;
            }
        }

        var formData = jQuery.parseJSON($('input[name="post_data"]').val());

        var postData = {
            'csrfToken': GlobalVariables.csrfToken,
            'post_data': formData
        };

        if ($captchaText.length > 0) {
            postData.captcha = $captchaText.val();
        }

        if (GlobalVariables.manageMode) {
            postData.exclude_appointment_id = GlobalVariables.appointmentData.id;
        }

        var postUrl = GlobalVariables.baseUrl + '/index.php/appointments/ajax_register_appointment',
            $layer = $('<div/>');

        $.ajax({
            url: postUrl,
            method: 'post',
            data: postData,
            dataType: 'json',
            beforeSend: function(jqxhr, settings) {
                $layer
                    .appendTo('body')
                    .css({
                        background: 'white',
                        position: 'fixed',
                        top: '0',
                        left: '0',
                        height: '100vh',
                        width: '100vw',
                        opacity: '0.5'
                    });
            }
        })
            .done(function(response) {
                if (!GeneralFunctions.handleAjaxExceptions(response)) {
                    $('.captcha-title small').trigger('click');
                    return false;
                }

                if (response.captcha_verification === false) {
                    $('#captcha-hint')
                        .text(EALang['captcha_is_wrong'] + '(' + response.expected_phrase + ')')
                        .fadeTo(400, 1);

                    setTimeout(function() {
                        $('#captcha-hint').fadeTo(400, 0);
                    }, 3000);

                    $('.captcha-title small').trigger('click');

                    $captchaText.css('border', '1px solid #dc3b40');

                    return false;
                }

                window.location.replace(GlobalVariables.baseUrl
                    + '/index.php/appointments/book_success/' + response.appointment_id);
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
                $('.captcha-title small').trigger('click');
                GeneralFunctions.ajaxFailureHandler(jqxhr, textStatus, errorThrown);
            })
            .always(function() {
                $layer.remove();
            });
    };

    /**
     * Get the unavailable dates of a provider.
     *
     * This method will fetch the unavailable dates of the selected provider and service and then it will
     * select the first available date (if any). It uses the "FrontendBookApi.getAvailableHours" method to fetch the appointment
     * hours of the selected date.
     *
     * @param {int} providerId The selected provider ID.
     * @param {int} serviceId The selected service ID.
     * @param {string} selectedDateString Y-m-d value of the selected date.
     */
    exports.getUnavailableDates = function(providerId, serviceId, selectedDateString) {
        var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_get_unavailable_dates',
            data = {
                provider_id: providerId,
                service_id: serviceId,
                selected_date: encodeURIComponent(selectedDateString),
                csrfToken: GlobalVariables.csrfToken
            };

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            dataType: 'json'
        })
            .done(function(response) {
                // Select first enabled date.
                var selectedDate = Date.parse(selectedDateString),
                    numberOfDays = new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 0).getDate();

                for (var i=1; i<=numberOfDays; i++) {
                    var currentDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), i);
                    if ($.inArray(currentDate.toString('yyyy-MM-dd'), response) === -1) {
                        $('#select-date').datepicker('setDate', currentDate);
                        FrontendBookApi.getAvailableHours(currentDate.toString('yyyy-MM-dd'));
                        break;
                    }
                }

                // If all the days are unavailable then hide the appointments hours.
                if (response.length === numberOfDays) {
                    $('#available-hours').text(EALang['no_available_hours']);
                }

                // Apply the new beforeShowDayHandler method to the #select-date datepicker.
                var beforeShowDayHandler = function(date) {
                    if ($.inArray(date.toString('yyyy-MM-dd'), response) != -1) {
                        return [false];
                    }
                    return [true];
                };

                $('#select-date').datepicker('option', 'beforeShowDay', beforeShowDayHandler);
            })
            .fail(GeneralFunctions.ajaxFailureHandler);
    };

})(window.FrontendBookApi);