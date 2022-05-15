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
        left join dm_employee_status as cc on cc.id = aa.employee_status_id
        where aa.employee_status_id in ('A','H')";
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
        $payperiod = $this->mylib->get_active_pp()->row_array();
        $employee = $this->db->get_where('employees', array('id' => $id))->row_array();
        $ws = $this->db->get_where('dm_work_schedule', array('ws_code' => $employee['ws_code']))->result_array();

        $this->db->query("drop temporary table if exists dtr");
        $temp_table = "create temporary table `dtr`
        select employee_id,`date`,`day`,`type`,ws_code,sched_amin,sched_amout,sched_pmin,sched_pmout,cws_amin,cws_amout,cws_pmin,cws_pmout,actual_amin,actual_amout,actual_pmin,actual_pmout,encoded_amin,encoded_amout,encoded_pmin,encoded_pmout,ot_start,ot_end,ut_start,ut_end,am_lates,pm_lates,am_min,pm_min,ot_min,ut_min
        from dtr_{$year} where employee_id = '".$employee['id']."' and `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";
        $this->db->query($temp_table);
        $temp_table = $this->db->get('dtr')->result_array();

        $interval = date_diff(date_create($payperiod['cfrom']), date_create($payperiod['cto']));
        $dtr = [];
        for($i = 0; $i <= $interval->days; $i++){
            $nextdate = date_format(date_add(date_create($payperiod['cfrom']), date_interval_create_from_date_string("{$i} days")), 'Y-m-d');
            $day = date_format(date_create($nextdate), "D");
            if($employee['ordinary_restday'] == $day){
                $type = "D";
            }elseif($employee['original_restday'] == $day){
                $type = "D";
            }else{
                $type = "R";
            }
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
                    $cws_amin = $temp_row['cws_amin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['cws_amin'];
                    $cws_amout = $temp_row['cws_amout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['cws_amout'];
                    $cws_pmin = $temp_row['cws_pmin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['cws_pmin'];
                    $cws_pmout = $temp_row['cws_pmout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['cws_pmout'];
                    $encoded_amin = $temp_row['encoded_amin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_amin'];
                    $encoded_amout = $temp_row['encoded_amout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_amout'];
                    $encoded_pmin = $temp_row['encoded_pmin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_pmin'];
                    $encoded_pmout = $temp_row['encoded_pmout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['encoded_pmout'];
                    $actual_amin = $temp_row['actual_amin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['actual_amin'];
                    $actual_amout = $temp_row['actual_amout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['actual_amout'];
                    $actual_pmin = $temp_row['actual_pmin'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['actual_pmin'];
                    $actual_pmout = $temp_row['actual_pmout'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['actual_pmout'];
                    $ot_start = $temp_row['ot_start'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ot_start'];
                    $ot_end = $temp_row['ot_end'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ot_end'];
                    $ut_start = $temp_row['ut_start'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ut_start'];
                    $ut_end = $temp_row['ut_end'] == '0000-00-00 00:00:00' ? '0000-00-00 00:00:00' : $temp_row['ut_end'];
                    $am_lates = $temp_row['am_lates'];
                    $pm_lates = $temp_row['pm_lates'];
                    $am_min = $temp_row['am_min'];
                    $pm_min = $temp_row['pm_min'];
                    $ot_min = $temp_row['ot_min'];
                    $ut_min = $temp_row['ut_min'];
                }
            }

            array_push($dtr, "(" . 
                $this->db->escape($employee['id']) .",". 
                $this->db->escape($nextdate) .",". 
                $this->db->escape($day) .",". 
                $this->db->escape($type) .",". 
                $this->db->escape($employee['ws_code']) .",". 
                $this->db->escape($sched_amin) .",". 
                $this->db->escape($sched_amout) .",". 
                $this->db->escape($sched_pmin) .",". 
                $this->db->escape($sched_pmout) .",". 
                $this->db->escape(!empty($cws_amin) ? $cws_amin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($cws_amout) ? $cws_amout : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($cws_pmin) ? $cws_pmin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($cws_pmout) ? $cws_pmout : '0000-00-00 00:00:00') .",".
                $this->db->escape(!empty($encoded_amin) ? $encoded_amin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_amout) ? $encoded_amout : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_pmin) ? $encoded_pmin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($encoded_pmout) ? $encoded_pmout : '0000-00-00 00:00:00') .",".
                $this->db->escape(!empty($actual_amin) ? $actual_amin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($actual_amout) ? $actual_amout : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($actual_pmin) ? $actual_pmin : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($actual_pmout) ? $actual_pmout : '0000-00-00 00:00:00') .",".
                $this->db->escape(!empty($ot_start) ? $ot_start : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ot_end) ? $ot_end : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ut_start) ? $ut_start : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($ut_end) ? $ut_end : '0000-00-00 00:00:00') .",". 
                $this->db->escape(!empty($am_lates) ? $am_lates : '0.00') .",". 
                $this->db->escape(!empty($pm_lates) ? $pm_lates : '0.00') .",". 
                $this->db->escape(!empty($am_min) ? $am_min : '0.00') .",". 
                $this->db->escape(!empty($pm_min) ? $pm_min : '0.00') .",". 
                $this->db->escape(!empty($ot_min) ? $ot_min : '0.00') .",". 
                $this->db->escape(!empty($ut_min) ? $ut_min : '0.00') . ")"
            );
        }
        //delete dtr records
        $str = "delete from dtr_{$year} where employee_id = '".$employee['id']."' and `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."')";
        $this->db->query($str);

        //insert dtr records
        $dtr_insert = "insert into dtr_{$year} (employee_id,`date`,`day`,`type`,ws_code,sched_amin,sched_amout,sched_pmin,sched_pmout,cws_amin,cws_amout,cws_pmin,cws_pmout,encoded_amin,encoded_amout,encoded_pmin,encoded_pmout,actual_amin,actual_amout,actual_pmin,actual_pmout,ot_start,ot_end,ut_start,ut_end,am_lates,pm_lates,am_min,pm_min,ot_min,ut_min)VALUES";
        $dtr_insert .= implode($dtr,",");
        $this->db->query($dtr_insert);

        $dtr_select = "select
        id,
        employee_id,
        `date`,
        `day`,
        `type`,
        ws_code,
        time(sched_amin)as sched_amin,
        time(sched_amout) as sched_amout,
        time(sched_pmin)as sched_pmin,
        time(sched_pmout)as sched_pmout,
        if(time(cws_amin)='00:00:00','',time(cws_amin))as cws_amin,
        if(time(cws_amout)='00:00:00','',time(cws_amout))as cws_amout,
        if(time(cws_pmin)='00:00:00','',time(cws_pmin))as cws_pmin,
        if(time(cws_pmout)='00:00:00','',time(cws_pmout))as cws_pmout,
        if(time(encoded_amin)='00:00:00','',time(encoded_amin))as encoded_amin,
        if(time(encoded_amout)='00:00:00','',time(encoded_amout))as encoded_amout,
        if(time(encoded_pmin)='00:00:00','',time(encoded_pmin))as encoded_pmin,
        if(time(encoded_pmout)='00:00:00','',time(encoded_pmout))as encoded_pmout,
        if(time(actual_amin)='00:00:00','',time(actual_amin))as actual_amin,
        if(time(actual_amout)='00:00:00','',time(actual_amout))as actual_amout,
        if(time(actual_pmin)='00:00:00','',time(actual_pmin))as actual_pmin,
        if(time(actual_pmout)='00:00:00','',time(actual_pmout))as actual_pmout,
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
                'cws_amin' => !empty($rw->cws_amin) ? $rw->date ." ". $rw->cws_amin . ":00" : "0000-00-00 00:00:00",
                'cws_amout' => !empty($rw->cws_amout) ? $rw->date ." ". $rw->cws_amout . ":00" : "0000-00-00 00:00:00",
                'cws_pmin' => !empty($rw->cws_pmin) ? $rw->date ." ". $rw->cws_pmin . ":00" : "0000-00-00 00:00:00",
                'cws_pmout' => !empty($rw->cws_pmout) ? $rw->date ." ". $rw->cws_pmout . ":00" : "0000-00-00 00:00:00",
                'encoded_amin' => !empty($rw->encoded_amin) ? $rw->date ." ". $rw->encoded_amin . ":00" : "0000-00-00 00:00:00",
                'encoded_amout' => !empty($rw->encoded_amout) ? $rw->date ." ". $rw->encoded_amout . ":00" : "0000-00-00 00:00:00",
                'encoded_pmin' => !empty($rw->encoded_pmin) ? $rw->date ." ". $rw->encoded_pmin . ":00" : "0000-00-00 00:00:00",
                'encoded_pmout' => !empty($rw->encoded_pmout) ? $rw->date ." ". $rw->encoded_pmout . ":00" : "0000-00-00 00:00:00",
                'actual_amin' => !empty($rw->encoded_amin) ? $rw->date ." ". $rw->encoded_amin . ":00" : "actual_amin",
                'actual_amout' => !empty($rw->encoded_amout) ? $rw->date ." ". $rw->encoded_amout . ":00" : "actual_amout",
                'actual_pmin' => !empty($rw->encoded_pmin) ? $rw->date ." ". $rw->encoded_pmin . ":00" : "actual_pmin",
                'actual_pmout' => !empty($rw->encoded_pmout) ? $rw->date ." ". $rw->encoded_pmout . ":00" : "actual_pmout",
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

    public function processmanhour(){
        $year = $this->mylib->get_active_yr();
        $payperiod = $this->mylib->get_active_pp()->row_array();
        // $posting_logs = $this->db->get_where('posting_logs', array('payperiod' => $payperiod['pperiod'], 'module' => 'postmanhour'));
        $posting_logs = $this->mylib->check_postinglogs($payperiod['pperiod'],'postmanhour');
        $employees = $this->db->get_where('employees', "employee_status_id in ('A','H')")->result_array();
        $grace_period = $this->db->get('dm_work_shift')->result_array();
        $empno = [];
        foreach($employees as $emp){
            array_push($empno, $emp['id']);
        }
        $empno = implode(",", $empno);

        $pansamantala = "pansamantala";
        $temp_dtr = "{$pansamantala}.dtr_".$this->mylib->random_string(10);
        $temp_mhr = "{$pansamantala}.mhr_".$this->mylib->random_string(10);
        //check if manhour posting already done
        if($posting_logs == false){
            $str = "create table if not exists {$temp_dtr}
            select * from dtr_{$year} where `date` >= date('".$payperiod['cfrom']."') and `date` <= date('".$payperiod['cto']."') and employee_id in ({$empno})";
            $this->db->query($str);

            $str = "create table if not exists {$temp_mhr} like mhr_{$year}";
            $this->db->query($str);

            $str = "update {$temp_dtr}
                SET
                    am_lates = (case
                    when encoded_amin > if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin)
                        then (hour(timediff(encoded_amin, if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin))) * 60) + (minute(timediff(encoded_amin, if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin))))
                    when encoded_amin <= if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin)
                        then 0
                    WHEN (encoded_amin = '0000-00-00 00:00:00'
                    OR encoded_amout = '0000-00-00 00:00:00')
                        THEN 0
                    else am_lates
                    end
                    ),
                    pm_lates = (case
                    when encoded_pmin > if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin)
                        then (hour(timediff(encoded_pmin, if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin))) * 60) + (minute(timediff(encoded_pmin, if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin))))
                    when encoded_pmin <= if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin)
                        then 0
                    WHEN (encoded_pmin = '0000-00-00 00:00:00'
                    OR encoded_pmout = '0000-00-00 00:00:00')
                        THEN 0
                    else pm_lates
                    end
                    ),
                    am_min = (case
                    when encoded_amin >= if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin)
                    and encoded_amout >= if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout)
                        then (hour(timediff(encoded_amin, if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout))) * 60) + (minute(timediff(encoded_amin, if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout))))
                       
                    when encoded_amin <= if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin)
                    and encoded_amout >= if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout)
                        then (hour(timediff(if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin), if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout))) * 60) + (minute(timediff(if(cws_amin!='0000-00-00 00:00:00',cws_amin,sched_amin), if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout))))

                    when encoded_amin != '0000-00-00 00:00:00'
                    and encoded_amout < if(cws_amout!='0000-00-00 00:00:00',cws_amout,sched_amout)
                        then 0
                    WHEN (encoded_amin = '0000-00-00 00:00:00'
                    OR encoded_amout = '0000-00-00 00:00:00')
                        THEN 0
                    else am_min
                    end
                    ),
                    pm_min = (case
                    when encoded_pmin <= if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin)
                    and encoded_pmout >= if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout)
                        then (hour(timediff(if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout), if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin))) * 60) + (minute(timediff(if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout), if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin))))
                        
                    when encoded_pmin > if(cws_pmin!='0000-00-00 00:00:00',cws_pmin,sched_pmin)
                    and encoded_pmout >= if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout)
                        then (hour(timediff(if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout), encoded_pmin)) * 60) + (minute(timediff(if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout), encoded_pmin)))
                        
                    when encoded_pmin != '0000-00-00 00:00:00'
                    and encoded_pmout < if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout)
                        then 0
                    
                    when (encoded_pmin = '0000-00-00 00:00:00'
                    OR encoded_pmout = '0000-00-00 00:00:00')
                        then 0
                    else pm_min
                    end
                    ),
                    ot_min = (case
                    when encoded_pmout >= if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout)
                    and ot_start >= if(cws_pmout!='0000-00-00 00:00:00',cws_pmout,sched_pmout)
                    and ot_end <= encoded_pmout
                        then (hour(timediff(ot_end, ot_start)) * 60) + (minute(timediff(ot_end, ot_start))) 
                    when (ot_start = '0000-00-00 00:00:00'
                    OR ot_end = '0000-00-00 00:00:00')
                        then 0
                    else ot_min
                    end
                    )
                where `date` >= date('".$payperiod['cfrom']."')
                    and `date` <= date('".$payperiod['cto']."')";
            $this->db->query($str);

            $res = $this->db->get($temp_dtr)->result_array();
            foreach($res as $rw){
                foreach($grace_period as $graceperiod_row){
                    if($graceperiod_row['ws_code'] == $rw['ws_code']){
                        $gp_mins = $graceperiod_row['grace_period'];
                        $am_lates = $rw['am_lates'] <= $gp_mins ? 0 : $rw['am_lates'] - $gp_mins;
                        $am_min = $rw['am_lates'] <= $gp_mins ? $rw['am_min'] + $rw['am_lates'] : $rw['am_min'] + $gp_mins;
                        $update = "update {$temp_dtr} set am_lates = {$am_lates}, am_min = {$am_min} where id = '".$rw['id']."' ";
                        $this->db->query($update);
                    }
                }
            }

            $str = "update dtr_{$year} aa, {$temp_dtr} bb set
                    aa.am_lates = bb.am_lates,
                    aa.pm_lates = bb.pm_lates,
                    aa.am_min = bb.am_min,
                    aa.pm_min = bb.pm_min,
                    aa.ot_min = bb.ot_min,
                    aa.ut_min = bb.ut_min
                    where aa.id = bb.id";
            $this->db->query($str);

            $str = "insert into {$temp_mhr} (employee_id,payperiod,total_tardy,regular,regular_ot,regular_ut)
            (select 
            employee_id,
            '".$payperiod['pperiod']."' as pperiod,
            round(((SUM(am_lates) + SUM(pm_lates))/60),2) AS total_tardy,
            ROUND(((SUM(am_min) + SUM(pm_min))/60),2) AS regular,
            ROUND((SUM(ot_min))/60,2) AS regular_ot,
            ROUND((SUM(ut_min))/60,2) AS regular_ut
            FROM {$temp_dtr} 
            WHERE `date` >= date('".$payperiod['cfrom']."')
            AND `date` <= date('".$payperiod['cto']."')
            AND `type` = 'R'
            GROUP BY employee_id)";
            $this->db->query($str);

            $str = "delete from mhr_{$year} where payperiod = date('".$payperiod['pperiod']."')";
            $this->db->query($str);
            
            $str = "delete from {$temp_mhr} where payperiod = date('".$payperiod['pperiod']."') and `regular` = 0";
            $this->db->query($str);

            $str = "insert into mhr_{$year}(employee_id,payperiod,total_tardy,regular,regular_ot,regular_ut,restday,restday_ot,restday_ut)
            (select employee_id,payperiod,total_tardy,regular,regular_ot,regular_ut,restday,restday_ot,restday_ut
            from {$temp_mhr} where payperiod = date('".$payperiod['pperiod']."') )";
            $this->db->query($str);

            //process salary adjustments
            $str = "select * from mhr_{$year} where payperiod = date('".$payperiod['pperiod']."')";
            $result = $this->db->query($str)->result_array();
            foreach($result as $rw){
                $str = "select * from salary_adjustments where employee_id = '".$rw['employee_id']."' and payperiod = '".$rw['payperiod']."' ";
                $adjstmnts = $this->db->query($str);

                foreach($employees as $emp_rw){
                    $salary = 0;
                    if($emp_rw['id'] == $rw['employee_id']){
                        $salary = $emp_rw['salary'];
                        break;
                    }
                }

                $gross = (floatval($salary) / 8) * floatval($rw['regular']);
                $fields = array(
                    'employee_id' => $rw['employee_id'],
                    'payperiod' => $rw['payperiod'],
                    'salary' => $salary,
                    'gross' => round($gross,2)
                );

                if($adjstmnts->num_rows() > 0){
                    $adjstmnts = $adjstmnts->row_array();
                    $fields['net'] = round($gross - floatval($adjstmnts['adjustments']),2);
                    $this->db->update('salary_adjustments', $fields, array('id' => $adjstmnts['id']));
                }else{
                    $fields['net'] = round($gross,2);
                    $this->db->insert('salary_adjustments', $fields);
                }
            }
            //end salary adjustments

            $result = array(
                'status' => true,
                'message' => 'Process Manhour Success!'
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'Manhour Posting Already Done!!!'
            );
        }
        
        $sstr = "drop table if exists {$temp_dtr}";
        $this->db->query($sstr);

        $sstr = "drop table if exists {$temp_mhr}";
        $this->db->query($sstr);

        return $result;
    }

    public function postmanhour($data=array()){
        $user_id = $data['user_id'];
        $year = $this->mylib->get_active_yr();
        $payperiod = $this->mylib->get_active_pp()->row_array();
        // $posting_logs = $this->db->get_where('posting_logs', array('payperiod' => $payperiod['pperiod'], 'module' => 'postmanhour'));
        $posting_logs = $this->mylib->check_postinglogs($payperiod['pperiod'],'postmanhour');
        if($posting_logs == false){
            $data = array(
                'module' => 'postmanhour',
                'payperiod' => $payperiod['pperiod'],
                'user_id' => $user_id,
                'log_date' => date('Y-m-d H:i:s')
            );
            $this->db->insert('posting_logs', $data);
            $result = array(
                'status' => true,
                'message' => 'Posting Manhour Success!!!'
            );
        }else{
            $result = array(
                'status' => false,
                'message' => 'Manhour Posting Already Done!!! Proceed on Payroll Computation...'
            );
        }

        return $result;
    }

    public function salary_adjustments($id){
        $payperiod = $this->mylib->get_active_pp()->row_array();
        $year = $this->mylib->get_active_yr();
        $str = "select aa.*,bb.regular FROM salary_adjustments aa
        LEFT JOIN mhr_{$year} bb ON bb.employee_id = aa.employee_id AND bb.payperiod = aa.payperiod
        WHERE aa.employee_id = '{$id}' AND DATE(aa.payperiod) = DATE('".$payperiod['pperiod']."')";
        return $this->db->query($str);
    }
    public function salary_adjustments_breakdown($id){
        $str = "select * from salary_adjustments_breakdown where adjustment_id = '{$id}'";
        return $this->db->query($str);
    }

    public function insertsalaryadjustment($data=array()){
        $this->db->trans_start();
        $post = $data;
        $payperiod = $this->mylib->get_active_pp()->row_array();
        $timestamp = date('Y-m-d H:i:s');

        $insert['table_name'] = 'salary_adjustments_breakdown';
        $insert['fields'] = array(
            'adjustment_id' => $post['adjustment_id'],
            'description' => strtoupper($post['description']),
            'amount' => $post['amount'],
            'created_by' => $post['user_id'],
            'created_at' => $timestamp
        );
        $insert = $this->builder->create_insert($insert);

        $select['table_name'] = 'salary_adjustments';
        $select['filters'] = array('id' => $post['adjustment_id']);
        $select = $this->builder->create_select($select)['result']->row_array();
        
        $net = floatval($select['net']) - floatval($post['amount']);
        $adjustments = floatval($select['adjustments']) + floatval($post['amount']);

        $update['table_name'] = 'salary_adjustments';
        $update['fields'] = array(
            'net' => round($net,2),
            'adjustments' => round($adjustments,2)
        );
        $update['filters'] = array('id' => $post['adjustment_id']);
        $update = $this->builder->create_update($update);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Insert Adjustment Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Insert Adjustment Success!'
            );
        }
        return $result;
    }
    public function updatesalaryadjustment($data=array()){
        $this->db->trans_start();
        $post = $data;
        $payperiod = $this->mylib->get_active_pp()->row_array();
        $timestamp = date('Y-m-d H:i:s');

        $select_adj['table_name'] = 'salary_adjustments';
        $select_adj['filters'] = array('id' => $post['adjustment_id']);
        $select_adj = $this->builder->create_select($select_adj)['result']->row_array();

        $select_brkdwn['table_name'] = 'salary_adjustments_breakdown';
        $select_brkdwn['filters'] = array('id' => $post['id']);
        $select_brkdwn = $this->builder->create_select($select_brkdwn)['result']->row_array();
        
        $update['table_name'] = 'salary_adjustments_breakdown';
        $update['fields'] = array(
            'adjustment_id' => $post['adjustment_id'],
            'description' => strtoupper($post['description']),
            'amount' => $post['amount'],
            'updated_by' => $post['user_id'],
            'updated_at' => $timestamp
        );
        $update['filters'] = array('id' => $post['id']);
        $update = $this->builder->create_update($update);
        //old value
        $net = floatval($select_adj['net']) + floatval($select_brkdwn['amount']);
        $adjustments = floatval($select_adj['adjustments']) - floatval($select_brkdwn['amount']);
        //updated value
        $net = floatval($net) - floatval($post['amount']);
        $adjustments = floatval($adjustments) + floatval($post['amount']);

        $update['table_name'] = 'salary_adjustments';
        $update['fields'] = array(
            'net' => round($net,2),
            'adjustments' => round($adjustments,2)
        );
        $update['filters'] = array('id' => $post['adjustment_id']);
        $update = $this->builder->create_update($update);

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Update Adjustment Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Update Adjustment Success!'
            );
        }
        return $result;
    }

    public function deletesalaryadjustment($data=array()){
        $this->db->trans_start();
        $post = $data;

        $select_adj['table_name'] = 'salary_adjustments';
        $select_adj['filters'] = array('id' => $post['adjustment_id']);
        $select_adj = $this->builder->create_select($select_adj)['result']->row_array();

        $select_brkdwn['table_name'] = 'salary_adjustments_breakdown';
        $select_brkdwn['filters'] = array('id' => $post['id']);
        $select_brkdwn = $this->builder->create_select($select_brkdwn)['result']->row_array();

        //old value
        $net = floatval($select_adj['net']) + floatval($select_brkdwn['amount']);
        $adjustments = floatval($select_adj['adjustments']) - floatval($select_brkdwn['amount']);

        $update['table_name'] = 'salary_adjustments';
        $update['fields'] = array(
            'net' => round($net,2),
            'adjustments' => round($adjustments,2)
        );
        $update['filters'] = array('id' => $post['adjustment_id']);
        $update = $this->builder->create_update($update);

        $this->db->delete('salary_adjustments_breakdown', array('id' => $post['id']));

        if($this->db->trans_status() === false){
            $this->db->trans_rollback();
            $result = array(
                'status' => false,
                'message' => 'Delete Adjustment Failed!'
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'message' => 'Delete Adjustment Success!'
            );
        }
        return $result;
    }
}