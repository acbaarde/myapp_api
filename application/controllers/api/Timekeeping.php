<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Timekeeping extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Timekeeping_model', 'timekeepingmodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }

    public function getEmployees_get(){
        $affected_rows = $this->timekeepingmodel->getemployees();
        if($this->db->affected_rows($affected_rows) > 0){
            $result = array(
                'status' => true,
                'message' => 'Success!',
                'result' => $affected_rows->result_array()
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'No Records Found!',
                'result' => []
            );
        }
        echo json_encode($result);
    }

    public function getEmployee_post(){
        $affected_rows = $this->mylib->get_active_pp();
        if($this->db->affected_rows($affected_rows) > 0){
            $employee = $this->timekeepingmodel->getemployee($this->input->post('id'))->row_array();
            $dtr = $this->timekeepingmodel->employee_dtr($this->input->post('id'));
            $result = array(
                'status' => true,
                'message' => 'Success!',
                'result' => [
                    'employee' => $employee,
                    'payperiod' => $affected_rows,
                    'dtr' => $this->db->query($dtr['query'])->result_array()
                ]
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'No Active Payperiod!',
                'result' => []
            );
        }
        echo json_encode($result);
    }

    public function savedtr_post(){
        $result = $this->timekeepingmodel->savedtr($this->input->post());
        if($result == true){
            $result = array(
                'status' => true,
                'message' => 'Dtr Save Success!'
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'Dtr Save Failed!'
            );
        }
        echo json_encode($result);
    }

    // public function employeeDtr_post(){
    //     $result = $this->timekeepingmodel->employee_dtr($this->input->post('id'));
    //     if($result['status'] == true){
    //         $result = array(
    //             'status' => true,
    //             'result' => [
    //                 'dtr' => $this->db->query($result['query'])->result_array()
    //             ]
    //         );
    //     }else{
    //         $result = array(
    //             'status' => false,
    //             'result' => [
    //                 'dtr' => []
    //             ]
    //         );
    //     }

    //     echo json_encode($result);
    // }
}