<?php defined('BASEPATH') or exit('No direct script access allowed');

class Appointment_model extends CI_Model{
    public function insert_entry($data){
        $post = $data;
        $id = $post['id'];
        $timestamp = date('Y-m-d H:i:s');
        $patient_id = $post['patient_id'];
        $physician_id = $post['physician_id'];
        $discount_id = $post['discount_id'];
        $discount_rmks = $post['discount_rmks'];
        $discount_percent = intval($post['discount_percent']);
        $submod_id = $post['submod_id'];
        $cash = $post['total_cash'];
        $balance = $post['total_change'];
        $total_amount = $post['total_amount'];
        $total_fee = $post['total_fee'];
        $user_id = $post['user_id'];

        $fields = array(
            'id' => $id,
            'status' => 'P',
            'patient_id' => $patient_id,
            'physician_id' => $physician_id,
            'discount_id' => $discount_id,
            'discount_rmks' => $discount_rmks,
            'discount_percent' => $discount_percent,
            'cash' => $cash,
            'balance' => $balance,
            'total_amount' => $total_amount,
            'total_fee' => $total_fee,
            'created_by' => $user_id,
            'created_at' => $timestamp
        );
        $this->db->trans_begin();
        $this->db->insert('appointment_entries', $fields);
        $insertid = $this->db->insert_id();
        
        $submod_id = $submod_id == '' ? [] : explode(",", $submod_id);
        foreach($submod_id as $rw){
            $lab = $this->db->get_where('laboratory_submodule', array('id' => $rw))->row_array();
            $fields = array(
                'control_id' => $id,
                'lab_id' => $lab['id'],
                'title' => $lab['title'],
                'abbr' => $lab['abbr'],
                'amount' => $lab['amount'],
                'created_by' => $user_id,
                'created_at' => $timestamp
            );
            $this->db->insert('appointment_lab_test', $fields);
            $lab_subs = $this->db->get_where('laboratory_subsubmodule', array('submod_id' => $lab['id']))->result_array();
            $sub_fields = [];
            foreach($lab_subs as $rw_sub){
                $sub_field = array(
                    'control_id' => $id,
                    'lab_id' => $rw_sub['submod_id'],
                    'result_title' => $rw_sub['title'],
                    'result_range' => $rw_sub['result_range']
                );
                array_push($sub_fields, $sub_field);
            }
            foreach($sub_fields as $field_insert){
                $this->db->insert('appointment_lab_results', $field_insert);
            }
        }
        
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'appointment_status' => 'P',
                'control_id' => $id,
                'apprvd' => $this->db->get_where('appointment_entries', array('id' => $id))->row_array()['approved']
            );
        }
        return $result;
    }

    public function update_entry($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $id = $post['id'];
        $patient_id = $post['patient_id'];
        $physician_id = $post['physician_id'];
        $discount_id = $post['discount_id'];
        $discount_rmks = $post['discount_rmks'];
        $discount_percent = intval($post['discount_percent']);
        $submod_id = $post['submod_id'];
        $cash = $post['total_cash'];
        $balance = $post['total_change'];
        $total_amount = $post['total_amount'];
        $total_fee = $post['total_fee'];
        $user_id = $post['user_id'];

        $fields = array(
            'status' => 'P',
            'patient_id' => $patient_id,
            'physician_id' => $physician_id,
            'discount_id' => $discount_id,
            'discount_rmks' => $discount_rmks,
            'discount_percent' => $discount_percent,
            'cash' => $cash,
            'balance' => $balance,
            'total_amount' => $total_amount,
            'total_fee' => $total_fee,
            'updated_by' => $user_id,
            'updated_at' => $timestamp
        );
        $this->db->trans_begin();
        $this->db->update('appointment_entries', $fields, array('id' => $id));

        $temp_labtest = $this->db->get_where('appointment_lab_test', array('control_id' => $id))->result_array();
        $optn = $submod_id == '' ? '' : " and lab_id in ({$submod_id})";
        $temp_labresults = $this->db->query("select * from appointment_lab_results where control_id = {$id} {$optn} ")->result_array();
        
        $this->db->query('delete from appointment_lab_test where control_id ="'.$id.'" ');
        $this->db->query('delete from appointment_lab_results where control_id ="'.$id.'" ');

        $submod_id = $submod_id == '' ? [] : explode(",", $submod_id);
        if(count($submod_id) > 0){
            $fields = [];
            foreach($submod_id as $rw){
                $cnt = 0;
                foreach($temp_labtest as $rw1){
                    if($rw == $rw1['lab_id']){
                        $cnt = 1;
                        $field = array(
                            'control_id' => $rw1['control_id'],
                            'lab_id' => $rw1['lab_id'],
                            'status' => $rw1['status'],
                            'title' => $rw1['title'],
                            'abbr' => $rw1['abbr'],
                            'amount' => $rw1['amount'],
                            'cancel_reason' => $rw1['cancel_reason'],
                            'remarks' => $rw1['remarks'],
                            'created_by' => $rw1['created_by'],
                            'created_at' => $rw1['created_at'],
                            'printed_by' => $rw1['printed_by'],
                            'printed_at' => $rw1['printed_at'],
                            'cancelled_by' => $rw1['cancelled_by'],
                            'cancelled_at' => $rw1['cancelled_at'],
                            'reprinted_by' => $rw1['reprinted_by'],
                            'reprinted_at' => $rw1['reprinted_at'],
                            'so_clinic' => $rw1['so_clinic'],
                            'so_remarks' => $rw1['so_remarks'],
                            'so_status' => $rw1['so_status'],
                            'so_received_by' => $rw1['so_received_by'],
                            'so_received_at' => $rw1['so_received_at']
                        );
                        array_push($fields, $field);
                        break;
                    }
                }
                if($cnt == 0){
                    $submod = $this->db->get_where('laboratory_submodule', array('id' => $rw))->row_array();
                    $field = array(
                        'control_id' => $id,
                        'lab_id' => $submod['id'],
                        'title' => $submod['title'],
                        'abbr' => $submod['abbr'],
                        'amount' => $submod['amount'],
                        'created_by' => $user_id,
                        'created_at' => $timestamp
                    );
                    array_push($fields, $field);
                }
            }

            $res_fields = [];
            foreach($fields as $rw_fld){
                $this->db->insert('appointment_lab_test', $rw_fld);
                $subsubmod = $this->db->get_where('laboratory_subsubmodule', array('submod_id' => $rw_fld['lab_id']))->result_array();
                $cnt = 0;
                foreach($temp_labresults as $rw_tmp){
                    if($rw_fld['lab_id'] == $rw_tmp['lab_id']){
                        $res_field = array(
                            'control_id' => $rw_tmp['control_id'],
                            'lab_id' => $rw_tmp['lab_id'],
                            'result_title' => $rw_tmp['result_title'],
                            'result_range' => $rw_tmp['result_range'],
                            'result_value' => $rw_tmp['result_value']
                        );
                        array_push($res_fields, $res_field);
                        $cnt = 1;
                    }
                }
                if($cnt == 0){
                    foreach($subsubmod as $rw_subsubmod){
                        $subsubmod_fields = array(
                            'control_id' => $rw_fld['control_id'],
                            'lab_id' => $rw_subsubmod['submod_id'],
                            'result_title' => $rw_subsubmod['title'],
                            'result_range' => $rw_subsubmod['result_range'],
                            'result_value' => ''
                        );
                        array_push($res_fields, $subsubmod_fields);
                    }
                }
            }
            foreach($res_fields as $res_rw){
                $this->db->insert('appointment_lab_results', $res_rw);
            }
        }
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true,
                'appointment_status' => $this->db->get_where('appointment_entries', array('id' => $id))->row_array()['status'],
                'control_id' => $id,
                'apprvd' => $this->db->get_where('appointment_entries', array('id' => $id))->row_array()['approved']
            );
        }
        return $result;
    }

    public function save_result_entry($data){
        $post = $data['lab_results'];
        $rmks = strtoupper($data['result_remarks']);
        $labtestid = $data['lab_test_id'];
        $arr = json_decode($post, true);
        $this->db->trans_begin();

        $chckval = [];
        foreach($arr as $rw){
            array_push($chckval, empty($rw['result_value']) ? is_numeric($rw['result_value']) ? 0 : 1 : 0); 
            $fields = array(
                'result_value' => strtoupper($rw['result_value'])
            );
            $this->db->update('appointment_lab_results', $fields, array('id' => $rw['id']));
        }
        if(array_sum($chckval) > 0){
            $status = ''; //FOR PENDING VALUE
        }else{
            $status = 'P'; //FOR PRINT VALUE
        }

        $this->db->update('appointment_lab_test', array('remarks' => $rmks, 'status' => $status), array('id'=>$labtestid));


        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function cancel_lab_test($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status'=>'C',
            'cancel_reason' => strtoupper($post['cancel_reason']),
            'cancelled_by' => $post['user_id'],
            'cancelled_at' => $timestamp
        );
        $this->db->update('appointment_lab_test', $fields, array('id' => $post['item_id']));
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function approved_reject_entry($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'approved' => $post['stat'] == 'approved' ? 'Y' : 'N', 
            'approved_by' => $post['user_id'],
            'approved_at' => $timestamp
        );
        $this->db->update('appointment_entries', $fields, array('id' => $post['item_id']));
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function post_entry($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status' => 'D', //POSTING VALUE
            'posted_by' => $post['user_id'],
            'posted_at' => $timestamp
        );
        $this->db->update('appointment_entries', $fields, array('id' => $post['id'], 'status' => 'P'));
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function post_print_item($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status' => 'D', //DONE VALUE AFTER PRINT
            'printed_by' => $post['user_id'],
            'printed_at' => $timestamp
        );
        $this->db->update('appointment_lab_test', $fields, array('id' => $post['item_id']));
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
            echo "Error print posting!!!";
            die();
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }
    public function reprint_lab_test($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status' => 'P', //REPRINT VALUE
            'reprinted_by' => $post['user_id'],
            'reprinted_at' => $timestamp
        );
        $this->db->update('appointment_lab_test', $fields, array('id' => $post['item_id']));
        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function cancel_entry($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status' => 'C', //REPRINT VALUE
            'cancelled_by' => $post['user_id'],
            'cancelled_at' => $timestamp
        );
        $this->db->update('appointment_entries', $fields, array('id' => $post['id']));
        $fields['cancel_reason'] = 'TRANSACTION CANCELLED (AUTO)';//auto cancel lab test
        $this->db->update('appointment_lab_test', $fields, array('control_id' => $post['id']));

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }

    public function save_sendout($data){
        $post = $data;
        $timestamp = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $fields = array(
            'status' => $post['status'] == '1' ? 'D' : '',
            'so_clinic' => strtoupper($post['clinic']),
            'so_remarks' => strtoupper($post['remarks']),
            'so_status' => $post['status'],
            'so_received_by' => $post['user_id'],
            'so_received_at' => $post['status'] == '1' ? $timestamp : '0000-00-00 00:00:00'
        );
        $this->db->update('appointment_lab_test', $fields, array('id' => $post['id']));

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array(
                'status' => false
            );
        }else{
            $this->db->trans_commit();
            $result = array(
                'status' => true
            );
        }
        return $result;
    }
}