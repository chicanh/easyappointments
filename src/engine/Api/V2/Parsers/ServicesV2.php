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
        $id_integrated = $response['id_integrated'];
        
        parent::encode($response);
        
        $response['id_integrated'] = $id_integrated;
    }

    /**
     * Decode Request
     *
     * @param array &$request The request to be decoded.
     * @param array $base Optional (null), if provided it will be used as a base array.
     */
    public function decode(array &$request, array $base = NULL)
    {
        if ( ! empty($request['id_integrated']))
        {
            $id_integrated = $request['id_integrated'];
        }
        parent::decode($request);
            
        if(isset($id_integrated)) {
        $request['id_integrated'] = $id_integrated;
        }
    }
}
