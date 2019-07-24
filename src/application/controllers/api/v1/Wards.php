<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/API_V1_Controller.php';
use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\UnsignedInteger;
class Wards extends API_V1_Controller {
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cities_model');
        $this->load->model('districts_model');
        $this->load->model('wards_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Wards;
    }

    public function get(){
        $city = $this->input->get('city');
        $district = $this->input->get('district');
        $id_wards = $this->input->get('id');
        if(!isset($city) && !isset($district) && !isset($id_wards)){
            $this->getAll();
        } else {
            $this->getAllByCityAndDistrict($id_wards, $city, $district);
        }
       
    }

    public function getAll(){
        try{
            $ward = $this->wards_model->getAllWards();
         
            $response = new Response($ward);
        
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
            $ward = $request->getBody();
            $this->parser->decode($ward);
            if (isset($ward['id']))
            {
                unset($ward['id']);
            }
            $this->validateRequestBody($ward);
            $ward['id'] = $this->wards_model->createWard($ward);
            
            $response = new Response($ward);
            $response->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    private function validateRequestBody($ward){ 
        if(!isset($ward['name'])){
            $this->_throwBadRequest('ward name is required field');
        }
        $districtId = $ward['id_district'];
        if(empty($this->districts_model->findDistrictBy($districtId))){
            $this->_throwBadRequest('District id "'.$districtId.'" is not found');
        }
    }

    public function getAllByCityAndDistrict($id_wards, $city, $district){
        try {
            $result = $this->wards_model->findWardBy($id_wards, $city, $district);
            if(empty($result)){
                $this->_throwRecordNotFound();
            }
            $response = new Response($result);
            $response->output();
        }catch(\Exception $exception){
            exit($this->_handleException($exception));
        }
    }

    public function delete($id){
        try{            
            $this->wards_model->delete($id);

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