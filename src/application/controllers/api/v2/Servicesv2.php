<?php defined('BASEPATH') OR exit('No direct script access allowed');

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

require_once __DIR__ . '/../v1/Services.php';

use \EA\Engine\Api\V1\Response;

/**
 * Services Controller
 *
 * @package Controllers
 * @subpackage API
 */
class ServicesV2 extends Services {
    /**
     * Services Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Services
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('services_model');
        $this->load->model('/v2/services_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\ServicesV2;
    }

    /**
     * GET API Method
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try {
            $condition = $id !== NULL ? 'id = ' . $id : NULL;
            $services = $this->services_model_v2->get_batch($condition);
            
            if ($id !== NULL && count($services) === 0)
                {
                    $this->_throwRecordNotFound();
                }

            if ($_GET['id_integrated'] !== NULL) {
                $condition ="id_integrated = '" . $_GET['id_integrated'] . "'";
                $services = $this->services_model_v2->get_batch($condition);
                if (count($services) === 0)
                {
                    $this->_throwRecordNotFound();
                }
            }

            $response = new Response($services);

            $response->encode($this->parser)
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
       parrent::put($id);
    }

    public function updateService() {
        $id_integrated = $this->input->get('id_integrated');
        if($id_integrated !=null) {
         $condition = "id_integrated = '" .$id_integrated . "'";
         $service = $this->services_model_v2->get_batch($condition);
            if (count($service) === 0) {
                $this->_throwRecordNotFound();
            }
            parent::put($service[0]['id']);
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
        parrent::delete($id);
    }
}
