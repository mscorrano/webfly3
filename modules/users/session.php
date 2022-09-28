<?php

if( !array_key_exists( "frwk_session_SESSION_ID", $_SESSION )) {
	if( !isset( $NOLOGIN ) || $NOLOGIN == false )
		header( "location: /login.php" );
} else {
	
	$WEBFLY = new WEBFLY_FRAMEWORK( $_FRWK["PARAMS"] );
	
	// RECUPERO I DATI DELL'UTENTE
	$sql = "SELECT * FROM utenti WHERE attivo=1 AND id_utente='".$_SESSION["frwk_session_USER_ID"]."'";

    $DATI_UTENTE = $WEBFLY->DB->exec_sql( $sql, true );
	if( !array_key_exists( "id_utente", $DATI_UTENTE )) {
		if( !isset( $NOLOGIN ) || $NOLOGIN == false )
			header( "location: /login.php" );
	} else {
		$WEBFLY->UTENTE = $DATI_UTENTE;
	}
	
	// Inizializzazione Progetto...
	if( $_SERVER["PHP_SELF"] == "/menu.php" && array_key_exists( "p", $_GET )) {
		$_SESSION["WEBFLY_PROJECT_ID"] = $_GET["p"];
	}
	
	$_SESSION["WEBFLY_ADMIN"] = false;
	
	// Dati Progetto...
	if( array_key_exists( "WEBFLY_PROJECT_ID", $_SESSION )) 
		$WEBFLY->PROGETTO = $_FRWK["DB"]->get_row( "progetti", $_SESSION["WEBFLY_PROJECT_ID"] );
	
	// Dati Profilo...
	if( property_exists( $WEBFLY, "PROGETTO" ) && is_array( $WEBFLY->PROGETTO ) && array_key_exists( "id_progetto", $WEBFLY->PROGETTO ) ) {
		$sql = "SELECT * FROM utenti_abilitazioni WHERE id_progetto='".$WEBFLY->PROGETTO["id_progetto"]."' AND id_utente='".$WEBFLY->UTENTE["id_utente"]."'";
		
		$dati_abilitazione = $_FRWK["DB"]->exec_sql( $sql, true );
		
		if( array_key_exists( "id_assegnazione", $dati_abilitazione )) {
			$WEBFLY->UTENTE["id_profilo"] = $dati_abilitazione["id_profilo"];
			
			$dati_profilo = $_FRWK["DB"]->get_row( "db_profili", $dati_abilitazione["id_profilo"] );
			$WEBFLY->UTENTE["profilo"] = $dati_profilo["descrizione"];
		}
	}
	
	// Dati Livello abilitazione
	if( array_key_exists( "FRWK_ABILITAZIONE", $_SESSION ))
		$WEBFLY->UTENTE["livello_abilitazione"] = $_SESSION["FRWK_ABILITAZIONE"];
	else
		$WEBFLY->UTENTE["livello_abilitazione"] = 0;

	$WEBFLY->SESSION = $_SESSION;
}

?>