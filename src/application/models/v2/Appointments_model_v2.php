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
 * Appointments Model v2
 *
 * @package Models/v2
 */
class Appointments_Model_V2 extends Appointments_Model {

    const CUSTOMER = 'customer';
    const PROVIDER = 'provider';
    const SERVICE = 'service';
    const PROVIDER_SERVICE = 'provider_service';
    const CUSTOMER_SERVICE = 'customer_service';

    /**
     * Insert a new appointment record to the database.
     *
     * @param array $appointment Associative array with the appointment's data. Each key has the same name with the
     * database fields.
     *
     * @return int Returns the id of the new record.
     *
     * @throws Exception If appointment record could not be inserted.
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->model('/v3/patient_model');
        $this->load->model('/v2/services_model_v2');
        $this->load->model('/v2/user_model_v2');
    }

    protected function _insert($appointment)
    {
        $appointment['book_datetime'] = date('Y-m-d H:i:s');
        $appointment['hash'] = $this->generate_hash();
        if (isset($appointment['attachment']))
        {
            unset($appointment['attachment']);
        }
        if (isset($appointment['attendants']))
        {
            unset($appointment['attendants']);
        }

        if ( ! $this->db->insert('ea_appointments', $appointment))
        {
            throw new Exception('Could not insert appointment record.');
        }

        return (int)$this->db->insert_id();
    }

    /**
     * Update an existing appointment record in the database.
     *
     * The appointment data argument should already include the record ID in order to process the update operation.
     *
     * @param array $appointment Associative array with the appointment's data. Each key has the same name with the
     * database fields.
     *
     * @throws Exception If appointment record could not be updated.
     */
    protected function _update($appointment)
    {
        if (isset($appointment['attachment']))
        {
            unset($appointment['attachment']);
        }
        if (isset($appointment['attendants']))
        {
            unset($appointment['attendants']);
        }

        $this->db->where('id', $appointment['id']);
        if ( ! $this->db->update('ea_appointments', $appointment))
        {
            throw new Exception('Could not update appointment record.');
        }
    }

    /**
     * Validate appointment data before the insert or update operations are executed.
     *
     * @param array $appointment Contains the appointment data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If appointment validation fails.
     */
    public function validate($appointment)
    {
        $this->load->helper('data_validation');

        // If a appointment id is given, check whether the record exists
        // in the database.
        if (isset($appointment['id']))
        {
            $num_rows = $this->db->get_where('ea_appointments',
                ['id' => $appointment['id']])->num_rows();
            if ($num_rows == 0)
            {
                throw new \EA\Engine\Api\V1\Exception('Provided appointment id does not exist in the database.', 404, 'Not Found');
            }
        }

        // Check if appointment dates are valid.
        if ( ! validate_mysql_datetime($appointment['start_datetime']))
        {
            throw new Exception('Appointment start datetime is invalid.');
        }

        if ( ! validate_mysql_datetime($appointment['end_datetime']))
        {
            throw new Exception('Appointment end datetime is invalid.');
        }

        // Check if the provider's id is valid.
        $num_rows = $this->db
            ->select('*')
            ->from('ea_users')
            ->join('ea_roles', 'ea_roles.id = ea_users.id_roles', 'inner')
            ->where('ea_users.id', $appointment['id_users_provider'])
            ->where('ea_roles.slug', DB_SLUG_PROVIDER)
            ->get()->num_rows();
        if ($num_rows == 0)
        {
            throw new \EA\Engine\Api\V1\Exception('Appointment provider id is invalid.', 404, 'Not Found');
        }

        if ($appointment['is_unavailable'] == FALSE)
        {
            // Check if the customer's id is valid.
            $num_rows = $this->db
                ->select('*')
                ->from('ea_users')
                ->join('ea_roles', 'ea_roles.id = ea_users.id_roles', 'inner')
                ->where('ea_users.id', $appointment['id_users_customer'])
                ->where('ea_roles.slug', DB_SLUG_CUSTOMER)
                ->get()->num_rows();
            if ($num_rows == 0)
            {
                throw new \EA\Engine\Api\V1\Exception('Appointment customer id is invalid.', 404, 'Not Found');
            }

            // Check if the service id is valid.
            $num_rows = $this->db->get_where('ea_services',
                ['id' => $appointment['id_services']])->num_rows();
            if ($num_rows == 0)
            {
                throw new \EA\Engine\Api\V1\Exception('Appointment service id is invalid.', 404, 'Not Found');
            }
        }

        return TRUE;
    }

