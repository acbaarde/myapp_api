<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Reports extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Reports_model', 'reportsmodel');
       $this->load->model('Mylib', 'mylib');
    }

    public function getPayperiod_post(){
        $post = $this->input->post();
        echo json_encode($this->db->get('dm_pp'.$post['year'])->result_array());
    }

    public function getYear_get(){
        $str = "SELECT `year` AS id, `year` AS `desc` FROM `year`";
        echo json_encode($this->db->query($str)->result_array());
    }

    public function getDtrinout_post(){
        $post = $this->input->post();
        $result = $this->reportsmodel->getdtrinout($post);
        if($result->num_rows() > 0){
            $employees = $this->reportsmodel->getemployees();
            $payperiod = $this->reportsmodel->getpayperiod($post);
            $result = array(
                'status' => true,
                'employees' => $employees->result_array(),
                'payperiod' => $payperiod->row_array(),
                'dtrinout' => $result->result_array()
            );
        }else{
            $result = array(
                'status' => false
            );
        }
        echo json_encode($result);
    }

    public function getManhourprooflist_post(){
        $post = $this->input->post();
        $res = $this->reportsmodel->getmanhourprooflist($post);
        if(count($res) > 0){
            $payperiod = $this->reportsmodel->getpayperiod($post);
            $result = array(
                'status' => true,
                'manhour' => $res,
                'payperiod' => $payperiod->row_array()
            );
        }else{
            $result = array(
                'status' => false
            );
        }
        echo json_encode($result);
    }
}