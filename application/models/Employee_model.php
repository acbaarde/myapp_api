<?php defined('BASEPATH') or exit('No direct script access allowed');

class Employee_model extends CI_Model{
    
    // public function insertemployee($data=array()){
    //     $firstname = $data['firstname'];
    //     $lastname = $data['lastname'];
    //     $middlename = $data['middlename'];
    //     $gender = $data['gender'];
    //     $birthdate = $data['birthdate'];
    //     $birthplace = $data['birthplace'];
    //     $citizenship_id = $data['citizenship_id'];
    //     $religion_id = $data['religion_id'];
    //     $civil_status_id = $data['civil_status_id'];
    //     $contact = $data['contact'];
    //     $permanent_address = $data['permanent_address'];
    //     $present_address = $data['present_address'];
    //     $region_id = $data['region_id'];
    //     $province_city_id = $data['province_city_id'];
    //     $barangay_town_id = $data['barangay_town_id'];
    //     $employment_status_id = $data['employment_status_id'];
    //     $employee_status_id = $data['employee_status_id'];
    //     $position_id = $data['position_id'];
    //     $hired_date = $data['hired_date'];
    //     $hold_date = $data['hold_date'];
    //     $separation_date = $data['separation_date'];
    //     $salary = $data['salary'];
    //     $allowance = $data['allowanace'];
    //     $employment_type_id = $data['employment_type_id'];
    //     $sss_no = $data['sss_no'];
    //     $tin_no = $data['tin_no'];
    //     $hdmf_no = $data['hdmf_no'];
    //     $philhealth_no = $data['philhealth_no'];

    //     $insert = array(
    //         'firstname' => $firstname,
    //         'lastname' => $lastname,
    //         'middlename' => $middlename,
    //         'gender' => $gender,
    //         'birthdate' => $birthdate,
    //         'birthplace' => $birthplace,
    //         'citizenship_id' => $citizenship_id,
    //         'religion_id' => $religion_id,
    //         'civil_status_id' => $civil_status_id,
    //         'contact' => $contact,
    //         'permanent_address' => $permanent_address,
    //         'present_address' => $present_address,
    //         'region_id' => $region_id,
    //         'province_city_id' => $province_city_id,
    //         'barangay_town_id' => $barangay_town_id,
    //         'employment_status_id' => $employment_status_id,
    //         'employee_status_id' => $employee_status_id,
    //         'position_id' => $position_id,
    //         'hired_date' => $hired_date,
    //         'hold_date' => $hold_date,
    //         'separation_date' => $separation_date,
    //         'salary' => $salary,
    //         'allowance' => $allowance,
    //         'employment_type_id' => $employment_type_id,
    //         'sss_no' => $sss_no,
    //         'tin_no' => $tin_no,
    //         'hdmf_no' => $hdmf_no,
    //         'philhealth_no' => $philhealth_no
    //     );

    //     $this->db->trans_begin();
    //     $this->db->insert('employees', $insert);

    //     if($this->db->trans_status() === FALSE){
    //         $this->db->trans_rollback();
    //         $result = true;
    //     }else{
    //         $this->db->trans_commit();
    //         $result = false;
    //     }
    //     return $result;
    // }
}