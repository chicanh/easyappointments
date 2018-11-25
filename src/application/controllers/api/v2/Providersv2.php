<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @updater     Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/../v1/Providers.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Providers Controller
 *
 * @package Controllers
 * @subpackage API
 */
class ProvidersV2 extends Providers {

    /**
     * Providers Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Providers
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('services_model');
        $this->load->model('/v2/user_model_v2');
        $this->load->model('/v2/services_model_v2');
        $this->load->model('/v2/services_providers_model_v2');
        $this->load->model('providers_model');
        $this->parser = new \EA\Engine\Api\V2\Parsers\ProvidersV2;
    }

    /**
     * GET API Method
     * Get all Providers (distinct) of all appointments that made by a specific user
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try {
            $conditions = [
                'is_unavailable' => FALSE
            ];

            if ($id !== NULL) {
                $conditions['id_integrated'] = $id;
            }

            $services_providers = array();
            $user_model = $this->user_model_v2;
            $services_model = $this->services_model_v2;
            $services_providers_model = $this->services_providers_model_v2;

            if ($_GET['id_service_integrated'] !== NULL) {
                // Get service id that have id_integrated = id_services_integrated in table ea_services
                $service = $services_model->find_by_id_integrated($_GET['id_service_integrated']);
                if (isset($service)) {
                    $services_providers = $services_providers_model->get_providers_by_service_id($service->id);
                }
            }

            $providers = array();
            if (count($services_providers) > 0) {
                foreach ($services_providers as $sp) {
                    $user = $user_model->find_by_id($sp['id_users']);
                    array_push($providers, $user);
                }
            }

            $response = new Response($providers);

            $response->search()
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