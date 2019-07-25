<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/API_V1_Controller.php';
use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\UnsignedInteger;
class Districts extends API_V1_Controller {
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cities_model');
        $this->load->model('districts_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Districts;
    }

    public function get(){
        $id = $this->input->get('id');
        $id_city = $this->input->get('city');
        if(!isset($id) && !isset($id_city)){
            $this->getAll();
        }else{
            $this->getByIdAndNameAndCity($id, $id_city);
        }
       
    }

    private function getAll(){
        try{
            $district = $this->districts_model->getAllDistricts();
         
            $response = new Response($district);
        
            $response->encode($this->parser)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function post(){
        try{
            $request = new Request();
            $district = $request->getBody();
            $this->parser->decode($district);
            if (isset($district['id']))
            {
                unset($district['id']);
            }
            $this->validateRequestBody($district);
            $district['id'] = $this->districts_model->createDistrict($district);
            
            $response = new Response($district);
            $response->encode($this->parser)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    private function validateRequestBody($district){ 
        if(!isset($district['name'])){
            $this->_throwBadRequest('District name is required field');
        }
        $cityId = $district['id_city'];
        if(empty($this->cities_model->findCityById($cityId))){
            $this->_throwBadRequest('City id "'.$cityId.'" is not found');
        }
    }

    public function getByIdAndNameAndCity($id, $id_city){
        try{
            $result = $this->districts_model->findDistrictBy($id, $id_city);
            if(empty($result)){
                $this->_throwRecordNotFound();
            }       
            $response = new Response($result);
            $response->encode($this->parser)->output();
        }catch(\Exception $exception){
            exit($this->_handleException($exception));
        }
    }

    public function delete($id){
        try{
            $this->districts_model->delete($id);

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