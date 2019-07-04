<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/../v1/API_V1_Controller.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;
class Categoriesv2 extends API_V1_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v2/category_model_v2');
    }

    public function get($id = NULL) {
        try {
            $condition = $id !== NULL ? 'id = ' . $id : NULL;
            $categories = $this->category_model_v2->get_batch($condition);

            if ($id !== NULL && count($categories) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $response = new Response($categories);

            $response
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id)
                ->output();
        } catch(\Exception $exception) {
            $this->_handleException($exception);
        }
    }

    public function put($id) {
        try
        {
            // Update the appointment record. 
            $batch = $this->category_model_v2->get_batch('id = ' . $id);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedCategory = $request->getBody();
            $baseCategory = $batch[0];
            $updatedCategory['id'] = $id;
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
            $request = new Request();
            $requestBody = $request->getBody();
            if(!empty($requestBody)) {
                $categoryIds = $this->category_model_v2->getCategoryId($requestBody);
                $response = new Response($categoryIds);
                $response->search()
                    ->sort()
                    ->paginate()
                    ->minimize()
                    ->output();
            }
        }
        catch (\Exception $exception)
        {
            $this->_handleException($exception);
        }
    }
}
?>