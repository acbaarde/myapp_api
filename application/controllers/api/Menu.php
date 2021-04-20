<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Menu extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Menu_model', 'menumodel');
       $this->load->model('Mylib', 'mylib');
    }

    public function getAllModule_get(){
        echo json_encode($this->menumodel->get_all_module());
    }
}