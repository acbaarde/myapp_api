<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Data_maintenance extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Datamaintenance_model', 'datamaintenance');
       $this->load->model('Mylib', 'mylib');
    }

    public function loadDatamaintenance_get(){
        $result = array(
            'gender' => $this->db->get('dm_gender')->result_array(),
            'employment_status' => $this->db->get('dm_employment_status')->result_array(),
            'employee_status' => $this->db->get('dm_employee_status')->result_array(),
            'employment_type' => $this->db->get('dm_employment_type')->result_array(),
            'position' => $this->db->get('dm_position')->result_array(),
            'citizenship' => $this->db->get('dm_citizenship')->result_array(),
            'religion' => $this->db->get('dm_religion')->result_array(),
            'region' => $this->db->get('dm_region')->result_array(),
            'province_city' => $this->db->get('dm_province_city')->result_array(),
            'barangay_town' => $this->db->get('dm_barangay_town')->result_array(),
            'civil_status' => $this->db->get('dm_civil_status')->result_array()
        );
        echo json_encode($result);
    }
}