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
    public function random_string($length) { 
		$key = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));
		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}
		return $key;
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
	public function patient_id_ctr(){
        $date = substr(date("Ymd"),2,6);
        $str = "select (id_number + 1)as id_number from id_ctr_patient where substr(id_number,1,6) = '{$date}' order by id_number desc limit 1";
        $query = $this->db->query($str)->row_array();
		if(!empty($query)){
			$newid = $query['id_number'];
		}else{
			$newid = $date . '001';
		}
        return $newid;
    }

    public function get_active_yr(){
        return $this->db->get_where('year', array('post' => ''))->row_array()['year'];
    }
    public function get_active_pp(){
        $year = $this->get_active_yr();
        $str = "SELECT * FROM dm_pp{$year} WHERE ppost <> 'P' ORDER BY DATE(pperiod) LIMIT 1";
        return $this->db->query($str);
    }
    
    function NumToHrMin2( $nVAR1 ) {
		$nNum1 = ((int) $nVAR1);
		$nNum2 =  round(($nVAR1 - $nNum1) * 60,2);
		return str_pad($nNum1,2,'0',STR_PAD_LEFT) . ':'. str_pad(self::m_int($nNum2),2,'0',STR_PAD_LEFT);
	}

    function NumToDHM10( $nVal ) {
		$value = explode('.',$nVal);
		$aday = $value[0];
		$aminute = $value[1];
		$day = 0;
		$minute = 0;
		$hour = 0;
		if($aday > 7){
			$xday = explode('.',($value[0] / 8));
			$days = $xday[0];
			$aaday = explode('.',($days * 8));
			$adays = $aaday[0];
			$xhour = $aday - $adays;
			$xminute = ($value[1] * .60);
			if($xminute > 59){
				$day = $days;
				$aminute = explode('.',($value[1] / 60));
				$hour_minute = $aminute[0];
				$minute = $value[1] - ($hour_minute * 60);
				$hour = $xhour + $hour_minute;
				if($hour > 7){
					$xxday = explode('.',($hour / 8));
					$hday = $xxday[0];
					$day = $day + $hday;
					$hour = $hour - ($hday * 8);
				}
			}
			else{
				$day = $days;
				$minute = $xminute;
				$hour = $xhour;
				if($hour > 7){
					$xxday = explode('.',($hour / 8));
					$hday = $xxday[0];
					$day = $day + $hday;
					$hour = $hour - ($hday * 8);
				}
			}
		}else{
			$xhour = $aday; 
			$xminute = ($value[1] * .60);
			if($xminute > 59){
				$aminute = explode('.',($value[1] / 60));
				$hour_minute = $aminute[0];
				$minute = $value[1] - ($hour_minute * 60);
				$hour = $xhour + $hour_minute;
				if($hour > 7){
					$xxday = explode('.',($hour / 8));
					$hday = $xxday[0];
					$day = $day + $hday;
					$hour = $hour - ($hday * 8);
				}
			}
			else{
				$minute = $xminute;
				$hour = $xhour;
				if($hour > 7){
					$xxday = explode('.',($hour / 8));
					$hday = $xxday[0];
					$day = $day + $hday;
					$hour = $hour - ($hday * 8);
				}
			}
		}
		return str_pad(self::m_int( $day ),2,'0',STR_PAD_LEFT) . ":" . str_pad(self::m_int( $hour ),2,'0',STR_PAD_LEFT) . ":" .
			str_pad(self::m_int( $minute ),2,'0',STR_PAD_LEFT);
	}
	
	public function m_int($nVar) {
		return (int) round($nVar + 0,2);
	}

	public function check_postinglogs($pperiod,$module){
		$result = $this->db->get_where('posting_logs' , array('payperiod' => $pperiod, 'module' => $module));
		if($result->num_rows() > 0){
			$result = true;
		}else{
			$result = false;
		}
		return $result;
	}
	public function isValidMd5($md5="") {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
}