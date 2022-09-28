<?php

// ACCESSO BANCA DATI TINN
include( __DIR__ . "/../../lib/TINN/tinn.php" );
include( "../lib/genera_pdf.php" );

$tinn_connection = new TINN( "DBEUROCF.IDB" );

// QUERY TEMPESTIVITA PAGAMENTI...

$sql_tempestivita = "SELECT FATTPA_CUFIPA_ENTE.DESCR_UFFICIO_IPA, FATTPA_CUFIPA_ENTE.COD_UFFICIO_IPA,
ROUND(SUM(DATEDIFF(DAY FROM IIF( D_SCADENZA IS NULL, DATEADD(30 DAY TO DOC_DATA), D_SCADENZA ) TO GFLIQUN1.D_INSERIMENTO) * GFLIQUN1.IMPORTO),2) AS TEMPESTIVITA, 
ROUND(SUM(GFLIQUN1.IMPORTO),2) AS TOTALE,
ROUND(SUM(DATEDIFF(DAY FROM IIF( D_SCADENZA IS NULL, DATEADD(30 DAY TO DOC_DATA), D_SCADENZA ) TO GFLIQUN1.D_INSERIMENTO) * GFLIQUN1.IMPORTO)/SUM(GFLIQUN1.IMPORTO),2) AS INDICE
FROM GFPNCGN1 
INNER JOIN GFLIQUN1 ON GFLIQUN1.FA_ESERCIZIO = GFPNCGN1.ESERCIZIO AND GFLIQUN1.FA_NUMERO = GFPNCGN1.NUMERO
INNER JOIN GFANCON1 ON GFPNCGN1.ANAGRAFICA   = GFANCON1.CODICE
LEFT  JOIN FATTPA_CUFIPA_ENTE ON FATTPA_CUFIPA_ENTE.COD_UFFICIO_IPA = GFANCON1.FE_COD_UFFICIO
WHERE GFPNCGN1.ESERCIZIO = ".date("Y")." AND GFPNCGN1.TOTALE_DOCUMENTO > 0
GROUP BY FATTPA_CUFIPA_ENTE.DESCR_UFFICIO_IPA, COD_UFFICIO_IPA
ORDER BY FATTPA_CUFIPA_ENTE.DESCR_UFFICIO_IPA DESC";

// QUERY FATTURE NON PAGATE...

$sql_non_pagate = "SELECT GFANCON1.CODICE, FATTPA_CUFIPA_ENTE.DESCR_UFFICIO_IPA, GFANCON1.RAGIONE_SOC, GFANCON1.PARTITA_IVA, GFPNCGN1.ESERCIZIO, GFPNCGN1.NUMERO, FATTPA_CUFIPA_ENTE.COD_UFFICIO_IPA, 
GFPNCGN1.DOC_NUMERO, GFPNCGN1.DOC_DATA, GFPNCGN1.PROTOCOLLO_ATTO, GFPNCGN1.CODICE_CIG, GFPNCGN1.TOTALE_DOCUMENTO, GFPNCGN1.D_SCADENZA, GFPNCGN1.ESERCIZIO, GFPNCGN1.NUMERO, GFPNCGN1.TOTALE_LIQ_REV, GFRIPNC.IMPORTO AS TOTALE_NC, NC_ESERCIZIO, NC_NUMERO, GFPNCGN1.NOTE
FROM GFPNCGN1 
INNER JOIN GFANCON1 ON GFPNCGN1.ANAGRAFICA = GFANCON1.CODICE
INNER JOIN GFCACON1 ON GFPNCGN1.CAUSALE = GFCACON1.CODICE_ELEMENTO AND GFCACON1.ENTRATA_SPESA = 'S'
LEFT JOIN FATTPA_MESSAGGIO_SDI ON FATTPA_MESSAGGIO_SDI.ANNO_PROTOCOLLO = GFPNCGN1.ESERCIZIO AND FATTPA_MESSAGGIO_SDI.NUMERO_PROTOCOLLO = GFPNCGN1.PROTOCOLLO_ATTO
LEFT JOIN FATTPA_CUFIPA_ENTE ON FATTPA_CUFIPA_ENTE.COD_UFFICIO_IPA = FATTPA_MESSAGGIO_SDI.COD_DESTINATARIO 
LEFT JOIN GFRIPNC ON GFPNCGN1.ESERCIZIO = GFRIPNC.FA_ESERCIZIO AND GFPNCGN1.NUMERO = GFRIPNC.FA_NUMERO
WHERE GFPNCGN1.ESERCIZIO >= 2015 AND GFPNCGN1.ANNULLATA IS NULL AND GFPNCGN1.TOTALE_LIQ_REV != GFPNCGN1.TOTALE_DOCUMENTO AND GFPNCGN1.TOTALE_DOCUMENTO > 10
AND (GFRIPNC.IMPORTO IS NULL OR GFPNCGN1.TOTALE_LIQ_REV != GFPNCGN1.TOTALE_DOCUMENTO + GFRIPNC.IMPORTO)
ORDER BY GFPNCGN1.ESERCIZIO, GFPNCGN1.NUMERO";


