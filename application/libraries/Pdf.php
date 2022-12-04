<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
// require_once(__DIR__ . '/vendor/autoload.php');
// require_once dirname(__FILE__) . '/mpdf/src/Mpdf.php';

class Pdf extends TCPDF
{
  protected $custom_footer = '';
  public function __construct()
  {
    parent::__construct();
  }

  public function Header()
  {
    $headerData = $this->getHeaderData();
    $this->SetFont('helvetica', null, 10);
    $this->writeHTML($headerData['string']);
  }

  // Page footer
  public function Footer()
  {
    // $FooterData = $this->getFooterData();
    // Position at 15 mm from bottom
    $this->SetY(-15);
    // Set font
    
    if( $this->custom_footer != ""){
      $this->SetFont('helvetica', null, 8);
      $this->Cell(0, 10, $this->custom_footer, 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
    // Page number
    $this->SetFont('helvetica', 'I', 8);
    $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
  }
  
	public function SetCustomFooter($footer) {
		$this->custom_footer = $footer;
  }
  
}
