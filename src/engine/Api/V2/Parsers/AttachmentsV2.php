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
 * Attachments Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 */
class AttachmentsV2 implements ParsersInterface {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        if(!isset($response) || empty($response)) {
            return null; 
        }
        
        $json = array();

        foreach ($response as $attachment)
        {
            if(isset($attachment['name'])) 
            {
                $json[$attachment['name']] = $attachment['value'];
            }
        }

        return $json;
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

        if ( ! empty($request['name']))
        {
            $decodedRequest['name'] = $request['name'];
        }

        if ( ! empty($request['value']))
        {
            $decodedRequest['value'] = $request['value'];
        }

        $request = $decodedRequest;
    }
}
