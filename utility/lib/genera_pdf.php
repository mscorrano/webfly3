<?php

require_once( __DIR__ . "/../../lib/fpdf/fpdf.php" );
require_once( __DIR__ . "/../../lib/fpdf/fpdfi/fpdi.php" );

class PDF_Polygon extends FPDI
{
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
   
	 function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){

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
}

function crea_pdf( $foglio_verticale = true, $dimensione="A4", $unita_misura="mm" ) {
   if( $foglio_verticale )
     $pagina = "P";
   else
     $pagina = "L";
        
   $pdf = new PDF_Polygon( $pagina, $unita_misura, $dimensione );
   
   $PATH =  __DIR__ . "/../../lib/fpdf/font/";
   
   $d = dir( $PATH );
   while (false !== ($font_file = $d->read())) {
      if( $font_file != "." && $font_file != ".." && strpos( $font_file, "." ) !== false ) {
         list( $nome, $ext ) = explode( ".", $font_file );

         if( $ext == "php" ) { 
            $pdf->AddFont( $nome, '', $font_file );
         }
      }
   }
   
   $pdf->AddPage();
   $pdf->SetAutoPageBreak( false );
   return $pdf;
}

function tabella( $pdf, $dati, $parametri = array() ) {
   
   $X_INIZIO = $pdf->GetX();
   
   $pdf->SetAutoPageBreak( false );
   if( $pdf->bMargin == 0 )
      $pdf->bMargin = 10;
      
   if( count($dati)==0 )
      return false;
   
   if( !array_key_exists( "dimensione", $parametri )) {
      $dimensione_tabella = round($pdf->GetPageWidth() - $pdf->GetX() - $pdf->rMargin,2);
   } else $dimensione_tabella = round($parametri["dimensione"],2);
   
   if( array_key_exists( "margine", $parametri )) {
	   $pdf->SetCellMargin( $parametri["margine"] );
   }
   
   if( array_key_exists( "font", $parametri )) {
      list( $default_font, $default_font_size ) = explode( ",", $parametri["font"] );
   } else {
      $default_font = "helvetica";
      $default_font_size = 9;
   }
   
   $dimensioni_campi = array();
   
   
   foreach( $dati as $record ) {
	  $totale_dimensioni = 0;
	  
	  $dimensione_campi  = array();
   
	  foreach( $record as $etichetta => $valore ) {
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "dimensione", $parametri["campi"][$etichetta] )) {
				$dimensioni_campi[$etichetta] = $parametri["campi"][$etichetta]["dimensione"];
		} else $dimensioni_campi[$etichetta] = 0;             
	  }
   }
   
   $campi_automatici  = 0;
   $totale_dimensioni = 0;
   foreach( $dimensioni_campi as $etichetta => $dimensione )
      if( $dimensione == 0 )
         $campi_automatici++;
      else {
		  if( !array_key_exists( "intestazione", $parametri["campi"][$etichetta]) || $parametri["campi"][$etichetta]["intestazione"]==true )
		     $totale_dimensioni += $parametri["campi"][$etichetta]["dimensione"];   
      }
   if( !array_key_exists( "altezza_righe", $parametri )) {
      $altezza_righe = 5;
   } else $altezza_righe = $parametri["altezza_righe"]; 
     
   if( !array_key_exists( "altezza_riga_intestazione", $parametri )) {
      $altezza_riga_intestazione = $altezza_righe;
   } else $altezza_riga_intestazione = $parametri["altezza_riga_intestazione"];
   
   if( $campi_automatici > 0) {
   
	  $dimensione_automatica = round(($dimensione_tabella - $totale_dimensioni) / $campi_automatici,2);
	  if( $dimensione_automatica < 0 )
		  $dimensione_automatica = 1;
	  
	  foreach( $dimensioni_campi as $etichetta => $dimensione )
		 if( $dimensione == 0 )
			$dimensioni_campi[$etichetta] = $dimensione_automatica;
   }
    
   $id_record = 0;
   $PRIMA_RIGA = true;
   $numero_pagina = $pdf->PageNo();
   
   $record_count = 0;
   foreach( $dati as $id_record => $record ) {
      $record_count++;
	  
      if( $pdf->GetY() >= ($pdf->GetPageHeight()-$pdf->bMargin - 5) ) {
         $pdf->AddPage();
         $pdf->SetX( $X_INIZIO );
      }
        
      if( $numero_pagina != $pdf->PageNo() ) {
         $PRIMA_RIGA = true;
         $numero_pagina = $pdf->PageNo();
      } 
        
	  if( (!array_key_exists( "intestazione", $parametri ) || $parametri["intestazione"] == true) && $PRIMA_RIGA ) {
	     $PRIMA_RIGA = false;
		 // Intestazione tabella...
	  
		 $id_campo = 0; 
		 foreach( $dimensioni_campi as $etichetta => $valore ) {  
		    $id_campo++;
		    
			if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
				array_key_exists( "intestazione", $parametri["campi"][$etichetta] )) {
			   $flag_intestazione = $parametri["campi"][$etichetta]["intestazione"];
			} else $flag_intestazione = true;
			
			if( $flag_intestazione ) { 
			   if( array_key_exists( "intestazione", $parametri ) && array_key_exists( $etichetta, $parametri["intestazione"] ) &&
				   array_key_exists( "font", $parametri["intestazione"][$etichetta] )) {
				  list( $font, $font_size, $font_attr ) = explode( ",", $parametri["intestazione"][$etichetta]["font"] );
			   } else {
				  if( array_key_exists( "font", $parametri["intestazione"] )) {
					  list( $font, $font_size, $font_attr ) = explode( ",", $parametri["intestazione"]["font"] );
				  } else {
				     $font      = $default_font;
				     $font_size = $default_font_size;
				     $font_attr = "";
				  }
			   } 
		 
			   $pdf->SetFont( $font, $font_attr, $font_size ); 
			   
			   if( array_key_exists( "intestazione", $parametri ) && array_key_exists( $etichetta, $parametri["intestazione"] ) &&
				   array_key_exists( "allineamento", $parametri["intestazione"][$etichetta] )) {
					  $allineamento = $parametri["intestazione"][$etichetta]["allineamento"];
			   } else $allineamento = "L";
		 
			   if( array_key_exists( "intestazione", $parametri ) && array_key_exists( "sfondo", $parametri["intestazione"] )) {
				 $colore = true;
				 list( $r, $g, $b ) = explode( ",", $parametri["intestazione"]["sfondo"]);
				 $pdf->SetFillColor( intval($r), intval($g), intval($b) );
			   } else $colore = false;
				 
			   if( array_key_exists( "intestazione", $parametri ) && array_key_exists( "colore", $parametri["intestazione"] )) {
				 $colore = true;
				 list( $r, $g, $b ) = explode( ",", $parametri["intestazione"]["colore"]);
				 $pdf->SetTextColor( intval($r), intval($g), intval($b) );
			   } else $pdf->SetTextColor( 0, 0, 0 );
		 
			   if( $id_campo == count($dimensioni_campi) )
				  $a_capo = 1;
			   else
				  $a_capo = 0;   
		  
			   if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
				   array_key_exists( "etichetta", $parametri["campi"][$etichetta] )) {
					  $txt_intestazione = $parametri["campi"][$etichetta]["etichetta"];
			   } else $txt_intestazione = $etichetta;
				
			   $pdf->Cell( $dimensioni_campi[$etichetta], $altezza_riga_intestazione, $txt_intestazione, 1, $a_capo, $allineamento, $colore ); 

			   if( $a_capo == 1 )
			      $pdf->SetX( $X_INIZIO );
			} else {
				if( $id_campo == count($dimensioni_campi) ) {
					$pdf->ln();
					$pdf->SetX( $X_INIZIO );
				}
			}				
		 }
	  }
    $id_campo = 0; 
    foreach( $record as $etichetta => $valore ) {  
         $id_campo++;
         
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "allineamento", $parametri["campi"][$etichetta] )) {
				$allineamento = $parametri["campi"][$etichetta]["allineamento"];
		 } else $allineamento = "L";

   	         
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "font", $parametri["campi"][$etichetta] )) { 
				if( substr_count( $parametri["campi"][$etichetta]["font"], "," ) >= 2 ) {
					list( $font, $font_size, $font_attr ) = explode( ",", $parametri["campi"][$etichetta]["font"] );
				} else {
					if( substr_count( $parametri["campi"][$etichetta]["font"], "," ) >= 1 ) {
						list( $font, $font_size ) = explode( ",", $parametri["campi"][$etichetta]["font"] );
						$font_attr = "";
					} else {
						$font = $parametri["campi"][$etichetta]["font"];
						$font_size = 10;
						$font_attr = "";
					}
				} 
		 } else {
			$font      = $default_font;
			$font_size = $default_font_size;
			$font_attr = "";
		 } 
		 
		 $pdf->SetFont( $font, $font_attr, $font_size );
         
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "allineamento", $parametri["campi"][$etichetta] )) {
				$allineamento = $parametri["campi"][$etichetta]["allineamento"];
		 } else $allineamento = "L";
		 
		 
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "colore", $parametri["campi"][$etichetta] )) {

		   list( $r, $g, $b ) = explode( ",", $parametri["intestazione"]["colore"]);
		   $pdf->SetTextColor( intval($r), intval($g), intval($b) );
		 } else $pdf->SetTextColor( 0, 0, 0 );
		 
		 $colore = false;
		 
		 if( array_key_exists( "colore_righe", $parametri ) && array_key_exists( "tutte", $parametri["colore_righe"] )) {
		   $colore = true;
		   
		   list( $r, $g, $b ) = explode( ",", $parametri["colore_righe"]["tutte"] );  
		 }	
		 
		 if( array_key_exists( "colore_righe", $parametri ) ) {
			$colore = true;
		
			if( array_key_exists( "pari", $parametri["colore_righe"] ) && ($record_count % 2 == 0 ))	   		
		       list( $r, $g, $b ) = explode( ",", $parametri["colore_righe"]["pari"] );  	 
		 
		    if( array_key_exists( "colore_righe", $parametri ) && array_key_exists( "dispari", $parametri["colore_righe"] ) && ($record_count % 2 == 1 )) 
		       list( $r, $g, $b ) = explode( ",", $parametri["colore_righe"]["dispari"] );  
		 }
		 
		 if( array_key_exists( "campi", $parametri ) && array_key_exists( $etichetta, $parametri["campi"] ) &&
			 array_key_exists( "sfondo", $parametri["campi"][$etichetta] )) {
		   $colore = true;
		
		   list( $r, $g, $b ) = explode( ",", $parametri["campi"][$etichetta]["sfondo"] );
		   
		   if( array_key_exists( "colore_alternativo", $parametri["campi"][$etichetta] ) && ($record_count % 2 == 0 ))
		      list( $r, $g, $b ) = explode( ",", $parametri["campi"][$etichetta]["colore_alternativo"] ); 	   
		 }
		 
		 if( $colore )
			  $pdf->SetFillColor( intval($r), intval($g), intval($b) );
		 
		 if( $id_campo == count($record) )
		    $a_capo = 1;
		 else
		    $a_capo = 0;   
		 
		 if( array_key_exists($etichetta, $dimensioni_campi )) {
        $X = $pdf->GetX();
        $Y = $pdf->GetY() + $altezza_righe / 2;	
		$pdf->Cell($dimensioni_campi[$etichetta], $altezza_righe,  utf8_decode($valore), "1", $a_capo, $allineamento, $colore );   
        
        if(    array_key_exists( "record", $parametri)
            && array_key_exists( $id_record, $parametri["record"] )
            && array_key_exists( $etichetta, $parametri["record"][$id_record])
            && array_key_exists( "barrato",  $parametri["record"][$id_record][$etichetta])
            && $parametri["record"][$id_record][$etichetta]["barrato"] )
           $pdf->Line( $X+1,$Y, $X+$dimensioni_campi[$etichetta]-1, $Y );
		 }	   
		 if( $a_capo == 1 )
			$pdf->SetX( $X_INIZIO );
      }
   }   
   
}

