<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Patient extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Patient_model', 'patientmodel');
       $this->load->model('Mylib', 'mylib');
    }
    public function getPatients_post(){
        // $this->db->select('*');
        // $this->db->from('patients');
        // $this->db->order_by('last_checkup,created_at', 'desc');
        $post = $this->input->post();
        $patients = "select * from patients where (id LIKE '%".$post['search_value']."%' OR firstname LIKE '%".$post['search_value']."%' OR lastname LIKE '%".$post['search_value']."%' OR middlename LIKE '%".$post['search_value']."%') order by lastname,firstname,middlename limit ".$post['limit']." offset ".$post['offset'];
        $total = "select * from patients where (id LIKE '%".$post['search_value']."%' OR firstname LIKE '%".$post['search_value']."%' OR lastname LIKE '%".$post['search_value']."%' OR middlename LIKE '%".$post['search_value']."%') order by lastname,firstname,middlename";
        $results['patients'] = $this->db->query($patients)->result_array();
        $results['total'] = $this->db->query($total)->num_rows();
        echo json_encode($results);
    }
    public function getAllPatients_get(){
        echo json_encode($this->db->get('patients')->result_array());
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

    // public function searchPatient_post(){
    //     $post = $this->input->post();
    //     $str = "select * from patients WHERE (id LIKE '%".$post['patient_id']."%' OR firstname LIKE '%".$post['patient_id']."%' OR lastname LIKE '%".$post['patient_id']."%' OR middlename LIKE '%".$post['patient_id']."%')";
    //     $query = $this->db->query($str);
    //     $results['patients'] = $query->result_array();
    //     echo json_encode($results);
    // }
}