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

    public function Rebates_post(){
        $post = $this->input->post();
        $physicians = $this->db->get('physicians');
        if($physicians->num_rows() > 0){
            $physicians = $physicians->result_array();
            $arr = [];
            foreach($physicians as $k=>$physician){
                $post['id'] = $physician['id'];
                $amnt = $this->reportsmodel->total_rebates($post);
                array_push($arr, [
                    'id' => $physician['id'],
                    'firstname' => $physician['firstname'],
                    'middlename' => $physician['middlename'],
                    'lastname' => $physician['lastname'],
                    'total' => $amnt,
                    'total_rebates' => number_format(round(floatval($amnt) * 0.10,2),2),
                    'rebates' => $this->reportsmodel->rebates($post),
                    'breakdown' => $this->reportsmodel->rebates_brkdwn($post)
                    // 'rebates' => $this->reportsmodel->rebates($post),
                    // 'breakdown' => $this->reportsmodel->rebates_brkdwn($post)
                ]);
            }
        }
        echo json_encode($arr);
    }

    public function Census_post(){
        $results = $this->reportsmodel->dateRange($this->input->post());
        $new_arr = [];
        foreach($results as $row){
            array_push($new_arr, [
                'date' => $row,
                'results' => $this->reportsmodel->lab_count($row)
            ]);
        }
        echo json_encode($new_arr);
    }

    public function Sales_post(){
        $post = $this->input->post();
        $str = "select
        aa.id as control_no,
        concat(bb.lastname,', ',bb.firstname,' ',bb.middlename)as fullname,
        bb.age,
        bb.agetype,
        ee.desc as gender,
        CONCAT(IF(cc.gender = 'f','Dra. ','Dr. '),cc.lastname,', ',cc.firstname,' ',cc.middlename)AS 'physician',
        cc.gender as physician_gender,
        dd.text as discount,
        aa.discount_percent,
        aa.cash,
        aa.total_amount
        from appointment_entries as aa
        left join patients bb on bb.id = aa.patient_id
        left join physicians cc on cc.id = aa.physician_id
        left join dm_discount dd on dd.id = aa.discount_id
        left join dm_gender ee on ee.id = bb.gender
        where date(aa.created_at) = date('".$post['date']."')
        and approved != 'N'
        order by aa.created_at";
        $result = $this->db->query($str);

        $sum = "select
        sum(total_amount)as total_amount,
        sum(cash) as cash
        from appointment_entries
        where date(created_at) = date('".$post['date']."')
        and approved != 'N'";
        $sum = $this->db->query($sum)->row_array();

        if($result->num_rows() > 0){
            $result = array(
                'status' => true,
                'results' => $result->result_array(),
                'total_amount' => $sum['total_amount'],
                'total_cash' => $sum['cash']
            );
        }else{
            $result = array(
                'status' => false,
                'results' => [],
                'total_amount' => 0,
                'total_cash' => 0
            );
        }

        echo json_encode($result);
    }
    public function Payslip_post(){
        echo json_encode($this->reportsmodel->payslip($this->input->post()));
    }
}