$tempestivita = $tinn_connection->exec_sql( $sql_tempestivita );

$INDICE_ENTE      = 0;
$TOTALE_PAGAMENTI = 0;
$RIEPILOGO = array();

foreach( $tempestivita as $ufficio ) {
	$INDICE_ENTE      += $ufficio["TEMPESTIVITA"];
	$TOTALE_PAGAMENTI += $ufficio["TOTALE"];

	if( trim($ufficio["COD_UFFICIO_IPA"]) == "" ) {
		$ufficio["COD_UFFICIO_IPA"] = "UF15J1";
	}

	if( !array_key_exists( $ufficio["COD_UFFICIO_IPA"], $RIEPILOGO )) {

		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]] = array();
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["CODICE"]         = $ufficio["COD_UFFICIO_IPA"];
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["DESCRIZIONE"]    = $ufficio["DESCR_UFFICIO_IPA"];
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"] = array();
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["NUMERO_FATTURE"] = 0;
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["TOTALE_FATTURE"] = 0;
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["ESERCIZI"]       = array();
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["TEMPESTIVITA"]   = array();
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["TEMPESTIVITA"]["PAGAMENTI"] = $ufficio["TOTALE"];
		$RIEPILOGO[$ufficio["COD_UFFICIO_IPA"]]["TEMPESTIVITA"]["INDICE"]    = $ufficio["INDICE"];	
	}	
}

if( $TOTALE_PAGAMENTI > 0 )
	$INDICE_ENTE = round( $INDICE_ENTE / $TOTALE_PAGAMENTI, 2 );
else
	$INDICE_ENTE = 0;

$non_pagate = $tinn_connection->exec_sql( $sql_non_pagate );

$RIEPILOGO["DATI_ENTE"]["INDICE_ENTE"]      = $INDICE_ENTE;
$RIEPILOGO["DATI_ENTE"]["TOTALE_PAGAMENTI"] = $TOTALE_PAGAMENTI;
$RIEPILOGO["DATI_ENTE"]["NUMERO_FATTURE"]   = 0;
$RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"]   = 0;
$RIEPILOGO["DATI_ENTE"]["ESERCIZI"]         = array();
                                                          