    /**
     * Get all, or specific records from appointment's table.
     *
     * @example $this->Model->getBatch('id = ' . $recordId);
     *
     * @param string $where_clause (OPTIONAL) The WHERE clause of the query to be executed. DO NOT INCLUDE 'WHERE'
     * KEYWORD.
     *
     * @param bool $aggregates (OPTIONAL) Defines whether to add aggregations or not.
     * @param $userId (OPTIONAL) The user id (provider or customer)
     * @param $serviceId (OPTIONAL) The service id
     * @param $type (OPTIONAL) 'provider' or 'customer' or 'services' or 'provider_services'
     *
     * @return array Returns the rows from the database.
     * @throws Exception
     */
    public function get_batch($where_clause = '', $aggregates = FALSE, $userId = NULL, $serviceId = NULL, $type = '')
    {
        if ($where_clause != '') {
            $this->db->where($where_clause);
        }
        switch ($type) {
            case self::CUSTOMER:
                $this->db->where('id_users_customer', $userId);
                break;
            case self::PROVIDER:
                $this->db->where('id_users_provider', $userId);
                break;
            case self::SERVICE:
                $this->db->where('id_services', $serviceId);
                break;
            case self::PROVIDER_SERVICE:
                $this->db->where('id_users_provider', $userId)->where('id_services', $serviceId);
                break;
            default:
                break;
        }

        $appointments = $this->db->get('ea_appointments')->result_array();

        if ($aggregates) {
            foreach ($appointments as &$appointment) {
                $appointment = $this->get_aggregates($appointment);
            }
        }

        return $appointments;
    }

    /**
     * Get all appointments by list of user ids
     * @param string $where_clause (OPTIONAL) The WHERE clause of the query to be executed. DO NOT INCLUDE 'WHERE'
     * KEYWORD.
     * @param bool $aggregates
     * @param $userId Id of user
     * @param string $type (OPTIONAL) 'provider' or 'customer'
     * @return array Returns the rows from the database.
     */
    public function get_by_user($where_clause = '', $aggregates = FALSE, $userId = NULL, $type = '')
    {
        if ($where_clause != '') {
            $this->db->where($where_clause);
        }
        switch ($type) {
            case self::CUSTOMER:
                $this->db->where('id_users_customer', $userId);
                break;
            case self::PROVIDER:
                $this->db->where('id_users_provider', $userId);
                break;
            default:
                break;
        }

        $appointments = $this->db->get('ea_appointments')->result_array();

        if ($aggregates) {
            foreach ($appointments as &$appointment) {
                $appointment = $this->get_aggregates($appointment);
            }
        }

        return $appointments;
    }

    /**
     * Find appointment by id_integrated
     * @param $idIntegrated
     * @return string
     * @throws Exception
     */
    public function find_by_id_integrated($idIntegrated)
    {
        if ( ! isset($idIntegrated))
        {
            throw new Exception('User idIntegrated is not provided: ' . print_r($idIntegrated, TRUE));
        }

        $query = $this->db->get_where('ea_appointments', ['id_integrated' => $idIntegrated ]);

        $appointment = $query->num_rows() > 0 ? $query->result() : NULL;

        return $appointment;
    }
    public function find_id_by_id_integrated($id_integrated)
    {
        $query = $this->db
        ->select('id')
        ->from('ea_appointments')->where('id_integrated',$id_integrated)->get();
        return $query->row()->id;
    }

    public function updateAppointmentStatus($id, $bookingStatus)
    {
        if(!isset($id))
        {
            throw new Exception('Not found this appointment: ' . print_r($id, TRUE));
        }
        $query = $this->db->set('status',$bookingStatus)->where('id',$id);
        
        if ( ! $this->db->update('ea_appointments'))
        {
            throw new Exception('Could not update appointment record.');
        }

    }
    protected function get_aggregates(array $appointment)
    {
        $appointment['service'] = $this->db->get_where('ea_services',
            ['id' => $appointment['id_services']])->row_array();
        $appointment['provider'] = $this->db->get_where('ea_users',
            ['id' => $appointment['id_users_provider']])->row_array();
        $appointment['customer'] = $this->db->get_where('ea_users',
            ['id' => $appointment['id_users_customer']])->row_array();
            
        $id = $appointment['id'];

        $patient = $this->db->select('ea_users.*')->from('ea_users')
        ->join('ea_appointments_attendants', 
        "ea_users.id = ea_appointments_attendants.id_users AND ea_appointments_attendants.id_appointment = $id")->get()->row_array();
        
        if(isset($patient)) {
            $appointment['patient'] = $this->patient_model->get_aggregates($patient);
        }
        if(isset($appointment['provider'])){
            $appointment['provider'] = $this->user_model_v2->get_aggregates($appointment['provider']);
        }
        if(isset($appointment['customer'])){
            $appointment['customer'] = $this->user_model_v2->get_aggregates($appointment['customer']);
        }

        return $appointment;
    }

