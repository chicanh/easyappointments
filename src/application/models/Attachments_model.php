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
 * Attachments Model
 *
 * @package Models
 */
class Attachments_Model extends CI_Model {
    /**
     * Get Attachment values from database.
     *
     * This method returns list attachments from the database for an appointment
     *
     * @param string $appointmentId The database attachment appointmentId.
     *
     * @return string Returns the database value for the selected attachment.
     *
     * @throws Exception If the $appointmentId argument is invalid.
     * @throws Exception If the requested $appointmentId attachment does not exist in the database.
     */
    public function get_attachment($appointmentId)
    {
        if ( ! is_string($appointmentId))
        { // Check argument type.
            throw new Exception('$appointmentId argument is not a string: ' . $appointmentId);
        }

        $query = $this->db->get_where('ea_appointments_attachments', ['id_appointment' => $appointmentId ]);

        if ($query->num_rows() == 0)
        { // Check if attachment exists in db.
            throw new Exception('$appointmentId attachment does not exist in DB: ' . $appointmentId);
        }
        
        $attachment = $query->num_rows() > 0 ? $query->row() : '';

        return $attachment->value;
    }

    /**
     * This method sets the value for an Attachment on the database.
     *
     * If the Attachment doesn't exist, it is going to be created, otherwise updated.
     *
     * @param $appointmentId The appointment id.
     * @param $attachment The attachment object.
     *
     * @return int Returns the attachment database id.
     *
     * @throws Exception If $name argument is invalid.
     * @throws Exception If the save operation fails.
     */
    public function set_attachment($appointmentId, $attachment)
    {
        if ($this->db->get_where('ea_appointments', ['id' => $appointmentId])->num_rows() == 0)
        {
            throw new Exception('$appointmentId argument does not exist in DB: ' . $appointmentId);
        }

        if ( empty($attachment['name']) || empty($attachment['value']))
        {
            throw new Exception('$attachment argument is empty. ');
        }

        $query = $this->db->get_where('ea_appointments_attachments', ['id_appointment' => $appointmentId]);

        if ($query->num_rows() > 0)
        {
            // Update attachment
            if ( ! $this->db->update('ea_appointments_attachments', ['id_appointment' => $appointmentId], ['value' => $attachment['value']], ['name' => $attachment['name']]))
            {
                throw new Exception('Could not update database attachment.');
            }
            $attachment_id = (int)$this->db->get_where('ea_appointments_attachments', ['id_appointment' => $appointmentId])->row()->id;
        }
        else
        {
            // Insert attachment
            $insert_data = [
                'id_appointment' => $appointmentId,
                'name' => $attachment['name'],
                'value' => $attachment['value']
            ];
            if ( ! $this->db->insert('ea_appointments_attachments', $insert_data))
            {
                throw new Exception('Could not insert database attachment.');
            }
            $attachment_id = (int)$this->db->insert_id();
        }

        return $attachment_id;
    }

    /**
     * Saves all the appointment attachments into the database.
     *
     * This method is useful when trying to save all the attachments at once instead of
     * saving them one by one.
     *
     * @param $appointmentId the appointment id
     * @param array $attachments Contains all the attachments.
     *
     * @return bool Returns the save operation result.
     *
     * @throws Exception When the update operation won't work for a specific attachment.
     */
    public function save_attachments($appointmentId, $attachments)
    {
        if ($this->db->get_where('ea_appointments', ['id' => $appointmentId])->num_rows() == 0)
        {
            throw new Exception('$appointmentId argument does not exist in DB: ' . $appointmentId);
        }
        if ( ! is_array($attachments))
        {
            throw new Exception('$attachments argument is invalid: ' . print_r($attachments, TRUE));
        }
        $query = $this->db->get_where('ea_appointments_attachments', ['id_appointment' => $appointmentId]);

        if ($query->num_rows() > 0) {
            // Update attachment
            foreach ($attachments as $attach) {
                if ( ! $this->db->update('ea_appointments_attachments', ['id_appointment' => $appointmentId], ['value' => $attach['value']], ['name' => $attach['name']]))
                {
                    throw new Exception('Could not update database attachment.');
                }
            }
        }
        else  {
            // Insert attachment
            foreach ($attachments as $attach) {
                $insert_data = [
                    'id_appointment' => $appointmentId,
                    'name' => $attach['name'],
                    'value' => $attach['value']
                ];
                if ( ! $this->db->insert('ea_appointments_attachments', $insert_data))
                {
                    throw new Exception('Could not insert database attachment.');
                }
            }
        }

        return TRUE;
    }

    /**
     * Remove a attachment from the database.
     *
     * @param string $appointmentId The attachment appointmentId to be removed.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If the $appointmentId argument is invalid.
     */
    public function remove_attachment($appointmentId)
    {
        if ( ! is_string($appointmentId))
        {
            throw new Exception('$appointmentId is not a string: ' . $appointmentId);
        }

        if ($this->db->get_where('ea_appointments_attachments', ['id_appointment' => $appointmentId])->num_rows() == 0)
        {
            return FALSE; // There is no such attachment.
        }

        return $this->db->delete('ea_appointments_attachments', ['id_appointment' => $appointmentId]);
    }

    /**
     * Returns all the appointment attachment at once.
     *
     * @return array Array of all the appointment attachments stored in the 'ea_appointments_attachments' table.
     */
    public function get_appointment_attachments()
    {
        return $this->db->get('ea_appointments_attachments')->result_array();
    }
}
