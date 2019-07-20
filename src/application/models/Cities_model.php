<?php defined('BASEPATH') OR exit('No direct script access allowed');
    define("CITY_TABLE", "integrated_cities");
    class Cities_model extends CI_Model {
        public function getAllCities(){
           return $this->db->select('*')->from(CITY_TABLE)->get()->result_array();
        }

        public function createCity($city){
            if ( ! $this->db->insert(CITY_TABLE, $city))
            {
                throw new Exception('Could not insert into city table');
            }
            return (int)$this->db->insert_id();
        }

        public function findCityBy($id = null, $city = null){
             $this->db->select('*')->from(CITY_TABLE);
            if(!empty($id)){
                $this->db->where('id', $id);
            }
            if(!empty($city)){
                $this->db->where('name', $city);
            }
            
            $result = $this->db->get()->result_array();
            return $result;
        }

        public function delete($id){
            $this->db->where('id', $id);
            if ( ! $this->db->delete(CITY_TABLE))
            {
                throw new Exception('Could not delete city with id '.$id);
            }
        }

        public function mappingAggregateCityIfAny($isAggregatesRequest, $result){
            if($isAggregatesRequest && !empty($result[0]['id_city'])){
                $result[0]['city'] = $this->findCityBy($result[0]['id_city']);
                unset($result[0]['id_city']);
            }
            return $result;
        }
    }

?>