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
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

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
        $this->load->model('/v2/category_model_v2');
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
        try
        {
            // Insert the service to the database. 
            $request = new Request();
            $service = $request->getBody();
            $this->parser->decode($service);

            if (isset($service['id']))
            {
                unset($service['id']);
            }

            $id = $this->services_model_v2->add($service);

            // Fetch the new object from the database and return it to the client.
            $batch = $this->services_model_v2->get_batch('id = ' . $id);
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
            // Update the service record. 
            $batch = $this->services_model_v2->get_batch('id = ' . $id);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedService = $request->getBody();
            $baseService = $batch[0];
            $this->parser->decode($updatedService, $baseService);
            $updatedService['id'] = $id;
            $id = $this->services_model_v2->add($updatedService);

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->services_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function updateService($id_integrated) {
         $condition = "id_integrated = '" .$id_integrated . "'";
         $service = $this->services_model_v2->get_batch($condition);
         $request = new Request();
         $updatedService = $request->getBody();
         $baseService = $service[0];
         $this->parser->decode($updatedService, $baseService);
         $updatedService['id'] = $baseService['id'];
         $id = $this->services_model_v2->add($updatedService);

         // Fetch the updated object from the database and return it to the client.
         $batch = $this->services_model_v2->get_batch('id = ' . $id);
         $response = new Response($batch);
         $response->encode($this->parser)->singleEntry($id)->output();
        
        
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
    public function removeServiceCategory($id_integrated) {
        try {
            $categoryIdIntegrated =  $this->input->get('categories');
            if(!isset($categoryIdIntegrated)) {
                throw new \EA\Engine\Api\V1\Exception('Field categories is required ', 400, 'Bad Request');
            }
            $categories = explode(',', $categoryIdIntegrated);
            $category_ids = $this->category_model_v2->getCategoryIdById_Integrated($categories);
            $condition = "id_integrated = '" .$id_integrated . "'";
            $service = $this->services_model_v2->get_batch($condition);
            if (count(array_intersect($category_ids, $service[0]['categories'])) === 0) {
                $this->_throwRecordNotFound();
            } else {
                $this->category_model_v2->removeCategoryService($service[0]["id"], $category_ids);
            }
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
 
    public function addCategoryToService($id_integrated) {
        try {

            $condition = "id_integrated = '" .$id_integrated . "'";
             $service = $this->services_model_v2->get_batch($condition);
             $request = new Request();
             $listCategoryIdIntegrated = $request->getBody();
             $category_ids = $this->category_model_v2->getCategoryIdById_Integrated($listCategoryIdIntegrated['categories']);
             if(empty($category_ids)) {
                $this->_throwRecordNotFound();
             }
             $this->services_model_v2->save_categories($category_ids, $service[0]['id']);   
             // Fetch all of the category belong to service
             $categories = $this->category_model_v2->getCategoriesByServiceId($id_integrated);
             $response = new Response($categories);
                    $response->search()
                        ->sort()
                        ->paginate()
                        ->minimize()
                        ->output();
        }  catch(\Exception $exception) {
            $this->_handleException($exception);
        }
    }
}
