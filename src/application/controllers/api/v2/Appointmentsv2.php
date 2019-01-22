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

require_once __DIR__ . '/../v1/Appointments.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Appointments Controller v2
 *
 * @package Controllers
 * @subpackage API
 */
class AppointmentsV2 extends Appointments {
    /**
     * Appointments Resource Parser
     *
     * @var \EA\Engine\Api\V2\Parsers\AppointmentsV2
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('services_model');
        $this->load->model('/v2/appointments_model_v2');
        $this->load->model('/v2/user_model_v2');
        $this->load->model('/v2/attachments_model_v2');
        $this->load->model('/v2/attendants_model_v2');
        $this->load->model('/v2/services_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\AppointmentsV2;
    }

    /**
     * GET API Method
     *
     * @param int $id_integrated Optional (null), the record ID to be returned.
     */
    
    public function get($id_integrated = null) {
        try {

        
        $conditions = [
            'is_unavailable' => FALSE
        ];

        if ($id_integrated !== NULL) {
            $conditions['id_integrated'] = $id_integrated;
        }
        
        $appointments = $this->appointments_model_v2->get_batch($conditions, array_key_exists('aggregates', $_GET));
        if($this->input->get('id_user_integrated')) {
            $appointments = $this->getAppointmentByUserId($conditions, $this->input->get('id_user_integrated'));
        } 
        else {

            if($this->input->get('id_provider_integrated') !=null && $this->input->get('id_service_integrated') !=null) {
                $appointments = $this->getAppointmentByProviderIdAndServiceId($conditions, $this->input->get('id_provider_integrated'), $this->input->get('id_service_integrated'));
            } else {
                
                if($this->input->get('id_provider_integrated') !=null) {
                    $appointments = $this->getAppointmentByProviderId($conditions, $this->input->get('id_provider_integrated'));
                }
        
                if($this->input->get('id_service_integrated') !=null) {
                    $appointments = $this->getAppointmentByServiceId($conditions, $this->input->get('id_service_integrated')); 
                    if($this->input->get('startDate')!=null && $this->input->get('endDate') !=null) {
                        $appointments = $this->getAllAppointmentByPeriodTime($this->input->get('startDate'), $this->input->get('endDate'), $this->input->get('id_service_integrated'));
                    }
                }
            }
        }

        if ($id_integrated !== NULL && count($appointments) === 0)
        {
                $this->_throwRecordNotFound();
        }
 
        $result = array();
        if (count($appointments) > 0) {
            foreach ($appointments as $appointment) {
                $attachments = $this->attachments_model_v2->get_batch($appointment['id']);
                 if (count($attachments) > 0) {
                     $appointment['attachment'] =  $attachments;
                 }
                array_push($result, $appointment);
            }
        }
            $response = new Response($result);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id_integrated)
                ->output();

     } catch (\Exception $exception) {
                exit($this->_handleException($exception));
     }
    }

    /**
     * POST API Method
     */
    public function post()
    {
        try
        {
            // Insert the appointment to the database. 
            $request = new Request();
            $appointment = $request->getBody();
            $this->parser->decode($appointment);

            if (isset($appointment['id']))
            {
                unset($appointment['id']);
            }

            $exist = $this->appointments_model_v2->find_by_id_integrated($appointment['id_integrated']);
            if (isset($exist)) {
                throw new \EA\Engine\Api\V1\Exception('$exist existed in DB: ' . $exist, 409, 'Duplicated');
            }

            $id = $this->appointments_model_v2->add($appointment);

            // add attachment
            if ( ! empty($appointment['attachment'])) {
                $this->attachments_model_v2->save_attachments($id, $appointment['attachment']);
            }

            // add attendants
            if ( ! empty($appointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $appointment['attendants']);
            }

            // Fetch the new object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $status = new NonEmptyText('201 Created');
            $response->encode($this->parser)->singleEntry(TRUE)->output($status);
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    /**
     * PUT API Method
     *
     * @param int $id The record ID to be updated.
     */
    public function put($id)
    {
        try
        {
            // Update the appointment record. 
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }
            $request = new Request();
            $updatedAppointment = $request->getBody();
            $baseAppointment = $batch[0];
            $this->parser->decode($updatedAppointment, $baseAppointment);
            $updatedAppointment['id'] = $id;
            $id = $this->appointments_model_v2->add($updatedAppointment);
            // Update the appointment attachments
            if ( ! empty($updatedAppointment['attachment'])) {
                $this->appointments_model_v2->save_attachments($id, $updatedAppointment['attachment']);
            }
            // Update the appointment attendants
            if ( ! empty($updatedAppointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $updatedAppointment['attendants']);
            }
            // Fetch the updated object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function updateAppointmentByIdIntegrated($id_integrated){
        try
        {
            // Update the appointment record. 
            $batch = $this->appointments_model_v2->get_batch("id_integrated = '" . $id_integrated ."'");

            $id = $this->appointments_model_v2->find_id_by_id_integrated($id_integrated);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedAppointment = $request->getBody();
            $baseAppointment = $batch[0];
            $this->parser->decode($updatedAppointment, $baseAppointment);
            $updatedAppointment['id'] = $id;
            $id = $this->appointments_model_v2->add($updatedAppointment);

            // Update the appointment attachments
            if ( ! empty($updatedAppointment['attachment'])) {
                $this->appointments_model_v2->save_attachments($id, $updatedAppointment['attachment']);
            }

            // Update the appointment attendants
            if ( ! empty($updatedAppointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $updatedAppointment['attendants']);
            }

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    /**
     * DELETE API Method
     *
     * @param int $id The record ID to be deleted.
     */
    public function delete($id)
    {
        try
        {
            $this->appointments_model_v2->delete($id);
            $this->attachments_model_v2->remove_attachment($id);
            $this->attendants_model_v2->remove_attendants($id);

            $response = new Response([
                'code' => 200,
                'message' => 'Record was deleted successfully!'
            ]);

            $response->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function updateAppointmentStatus($id_integrated) {
        try
        {
            if($id_integrated !== null && $_GET['status'] !==null)
            {
                $batch = $this->appointments_model_v2->get_batch("id_integrated = '" . $id_integrated ."'");
                $id = $this->appointments_model_v2->find_id_by_id_integrated($id_integrated);
                $this->appointments_model_v2->updateAppointmentStatus($id, $_GET['status']);
                $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
                $response = new Response($batch);
                $status = new NonEmptyText('200 OK');
                $response->encode($this->parser)->singleEntry(TRUE)->output($status);
            } 
            else 
            {
                $this->_throwRecordNotFound();
            }
        } 
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    /**
     * Default method, use to get list of record by id , startdate & enddate
     * jira ticket : https://davidodev.atlassian.net/browse/EAI-28
     */
    private function getAllAppointmentByPeriodTime($startDate, $endDate, $id_integrated){
        $page = $this->input->get('page');
        $size = $this->input->get('size');
 
        $service = $this->services_model_v2->find_by_id_integrated($id_integrated);
        if(count($service) == 0){
            http_response_code(404);
            print('Could not found services with id: '.$id_integrated);
            return;
        }
        
        $appointments = $this->appointments_model_v2->getAllAppointmentBy($service, array_key_exists('aggregates', $_GET), $startDate, $endDate, $page, $size);
        $response = new Response($appointments);
        $response->encode($this->parser)
            ->output();
    }

    private function getAppointmentByProviderId($conditions, $id_provider_integrated) {
        $provider = $this->user_model_v2->find_by_id_integrated($id_provider_integrated);
        if (isset($provider)) {
          $appointments = $this->appointments_model_v2->get_batch($conditions, array_key_exists('aggregates', $_GET), $provider['id'], NULL, $this->appointments_model_v2::PROVIDER);
        }
        return $appointments;
    }

    private function getAppointmentByServiceId($conditions, $id_service_integrated) {
        $service = $this->services_model_v2->find_by_id_integrated($id_service_integrated);
        if (isset($service)) {
           $appointments = $this->appointments_model_v2->get_batch($conditions, array_key_exists('aggregates', $_GET), NULL, $service[0]->id, $this->appointments_model_v2::SERVICE);
        } else {
            set_status_header(404);
            echo 'id_service_integrated is not exist in database';
            exit;
        }
        return $appointments;
    }
    private function getAppointmentByUserId($conditions, $id_user_integrated) {
        $user = $this->user_model_v2->find_by_id_integrated($id_user_integrated);
        if (isset($user)) {
            $appointments = $this->appointments_model_v2->get_batch($conditions, array_key_exists('aggregates', $_GET), $user['id'], NULL, $this->appointments_model_v2::CUSTOMER);
        }

        return $appointments;
    }

    private function getAppointmentByProviderIdAndServiceId($conditions, $id_provider_integrated, $id_service_integrated) {
        $user =  $this->user_model_v2->find_by_id_integrated($id_provider_integrated);
        if (isset($user)) {
            $service = $this->services_model_v2->find_by_id_integrated($id_service_integrated);
            if (isset($service)) {
                $appointments = $this->appointments_model_v2->get_batch($conditions, array_key_exists('aggregates', $_GET), $user['id'], $service[0]->id, $this->appointments_model_v2::PROVIDER_SERVICE);
            } else {
                set_status_header(404);
                echo 'id_service_integrated is not exist in database';
                exit;
            }          
        }
        return $appointments;
    }

    /**
     * Count appointments by gender
     * jira ticket : https://davidodev.atlassian.net/browse/EAI-30
     */
    public function getTotalAppointmentGroupByGender(){
        $startDate= $this->input->get('startDate');
        $endDate = $this->input->get('endDate');
        $id_integrated = $this->input->get('id_service_integrated');
        $service = $this->services_model_v2->find_by_id_integrated($id_integrated);
        if(count($service) == 0){
            http_response_code(404);
            print('Could not found services with id: '.$id_integrated);
            return;
        }
        $resultSet = $this->appointments_model_v2->getStatisticAppointment($service[0]->id, $startDate, $endDate);
        $response = new Response($resultSet);
        $response->output();   
    }
}
