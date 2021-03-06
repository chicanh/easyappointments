<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/../v1/API_V1_Controller.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;
use \EA\Engine\Api\V2\DuplicateException;
class Categoriesv2 extends API_V1_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v2/category_model_v2');
    }

    public function get($id_integrated = NULL) {
        try {
            $categories = $this->input->get('categories');
            if(isset($categories)) {
                return $this->getCategoryIds();
            }
            $condition = $id_integrated !== NULL ? "id_integrated = '" . $id_integrated . "'" : NULL;
            $categories = $this->category_model_v2->get_batch($condition);

            if ($id_integrated !== NULL && count($categories) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $response = new Response($categories);

            $response
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id_integrated)
                ->output();
        } catch(\Exception $exception) {
            $this->_handleException($exception);
        }
    }

    public function put($id_integrated) {
        try
        {
            // Update the appointment record. 
            $condition = $id_integrated !== NULL ? "id_integrated = '" . $id_integrated . "'" : NULL;
            $batch = $this->category_model_v2->get_batch($condition);

            if ($id_integrated !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedCategory = $request->getBody();
            $id = $this->category_model_v2->add($updatedCategory);

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->category_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function post() {
        try
        {
            $request = new Request();
            $category = $request->getBody();

            if (isset($category['id']))
            {
                unset($category['id']);
            }
            $id = $this->category_model_v2->add($category);
            $batch = $this->category_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $status = new NonEmptyText('201 Created');
            $response->singleEntry(TRUE)->output($status);
        }

        catch (DuplicateException $exception)
        {
            $this->_handleException($exception);
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function getCategoryByServiceIdIntegrated($id_service_integrated) {
        try
        {
            $categories = $this->category_model_v2->getCategoriesByServiceId($id_service_integrated);
            $response = new Response($categories);

            $response->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function getCategoryByProviderId($id_provider_integrated) {
        try
        {
            $categories = $this->category_model_v2->getCategoriesByProviderId($id_provider_integrated);
            $response = new Response($categories);

            $response->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }

    public function getCategoryIds() {
        try
        {
            $categoryIdIntegrated = $this->input->get('categories');
            if(!isset($categoryIdIntegrated)) {
                throw new \EA\Engine\Api\V1\Exception('Field categories is required ', 400, 'Bad Request');
            }
            $categories = explode(',', $categoryIdIntegrated);
            $categoryIds = $this->category_model_v2->getCategoryId($categories);
                $response = new Response($categoryIds);
                $response->search()
                    ->sort()
                    ->paginate()
                    ->minimize()
                    ->output();
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
}
?>