<?php defined('BASEPATH') or exit('No direct script access allowed');

class Payroll_model extends CI_Model{
    public function processpayroll($data=array()){
        $post = $data;
        $year = $this->mylib->get_active_yr();
        $payperiod = $this->mylib->get_active_pp()->row_array();
        $postmanhour_logs = $this->mylib->check_postinglogs($payperiod['pperiod'],'postmanhour');
        $processpayroll_logs = $this->mylib->check_postinglogs($payperiod['pperiod'],'processpayroll');
        $str = "
            select
            aa.employee_id,
            aa.payperiod,
            bb.salary,
            bb.gross,
            bb.net,
            bb.adjustments
            from mhr_{$year} as aa
            left join salary_adjustments as bb on bb.employee_id = aa.employee_id and bb.payperiod = aa.payperiod
            where aa.payperiod = '".$payperiod['pperiod']."'";
        $mhr = $this->db->query($str)->result_array();

        if($postmanhour_logs == true){
            if($processpayroll_logs == false){
                $this->db->trans_start();

                $str = "delete from payslip_{$year} where payperiod = '".$payperiod['pperiod']."' ";
                $this->db->query($str);

                $insert = "insert into payslip_{$year} (employee_id,payperiod,salary,gross,net,deduction)VALUES";
                $fields = [];
                foreach($mhr as $rw){
                    array_push($fields, "(".
                        $this->db->escape($rw['employee_id']) . "," .
                        $this->db->escape($rw['payperiod']) . "," .
                        $this->db->escape($rw['salary']) . "," .
                        $this->db->escape($rw['gross']) . "," .
                        $this->db->escape($rw['net']) . "," .
                        $this->db->escape($rw['adjustments']) .")"
                    );
                }
                //to check if no manhour to process
                if(count($fields) > 0){
                    $insert .= implode($fields, ",");
                    $this->db->query($insert);
                }

                $insert_logs['table_name'] = 'posting_logs';
                $insert_logs['fields'] = array(
                    'module' => 'processpayroll',
                    'payperiod' => $payperiod['pperiod'],
                    'user_id' => $post['user_id'],
                    'log_date' => date('Y-m-d H:i:s')
                );
                $insert_logs = $this->builder->create_insert($insert_logs);

                $post_pp['table_name'] = "dm_pp{$year}";
                $post_pp['fields'] = array(
                    'ppost' => 'P'
                );
                $post_pp['filters'] = array('id' => $payperiod['id']);
                $post_pp = $this->builder->create_update($post_pp);

                if($this->db->trans_status() === false){
                    $this->db->trans_rollback();
                    $result = array(
                        'status' => false,
                        'message' => 'Process payroll error! Please contact administrator.'
                    );
                }else{
                    $this->db->trans_commit();
                    $result = array(
                        'status' => true,
                        'message' => 'Payroll process success!!!'
                    );
                }                
            }else{
                $result = array(
                    'status' => false,
                    'message' => 'Payroll process already posted!'
                );
            }   
        }else{
            $result = array(
                'status' => false,
                'message' => 'Manhour not yet posted!'
            );
        }

        return $result;
    }
}