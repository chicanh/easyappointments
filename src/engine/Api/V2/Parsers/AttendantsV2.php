<?php

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      Davido Team
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\ParsersInterface;

/**
 * Attendants Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 */
class AttendantsV2 implements ParsersInterface {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'appointmentId' => $response['id_appointment'] !== NULL ? (int)$response['id_appointment'] : NULL
        ];

        if (array_key_exists('id_users', $response))
        {
            $encodedResponse['idUsers'] = $response['id_users'];
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

        if ( ! empty($request['appointmentId']))
        {
            $decodedRequest['id_appointment'] = $request['appointmentId'];
        }

        if ( ! empty($request['idUsers']))
        {
            $decodedRequest['id_users'] = $request['idUsers'];
        }

        $request = $decodedRequest;
    }
}
