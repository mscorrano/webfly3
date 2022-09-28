<?php

function analizza_opera( $DB_MANAGER, $id_opera = -1 ) {

	$sql = "SELECT * FROM opere_pubbliche";
	if( $id_opera != -1 )
		$sql .= " WHERE id_opera='".$id_opera."'";
	
	$elenco_opere = $DB_MANAGER->exec_sql( $sql );
	
	$ESITI = array();
	$ESITI["NUMERO_ANOMALIE"] = 0;
	$ESITI["ANOMALIE"] = array();
	
	// Carica sonde di analisi...
	global $LLPP_SONDE;
	
	if( !is_array( $LLPP_SONDE )) {
		$LLPP_SONDE = array();
		
		$elenco = scandir( __DIR__ . "/analisi" );
		foreach( $elenco as $file ) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if( strtolower( $ext ) == "php" ) {
				include( __DIR__ . "/analisi/".$file );
			}
		}
	}
	
	foreach( $elenco_opere as $dati_opera ) {
		$ANOMALIA_OPERA = array();
		
		$ANOMALIA_OPERA["ID"] 		= $dati_opera["id_opera"];
		$ANOMALIA_OPERA["ERRORI"] 	= 0;
		$ANOMALIA_OPERA["MESSAGGI"] = array();
		
		// Verifiche...
		foreach( $LLPP_SONDE as $SONDA ) {
			$esito = $SONDA( $dati_opera, $DB_MANAGER );
			
			if( is_array( $esito )) {
				if( $esito["CODICE"] != 0 ) {
					$ANOMALIA_OPERA["ERRORI"]++;
					$ANOMALIA_OPERA["MESSAGGI"][] = $esito;
				}
			}
		}
		
		$ESITI["NUMERO_ANOMALIE"] += $ANOMALIA_OPERA["ERRORI"];
		$ESITI["ANOMALIE"][] = $ANOMALIA_OPERA;
		
		if( $ANOMALIA_OPERA["ERRORI"] > 0 ) {
			$DB_MANAGER->exec_sql( "UPDATE opere_pubbliche SET flag_criticita=1, esito_controllo='".addslashes(serialize($ANOMALIA_OPERA))."' WHERE id_opera='".$dati_opera["id_opera"]."'" );
		} else {
			$DB_MANAGER->exec_sql( "UPDATE opere_pubbliche SET flag_criticita=0, esito_controllo='' WHERE id_opera='".$dati_opera["id_opera"]."' AND flag_criticita=1" );
		}
	}
	
	return $ESITI;
}