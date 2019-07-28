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

    public function getAppointmentWithIdUserIntegrated($id_user_integrated) {

        return $this->db->select('*')->from('ea_appointments')
        ->join('integrated_users_patients', 'ea_appointments.id_users_customer = integrated_users_patients.id_patients')
        ->where('integrated_users_patients.id_user_integrated', $id_user_integrated)->get()->result_array();
    }

    public function getAppointmentWithUserIdAndServiceIdAndPatientId($id_user_integrated, 
                                                                    $id_service_integrated, 
                                                                    $id_patients,
                                                                    $startDate,
                                                                    $endDate,
                                                                    $page,
                                                                    $size,
                                                                    $sort) {                                      
        $this->db->select('*')->from('ea_appointments')
                ->join('integrated_users_patients', 'ea_appointments.id_users_customer = integrated_users_patients.id_patients')
                ->where('integrated_users_patients.id_user_integrated', $id_user_integrated)
                ->where('integrated_users_patients.id_service_integrated', $id_service_integrated);
        if(isset($id_patients)){
            $this->db->where('integrated_users_patients.id_patients', $id_patients);
        }
        return $this->db->get()->result_array();
    }

    public function countAppointmentsByCondition($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate){
        $arrayParams = $this->initStoredProcedureParams($id_user_integrated, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);
        $totalQuery = 'CALL countAppointmentsByCondition(?, ?, ?, ?, ?)';
        $query = $this->db->query($totalQuery, $arrayParams);
        $response = $query->result_array()[0]['total'];
        $this->releaseStoredProcedureQuery($query);
        return $response;
    }

    public function getAppointmentWithServiceIdAndPatientId($id_service_integrated, 
                                                            $id_patient_integrated,
                                                            $startDate,
                                                            $endDate,
                                                            $page,
                                                            $size,
                                                            $sort) {                                                                           
        $resultSet['total'] = $this->countAppointmentsByCondition(null, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);

 
        $arrayParams = $this->initStoredProcedureParams(null, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);               
        $appointmentsQuery = 'CALL getAppointmentsByConditionAndPaging(?, ?, ?, ?, ?, ?, ?, ?)';
        array_push($arrayParams, $page, $size, $sort);
        $query = $this->db->query($appointmentsQuery, $arrayParams);
        $resultSet['appointments'] = $query->result_array();
        $this->initStoredProcedureParams(null, $id_service_integrated, $id_patient_integrated, $startDate, $endDate);    
        return $resultSet;
    
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
