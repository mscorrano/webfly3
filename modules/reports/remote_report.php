<?php

require_once( __DIR__ . "/../../lib/fpdf/fpdf.php" );
require_once( __DIR__ . "/../../lib/fpdf/fpdfi/fpdi.php" );

if( !function_exists( "icona" )) {
	require_once( __DIR__ . "/../../lib/fontawesome/icone_pdf.php" );
}

class WEBFLY_REPORT_FIELD {
	public	$nome		 					= "";
	public	$dimensione  					= "";
	public  $etichetta	 					= "";
	public	$formato						= "";
	public	$nascosto						= false;
	public	$allineamento					= "L";
	public	$font							= "TitilliumWeb_Regular";
	public 	$font_attr						= "";
	public	$font_size						= 9;
	public	$font_intestazione				= "TitilliumWeb_Bold";
	public 	$font_attr_intestazione			= "";
	public	$font_size_intestazione			= 10;
	public	$allineamento_intestazione		= "L";
	public	$colore							= "0,0,0";
	public	$colore_alternativo				= "";
	public	$sfondo							= "";
	
	public function formatta( $valore ) {
		switch( strtolower($this->formato) ) {
			case "valuta":
				return number_format( $valore, 2, ",", "." );
				break;
				
			case "data":
				return date("d-m-Y", strtotime( $valore ));
				break;
				
			default:
				return utf8_decode($valore);
				break;
		}
	}
}

class WEBFLY_REPORT_HEADER {
	public	$altezza 						= 9;
	public	$sfondo							= "0,0,0";
	public	$colore							= "255,255,255";
	
}

class WEBFLY_REPORT_PARAMETERS {
	public	$dimensione 					= 0;
	public	$margine    					= "";
	public  $font_name  					= "TitilliumWeb_Regular";
	public  $font_size  					= "10";
	public	$altezza_righe					= 9;
	public	$intestazione					= NULL;
	public	$colore_righe					= "";
	public	$colore_righe_pari				= "230,230,230";
	public	$colore_righe_dispari			= "";
	public	$X 								= -1;
	public  $Y 								= -1;
	
	private	$campi							= array();

	public function __construct() {
		$this->intestazione = new WEBFLY_REPORT_HEADER;
	}
	public function __get( $nome ) { 
		foreach( $this->campi as $campo )
			if( $campo->nome == $nome ) { 
				return $campo;
			}
		$campo_standard = new WEBFLY_REPORT_FIELD;
		return $campo_standard;
	}
	
	public function add_field_params( $nome ) {
		$nuovo_campo = new WEBFLY_REPORT_FIELD;
		
		$nuovo_campo->nome = $nome;
		return $this->campi[] = $nuovo_campo;
	}
	
	public function parametri_record( $id_record ) {
		return array();
	}
}

class WEBFLY_PDF extends FPDI {
	protected $title;
	protected $REMOTE_DATA;
	protected $angle;

	function Rotate($angle,$x=-1,$y=-1)	{
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0)
		{
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}  
   
   function SetCellMargin( $margin=0 ) {
		$this->cMargin = $margin;
   }
	
   function Polygon($points, $style='D') {
	   //Draw a polygon
	   if($style=='F')
		   $op = 'f';
	   elseif($style=='FD' || $style=='DF')
		   $op = 'b';
	   else
		   $op = 's';

	   $h = $this->h;
	   $k = $this->k;

	   $points_string = '';
	   for($i=0; $i<count($points); $i+=2){
		   $points_string .= sprintf('%.2F %.2F', $points[$i]*$k, ($h-$points[$i+1])*$k);
		   if($i==0)
			   $points_string .= ' m ';
		   else
			   $points_string .= ' l ';
	   }
	   $this->_out($points_string . $op);
   }
   