    /**
     * Query all relative appointment by service id_integrated, start date & end date
     */
    public function getAllAppointmentBy($service, $aggregates = FALSE, $otherRequestParams, $type = ''){
        $startDate = $otherRequestParams['startDate'];
        $endDate = $otherRequestParams['endDate'];
        $page = $otherRequestParams['page'];
        $size = $otherRequestParams['size'];
        $sort = $otherRequestParams['sort'] == '' ? 'DESC' : $otherRequestParams['sort'];
        $id_service_integrated = $otherRequestParams['id_service_integrated'];
        $id_user_integrated = $otherRequestParams['id_user_integrated'];
        $otherQuery = $otherRequestParams['q'];

        if(strlen($startDate) != 0){
            $condition['start_datetime >='] = $startDate;
        }
        if(strlen($endDate) != 0){
            $endDate .= ' 23:59:00';
            $condition['end_datetime <='] = $endDate;
        }

        if($otherQuery != null && $otherQuery != ''){
            $idList = $this->find_list_userId_by($otherQuery, $service[0]->id);
            if(sizeof($idList) > 0){
                $this->db->where_in('id_users_customer', $idList);
            }else{
                $this->db->where('id_integrated', $otherQuery);
            }
        }
       
        switch ($type) {
            case self::CUSTOMER:
                $condition['id_users_customer'] = $service['id'];
                break;
            case self::SERVICE:
                $condition['id_services'] = $service[0]->id;
                break;
            case self::CUSTOMER_SERVICE:
                // both customer and service
                $condition['id_users_customer'] =$this->user_model_v2->find_by_id_integrated($id_user_integrated)['id'];
                $condition['id_services'] = $this->services_model_v2->find_by_id_integrated($id_service_integrated)[0]->id;
                break;
            default:
                break;
        }
        $condition["status <>"] = 'unconfirmed';

        $this->db->order_by("DATE(start_datetime)", $sort);
        $this->db->order_by("TIME(start_datetime)", "asc");

        $appointmentData = $this->db->select("COUNT(*) as total, SUM(fee) + SUM(service_fee) as amount")
        ->get_where('ea_appointments', $condition)->result_array();
        
        $totalRecords = $appointmentData[0]['total'];
        $amount = isset($appointmentData[0]['amount']) ? $appointmentData[0]['amount'] : 0;
        
		if($page != ''&& $size != ''){
            $offset = ($page - 1 ) * $size;
            $appointments = $this->db
            ->order_by("DATE(start_datetime)",$sort)
            ->order_by("TIME(start_datetime)", "asc")
            ->get_where('ea_appointments', $condition, $size, $offset)->result_array();
        }else{
            $appointments = $this->db
            ->order_by("DATE(start_datetime)",$sort)
            ->order_by("TIME(start_datetime)", "asc")
            ->get_where('ea_appointments', $condition)->result_array();
        }
        $totalRecords = sizeof($appointments);
        if ($aggregates) {
            foreach ($appointments as &$appointment) {
                $appointment = $this->get_aggregates($appointment);
            }
        }
        $resultSet['total'] = $totalRecords;
        $resultSet['appointments'] = $appointments;
        $resultSet['amount'] = $amount;
        return $resultSet;
    }

    public function get_batch_paging($where_clause = '', $aggregates = FALSE, $userId = NULL, $serviceId = NULL, $type = '', $requestParams)
    {
        $sort = $requestParams['sort'];
        $page = $requestParams['page'];
        $size = $requestParams['size'];
        $otherQuery = $requestParams['q'];
        $sort = $sort == null || $sort == '' ? 'DESC' : $sort; // set default value for sort
        switch ($type) {
            case self::CUSTOMER:
                $where_clause['id_users_customer'] = $userId;
                break;
            case self::PROVIDER:
                $where_clause['id_users_provider'] = $userId;
                break;
            case self::SERVICE:
                $where_clause['id_services'] = $serviceId;
                break;
            case self::PROVIDER_SERVICE:
                $where_clause['id_users_provider'] = $userId;
                $where_clause['id_services'] = $serviceId;
                break;
            default:
                break;
        }

        if($otherQuery != null && $otherQuery != ''){
            $idList = $this->find_list_userId_by($otherQuery, $serviceId);
            if(sizeof($idList) > 0){
                $listId = $this->handlerArrayString($idList);
                $where_clause["id_users_customer IN (".$listId.")"] = null;
            }else{
                $where_clause['id_integrated'] = $otherQuery;
            }
        }

        $where_clause["status <>"] = 'unconfirmed';

        $appointmentData = $this->db->select("COUNT(*) as total, SUM(fee) + SUM(service_fee) as amount")->order_by("DATE(start_datetime)",$sort)
                                ->order_by("TIME(start_datetime)",'asc')
                                ->get_where('ea_appointments', $where_clause)->result_array();
        $totalRecords = $appointmentData[0]['total'];
        $amount = isset($appointmentData[0]['amount']) ? $appointmentData[0]['amount'] : 0;

        if($page != '' && $size != ''){
            $offset = ($page - 1 ) * $size;
            $this->db->limit($size,$offset);
            $appointments = $this->db->order_by("DATE(start_datetime)",$sort)
                                        ->order_by("TIME(start_datetime)", "asc")
                                        ->get_where('ea_appointments', $where_clause, $size, $offset)->result_array();
        } else {
            $appointments = $this->db
            ->order_by("DATE(start_datetime)",$sort)
            ->order_by("TIME(start_datetime)", "asc")
            ->get_where('ea_appointments', $where_clause)->result_array();
        }
        if ($aggregates) {
            foreach ($appointments as &$appointment) {
                $appointment = $this->get_aggregates($appointment);
            }
        }
        $resultSet['total'] = $totalRecords;
        $resultSet['appointments'] = $appointments;
        $resultSet['amount'] = $amount;
        return $resultSet;
    }
    
