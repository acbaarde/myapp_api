<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model{
    public function get_all_module(){
        $str = "select * FROM module_menu GROUP BY mod_code1 ORDER BY mod_code1";
        return $this->db->query($str)->result_array();
    }
}