$RAGIONI_SOCIALI_ESCLUSE = "HERA COMM%,ENEL ENERGIA%,A.C.A.%,TELECOM ITALIA S.P.A (TIM),A2A ENERGIA SPA,A2A ENERGIA SPA,GALA S.p.A.,Eni gas e luce SpA,AGSM AIM ENERGIA SPA,ESTRA ENERGIE SRL";
foreach( $non_pagate as $fattura ) {
	$FATTURA_ESCLUSA = false;

	if( $fattura["COD_UFFICIO_IPA"] == "" ) {
		$fattura["COD_UFFICIO_IPA"] = "UF15J1";
	}
	
	$elenco_escluse = explode( ",", $RAGIONI_SOCIALI_ESCLUSE );
	foreach( $elenco_escluse as $ragione_sociale ) {
		if( strtoupper(trim($fattura["RAGIONE_SOC"])) == strtoupper($ragione_sociale) ) {
			$FATTURA_ESCLUSA = true;
			break;
		}
		
		if( strpos( $ragione_sociale, "%" ) !== false ) {
			$pattern = substr( $ragione_sociale, 0, strpos( $ragione_sociale, "%" ) );
			
			if( substr(strtoupper(trim($fattura["RAGIONE_SOC"])),0,strlen($pattern)) == strtoupper($pattern) ) {
				$FATTURA_ESCLUSA = true;
				break;
			}
		}
	}		
	if( !$FATTURA_ESCLUSA && array_key_exists( $fattura["COD_UFFICIO_IPA"], $RIEPILOGO )) {
		
		$RIEPILOGO["DATI_ENTE"]["NUMERO_FATTURE"]++;
		$RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"] += $fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"]-$fattura["TOTALE_NC"];	
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["NUMERO_FATTURE"]++;
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["TOTALE_FATTURE"] += $fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"]-$fattura["TOTALE_NC"];
		
		if( !array_key_exists( $fattura["ESERCIZIO"], $RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ESERCIZI"] )) {
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ESERCIZI"][$fattura["ESERCIZIO"]]["NUMERO_FATTURE"] = 0;
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ESERCIZI"][$fattura["ESERCIZIO"]]["TOTALE_FATTURE"] = 0;
		}	
		
		if( !array_key_exists( $fattura["ESERCIZIO"], $RIEPILOGO["DATI_ENTE"]["ESERCIZI"] )) {
			$RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$fattura["ESERCIZIO"]]["NUMERO_FATTURE"] = 0;
			$RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$fattura["ESERCIZIO"]]["TOTALE_FATTURE"] = 0;
		}
		
		$RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$fattura["ESERCIZIO"]]["NUMERO_FATTURE"]++;
		$RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$fattura["ESERCIZIO"]]["TOTALE_FATTURE"] += $fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"]-$fattura["TOTALE_NC"];
		
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ESERCIZI"][$fattura["ESERCIZIO"]]["NUMERO_FATTURE"]++;
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ESERCIZI"][$fattura["ESERCIZIO"]]["TOTALE_FATTURE"] += $fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"]-$fattura["TOTALE_NC"];	
		
		if( !array_key_exists( $fattura["RAGIONE_SOC"], $RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"] )) {
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["NUMERO_FATTURE"] = 0;
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["TOTALE_FATTURE"] = 0;
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["CODICE"] = $fattura["CODICE"];
			$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["DETTAGLIO"] = array();
		}
			
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["NUMERO_FATTURE"]++;
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["TOTALE_FATTURE"] += $fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"]-$fattura["TOTALE_NC"];
		$RIEPILOGO[$fattura["COD_UFFICIO_IPA"]]["ELENCO_FATTURE"][$fattura["RAGIONE_SOC"]]["DETTAGLIO"][] = $fattura;
	}
}


$pdf = crea_pdf();

$pdf->Image( $_SERVER["DOCUMENT_ROOT"]."/components/img/provincia_stemma.png", 12,15, 20,25 );
$pdf->SetXY( 33,21 );
$pdf->SetFont( 'calibri_b', '', 14 );
$pdf->Cell( 5,6, utf8_decode("Provincia di Pescara") );
$pdf->SetXY( 33,25 );
$pdf->SetFont( 'calibri', '', 10 );
$pdf->Cell( 5,5, "Settore Finanziario" );
$pdf->SetXY( 33,29 );
$pdf->SetFont( 'calibri', 'U', 9 );
$pdf->Cell( 5,5, utf8_decode("Servizio Contabilità e Programmazione Finanziaria") );
$pdf->SetXY( 33,34 );
$pdf->SetFont( 'calibri', '', 9 );
$pdf->Cell( 5,5, "Piazza Italia, 34 - 65125 Pescara (PE)" );

$pdf->SetXY( 0,45 );
$pdf->SetFont( 'RobotoMono-Bold', '', 30 );
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell( 210,20, utf8_decode("MONITORAGGIO"), 0, 0, "C", 1 );
$pdf->SetXY( 0,60 );
$pdf->SetFont( 'RobotoMono-Bold', '', 16 );
$pdf->Cell( 210,10, utf8_decode("Tempi medi di pagamento e Stock Debito"), 0, 0, "C", 1 );

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont( 'RobotoMono-Bold', '', 14 );

$pdf->SetXY( 0,70 );
$pdf->Cell( 210,10, utf8_decode("DATA MONITORAGGIO : ".date("d-m-Y")), 0, 0, "C", 0 );

