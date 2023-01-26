<?php defined('BASEPATH') OR exit('No direct script access allowed');
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Menu extends REST_Controller {
    
    public function __construct() {
       parent::__construct();
       $this->load->model('Menu_model', 'menumodel');
       $this->load->model('Mylib', 'mylib');
       $this->load->library('Query_builder','','builder');
    }

    // public function getAllModule_get(){
    //     echo json_encode($this->menumodel->get_all_module());
    // }
    
    // public function modMenu_get(){
    //     $query = $this->db->query($this->menumodel->mod_menu1());
    //     if($query->num_rows() > 0){
    //         $rows = $query->result_array();
    //         $data['menu'] = [];
    //         foreach($rows as $key1=>$row1){
    //             array_push($data['menu'], [
    //                 'menu_code' => $row1['mod_code1'],
    //                 'menu_title' => $row1['mod_name1'],
    //                 'menu_icon' => $row1['mod_icon1'],
    //                 'sub_menus' => []
    //             ]);
    //             $query = $this->db->query($this->menumodel->mod_menu2($row1['mod_code1']));
    //             if($query->num_rows() > 0){
    //                 $rows = $query->result_array();
    //                 foreach($rows as $key2=>$row2){
    //                     array_push($data['menu'][$key1]['sub_menus'],[
    //                         'menu_code' => $row2['mod_code2'],
    //                         'menu_title' => $row2['mod_name2'],
    //                         'menu_icon' => $row1['mod_icon2'],
    //                         'menu_path' => $row2['mod_url2'],
    //                         'sub_menus' => []
    //                     ]);
    //                     $query = $this->db->query($this->menumodel->mod_menu3($row1['mod_code1'],$row2['mod_code2']));
    //                     if($query->num_rows() > 0){
    //                         $rows = $query->result_array();
    //                         foreach($rows as $key3=>$row3){
    //                             array_push($data['menu'][$key1]['sub_menus'][$key2]['sub_menus'],[
    //                                 'menu_code' => $row3['mod_code3'],
    //                                 'menu_title' => $row3['mod_name3']
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }
                
    //         }
    //     }
    //     echo json_encode(isset($data) ? $data : ['message' => 'No Menu(s) Found!']);
    // }

    public function moduleMenu_get(){
        $str = "select * from mod_menu order by mod_title";
        echo json_encode($this->db->query($str)->result_array());
    }

    public function Menu_get(){
        $menu = [];
        $menu1 = $this->db->get_where('mod_menu', array('mod_lvl' => 1))->result_array();
        foreach($menu1 as $k1=>$rw1){
            array_push($menu, array(
                'id' => $rw1['id'],
                'name' => $rw1['mod_title'],
                'children' => []
            ));
            $menu2 = $this->db->get_where('mod_menu', array('mod_parent' => $rw1['id']))->result_array();
            foreach($menu2 as $k2=>$rw2){
                array_push($menu[$k1]['children'], array(
                    'id' => $rw2['id'],
                    'name' => $rw2['mod_title'],
                    'children' => []
                ));
                $menu3 = $this->db->get_where('mod_menu', array('mod_parent' => $rw2['id']))->result_array();
                foreach($menu3 as $k3=>$rw3){
                    array_push($menu[$k1]['children'][$k2]['children'], array(
                        'id' => $rw3['id'],
                        'name' => $rw3['mod_title'],
                        'children' => []
                    ));
                }
            }
        }
        echo json_encode($menu);
    }
    public function getUseraccess_post(){
        $post = $this->input->post('id');
        $result = $this->db->query("select mod_id from users_mod_access where user_id = '{$post}'")->result_array();
        $access = [];
        foreach($result as $rw){
            array_push($access, $rw['mod_id']);
        }
        echo json_encode($access);
    }
    public function saveUseraccess_post(){    
        echo json_encode($this->menumodel->saveuseraccess($this->input->post()));
    }
}