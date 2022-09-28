<?php
global $LLPP_SONDE;
$LLPP_SONDE[] = function( $dati_opera, $DB_MANAGER ) {
	$ESITO = array();
	
	$ESITP["TIPO"]			= "RUP";
	$ESITO["CODICE"] 		= 0;
	$ESITO["DESCRIZIONE"] 	= "";
	$ESITO["MESSAGGIO"]		= "";
	
	if( $dati_opera["id_rup"] == "" || $dati_opera["id_rup"] == "-1" ) {
		$ESITO["CODICE"] = 1;
		$ESITO["DESCRIZIONE"] = "RUP NON INSERITO";
	} else {
		
		$dati_rup = $DB_MANAGER->get_row( "rup", $dati_opera["id_rup"] );
		
		
		if( !array_key_exists( "id_rup", $dati_rup ) ) {
			$ESITO["CODICE"] = 2;
			$ESITO["DESCRIZIONE"] = "RUP NON PRESENTE";
		} else { 
		
			if( $dati_rup["flag_attivo"] == 0 ) {
				$ESITO["CODICE"] = 3;
				$ESITO["DESCRIZIONE"] = "RUP NON IN SERVIZIO (DA SOSTITUIRE)";
			}
		}
	}
	
	return $ESITO;
};

?>