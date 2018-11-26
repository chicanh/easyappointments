<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @updater     Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/../v1/API_V1_Controller.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Attendants Controller
 *
 * @package Controllers
 * @subpackage API
 */
class AttendantsV2 extends API_V1_Controller {

    const PROVIDER = 2;
    const CUSTOMER = 3;

    /**
     * Attendants Resource Parser
     *
     * @var \EA\Engine\Api\V2\Parsers\AttendantsV2
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/appointments_model');
        $this->load->model('/v2/appointments_model_v2');
        $this->load->model('/v2/user_model_v2');
        $this->load->model('/v2/attendants_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\AttendantsV2;
    }

    /**
     * GET API Method
     * Get all attendants (distinct) of all appointments that made by a specific user
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try {
            $conditions = [
                'is_unavailable' => FALSE
            ];

            if ($id !== NULL) {
                $conditions['id_integrated'] = $id;
            }

            $appointments = array();
            $user_model = $this->user_model_v2;
            $appointments_model = $this->appointments_model_v2;
            $attendants_model = $this->attendants_model_v2;

            if ($_GET['id_user_integrated'] !== NULL) {
                // Get user id that have id_integrated = id_user_integrated in table ea_users
                $user = $user_model->find_by_id_integrated($_GET['id_user_integrated']);
                if (isset($user)) {
                    if ($user->id_roles == self::CUSTOMER) {
                        $appointments = $appointments_model->get_by_user($conditions, array_key_exists('aggregates', $_GET), $user[0]->id, $appointments_model::CUSTOMER);
                    } else if ($user->id_roles == self::PROVIDER) {
                        $appointments = $appointments_model->get_by_user($conditions, array_key_exists('aggregates', $_GET), $user[0]->id, $appointments_model::PROVIDER);
                    }
                }
            }

            $attendants = array();

            if (count($appointments) > 0) {
                $appointmentIds = array_column($appointments, 'id');
                $userIds = $attendants_model->get_users_by_appointments($appointmentIds);
                if (count($userIds) > 0) {
                    foreach ($userIds as $id) {
                        $user = $user_model->find_by_id($id);
                        array_push($attendants, $user);
                    }
                }
            }

            $response = new Response($attendants);

            $response->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->output();

        } catch (\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }

    // Todo: Get attendants of an appointment
    // Todo: Delete attendant of an user from an appointment

}
