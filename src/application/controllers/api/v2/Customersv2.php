<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/../v1/Customers.php';

use \EA\Engine\Api\V1\Response;

/**
 * Customers Controller
 *
 * @package Controllers
 * @subpackage API
 */

class CustomersV2 extends Customers {
    /**
     * Customers Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Customers
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v2/customers_model_v2');
        $this->load->model('/v2/user_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\CustomersV2;
    }

    /**
     * GET API Method
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try {
            $customer = NULL;
            if ($_GET['id_integrated'] !== NULL) {
                // Get service id that have id_integrated = id_services_integrated in table ea_services
                $condition = "id_integrated = '" .$_GET['id_integrated'] . "'";
                $customer = $this->customers_model_v2->get_batch($condition);
                if(count($customer) == 0) {
                    throw new \EA\Engine\Api\V1\Exception('$customer does not exist in DB: ' . $customer, 404, 'Not Found');
                }
            }
            else if ($_GET['phone'] !== NULL) {
                // Get user that have phone = {phone_number or mobile_number} in table ea_users
                $customer = $this->user_model_v2->find_by_phone($_GET['phone']);
                if ($customer['id_roles'] != 3) {
                    throw new \EA\Engine\Api\V1\Exception('$customer does not exist in DB: ' . $customer, 404, 'Not Found');
                }
               
            } else if($id!==NULL) {
                $condition = 'id = ' . $id;
                $customer = $this->customers_model_v2->get_CustomerById($condition);
            }
            else {
            $customer = $this->customers_model_v2->get_batch($condition);
            }
        $response = new Response($customer);
        $response
            ->search()
            ->sort()
            ->paginate()
            ->minimize()
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
        parent::post();
    }

    /**
     * PUT API Method
     *
     * @param int $id The record ID to be updated.
     */
    public function put($id)
    {
       parent::put($id);
    }

    public function updateCustomer($id_integrated) {
        if($id_integrated !=null) {
            $condition = "id_integrated = '" .$id_integrated . "'";
            $customer = $this->customers_model_v2->get_batch($condition);
            if (count($customer) === 0){
                    $this->_throwRecordNotFound();
            }
            parent::put($customer[0]['id']);
        } else {
            set_status_header(400);
            echo 'please enter id_integrated';
        }
    }

    /**
     * DELETE API Method
     *
     * @param int $id The record ID to be deleted.
     */
    public function delete($id)
    {
        parent::delete($id);
    }
}
