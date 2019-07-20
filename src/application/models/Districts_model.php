<?php defined('BASEPATH') OR exit('No direct script access allowed');
    define("DISTRICT_TABLE", "integrated_districts");
    class Districts_model extends CI_Model {
        public function getAllDistricts(){
           return $this->db->select('*')->from(DISTRICT_TABLE)->get()->result_array();
        }

        public function createDistrict($district){
            if ( ! $this->db->insert(DISTRICT_TABLE, $district))
            {
                throw new Exception('Could not insert into district table');
            }
            return (int)$this->db->insert_id();
        }

        public function findDistrictBy($id = null, $cityId = null){

            $this->db->select(' integrated_districts.id as id,
                                integrated_districts.name as district,
                                integrated_cities.id as id_city,
                                integrated_cities.name as city')
            
            ->from(DISTRICT_TABLE)
            ->join('integrated_cities', 'integrated_districts.id_city  = integrated_cities.id');
            if(isset($cityId)){
                $this->db->where(DISTRICT_TABLE.'.id_city', $cityId);
            }
            if(isset($id)){
                $this->db->where(DISTRICT_TABLE.'.id', $id);
            }
            $result = $this->db->get()->result_array();
            
            return $result;
        }

        public function delete($id){
            $this->db->where('id', $id);
            if ( ! $this->db->delete(DISTRICT_TABLE))
            {
                throw new Exception('Could not delete district with id '.$id);
            }
        }
    }

?>