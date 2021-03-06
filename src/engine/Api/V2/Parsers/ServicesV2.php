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
use \EA\Engine\Api\V1\Parsers\Services;

/**
 * Services Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 */
class ServicesV2 extends Services {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'name' => $response['name'],
            'duration' => (int)$response['duration'],
            'price' => (float)$response['price'],
            'currency' => $response['currency'],
            'description' => $response['description'],
            'availabilitiesType' => $response['availabilities_type'],
            'attendantsNumber' => (int)$response['attendants_number'],
            'id_integrated' => $response['id_integrated']
        ];

        if (array_key_exists('categories', $response))
        {
            $encodedResponse['categories'] = $response['categories'];
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

        if ( ! empty($request['name']))
        {
            $decodedRequest['name'] = $request['name'];
        }

        if ( ! empty($request['duration']))
        {
            $decodedRequest['duration'] = $request['duration'];
        }

        if ( ! empty($request['price']))
        {
            $decodedRequest['price'] = $request['price'];
        }

        if ( ! empty($request['currency']))
        {
            $decodedRequest['currency'] = $request['currency'];
        }

        if ( ! empty($request['description']))
        {
            $decodedRequest['description'] = $request['description'];
        }

        if ( ! empty($request['availabilitiesType']))
        {
            $decodedRequest['availabilities_type'] = $request['availabilitiesType'];
        }

        if ( ! empty($request['attendantsNumber']))
        {
            $decodedRequest['attendants_number'] = $request['attendantsNumber'];
        }

        if ( ! empty($request['id_integrated']))
        {
            $decodedRequest['id_integrated'] = $request['id_integrated'];
        }

        if ( ! empty($request['categories']))
        {
            $decodedRequest['categories'] = $request['categories'];
        }

        $request = $decodedRequest;
    }
}
