<?php defined('BASEPATH') or exit('No direct script access allowed');

class Patient_model extends CI_Model{

    public function insertpatient($data=array()){
        $newId = $this->mylib->patient_id_ctr();
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $middlename = $data['middlename'];
        $age = $data['age'];
        $agetype = $data['agetype'];
        $gender = $data['gender'];
        $contact = $data['contact'];
        $address = $data['address'];
        $created_by = $data['user_id'];
        $created_at = date('Y-m-d H:i:s');

        $insert = array(
            'id' => $newId,
            'firstname' => strtoupper($firstname),
            'lastname' => strtoupper($lastname),
            'middlename' => strtoupper($middlename),
            'age' => $age,
            'agetype' => $agetype,
            'gender' => $gender,
            'contact' => $contact,
            'address' => $address,
            'created_at' => $created_at,
            'created_by' => $created_by
        );

        $this->db->trans_begin();
        $this->db->insert('patients', $insert);

        //store new created id
        $this->db->insert('id_ctr_patient', array('id_number' => $newId));

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
        $agetype = $data['agetype'];
        $gender = $data['gender'];
        $contact = $data['contact'];
        $address = $data['address'];
        $updated_at = date('Y-m-d H:i:s');
        $updated_by = $data['user_id'];

        $update = array(
            'firstname' => strtoupper($firstname),
            'lastname' => strtoupper($lastname),
            'middlename' => strtoupper($middlename),
            'age' => $age,
            'agetype' => $agetype,
            'gender' => $gender,
            'contact' => $contact,
            'address' => $address,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by
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