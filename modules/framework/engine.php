<?php

// ENABLE WekFLY 3.0 Engine

// SESSION START...

switch( session_status() ) {
	case PHP_SESSION_NONE:
		session_start(); 
		break;
		
	case PHP_SESSION_DISABLED:
		die("NECESSARIO ABILITARE LA GESTIONE DELLE SESSIONI");
		break;
}

// INCLUDE SECTION...

if( !isset( $_FRWK )) {
	include( __DIR__."/../../conf/config.php" );
}

if( !class_exists( "DB_MANAGER" )) {
	include( __DIR__."/../../lib/database/dbmanager.php" );
}

if( !class_exists( "DATATABLE" )) {
	include( __DIR__."/../../lib/database/database.php" );
}

if( !class_exists( "WEBFLY_FRAMEWORK" )) {
	include( __DIR__."/../../modules/framework/framework.php" );
}

if( !class_exists( "WEBFLY_FORM" )) {
	include( __DIR__."/../../modules/framework/form.php" );
}

if( !class_exists( "WEBFLY_REMOTE_FORM" )) {
	include( __DIR__."/../../modules/framework/remote_form.php" );
}

if( !class_exists( "WEBFLY_PDF" )) {
	include( __DIR__."/../../modules/reports/remote_report.php" );
}

// INITIALIZE...

$_FRWK["DB"] = new DB_MANAGER( $_FRWK["PARAMS"]["DATABASE"]["DB_SERVER"], $_FRWK["PARAMS"]["DATABASE"]["USERNAME"], $_FRWK["PARAMS"]["DATABASE"]["PASSWD"], $_FRWK["PARAMS"]["DATABASE"]["DATABASE"] );

include( __DIR__."/../users/session.php" );
