<?php
global $LLPP_SONDE;
$LLPP_SONDE[] = function( $dati_opera ) {
	$ESITO = array();
	
	$ESITP["TIPO"]			= "CUP";
	$ESITO["CODICE"] 		= 0;
	$ESITO["DESCRIZIONE"] 	= "";
	$ESITO["MESSAGGIO"]		= "";
	
	if( $dati_opera["CUP"] == "" ) {
		$ESITO["CODICE"] = 1;
		$ESITO["DESCRIZIONE"] = "CUP NON INSERITO";
	} else {
		if( strlen( $dati_opera["CUP"] ) != 15 ) {
			$ESITO["CODICE"] = 4;
			$ESITO["DESCRIZIONE"] = "CUP FORMALMENTE ERRATO <em>[LUNGHEZZA ".strlen( $dati_opera["CUP"] )." E NON 15 CARATTERI]</em>";
		} else {
			$link_cup = "https://opencup.gov.it/progetto/-/cup/".$dati_opera["CUP"]."/";
			$ESITO["MESSAGGIO"] = $link_cup;
			
			$verifica = file_get_contents( $link_cup );
			
			if( strpos( $verifica, "CUP NON PRESENTE" ) !== false ) {
				$ESITO["CODICE"] = 2;
				$ESITO["DESCRIZIONE"] = "CUP ERRATO";
			}
			
			if( strpos( $verifica, "PROVINCIA" ) === false || strpos( $verifica, "PESCARA" ) === false ) {
				$ESITO["CODICE"] = 3;
				$ESITO["DESCRIZIONE"] = "CUP INESISTENTE";
			}
		}
	}
	
	return $ESITO;
};

?>