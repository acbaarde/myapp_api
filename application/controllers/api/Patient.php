<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Patient extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Patient_model', 'patientmodel');
       $this->load->model('Mylib', 'mylib');
    }
    public function getPatients_get(){
        $this->db->select('*');
        $this->db->from('patients');
        $this->db->order_by('last_checkup,created_at', 'desc');
        echo json_encode($this->db->get()->result());
    }
    public function getPatient_post(){
        $patient = $this->db->get_where('patients', array('id' => $this->input->post('id')));
        $discount = $this->db->get('dm_discount');
        $physicians = $this->mylib->getPhysicians();
        $result = array(
            'patient' => $patient->row_array(),
            'discount' => $discount->result_array(),
            'physicians' => $physicians->result_array()
        );
        echo json_encode($result);
    }
    public function insertPatient_post(){
        $result = $this->patientmodel->insertpatient($this->input->post());
        if($result == true){
            $result = array(
                'message' => 'Insert Success!',
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'Insert Failed!',
                'status' => false
            );
        }
        echo json_encode($result);
    }
    public function updatePatient_post(){
        $result = $this->patientmodel->updatepatient($this->input->post());
        if($result == true){
            $result = array(
                'message' => 'Update Success!',
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'Update Failed!',
                'status' => false
            );
        }
        echo json_encode($result);
    }
}