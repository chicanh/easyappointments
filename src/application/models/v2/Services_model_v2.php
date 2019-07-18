<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Services Model v2
 *
 * @package Models
 */
class Services_Model_V2 extends Services_Model {

    /**
     * Find service by id_integrated
     * @param $idIntegrated
     * @return string
     * @throws Exception
     */
    public function find_by_id_integrated($idIntegrated)
    {
        if ( ! isset($idIntegrated))
        {
            throw new Exception('Service idIntegrated is not provided: ' . print_r($idIntegrated, TRUE));
        }

        $query = $this->db->get_where('ea_services', ['id_integrated' => $idIntegrated ]);

        $service = $query->num_rows() > 0 ? $query->result() : NULL;

        return $service;
    }
    public function get_row($id_service_integrated)
    {
        return $this->db->get_where('ea_services', ['id_integrated' => $id_service_integrated])->row_array();
    }

    public function get_batch($where_clause = NULL)
    {
        if ($where_clause != NULL)
        {
            $this->db->where($where_clause);
        }
        $batch = $this->db->get('ea_services')->result_array(); 
        foreach ($batch as &$service)
        {
            $categories = $this->db->get_where('integrated_services_categories', ['id_services' => $service['id']]) -> result_array();
            $service['categories'] = [];
            foreach ($categories as $category)
            {
                $service['categories'][] = $category['id_categories'];
            }
        }
        return $batch;
    }

    public function add($service) {
        parent::validate($service);

        if ( ! isset($service['id']))
        {
            $service['id'] = $this->_insert($service);
        }
        else
        {
            $this->_update($service);
        }

        return (int)$service['id'];
    }

    protected function _insert($service)
    {
        $categories = $service['categories'];
        unset($service['categories']);

        if ( ! $this->db->insert('ea_services', $service))
        {
            throw new Exception('Could not insert service record.');
	}

	if(isset($categories)) 
	{
	   $this->save_categories($categories, (int)$this->db->insert_id());
	}

        return (int)$this->db->insert_id();
    }

    public function save_categories($categories, $service_id)
    {
        // Validate method arguments.
        if ( ! is_array($categories))
        {
            throw new Exception('Invalid argument type $categories: ' . $categories);
        }

        if ( ! is_numeric($service_id))
        {
            throw new Exception('Invalid argument type $service_id: ' . $service_id);
        }

        foreach ($categories as $category_id)
        {
            $category_services = [
                'id_services' => $service_id,
                'id_categories' => $category_id
            ];
            // Check if record exists in db.
            if ($this->db->get_where('integrated_services_categories', ['id_categories' => $category_id, 'id_services' => $service_id])
            ->num_rows() == 0)
            {
                $this->db->insert('integrated_services_categories', $category_services);
            }
            
        }
    }

    protected function _update($service)
    {
        $categories = $service['categories'];
        unset($service['categories']);
        $this->db->where('id', $service['id']);
        if ( ! $this->db->update('ea_services', $service))
        {
            throw new Exception('Could not update service record');
        }

        $this->save_categories($categories, $service['id']);

        return (int)$this->db->insert_id();
    }

}
