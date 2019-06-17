<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Customers Model v2
 *
 * @package Models
 */
class Customers_Model_V2 extends Customers_Model {

    public function add($customer)
    {
        // Validate the customer data before doing anything.
        $this->validate($customer);

	$customer['id'] = $this->_insert($customer);
        return $customer['id'];
    }

    /**
     * Check if a particular customer record already exists.
     *
     * This method checks whether the given customer already exists in the database. It doesn't search with the id, but
     * with the following fields: "email"
     *
     * @param array $customer Associative array with the customer's data. Each key has the same name with the database
     * fields.
     *
     * @return bool Returns whether the record exists or not.
     *
     * @throws Exception If customer email property is missing.
     */
    public function exists($customer)
    {
        if ( ! isset($customer['email']))
        {
            $this->response(['status' => FALSE, 'error' => 'Customer\'s email is not provided.']);
            throw new Exception('Customer\'s email is not provided.');
        }

        // This method shouldn't depend on another method of this class.
        $num_rows = $this->db
            ->select('*')
            ->from('ea_users')
            ->join('ea_roles', 'ea_roles.id = ea_users.id_roles', 'inner')
            ->where('ea_users.email', $customer['email'])
            ->where('ea_roles.slug', DB_SLUG_CUSTOMER)
            ->get()->num_rows();

        return ($num_rows > 0) ? TRUE : FALSE;
    }

    /**
     * Override from v1 to validate customer data before the insert or update operation is executed.
     * 
     * This does not validate email address if it is unset
     *
     * @param array $customer Contains the customer data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If customer validation fails.
     */
    public function validate($customer)
    {
        $this->load->helper('data_validation');

        // If a customer id is provided, check whether the record
        // exist in the database.
        if (isset($customer['id']))
        {
            $num_rows = $this->db->get_where('ea_users',
                ['id' => $customer['id']])->num_rows();
            if ($num_rows == 0)
            {
                throw new Exception('Provided customer id does not '
                    . 'exist in the database.');
            }
        }
        // Validate required fields
        if (! isset($customer['phone_number']))
        {
            throw new Exception('Not all required fields are provided: '
                . print_r($customer, TRUE));
        }

        // Validate email address
        if(isset($customer['email']))
        {
            if ( ! filter_var($customer['email'], FILTER_VALIDATE_EMAIL))
            {
                throw new Exception('Invalid email address provided: '
                    . $customer['email']);
            }
        }

        return TRUE;
    }

    public function get_batch($where_clause = '') {
        return  parent::get_batch($where_clause);
    }

    public function get_CustomerById($where_clause = '') {
        $customers_role_id = parent::get_customers_role_id();

        if ($where_clause != '')
        {
            $this->db->where($where_clause);
        }

        $this->db->where('id_roles', $customers_role_id);

        return $this->db->get('ea_users')->row_array();
    }

}
