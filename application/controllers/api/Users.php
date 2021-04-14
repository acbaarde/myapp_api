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
            $isLogin['message'] = 'You are now logged in!';
            $result = $isLogin;
        }else{
            $result = array(
                'message' => 'Invalid username or password',
                'status' => false
            );
        }

        echo json_encode($result);
    }

    public function register_post(){
        $isExist = $this->usermodel->checkuser($this->input->post('username'));
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
                'message' => 'Username already exist!',
                'status' => false
            );
        }
 
        echo json_encode($result);
    }

    public function test_get(){

        echo json_encode(date('h:i:s'));
    }

}