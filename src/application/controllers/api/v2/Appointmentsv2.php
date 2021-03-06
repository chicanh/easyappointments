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
    protected $attachments_parser;

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
        $this->attachments_parser = new \EA\Engine\Api\V2\Parsers\AttachmentsV2;
    }

    /**
     * GET API Method
     *
     * @param int $id_integrated Optional (null), the record ID to be returned.
     */
    
    public function get($id_integrated = null) {
        try {

            $sort = $this->input->get('sort');
            $page = $this->input->get('page');
            $size = $this->input->get('length');
            $startDate = $this->input->get('startDate');
            $endDate = $this->input->get('endDate');

            $totalAppointmentsByPeriodTime = 0;

            $conditions = [
                'is_unavailable' => FALSE
            ];

            if ($id_integrated !== NULL) {
                $conditions['id_integrated'] = $id_integrated;
            }


            $resultSet = ($startDate == null && $endDate == null) ? $this->getDataWithoutDateRange($conditions) : 
                                                                    $this->getDataWitDateRange($conditions);
            $appointments = $resultSet['appointments'];
            $totalAppointmentsByPeriodTime = $resultSet['total'];


            if ($id_integrated !== NULL && count($appointments) === 0)
            {
                    $this->_throwRecordNotFound();
            }
    
            $result = $this->getAttachments($appointments);
            $encodedAppointments = $this->encodedAppointments($result);  
            
            $responseSet['total'] = $totalAppointmentsByPeriodTime == null ? 0 : $totalAppointmentsByPeriodTime;
            $responseSet['amount'] = $resultSet['amount'];
            $responseSet['appointments'] = $encodedAppointments;
            $response = new Response($responseSet);
            $response->singleAppointmentEntry($id_integrated)->output();
            
        } catch (\Exception $exception) {
                    exit($this->_handleException($exception));
        }
    }

    private function getDataWithoutDateRange($conditions){
        $id_service_integrated = $this->input->get('id_service_integrated');
        $id_provider_integrated = $this->input->get('id_provider_integrated');
        $id_user_integrated = $this->input->get('id_user_integrated');
        $otherRequestParams = $this->input->get();
        if($id_provider_integrated != null && $id_service_integrated != null) {
            return $this->getAppointmentByProviderIdAndServiceId($conditions, $id_provider_integrated, $id_service_integrated, $otherRequestParams);
        } else if($id_user_integrated != null && $id_service_integrated != null) {
            return $this->appointments_model_v2->getAllAppointmentBy(null, array_key_exists('aggregates', $_GET), $otherRequestParams, $this->appointments_model_v2::CUSTOMER_SERVICE);
        }
        else if($id_user_integrated != null ) {
            return $this->getAppointmentByUserId($conditions, $id_user_integrated, $otherRequestParams);
        }else if($id_provider_integrated != null) {
            return  $this->getAppointmentByProviderId($conditions, $id_provider_integrated, $otherRequestParams);     
        }else if($id_service_integrated != null) {
            return $this->getAppointmentByServiceId($conditions, $id_service_integrated, $otherRequestParams);
        }else{
            return $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), null, null ,'' , $otherRequestParams);
        }
    }

    private function getDataWitDateRange($conditions){
        $id_service_integrated = $this->input->get('id_service_integrated');
        $id_user_integrated = $this->input->get('id_user_integrated');
        $otherRequestParams = $this->input->get();
        if($id_service_integrated != null && $id_user_integrated != null ){
            return $this->appointments_model_v2->getAllAppointmentBy(null, array_key_exists('aggregates', $_GET), $otherRequestParams, $this->appointments_model_v2::CUSTOMER_SERVICE);
        } else if($id_service_integrated != null ){
           return $this->getAllAppointmentByPeriodTime($startDate, $endDate, $id_service_integrated, $otherRequestParams, $this->appointments_model_v2::SERVICE);
        }else if($id_user_integrated != null){
           return $this->getAllAppointmentByPeriodTime($startDate, $endDate, $id_user_integrated, $otherRequestParams, $this->appointments_model_v2::CUSTOMER);
        }else{
            return $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), null, null ,'' , $otherRequestParams);
        }
    }

    private function getAttachments($appointments){
        $result = array();
        if (count($appointments) > 0) {
            foreach ($appointments as $appointment) {
                $attachments = $this->attachments_model_v2->get_batch($appointment['id']);
                $attachments = $this->attachments_parser->encode($attachments);

                $appointment['attachment'] =  $attachments;
                array_push($result, $appointment);
            }
        }
        return $result;
    }

    private function encodedAppointments($appointments){
        $encodedAppointments = [];  
        foreach ($appointments as &$value){
            array_push($encodedAppointments,$this->parser->customEncode($value));
        }
        return $encodedAppointments;
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
    public function put($id_integrated)
    {
        try
        {
            $batch = $this->appointments_model_v2->get_batch("id_integrated = '" . $id_integrated ."'");
            $id = $this->appointments_model_v2->find_id_by_id_integrated($id_integrated);
            
            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            if(isset($_GET['status'])) {
                return $this->updateAppointmentStatus($id_integrated, $id, $batch);
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

    public function updateAppointmentByIdIntegrated($batch){
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

    public function updateAppointmentStatus($id_integrated, $id, $batch) {
        try
        {
            if($_GET['status'] !==null)
            {
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
    private function getAllAppointmentByPeriodTime($startDate, $endDate, $id_integrated, $otherRequestParams, $type = ''){
        $service = [];

        switch ($type) {
            case $this->appointments_model_v2::CUSTOMER:
                $service = $this->user_model_v2->find_by_id_integrated($id_integrated);
                break;
            case $this->appointments_model_v2::SERVICE:
                $service = $this->services_model_v2->find_by_id_integrated($id_integrated);
                break;
            default:
                break;
        }
        if(count($service) == 0){
            http_response_code(404);
            exit();
        }
        $resultSet = $this->appointments_model_v2->getAllAppointmentBy($service, array_key_exists('aggregates', $_GET), $otherRequestParams, $type);
        return $resultSet;
    }

    private function getAppointmentByProviderId($conditions, $id_provider_integrated, $otherRequestParams) {
        $provider = $this->user_model_v2->find_by_id_integrated($id_provider_integrated);
        if (isset($provider)) {
          $appointments = $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), $provider['id'], NULL, $this->appointments_model_v2::PROVIDER, $otherRequestParams);
        }
        return $appointments;
    }

    private function getAppointmentByServiceId($conditions, $id_service_integrated, $otherRequestParams) {
        $service = $this->services_model_v2->find_by_id_integrated($id_service_integrated);
        if (isset($service)) {
           $appointments = $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), NULL, $service[0]->id, $this->appointments_model_v2::SERVICE, $otherRequestParams);
        } else {
            set_status_header(404);
            echo 'id_service_integrated is not exist in database';
            exit;
        }
        return $appointments;
    }

    private function getAppointmentByUserId($conditions, $id_user_integrated, $otherRequestParams) {
        $user = $this->user_model_v2->find_by_id_integrated($id_user_integrated);
        if (isset($user)) {
            $appointments = $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), $user['id'], NULL, $this->appointments_model_v2::CUSTOMER, $otherRequestParams);
        }

        return $appointments;
    }

    private function getAppointmentByProviderIdAndServiceId($conditions, $id_provider_integrated, $id_service_integrated, $otherRequestParams) {
        $user =  $this->user_model_v2->find_by_id_integrated($id_provider_integrated);
        if (isset($user)) {
            $service = $this->services_model_v2->find_by_id_integrated($id_service_integrated);
            if (isset($service)) {
                $appointments = $this->appointments_model_v2->get_batch_paging($conditions, array_key_exists('aggregates', $_GET), $user['id'], $service[0]->id, $this->appointments_model_v2::PROVIDER_SERVICE, $otherRequestParams);
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
            exit();
        }
        $resultSet = $this->appointments_model_v2->getStatisticAppointment($service[0]->id, $startDate, $endDate);
        $response = new Response($resultSet);
        $response->output();   
    }

    public function updateAppointmentByOrderId($orderId) {
        try
        {
            $request = new Request();
            $updatedAppointment = $request->getBody();
            $this->appointments_model_v2->updateAppointmentByOrderId($orderId, $updatedAppointment);
            $batch = $this->appointments_model_v2->get_batch('order_id = ' . $orderId);
            $response = new Response($batch);
            $status = new NonEmptyText('200 OK');
            $response->encode($this->parser)->singleEntry(TRUE)->output($status);
        } 
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function getAppointmentByOrderId($orderId) {
       try {
           if($this->input->get('id_user_integrated') != null){
            $user = $this->user_model_v2->find_by_id_integrated($this->input->get('id_user_integrated'));
            $where['id_users_customer'] = $user['id'];
           }         
           $where['order_id'] = $orderId;
           $appointment = $this->appointments_model_v2->get_batch($where);
           if($appointment == null ) {
            throw new \EA\Engine\Api\V1\Exception('User id does not exist in the database.', 404, 'Not Found');
           }
           $result = $this->appointments_model_v2->getAppointmentsWithCondition($appointment, array_key_exists('aggregates', $_GET));   
           $responseSet['total'] = sizeof($result['appointments']);
           $responseSet['appointments'] = $this->encodedAppointments($result['appointments']);
           $response = new Response($responseSet);
           $response->singleAppointmentEntry($id_integrated)->output();
       } catch (\Exception $exception) {
            exit($this->_handleException($exception));
       }   
    }

    public function getAppointmentWithServiceIntegrated($id_service_integrated) {
        try {
                $appointments = $this->appointments_model_v2->getAppointmentWithServiceIntegrated($id_service_integrated);
                $response = new Response($appointments);
                 $response->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();
        }catch(\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }
}
