<?php
global $LLPP_SONDE;
$LLPP_SONDE[] = function( $dati_opera, $DB_MANAGER ) {
	$ESITO = array();
	
	$ESITP["TIPO"]			= "NOTE_CRITICITA";
	$ESITO["CODICE"] 		= 0;
	$ESITO["DESCRIZIONE"] 	= "";
	$ESITO["MESSAGGIO"]		= "";
	
	$elenco = $DB_MANAGER->exec_sql( "SELECT * FROM note_opere WHERE id_opera='".$dati_opera["id_opera"]."' AND flag_criticita=1 AND flag_risolta=0" );

	if( count( $elenco ) > 1 )
		$ESITO["DESCRIZIONE"] = '<ul>';	
	
	foreach( $elenco as $nota ) {
		$ESITO["CODICE"] = 1;
		if( $ESITO["DESCRIZIONE"] != "" ) {
			$ESITO["DESCRIZIONE"] = '<ul><li>'.$nota["nota"].'</li>';
		}
		
		if( count( $elenco ) > 1 )
			$ESITO["DESCRIZIONE"] = '<ul><li>'.$nota["nota"].'</li>';
		else
			$ESITO["DESCRIZIONE"] = $nota["nota"];
	}

	if( count( $elenco ) > 1 )
		$ESITO["DESCRIZIONE"] = '</ul>';	
	
	return $ESITO;
};

?>