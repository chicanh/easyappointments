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
            throw new \EA\Engine\Api\V1\Exception('$idIntegrated does not exist in DB: ' . $idIntegrated, 404, 'Not Found');
        }

        $user = $query->num_rows() > 0 ? $query->row() : ''; // Get first record found

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

        return $query->row();

    }

}
