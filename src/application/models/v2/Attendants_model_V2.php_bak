<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      Davido Team
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Attendants Model
 *
 * @package Models/v2
 */
class Attendants_Model_V2 extends CI_Model {

    /**
     * Get all, or specific records from Attendants's table.
     * This method returns list attendants from the database for an appointment
     *
     * @param string $appointmentId The database attendants appointmentId.
     *
     * @return string Returns the database value for the attendants.
     *
     * @throws Exception If the $appointmentId argument is invalid.
     */
    public function get_batch($appointmentId)
    {
        if (!is_string($appointmentId)) { // Check argument type.
            throw new Exception('$appointmentId argument is not a string: ' . $appointmentId);
        }
        $attendants = $this->db->get_where('ea_appointments_attendants', ['id_appointment' => $appointmentId])->result_array();
        return $attendants;
    }

    /**
     * Saves all the appointment attendants into the database.
     *
     * This method is useful when trying to save all the attendants at once instead of
     * saving them one by one.
     *
     * @param $appointmentId The appointment id
     * @param array $attendants Contains all the attendants.
     *
     * @return bool Returns the save operation result.
     *
     * @throws Exception When the update operation won't work for a specific attendant.
     */
    public function save_attendants($appointmentId, $attendants)
    {
        if ($this->db->get_where('ea_appointments', ['id' => $appointmentId])->num_rows() == 0) {
            throw new Exception('$appointmentId argument does not exist in DB: ' . $appointmentId);
        }
        if (!is_array($attendants)) {
            throw new Exception('$attendants argument is invalid: ' . print_r($attendants, TRUE));
        }
        $query = $this->db->get_where('ea_appointments_attendants', ['id_appointment' => $appointmentId]);

        if ($query->num_rows() > 0) {
            // Update attendant
            foreach ($attendants as $attendant) {
                if (!$this->db->update('ea_appointments_attendants', ['id_appointment' => $appointmentId], ['id_users' => $attendant])) {
                    throw new Exception('Could not update database attendant.');
                }
            }
        } else {
            // Insert attendant
            foreach ($attendants as $attendant) {
                $insert_data = [
                    'id_appointment' => $appointmentId,
                    'id_users' => $attendant
                ];
                if (!$this->db->insert('ea_appointments_attendants', $insert_data)) {
                    throw new Exception('Could not insert database attendant.');
                }
            }
        }

        return TRUE;
    }

    /**
     * Remove an appointment attendant from the database.
     *
     * @param string $appointmentId The attendant appointmentId to be removed.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If the $appointmentId argument is invalid.
     */
    public function remove_appointment($appointmentId)
    {
        if (!is_string($appointmentId)) {
            throw new Exception('$appointmentId is not a string: ' . $appointmentId);
        }

        if ($this->db->get_where('ea_appointments_attendants', ['id_appointment' => $appointmentId])->num_rows() == 0) {
            return FALSE; // There is no such attendant.
        }

        return $this->db->delete('ea_appointments_attendants', ['id_appointment' => $appointmentId]);
    }

    /**
     * Remove an user attendant of an appointment from the database.
     *
     * @param string $appointmentId The attendant appointmentId.
     * @param string $userId The attendants userId.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If the $appointmentId or $userId argument is invalid.
     */
    public function remove_attendant($appointmentId, $userId)
    {
        if (!is_string($appointmentId) || !is_string($userId)) {
            throw new Exception('Param is not a string');
        }

        if ($this->db->get_where('ea_appointments_attendants', ['id_appointment' => $appointmentId, 'id_users' => $userId])->num_rows() == 0) {
            return FALSE; // There is no such attendant.
        }

        return $this->db->delete('ea_appointments_attendants', ['id_appointment' => $appointmentId, 'id_users' => $userId]);
    }

    /**
     * Returns all appointments attendant of an user.
     *
     * @param string $userId The attendants userId.
     *
     * @return array Array of all appointments attendant stored in the 'ea_appointments_attendants' table.
     *
     * @throws Exception If the $userId argument is invalid.
     */
    public function get_appointments_by_user($userId)
    {
        if (!is_string($userId)) { // Check argument type.
            throw new Exception('$userId argument is not a string: ' . $userId);
        }
        $attendants = $this->db->get_where('ea_appointments_attendants', ['id_users' => $userId])->result_array();
        return $attendants;
    }

    /**
     * Returns list users that attendant list appointments.
     *
     * @param $appointmentIds The list of appointments id.
     *
     * @return array Array of all users attendant appointments stored in the 'ea_appointments_attendants' table.
     *
     * @throws Exception If the $appointments argument is invalid.
     */
    public function get_users_by_appointments($appointmentIds)
    {
        if (!is_array($appointmentIds)) { // Check argument type.
            throw new Exception('$appointmentIds argument is not an array: ' . $appointmentIds);
        }

        $this->db->distinct();
        $this->db->select('id_users');
        $this->db->where_in('id_appointment', $appointmentIds);
        $query = $this->db->get('ea_appointments_attendants');
        $userIds = $query->result();

        $result = array();
        if (count($userIds) > 0) {
            foreach ($userIds as $userId) {
                array_push($result, $userId->id_users);
            }
        }
        return $result;
    }

}
