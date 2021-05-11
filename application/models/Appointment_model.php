<?php defined('BASEPATH') or exit('No direct script access allowed');

class Appointment_model extends CI_Model{
    
    public function insertappointment($data=array()){

        $timestamp = date('Y-m-d H:i:s');
        $patient_id = $data['patient_id'];
        // $physician_id = $data['physician_id'];
        $discount_type = $data['discount_type'];
        $discount_percent = intval($data['discount_percent']);
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
            'discount_type' => $discount_type,
            'discount' => $discount_percent,
            'status' => $status,
            'payment' => $total_cash,
            'totalamount' => $total_fee,
            'created_at' => $created_at,
            'payment_date' => $payment_date
        );

        $str = "update patients set `status` = '{$status}', `last_checkup` = '{$timestamp}' where id = '{$patient_id}'";
        
        $this->db->trans_begin();
        $this->db->insert('appointments', $insert);
        $this->db->query($str);
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

        // $result = $this->db->get_where('appointments', array('patient_id' => $patient_id, 'status' => $status, 'created_at' => $cdate));
        $str = "
        select
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
        bb.totalamount,
        bb.payment
        from patients as aa
        left join appointments as bb on bb.patient_id = aa.id and bb.status = aa.status and bb.created_at = aa.last_checkup
        where aa.id = '{$patient_id}' and aa.status = '{$status}' and aa.last_checkup = '{$cdate}'";
        return $this->db->query($str);
    }
}