<?php

require_once( __DIR__ . "/../../../modules/reports/remote_report.php" );

include( __DIR__ . "/../utility/analisi_opere.php" );

class STAMPA_ELENCO extends WEBFLY_PDF {
	private $DB_MANAGER;
	
	function __construct() {
		global $WEBFLY;
		
		$this->DB_MANAGER = $WEBFLY->DB_MANAGER;
		parent::__construct( false, "A4" );
	}
	
	function onload() {
		
		$FILTRO_OPERE = "";
		
		analizza_opera( $this->DB_MANAGER );
		
		$this->title = "REPORT MONITORAGGIO OPERE PUBBLICHE";
		
		$this->Image( __DIR__ . "/../risorse/stemma_provincia.png", 5,5, 18,30 );
		$this->SetXY( 22, 11 );
		$this->SetFont( "TitilliumWeb_Bold", "", 16 );
		$this->Cell( 10, 10, "Provincia di Pescara" );
		$this->SetXY( 22, 16 );
		$this->SetFont( "TitilliumWeb_Regular", "", 12 );
		$this->Cell( 10, 10, "Settore Tecnico - Servizio Monitoraggio Opere Pubbliche" );;
		$this->SetXY( 22, 24 );
		$this->SetFont( "TitilliumWeb_Italic", "", 10 );
		$this->Cell( 10, 10, "Data aggiornamento ".date("d-m-Y") );

		$this->SetXY( 10, 40 );
		$this->SetFont( "TitilliumWeb_Bold", "", 36 );
		$this->Cell( 277, 10, "RIEPILOGO COMPLESSIVO", 0,0,"C" );
		
		$categorie = $this->DB_MANAGER->exec_sql( "SELECT * FROM db_categorie_opere WHERE id_categoria" );
		
		$DIMENSIONE_SLOT = 70;
		
		$X = 10;
		
		$DIMENSIONE_ICONA = 25;
		
		$Y = 60;
		$COLONNA = 0;
		foreach( $categorie as $categoria ) {
			$COLONNA++;
			if( $COLONNA > 4 ) {
				$COLONNA = 1;
				$Y += 75;
				$X =  10;
			}
			
			$X_CENTRO = $X + $DIMENSIONE_SLOT / 2;
			$this->Image( icona( $categoria["icona"] ), $X_CENTRO - $DIMENSIONE_ICONA / 2, $Y, $DIMENSIONE_ICONA, $DIMENSIONE_ICONA, 'JPG' );
			
			$this->SetXY( $X, $Y + $DIMENSIONE_ICONA + 5);
			$this->SetFont( "TitilliumWeb_Bold", "", 14 );
			$this->SetFillColor( 0, 114, 184 );
			$this->SetTextColor( 255, 255, 255 );
			$this->Cell( $DIMENSIONE_SLOT, 10, utf8_decode($categoria["descrizione"]), 1,0, "C", 1 );
			
			$totale = $this->DB_MANAGER->exec_sql( "SELECT SUM(importo_finanziamento) as TOTALE, COUNT(*) as NUMERO FROM opere_pubbliche INNER JOIN db_fasi ON opere_pubbliche.id_fase = db_fasi.id_fase WHERE id_categoria_opera='".$categoria["id_categoria"]."' AND importo_finanziamento>0 ".$FILTRO_OPERE, true );
			
			if( array_key_exists( "TOTALE", $totale )) {
				$TOTALE     = $totale["TOTALE"];
				$INTERVENTI = $totale["NUMERO"];
				$PROBLEMI   = 0;
			} else {
				$TOTALE     = 0;
				$INTERVENTI = 0;
				$PROBLEMI   = 0;
			}
			
			$this->SetTextColor( 0, 0, 0 );
						
			$this->SetXY( $X, $Y + $DIMENSIONE_ICONA + 15);
			
			if( $PROBLEMI > 0 ) {
				$this->SetTextColor( 255, 255, 255 );
				$this->SetFillColor( 247, 62, 90 );
				$this->SetFillColor( 255, 153, 0 );
			} else {
				$this->SetTextColor( 255, 255, 255 );
				$this->SetFillColor( 0, 207, 134 );
			}
			$this->SetFont( "TitilliumWeb_Bold", "", 14 );
			$this->Cell( $DIMENSIONE_SLOT, 10, utf8_decode( $PROBLEMI . " Criticità"), "LR",0, "C", 1 );
			
			$this->SetTextColor( 0, 0, 0 );
			$this->SetFont( "TitilliumWeb_Regular", "", 12 );
			
			$this->SetXY( $X, $Y + $DIMENSIONE_ICONA + 25);
			
			$this->Cell( $DIMENSIONE_SLOT, 7, utf8_decode( number_format($INTERVENTI,0,",",".") . " opere"), "LR",0, "C", 0 );
			
			$this->SetXY( $X, $Y + $DIMENSIONE_ICONA + 32);
			
			$this->SetFont( "TitilliumWeb_Bold", "", 14 );
			$this->Cell( $DIMENSIONE_SLOT, 8, utf8_decode( number_format($TOTALE,0,",",".") . " Euro"), "LRB",0, "C", 0 );
			
			$X += $DIMENSIONE_SLOT;
		}
		
		$this->AddPage();

		$this->SetXY( 10, 10 );
		$this->SetFont( "TitilliumWeb_Bold", "", 14 );
		$this->Cell( 277, 10, "RIEPILOGO PER COMUNE E RUP", 0,0,"C" );
		
			
		$riepilogo_comuni = $this->DB_MANAGER->exec_sql( "SELECT db_comuni.descrizione, COUNT(*) AS numero, SUM(importo_finanziamento) AS importo FROM opere_pubbliche LEFT JOIN db_comuni ON opere_pubbliche.id_comune = db_comuni.id_comune WHERE importo_finanziamento>0 ".$FILTRO_OPERE." GROUP BY db_comuni.descrizione ORDER BY db_comuni.descrizione" );

		$this->SetXY( 10, 20 );
		
		$parametri = new WEBFLY_REPORT_PARAMETERS;
		$parametri->dimensione = 140;
		$parametri->intestazione->sfondo = "0,114,184";
		$parametri->altezza_righe = 8;
		$descrizione = $parametri->add_field_params( "descrizione" );
		$descrizione->etichetta = "Comune";
		$descrizione->dimensione = 60;
		$descrizione->font = "TitilliumWeb_Bold";
		$numero = $parametri->add_field_params( "numero" );
		$numero->etichetta = "Num. Interventi";
		$numero->allineamento = "C";
		
		$importo = $parametri->add_field_params( "importo" );
		$importo->allineamento = "R";
		$importo->etichetta = "Totale Opere";
		$importo->formato = "Valuta";
		
		$this->tabella( $riepilogo_comuni, $parametri );	
		
		$riepilogo_comuni = $this->DB_MANAGER->exec_sql( "SELECT nome_esteso, COUNT(*) AS numero, SUM(importo_finanziamento) AS importo FROM opere_pubbliche LEFT JOIN rup ON opere_pubbliche.id_rup = rup.id_rup WHERE importo_finanziamento>0 ".$FILTRO_OPERE." GROUP BY nome_esteso ORDER BY importo DESC" );

		$this->SetXY( 155, 20 );
		
		$parametri = new WEBFLY_REPORT_PARAMETERS;
		$parametri->dimensione = 132;
		$parametri->intestazione->sfondo = "0,114,184";
		$parametri->altezza_righe = 8;
		$descrizione = $parametri->add_field_params( "nome_esteso" );
		$descrizione->etichetta = "Nominativo RUP";
		$descrizione->dimensione = 60;
		$descrizione->font = "TitilliumWeb_Bold";
		$numero = $parametri->add_field_params( "numero" );
		$numero->etichetta = "Num. Interventi";
		$numero->allineamento = "C";
		
		$importo = $parametri->add_field_params( "importo" );
		$importo->allineamento = "R";
		$importo->etichetta = "Totale Opere";
		$importo->formato = "Valuta";
		
		$this->tabella( $riepilogo_comuni, $parametri );
		
		// Elenco Opere...
		foreach( $categorie as $categoria ) {
			$this->AddPage();
			
			$this->SetXY( 0, 10 );
			$this->SetFont( "TitilliumWeb_Bold", "", 14 );
			$this->SetFillColor( 0, 114, 184 );
			$this->SetTextColor( 255, 255, 255 );
			$this->Cell( 40, 10, "", 0,0, "C", 1 );
			$this->Cell( 257, 10, strtoupper(utf8_decode($categoria["descrizione"])), 0,0, "L", 1 );
			$this->Image( icona( $categoria["icona"] ), 10,5, 22,22, 'JPG' );
			
		}
	}
}
