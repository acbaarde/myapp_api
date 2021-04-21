<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Menu extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Menu_model', 'menumodel');
       $this->load->model('Mylib', 'mylib');
    }

    public function getAllModule_get(){
        echo json_encode($this->menumodel->get_all_module());
    }
    
    public function modMenu_get(){
        // $str = "select * from module_menu where `active` = 'A' GROUP BY mod_code1 ORDER BY mod_code1";
        // $query = $this->db->query($str);
        $query = $this->db->query($this->menumodel->mod_menu1());
        if($query->num_rows() > 0){
            $rows = $query->result_array();
            $data['menu'] = [];
            foreach($rows as $key1=>$row1){
                array_push($data['menu'], [
                    'menu_code' => $row1['mod_code1'],
                    'menu_title' => $row1['mod_name1'],
                    'sub_menu' => []
                ]);

                // $str = "select * from module_menu where mod_code1 = " . $this->db->escape($row1['mod_code1']) . " and mod_code2 != '' and `active` = 'A' GROUP BY mod_code2 order by mod_code2 ";
                // $query = $this->db->query($str);
                $query = $this->db->query($this->menumodel->mod_menu2($row1['mod_code1']));
                if($query->num_rows() > 0){
                    $rows = $query->result_array();
                    foreach($rows as $key2=>$row2){
                        array_push($data['menu'][$key1]['sub_menu'],[
                            'menu_code' => $row2['mod_code2'],
                            'menu_title' => $row2['mod_name2'],
                            'sub_menu' => []
                        ]);
                        
                        // $str = "select * FROM module_menu WHERE mod_code1 = " . $this->db->escape($row1['mod_code1']) . " and mod_code2 = " . $this->db->escape($row2['mod_code2']) . " AND mod_code3 != '' AND `active` = 'A' ORDER BY mod_code3";
                        // $query = $this->db->query($str);
                        $query = $this->db->query($this->menumodel->mod_menu3($row1['mod_code1'],$row2['mod_code2']));
                        if($query->num_rows() > 0){
                            $rows = $query->result_array();
                            foreach($rows as $key3=>$row3){
                                array_push($data['menu'][$key1]['sub_menu'][$key2]['sub_menu'],[
                                    'menu_code' => $row3['mod_code3'],
                                    'menu_title' => $row3['mod_name3']
                                ]);
                            }
                        }
                    }
                }
                
            }
        }
        // echo json_encode($data == '' ? ['message' => 'no menu(s) found!'] : $data);
        echo json_encode(isset($data) ? $data : ['message' => 'No Menu(s) Found!']);
    }
}