<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model{
    public function get_all_module(){
        $str = "select * FROM module_menu GROUP BY mod_code1 ORDER BY mod_code1";
        return $this->db->query($str)->result_array();
    }

    public function mod_menu1(){
        // $query = "select * from module_menu where `active` = 'Y' GROUP BY mod_code1 ORDER BY `order`";
        $query = "select * from (select * FROM module_menu where `active` = 'Y' GROUP BY mod_code1)as aa 
        left join module_menu_sort as bb on bb.mod_code = aa.mod_code1 order by bb.order";
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


    public function saveuseraccess($data=array()){
        $post = $data;
        $timestamp = date("Y-m-d H:i:s");
        $this->db->trans_begin();
        $str = "delete from users_mod_access where user_id = ". $this->db->escape($post['id']);
        $this->db->query($str);
        $mod_id = explode(",", $post['mod_id']);
        foreach($mod_id as $rw){
            $useraccess['table_name'] = "users_mod_access";
            $useraccess['fields'] = array(
                'user_id' => $post['id'],
                'mod_id' => $rw,
                'created_by' => $post['user_id'],
                'created_at' => $timestamp
            );
            $useraccess = $this->builder->create_insert($useraccess);
        }
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