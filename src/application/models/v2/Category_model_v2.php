<?php defined('BASEPATH') OR exit('No direct script access allowed');
use \EA\Engine\Api\V2\DuplicateException;
class Category_model_v2 extends CI_Model {

    public function get($id) {
        if ( ! is_numeric($id))
        {
            throw new Exception('Invalid argument type given $id: ' . $id);
        }

        $result = $this->db->get_where('integrated_categories', ['id' => $id]);

        if ($result->num_rows() == 0)
        {
            throw new Exception('Category record does not exist.');
        }

        return $result->row_array();
    }

    public function exists($category) {
        if(!isset($category['name']) ||
         !isset($category['id_integrated'])) {
            throw new Exception('Record does not exist' . print_r($category, TRUE));
         }
         $num_rows = $this->db->get_where('integrated_categories', [
            'name' => $category['name'],
            'id_integrated' => $category['id_integrated']
        ])->num_rows();

        return ($num_rows > 0) ? TRUE : FALSE;
    }

    public function validate($category) {
        $this->load->helper('data_validation');
        // incase request has provided id
        if (isset($category['id']))
        {
            $num_rows = $this->db->get_where('integrated_categories', ['id' => $category['id']])
                ->num_rows();
            if ($num_rows == 0)
            {
                throw new Exception('Provided id does not exist in the database.');
            }
        }
        // Incase request has provided id_integrated
        if (empty($category['id_integrated']))
        {
            throw new Exception('id_integrated is required field');

        }
        // Check for required fields
        if ($category['name'] == '')
        {
            throw new Exception('Name is required '
                . print_r($category, TRUE));
        }
    }

    public function get_batch($where_clause = '')
    {
    
        if ($where_clause != '')
        {
            $this->db->where($where_clause);
        }

        $categories = $this->db->get('integrated_categories')->result_array();

        return $categories;
    } 
   
    public function add($category)
    {
        $this->validate($category);

        if ($this->exists($category) && ! isset($category['id']))
        {
            // $category['id'] = $this->find_record_id($category);
            throw new DuplicateException('Duplicate record in database');
        }

        if ( ! isset($category['id']))
        {
            $category['id'] = $this->_insert($category);
        }
        else
        {
            $category['id'] = $this->_update($category);
        }

        return (int)$category['id'];
    }

    protected function _insert($category)
    {
        if ( ! $this->db->insert('integrated_categories', $category))
        {
            throw new Exception('Could not insert category record.');
        }

        return (int)$this->db->insert_id();
    }

    protected function _update($category)
    {
        $this->db->where('id', $category['id']);
        if ( ! $this->db->update('integrated_categories', $category))
        {
            throw new Exception('Could not update category record.');
        }
    }

    public function getCategoriesByServiceId($id_service_integrated) {

        $serviceId = $this->db->get_where('ea_services',['id_integrated' => $id_service_integrated])->row()->id;
        if(empty($serviceId)) {
            throw new Exception('Can not find any record with id_service_integrated');
        }
        
        $categories = $this->db->select('*')->from('integrated_categories')
        ->join('integrated_services_categories','integrated_services_categories.id_categories = integrated_categories.id')
        ->where('integrated_services_categories.id_services', $serviceId)->get()->result_array();
        return $categories;
    }

    public function getCategoriesByProviderId($id_provider_integrated) {

        $providerId = $this->db->get_where('ea_users',['id_integrated' => $id_provider_integrated])->row()->id;
        if(empty($providerId)) {
            throw new Exception('Can not find any record with id_provider_integrated');
        }
        
        $categories = $this->db->select('*')->from('integrated_categories')
        ->join('integrated_provider_categories','integrated_provider_categories.id_categories = integrated_categories.id')
        ->where('integrated_provider_categories.id_providers', $providerId)->get()->result_array();
        return $categories;
    }

    public function find_record_id($category)
    {
        if ( ! isset($category['id_integrated']))
        {
            throw new Exception('id_integrated was not provided:' . print_r($category, TRUE));
        }

        $result = $this->db
            ->select('integrated_categories.id')
            ->from('integrated_categories')
            ->where('integrated_categories.id_integrated', $category['id_integrated'])
            ->where('integrated_categories.name', $category['name'])
            ->get();

        if ($result->num_rows() == 0)
        {
            throw new Exception('Could not find category record id.');
        }

        return (int)$result->row()->id;
    }
}

?>