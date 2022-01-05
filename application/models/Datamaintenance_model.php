<?php defined('BASEPATH') or exit('No direct script access allowed');

class Datamaintenance_model extends CI_Model{
    public function insertphysician($data=array()){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_start();

        $physician['table_name'] = 'physicians';
        $physician['fields'] = array(
            'firstname' => strtoupper($post['firstname']),
            'lastname' => strtoupper($post['lastname']),
            'middlename' => strtoupper($post['middlename']),
            'gender' => $post['gender'],
            'contact' => $post['contact'],
            'address' => strtoupper($post['address']),
            'created_by' => $post['user_id'],
            'created_at' => $timestamp
        );
        $physician = $this->builder->create_insert($physician);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Insert Physician Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Insert Physician Success!'
            );
        }
        return $result;
    }
    public function updatephysician($data=array()){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();

        $physician['table_name'] = 'physicians';
        $physician['fields'] = array(
            'firstname' => strtoupper($post['firstname']),
            'lastname' => strtoupper($post['lastname']),
            'middlename' => strtoupper($post['middlename']),
            'gender' => $post['gender'],
            'contact' => $post['contact'],
            'address' => strtoupper($post['address']),
            'updated_by' => $post['user_id'],
            'updated_at' => $timestamp
        );
        $physician['filters'] = array('id' => $post['id']);
        $physician = $this->builder->create_update($physician);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Physician Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Physician Success!'
            );
        }
        return $result;
    }

    public function insertpayperiod($data=array()){
        $post = $data;
        $year = $this->mylib->get_active_yr();
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_start();

        $payperiod['table_name'] = "dm_pp{$year}";
        $payperiod['fields'] = array(
            'pperiod' => $post['pperiod'],
            'cfrom' => $post['cfrom'],
            'cto' => $post['cto'],
            'ppost' => $post['ppost'],
            'created_by' => $post['user_id'],
            'created_at' => $timestamp
        );
        $payperiod = $this->builder->create_insert($payperiod);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Insert Payperiod Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Insert Payperiod Success!'
            );
        }
        return $result;
    }
    public function updatepayperiod($data=array()){
        $post = $data;
        $year = $this->mylib->get_active_yr();
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_start();

        $payperiod['table_name'] = "dm_pp{$year}";
        $payperiod['fields'] = array(
            'pperiod' => $post['pperiod'],
            'cfrom' => $post['cfrom'],
            'cto' => $post['cto'],
            'ppost' => $post['ppost'],
            'updated_by' => $post['user_id'],
            'updated_at' => $timestamp
        );
        $payperiod['filters'] = array('id' => $post['id']);
        $payperiod = $this->builder->create_update($payperiod);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Payperiod Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Payperiod Success!'
            );
        }
        return $result;
    }

    public function insertworksched($data=array()){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_start();

        $work_shift['table_name'] = "dm_work_shift";
        $work_shift['fields'] = array(
            'ws_code' => $post['ws_code'],
            '`desc`' => $post['desc'],
            'grace_period' => $post['grace_period'],
            'created_by' => $post['user_id'],
            'created_at' => $timestamp
        );
        $work_shift = $this->builder->create_insert($work_shift);

        $time = explode("-", $post['desc']);
        $amin = $time[0];
        $amout = $time[1];
        $pmin = $time[2];
        $pmout = $time[3];

        $day = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        foreach($day as $rw){
            $work_sched['table_name'] = "dm_work_schedule";
            $work_sched['fields'] = array(
                'ws_code' => $post['ws_code'],
                'ws_day' => $rw,
                'ws_amin' => $amin,
                'ws_amout' => $amout,
                'ws_pmin' => $pmin,
                'ws_pmout' => $pmout
            );
            $work_sched = $this->builder->create_insert($work_sched);
        }

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Insert Worksched Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Insert Worksched Success!'
            );
        }
        return $result;
    }
    public function updateworksched($data=array()){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_start();

        $work_shift['table_name'] = "dm_work_shift";
        $work_shift['fields'] = array(
            'ws_code' => $post['ws_code'],
            '`desc`' => $post['desc'],
            'grace_period' => $post['grace_period'],
            'updated_by' => $post['user_id'],
            'updated_at' => $timestamp
        );
        $work_shift['filters'] = array('id' => $post['id']);
        $work_shift = $this->builder->create_update($work_shift);

        $time = explode("-", $post['desc']);
        $amin = $time[0];
        $amout = $time[1];
        $pmin = $time[2];
        $pmout = $time[3];

        $day = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        foreach($day as $rw){
            $work_sched['table_name'] = "dm_work_schedule";
            $work_sched['fields'] = array(
                'ws_code' => $post['ws_code'],
                'ws_amin' => $amin,
                'ws_amout' => $amout,
                'ws_pmin' => $pmin,
                'ws_pmout' => $pmout
            );
            $work_sched['filters'] = array('ws_code' => $post['ws_code'], 'ws_day' => $rw);
            $work_sched = $this->builder->create_update($work_sched);
        }

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Worksched Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Worksched Success!'
            );
        }
        return $result;
    }
    public function save_company_info($data){
        $post = $data;
        $this->db->trans_start();

        $fields = array(
            'company_name' => $post['company_name'],
            'address' => $post['address'],
            'tel_no' => $post['tel_no'],
            'doh_lic_no' => $post['doh_lic_no'],
            'pathologist_name' => $post['pathologist_name'],
            'pathologist_lic_no' => $post['pathologist_lic_no']
        );
        $this->db->update('dm_company_info', $fields, array('id'=>$post['id']));

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Company Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Company Success!'
            );
        }
        return $result;
    }
}