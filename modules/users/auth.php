<?php

$LDAP_AUTH = true;
if( !isset( $_FRWK )) {
	include( __DIR__."/../../conf/config.php" ); 
}
	
if( !array_key_exists( "LDAP", $_FRWK )) 
	$LDAP_AUTH = false;
else
	$LDAP_AUTH = $_FRWK["LDAP"];

if( !array_key_exists( "LDAP_SERVER", $_FRWK ) || $_FRWK["LDAP_SERVER"] == "" )
	$LDAP_AUTH = false;

if( !array_key_exists( "LDAP_DOMAIN", $_FRWK ) || $_FRWK["LDAP_DOMAIN"] == "" )
	$LDAP_AUTH = false;

if( !array_key_exists( "LDAP_PORT", $_FRWK ) || $_FRWK["LDAP_PORT"] == "" )
	$LDAP_PORT = "";
else
	$LDAP_PORT = $_FRWK["LDAP_PORT"];

if( $LDAP_AUTH ) {

	$LDAP_CONNECTION = ldap_connect( $_FRWK["LDAP_SERVER"], $LDAP_PORT ) or die( "SERVER LDAP NON RAGGIUNGIBILE" );
	
	
	$username = $_POST["frwk_username"];
	$password = $_POST["frwk_password"];
	
	ldap_set_option($LDAP_CONNECTION, LDAP_OPT_PROTOCOL_VERSION, 3) or die('ERRORE LDAP (protocol version)');
	ldap_set_option($LDAP_CONNECTION, LDAP_OPT_REFERRALS, 0);
	
	$LOGIN = @ldap_bind($LDAP_CONNECTION, $username . "@". $_FRWK["LDAP_DOMAIN"], $password);

	$SUCCESS = false;
	if( $LOGIN ) {
		// Credenziali corrette... verifico se l'utente è abilitato...
		$sql = "SELECT *
			   FROM  utenti
			   WHERE attivo=1 AND username='".$username."' AND ldap=1";
			  
		$utenti = $_FRWK["DB"]->exec_sql( $sql );

		foreach( $utenti as $utente ) {
		  if( $utente["username"]==$_POST["frwk_username"] ) {
			 $SUCCESS = true;
			 $dati_utente = $utente;
			 break;
		  }   
		}
		
		if( !$SUCCESS ) {
			echo "<div class='alert alert-danger mt-2 bg-white'>UTENTE NON ABILITATO ALL'ACCESSO.</div>";
		}
	} else echo "<div class='alert alert-danger mt-2 bg-white'>Nome utente e/o password errata.</div>";
	

} else {
	$SUCCESS = false;
}

?>