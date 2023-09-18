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

    $pdf->SetFont('Courier','B',11);
	$pdf->Text($acol[0],$nrw,'');

	//HEADER
	$image = "./assets/images/wjelogo.png";
	$pdf->Image($image, 25, 7, 25, 24);

	$nrw = $nrw + $pagitan;
	$pdf->Text($acol[9],$nrw,$header['company_name']);

	$pdf->SetFont('Courier','',10);
	$nrw = $nrw + $pagitan;
	$pdf->Text(59,$nrw,substr($header['address'], 0, 43));
	$nrw = $nrw + $pagitan;
	$pdf->Text(63,$nrw,substr($header['address'], 43, 36));
	$nrw = $nrw + $pagitan;
	$pdf->Text(55,$nrw,substr($header['address'], 79));
	$nrw = $nrw + $pagitan;
	$pdf->Text(84,$nrw,"TEL. No. " . $header['tel_no']);
	$nrw = $nrw + $pagitan;
	$pdf->Text(82,$nrw,"DOH Lic. No. " . $header['doh_lic_no']);
	
	$pdf->SetFont('Courier','',10);
	$nrw = $nrw + $pagitan + 10;
	$pdf->Text($acol[0],$nrw,"Patient Name: ".utf8_decode($patient->fullname));
	$pdf->Text($acol[1],$nrw,"Physician: " .utf8_decode($patient->physician));
	$nrw = $nrw + $pagitan;
	$pdf->Text($acol[0],$nrw,"Patient ID: " .$patient->patient_id);
	$pdf->Text($acol[1],$nrw,"Date: " .$patient->date);
	$nrw = $nrw + $pagitan;
	$pdf->Text($acol[0],$nrw,"Age/Gender: " .$patient->age ."/". $patient->gender);
	
	//BODY
	$nrw = $nrw + $pagitan;
	$pdf->SetY($nrw);
	$pdf->SetX($acol_ed[0]);
	$pdf->SetFont('Courier','B',10);
	$pdf->Cell(194,8,$results->title,1,0,'C');

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
		$pdf->SetFont('Courier','B',9);
		$pdf->Cell($cols[0],8,'TEST',1,0,'C');
		$pdf->Cell($cols[2],8,'RESULTS',1,0,'C');
		$pdf->Cell($cols[1],8,'REFERENCE VALUE',1,0,'C');

		$pdf->SetFont('Courier','',8);
		foreach($results->lab_results as $row){
			$pdf->Ln();
			$pdf->SetX($acol_ed[0]);
			$pdf->Cell($cols[0],8,utf8_decode($row->result_title),1,0,'C');
			$pdf->Cell($cols[2],8,utf8_decode($row->result_value),1,0,'C');
			$pdf->Cell($cols[1],8,utf8_decode($row->result_range),1,0,'C');
		}
	}else{
		$cols[0] = 97;
		$cols[2] = 97;

		$pdf->Ln();
		$pdf->SetX($acol_ed[0]);
		$pdf->SetFont('Courier','B',9);
		$pdf->Cell($cols[0],8,'TEST',1,0,'C');
		$pdf->Cell($cols[2],8,'RESULTS',1,0,'C');

		$pdf->SetFont('Courier','',8);
		foreach($results->lab_results as $row){
			$pdf->Ln();
			$pdf->SetX($acol_ed[0]);
			$pdf->Cell($cols[0],8,$row->result_title,1,0,'C');
			$pdf->Cell($cols[2],8,$row->result_value,1,0,'C');
		}
	}
	

	$pdf->Ln();
	$pdf->SetX($acol_ed[0]);
	$pdf->Cell(20,8,'* REMARKS:',0,0,'L');
	$pdf->MultiCell(174,8,$results->remarks,0,'L',0);

	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,8, $user_name, 'B', 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,8, $header['pathologist_name'], 'B', 0, 'C');
	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,8, 'MEDICAL TECHNOLOGIST', 0, 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,8, 'PATHOLOGIST', 0, 0, 'C');

	$pdf->Ln();
	$pdf->SetX(30);
	$pdf->Cell(57,8, 'Lic. No. '. $employee['license_no'], 0, 0, 'C');
	$pdf->SetX(127);
	$pdf->Cell(57,8, 'Lic. No. '. $header['pathologist_lic_no'], 0, 0, 'C');

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