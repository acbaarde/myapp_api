<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Payroll extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Payroll_model', 'payrollmodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }
    
    public function processPayroll_post(){
        echo json_encode($this->payrollmodel->processpayroll($this->input->post()));
    }
}