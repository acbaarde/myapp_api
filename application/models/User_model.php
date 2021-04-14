<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model{

    public function login($data = array()){
        $username = $data['username'];
        $password = $data['password'];

        $str = "select id from users as user where username = '{$username}' and password = '{$password}'";
        $query = $this->db->query($str);

        if($query->num_rows() > 0){
            $user_id = $query->row_array()['id'];
            $created_at = date('Y-m-d H:i:s');

            $fields = array(
                'last_login' => $created_at
            );        
            $this->db->trans_begin();
            $this->db->update('users', $fields, 'id = '. $user_id);
            if($this->db->trans_status() === false){
                $this->db->trans_rollback();
                $result = false;
            }else{
                $insert_token = $this->insert_access_token($user_id,$created_at);
                if($insert_token['status'] == true){
                    $this->db->trans_commit();
                    $result = $insert_token;
                }else{
                    $result = false;
                }
            }
        }else{
            $result = false;
        }

        return $result;
    }

    public function register($data = array()){
        $username = $data['username'];
        $password = $this->encryption->encrypt($data['password']);
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $user_type = $data['user_type'];
        $created_at = date('Y-m-d H:i:s');

        //insert users table
        $insert = array(
            'username' => $username,
            'password' => $password,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'user_type' => $user_type,
            'created_at' => $created_at
        );

        $this->db->trans_begin();
        $this->db->insert('users', $insert);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = true;
        }
        return $result;
    }

    function insert_access_token($user_id,$created_at){
        $access_token = $this->mylib->generate_string($this->mylib->permitted_chars(), 50);
        $expires_in = date('Y-m-d H:i:s');

        $insert = array(
            'access_token' => $access_token,
            'user_id' => $user_id,
            'created_at' => $created_at,
            'expires_in' => $expires_in
        );
        $this->db->trans_begin();
        $this->db->insert('users_access_token', $insert);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'token' => $access_token,
                'expires_in' => $expires_in,
                'user_id' => $user_id
            );
        }
        return $result;
    }

    public function checkuser($username){
        $str = "select * from users where username = " . $this->db->escape(trim($username));
        $query = $this->db->query($str);
        if($query->num_rows() > 0){
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

}