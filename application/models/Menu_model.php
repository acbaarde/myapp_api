<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model{
    public function get_all_module(){
        $str = "select * FROM module_menu GROUP BY mod_code1 ORDER BY mod_code1";
        return $this->db->query($str)->result_array();
    }

    public function mod_menu1(){
        $query = "select * from module_menu where `active` = 'Y' GROUP BY mod_code1 ORDER BY mod_code1";
        return $query;
    }

    public function mod_menu2($data){
        $query = "select * from module_menu where mod_code1 = " . $this->db->escape($data) . " and mod_code2 != '' and `active` = 'Y' GROUP BY mod_code2 order by mod_code2 ";
        return $query;
    }
    public function mod_menu3($data1,$data2){
        $query = "select * FROM module_menu WHERE mod_code1 = " . $this->db->escape($data1) . " and mod_code2 = " . $this->db->escape($data2) . " AND mod_code3 != '' AND `active` = 'Y' ORDER BY mod_code3";
        return $query;
    }

}