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
        echo json_encode($this->mylib->getPhysicians()->result_array());
    }
    public function Physicians_get(){
        echo json_encode($this->db->get('physicians')->result_array());
    }
    public function getPayperiod_get(){
        $year = $this->mylib->get_active_yr();
        echo json_encode($this->db->get("dm_pp{$year}")->result_array());
    }
    // public function totalRows_post(){
    //     $post = $this->input->post();
    //     $numRows = $this->db->get($post['table_name'])->num_rows();
    //     echo json_encode($numRows);
    // }
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
        $str = "select lab_id from appointment_lab_test where year(created_at) = '".$post['year']."' and month(created_at) = '".$post['month']."' ";
        $res = $this->db->query($str)->result_array();
        $submod = [];
        foreach($res as $row){
            // $rw = explode(",", $row['lab_id']);
            array_push($submod, $row['lab_id']);
            // foreach($row as $rw){
            //     array_push($submod, $rw);
            // }
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
        // $results['pending'] = $this->appointment_count('pending');
        // $results['released'] = $this->appointment_count('released');
        // $results['for_approval'] = $this->appointment_count('for_approval');
        // $results['all'] = $this->appointment_count('all');
        $results['all_patient'] = $this->appointment_count('all_patient');
        $results['all_pending'] = $this->appointment_count('all_pending');
        $results['all_cancelled'] = $this->appointment_count('all_cancelled');
        $results['completed'] = $this->appointment_count('completed');
        $results['cash'] = $this->appointment_count('cash');
        $results['change'] = $this->appointment_count('change');
        $results['total_cash'] = $this->appointment_count('total_cash');
        echo json_encode($results);
    }
    function getforreleased(){
        $today = date("Y-m-d");
        $str = "select aa.control_id,
        bb.patient_id,
        cc.lastname,
        cc.firstname,
        cc.middlename,
        IF(ee.send_out=1,CONCAT(aa.title,' (SEND OUT)'),aa.title) AS `title`,
        aa.abbr,
        IF(aa.status='D','DONE','FOR PRINTING') `status`
        FROM appointment_lab_test AS aa
        LEFT JOIN appointment_entries AS bb ON bb.id = aa.control_id
        LEFT JOIN patients AS cc ON cc.id = bb.patient_id
        LEFT JOIN laboratory_submodule AS dd ON dd.id = aa.lab_id
        LEFT JOIN laboratory_module AS ee ON ee.id = dd.mod_id
        WHERE DATE(aa.created_at) = date('{$today}')
        AND aa.`status` IN ('D','P')/*D=Done,P=Printing*/
        AND IF(bb.discount_id = 3, bb.approved = 'Y', bb.approved = '')
        ORDER BY aa.control_id,bb.patient_id";
        return $this->db->query($str)->result_array();
    }
    function appointment_count($status){
        $today = date("Y-m-d");
        // if($status == 'pending'){
        //     $str = "select count(id)as cnt from appointment_entries where date(created_at) = date('{$today}') and `status` = 'P'";
        // }elseif($status == 'released'){
        //     $str = "select count(aa.id)as cnt FROM appointment_lab_test AS aa
        //     LEFT JOIN appointment_entries AS bb ON bb.id = aa.control_id
        //     WHERE DATE(aa.created_at) = date('{$today}') 
        //     AND aa.`status` IN ('D','P')
        //     AND IF(bb.discount_id = 3, bb.approved = 'Y', bb.approved = '')";
        // }elseif($status == 'for_approval'){
        //     $str = "select count(id)as cnt from appointment_entries where date(created_at) = date('{$today}') and `status` = 'P' and discount_id = 3";
        // }
        
        if($status == 'all_patient'){
            $str = "SELECT COUNT(id) AS cnt FROM patients";
        }elseif($status == 'all_pending'){
            $str = "select count(id)as cnt from appointment_entries where SUBSTR(created_at,1,7) = SUBSTR('{$today}',1,7) and `status` = 'P'";
        }elseif($status == 'all_cancelled'){
            $str = "select count(id)as cnt from appointment_entries where SUBSTR(created_at,1,7) = SUBSTR('{$today}',1,7) and `status` = 'C'";
        }elseif($status == 'completed'){
            $str = "select count(id)as cnt from appointment_entries where SUBSTR(created_at,1,7) = SUBSTR('{$today}',1,7) and `status` = 'D'";
        }elseif($status == 'cash'){
            $str = "SELECT ifnull(SUM(cash),'0.00') AS cnt FROM appointment_entries WHERE `status` != 'C' and DATE(created_at) = DATE('{$today}')";
        }elseif($status == 'change'){
            $str = "SELECT ifnull(SUM(balance),'0.00') AS cnt FROM appointment_entries WHERE `status` != 'C' and DATE(created_at) = DATE('{$today}')";
        }elseif($status == 'total_cash'){
            $str = "SELECT ifnull(SUM(total_amount),'0.00') AS cnt FROM appointment_view WHERE `status` != 'C' and DATE(created_at) = DATE('{$today}')";
        }else{
            //ALL
            $str = "select count(id)as cnt from appointment_entries where date(created_at) = date('{$today}')";
        }
        return $this->db->query($str)->row_array()['cnt'];
    }

    public function getAppointments_get(){
        $post = $this->input->post();
        $timestamp = date("Y-m-d");
        $str = "select * from appointment_view where date(created_at) = '".$timestamp."' order by created_at desc";
        $results['all'] = $this->db->query($str)->result_array();
        $results['for_released'] = $this->getforreleased();
        $results['for_sendout'] = $this->db->get('appointment_sendout_view')->result_array();
        echo json_encode($results);
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

    public function getCompanyInfo_get(){
        echo json_encode($this->db->get('dm_company_info')->row_array());
    }

    public function saveCompanyInfo_post(){
        echo json_encode($this->datamaintenance->save_company_info($this->input->post()));
    }

}