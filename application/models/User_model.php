<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model{
    public function login($data = array()){
        $username = $data['username'];
        $password = $data['password'];

        return "select user.*,key.my_key from users as `user` left join api_keys as `key` on key.user_id = user.id
        where user.username = ".$this->db->escape(trim($username))."
        and user.password = ".$this->db->escape(trim($password));
    }
}