function genera_grafico_valutazioni( $pdf, $X,$Y, $W,$H, $VALORI, $VALORI_RIFERIMENTO ) {
   
   if( $W < $H )
      $DIM_TESTO = $W / 10;
   else
      $DIM_TESTO = $H / 10;
      
   if( $DIM_TESTO < 5 )   
      $DIM_TESTO = 5;
      
   $FONT_SIZE = $DIM_TESTO * 1.4;
   if( $FONT_SIZE > 10 )
      $FONT_SIZE = 10;
         
   //$pdf->Rect( $X, $Y, $W, $H );
   
   $num_coefficienti = count( $VALORI );
   
   $W /= 2;
   $H /= 2;
   
   $CENTRO["X"] = $X + $W;
   $CENTRO["Y"] = $Y + $H;
      
   $W -= $DIM_TESTO * 1;
   $H -= $DIM_TESTO * 1;
   
   $pdf->SetFont("helveticab",'',$FONT_SIZE );
   $num_asse = 0;
   
   if( $num_coefficienti == 0 )
      $gamma = 2*pi();
   else   
      $gamma = 2*pi() / ($num_coefficienti * 6 );
   
   $POLIGONO    = array();
   $RIFERIMENTO = array();
   $pdf->SetLineWidth( 0.1 ); 
   foreach( $VALORI_RIFERIMENTO as $id_valore => $valore ) {   
      $num_asse++;
      $id_asse = $num_asse-1;
      
      $alpha = 2 * pi() / $num_coefficienti * $id_asse;
      // Graduazione asse...
      $W_MIN = 0.15 * $W;
	  $W_MAX = 0.95 * $W;
	  $H_MIN = 0.15 * $H;
	  $H_MAX = 0.95 * $H;
	  $DELTA_W = ($W_MAX - $W_MIN) / 10;
	  $DELTA_H = ($H_MAX - $H_MIN) / 10;
      
      // Vertici poligono...
  	  $MIN = $VALORI_RIFERIMENTO[$id_valore]["voto_minimo"];
	  $MAX = $VALORI_RIFERIMENTO[$id_valore]["voto_massimo"];
	  $MED = $VALORI_RIFERIMENTO[$id_valore]["voto_medio"];	
      
      if( array_key_exists( $id_valore, $VALORI )) {
         $VALORE = $VALORI[$id_valore]["media"];
      } else {
         $VALORE = 0;
      }
      
      $W_POL = $W_MIN + ($VALORE-1) * $DELTA_W;
      $H_POL = $H_MIN + ($VALORE-1) * $DELTA_H;
      
      $POLIGONO[] = $CENTRO["X"] + $W_POL * cos( $alpha );
      $POLIGONO[] = $CENTRO["Y"] + $H_POL * sin( $alpha );     
       
      $W_POL = $W_MIN + ($MED-1) * $DELTA_W;
      $H_POL = $H_MIN + ($MED-1) * $DELTA_H;
      
      $RIFERIMENTO[] = $CENTRO["X"] + $W_POL * cos( $alpha );
      $RIFERIMENTO[] = $CENTRO["Y"] + $H_POL * sin( $alpha );
   }
   
   $pdf->SetFillColor( 166, 166, 166 );   
   $pdf->SetLineWidth( 0.25 ); 
   $pdf->Polygon( $RIFERIMENTO, 'FD' );
   
   $pdf->SetLineWidth( 0.75 );
   $pdf->Polygon( $POLIGONO, 'D' );  
   $pdf->SetLineWidth( 0.1 ); 
   
   $num_asse = 0;
   foreach( $VALORI_RIFERIMENTO as $id_valore => $valore ) {
      $num_asse++;
      $id_asse = $num_asse-1;
      
      $alpha = 2 * pi() / $num_coefficienti * $id_asse;
      
      $POS["X"] = $CENTRO["X"] + $W * cos( $alpha );
      $POS["Y"] = $CENTRO["Y"] + $H * sin( $alpha );
      /*
      if( $alpha < pi()/4 || $alpha > 7*pi()/4 )
         $SEGNO1 = 1;
      else*/
         $SEGNO1 = -1;
         
      if( $alpha < pi() )
         $SEGNO2 = -1;
      else
         $SEGNO2 = -1;       
         
      if( $alpha == 0 ) {
         $SEGNO1 = 0;   
         $SEGNO2 = -1;   
      }    
       
      $TXT["X"] = $CENTRO["X"] + ($W+$DIM_TESTO * 0.5) * cos( $alpha ) - $DIM_TESTO / 2;
      $TXT["Y"] = $CENTRO["Y"] + ($H+$DIM_TESTO * 0.5) * sin( $alpha ) - $DIM_TESTO / 2;
      
      $pdf->Line( $CENTRO["X"], $CENTRO["Y"], $POS["X"], $POS["Y"] );
      $pdf->SetXY( $TXT["X"], $TXT["Y"] );
      $pdf->Cell( $DIM_TESTO, $DIM_TESTO, $valore["codice"], 0, 0, "C" );
      
      // Graduazione asse...
      $W_MIN = 0.15 * $W;
	  $W_MAX = 0.95 * $W;
	  $H_MIN = 0.15 * $H;
	  $H_MAX = 0.95 * $H;
	  $DELTA_W = ($W_MAX - $W_MIN) / 10;
	  $DELTA_H = ($H_MAX - $H_MIN) / 10;
	  
      for( $i=1; $i<=10; $i++ ) {
      
		 $WN = $W_MIN + ($i-1) * $DELTA_W;
		 $HN = $H_MIN + ($i-1) * $DELTA_H;
		 
		 $POS1["X"] = $CENTRO["X"] + $WN * cos( $alpha - $gamma );
		 $POS1["Y"] = $CENTRO["Y"] + $HN * sin( $alpha - $gamma );	
		 		 
		 $POS2["X"] = $CENTRO["X"] + $WN * cos( $alpha + $gamma );
		 $POS2["Y"] = $CENTRO["Y"] + $HN * sin( $alpha + $gamma );	
		 
		 $pdf->Line( $POS1["X"],$POS1["Y"], $POS2["X"],$POS2["Y"] );	          
      }
   }
   $pdf->SetLineWidth( 0.1 );
   $pdf->Rect( $X+1.5, $Y+2*$H+$DIM_TESTO/2+1.5, $DIM_TESTO/2, $DIM_TESTO/2, 'FD' );
   $pdf->SetXY( $X, $Y+2*$H+$DIM_TESTO+1.5 );
   $pdf->Cell( $DIM_TESTO, $DIM_TESTO, "Media" );
}
?>