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
        $this->load->model('/v2/providers_model_v2');
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

            $response = NULL;
            $user_model = $this->user_model_v2;
            $services_model = $this->services_model_v2;
            $services_providers_model = $this->services_providers_model_v2;
            $name = $this->input->get('name');
            $idServiceIntegrated = $this->input->get("id_service_integrated");
            if($name && $idServiceIntegrated){
                $this->getByFullName($name, $idServiceIntegrated);
            }
            else if ($_GET['id_service_integrated'] !== NULL) {
                $services_providers = array();
                // Get service that have id_integrated = id_services_integrated in table ea_services
                $service = $services_model->find_by_id_integrated($_GET['id_service_integrated']);
                if (isset($service)) {
                    $services_providers = $services_providers_model->getProvidersByServiceId($service[0]->id);
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
            } else if ($_GET['id_integrated'] !== NULL) {
                // Get user that have id_integrated = id_integrated in table ea_users
                $provider = $this->providers_model_v2->get_batch("id_integrated = '" . $_GET['id_integrated'] . "'");
                $response = new Response($provider);
                $response->encode($this->parser)->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();
            } else {
                $this->getProvider($id);
            }
            


        } catch (\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }
    /**
     * POST API Method
     */
    public function post()
    {
        try
        {
            // Insert the provider to the database. 
            $request = new Request();
            $provider = $request->getBody();
            $this->parser->decode($provider);

            if (isset($provider['id']))
            {
                unset($provider['id']);
            }

            $id = $this->providers_model_v2->add($provider);

            // Fetch the new object from the database and return it to the client.
            $batch = $this->providers_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $status = new NonEmptyText('201 Created');
            $response->encode($this->parser)->singleEntry(TRUE)->output($status);
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    /**
     * PUT API Method
     *
     * @param int $id The record ID to be updated.
     */
    public function put($id)
    {
        try
        {
            // Update the provider record. 
            $batch = $this->providers_model_v2->get_batch('id = ' . $id);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedProvider = $request->getBody();
            $baseProvider = $batch[0];
            $this->parser->decode($updatedProvider, $baseProvider);
            $updatedProvider['id'] = $id;
            $id = $this->providers_model_v2->add($updatedProvider);

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->providers_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function updateProvider($id_integrated) {
        if($id_integrated !=null) {
         $condition = "id_integrated = '" .$id_integrated . "'";
         $provider = $this->providers_model->get_batch($condition);
            if (count($provider) === 0) {
                $this->_throwRecordNotFound();
            }
            parent::put($provider[0]['id']);
        } else {
            set_status_header(400);
            echo 'please enter id_integrated';
        }
    }

    public function updateProviderByServiceId($id_service_integrated, $id_integrated) {
        try {
            if($id_integrated !=null && $id_service_integrated!=null) {
                $service = $this->services_model_v2->get_batch("id_integrated='". $id_service_integrated . "'");
                $provider = $this->providers_model_v2->get_batch("id_integrated='". $id_integrated . "'");
                if (!empty($service) && isset($provider)) {
                        $services_providers = $this->services_providers_model_v2->getProviderByServiceId($service[0]['id'], $provider[0]['id']);
                        if(!empty($services_providers)) {
                            return $this->put($provider[0]['id']);
                        } else {
                            $this->_throwRecordNotFound();
                        }
                    } else {
                        $this->_throwRecordNotFound();
                    }
                } else {
                    set_status_header(400);
                    echo 'please enter id_integrated';
                }
        } catch(\Exception $exception) {
            $this->_handleException($exception);
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
    
    private function getProvider($id=null) {
        $condition = $id !== NULL ? 'id = ' . $id : NULL;
            $providers = $this->providers_model_v2->get_batch($condition);

            if ($id !== NULL && count($providers) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $response = new Response($providers);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id)
                ->output();
    }

    public function updateProviderIdIntegrated($id_integrated, $newIdIntegrated) {
        try
        {
            if($id_integrated !=null && $newIdIntegrated != null) {
                $condition = "id_integrated = '" .$id_integrated . "'";
                $provider = $this->providers_model->get_batch($condition);
                if (count($provider) === 0) {
                    $this->_throwRecordNotFound();
                }
                $this->providers_model_v2->updateProviderIdIntegrated($provider[0]['id'], $newIdIntegrated);
                return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(array(
                        'id_integrated' => $newIdIntegrated
                )));


            } else {
                set_status_header(400);
                throw new Exception('Please enter required field.');
            }
        } catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
 
    public function getProvidersWithCategoryAndService($category_integrated, $service_integrated) {
        try {
            $providers = $this->providers_model_v2->getProvidersByCategoryAndService($category_integrated, $service_integrated);
            $response = new Response($providers);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();
        } catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    private function getByFullName($name, $idServiceIntegrated){
        try {
            $providers =  $this->providers_model_v2->getProviderBy($name, $idServiceIntegrated);

            $response = new Response($providers);
            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->output();
        } catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function addProvidersToService($idServiceIntegrated)
    {
        try {
            $request = new Request();
            $requestBody = $request->getBody();
            $idProvidersIntegrated = $requestBody['providers'];
            if (!$idServiceIntegrated && !$idProvidersIntegrated) {
                throw new Exception('idServiceIntegrated and idProvidersIntegrated are required in request body');
            }
            if (!is_array($idProvidersIntegrated)) {
                throw new Exception('idProvidersIntegrated must be an array');
            }
            $providers = $this->providers_model_v2->getProvidersByIdIntegrated($idProvidersIntegrated);
            if (!$providers) {
                $this->_throwRecordNotFound('Providers with id_integrated in list: "' . implode(" , ", $idProvidersIntegrated) . '" not found');
            }
            $services_id = $this->services_model_v2->find_by_id_integrated($idServiceIntegrated)[0]->id;
            if (!$services_id) {
                $this->_throwRecordNotFound('Service id with id_integrated: "' . $services_id . '" not found');
            }
            $providers = $this->providers_model_v2->addProviderToService($services_id, $providers);
            $response = new Response($providers);
            $response->encode($this->parser)->output();
        } catch (\Exception $exception) {
            $this->_handleException($exception);
        }
    }
    public function removeProviderToService($idProvidersIntegrated, $idServiceIntegrated)
    {
        try {
            $provider_id = $this->providers_model_v2->getProvidersByIdIntegrated(array($idProvidersIntegrated))[0]['id'];
            if (!$provider_id) {
                $this->_throwRecordNotFound('Providers with id_integrated : "' . $provider_id . '" not found');
            }
            $services_id = $this->services_model_v2->find_by_id_integrated($idServiceIntegrated)[0]->id;
            if (!$services_id) {
                $this->_throwRecordNotFound('Service id with id_integrated: "' . $services_id . '" not found');
            }
            $this->providers_model_v2->removeProviderToService($services_id, $provider_id);
        } catch (\Exception $exception) {
            $this->_handleException($exception);
        }
    }
}
