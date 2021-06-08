<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Query_builder {
    public $CI;

    function __construct(){
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function create_select($data = array()){
        $fields = isset($data['fields']) ? $data['fields'] : '*';
        $table_name = isset($data['table_name']) ? $data['table_name'] : '';
        $filters = isset($data['filters']) ? $data['filters'] : array();
        $group_by = isset($data['group_by']) ? $data['group_by'] : null;
        $order_by = isset($data['order_by']) ? $data['order_by'] : array();
        $limit = isset($data['limit']) ? $data['limit'] : null;

        $query = "SELECT " . $fields . " FROM " . $table_name . " ";

        if(!empty($filters)){
            $filters = $this->CI->db->escape($filters);

            $query .= " WHERE ";
            foreach($filters as $key => $value){
                if($this->check_operators($key) === true){
                    $query .= $key . " " . $value . " AND ";
                }else{
                    $query .= $key . " = " . $value . " AND ";
                }
            }
            $query = substr($query, 0, -4);
        }

        if(!empty($group_by)){
            $query .= "GROUP BY " . $group_by . " ";
        }

        if(!empty($order_by)){
            $query .= "ORDER BY ";
            foreach($order_by as $key => $value){
                $query .= $key . " " . ($value == '' ? 'DESC' : $value) . " ,";
            }
            $query = substr($query, 0, -1);
        }

        if(!empty($limit)){
            $query .= "LIMIT " . $limit . " ";
        }

        $return['query'] = $query;
        $return['result'] = $this->CI->db->query($query);
        return $return;
    }

    public function check_operators($string){
        $operators = array('=','>','<','>=','<=','<>','!='); //sql operators

        $status = 0;
        foreach($operators as $operator){
            if(strpos($string,$operator) !== FALSE){
                $status = $status + 1;
            }
        }
        return $status > 0 ? true : false;
    }

    public function create_insert($data = array()){
        $table_name = isset($data['table_name']) ? $data['table_name'] : '';
        $fields = isset($data['fields']) ? $data['fields'] : array();

        $keys_array = array();
        $values_array = array();

        foreach($fields as $key => $value){
            $value =  $this->CI->db->escape($value);
            array_push($keys_array,$key);
            array_push($values_array,$value);
        }

        $keys_array = implode(', ', $keys_array);
        $values_array = implode(', ',$values_array);

        $sql = "INSERT INTO " . $table_name . "(" . $keys_array . ") VALUES (". $values_array . ")";
        
        $return['query'] = $sql;  //return sql query
        $return['result'] = $this->CI->db->query($sql); //return query results

        return $return;
    }
    
    public function create_update($data = array()){
        $table_name = isset($data['table_name']) ? $data['table_name'] : '';
        $fields = isset($data['fields']) ? $data['fields'] : array();
        $filters = isset($data['filters']) ? $data['filters'] : array();

        if(!empty($fields)){
            $sql = "UPDATE " . $table_name . " SET " ;
            //loop fields
            foreach($fields as $key => $value){
                $sql .= $key . " = " . $this->CI->db->escape($value) . " ,";
            }
            $sql = substr($sql, 0, -1);
        }

        //checks if there is a filter (WHERE)
        if(!empty($filters)){
            $filters = $this->CI->db->escape($filters);
            
            $sql .= " WHERE ";
            foreach($filters as $key => $value){
                //check if key has sql operators
                if($this->check_operators($key)){
                    $sql .= $key . " " . $value . " AND ";
                }else{
                    $sql .= $key . " = " . $value . " AND ";
                }
            }
            $sql = substr($sql, 0, -4);
        }

        $return['query'] = $sql; //return sql query
        $return['result'] = $this->CI->db->query($sql); //return sql query 

        return $return;
    }
}
?>