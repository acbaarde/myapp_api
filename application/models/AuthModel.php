<?php defined('BASEPATH') or exit('No direct script access allowed');

class AuthModel extends CI_Model{
    public function check_login($user_id, $password){
        $user_id = $user_id;
        $password = md5($password);

        $str = "select * from users where user_id = '{$user_id}' and password = '{$password}'";
        $query = $this->db->query($str);
        if($query->num_rows() > 0){
            return array(
                'status' => true,
                'result' => $query->result_array()
            );
        }else{
            return array(
                'status' => false,
                'result' => "No Records Found!"
            );
        }
    }
}