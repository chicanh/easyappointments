<?php

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Providers;
use \EA\Engine\Api\V1\Parsers\Customers;
use \EA\Engine\Api\V1\Parsers\Services;
use \EA\Engine\Api\V1\Parsers\Appointments;

/**
 * Appointments Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 */
class AppointmentsV2 extends Appointments {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'book' => $response['book_datetime'],
            'start' => $response['start_datetime'],
            'end' => $response['end_datetime'],
            'hash' => $response['hash'],
            'notes' => $response['notes'],
            'customerId' => $response['id_users_customer'] !== NULL ? (int)$response['id_users_customer'] : NULL,
            'providerId' => $response['id_users_provider'] !== NULL ? (int)$response['id_users_provider'] : NULL,
            'serviceId' => $response['id_services'] !== NULL ? (int)$response['id_services'] : NULL,
            'googleCalendarId' => $response['id_google_calendar'] !== NULL ? (int)$response['id_google_calendar'] : NULL,
            'status' => $response['status'],
            'id_integrated' => $response['id_integrated'],
            'cancelReason' => $response['cancel_reason']
        ];

        if (isset($response['provider']))
        {
            $providerParser = new Providers();
            $providerParser->encode($response['provider']);
            $encodedResponse['provider'] = $response['provider'];
        }

        if (isset($response['customer']))
        {
            $customerParser = new Customers();
            $customerParser->encode($response['customer']);
            $encodedResponse['customer'] = $response['customer'];
        }

        if (isset($response['service']))
        {
            $serviceParser = new Services();
            $serviceParser->encode($response['service']);
            $encodedResponse['service'] = $response['service'];
        }
        if (isset($response['patient']))
        {
            $customerParser = new Customers();
            $customerParser->encode($response['patient']);
            $encodedResponse['patient'] = $response['patient'];
        }

        $response = $encodedResponse;
    }

    /**
     * Decode Request
     *
     * @param array &$request The request to be decoded.
     * @param array $base Optional (null), if provided it will be used as a base array.
     */
    public function decode(array &$request, array $base = NULL)
    {
        $decodedRequest = $base ?: [];

        if ( ! empty($request['id']))
        {
            $decodedRequest['id'] = $request['id'];
        }

        if ( ! empty($request['book']))
        {
            $decodedRequest['book_datetime'] = $request['book'];
        }

        if ( ! empty($request['start']))
        {
            $decodedRequest['start_datetime'] = $request['start'];
        }

        if ( ! empty($request['end']))
        {
            $decodedRequest['end_datetime'] = $request['end'];
        }

        if ( ! empty($request['hash']))
        {
            $decodedRequest['hash'] = $request['hash'];
        }

        if ( ! empty($request['notes']))
        {
            $decodedRequest['notes'] = $request['notes'];
        }

        if ( ! empty($request['customerId']))
        {
            $decodedRequest['id_users_customer'] = $request['customerId'];
        }

        if ( ! empty($request['providerId']))
        {
            $decodedRequest['id_users_provider'] = $request['providerId'];
        }

        if ( ! empty($request['serviceId']))
        {
            $decodedRequest['id_services'] = $request['serviceId'];
        }

        if ( ! empty($request['googleCalendarId']))
        {
            $decodedRequest['id_google_calendar'] = $request['googleCalendarId'];
        }

        if ( ! empty($request['status']))
        {
            $decodedRequest['status'] = $request['status'];
        }

        if ( ! empty($request['id_integrated']))
        {
            $decodedRequest['id_integrated'] = $request['id_integrated'];
        }

        if ( ! empty($request['cancelReason']))
        {
            $decodedRequest['cancel_reason'] = $request['cancelReason'];
        }

        if ( ! empty($request['attachment']))
        {
            $decodedRequest['attachment'] = $request['attachment'];
        }

        if ( ! empty($request['attendants']))
        {
            $decodedRequest['attendants'] = $request['attendants'];
        }

        $decodedRequest['is_unavailable'] = FALSE;

        $request = $decodedRequest;
    }
}
