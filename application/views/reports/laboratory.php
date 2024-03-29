<?php   
$mpathdn = _XMYAPP_PATH_;
define('FPDF_FONTPATH',$mpathdn . '/fpdf17/');
require($mpathdn . '/fpdf17/fpdf.php');
$pdf =new FPDF('P','mm',array(215.9,279.5));  

$pdf->SetAutoPageBreak(true, 1);
$pdf->SetMargins(0.3, 0, 0, 0);
$pdf->cMargin = 0;
$pdf->Open();
$pdf->AddPage();	

$pagitan=4;
$hspace=40;
$nrw=4;
$acol = array();
$acol[0] = 5;
$npage = 0;
$acol_ed = array();
$ncoled_adj = 40;
$acol_ed[0] = 10; $acol_ed[1] = 60; $acol_ed[2] = 67 + $ncoled_adj;
$acol_ed[3] = 125 + $ncoled_adj; 
$acol_ed[4] = 149 + $ncoled_adj;
$acol_ed[5] = 168; $acol_ed[6] = 192; $acol_ed[7] = 230;
$nrec = 0;
$lnewpage = 0;

    $acol[0]=15;$acol[1]=113;$acol[2]=130;$acol[3]=213;$acol[4]=115;$acol[5]=135;$acol[6]=155;$acol[7]=170;$acol[8]=185;
	$acol[9]=75;

    //LOGO HEADER
	$image = "./assets/images/wjelogo.png";
	$pdf->Image($image, 12, 7, 25, 24);

    $pdf->SetFont('Times','B',11);
    $nrw = $nrw + 9;
    $pdf->Text(83,$nrw,$header['company_name']);

    $pdf->SetY(14);
	$pdf->SetX(10);
    $pdf->SetFont('Times','',11);
    $pdf->MultiCell(194,4, $header['address'] ."\n". "TEL. No. " . $header['tel_no'] . " DOH Lic. No. " . $header['doh_lic_no'],0,'C',0);

    //PATIENT HEADER
	$pdf->SetFont('Times','',10);
	$nrw = $nrw + $pagitan + 20;
	$pdf->Text($acol[0],$nrw,"Patient Name: ".utf8_decode($patient->fullname));
	$pdf->Text($acol[1],$nrw,"Physician: " .utf8_decode($patient->physician));
	$nrw = $nrw + $pagitan;
	$pdf->Text($acol[0],$nrw,"Patient ID: " .$patient->patient_id);
	$pdf->Text($acol[1],$nrw,"Date: " .$patient->date);
	$nrw = $nrw + $pagitan;
	$pdf->Text($acol[0],$nrw,"Age/Gender: " .$patient->age ."/". $patient->gender);
	
	//BODY
	$nrw = $nrw + $pagitan;
    $cellHeight = 5;
	$pdf->SetY($nrw);
	$pdf->SetX($acol_ed[0]);
	$pdf->SetFont('Times','B',10);
	$pdf->Cell(194,$cellHeight,$results->title,1,0,'C');

	foreach($results->lab_results as $row){
		if($row->result_range != ''){
			$resultrange_column = 1;
		}
	}

	if(isset($resultrange_column)){
		$cols[0] = 72;
		$cols[1] = 72;
		$cols[2] = 50;

		$pdf->Ln();
		$pdf->SetX($acol_ed[0]);
		$pdf->SetFont('Times','B',9);
		$pdf->Cell($cols[0],$cellHeight,'TEST',1,0,'C');
		$pdf->Cell($cols[2],$cellHeight,'RESULTS',1,0,'C');
		$pdf->Cell($cols[1],$cellHeight,'REFERENCE VALUE',1,0,'C');

		$pdf->SetFont('Times','',8);
		foreach($results->lab_results as $row){
			$pdf->Ln();
			$pdf->SetX($acol_ed[0]);
			$pdf->Cell($cols[0],$cellHeight,utf8_decode($row->result_title),1,0,'C');
			$pdf->Cell($cols[2],$cellHeight,utf8_decode($row->result_value),1,0,'C');
			$pdf->Cell($cols[1],$cellHeight,utf8_decode($row->result_range),1,0,'C');
		}
	}else{
		$cols[0] = 97;
		$cols[2] = 97;

		$pdf->Ln();
		$pdf->SetX($acol_ed[0]);
		$pdf->SetFont('Times','B',9);
		$pdf->Cell($cols[0],$cellHeight,'TEST',1,0,'C');
		$pdf->Cell($cols[2],$cellHeight,'RESULTS',1,0,'C');

		$pdf->SetFont('Times','',8);
		foreach($results->lab_results as $row){
			$pdf->Ln();
			$pdf->SetX($acol_ed[0]);
			$pdf->Cell($cols[0],$cellHeight,$row->result_title,1,0,'C');
			$pdf->Cell($cols[2],$cellHeight,$row->result_value,1,0,'C');
		}
	}
	

	$pdf->Ln();
	$pdf->SetX($acol_ed[0]);
	$pdf->Cell(20,$cellHeight,'* REMARKS:',0,0,'L');
	$pdf->MultiCell(174,$cellHeight,$results->remarks,0,'L',0);

	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,$cellHeight, $user_name, 'B', 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,$cellHeight, $header['pathologist_name'], 'B', 0, 'C');
	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,$cellHeight, 'MEDICAL TECHNOLOGIST', 0, 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,$cellHeight, 'PATHOLOGIST', 0, 0, 'C');

	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,$cellHeight, 'Lic. No. '. $employee['license_no'], 0, 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,$cellHeight, 'Lic. No. '. $header['pathologist_lic_no'], 0, 0, 'C');

	$filename = basename(".downloads/pdf/" . 'lp' . date("Ymdhis")) . '.pdf';
    $file = "./downloads/pdf/" . $filename;
    //Save PDF to file
    $pdf->Output($file);
    // Everything for owner, read and execute for others
	// echo $_SERVER['HTTP_HOST'].substr($file,1);
	$url = explode("/", base_url());
	echo $url[0]."//".$_SERVER['HTTP_HOST']."/".$url[3].substr($file, 1);

    chmod($file, 0755);
    // echo "<HTML><SCRIPT>document.location='{$file}';  </SCRIPT></HTML>";

    $pdf->close();
 ?>