	 function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5, $no_text=false) {

		$wide = $baseline;
		$narrow = $baseline / 3 ; 
		$gap = $narrow;

		$barChar['0'] = 'nnnwwnwnn';
		$barChar['1'] = 'wnnwnnnnw';
		$barChar['2'] = 'nnwwnnnnw';
		$barChar['3'] = 'wnwwnnnnn';
		$barChar['4'] = 'nnnwwnnnw';
		$barChar['5'] = 'wnnwwnnnn';
		$barChar['6'] = 'nnwwwnnnn';
		$barChar['7'] = 'nnnwnnwnw';
		$barChar['8'] = 'wnnwnnwnn';
		$barChar['9'] = 'nnwwnnwnn';
		$barChar['A'] = 'wnnnnwnnw';
		$barChar['B'] = 'nnwnnwnnw';
		$barChar['C'] = 'wnwnnwnnn';
		$barChar['D'] = 'nnnnwwnnw';
		$barChar['E'] = 'wnnnwwnnn';
		$barChar['F'] = 'nnwnwwnnn';
		$barChar['G'] = 'nnnnnwwnw';
		$barChar['H'] = 'wnnnnwwnn';
		$barChar['I'] = 'nnwnnwwnn';
		$barChar['J'] = 'nnnnwwwnn';
		$barChar['K'] = 'wnnnnnnww';
		$barChar['L'] = 'nnwnnnnww';
		$barChar['M'] = 'wnwnnnnwn';
		$barChar['N'] = 'nnnnwnnww';
		$barChar['O'] = 'wnnnwnnwn'; 
		$barChar['P'] = 'nnwnwnnwn';
		$barChar['Q'] = 'nnnnnnwww';
		$barChar['R'] = 'wnnnnnwwn';
		$barChar['S'] = 'nnwnnnwwn';
		$barChar['T'] = 'nnnnwnwwn';
		$barChar['U'] = 'wwnnnnnnw';
		$barChar['V'] = 'nwwnnnnnw';
		$barChar['W'] = 'wwwnnnnnn';
		$barChar['X'] = 'nwnnwnnnw';
		$barChar['Y'] = 'wwnnwnnnn';
		$barChar['Z'] = 'nwwnwnnnn';
		$barChar['-'] = 'nwnnnnwnw';
		$barChar['.'] = 'wwnnnnwnn';
		$barChar[' '] = 'nwwnnnwnn';
		$barChar['*'] = 'nwnnwnwnn';
		$barChar['$'] = 'nwnwnwnnn';
		$barChar['/'] = 'nwnwnnnwn';
		$barChar['+'] = 'nwnnnwnwn';
		$barChar['%'] = 'nnnwnwnwn';

		$this->SetFont('Arial','',10);
		if( !$no_text )
			$this->Text($xpos, $ypos + $height + 4, $code);
		$this->SetFillColor(0);

		$code = '*'.strtoupper($code).'*';
		for($i=0; $i<strlen($code); $i++){
			$char = $code[$i];
			if(!isset($barChar[$char])){
				$this->Error('Invalid character in barcode: '.$char);
			}
			$seq = $barChar[$char];
			for($bar=0; $bar<9; $bar++){
				if($seq[$bar] == 'n'){
					$lineWidth = $narrow;
				}else{
					$lineWidth = $wide;
				}
				if($bar % 2 == 0){
					$this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
				}
				$xpos += $lineWidth;
			}
			$xpos += $gap;
		}
	}  
	
	function __construct( $verticale = true, $dimensione="A4", $unita_misura="mm" ) {
		if( $verticale )
			$pagina = "P";
		else
			$pagina = "L";

		$this->angle       = 0;
		$this->title	   = "";
		$this->REMOTE_DATA = array();
		
		parent::__construct( $pagina, $unita_misura, $dimensione );

		$PATH = __DIR__."/../../lib/fpdf/font/";

		$d = dir( $PATH );
		while (false !== ($font_file = $d->read())) {
		  if( $font_file != "." && $font_file != ".." && strpos( $font_file, "." ) !== false ) {
			 list( $nome, $ext ) = explode( ".", $font_file );

			 if( $ext == "php" ) { 
				$this->AddFont( $nome, '', $font_file );
			 }
		  }
		}

		$this->AddPage();
		$this->SetAutoPageBreak( false );
	}
	

	function tabella( $dati, $parametri = NULL ) {
	   
		if( !is_object( $parametri )) {
			$parametri_tabella = new WEBFLY_REPORT_PARAMETERS;
		} else {
			if( get_class( $parametri ) != "WEBFLY_REPORT_PARAMETERS" )
			   throw new Exception( "I parametri per la tabella devono essre del tipo WEBFLY_REPORT_PARAMETERS (0x90041)", "0x90041" );
		   
			$parametri_tabella = $parametri;
		}
		
		if( $parametri_tabella->X > 0 )
			$X_INIZIO = $parametri_tabella->X;
		else
			$X_INIZIO = $this->GetX();
		
		if( $parametri_tabella->Y > 0 )
			$this->SetY( $parametri_tabella->Y );
	   
		$this->SetAutoPageBreak( false );
		if( $this->bMargin == 0 )
			$this->bMargin = 10;
		  
		if( count($dati)==0 )
			return false;
		
		if( $parametri_tabella->dimensione == 0 ) {
			$dimensione_tabella = round($this->GetPageWidth() - $this->GetX() - $this->rMargin,2);
		} else $dimensione_tabella = round($parametri_tabella->dimensione,2);
		
		if( $parametri_tabella->margine != "" ) {
		   $this->SetCellMargin( $parametri_tabella->margine );
		}
		
		$default_font 		= $parametri_tabella->font_name;
		$default_font_size 	= $parametri_tabella->font_size;

		$dimensioni_campi = array();
	   
		foreach( $dati as $record ) {
			$totale_dimensioni = 0;
		  
			$dimensione_campi  = array();

			foreach( $record as $etichetta => $valore ) {
				$dimensioni_campi[$etichetta] = $parametri_tabella->$etichetta->dimensione;             
			}
		}
	   
		$campi_automatici  = 0;
		$totale_dimensioni = 0;
		foreach( $dimensioni_campi as $etichetta => $dimensione )
			if( $dimensione == 0 )
				$campi_automatici++;
			else {
				if( !$parametri_tabella->$etichetta->nascosto )
					$totale_dimensioni += $dimensione;   
			}
	   
		if( $campi_automatici > 0) {
	   
			$dimensione_automatica = round(($dimensione_tabella - $totale_dimensioni) / $campi_automatici,2);
			if( $dimensione_automatica < 0 )
				$dimensione_automatica = 1;
		  
			foreach( $dimensioni_campi as $etichetta => $dimensione )
				if( $dimensione == 0 )
					$dimensioni_campi[$etichetta] = $dimensione_automatica;
		}
		
		$id_record 		= 0;
		$PRIMA_RIGA 	= true;
		$numero_pagina 	= $this->PageNo();
	   
		$record_count = 0;
		foreach( $dati as $id_record => $record ) {
			$record_count++;
		  
			if( $this->GetY() >= ($this->GetPageHeight()-$this->bMargin - 5) ) {
				$this->AddPage();
				$this->SetX( $X_INIZIO );
			}
			
			if( $numero_pagina != $this->PageNo() ) {
				$PRIMA_RIGA = true;
				$numero_pagina = $this->PageNo();
			} 
			
			if( $parametri_tabella->$etichetta->nascosto == false && $PRIMA_RIGA ) {
				$PRIMA_RIGA = false;
				// Intestazione tabella...
		  
				$id_campo = 0; 
				foreach( $dimensioni_campi as $etichetta => $valore ) {  
					$id_campo++;
				
					$flag_intestazione = !$parametri_tabella->$etichetta->nascosto;
				
					if( $flag_intestazione ) { 
						$this->SetFont( $parametri_tabella->$etichetta->font_intestazione,
										$parametri_tabella->$etichetta->font_attr_intestazione,
										$parametri_tabella->$etichetta->font_size_intestazione ); 
				   
						$allineamento = $parametri_tabella->$etichetta->allineamento_intestazione;
					} else $allineamento = "L";
			 
					if( $parametri_tabella->intestazione->sfondo != "" ) {
						$colore = true;
						list( $r, $g, $b ) = explode( ",", $parametri_tabella->intestazione->sfondo );
						$this->SetFillColor( intval($r), intval($g), intval($b) );
					} else $colore = false;
					 
					if( $parametri_tabella->intestazione->colore != "" ) {
						$colore = true;
						list( $r, $g, $b ) = explode( ",", $parametri_tabella->intestazione->colore );
						$this->SetTextColor( intval($r), intval($g), intval($b) );
					} else $this->SetTextColor( 0, 0, 0 );
			 
					if( $id_campo == count($dimensioni_campi) )
						$a_capo = 1;
					else
						$a_capo = 0;   
			  
					if( $parametri_tabella->$etichetta->etichetta != "" ) { 
						  $txt_intestazione = $parametri_tabella->$etichetta->etichetta;
					} else $txt_intestazione = $etichetta;
					
					$this->Cell( $dimensioni_campi[$etichetta], $parametri_tabella->intestazione->altezza, $txt_intestazione, 1, $a_capo, $allineamento, $colore ); 

					if( $a_capo == 1 ) {
						$this->SetX( $X_INIZIO );
					} else {
						if( $id_campo == count($dimensioni_campi) ) { 
							$this->ln();
							$this->SetX( $X_INIZIO );
						}
					}				
				}
			}
		
			$id_campo = 0; 
			foreach( $record as $etichetta => $valore ) {  
				$id_campo++;
				$parametri_campo = $parametri_tabella->$etichetta;
			
				$allineamento = $parametri_campo->allineamento;
	
				$font 		= $parametri_campo->font;
				$font_size 	= $parametri_campo->font_size;
				$font_attr 	= $parametri_campo->font_attr;	
			 
				$this->SetFont( $font, $font_attr, $font_size );
		 
				if( $parametri_campo->colore != "" ) {
					list( $r, $g, $b ) = explode( ",", $parametri_campo->colore );
					$this->SetTextColor( intval($r), intval($g), intval($b) );
				} else $this->SetTextColor( 0, 0, 0 );
			 
				$colore = false;
			 
				if( $parametri_tabella->colore_righe != "" ) {
					$colore = true;
			   
					list( $r, $g, $b ) = explode( ",", $parametri_tabella->colore_righe );  
				}
				
				if( $parametri_tabella->colore_righe_pari != ""  && ($record_count % 2 == 0 )) {
					$colore = true;
			   
					list( $r, $g, $b ) = explode( ",", $parametri_tabella->colore_righe_pari );  
				}
				
				if( $parametri_tabella->colore_righe_dispari != ""  && ($record_count % 2 == 1 )) {
					$colore = true;
			   
					list( $r, $g, $b ) = explode( ",", $parametri_tabella->colore_righe_dispari );  
				}	
			 
				if( $parametri_campo->sfondo != "" ) {
				   $colore = true;
				
				   list( $r, $g, $b ) = explode( ",", $parametri_campo->sfondo );   
				}
				
				if( $parametri_campo->colore_alternativo != "" && ($record_count % 2 == 0 )) {
					$colore = true;
					
					list( $r, $g, $b ) = explode( ",", $parametri_campo->colore_alternativo ); 	
				}
				
				if( $colore )
					$this->SetFillColor( intval($r), intval($g), intval($b) );
			 
				if( $id_campo == count($record) )
					$a_capo = 1;
				else
					$a_capo = 0;   
			 
				if( !$parametri_campo->nascosto ) {
					$X = $this->GetX();
					$Y = $this->GetY() + $parametri_tabella->altezza_righe / 2;	
					$this->Cell($dimensioni_campi[$etichetta], $parametri_tabella->altezza_righe,  $parametri_campo->formatta($valore), "1", $a_capo, $allineamento, $colore );   
			
					$parametri_record = $parametri_tabella->parametri_record( $id_record );
					
					if( array_key_exists( "barrato", $parametri_record) )
						$this->Line( $X+1,$Y, $X+$dimensioni_campi[$etichetta]-1, $Y );
				}	   
			 
				if( $a_capo == 1 ) {
					$this->SetX( $X_INIZIO );
				}
			}
		}   
	   
	}
	
	function values( $form_data ) {
		if( !is_array( $form_data ))
			throw new Exception( "I Parametri forniti devono essere degli array (0x90001)", "90001" );

		foreach( $form_data as $key => $value ) { 
			$this->REMOTE_DATA[$key] = $value;
		}
	}
	
	function report_title() {
		if( $this->title != "" )
			return $this->title;
		else
			return "Report";
	}
	
	// STRUTTURA DEL REPORT...
	public function structure() {
		
		if( method_exists( $this, "onload" ))
			$this->onload();
		else
			parent::structure();
	}
}