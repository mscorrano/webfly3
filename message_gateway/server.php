<?php

if( !defined( "_SERVER" ) || !array_key_exists( "DOCUMENT_ROOT", $_SERVER ) | $_SERVER["DOCUMENT_ROOT"] == "" )
	$_SERVER["DOCUMENT_ROOT"] = "/var/www/html/";
		
include( $_SERVER["DOCUMENT_ROOT"]."message_gateway/lib/interfaccia.php" );
include( "conf/clients.php" );

function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

$AUTORIZZATO = false;
if( array_key_exists( "k", $_GET )) {
	foreach( $ALLOWED as $CLIENT ) {
		if( $CLIENT["REMOTE_IP"] == $_SERVER["HTTP_X_REAL_IP"] ) {
			// Indirizzo corretto..
			if( $_GET["k"] == hash( "sha256", date("YmdHi")."_".$CLIENT["SECRET"] ) )
				$AUTORIZZATO = true;
		}
	}
}

if( !$AUTORIZZATO ) {
	echo base64url_encode( serialize( array( "STATUS" => "ERROR", "ERROR_CODE" => 900, "RETVAL" => "UNAUTHORIZED" )));
	die();
}

if( !array_key_exists( "f", $_GET )) {
	echo base64url_encode( serialize( array( "STATUS" => "ERROR", "ERROR_CODE" => 901, "RETVAL" => "FUNCTION_ERROR" )));
	die();
}

if( !array_key_exists( "p", $_GET )) {
	echo base64url_encode( serialize( array( "STATUS" => "ERROR", "ERROR_CODE" => 902, "RETVAL" => "PARAMS_ERROR" )));
	die();
}

try { 

	$chiamata  = base64url_decode( $_GET["f"] );
	$parametri = base64url_decode( $_GET["p"] );
	
	$parametri = @unserialize( $parametri );
	if( !is_array( $parametri ) ) {
		echo (serialize( array( "STATUS" => "ERROR", "ERROR_CODE" => 903, "RETVAL" => "PARAMS_ERROR: PARAM=[".$esito."] " )));
		die();
	}
	
	if( !is_array( $parametri )) {
		echo base64url_encode( serialize( array( "STATUS" => "ERROR", "ERROR_CODE" => 904, "RETVAL" => "PARAMS_ERROR: [".$parametri."]" )));
		die();
	}
	$MAILER = new INVIO_MASSIVO();

	$STATUS 	= "OK";
	$ERROR_CODE = 0;
	$retval = "";
	switch( strtolower($chiamata) ) {
		case "bonifica_indirizzo":
			$obbligatori = array( "indirizzo" );
			foreach( $obbligatori as $par ) {
				if( !array_key_exists( $par, $parametri )) {
					$STATUS 	= "ERROR";
					$ERROR_CODE = 909;
					$retval		= "Parametro : ".$par." MANCANTE";
					break;
				}
			}
			if( $ERROR_CODE == 0 ) 
				$retval = $MAILER->bonifica_indirizzo( $parametri["indirizzo"] );
			break;
			
		case "richiedi_invio":
			
			$obbligatori = array( "indirizzo" );
			foreach( $obbligatori as $par ) {
				if( !array_key_exists( $par, $parametri )) {
					$STATUS 	= "ERROR";
					$ERROR_CODE = 909;
					$retval		= "Parametro : ".$par." MANCANTE";
					break;
				}
			}
			if( $ERROR_CODE == 0 ) 
				$retval = $MAILER->richiedi_invio( $parametri["tipo_invio"], $parametri["oggetto"], base64url_decode($parametri["messaggio"]), $parametri["parametri"] );
			break;
			
		case "invia_email":
			
			$obbligatori = array( "indirizzo_email", "oggetto", "messaggio", "id_mittente", "protocollo", "lotto", "chiave_1", "chiave_2", "chiave_3" );
			
			foreach( $obbligatori as $par ) {
				if( !array_key_exists( $par, $parametri )) {
					$STATUS 	= "ERROR";
					$ERROR_CODE = 909;
					$retval		= "Parametro : ".$par." MANCANTE";
					break;
				}
			}
			if( $ERROR_CODE == 0 ) 
				$retval = $MAILER->invia_email( $parametri["indirizzo_email"], $parametri["oggetto"], base64url_decode($parametri["messaggio"]), $parametri["id_mittente"], $parametri["protocollo"], $parametri["lotto"], $parametri["chiave_1"], $parametri["chiave_2"], $parametri["chiave_3"] );
			break;
			
		case "invia_pec":
			
			$obbligatori = array( "indirizzo_email", "oggetto", "messaggio", "id_mittente", "protocollo", "lotto", "chiave_1", "chiave_2", "chiave_3" );
			
			foreach( $obbligatori as $par ) {
				if( !array_key_exists( $par, $parametri )) {
					$STATUS 	= "ERROR";
					$ERROR_CODE = 909;
					$retval		= "Parametro : ".$par." MANCANTE";
					break;
				}
			}
			if( $ERROR_CODE == 0 ) 
				$retval = $MAILER->invia_pec( $parametri["indirizzo_email"], $parametri["oggetto"], base64url_decode($parametri["messaggio"]), $parametri["id_mittente"], $parametri["protocollo"], $parametri["lotto"], $parametri["chiave_1"], $parametri["chiave_2"], $parametri["chiave_3"] );
			break;
			
		case "invia_sms":
			
			$obbligatori = array( "destinatario", "messaggio", "id_mittente", "protocollo", "lotto", "chiave_1", "chiave_2", "chiave_3" );
			
			foreach( $obbligatori as $par ) {
				if( !array_key_exists( $par, $parametri )) {
					$STATUS 	= "ERROR";
					$ERROR_CODE = 909;
					$retval		= "Parametro : ".$par." MANCANTE";
					break;
				}
			}
			if( $ERROR_CODE == 0 ) 
				$retval = $MAILER->invia_sms( $parametri["destinatario"], base64url_decode($parametri["messaggio"]), $parametri["id_mittente"], $parametri["protocollo"], $parametri["lotto"], $parametri["chiave_1"], $parametri["chiave_2"], $parametri["chiave_3"] );
			break;
			
		case "ping":
			$retval = "PONG";
			break;
			
		default:
			$ERROR_CODE = "905";
			$STATUS		= "ERROR";
			$retval		= "UNKNOWN FUNCTION (".strtolower($chiamata).")";
	}
} catch( Exception $e ) {
	$ERROR_CODE = $e->getCode();
	$STATUS		= "ERROR";
	$retval 	= $e->getMessage();
}

echo base64url_encode( serialize( array( "STATUS" => $STATUS, "ERROR_CODE" => $ERROR_CODE, "RETVAL" => $retval )));
?>