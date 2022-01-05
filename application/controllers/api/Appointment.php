<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Appointment extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Appointment_model', 'appointmentmodel');
       $this->load->model('Mylib', 'mylib');
    }
    public function getEntry_post(){
        $post = $this->input->post();
        $str = 'select aa.*,bb.firstname,bb.middlename,bb.lastname,bb.gender,bb.age,bb.agetype,bb.address
        FROM appointment_entries AS aa
        LEFT JOIN patients AS bb ON bb.id = aa.patient_id
        WHERE aa.id = "'.$post['id'].'" ';
        $results['patient'] = $this->db->query($str)->row_array();
        // $results['submodule'] = $this->db->get_where('appointment_lab_test', array('control_id' => $post['id']))->result_array();
        $str = 'select
            aa.id AS item_id,
            aa.lab_id AS id,
            aa.control_id,
            aa.`status`,
            cc.send_out,
            aa.title,
            aa.abbr,
            aa.amount,
            aa.created_by,
            aa.created_at,
            aa.printed_by,
            aa.printed_at,
            aa.so_clinic,
            aa.so_remarks,
            aa.so_status,
            aa.cancel_reason,
            aa.cancelled_by,
            aa.cancelled_at
        FROM
            appointment_lab_test AS aa
            LEFT JOIN laboratory_submodule AS bb ON bb.id = aa.lab_id
            LEFT JOIN laboratory_module AS cc ON cc.id = bb.mod_id
        WHERE control_id = "'.$post['id'].'" ';
        $results['submodule'] = $this->db->query($str)->result_array();
        echo json_encode($results);
    }
    public function getEntries_post(){
        $post = $this->input->post();
        $str = "select
            aa.id,
            aa.status,
            aa.patient_id,
            bb.lastname,
            bb.firstname,
            bb.middlename,
            aa.created_at,
            aa.approved,
            aa.discount_id,
            if(aa.status = 'P',0,if(aa.status = 'D',1,''))as ordr
            FROM appointment_entries AS aa
            LEFT JOIN patients AS bb ON bb.id = aa.patient_id
            LEFT JOIN physicians AS cc ON cc.id = aa.physician_id
            LEFT JOIN dm_discount AS dd ON dd.id = aa.discount_id
            where (aa.id like '%".$post['search_value']."%' or aa.patient_id like '%".$post['search_value']."%' or bb.lastname like '%".$post['search_value']."%' or bb.firstname like '%".$post['search_value']."%' or bb.middlename like '%".$post['search_value']."%')
            order by ordr,aa.created_at desc";
        $entries = $str ." limit ".$post['limit']." offset ".$post['offset'];
        $results['entries'] = $this->db->query($entries)->result_array();
        $results['total'] = $this->db->query($str)->num_rows();
        echo json_encode($results);
    }
    public function getCtrlNo_get(){
        $str = "select (id + 1)as control_no from appointment_entries order by id desc limit 1";
        $result = $this->db->query($str);
        if($result->num_rows() > 0){
            $result = $result->row_array();
        }else{
            $result = array('control_no' => 1);
        }
        echo json_encode($result);
    }

    public function insertEntry_post(){
        echo json_encode($this->appointmentmodel->insert_entry($this->input->post()));
    }
    public function updateEntry_post(){
        echo json_encode($this->appointmentmodel->update_entry($this->input->post()));
    }

    public function saveResultEntry_post(){
        echo json_encode($this->appointmentmodel->save_result_entry($this->input->post()));
    }

    public function getLabResults_post(){
        $post = $this->input->post();
        $results['lab_results'] = $this->db->get_where('appointment_lab_results', array('control_id' => $post['control_id'], 'lab_id' => $post['lab_id']))->result_array();
        $results['result_remarks'] = $this->db->get_where('appointment_lab_test', array('id' => $post['item_id']))->row_array()['remarks'];
        echo json_encode($results);
    }

    public function cancelLabTest_post(){
        echo json_encode($this->appointmentmodel->cancel_lab_test($this->input->post()));
    }
    public function approvedRejectEntry_post(){
        echo json_encode($this->appointmentmodel->approved_reject_entry($this->input->post()));
    }

    public function postEntry_post(){
        echo json_encode($this->appointmentmodel->post_entry($this->input->post()));
    }
    public function checkPendingLabTest_post(){
        $post = $this->input->post();
        $lab_test = $this->db->get_where('appointment_lab_test', array('control_id' => $post['id'], 'status' => ''));
        if($lab_test->num_rows() > 0){
            $result = true;
        }else{
            $result = false;
        }
        echo json_encode($result);
    }
    public function checkPayment_post(){
        $post = $this->input->post();
        $entry = $this->db->get_where('appointment_entries', array('id' => $post['id'], 'status' => 'P'))->row_array();
        if(intval($entry['cash']) >= intval($entry['total_amount'])){
            $result = true;
        }else{
            $result = false;
        }
        echo json_encode($result);
    }

    public function postPrintItem_post(){
        echo json_encode($this->appointmentmodel->post_print_item($this->input->post()));
    }
    public function reprintLabTest_post(){
        echo json_encode($this->appointmentmodel->reprint_lab_test($this->input->post()));
    }

    public function cancelEntry_post(){
        echo json_encode($this->appointmentmodel->cancel_entry($this->input->post()));
    }
    
    public function saveSendout_post(){
        echo json_encode($this->appointmentmodel->save_sendout($this->input->post()));
    }
}