<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Laboratory extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Laboratory_model', 'laboratorymodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
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
                        'mod_id' => $sub_row['mod_id']
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
                                'submod_title' => $subsub_row['abbr']
                            ]);
                            $exist++;
                        }
                    }
                    if($exist == 0){
                        array_push($modules[$key]['subsubmodules'],[
                            'id' => $subsub_row['id'],
                            'submod_id' => $subsub_row['submod_id'],
                            'title' => $subsub_row['title'],
                            'result' => '0.00',
                            'submod_title' => $subsub_row['abbr']
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
                'title' => strtoupper($post['title']),
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
                'title' => strtoupper($post['title']),
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
                'title' => strtoupper($post['title']),
                'amount' => $post['amount'],
                'abbr' => strtoupper($post['abbr']),
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
                'title' => strtoupper($post['title']),
                'amount' => $post['amount'],
                'abbr' => strtoupper($post['abbr']),
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
                'submod_id' => strtoupper($post['submod_id']),
                'title' => strtoupper($post['title']),
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
                'submod_id' => strtoupper($post['submod_id']),
                'title' => strtoupper($post['title']),
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