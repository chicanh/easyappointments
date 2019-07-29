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
}
