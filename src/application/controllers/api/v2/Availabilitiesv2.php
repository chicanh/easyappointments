<?php defined('BASEPATH') OR exit('No direct script access allowed');

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

require_once __DIR__ . '/../v1/Availabilities.php';
require_once __DIR__ . '/../../Appointments.php';

use \EA\Engine\Types\UnsignedInteger;

/**
 * Availabilities Controller
 *
 * @package Controllers
 * @subpackage API
 */
class Availabilitiesv2 extends Availabilities {
    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('appointments_model');
        $this->load->model('/v2/providers_model_v2');
        $this->load->model('/v2/services_model_v2');
        $this->load->model('settings_model');
    }

    /**
     * GET API Method
     *
     * Provide the "providerId", "serviceId" and "date" GET parameters to get the availabilities for a specific date.
     * If no "date" was provided then the current date will be used.
     */
    public function get()
    {
        try
        {
            $provider_model = $this->providers_model_v2;
            $service_model = $this->services_model_v2;
            $id_provider_integrated = $this->input->get('id_provider_integrated');
            $id_service_integrated =  $this->input->get('id_service_integrated');

            if ($this->input->get('date'))
            {
                $date = new DateTime($this->input->get('date'));
            }
            else
            {
                $date = new DateTime();
            }

            $provider = $provider_model->get_row($id_provider_integrated);
            $service = $service_model->get_row($id_service_integrated);
            $providerId =  $provider['id'];
            $serviceId = $service['id'];
            $emptyPeriods = $this->_getProviderAvailableTimePeriods($providerId,
                $date->format('Y-m-d'), []);

            $availableHours = $this->_calculateAvailableHours($emptyPeriods,
                $date->format('Y-m-d'), $service['duration'], FALSE, $service['availabilities_type']);

            if ($service['attendants_number'] > 1)
            {
                $availableHours = $this->_getMultipleAttendantsHours($date->format('Y-m-d'), $service, $provider);
            }

            // If the selected date is today, remove past hours. It is important  include the timeout before
            // booking that is set in the back-office the system. Normally we might want the customer to book
            // an appointment that is at least half or one hour from now. The setting is stored in minutes.
            if ($date->format('Y-m-d') === date('Y-m-d'))
            {
                $bookAdvanceTimeout = $this->settings_model->get_setting('book_advance_timeout');

                foreach ($availableHours as $index => $value)
                {
                    $availableHour = strtotime($value);
                    $currentHour = strtotime('+' . $bookAdvanceTimeout . ' minutes', strtotime('now'));
                    if ($availableHour <= $currentHour)
                    {
                        unset($availableHours[$index]);
                    }
                }
            }

            $availableHours = array_values($availableHours);
            sort($availableHours, SORT_STRING);
            $availableHours = array_values($availableHours);
            $duration = $this->services_model->get_value('duration',$serviceId);
            print_r($duration);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($availableHours));
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

}