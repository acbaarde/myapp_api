<?php defined('BASEPATH') or exit('No direct script access allowed');

class Patient_model extends CI_Model{

    public function insertpatient($data=array()){
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $middlename = $data['middlename'];
        $age = $data['age'];
        $gender = $data['gender'];
        $contact = $data['contact'];
        $address = $data['address'];
        $created_at = date('Y-m-d H:i:s');

        $insert = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'middlename' => $middlename,
            'age' => $age,
            'gender' => $gender,
            'contact' => $contact,
            'address' => $address,
            'created_at' => $created_at
        );

        $this->db->trans_begin();
        $this->db->insert('patients', $insert);

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = true;
        }

        return $result;
    }

    public function updatepatient($data=array()){
        $id = $data['id'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $middlename = $data['middlename'];
        $age = $data['age'];
        $gender = $data['gender'];
        $contact = $data['contact'];
        $address = $data['address'];
        $updated_at = date('Y-m-d H:i:s');

        $update = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'middlename' => $middlename,
            'age' => $age,
            'gender' => $gender,
            'contact' => $contact,
            'address' => $address,
            'updated_at' => $updated_at
        );

        $this->db->trans_begin();
        $this->db->update('patients', $update, 'id=' . $id);
        
        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = true;
        }

        return $result;
    }
}