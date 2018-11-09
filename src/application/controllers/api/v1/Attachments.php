<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @updater     Davido Team
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/API_V1_Controller.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Api\V1\Request;
use \EA\Engine\Types\NonEmptyText;

/**
 * Attachments Controller
 *
 * @package Controllers
 * @subpackage API
 */
class Attachments extends API_V1_Controller {
    /**
     * Attachments Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Attachments
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('attachments_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Attachments;
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

            $attachments = $this->attachments_model->get_attachment($conditions['id']);

            if ($id !== NULL && count($attachments) === 0)
            {
                $this->_throwRecordNotFound();
            }

            $response = new Response($attachments);

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

}
