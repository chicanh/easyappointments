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
 * Services Model v2
 *
 * @package Models
 */
class Services_Model_V2 extends Services_Model {

    /**
     * Find service by id_integrated
     * @param $idIntegrated
     * @return string
     * @throws Exception
     */
    public function find_by_id_integrated($idIntegrated)
    {
        if ( ! isset($idIntegrated))
        {
            throw new Exception('Service idIntegrated is not provided: ' . print_r($idIntegrated, TRUE));
        }

        $query = $this->db->get_where('ea_services', ['id_integrated' => $idIntegrated ]);

        $service = $query->num_rows() > 0 ? $query->result() : NULL;

        return $service;
    }
    public function get_row($id_service_integrated)
    {
        return $this->db->get_where('ea_services', ['id_integrated' => $id_service_integrated])->row_array();
    }

    public function get_batch($where_clause = NULL)
    {
        return parent::get_batch($where_clause);
    }

    /**
     * Query all relative appointment by service id_integrated, start date & end date
     */
    public function getAllAppointmentBy($service_id, $startDate, $endDate){
        $service = $this->db->get_where('ea_services', ['id_integrated'=>$service_id])->result_array();
        if(count($service) == 0){
            throw new Exception('Could not found services with id: '.$service_id);
        }
        if(strlen($startDate) != 0){
            $condition['start_datetime >='] = $startDate;
        }
        if(strlen($endDate) != 0){
            $condition['end_datetime <='] = $endDate;
        }
        
        $condition['id_services'] = $service[0]['id'];
   
        $appointments = $this->db->get_where('ea_appointments', $condition)->result_array();
        return $appointments;
    }

}
