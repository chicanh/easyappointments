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

namespace EA\Engine\Api\V2;
use \EA\Engine\Api\V1\Exception;

class DbHandlerException {
    public static function handle($dbError){
        if(strpos($dbError['message'], 'Duplicate')){
            throw new DbConflictException($dbError['message']);
        }
        throw new Exception($dbError['message'], 400);
    }
}
