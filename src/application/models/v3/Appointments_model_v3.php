<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointments Model v3
 *
 * @package Models/v3
 */
class Appointments_Model_V3 extends Appointments_Model {

    public function getAppointmentsWithCondition($id_user_integrated, 
                                                $id_service_integrated, 
                                                $id_patient_integrated,
                                                $startDate,
                                                $endDate,
                                                $page,
                                                $size,
                                                $sort) {     
        $resultSet['total'] = $this->countAppointmentsByCondition($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);
        $resultSet['appointments'] = $this->getAppointmentsByCondition($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate, $page, $size, $sort); 
        return $resultSet; 
    }

    private function getAppointmentsByCondition($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate, $page, $size, $sort){
        $arrayParams = $this->initStoredProcedureParams($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);               
        array_push($arrayParams, $page, $size, $sort);
        $query = $this->db->query(GET_APPOINTMENTS_WITH_CONDITION_AND_PAGING_SP, $arrayParams);
        $response = $query->result_array();
        $this->releaseStoredProcedureQuery($query);
        foreach ($response as &$appointment) {
            $appointment = $this->get_aggregates($appointment);
        }
        return $response;
    }

    private function countAppointmentsByCondition($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate){
        $arrayParams = $this->initStoredProcedureParams($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);
        $query = $this->db->query(COUNT_APPOINTMENTS_WITH_CONDITION_SP, $arrayParams);
        $response = $query->result_array()[0]['total'];
        $this->releaseStoredProcedureQuery($query);
        return $response;
    }

    private function releaseStoredProcedureQuery($query){
        $query->next_result(); 
        $query->free_result(); 
    }

    private function initStoredProcedureParams($id_user_integrated,
                                               $id_service_integrated, 
                                               $id_patient_integrated, 
                                               $startDate = null, 
                                               $endDate = null) {     
        return [$id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate];
    }

    public function getUserAppointments($id_integrated, $id_user_integrated) {
        $patientId = $this->db->get_where('ea_users',['id_integrated' => $id_integrated])->row()->id;
        if(empty($patientId)) {
            throw new Exception('Can not find any record with id_integrated');
        }

        return $this->db->select('*')->from('ea_appointments')
        ->join('integrated_users_patients', 'ea_appointments.id_users_customer = integrated_users_patients.id_patients')
        ->where('integrated_users_patients.id_user_integrated', $id_user_integrated)
        ->where('integrated_users_patients.id_patients', $patientId)
        ->get()->result_array();
    }

    public function getAddressBookingStatistic($idServiceIntegrated, $cityId, $startDate, $endDate, $gender, $firstTime, $useHealthInsurance, $idProviderIntegrated) {
        $arrayParams = [$idServiceIntegrated, $cityId, $startDate, $endDate, $gender, $firstTime, $useHealthInsurance, implode(',',$idProviderIntegrated)];
        $query = $this->db->query(GET_DISTRICTS_BOOKING_STATISTIC_WITH_CONDITION, $arrayParams);
        $response = $query->result_array();
        $this->releaseStoredProcedureQuery($query);
        return $response;

    }

    protected function get_aggregates(array $appointment)
    {
        $appointment['service'] = $this->db->get_where('ea_services',
            ['id' => $appointment['id_services']])->row_array();
        $appointment['provider'] = $this->db->get_where('ea_users',
            ['id' => $appointment['id_users_provider']])->row_array();
        $appointment['customer'] = $this->db->get_where('ea_users',
            ['id' => $appointment['id_users_customer']])->row_array();
            
        $id = $appointment['id'];

        $appointment['patient'] = $this->db->select('*')->from('ea_users')
        ->join('ea_appointments_attendants', 
        "ea_users.id = ea_appointments_attendants.id_users AND ea_appointments_attendants.id_appointment = $id")->get()->row_array();
        return $appointment;
    }

    public function getAppointmentsWorkingDate($id_service_integrated, $id_provider_integrated, $dates) {
        $sqlQuery = "SELECT ea_customer.email, 
                            ea_appointments.id_integrated as bookingId, 
                            CONCAT(ea_provider.first_name,' ',ea_provider.last_name) as doctorName,
                            DATE(ea_appointments.start_datetime) as date 
                    FROM ea_appointments 
                    INNER JOIN ea_users ea_provider ON ea_appointments.id_users_provider = ea_provider.id 
                    INNER JOIN ea_users ea_customer ON ea_appointments.id_users_customer = ea_customer.id 
                    INNER JOIN ea_services ON ea_appointments.id_services = ea_services.id 
                    WHERE ea_services.id_integrated = ? AND ea_provider.id_integrated = ? AND (";
        $arrlength = sizeof($dates);       
        for($i = 0; $i < $arrlength; $i++) {
            $statement = "DATE(ea_appointments.start_datetime) = '".$dates[$i]."'";
            if($i < $arrlength - 1){
                $statement .= " OR ";
            }
            $sqlQuery .= $statement;
        }
        $sqlQuery .= ");";
        $result = $this->db->query($sqlQuery, array($id_service_integrated, $id_provider_integrated));
        return $result->result_array();
    }
}
