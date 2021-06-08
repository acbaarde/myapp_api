<?php defined('BASEPATH') or exit('No direct script access allowed');

class Laboratory_model extends CI_Model{

    public function loadlabmodule($data=array()){
        if(isset($data['released'])){
            $row = $this->db->get_where('appointments', array('id' => $data['appointment_id']))->row_array();
            $modules = $this->db->query("SELECT `mod_id` FROM laboratory_submodule WHERE id IN (". $row['submod_id'] .") GROUP BY `mod_id`")->result_array();
            $mod_id = "(";
            foreach($modules as $module){
                $mod_id .= $module['mod_id'] . ",";
            }
            $mod_id = substr($mod_id,0,strlen($mod_id) -1) . ")";
            $result = $this->db->get_where('laboratory_module' , 'id IN' . $mod_id );
        }else{
            $result = $this->db->get('laboratory_module');
        }

        return $result;
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