<?php defined('BASEPATH') or exit('No direct script access allowed');

class Mylib extends CI_Model{
    public function generate_string($input, $strength) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }

    public function permitted_chars(){
        return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    public function getDiscount(){
        return $this->db->get('discount');
    }

    public function getPhysicians(){
        $str = "select id as value,concat(lastname,', ',firstname,' ',middlename)as text,gender from physicians order by lastname";
        return $this->db->query($str);
    }

    public function id_ctr(){
		$year = $this->get_active_yr();

		$str = "select (id_number + 1) AS id_number from id_ctr order by id_number desc limit 1";
		$query = $this->db->query($str)->row_array();
		if(!empty($query)){
			$newid = $query['id_number'];
		}else{
			$newid = substr($year,2,2) . '00001';
		}
		return $newid;
	}

    public function get_active_yr(){
        return $this->db->get_where('year', array('post' => ''))->row_array()['year'];
    }
    public function get_active_pp(){
        $year = $this->get_active_yr();
        $str = "SELECT * FROM dm_pp{$year} WHERE ppost <> 'P' ORDER BY cutoff LIMIT 1";
        return $this->db->query($str)->row_array();
    }
}