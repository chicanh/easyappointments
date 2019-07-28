<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @updater     Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/../v2/Appointmentsv2.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Appointments Controller v3
 *
 * @package Controllers
 * @subpackage API
 */
class AppointmentsV3 extends AppointmentsV2 {

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v3/appointments_model_v3');
        $this->parser = new \EA\Engine\Api\V2\Parsers\AppointmentsV2;
        $this->attachments_parser = new \EA\Engine\Api\V2\Parsers\AttachmentsV2;
    }
    
    public function get() {
        
        try {
            $idUserIntegrated = $this->input->get('id_user_integrated');
            $idPatientIntegrated = $this->input->get('id_patient_integrated');
            $idServiceIntegrated = $this->input->get('id_service_integrated');
            $page = $this->input->get('page');
            $size = $this->input->get('length');
            $sort = $this->input->get('sort');
            $startDate = $this->input->get('startDate');
            $endDate = $this->input->get('endDate');

            if($idUserIntegrated == null || $idServiceIntegrated == null){
                throw new \EA\Engine\Api\V1\Exception('id_user_integrated & id_service_integrated are  required', 400);
            }
          
            
            $appointments =  $this->appointments_model_v3->getAppointmentWithUserIdAndServiceIdAndPatientId($idUserIntegrated, 
                                                                                                            $idServiceIntegrated, 
                                                                                                            $idPatientIntegrated,
                                                                                                            $startDate,
                                                                                                            $endDate,
                                                                                                            $page,
                                                                                                            $size,
                                                                                                            $sort);
            $appointments = $this->encodedAppointments($appointments);
            $response = new Response($appointments);
            $response->search()
                     ->sort()
                    ->paginate()
                    ->minimize()
                    ->output();
           
            
        } catch (\Exception $exception) {
                    exit($this->_handleException($exception));
        }
    }

    public function getAppointmentWithServiceIdAndPatientId($idServiceIntegrated, $idPatientIntegrated) {

        try {
            $page = $this->input->get('page');
            $size = $this->input->get('length');
            $sort = $this->input->get('sort');
            $startDate = $this->input->get('startDate');
            $endDate = $this->input->get('endDate');

            
            // $idUserIntegrated='65a4a7dc-bedd-4d42-8a8f-46b5283f482c';
            // $idServiceIntegrated='81cbf841-1929-4c81-92c3-9ebc343a7282';
            // $idPatientIntegrated='6eb8cde6-9237-47b8-bd27-e0b3b4d6bffb';
            // $page='1';
            // $size='10';
            // $sort='DESC';
            // $startDate = '2017-01-01';
            // $endDate = '2019-01-01';

            $resultSet =  $this->appointments_model_v3->getAppointmentWithServiceIdAndPatientId($idServiceIntegrated, 
                                                                                                $idPatientIntegrated,
                                                                                                $startDate,
                                                                                                $endDate,
                                                                                                $page,
                                                                                                $size, 
                                                                                                $sort);
            
            $resultSet['appointments'] = $this->encodedAppointments($resultSet['appointments']);
            $response = new Response($resultSet);
            $response->output();
           
            
        } catch (\Exception $exception) {
                    exit($this->_handleException($exception));
        }
    }
    public function getUserAppointments($id_integrated) {
        try {
            if($this->input->get('id_user_integrated') != null) {
                $appointments = $this->appointments_model_v3->getUserAppointments($id_integrated, $this->input->get('id_user_integrated'));
                $response = new Response($appointments);
                $response
                    ->search()
                    ->sort()
                    ->paginate()
                    ->minimize()
                    ->output();
            }
            else {
                throw new \EA\Engine\Api\V1\Exception('id_user_integrated is required', 400);
            }
            
        } catch (\Exception $exception) {
                    exit($this->_handleException($exception));
        }
    }

    private function encodedAppointments($appointments){
        $encodedAppointments = [];  
        foreach ($appointments as &$value){
            array_push($encodedAppointments,$this->parser->customEncode($value));
        }
        return $encodedAppointments;
    }

    
}