$pdf->SetFont( 'RobotoMono-Bold', '', 11);
$pdf->SetFillColor(0, 87, 174);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY( 10,85 );
$pdf->Cell( 74,12, "SETTORE", 1, 0, "L", 1 );
$pdf->Cell( 16,12, "CODICE", 1, 0, "L", 1 );
$pdf->Cell( 50,6, "FATTURE PAGATE", "LRT", 0, "L", 1 );
$pdf->Cell( 50,6, "FATTURE NON PAGATE", "LRT", 0, "L", 1 );
$pdf->SetXY( 100,91 );
$pdf->Cell( 30,6, "Importo",      1, 0, "L", 1 );
$pdf->Cell( 20,6, "Tempo", 1, 0, "L", 1 );
$pdf->SetXY( 150,91 );
$pdf->Cell( 20,6, "Numero", 1, 0, "L", 1 );
$pdf->Cell( 30,6, "Importo",      1, 0, "L", 1 );

$ALTEZZA_RIGA = 7;
$Y = $pdf->GetY()-2;
$RIGA = 0;
$pdf->SetFont( 'RobotoMono-Regular', '', 8);
$MASSIMA_LUNGHEZZA = 40;

$ANNO_MINIMO  = 9999;
$ANNO_MASSIMO = 0;

$TOTALE_ENTE = 0;

foreach( $RIEPILOGO as $codice_ufficio => $ufficio ) {
	if( $codice_ufficio != "DATI_ENTE" ) {
		$Y += $ALTEZZA_RIGA;
		$pdf->SetXY( 10, $Y );
		
		if( ($RIGA++)%2 == 1 ) {
			$pdf->SetFillColor(177, 213, 249);
			$pdf->SetTextColor(  0,   0,   0);
		} else {
			$pdf->SetFillColor(255, 255, 255);
			$pdf->SetTextColor(  0,   0,   0);
		}

		if( $ufficio["TEMPESTIVITA"]["INDICE"] > $RIEPILOGO["DATI_ENTE"]["INDICE_ENTE"] ) {
			$pdf->SetFillColor(255, 204,   0);
			$pdf->SetTextColor( 66,  66,  66);
			$pdf->SetFont( 'RobotoMono-Bold', '', 8);
			$RIGA = 0;
		} else $pdf->SetFont( 'RobotoMono-Regular', '', 8);
		
		if( $ufficio["TEMPESTIVITA"]["INDICE"] > 0 ) {
			$pdf->SetFillColor(237,  28,  36);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFont( 'RobotoMono-Bold', '', 8);
			$RIGA = 0;
		}
		
		if( strlen( $ufficio["DESCRIZIONE"] ) > $MASSIMA_LUNGHEZZA )
			$nome_ufficio = substr( $ufficio["DESCRIZIONE"],0, $MASSIMA_LUNGHEZZA ). "...";
		else $nome_ufficio = $ufficio["DESCRIZIONE"];
		
		$TOTALE_ENTE += $ufficio["TEMPESTIVITA"]["PAGAMENTI"];
		
		$pdf->Cell( 74,$ALTEZZA_RIGA, $nome_ufficio, 1, 0, "L", 1 );
		$pdf->Cell( 16,$ALTEZZA_RIGA, $codice_ufficio, 1, 0, "C", 1 );
		$pdf->Cell( 30,$ALTEZZA_RIGA, number_format($ufficio["TEMPESTIVITA"]["PAGAMENTI"],2,",","."), 1, 0, "R", 1 );
		$pdf->Cell( 20,$ALTEZZA_RIGA, number_format($ufficio["TEMPESTIVITA"]["INDICE"],0,",","."), 1, 0, "R", 1 );
		$pdf->Cell( 20,$ALTEZZA_RIGA, number_format($ufficio["NUMERO_FATTURE"],0,",","."), 1, 0, "R", 1 );
		if( $ufficio["TOTALE_FATTURE"] > 0 )
			$pdf->Cell( 30,$ALTEZZA_RIGA, number_format($ufficio["TOTALE_FATTURE"],2,",","."), 1, 0, "R", 1 );
		else
			$pdf->Cell( 30,$ALTEZZA_RIGA, number_format(0,2,",","."), 1, 0, "R", 1 );
		
		foreach( $ufficio["ESERCIZI"] as $anno => $dati ) {
			if( $anno < $ANNO_MINIMO )
				$ANNO_MINIMO = $anno;
			
			if( $anno > $ANNO_MASSIMO )
				$ANNO_MASSIMO = $anno;
		}
	}
}
$Y += $ALTEZZA_RIGA;
$pdf->SetXY( 10, $Y );

