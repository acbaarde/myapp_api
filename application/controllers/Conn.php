<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Conn extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
    }

    public function index_get(){
        echo json_encode('Connected!');
    }
}