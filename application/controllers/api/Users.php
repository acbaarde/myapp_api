<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Users extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('User_model', 'usermodel');
    }

    public function index_post(){
        $query = $this->usermodel->login($this->input->post());
        $data = $this->db->query($query);
        if($data->num_rows() > 0){
            $row = $data->row_array();
            $result = array(
                'message' => 'Login successful!',
                'my_key' => $row['my_key'],
                'status' => true
            );
        }else{
            $result = array(
                'message' => 'error',
                'my_key' => null,
                'status' => false
            );
        }
        echo json_encode($result);
    }

}