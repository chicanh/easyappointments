<?php defined('BASEPATH') OR exit('No direct script access allowed');
    define("WARD_TABLE", "integrated_wards");
    class Wards_model extends CI_Model {
        public function getAllWards(){
           return $this->db->select('*')->from(WARD_TABLE)->get()->result_array();
        }

        public function createWard($Ward){
            if ( ! $this->db->insert(WARD_TABLE, $Ward))
            {
                throw new Exception('Could not insert into Ward table');
            }
            return (int)$this->db->insert_id();
        }

        public function findWardBy($id = null, $city = null, $district = null){

            $this->db
            ->select('integrated_wards.id as id, 
                               integrated_wards.name as name')
            ->from(WARD_TABLE)
            ->join('integrated_districts', 'integrated_districts.id  = integrated_wards.id_district')
            ->join('integrated_cities', 'integrated_districts.id_city  = integrated_cities.id');
            if(isset($district)){
                $this->db->where('integrated_wards.id_district', $district);
            }
            if(isset($city)){
                $this->db->where('integrated_districts.id_city', $city);
            }
            if(isset($id)){
                $this->db->where('integrated_wards.id', $id);
            }
           
            $result = $this->db->get()->result_array();
            return $result;
        }

        public function delete($id){
            $this->db->where('id', $id);
            if ( ! $this->db->delete(WARD_TABLE))
            {
                throw new Exception('Could not delete Ward with id '.$id);
            }
        }


    }

?>