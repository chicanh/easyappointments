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

            $patient_integrated['id_user_integrated'] = $request->getBody()["id_user_integrated"];
            $patient_integrated['id_patients'] = $user_id;
            $patient_integrated['id_service_integrated'] = $request->getBody()["id_service_integrated"];
            $this->patient_model->add($patient_integrated);

            $patient['id'] = $user_id;
            $patient = $this->patient_model->get_aggregates($patient);
            $patient = $this->parser->customEncode($patient);
            $response = new Response($patient);
            $status = new NonEmptyText('201 Created');
            $response->output($status);
        
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function put($idPatients, $idUserIntegrated, $idServiceIntegrated){
        try
        {
            $request = new Request();
            $patient = $request->getBody();

            $this->parser->decode($patient);

            $this->customers_model_v2->update($idPatients, $patient);
           
            $patient_integrated['id_user_integrated'] = $idUserIntegrated;
            $patient_integrated['id_patients'] = $idPatients;
            $patient_integrated['id_service_integrated'] = $idServiceIntegrated;
            $this->patient_model->update($patient_integrated);
            $patient['id'] = $idPatients;
            $patient = $this->patient_model->get_aggregates($patient);
            $patient = $this->parser->customEncode($patient);
            $response = new Response($patient);
            $status = new NonEmptyText('200 Created');
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
        $page = $this->input->get('page');
        $size = $this->input->get('length');
        try {
            if($id_service_integrated == null){
                throw new \EA\Engine\Api\V1\Exception('id_service_integrated is required', 400);
            }
            $result = $this->patient_model->get($id_user_integrated, $id_service_integrated, $page, $size, array_key_exists('aggregates', $_GET));
            $result['patients'] = $this->encodePatients($result['patients']);
            $response = new Response($result);
            $response->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
        
    }

    public function getPatient($id_integrated) {
        try {
            $id_user_integrated = $this->input->get('id_user_integrated');
            $id_service_integrated = $this->input->get('id_service_integrated');
            if($id_service_integrated == null){
                throw new \EA\Engine\Api\V1\Exception('id_service_integrated are required', 400);
            }
            else {
		$patients = $this->patient_model->getPatient($id_user_integrated, $id_service_integrated, $id_integrated, array_key_exists('aggregates', $_GET));
		if(!empty($patients)) {
	                $response = new Response($patients);
			$response->encode($this->parser)->singleEntry(TRUE)->output();
		} else {
			$this->_throwRecordNotFound();				
		}
            }
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    private function encodePatients($patients){
        $encodedPatients = [];  
        foreach ($patients as &$value){
            array_push($encodedPatients,$this->parser->customEncode($value));
        }
        return $encodedPatients;
    }
}
