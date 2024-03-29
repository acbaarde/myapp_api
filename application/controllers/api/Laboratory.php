<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Laboratory extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Laboratory_model', 'laboratorymodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }

    public function getLabModule_get(){
        $result['module'] = $this->db->get('laboratory_module')->result_array();
        $result['submodule'] = $this->db->get('laboratory_submodule')->result_array();
        echo json_encode($result);
    }

    public function loadLabmodule_post(){
        $result = $this->laboratorymodel->loadlabmodule($this->input->post());
        $patientlabtest = $this->laboratorymodel->load_patientlabtest($this->input->post('appointment_id'))->result_array();
        if($result->num_rows() > 0){
            $rows = $result->result_array();
            $modules = [];
            foreach($rows as $key=>$row){
                array_push($modules,[
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'submodules' => [],
                    'subsubmodules' => []
                ]);
                $sub_rows = $this->laboratorymodel->loadsubmodule($row['id'])->result_array();
                foreach($sub_rows as $sub_key=>$sub_row){
                    array_push($modules[$key]['submodules'],[
                        'value' => $sub_row['id'],
                        'text' => $sub_row['title'] . " (" . $sub_row['amount'] . " Php)",
                        'amount' => $sub_row['amount'],
                        'mod_id' => $sub_row['mod_id'],
                        'abbr' => $sub_row['abbr']
                        // 'sub_test' => 0,
                        // 'sub_result' => '0.00'
                    ]);
                }
                $subsub_rows = $this->laboratorymodel->loadsubsubmodule($row['id'])->result_array();
                foreach($subsub_rows as $subsub_row){
                    $exist = 0;
                    foreach($patientlabtest as $labtest){
                        if($labtest['subsubmod_id'] == $subsub_row['id']){
                            array_push($modules[$key]['subsubmodules'],[
                                'id' => $subsub_row['id'],
                                'submod_id' => $subsub_row['submod_id'],
                                'title' => $subsub_row['title'],
                                'result' => $labtest['result_value'],
                                'submod_title' => $subsub_row['abbr'],
                                'result_range' => $subsub_row['result_range']
                            ]);
                            $exist++;
                        }
                    }
                    if($exist == 0){
                        array_push($modules[$key]['subsubmodules'],[
                            'id' => $subsub_row['id'],
                            'submod_id' => $subsub_row['submod_id'],
                            'title' => $subsub_row['title'],
                            'result' => '',
                            'submod_title' => $subsub_row['abbr'],
                            'result_range' => $subsub_row['result_range']
                        ]);
                    }
                }
            }

            $result = array(
                'status' => true,
                'modules' => $modules
            );

        }else{
            $result = array(
                'status' => false,
                'modules' => []
            );
        }

        echo json_encode($result);
    }

    public function saveModule_post(){
        $post = $this->input->post();
        $timestamp = date("Y-m-d H:i:s");
        if($post['id'] == 'undefined'){
            $insert['table_name'] = 'laboratory_module';
            $insert['fields'] = array(
                'title' => $post['title'],
                'send_out' => $post['send_out'],
                'created_by' => $post['user_id'],
                'created_at' => $timestamp
            );
            $insert = $this->builder->create_insert($insert);
            $result = array(
                'status' => true,
                'message' => 'Insert Success!'
            );
        }else{
            $update['table_name'] = 'laboratory_module';
            $update['fields'] = array(
                'title' => $post['title'],
                'send_out' => $post['send_out'],
                'updated_by' => $post['user_id'],
                'updated_at' => $timestamp
            );
            $update['filters'] = array('id' => $post['id']);
            $update = $this->builder->create_update($update);

            $result = array(
                'status' => true,
                'message' => 'Update Success!'
            );
        }
        echo json_encode($result);
    }

    public function saveSubModule_post(){
        $post = $this->input->post();
        $timestamp = date("Y-m-d H:i:s");

        if(isset($post['id'])){
            $update['table_name'] = 'laboratory_submodule';
            $update['fields'] = array(
                'mod_id' => $post['mod_id'],
                'title' => $post['title'],
                'amount' => $post['amount'],
                'abbr' => $post['abbr'],
                'updated_by' => $post['user_id'],
                'updated_at' => $timestamp
            );
            $update['filters'] = array('id' => $post['id']);
            $update = $this->builder->create_update($update);
            $result = array(
                'status' => true,
                'message' => 'Update Success!'
            );
        }else{
            $insert['table_name'] = 'laboratory_submodule';
            $insert['fields'] = array(
                'mod_id' => $post['mod_id'],
                'title' => $post['title'],
                'amount' => $post['amount'],
                'abbr' => $post['abbr'],
                'created_by' => $post['user_id'],
                'created_at' => $timestamp
            );
            $insert = $this->builder->create_insert($insert);
            $result = array(
                'status' => true,
                'message' => 'Insert Success!'
            );
        }
        echo json_encode($result);
    }

    public function saveSubSubModule_post(){
        $post = $this->input->post();
        $timestamp = date("Y-m-d H:i:s");

        if(isset($post['id'])){
            $update['table_name'] = 'laboratory_subsubmodule';
            $update['fields'] = array(
                'mod_id' => $post['mod_id'],
                'submod_id' => $post['submod_id'],
                'title' => $post['title'],
                'result_range' => $post['result_range'],
                'updated_by' => $post['user_id'],
                'updated_at' => $timestamp
            );
            $update['filters'] = array('id' => $post['id']);
            $update = $this->builder->create_update($update);
            $result = array(
                'status' => true,
                'message' => 'Update Success!'
            );
        }else{
            $insert['table_name'] = 'laboratory_subsubmodule';
            $insert['fields'] = array(
                'mod_id' => $post['mod_id'],
                'submod_id' => $post['submod_id'],
                'title' => $post['title'],
                'result_range' => $post['result_range'],
                'created_by' => $post['user_id'],
                'created_at' => $timestamp
            );
            $insert = $this->builder->create_insert($insert);
            $result = array(
                'status' => true,
                'message' => 'Insert Success!'
            );
        }
        echo json_encode($result);
    }
}