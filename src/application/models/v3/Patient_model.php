<?php defined('BASEPATH') OR exit('No direct script access allowed');

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

        if (empty($patient_integrated['id_user_integrated']))
        {
            throw new Exception('id_user_integrated is required field');
        }

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
            throw new Exception('Could not insert integrated_users_patients record.');
        }
    }

    public function get($id_user_integrated) {
        return $this->db->select('*')->from('ea_users')
        ->join('integrated_users_patients', 'integrated_users_patients.id_patients  = ea_users.id')
        ->where('integrated_users_patients.id_user_integrated ', $id_user_integrated)->get()->result_array();
    }

    public function getPatient($id_user_integrated, $id_integrated) {
        if(empty($id_user_integrated)) {
            throw new Exception('Field $id_user_integrated is required');
        }
 
        if(empty($id_integrated)) {
            throw new Exception('Field $id_integrated is required');
        }
 
        return $this->db->select('*')->from('ea_users')
        ->join('integrated_users_patients', 'integrated_users_patients.id_patients  = ea_users.id')
        ->where('integrated_users_patients.id_user_integrated ', $id_user_integrated)
        ->where('ea_users.id_integrated  ', $id_integrated)
        ->get()->result_array();
    }
}
?>