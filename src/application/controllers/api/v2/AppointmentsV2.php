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

require_once __DIR__ . '/../v1/Appointments.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Appointments Controller v2
 *
 * @package Controllers
 * @subpackage API
 */
class AppointmentsV2 extends Appointments {
    /**
     * Appointments Resource Parser
     *
     * @var \EA\Engine\Api\V2\Parsers\AppointmentsV2
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('services_model');
        $this->load->model('/v2/appointments_model_v2');
        $this->load->model('/v2/user_model_v2');
        $this->load->model('/v2/attachments_model_v2');
        $this->load->model('/v2/attendants_model_v2');
        $this->load->model('/v2/services_model_v2');
        $this->parser = new \EA\Engine\Api\V2\Parsers\AppointmentsV2;
    }

    /**
     * GET API Method
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
            $services_model = $this->services_model_v2;

            if ($_GET['id_user_integrated'] !== NULL) {
                // Get user id that have id_integrated = id_user_integrated in table ea_users
                $user = $user_model->find_by_id_integrated($_GET['id_user_integrated']);
                if (isset($user)) {
                    $appointments = $appointments_model->get_batch($conditions, array_key_exists('aggregates', $_GET), $user[0]->id, NULL, $appointments_model::CUSTOMER);
                }
            } else {
                if ($_GET['id_provider_integrated'] !== NULL && $_GET['id_services_integrated'] !== NULL) {
                    // Get user id that have id_integrated = id_provider_integrated in table ea_users
                    $user = $user_model->find_by_id_integrated($_GET['id_provider_integrated']);
                    if (isset($user)) {
                        if ($_GET['id_services_integrated'] !== NULL) {
                            // Get service id that have id_integrated = id_services_integrated in table ea_services
                            $service = $services_model->find_by_id_integrated($_GET['id_services_integrated']);
                            if (isset($service)) {
                                $appointments = $appointments_model->get_batch($conditions, array_key_exists('aggregates', $_GET), $user[0]->id, $service->id, $appointments_model::PROVIDER_SERVICE);
                            }
                        }

                    }
                } else {
                    if ($_GET['id_provider_integrated'] !== NULL) {
                        // Get user id that have id_integrated = id_provider_integrated in table ea_users
                        $user = $user_model->find_by_id_integrated($_GET['id_provider_integrated']);
                        if (isset($user)) {
                            $appointments = $appointments_model->get_batch($conditions, array_key_exists('aggregates', $_GET), $user[0]->id, NULL, $appointments_model::PROVIDER);
                        }
                    } else if ($_GET['id_services_integrated'] !== NULL) {
                        // Get service id that have id_integrated = id_services_integrated in table ea_services
                        $service = $services_model->find_by_id_integrated($_GET['id_services_integrated']);
                        if (isset($service)) {
                            $appointments = $appointments_model->get_batch($conditions, array_key_exists('aggregates', $_GET), NULL, $service[0]->id, $appointments_model::SERVICE);
                        }

                    }
                }
            }

            if ($id !== NULL && count($appointments) === 0) {
                $this->_throwRecordNotFound();
            }

            $result = array();
            if (count($appointments) > 0) {
                foreach ($appointments as $appointment) {
                    $attachments = $this->attachments_model_v2->get_batch($appointment['id']);
                    if (count($attachments) > 0) {
                        $appointment['attachment'] =  $attachments;
                    }
                    array_push($result, $appointment);
                }
            }

            $response = new Response($result);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id)
                ->output();

        } catch (\Exception $exception) {
            exit($this->_handleException($exception));
        }
    }

    /**
     * POST API Method
     */
    public function post()
    {
        try
        {
            // Insert the appointment to the database. 
            $request = new Request();
            $appointment = $request->getBody();
            $this->parser->decode($appointment);

            if (isset($appointment['id']))
            {
                unset($appointment['id']);
            }

            $exist = $this->appointments_model_v2->find_by_id_integrated($appointment['id_integrated']);
            if (isset($exist)) {
                throw new \EA\Engine\Api\V1\Exception('$exist existed in DB: ' . $exist, 409, 'Duplicated');
            }

            $id = $this->appointments_model_v2->add($appointment);

            // add attachment
            if ( ! empty($appointment['attachment'])) {
                $this->attachments_model_v2->save_attachments($id, $appointment['attachment']);
            }

            // add attendants
            if ( ! empty($appointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $appointment['attendants']);
            }

            // Fetch the new object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $status = new NonEmptyText('201 Created');
            $response->encode($this->parser)->singleEntry(TRUE)->output($status);
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    /**
     * PUT API Method
     *
     * @param int $id The record ID to be updated.
     */
    public function put($id)
    {
        try
        {
            // Update the appointment record. 
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }
            $request = new Request();
            $updatedAppointment = $request->getBody();
            $baseAppointment = $batch[0];
            $this->parser->decode($updatedAppointment, $baseAppointment);
            $updatedAppointment['id'] = $id;
            $id = $this->appointments_model_v2->add($updatedAppointment);
            // Update the appointment attachments
            if ( ! empty($updatedAppointment['attachment'])) {
                $this->appointments_model_v2->save_attachments($id, $updatedAppointment['attachment']);
            }
            // Update the appointment attendants
            if ( ! empty($updatedAppointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $updatedAppointment['attendants']);
            }
            // Fetch the updated object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function updateAppointmentByIdIntegrated($id_integrated){
        try
        {
            // Update the appointment record. 
            $batch = $this->appointments_model_v2->get_batch("id_integrated = '" . $id_integrated ."'");

            $id = $this->appointments_model_v2->find_id_by_id_integrated($id_integrated);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedAppointment = $request->getBody();
            $baseAppointment = $batch[0];
            $this->parser->decode($updatedAppointment, $baseAppointment);
            $updatedAppointment['id'] = $id;
            $id = $this->appointments_model_v2->add($updatedAppointment);

            // Update the appointment attachments
            if ( ! empty($updatedAppointment['attachment'])) {
                $this->appointments_model_v2->save_attachments($id, $updatedAppointment['attachment']);
            }

            // Update the appointment attendants
            if ( ! empty($updatedAppointment['attendants'])) {
                $this->attendants_model_v2->save_attendants($id, $updatedAppointment['attendants']);
            }

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
            $response = new Response($batch);
            $response->encode($this->parser)->singleEntry($id)->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    /**
     * DELETE API Method
     *
     * @param int $id The record ID to be deleted.
     */
    public function delete($id)
    {
        try
        {
            $this->appointments_model_v2->delete($id);
            $this->attachments_model_v2->remove_attachment($id);
            $this->attendants_model_v2->remove_attendants($id);

            $response = new Response([
                'code' => 200,
                'message' => 'Record was deleted successfully!'
            ]);

            $response->output();
        }
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }

    public function updateAppointmentStatus($id_integrated) {
        try
        {
            if($id_integrated !== null && $_GET['status'] !==null)
            {
                $batch = $this->appointments_model_v2->get_batch("id_integrated = '" . $id_integrated ."'");
                $id = $this->appointments_model_v2->find_id_by_id_integrated($id_integrated);
                $this->appointments_model_v2->updateAppointmentStatus($id, $_GET['status']);
                $batch = $this->appointments_model_v2->get_batch('id = ' . $id);
                $response = new Response($batch);
                $status = new NonEmptyText('200 OK');
                $response->encode($this->parser)->singleEntry(TRUE)->output($status);
            } 
            else 
            {
                $this->_throwRecordNotFound();
            }
        } 
        catch (\Exception $exception)
        {
            exit($this->_handleException($exception));
        }
    }
}
