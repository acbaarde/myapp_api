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
        if($result == true){
            $result = array(
                'message' => 'Insert Appointment Success!',
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'Insert Appointment Failed!',
                'status' => false
            );
        }
        echo json_encode($result);
    }

    public function getAppointment_post(){
        $affectedrows = $this->appointmentmodel->getappointment($this->input->post());
        if($this->db->affected_rows($affectedrows) > 0){
            $result = array(
                'status' => true,
                'result' => $affectedrows->row_array()
            );
        }else{
            $result = array(
                'status' => false,
                'result' => []
            );
        }
        echo json_encode($result);
    }
}