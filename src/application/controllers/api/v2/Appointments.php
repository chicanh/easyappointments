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
 * Appointments Controller v2
 *
 * @package Controllers
 * @subpackage API
 */
class Appointments extends API_V1_Controller {
    /**
     * Appointments Resource Parser
     *
     * @var \EA\Engine\Api\V2\Parsers\Appointments
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v2/appointments_model');
        $this->parser = new \EA\Engine\Api\V2\Parsers\Appointments;
        $this->load->model('/v2/attachments_model');
    }

    /**
     * GET API Method
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try
        {
            $conditions = [
                'is_unavailable' => FALSE
            ];

            if ($id !== NULL)
            {
                $conditions['id'] = $id;
            }

            $appointments = $this->appointments_model->get_batch($conditions, array_key_exists('aggregates', $_GET));

            if ($id !== NULL && count($appointments) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $response = new Response($appointments);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id)
                ->output();

        }
        catch (\Exception $exception)
        {
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

            $id = $this->appointments_model->add($appointment);

            // add attachment
            if ( ! empty($appointment['attachment'])) {
                $this->attachments_model->save_attachments($id, $appointment['attachment']);
            }

            // Fetch the new object from the database and return it to the client.
            $batch = $this->appointments_model->get_batch('id = ' . $id);
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
            $batch = $this->appointments_model->get_batch('id = ' . $id);

            if ($id !== NULL && count($batch) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $request = new Request();
            $updatedAppointment = $request->getBody();
            $baseAppointment = $batch[0];
            $this->parser->decode($updatedAppointment, $baseAppointment);
            $updatedAppointment['id'] = $id;
            $id = $this->appointments_model->add($updatedAppointment);

            // Update the appointment attachments
            if ( ! empty($updatedAppointment['attachment'])) {
                $this->parser->decode($updatedAppointment['attachment']);
                $this->attachments_model->set_attachment($id, $updatedAppointment['attachment']);
            }

            // Fetch the updated object from the database and return it to the client.
            $batch = $this->appointments_model->get_batch('id = ' . $id);
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
            $this->appointments_model->delete($id);
            $this->attachments_model->remove_attachment($id);

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
}
