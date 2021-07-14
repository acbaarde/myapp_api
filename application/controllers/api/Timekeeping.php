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
        $employees = $this->timekeepingmodel->getemployees();
        if($employees->num_rows() > 0){
            $result = array(
                'status' => true,
                'message' => 'Success!',
                'result' => $employees->result_array()
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
        $payperiod = $this->mylib->get_active_pp();
        $post = $this->input->post();
        if($payperiod->num_rows() > 0){
            $employee = $this->timekeepingmodel->getemployee($post['id'])->row_array();
            $dtr = $this->timekeepingmodel->employee_dtr($post['id']);
            $salary_adjustments = $this->timekeepingmodel->salary_adjustments($post['id']);
            $salary_adj = $salary_adjustments->num_rows() > 0 ? $salary_adjustments->row_array() : [];
            $salary_adjustments_breakdown = $this->timekeepingmodel->salary_adjustments_breakdown(isset($salary_adj['id']) ? $salary_adj['id'] : 0);
            $result = array(
                'status' => true,
                'message' => 'Success!',
                'result' => [
                    'employee' => $employee,
                    'payperiod' => $payperiod->row_array(),
                    'dtr' => $this->db->query($dtr['query'])->result_array(),
                    'salary_adjustments' => $salary_adj,
                    'salary_adjustments_breakdown' => $salary_adjustments_breakdown->num_rows() > 0 ? $salary_adjustments_breakdown->result_array() : []
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

    public function processmanhour_post(){
        echo json_encode($this->timekeepingmodel->processmanhour());
    }
    public function postmanhour_post(){
        echo json_encode($this->timekeepingmodel->postmanhour($this->input->post()));
    }

    public function insertSalaryAdjustment_post(){
        echo json_encode($this->timekeepingmodel->insertsalaryadjustment($this->input->post()));
    }
    public function updateSalaryAdjustment_post(){
        echo json_encode($this->timekeepingmodel->updatesalaryadjustment($this->input->post()));
    }
    public function deleteSalaryAdjustment_post(){
        echo json_encode($this->timekeepingmodel->deletesalaryadjustment($this->input->post()));
    }

    public function activePayperiod_get(){
        $pp = $this->mylib->get_active_pp();
        if($pp->num_rows() > 0){
            $result = array(
                'status' => true,
                'message' => 'Success!',
                'pperiod' => $pp->row_array()['pperiod']
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'No payroll period established!',
                'pperiod' => ''
            );
        }
        echo json_encode($result);
    }
}