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

require_once __DIR__ . '/../v2/Customersv2.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Services Controller
 *
 * @package Controllers
 * @subpackage API
 */
class PatientsV3 extends Customersv2 {
    /**
     * Services Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Services
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v3/patient_model');
        $this->load->model('/v2/customers_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\CustomersV2;
    }

    public function post() {

        try
        {
            $request = new Request();
            $patient = $request->getBody();
            if (isset($patient['id_user_integrated']))
            {
                unset($patient['id_user_integrated']);
            }

            $this->parser->decode($patient);

            if (isset($patient['id']))
            {
                unset($patient['id']);
            }

            $user_id = $this->customers_model_v2->add($patient);
            $requestPatient = $request->getBody();
            $patient_integrated['id_user_integrated'] = $requestPatient["id_user_integrated"];
            $patient_integrated['id_patients'] = $user_id;
            $patient_integrated['id_service_integrated'] = $requestPatient["id_service_integrated"];
            $this->patient_model->add($patient_integrated);
            $response = new Response($requestPatient);
            $status = new NonEmptyText('201 Created');
            $response->output($status);
        
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
    /**
    * This is entry point: http://localhost/index.php/api/v3/patients
    */
    public function get() {
        $id_user_integrated = $this->input->get('id_user_integrated');
        $id_service_integrated = $this->input->get('id_service_integrated');
        try {
            if($id_user_integrated == null || $id_service_integrated == null){
                throw new \EA\Engine\Api\V1\Exception('id_user_integrated and id_service_integrated are required', 400);
            }
             $patients = $this->patient_model->get($id_user_integrated, $id_service_integrated);
             $response = new Response($patients);
             $response
                 ->search()
                 ->sort()
                 ->paginate()
                 ->minimize()
                 ->output();

        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
        
    }

    public function getPatient($id_integrated) {
        try {
            if($this->input->get('id_user_integrated') != null) {
                $patient = $this->patient_model->getPatient($this->input->get('id_user_integrated'), $id_integrated);
                $response = new Response($patient);
                $response->singleEntry(TRUE)->output();
            }
         else {
            throw new \EA\Engine\Api\V1\Exception('id_user_integrated is required', 400);
        }
    }
    catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
}
