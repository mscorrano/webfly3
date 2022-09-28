<?php

switch( session_status() ) {
	case PHP_SESSION_NONE:
		if( array_key_exists( "s", $_GET )) {
			session_start( $_GET["s"] );
		} else session_start();
			break;
		
	case PHP_SESSION_DISABLED:
		die("NECESSARIO ABILITARE LA GESTIONE DELLE SESSIONI");
		break;
}

// RICHIEDI PROFILO ADMIN... ED IMPOSTA IL DB su WEBFLY 
$ID_PROGETTO 	= 0;
$REQUIRE_ADMIN 	= true;
$DATABASE 		= "webfly";

global $WEBFLY_FORMS;
$WEBFLY_FORMS   = array();
$WEBFLY_FORMS[] = array( "name" 	=> "gestione_utenti",	"db_table" 	=> "utenti",
						 "event"	=> "\utenti\save" );
						 
include( __DIR__."/../runtime/server.php" );

?>