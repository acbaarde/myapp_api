<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Laboratory extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Laboratory_model', 'laboratorymodel');
       $this->load->model('Mylib', 'mylib');
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
}