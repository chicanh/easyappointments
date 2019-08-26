<?php defined('BASEPATH') OR exit('No direct script access allowed');
use \EA\Engine\Api\V2\DbHandlerException;

    class Patient_model extends CI_Model {

        public function add($patient_integrated)
        {
            $this->validate($patient_integrated);
    
            if ($this->exists($patient_integrated))
            {
                throw new Exception('Record existed in database');
            }
            
            $this->_insert($patient_integrated);
        }

    

    public function validate($patient_integrated) {
        $this->load->helper('data_validation');

        if (empty($patient_integrated['id_patients']))
        {
            throw new Exception('id_user_integrated is required field');
        }
    }

    public function exists($patient_integrated) {
         $num_rows = $this->db->get_where('integrated_users_patients', [
            'id_user_integrated' => $patient_integrated['id_user_integrated'],
            'id_patients' => $patient_integrated['id_patients']
        ])->num_rows();

        return ($num_rows > 0) ? TRUE : FALSE;
    }

    protected function _insert($patient_integrated)
    {
        if ( ! $this->db->insert('integrated_users_patients', $patient_integrated))
        {
            DbHandlerException::handle($this->db->error());
        }
    }

    public function get($id_user_integrated, $id_service_integrated, $page, $size, $isAggregates) {
        $offset = ($page - 1 ) * $size;
        $patients = $this->getPatientWithIdUserAndIdServiceQuery($id_user_integrated, $id_service_integrated)->limit($size, $offset)->get()->result_array();
        if($isAggregates){
            foreach ($patients as &$patient) {
                $patient =  $this->get_aggregates($patient);
            }
           
        }
        $result['total'] = $this->getPatientWithIdUserAndIdServiceQuery($id_user_integrated, $id_service_integrated)->get()->result_id->num_rows;
        $result['patients'] = $patients;
        return $result;
    }

    public function getPatient($id_user_integrated,$id_service_integrated, $id_integrated, $isAggregates) {
 
        if(empty($id_integrated)) {
            throw new Exception('Field $id_integrated is required');
        }
 
        $this->db->select('*')->from('ea_users')->join('integrated_users_patients', 'integrated_users_patients.id_patients  = ea_users.id');
        if(!empty($id_user_integrated)){
            $this->db->where('integrated_users_patients.id_user_integrated ', $id_user_integrated);
        }
        if(!empty($id_service_integrated)){
            $this->db->where('integrated_users_patients.id_service_integrated ', $id_service_integrated);
        }


        $patients = $this->db->where('ea_users.id_integrated  ', $id_integrated)->get()->result_array();
        if($isAggregates){
            foreach ($patients as &$patient) {
                $patient =  $this->get_aggregates($patient);
            }
           
        }
        return $patients;
    }

    private function getPatientWithIdUserAndIdServiceQuery($id_user_integrated, $id_service_integrated){
        $this->db->select('*')->from('ea_users')->join('integrated_users_patients', 'integrated_users_patients.id_patients  = ea_users.id');
        if(!empty($id_user_integrated)){
            $this->db->where('integrated_users_patients.id_user_integrated ', $id_user_integrated);
        }
        if(!empty($id_service_integrated)){
            $this->db->where('integrated_users_patients.id_service_integrated ', $id_service_integrated);
        }
        return $this->db;
    }

    public function get_aggregates(array $patients)
    {
        $patients['city_id'] = $this->db->select('id, name')->from('integrated_cities')->where('id', $patients['city_id'])->get()->result_array()[0];
        $patients['district_id'] = $this->db->select('id, name')->from('integrated_districts')->where('id', $patients['district_id'])->get()->result_array()[0];
        $patients['ward_id'] = $this->db->select('id, name')->from('integrated_wards')->where('id', $patients['ward_id'])->get()->result_array()[0];
        return $patients;
    }

    public function update($patient_integrated){
        $this->db->update('integrated_users_patients', $patient_integrated, array('id_patients' => $patient_integrated['id_patients']));
        return $patient_integrated;
    }
}
?>