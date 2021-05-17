<?php defined('BASEPATH') or exit('No direct script access allowed');

class Appointment_model extends CI_Model{
    
    public function insertappointment($data=array()){
        $timestamp = date('Y-m-d H:i:s');
        $patient_id = $data['patient_id'];
        $physician_id = $data['physician_id'];
        $discount_type = $data['discount_type'];
        $discount_rmks = $data['discount_rmks'];
        $discount_percent = intval($data['discount_percent']);
        $submod_id = $data['submod_id'];
        $total_fee = doubleval($data['total_fee']);
        $total_cash = doubleval($data['total_cash']);
        //F = FOR PRINT , P = PENDING
        if($total_fee == 0){
            $status = 'P';
        }else{
            if($total_fee == $total_cash){
                $status = 'F';
            }else{
                $status = 'P';
            }
        }
        $created_at = $timestamp;
        $payment_date = $total_cash > 0 ? $timestamp : date('0000-00-00 00:00:00');
        //insert patient appointment
        $insert = array(
            'patient_id' => $patient_id,
            'physician_id' => $physician_id,
            'discount_type' => $discount_type,
            'discount_rmks' => $discount_rmks,
            'discount' => $discount_percent,
            'status' => $status,
            'payment' => $total_cash,
            'totalamount' => $total_fee,
            'submod_id' => $submod_id,
            'created_at' => $created_at,
            'payment_date' => $payment_date
        );
        //update patient status along with appointment status
        $str = "update patients set `status` = '{$status}', `last_checkup` = '{$timestamp}' where id = '{$patient_id}'";

        $this->db->trans_begin();
        $this->db->insert('appointments', $insert);
        $insertid = $this->db->insert_id();
        $this->db->query($str);
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result['status'] = false;
        }else{
            $this->db->trans_commit();
            $result['status'] = true;
            $result['cdate'] = $timestamp;
            $result['stat'] = $status;
            $result['appointment_id'] = $insertid;
        }
        return $result;
    }

    public function insert_patientlabtest($appointment_id, $data=array()){
        $lab_tests = json_decode($data['lab_test']);
        $patient_id = $data['patient_id'];
        $timestamp = date('Y-m-d H:i:s');

        $this->db->trans_begin();
        //DELETE lab tests
        $this->db->delete('patient_lab_test', array('appointment_id' => $appointment_id, 'patient_id' => $patient_id));
        if(count($lab_tests) > 0){
            $insert_lab_test = "insert into patient_lab_test (appointment_id,patient_id,subsubmod_id,result_name,result_value,created_at)values";
            foreach($lab_tests as $lab_test){
                $insert_lab_test .= "(".
                    $this->db->escape($appointment_id) . "," .
                    $this->db->escape($patient_id) . "," .
                    $this->db->escape($lab_test->id) . "," .
                    $this->db->escape($lab_test->title) . "," .
                    $this->db->escape($lab_test->result) . "," .
                    $this->db->escape($timestamp) . "),";
            }
            $insert_lab_test = substr($insert_lab_test,0,strlen($insert_lab_test) - 1);
            //insert lab tests
            $this->db->query($insert_lab_test);
        }

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = true;
        }
        return $result;
    }

    public function getappointment($data=array()){
        $patient_id = $data['patient_id'];
        $status = $data['status'];
        $cdate = $data['cdate'];

        $str = "
        select
        aa.id,
        bb.id as appointment_id,
        aa.firstname,
        aa.middlename,
        aa.lastname,
        aa.age,
        aa.gender,
        aa.status,
        aa.contact,
        aa.address,
        bb.discount,
        bb.discount_type,
        bb.discount_rmks,
        bb.totalamount,
        bb.payment,
        bb.physician_id,
        bb.submod_id
        from patients as aa
        left join appointments as bb on bb.patient_id = aa.id and bb.status = aa.status and bb.created_at = aa.last_checkup
        where aa.id = '{$patient_id}' and aa.status = '{$status}' and aa.last_checkup = '{$cdate}'";
        return $this->db->query($str);
    }

    public function getpatientlabtest($data=array()){
        $patient_id = $data['patient_id'];
        $status = $data['status'];
        $cdate = $data['cdate'];

        $str = "select bb.* from appointments as aa
        inner join patient_lab_test as bb on bb.appointment_id = aa.id
        where aa.patient_id = '{$patient_id}' and aa.status = '{$status}' and aa.created_at = '{$cdate}'";
        return $this->db->query($str);
    }

    public function updateappointment($data=array()){
        $timestamp = date('Y-m-d H:i:s');
        $appointment_id = $data['appointment_id'];
        $patient_id = $data['patient_id'];
        $physician_id = $data['physician_id'];
        $discount_type = $data['discount_type'];
        $discount_rmks = $data['discount_rmks'];
        $discount_percent = intval($data['discount_percent']);
        $submod_id = $data['submod_id'];
        $total_fee = doubleval($data['total_fee']);
        $total_cash = doubleval($data['total_cash']);
        $cdate = date($data['cdate']);
        //F = FOR PRINT , P = PENDING
        if($total_fee == 0){
            $status = 'P';
        }else{
            if($total_fee == $total_cash){
                $status = 'F';
            }else{
                $status = 'P';
            }
        }
        $payment_date = $total_cash > 0 ? $timestamp : date('0000-00-00 00:00:00');
        $update = array(
            'physician_id' => $physician_id,
            'discount_type' => $discount_type,
            'discount_rmks' => $discount_rmks,
            'discount' => $discount_percent,
            'status' => $status,
            'payment' => $total_cash,
            'totalamount' => $total_fee,
            'payment_date' => $payment_date,
            'submod_id' => $submod_id
        );
        $str = "update patients set `status` = '{$status}' where id = '{$patient_id}'";
        $this->db->trans_begin();
        $this->db->update('appointments', $update, 'id='. $appointment_id);
        $this->db->query($str); //update patient status along with appointment status
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result['status'] = false;
        }else{
            $this->db->trans_commit();
            $result['status'] = true;
            $result['stat'] = $status;
            $result['cdate'] = $cdate;
        }
        return $result;
    }
    public function chipselected($data){
        $submod_ids = explode("," , $data);
        $submod = [];
        foreach($submod_ids as $id){
            $resmod = $this->db->get_where('laboratory_submodule', 'id='.$id)->row_array();
            if($this->db->affected_rows($resmod) > 0){
                array_push($submod, array(
                    'value' => $resmod['id'],
                    'text' => $resmod['title'] . " (" . $resmod['amount'] . " Php)",
                    'mod_id' => $resmod['mod_id'],
                    'amount' => $resmod['amount']
                ));
            }
        }
        return $submod;
    }
}