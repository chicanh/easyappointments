<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/API_V1_Controller.php';
use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\UnsignedInteger;
class Cities extends API_V1_Controller {
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cities_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Cities;
    }

    public function get()
    {
        $id = $this->input->get('id');
        $city = $this->input->get('city');
        $q = $this->input->get('q');
        if (!isset($id) && !isset($city) && !isset($q)) {
            $this->getAll();
        } else if (isset($q)) {
            $this->getCitiesNameLike($q);
        } else {
            $this->getByEitherIdOrName($id, $city);
        }
    }

    public function getAll(){
        try{
            $sort = $this->input->get('sort');
            $cities = $this->cities_model->getAllCities($sort);
         
            $response = new Response($cities);
        
            $response->encode($this->parser)->output();
        } catch (\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }

    public function getCitiesNameLike($q)
    {
        try {
            $cities = $this->cities_model->getCityNameLike($q);

            $response = new Response($cities);

            $response->encode($this->parser)->output();
        } catch (\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }

    public function post(){
        try{
            $request = new Request();
            $city = $request->getBody();
            $this->parser->decode($city);
            if (isset($city['id']))
            {
                unset($city['id']);
            }

            if(!isset($city['name'])){
                $this->_throwBadRequest('city name is required field');
            }
          
            $city['id'] = $this->cities_model->createCity($city);
          
            $response = new Response($city);
            $response->encode($this->parser)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function getByEitherIdOrName($id, $name){
        try{
            $result = $this->cities_model->findCityBy($id, $name);
            if(empty($result)){
                $this->_throwRecordNotFound();
            }
            $response = new Response($result);
            $response->encode($this->parser)->singleEntry(TRUE)->output();
        }catch(\Exception $exception){
            exit($this->_handleException($exception));
        }
    }

    public function delete($id){
        try{
            $this->cities_model->delete($id);

            $response = new Response([
                'code' => 200,
                'message' => 'Record was deleted successfully!'
            ]);

            $response->output();
        }catch(\Exception $exception){
            exit($this->_handleException($exception));
        }
    }
}
?>