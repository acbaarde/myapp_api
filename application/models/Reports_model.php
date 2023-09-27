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

    public function rebates($data=array()){
        $post = $data;
        $str = "select 
        aa.physician_id,
        bb.lastname,
        bb.firstname,
        bb.middlename,
        bb.age,
        bb.gender,
        sum(cc.amount)as total_amount,
        aa.created_at
        from appointment_entries aa
        left join appointment_lab_test cc on cc.control_id = aa.id
        left join patients bb on bb.id = aa.patient_id
        where (aa.discount_id = '' OR aa.discount_id = 0 OR aa.discount_id IS NULL)
        and aa.discount_percent = 0
        and aa.approved != 'N'
        and aa.physician_id = '". $post['id'] ."'
        and date(aa.created_at) >= date('". $post['dateFrom'] ."')
        and date(aa.created_at) <= date('". $post['dateTo'] ."')
        and cc.so_status != 1
        group by date(aa.created_at)
        order by date(aa.created_at)";
        $query = $this->db->query($str);
        if($query->num_rows() > 0){
            $ddte = [];
            $result_rows = $query->result_array();
            $interval = date_diff(date_create($post['dateFrom']), date_create($post['dateTo']));
            for($i = 0; $i <= $interval->days; $i++){
                $nextdate = date_format(date_add(date_create($post['dateFrom']), date_interval_create_from_date_string("{$i} days")), 'Y-m-d');
                array_push($ddte, $nextdate);
            }
        }else{
            $result_rows = [];
        }

        return $result_rows;
    }
    public function rebates_brkdwn($data=array()){
        $post = $data;
        $str = "select 
        aa.id,
        aa.physician_id,
        cc.lastname,
        cc.firstname,
        cc.middlename,
        cc.age,
        cc.agetype,
        cc.gender,
        sum(dd.amount) as total_amount,
        DATE(aa.created_at)AS `date`
        FROM appointment_entries aa
        LEFT JOIN physicians bb ON bb.id = aa.physician_id
        LEFT JOIN patients cc ON cc.id = aa.patient_id
        left join appointment_lab_test dd on dd.control_id = aa.id
        WHERE (aa.discount_id = '' OR aa.discount_id = 0 OR aa.discount_id IS NULL)
        AND aa.discount_percent = 0
        AND aa.approved != 'N'
        AND physician_id = '".$post['id']."'
        AND DATE(aa.created_at) >= DATE('".$post['dateFrom']."')
        AND DATE(aa.created_at) <= DATE('".$post['dateTo']."')
        and dd.so_status != 1
        group by aa.id
        ORDER BY aa.physician_id,DATE(aa.created_at)";
        $results = $this->db->query($str)->result_array();

        $arr = [];
        foreach($results as $row){
            $lab_tests = $this->db->query("select abbr from appointment_lab_test where control_id = '".$row['id']."' and so_status != 1 ")->result_array();
            $arrLab = [];
            foreach($lab_tests as $rw){
                array_push($arrLab, $rw['abbr']);
            }
            $row['lab_test'] = implode(",", $arrLab);
            array_push($arr, $row);
        }
        return $arr;
    }

    public function total_rebates($data=array()){
        $post = $data;
        $str = "
        SELECT SUM(amount) AS `total` FROM appointment_entries AS ent
        LEFT JOIN appointment_lab_test AS lab ON lab.control_id = ent.id
        WHERE ent.physician_id = '".$post['id']."'
        AND (ent.discount_id = '' or ent.discount_id = 0 or ent.discount_id is null)
        AND ent.discount_percent = 0
        AND ent.approved != 'N'
        AND DATE(ent.created_at) >= DATE('".$post['dateFrom']."') 
        AND DATE(ent.created_at) <= DATE('".$post['dateTo']."')
        and lab.so_status != 1";
        return $this->db->query($str)->row_array()['total'];
    }

    public function dateRange($data=array()){
        $post = $data;
        $ddte = [];
        $interval = date_diff(date_create($post['dateFrom']), date_create($post['dateTo']));
        for($i = 0; $i <= $interval->days; $i++){
            $nextdate = date_format(date_add(date_create($post['dateFrom']), date_interval_create_from_date_string("{$i} days")), 'Y-m-d');
            array_push($ddte, $nextdate);
        }
        return $ddte;
    }

    public function lab_count($date){
        $submods = $this->db->query("select * from laboratory_submodule order by mod_id,id")->result_array();
        $id_arr = [];
        $submod_ids = $this->db->query("select lab_id FROM appointment_lab_test WHERE DATE(created_at) = DATE('".$date."')")->result_array();
        foreach($submod_ids as $id_row){
            $ids = explode(",", $id_row['lab_id']);
            foreach($ids as $id){
                array_push($id_arr, $id);
            }
        }
        $sub_arr = [];
        $res = [];
        foreach($submods as $row){
            $cnt = 0;
            foreach($id_arr as $idarr){
                if($idarr == $row['id']){
                    $cnt++;
                }
            }
            array_push($res, $cnt);
        }
        return $res;
    }

    public function payslip($data = array()){
        $post = $data;
        $payperiod = $this->db->get_where('dm_pp'.$post['year'], array('id' => $post['payperiod_id']))->row_array();
        $str = "select
        aa.*,
        bb.firstname,bb.lastname,bb.middlename,bb.position,
        cc.*,
        dd.id AS `adjustment_id`
        FROM payslip_".$post['year']." AS aa
        LEFT JOIN employee_view AS bb ON bb.id = aa.employee_id
        LEFT JOIN mhr_".$post['year']." AS cc ON cc.employee_id = aa.employee_id AND cc.payperiod = aa.payperiod
        LEFT JOIN salary_adjustments AS dd ON dd.employee_id = aa.employee_id AND dd.payperiod = aa.payperiod
        WHERE DATE(aa.payperiod) = DATE('".$payperiod['pperiod']."')";
        $employees = $this->db->query($str);

        $results = [];
        if($employees->num_rows() > 0){
            $employees = $employees->result_array();
            foreach($employees as $k=>$row){
                $sal_per_hr = floatval($row['salary']) / 8;
                array_push($results, [
                    'payperiod' => $row['payperiod'],
                    'lastname' => $row['lastname'],
                    'firstname' => $row['firstname'],
                    'middlename' => $row['middlename'],
                    'position' => $row['position'],
                    'salary' => $row['salary'],
                    'reg_hrs' => $row['regular'],
                    'reg_hrs_pay' => round($sal_per_hr * floatval($row['regular']),2),
                    'reg_ot' => $row['regular_ot'],
                    'reg_ot_pay' => round($sal_per_hr * floatval($row['regular_ot']),2),
                    'gross' => $row['gross'],
                    'net' => $row['net'],
                    'total_earnings' => $row['additions'],
                    'total_deductions' => $row['deductions'],
                    'deductions' => [],
                    'earnings' => []
                ]);

                $adjustments = $this->db->get_where('salary_adjustments_breakdown', array('adjustment_id' => $row['adjustment_id']))->result_array();
                foreach($adjustments as $adj_row){
                    if($adj_row['adjustment_code'] == 'A'){
                        array_push($results[$k]['earnings'], [
                            'description' => $adj_row['description'],
                            'amount' => $adj_row['amount']
                        ]);
                    }
                    if($adj_row['adjustment_code'] == 'D'){
                        array_push($results[$k]['deductions'], [
                            'description' => $adj_row['description'],
                            'amount' => $adj_row['amount']
                        ]);
                    }
                }
            }

            $result = array(
                'status' => true,
                'results' => $results
            );
        }else{
            $result = array(
                'status' => false,
            );
        }

        return $result;
    }
}