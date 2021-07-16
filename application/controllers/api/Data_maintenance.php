<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Data_maintenance extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Datamaintenance_model', 'datamaintenance');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }

    public function loadDatamaintenance_get(){
        $result = array(
            'gender' => $this->db->get('dm_gender')->result_array(),
            'employment_status' => $this->db->get('dm_employment_status')->result_array(),
            'employee_status' => $this->db->get('dm_employee_status')->result_array(),
            'employment_type' => $this->db->get('dm_employment_type')->result_array(),
            'position' => $this->db->get('dm_position')->result_array(),
            'citizenship' => $this->db->get('dm_citizenship')->result_array(),
            'religion' => $this->db->get('dm_religion')->result_array(),
            'region' => $this->db->get('dm_region')->result_array(),
            'province_city' => $this->db->get('dm_province_city')->result_array(),
            'barangay_town' => $this->db->get('dm_barangay_town')->result_array(),
            'civil_status' => $this->db->get('dm_civil_status')->result_array(),
            'work_shift' => $this->db->query('select ws_code, concat(ws_code," => ",`desc`)as `desc` from dm_work_shift order by ws_code')->result_array()
        );
        echo json_encode($result);
    }

    public function getGender_get(){
        echo json_encode($this->db->get('dm_gender')->result_array());
    }
    public function getDiscount_get(){
        echo json_encode($this->db->get('dm_discount')->result_array());
    }
    public function getPhysicians_get(){
        echo json_encode($this->db->get('physicians')->result_array());
    }
    public function getPayperiod_get(){
        $year = $this->mylib->get_active_yr();
        echo json_encode($this->db->get("dm_pp{$year}")->result_array());
    }
    public function getWorksched_get(){
        $result = array(
            'status' => true,
            'work_shift' => $this->db->query('select * from dm_work_shift order by ws_code')->result_array(),
            'work_sched' => $this->db->get('dm_work_schedule')->result_array()
        );
        echo json_encode($result);
    }

    public function insertPhysician_post(){
        echo json_encode($this->datamaintenance->insertphysician($this->input->post()));
    }
    public function updatePhysician_post(){
        echo json_encode($this->datamaintenance->updatephysician($this->input->post()));
    }

    public function insertPayperiod_post(){
        echo json_encode($this->datamaintenance->insertpayperiod($this->input->post()));
    }
    public function updatePayperiod_post(){
        echo json_encode($this->datamaintenance->updatepayperiod($this->input->post()));
    }

    public function insertWorksched_post(){
        echo json_encode($this->datamaintenance->insertworksched($this->input->post()));
    }
    public function updateWorksched_post(){
        echo json_encode($this->datamaintenance->updateworksched($this->input->post()));
    }

    public function dashboardData_post(){
        $post = $this->input->post();
        $str = "select submod_id from appointments where year(created_at) = '".$post['year']."' and month(created_at) = '".$post['month']."' ";
        $res = $this->db->query($str)->result_array();
        $submod = [];
        foreach($res as $row){
            $row = explode(",", $row['submod_id']);
            foreach($row as $rw){
                array_push($submod, $rw);
            }
        }

        $str = "select id,abbr from laboratory_submodule";
        $res = $this->db->query($str)->result_array();

        $new_submod = [];
        foreach($res as $k=>$rw){
            array_push($new_submod, array(
                'abbr' => $rw['abbr']
            ));
            $i=1;
            foreach($submod as $srw){
                if($srw == $rw['id']){
                    $new_submod[$k]['value'] = $i++;
                }
            }
            if(!isset($new_submod[$k]['value'])){
                $new_submod[$k]['value'] = 0;
            }
        }
        $results['submod'] = $new_submod;
        $results['pending'] = $this->appointment_count('pending');
        $results['released'] = $this->appointment_count('released');
        $results['all'] = $this->appointment_count('all');
        echo json_encode($results);
    }
    function appointment_count($status){
        $today = date("Y-m-d");
        if($status == 'pending'){
            $optn = " and `approved` = '' ";
        }elseif($status == 'released'){
            $optn = " and `status` = 'F' and `approved` = 'Y'";
        }else{
            $optn = "";
        }
        $str = "select count(id)as cnt from appointments where date(created_at) = date('{$today}') {$optn}";
        return $this->db->query($str)->row_array()['cnt'];
    }

    public function getAppointments_get(){
        $post = $this->input->post();
        $timestamp = date("Y-m-d");
        $str = "select * from appointment_view where date(created_at) = '".$timestamp."' order by created_at";
        echo json_encode($this->db->query($str)->result_array());
    }

    public function loadModule_get(){
        $submodule = "select 
        bb.id as mod_id,
        aa.id,
        bb.title as mod_title,
        aa.title,
        aa.amount,
        aa.abbr
        from laboratory_submodule aa
        left join laboratory_module bb on bb.id = aa.mod_id
        order by aa.mod_id,aa.title";

        $labtest = "select 
        aa.id,
        aa.submod_id,
        bb.title as submod_title,
        aa.title,
        aa.result_range
        from laboratory_subsubmodule aa
        left join laboratory_submodule bb on bb.id = aa.submod_id
        order by aa.mod_id,aa.submod_id,aa.title";

        $result = array(
            'status' => true,
            'module' => $this->db->get('laboratory_module')->result_array(),
            'submodule' => $this->db->query($submodule)->result_array(),
            'labtest' => $this->db->query($labtest)->result_array()
        );
        echo json_encode($result);
    }

}