$pdf->SetFillColor(  0, 102, 204);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont( 'RobotoMono-Bold', '', 10);
$pdf->Cell( 90,$ALTEZZA_RIGA, "TOTALE ENTE", 1, 0, "L", 1 );
$pdf->Cell( 30,$ALTEZZA_RIGA, number_format($TOTALE_ENTE,2,",","."), 1, 0, "R", 1 );
$pdf->Cell( 20,$ALTEZZA_RIGA, $RIEPILOGO["DATI_ENTE"]["INDICE_ENTE"], 1, 0, "R", 1 );
$pdf->Cell( 20,$ALTEZZA_RIGA, number_format($RIEPILOGO["DATI_ENTE"]["NUMERO_FATTURE"],0,",","."), 1, 0, "R", 1 );
if( $RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"] > 0 )
	$pdf->Cell( 30,$ALTEZZA_RIGA, number_format($RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"],2,",","."), 1, 0, "R", 1 );
else
	$pdf->Cell( 30,$ALTEZZA_RIGA, number_format(0,2,",","."), 1, 0, "R", 1 );
$pdf->SetTextColor(  0,   0,   0);
$Y += $ALTEZZA_RIGA + 3;
$pdf->SetXY( 9, $Y );
$pdf->SetFont( 'RobotoMono-Regular', '', 8);
$pdf->Cell( 20,5, "Legenda :", 0, 0, "L", 0 );
$pdf->SetFillColor(255, 204,   0);
$pdf->Rect( 27, $Y, 5,5, "DF" );
$pdf->SetXY( 33, $Y );
$pdf->Cell( 60,5, "Superiore al tempo medio dell'Ente", 0, 0, "L", 0 );

$pdf->SetFillColor(237,  28,  36);
$pdf->Rect( 100, $Y, 5,5, "DF" );
$pdf->SetXY( 106, $Y );
$pdf->Cell( 20,5, "Tempi medi di pagamento superiori alla scadenza", 0, 0, "L", 0 );

$Y += 10;

$pdf->SetXY( 0,$Y );
$pdf->SetFont( 'RobotoMono-Bold', '', 14);
$pdf->Cell( 210,10, utf8_decode("RIPARTIZIONE STOCK DEBITO PER ESERCIZIO"), 0, 0, "C", 0 );
$pdf->SetFillColor(0, 102, 204);

$ALTEZZA = 9.5;
for( $anno = $ANNO_MINIMO; $anno <= $ANNO_MASSIMO; $anno++ ) {
	if( array_key_exists( $anno, $RIEPILOGO["DATI_ENTE"]["ESERCIZI"] )) {
		$Y += $ALTEZZA;
		$pdf->SetXY( 10,$Y );
		$pdf->SetFont( 'RobotoMono-Bold', '', 14);
		$pdf->Cell(  20,$ALTEZZA, utf8_decode($anno), 1, 0, "C", 0 );
		$pdf->SetFont( 'RobotoMono-Regular', '', 12);
		$pdf->Cell(  20,$ALTEZZA, number_format($RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["NUMERO_FATTURE"],0,",","."), 1, 0, "R", 0 );
		$pdf->SetFont( 'RobotoMono-Regular', '', 10);
		$pdf->Cell(  30,$ALTEZZA, number_format($RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"],2,",","."), 1, 0, "R", 0 );
		$pdf->Cell(  20,$ALTEZZA, number_format(round( 120 * $RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"] / $RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"],1 ),1,",",".")."%", 1, 0, "R", 0 );
		$pdf->Cell( 100,$ALTEZZA, "", 1, 0, "R", 0 );
		
		if( $RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"] > $RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"] )
			$RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"] = $RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"];
		
		if( $RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"] > 0 )
			$pdf->Rect( 101, $Y+1, round( 98 * $RIEPILOGO["DATI_ENTE"]["ESERCIZI"][$anno]["TOTALE_FATTURE"] / $RIEPILOGO["DATI_ENTE"]["TOTALE_FATTURE"],0 )   ,$ALTEZZA-2, "DF" );
		
		if( $Y > 270 ) {
			$pdf->AddPage();
			$Y = 10;
		}
	}
}

$RIGHE_PER_PAGINA  = 50;
$MASSIMA_LUNGHEZZA = 62;
$MASSIMA_LUNGHEZZA_DITTA = 41;
foreach( $RIEPILOGO as $codice_ufficio => $ufficio ) {
	$VISUALIZZA = true;
	if( $codice_ufficio == "DATI_ENTE" )
		$VISUALIZZA = false;
	
	if( array_key_exists( "codice", $_GET ) && $_GET["codice"] != $codice_ufficio )
		$VISUALIZZA = false;
	
	if( $VISUALIZZA ) {	
		$NEW_PAGE 	= true;
		$NEW_HEADER = true;
		
		$RIGA = 0;
		$pdf->SetFont( 'RobotoMono-Bold', '', 12 );
		$pdf->SetFillColor(0, 102, 204);
		$pdf->SetTextColor(255, 255, 255);
		foreach( $ufficio["ELENCO_FATTURE"] as $ditta => $elenco_fatture ) {
			if( $NEW_PAGE ) {
				$NEW_PAGE = false;
				$pdf->AddPage();
		
				$pdf->SetXY( 0,10 );
				$pdf->SetFont( 'RobotoMono-Bold', '', 14 );
				$pdf->SetFillColor(0, 102, 204);
				$pdf->SetTextColor(255, 255, 255);
				
				$Y = 10;
			}
				
			if( $NEW_HEADER ) {	
				$NEW_HEADER = false;
				if( strlen( $ufficio["DESCRIZIONE"] ) > $MASSIMA_LUNGHEZZA )
					$nome_ufficio = substr( $ufficio["DESCRIZIONE"],0, $MASSIMA_LUNGHEZZA ). "...";
				else $nome_ufficio = $ufficio["DESCRIZIONE"];				
				$pdf->Cell( 210,20, utf8_decode( $nome_ufficio ), 0, 0, "C", 1 );
				
				$Y = 35;
			}
			
			$pdf->SetFont( 'RobotoMono-Bold', '', 11 );
			$pdf->SetXY( 10, $Y );
			
			if( strlen( trim($ditta) ) > $MASSIMA_LUNGHEZZA_DITTA )
				$nome_ditta = substr( trim($ditta),0, $MASSIMA_LUNGHEZZA_DITTA )."... (".$elenco_fatture["CODICE"].")";
			else $nome_ditta = trim($ditta)." (".$elenco_fatture["CODICE"].")";	
			
			$NEW_HEADER = true;			
			$ALTEZZA_DETTAGLIO = 5;
			foreach( $elenco_fatture["DETTAGLIO"] as $fattura ) {
				if( $NEW_PAGE ) {					
					$NEW_PAGE = false;
					$pdf->AddPage();
					$NEW_HEADER = true;
					$pdf->setXY( 10,10 );
					$Y = 4;
				}
				
				$Y += $ALTEZZA_DETTAGLIO;
				
				if( $NEW_HEADER ) {
					$pdf->SetXY( 10, $Y );
					$NEW_HEADER = false;
					
					$RIGA = 0;
					$pdf->SetFont( 'RobotoMono-Bold', '', 11 );
					$pdf->SetFillColor(   0,  87, 174);
					$pdf->SetTextColor( 255, 255, 255);
					$pdf->Cell( 125,6, utf8_decode($nome_ditta), 1, 0, "L", 1 );
					$pdf->Cell(  20,6, utf8_decode("Num. ".number_format($elenco_fatture["NUMERO_FATTURE"],0,",",".")), 1, 0, "R", 1 );
					$pdf->Cell(  45,6, utf8_decode("Totale ".number_format($elenco_fatture["TOTALE_FATTURE"],2,",",".")), 1, 0, "R", 1 );
					
					$Y += 6;
					$pdf->SetXY( 10, $Y );
					$pdf->SetFont( 'RobotoMono-Bold', '', 7 );
					$pdf->SetFillColor(   0, 102, 204);
					$pdf->SetTextColor( 255, 255, 255);
					$pdf->Cell( 20, 6, "Prima Nota", 1, 0, "L",1 );
					$pdf->Cell( 17, 6, "Protocollo", 1, 0, "L",1 );
					$pdf->Cell( 20, 6, "Num. Fattura", 1, 0, "L",1 );
					$pdf->Cell( 20, 6, "CIG", 1, 0, "L",1 );
					$pdf->Cell( 20, 6, "Data Fattura", 1, 0, "L",1 );
					$pdf->Cell( 20, 6, "Scadenza", 1, 0, "L",1 );
					$pdf->Cell( 25, 6, "Importo", 1, 0, "L",1 );
					$pdf->Cell( 48, 6, "Note", 1, 0, "L",1 );
					$Y += 6;
				}
				
				if( ($RIGA++)%2 == 1 ) {
					$pdf->SetFillColor(177, 213, 249);
					$pdf->SetTextColor(  0,   0,   0);
				} else {
					$pdf->SetFillColor(255, 255, 255);
					$pdf->SetTextColor(  0,   0,   0);
				}				
				$pdf->SetXY( 10, $Y );
				$pdf->SetFont( 'RobotoMono-Regular', '', 8 );

				$pdf->Cell( 20, $ALTEZZA_DETTAGLIO, $fattura["NUMERO"]."/".$fattura["ESERCIZIO"], 1, 0, "C",1 );
				$pdf->Cell( 17, $ALTEZZA_DETTAGLIO, $fattura["PROTOCOLLO_ATTO"], 1, 0, "C",1 );
				$pdf->Cell( 20, $ALTEZZA_DETTAGLIO, $fattura["DOC_NUMERO"], 1, 0, "C",1 );
				$pdf->Cell( 20, $ALTEZZA_DETTAGLIO, $fattura["CODICE_CIG"], 1, 0, "C",1 );
				$pdf->Cell( 20, $ALTEZZA_DETTAGLIO, date("d-m-Y", strtotime($fattura["DOC_DATA"])), 1, 0, "C",1 );
				$pdf->Cell( 20, $ALTEZZA_DETTAGLIO, date("d-m-Y", strtotime($fattura["D_SCADENZA"])), 1, 0, "C",1 );
				$pdf->Cell( 25, $ALTEZZA_DETTAGLIO, number_format($fattura["TOTALE_DOCUMENTO"],2,",","."), 1, 0, "R",1 );
				
				if( $fattura["TOTALE_NC"] != 0 )
					$NOTA_CREDITO = number_format(-$fattura["TOTALE_NC"],2,",","."). " (N.C. ".$fattura["NC_NUMERO"]."/".substr($fattura["NC_ESERCIZIO"],2,2).")";
				else
					$NOTA_CREDITO = "";
				
				if( $fattura["TOTALE_LIQ_REV"] != 0 ) {
					$NOTA_CREDITO = "Insoluta per ".number_format($fattura["TOTALE_DOCUMENTO"]-$fattura["TOTALE_LIQ_REV"],2,",",".");
				}
				$NOTA_CREDITO .= $fattura["NOTE"];
				$MAX_LEN = 78;
				if( strlen( $NOTA_CREDITO ) > $MAX_LEN )
					$NOTA_CREDITO = substr( $NOTA_CREDITO, 0, $MAX_LEN )."...";
				
				if( strlen( $NOTA_CREDITO ) > 28 ) {
					$pdf->SetFont( 'RobotoMono-Regular', '', 5 );
				
					$pdf->Cell( 48, $ALTEZZA_DETTAGLIO/2, substr($NOTA_CREDITO, 0, floor($MAX_LEN/2)), "LRT", 0, "L",1 );
					$pdf->SetXY( 152, $Y + $ALTEZZA_DETTAGLIO/2);
					$pdf->Cell( 48, $ALTEZZA_DETTAGLIO/2, substr($NOTA_CREDITO, floor($MAX_LEN/2)), "LRB", 0, "L",1 );
				} else {
					$pdf->Cell( 48, $ALTEZZA_DETTAGLIO, $NOTA_CREDITO, 1, 0, "L",1 );
				}
				$pdf->SetFont( 'RobotoMono-Regular', '', 8 );
				if( $Y >= 265 ) {
					$NEW_PAGE = true;
				}
			}
			
			$Y += $ALTEZZA_RIGA;
			
			
			if( $Y >= 255 ) {
				$NEW_PAGE = true;
			}
		}
	}
}
$pdf->output();