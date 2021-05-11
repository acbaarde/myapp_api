<?php defined('BASEPATH') or exit('No direct script access allowed');

class Laboratory_model extends CI_Model{

    public function loadlabmodule(){
        return $this->db->get('laboratory_module');
    }

    public function loadsubmodule($data){
        $id = $data;
        return $this->db->get_where('laboratory_submodule', array('mod_id' => $id));
    }

    public function loadsubsubmodule($data){
        $id = $data;
        return $this->db->get_where('laboratory_subsubmodule', array('mod_id' => $id));
    }
}