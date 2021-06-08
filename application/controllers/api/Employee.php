<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Employee extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Employee_model', 'employeemodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }

    public function getEmployees_get(){
        echo json_encode($this->db->get('employees')->result_array());
    }

    public function insertEmployee_post(){
        $new_emp_id = $this->mylib->id_ctr();
        $post = $this->input->post();
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();

        $employee['table_name'] = 'employees';
        $employee['fields'] = array(
            'id' => $new_emp_id,
            'firstname' => $post['firstname'],
            'lastname' => $post['lastname'],
            'middlename' => $post['middlename'],
            'gender' => $post['gender'],
            'birthdate' => $post['birthdate'],
            'birthplace' => $post['birthplace'],
            'citizenship_id' => $post['citizenship_id'],
            'religion_id' => $post['religion_id'],
            'civil_status_id' => $post['civil_status_id'],
            'contact' => $post['contact'],
            'permanent_address' => $post['permanent_address'],
            'present_address' => $post['present_address'],
            'region_id' => $post['region_id'],
            'province_city_id' => $post['province_city_id'],
            'barangay_town_id' => $post['barangay_town_id'],
            'employment_status_id' => $post['employment_status_id'],
            'employee_status_id' => $post['employee_status_id'],
            'position_id' => $post['position_id'],
            'hired_date' => $post['hired_date'],
            'hold_date' => $post['hold_date'],
            'separation_date' => $post['separation_date'],
            'salary' => $post['salary'],
            'allowance' => $post['allowance'],
            'employment_type_id' => $post['employment_type_id'],
            'sss_no' => $post['sss_no'],
            'tin_no' => $post['tin_no'],
            'hdmf_no' => $post['hdmf_no'],
            'philhealth_no' => $post['philhealth_no']
        );
        $employee = $this->builder->create_insert($employee);

        //insert logs
        $employee_logs['table_name'] = 'employees_logs';
        $employee_logs['fields'] = array(
            'employee_id' => $new_emp_id,
            'actions' => 'create',
            'user_id' => $post['user_id'],
            'log_date' => $timestamp,
            'query' => $employee['query']
        );
        $this->builder->create_insert($employee_logs);

        //store created id
        $this->db->insert('id_ctr', array('id_number' => $new_emp_id));
        
        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Insert Employee Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Insert Employee Success!'
            );
        }

        echo json_encode($result);
    }
    public function updateEmployee_post(){
        $post = $this->input->post();
        $timestamp = date('Y-m-D H:i:s');
        $this->db->trans_begin();

        $employee['table_name'] = 'employees';
        $employee['fields'] = array(
            'firstname' => $post['firstname'],
            'lastname' => $post['lastname'],
            'middlename' => $post['middlename'],
            'gender' => $post['gender'],
            'birthdate' => $post['birthdate'],
            'birthplace' => $post['birthplace'],
            'citizenship_id' => $post['citizenship_id'],
            'religion_id' => $post['religion_id'],
            'civil_status_id' => $post['civil_status_id'],
            'contact' => $post['contact'],
            'permanent_address' => $post['permanent_address'],
            'present_address' => $post['present_address'],
            'region_id' => $post['region_id'],
            'province_city_id' => $post['province_city_id'],
            'barangay_town_id' => $post['barangay_town_id'],
            'employment_status_id' => $post['employment_status_id'],
            'employee_status_id' => $post['employee_status_id'],
            'position_id' => $post['position_id'],
            'hired_date' => $post['hired_date'],
            'hold_date' => $post['hold_date'],
            'separation_date' => $post['separation_date'],
            'salary' => $post['salary'],
            'allowance' => $post['allowance'],
            'employment_type_id' => $post['employment_type_id'],
            'sss_no' => $post['sss_no'],
            'tin_no' => $post['tin_no'],
            'hdmf_no' => $post['hdmf_no'],
            'philhealth_no' => $post['philhealth_no']
        );
        $employee['filters'] = array('id' => $post['id']);
        $employee = $this->builder->create_update($employee);
        
        //insert logs
        $employee_logs['table_name'] = 'employees_logs';
        $employee_logs['fields'] = array(
            'employee_id' => $post['id'],
            'actions' => 'update',
            'user_id' => $post['user_id'],
            'log_date' => $timestamp,
            'query' => $employee['query']
        );
        $this->builder->create_insert($employee_logs);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Employee Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Employee Success!'
            );
        }
        echo json_encode($result);
    }
}