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
}