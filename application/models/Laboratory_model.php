<?php defined('BASEPATH') or exit('No direct script access allowed');

class Laboratory_model extends CI_Model{

    public function loadlabmodule(){
        return $this->db->get('laboratory_module');
    }

    public function loadsubmodule($id){
        return $this->db->get_where('laboratory_submodule', array('mod_id' => $id));
    }

    public function loadsubsubmodule($id){
        return $this->db->get_where('laboratory_subsubmodule', array('mod_id' => $id));
    }

    public function load_patientlabtest($id){
        return $this->db->get_where('patient_lab_test', array('appointment_id' => $id));
    }
}