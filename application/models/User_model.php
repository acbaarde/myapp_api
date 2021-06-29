<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model{

    public function login($data = array()){
        $username = $data['username'];
        $password = $data['password'];

        $str = "select * from users as user where user_id = '{$username}' and password = '{$password}' and `active` = 'Y'";
        $query = $this->db->query($str);

        if($query->num_rows() > 0){
            $user = $query->row_array();
            $created_at = date('Y-m-d H:i:s');

            $fields = array(
                'last_login' => $created_at
            );        
            $this->db->trans_begin();
            $this->db->update('users', $fields, array('user_id' => $user['user_id']));
            if($this->db->trans_status() === false){
                $this->db->trans_rollback();
                $result['status'] = false;
            }else{
                // $insert_token = $this->insert_access_token($user_id,$created_at);
                // if($insert_token['status'] == true){
                //     $this->db->trans_commit();
                //     $result = $insert_token;
                // }else{
                //     $result = false;
                // }
                
                $this->db->trans_commit();
                $result = $user;
                $result['status'] = true;
            }
        }else{
            $result['status'] = false;
        }

        return $result;
    }

    public function register($data = array()){
        $user_id = $data['user_id'];
        // $password = $this->encryption->encrypt($data['password']);
        $password = $data['password'];
        $user_type = $data['user_type'];
        // $firstname = $data['firstname'];
        // $lastname = $data['lastname'];
        // $user_type = $data['user_type'];
        $created_at = date('Y-m-d H:i:s');

        $str = "select concat(lastname,', ',firstname,' ',middlename)as fullname from employees where id = {$user_id}";
        $emp = $this->db->query($str)->row_array();

        //insert users table
        $insert = array(
            'user_id' => $user_id,
            'password' => $password,
            'fullname' => $emp['fullname'],
            // 'firstname' => $firstname,
            // 'lastname' => $lastname,
            // 'user_type' => $user_type,
            'created_at' => $created_at,
            'active' => 'Y',
            'user_type' => $user_type
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
                'access_token' => $access_token,
                'expires_in' => $expires_in,
                'user_id' => $user_id
            );
        }
        return $result;
    }

    public function checkuser($user_id){
        $str = "select * from users where user_id = " . $this->db->escape(trim($user_id));
        $query = $this->db->query($str);
        if($query->num_rows() > 0){
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public function get_active_user($user_id){
        // $access_token = $data['access_token'];
        // $str = "select user.*,token.access_token from users as user left join users_access_token as token on token.user_id = user.id where token.access_token = '{$access_token}'";
        $str = "select * from users where user_id = '{$user_id}'";
        $query = $this->db->query($str)->row_array();
        return $query;
    }

    public function get_usermodaccess($user_id){
        $str = "select 
        aa.*,
        bb.mod_title,
        bb.mod_path
        from users_mod_access as aa
        left join mod_menu as bb on bb.id = aa.mod_id
        where aa.user_id = '{$user_id}'";
        $query = $this->db->query($str)->result_array();
        return $query;
    }

    public function delete_user($data = array()){
        $user_id = $data['id'];
        $this->db->delete('users', array('id' => $user_id));
        return $this->db->affected_rows();
    }

    public function update_user($data=array()){
        $user_id = $data['user_id'];
        // $username = $data['username'];
        $password = $data['password'];
        $user_type = $data['user_type'];
        // $firstname = $data['firstname'];
        // $lastname = $data['lastname'];
        // $user_type = $data['user_type'];

        $update = array(
            // 'user_id' => $user_id,
            'password' => $password,
            'user_type' => $user_type
            // 'firstname' => $firstname,
            // 'lastname' => $lastname,
            // 'user_type' => $user_type
        );
        $this->db->trans_begin();
        $this->db->update('users', $update, array('user_id' => $user_id));
        
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