    public function getStatisticAppointment($id_service,$startDate, $endDate){
        if(strlen($startDate) != 0){
            $condition['ea_appointments.start_datetime >='] = $startDate;
        }
        if(strlen($endDate) != 0){
            $endDate .= ' 23:59:00';
            $condition['ea_appointments.end_datetime <='] = $endDate;
        }
        $condition["ea_appointments.id_services"] = $id_service;
        $this->db->select('ea_users.gender as gender, COUNT(ea_appointments.id) as total')
                 ->from('ea_appointments')
                 ->where($condition)
                 ->join('ea_users', 'ea_appointments.id_users_customer = ea_users.id')
                 ->group_by('ea_users.gender');
        $result = $this->db->get()->result_array();
        return $result;
    }

    private function find_list_userId_by($fullName, $id_service_integrated){
        $this->load->model('/v2/user_model_v2');
        $result = $this->user_model_v2->find_list_userId_by_fullName($fullName, $id_service_integrated);
        return $result;
    }

    private function handlerArrayString($arrays){
        $result = '';
        foreach ($arrays as &$value){
            $result .= $value . ',';
        }
        return substr($result, 0 , -1);
    }

    public function updateAppointmentByOrderId($orderId, $request)
    {
        $num_rows = $this->getNumberOfRecord($orderId);
        if ($num_rows == 0)
          {
            throw new \EA\Engine\Api\V1\Exception('Provided order id does not exist in the database.', 404, 'Not Found');
          }

        $this->db->where('order_id',$orderId);
        
        if ( ! $this->db->update('ea_appointments', $request))
        {
            throw new Exception('Could not update appointment record.');
        }
    }

    public function getNumberOfRecord($orderId) {
        $num_rows = $this->db->select('order_id')->from('ea_appointments')->where('order_id', $orderId)->get()->num_rows();
        return $num_rows;
    }

    public function getAppointmentsWithCondition($appointments, $aggregates = FALSE) {
        if ($aggregates) {
            foreach ($appointments as &$appointment) {
                $appointment = $this->get_aggregates($appointment);
            }
        }
        $resultSet['appointments'] = $appointments;
        return $resultSet;

    }
    public function getAppointmentWithServiceIntegrated($id_service_integrated) {
        $serviceId = $this->db->get_where('ea_services', ['id_integrated' => $id_service_integrated])->row()->id;
        if(empty($serviceId)) {
            throw new Exception('Can not find any record with id_service_integrated');
        }

        return $this->db->select('*')->from('ea_appointments')
        ->join('ea_services', 'ea_services.id = ea_appointments.id_services')
        ->where('ea_appointments.id_services', $serviceId)->get()->result_array();
    }

    public function getAppointmentWithIdUserIntegrated($id_user_integrated) {

        return $this->db->select('*')->from('ea_appointments')
        ->join('integrated_users_patients', 'ea_appointments.id_users_customer = integrated_users_patients.id_patients')
        ->where('integrated_users_patients.id_user_integrated', $id_user_integrated)
        ->where('ea_appointments.status !=', "unconfirmed")
        ->get()->result_array();
    }

    public function getUserAppointments($id_integrated, $id_user_integrated) {
        $patientId = $this->db->get_where('ea_users',['id_integrated' => $id_integrated])->row()->id;
        if(empty($patientId)) {
            throw new Exception('Can not find any record with id_integrated');
        }

        return $this->db->select('*')->from('ea_appointments')
        ->join('integrated_users_patients', 'ea_appointments.id_users_customer = integrated_users_patients.id_patients')
        ->where('integrated_users_patients.id_user_integrated', $id_user_integrated)
        ->where('integrated_users_patients.id_patients', $patientId)
        ->where('ea_appointments.status !=', "unconfirmed")
        ->get()->result_array();
    }
}
