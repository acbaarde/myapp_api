<?php defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends CI_Model{

    public function getpayperiod($data=array()){
        $year = $data['year'];
        $payperiod_id = $data['payperiod_id'];
        return $this->db->get_where('dm_pp'.$year, array('id' => $payperiod_id));
    }
    public function getdtrinout($data=array()){
        $year = $data['year'];
        $payperiod_id = $data['payperiod_id'];
        $payperiod = $this->getpayperiod($data)->row_array();

        $str = "select * from dtr_{$year} where `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";
        return $this->db->query($str);
    }
    public function getemployees(){
        $str = "select
        aa.id,
        aa.lastname,
        aa.firstname,
        aa.middlename,
        bb.desc as position_desc
        FROM employees AS aa
        LEFT JOIN dm_position AS bb ON bb.id = aa.position_id
        WHERE employee_status_id != 'S'";
        return $this->db->query($str);
    }
    public function getmanhourprooflist($data=array()){
        $year = $data['year'];
        $payperiod = $this->getpayperiod($data)->row_array();
        $str = "select 
        aa.*,
        bb.firstname,
        bb.lastname,
        bb.middlename 
        FROM mhr_{$year} AS aa
        LEFT JOIN employees AS bb ON bb.id = aa.employee_id
        WHERE aa.payperiod = DATE('".$payperiod['pperiod']."')";
        $res = $this->db->query($str);
        if($res->num_rows() > 0){
            $res = $res->result_array();
            $result = [];
            foreach($res as $rw){
                $data = array(
                    'employee_id' => $rw['employee_id'],
                    'firstname' => $rw['firstname'],
                    'lastname' => $rw['lastname'],
                    'middlename' => $rw['middlename'],
                    'regular' => $this->mylib->NumToDHM10($rw['regular']),
                    'restday' => $this->mylib->NumToDHM10($rw['restday']),
                    'regular_ot' => $this->mylib->NumToHrMin2($rw['regular_ot']),
                    'regular_ut' => $this->mylib->NumToHrMin2($rw['regular_ut']),
                    'restday_ot' => $this->mylib->NumToHrMin2($rw['restday_ot']),
                    'restday_ut' => $this->mylib->NumToHrMin2($rw['restday_ut']),
                    'total_tardy' => round($rw['total_tardy']*60,0,2)
                );
                array_push($result, $data);
            }
        }else{
            $result = [];
        }

        return $result;
    }
}