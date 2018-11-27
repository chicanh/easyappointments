<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      Davido Team
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
            $result = array();
            $service = NULL;
            if ($_GET['id_integrated'] !== NULL) {
                // Get service id that have id_integrated = id_services_integrated in table ea_services
                $service = $this->services_model_v2->find_by_id_integrated($_GET['id_integrated']);
                if (!isset($service)) {
                    throw new \EA\Engine\Api\V1\Exception('$service does not exist in DB: ' . $service, 404, 'Not Found');
                }
                array_push($result, $service);
            }

            $response = new Response($result);

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
