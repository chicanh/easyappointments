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
 * Services Providers Model v2
 *
 * @package Models
 */
class Services_Providers_Model_V2 extends CI_Model {

    /**
     * Find service by id_integrated
     * @param $serviceId
     * @return string
     * @throws Exception
     */
    public function getProviderByServiceId($serviceId, $providerId)
    {
        if ( ! isset($serviceId))
        {
            throw new Exception('$serviceId is not provided: ' . print_r($serviceId, TRUE));
        }

        if ( ! isset($providerId))
        {
            throw new Exception('$providerId is not provided: ' . print_r($providerId, TRUE));
        }

        $query = $this->db->get_where('ea_services_providers', ['id_services' => $serviceId, 'id_users' => $providerId ]);

        if ($query->num_rows() == 0)
        { // Check if id_integrated exists in ea_$services
            throw new \EA\Engine\Api\V1\Exception('$serviceId does not exist in DB: ' . $serviceId, 404, 'Not Found');
        }

        $services_providers = $query->row();

        return $services_providers;
    }

    public function getProvidersByServiceId($serviceId)
    {
        if ( ! isset($serviceId))
        {
            throw new Exception('$serviceId is not provided: ' . print_r($serviceId, TRUE));
        }

        $query = $this->db->get_where('ea_services_providers', ['id_services' => $serviceId ]);

        if ($query->num_rows() == 0)
        { // Check if id_integrated exists in ea_$services
            throw new \EA\Engine\Api\V1\Exception('$serviceId does not exist in DB: ' . $serviceId, 404, 'Not Found');
        }

        $services_providers = $query->result_array();

        return $services_providers;
    }

}
