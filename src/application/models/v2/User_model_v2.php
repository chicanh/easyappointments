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
 * User Model v2
 *
 * @package Models
 *
 */
class User_Model_V2 extends User_Model {

    /**
     * Find user by id_integrated
     * @param $idIntegrated
     * @return string
     * @throws Exception
     */
    public function find_by_id_integrated($idIntegrated)
    {
        if ( ! isset($idIntegrated))
        {
            throw new Exception('User idIntegrated is not provided: ' . print_r($idIntegrated, TRUE));
        }

        $query = $this->db->get_where('ea_users', ['id_integrated' => $idIntegrated ]);

        if ($query->num_rows() == 0)
        { // Check if id_integrated exists in ea_users
            set_status_header(404);
            echo 'the provided id is not exist in database';
            exit;
        }

        $user = $query->num_rows() > 0 ? $query->row_array() : '';

        return $user;
    }

    /**
     * Find user by id
     * @param $id The user id
     * @return string
     * @throws Exception If not found
     */
    public function find_by_id($id)
    {
        if ( ! isset($id))
        {
            throw new Exception('User $id is not provided: ' . print_r($id, TRUE));
        }

        $query = $this->db->get_where('ea_users', ['id' => $id ]);

        if ($query->num_rows() == 0)
        { // Check if $id exists in ea_users
            throw new \EA\Engine\Api\V1\Exception('$id does not exist in DB: ' . $id, 404, 'Not Found');
        }

        return $query->row_array();

    }

    /**
     * Find user by phone (or mobile)
     * @param $phone
     * @return string
     * @throws Exception
     */
    public function find_by_phone($phone)
    {
        if ( ! isset($phone))
        {
            throw new Exception('User phone is not provided: ' . print_r($phone, TRUE));
        }

        $this->db->where('phone_number', $phone);
        $this->db->or_where('mobile_number', $phone);
        $query = $this->db->get('ea_users');

        if ($query->num_rows() == 0)
        { // Check if phone exists in ea_users
            throw new \EA\Engine\Api\V1\Exception('$phone does not exist in DB: ' . $phone, 404, 'Not Found');
        }

        $user = $query->num_rows() > 0 ? $query->row_array() : '';

        return $user;
    }

    public function find_list_userId_by_fullName($fullName, $id_service_integrated){
        $idList = [];
        if($fullName == null || $fullName == ''){
            return $idList;
        }
        $ID_ROLES_OF_CUSTOMER = 3;

        $result = [];
        if(strlen($id_service_integrated) == 0){
            $sql = "SELECT id FROM ea_users WHERE CONCAT(first_name, ' ',last_name) LIKE ? AND id_roles = ?";
            $result = $this->db->query($sql, array('%'.$fullName.'%', $ID_ROLES_OF_CUSTOMER))->result_array();
        }else{
           $this->db->select("eaUsers.id")
                    ->from("ea_users eaUsers")
                    ->join('ea_appointments eaAppointments', 'eaAppointments.id_users_customer = eaUsers.id')
                    ->where('eaUsers.id_roles', $ID_ROLES_OF_CUSTOMER)
                    ->where('eaAppointments.id_services', $id_service_integrated)
                    ->where("CONCAT(eaUsers.first_name, ' ', eaUsers.last_name) LIKE '%".$fullName."%'", NULL, FALSE);
            $result = $this->db->get()->result_array();
        }
        foreach($result as &$record){
            array_push($idList, $record['id']);
        }
        return $idList;
    }

    public function get_aggregates(array $patients)
    {
        $patients['city_id'] = $this->db->select('id, city')->from('integrated_cities')->where('id', $patients['city_id'])->get()->result_array()[0];
        $patients['district_id'] = $this->db->select('id, name')->from('integrated_districts')->where('id', $patients['district_id'])->get()->result_array()[0];
        $patients['ward_id'] = $this->db->select('id, name')->from('integrated_wards')->where('id', $patients['ward_id'])->get()->result_array()[0];
        return $patients;
    }
}
