<?php defined('BASEPATH') or exit('No direct script access allowed');

class Timekeeping_model extends CI_Model{

    public function getemployees(){
        $str = "select
        aa.id,
        concat(aa.lastname,', ',aa.firstname,' ',aa.middlename) as fullname,
        bb.desc as position_desc,
        cc.desc as employee_status_desc
        from employees as aa 
        left join dm_position as bb on bb.id = aa. position_id
        left join dm_employee_status as cc on cc.id = aa.employee_status_id";
        return $this->db->query($str);
    }

    public function getemployee($id){
        $str = "select
        aa.lastname,
        aa.firstname,
        aa.middlename,
        aa.id,
        bb.desc as `position_desc`,
        cc.desc as `employment_status_desc`
        FROM employees AS aa
        LEFT JOIN dm_position AS bb ON bb.id = aa.position_id
        LEFT JOIN dm_employment_status AS cc ON cc.id = aa.employment_status_id
        WHERE aa.id = {$id}";
        return $this->db->query($str);
    }

    public function employee_dtr($id){
        $this->db->trans_begin();
        $year = $this->mylib->get_active_yr();
        $payperiod = $this->mylib->get_active_pp();
        $employee = $this->db->get_where('employees', array('id' => $id))->row_array();
        $ws = $this->db->get_where('dm_work_schedule', array('ws_code' => $employee['ws_code']))->result_array();

        $this->db->query("drop temporary table if exists dtr");
        $temp_table = "create temporary table `dtr`
        select employee_id,`date`,`day`,`type`,ws_code,sched_amin,sched_amout,sched_pmin,sched_pmout,actual_amin,actual_amout,actual_pmin,actual_pmout,encoded_amin,encoded_amout,encoded_pmin,encoded_pmout,ot_start,ot_end,ut_start,ut_end
        from dtr_{$year} where employee_id = '".$employee['id']."' and `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";
        $this->db->query($temp_table);
        $temp_table = $this->db->get('dtr')->result_array();

        $interval = date_diff(date_create($payperiod['cfrom']), date_create($payperiod['cto']));
        $dtr = [];
        for($i = 0; $i <= $interval->days; $i++){
            $nextdate = date_format(date_add(date_create($payperiod['cfrom']), date_interval_create_from_date_string("{$i} days")), 'Y-m-d');
            $day = date_format(date_create($nextdate), "D");
            // $type = '';
            foreach($ws as $rw){
                if($rw['ws_day'] == $day){
                    $sched_amin = $nextdate . " " . $rw['ws_amin'];
                    $sched_amout = $nextdate . " " . $rw['ws_amout'];
                    $sched_pmin = $nextdate . " " . $rw['ws_pmin'];
                    $sched_pmout = $nextdate . " " . $rw['ws_pmout'];
                }
            }
            
            foreach($temp_table as $temp_row){
                if($temp_row['date'] == $nextdate){
                    $encoded_amin = $temp_row['encoded_amin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_amin'];
                    $encoded_amout = $temp_row['encoded_amout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_amout'];
                    $encoded_pmin = $temp_row['encoded_pmin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_pmin'];
                    $encoded_pmout = $temp_row['encoded_pmout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_pmout'];
                    $ot_start = $temp_row['ot_start'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ot_start'];
                    $ot_end = $temp_row['ot_end'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ot_end'];
                    $ut_start = $temp_row['ut_start'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ut_start'];
                    $ut_end = $temp_row['ut_end'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ut_end'];
                }
            }

            array_push($dtr, "(" . 
                $this->db->escape($employee['id']) .",". 
                $this->db->escape($nextdate) .",". 
                $this->db->escape($day) .",". 
                $this->db->escape($employee['ws_code']) .",". 
                $this->db->escape($sched_amin) .",". 
                $this->db->escape($sched_amout) .",". 
                $this->db->escape($sched_pmin) .",". 
                $this->db->escape($sched_pmout) .",". 
                $this->db->escape(!empty($encoded_amin) ? $encoded_amin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_amout) ? $encoded_amout : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_pmin) ? $encoded_pmin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_pmout) ? $encoded_pmout : '0000-00-00 00:00:00') .",".
                $this->db->escape(!empty($ot_start) ? $ot_start : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ot_end) ? $ot_end : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ut_start) ? $ut_start : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ut_end) ? $ut_end : '0000-00-00 00:00:00')
                . ")"
            );
        }
        //delete dtr records
        $str = "delete from dtr_{$year} where employee_id = '".$employee['id']."' and `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";
        $this->db->query($str);

        //insert dtr records
        $dtr_insert = "insert into dtr_{$year} (employee_id,date,day,ws_code,sched_amin,sched_amout,sched_pmin,sched_pmout,encoded_amin,encoded_amout,encoded_pmin,encoded_pmout,ot_start,ot_end,ut_start,ut_end)VALUES";
        $dtr_insert .= implode($dtr,",");
        $this->db->query($dtr_insert);

        $dtr_select = "select
        id,
        employee_id,
        date,
        day,
        ws_code,
        time(sched_amin)as sched_amin,
        time(sched_amout) as sched_amout,
        time(sched_pmin)as sched_pmin,
        time(sched_pmout)as sched_pmout,
        if(time(encoded_amin)='00:00:00','',time(encoded_amin))as encoded_amin,
        if(time(encoded_amout)='00:00:00','',time(encoded_amout))as encoded_amout,
        if(time(encoded_pmin)='00:00:00','',time(encoded_pmin))as encoded_pmin,
        if(time(encoded_pmout)='00:00:00','',time(encoded_pmout))as encoded_pmout,
        if(time(ot_start)='00:00:00','',time(ot_start))as ot_start,
        if(time(ot_end)='00:00:00','',time(ot_end))as ot_end,
        if(time(ut_start)='00:00:00','',time(ut_start))as ut_start,
        if(time(ut_end)='00:00:00','',time(ut_end))as ut_end
        from dtr_{$year} where employee_id = '".$employee['id']."' and `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result['status'] = false;
        }else{
            $this->db->trans_commit();
            $result['status'] = true;
            $result['query'] = $dtr_select;
        }
        return $result;
    }

    public function savedtr($data=array()){
        $year = $this->mylib->get_active_yr();
        $dtr = json_decode($data['dtr']);

        $this->db->trans_begin();
        foreach($dtr as $rw){
            
            $fields = array(
                'encoded_amin' => !empty($rw->encoded_amin) ? $rw->date ." ". $rw->encoded_amin . ":00" : "0000-00-00 00:00:00",
                'encoded_amout' => !empty($rw->encoded_amout) ? $rw->date ." ". $rw->encoded_amout . ":00" : "0000-00-00 00:00:00",
                'encoded_pmin' => !empty($rw->encoded_pmin) ? $rw->date ." ". $rw->encoded_pmin . ":00" : "0000-00-00 00:00:00",
                'encoded_pmout' => !empty($rw->encoded_pmout) ? $rw->date ." ". $rw->encoded_pmout . ":00" : "0000-00-00 00:00:00",
                'ot_start' => !empty($rw->ot_start) ? $rw->date ." ". $rw->ot_start . ":00" : "0000-00-00 00:00:00",
                'ot_end' => !empty($rw->ot_end) ? $rw->date ." ". $rw->ot_end . ":00" : "0000-00-00 00:00:00",
                'ut_start' => !empty($rw->ut_start) ? $rw->date ." ". $rw->ut_start . ":00" : "0000-00-00 00:00:00",
                'ut_end' => !empty($rw->ut_end) ? $rw->date ." ". $rw->ut_end . ":00" : "0000-00-00 00:00:00"
            );
            $this->db->update('dtr_'.$year, $fields, array('id' => $rw->id));
        }
        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = false;
        }else{
            $this->db->trans_commit();
            $result = true;
        }
        return $result;
    }

}