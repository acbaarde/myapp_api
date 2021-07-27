<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Users extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('User_model', 'usermodel');
       $this->load->model('Mylib', 'mylib');
    }

    public function login_post(){
        $isLogin = $this->usermodel->login($this->input->post());
        if($isLogin['status'] == true){
            $active_user = $this->usermodel->get_active_user($isLogin['user_id']);
            $active_user['user_access'] = $this->usermodel->get_usermodaccess($isLogin['user_id']);
            $active_user['message'] = 'Login Successful!';
            $active_user['status'] = true;
            $result = $active_user;
        }else{
            $result = array(
                'message' => 'Invalid username or password!!!',
                'status' => false
            );
        }
        echo json_encode($result);
    }

    public function registerUser_post(){
        $isExist = $this->usermodel->checkuser($this->input->post('user_id'));
        if($isExist == false){
            $register = $this->usermodel->register($this->input->post());
            if($register == true){
                $result = array(
                    'message' => 'Registration success!',
                    'status' => true
                );
            }else{
                $result = array(
                    'message' => 'Registration failed!',
                    'status' => false
                );
            }
            
        }else{
            $result = array(
                'message' => 'User already exist!',
                'status' => false
            );
        }
 
        echo json_encode($result);
    }

    public function getEmployees_get(){
        $str = "select id,concat(lastname,', ',firstname,' ',middlename)as fullname,concat(id,' => ',concat(lastname,', ',firstname,' ',middlename))as `desc` from employees where employee_status_id = 'A'";
        echo json_encode($this->db->query($str)->result_array());
    }

    public function getUser_post(){
        echo json_encode($this->usermodel->get_active_user($this->input->post()));
    }

    public function getAllUser_get(){
        echo json_encode($this->usermodel->getalluser());
    }

    public function testpost_post(){
        echo json_encode($this->input->post());
    }

    public function deleteUser_post(){
        $affected_rows = $this->usermodel->delete_user($this->input->post());
        if($affected_rows == 1){
            $result = array(
                'message' => 'User deletion success!',
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'Error: Something happened!',
                'status' => false
            );
        }
        echo json_encode($result);
    }

    public function updateUser_post(){
        $update = $this->usermodel->update_user($this->input->post());
        if($update == true){
            $result = array(
                'message' => 'User update success!',
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'Error: Something happened!',
                'status' => false
            );
        }
        echo json_encode($result);
    }

    public function updatePassword_post(){
        echo json_encode($this->usermodel->update_password($this->input->post()));
    }
}