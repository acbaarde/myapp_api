<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Appointment extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Appointment_model', 'appointmentmodel');
       $this->load->model('Mylib', 'mylib');
    }

    public function insertAppointment_post(){
        $result = $this->appointmentmodel->insertappointment($this->input->post());
        if($result['status'] == true){
            $insertlabtest = $this->appointmentmodel->insert_patientlabtest($result['appointment_id'], $this->input->post());
            if($insertlabtest == true){
                $res = array(
                    'message' => 'Insert Appointment Success!',
                    'status' => true,
                    'cdate' => $result['cdate'],
                    'stat' => $result['stat']
                );
            }else{
                $res = array(
                    'message' => 'Error inserting lab tests!',
                    'status' => false,
                    'cdate' => [],
                    'stat' => []
                );
            }
        }else{
            $res = array(
                'message' => 'Insert Appointment Failed!',
                'status' => false,
                'cdate' => [],
                'stat' => []
            );
        }
        echo json_encode($res);
    }

    public function getAppointment_post(){
        $affectedrows = $this->appointmentmodel->getappointment($this->input->post())->row_array();
        if($this->db->affected_rows($affectedrows) > 0){
            $lab_test = $this->appointmentmodel->getpatientlabtest($this->input->post());
            $discount = $this->db->get('discount');
            $physicians = $this->mylib->getPhysicians();
            $submod = $this->appointmentmodel->chipselected($affectedrows['submod_id']);
            $result = array(
                'status' => true,
                'patient' => $affectedrows,
                'lab_test' => $lab_test->result_array(),
                'discount' => $discount->result_array(),
                'physicians' => $physicians->result_array(),
                'chips_selected' => $submod
            );
        }else{
            $result = array(
                'status' => false,
                'patient' => [],
                'lab_test' => [],
                'discount' => [],
                'physicians' => [],
                'chips_selected' => []
            );
        }
        echo json_encode($result);
    }

    public function updateAppointment_post(){
        $result = $this->appointmentmodel->updateappointment($this->input->post());
        if($result['status'] == true){
            $insertlabtest = $this->appointmentmodel->insert_patientlabtest($this->input->post('appointment_id'), $this->input->post());
            if($insertlabtest == true){
                $result = array(
                    'message' => 'Updating Appointment Success!',
                    'status' => true,
                    'cdate' => $result['cdate'],
                    'stat' => $result['stat']
                );
            }else{
                $result = array(
                    'message' => 'Error updating lab tests!',
                    'status' => false,
                    'cdate' => [],
                    'stat' => []
                );
            }
        }else{
            $result = array(
                'message' => 'Updating Appointment Failed!',
                'status' => false,
                'cdate' => [],
                'stat' => []
            );
        }
        echo json_encode($result);
    }

    public function getAppointment_forreleased_get(){
        $result = $this->appointmentmodel->getappointment_forreleased()->result_array();
        if($this->db->affected_rows($result) > 0){
            $res = array(
                'result' => $result,
                'status' => true 
            );
        }else{
            $res = array(
                'result' => [],
                'status' => false 
            );
        }
        echo json_encode($res);
    }

    public function postAppointment_post(){
        $result = $this->appointmentmodel->postappointment($this->input->post());
        if($result == true){
            $res = array(
                'message' => 'Update Successful!',
                'status' => true 
            );
        }else{
            $res = array(
                'message' => 'Update Failed!',
                'status' => false 
            );
        }
        echo json_encode($res);
    }

    public function insertmod_post(){
        $mod_res = json_decode($this->input->post('lab_test'));
        echo json_encode(count($mod_res));
    }
}