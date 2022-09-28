<?php

if( !isset( $_FRWK )) {
	include( __DIR__."/../conf/config.php" );
}

if( !class_exists( "DB_MANAGER" )) {
	include( __DIR__."/../lib/database/dbmanager.php" );
}

if( !class_exists( "WEBFLY_FRAMEWORK" )) {
	include( __DIR__."/../modules/framework/framework.php" );
}
if( !class_exists( "WEBFLY_FORM" )) {
	include( __DIR__."/../modules/framework/form.php" );
}

// FUNZIONI RUNTIME
function include_event_manager( $PATH ) {
	if( is_dir( $PATH )) {
		$handle = opendir( $PATH );
		
		while( ($entry = readdir( $handle )) !== false ) { 
			if( $entry != "." && $entry != ".." ) {
				if( is_dir( $PATH ."/". $entry )) {
					include_event_manager( $PATH ."/". $entry );
				} else {
					$file = pathinfo( $PATH ."/". $entry );
					if( $file["extension"] == "php" ) 
						include( $PATH ."/". $entry );
				}
			}
		}
	}
}

function response( $messaggio, $remote_form = null ) {
	$message = array();
	$message["MESSAGE"] = $messaggio;
	
	echo "WF;".base64_encode(json_encode($message));
	die();
}

function remote_error( $messaggio ) {
	echo "ERR;".base64_encode(json_encode($messaggio));
	die();
}


// INIT...

$_FRWK["DB"] = new DB_MANAGER( $_FRWK["PARAMS"]["DATABASE"]["DB_SERVER"], $_FRWK["PARAMS"]["DATABASE"]["USERNAME"], $_FRWK["PARAMS"]["DATABASE"]["PASSWD"], $_FRWK["PARAMS"]["DATABASE"]["DATABASE"] );

if( !array_key_exists( "frwk_session_SESSION_ID", $_SESSION )) {
	remote_error( "AUTH-REQUIRED" );
} else {
	global $WEBFLY;
	$WEBFLY = new WEBFLY_FRAMEWORK( $_FRWK["PARAMS"] );
	
	// RECUPERO I DATI DELL'UTENTE
	$sql = "SELECT * FROM utenti WHERE attivo=1 AND id_utente='".$_SESSION["frwk_session_USER_ID"]."'";

    $DATI_UTENTE = $WEBFLY->DB->exec_sql( $sql, true );
	if( !array_key_exists( "id_utente", $DATI_UTENTE )) {
		if( !isset( $NOLOGIN ) || $NOLOGIN == false )
			header( "location: login.php" );
	} else {
		$WEBFLY->UTENTE = $DATI_UTENTE;
	}
	
	// Dati Livello abilitazione
	if( array_key_exists( "FRWK_ABILITAZIONE", $_SESSION ))
		$WEBFLY->UTENTE["livello_abilitazione"] = $_SESSION["FRWK_ABILITAZIONE"];
	else
		$WEBFLY->UTENTE["livello_abilitazione"] = 0;
	
	// INIZIALIZZO IL PROGETTO...
	$WEBFLY->PROGETTO = $_FRWK["DB"]->get_row( "progetti", $ID_PROGETTO );
	
	if( !array_key_exists( "id_progetto", $WEBFLY->PROGETTO ) ) {
		die("ERR;ERRORE IN FASE DI INIZIALIZZAZIONE PROGETTO (RUNTIME-0x100)");
	}
	
	// INIZIALIZZAZIONE DB_MANAGER DI DEFAULT
	$WEBFLY->DB_MANAGER = null;
	
	if( $WEBFLY->PROGETTO["db_progetto"] != "" ) {
		$WEBFLY->DB_MANAGER = new DB_MANAGER( $_FRWK["PARAMS"]["DATABASE"]["DB_SERVER"], $_FRWK["PARAMS"]["DATABASE"]["USERNAME"], $_FRWK["PARAMS"]["DATABASE"]["PASSWD"], $WEBFLY->PROGETTO["db_progetto"] );
	}

	// INIZIALIZZAZIONE REMOTE ENGINES...
	
	$engines = scandir( __DIR__ . "/engines" );
	
	foreach( $engines as $id => $file ) {
		if( strpos( strtoupper( $file ), ".PHP" )!==false )
			include( __DIR__ ."/engines/".$file );
	}
		
	$WEBFLY->SESSION = $_SESSION;
	session_write_close();
}
?>