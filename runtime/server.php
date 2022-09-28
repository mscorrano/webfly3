<?php
// WEBFLY REMOTE SERVER

// Ricostruzione sessione...
switch( session_status() ) {
	case PHP_SESSION_NONE:
		if( array_key_exists( "s", $_POST )) {
			session_start( $_POST["s"] );
		} else session_start();
			break;
		
	case PHP_SESSION_DISABLED:
		remote_error( "NECESSARIO ABILITARE LA GESTIONE DELLE SESSIONI" );
		break;
}

if( !isset( $ID_PROGETTO )) {
	$ID_PROGETTO = -1;
}

if( array_key_exists( "WEBFLY_PROJECT_ID", $_SESSION ))
	$ID_PROGETTO = $_SESSION["WEBFLY_PROJECT_ID"];

$COMMAND = "PING";

$COMMANDS = array( 	"sf" => "FORM_SAVE_DATA",
					"gf" => "LOAD_FORM",
					"gd" => "FORM_POST_DATA",
					"bc" => "BUTTON_CLICK",
					"gr" => "GET_REPORT",
					"ev" => "REMOTE_EVENT",
					"ff" => "FETCH_ELEMENT",
					"mc" =>	"CALL_METHOD"
					);
						
if( array_key_exists( "q", $_GET )) {
							
	if( array_key_exists( $_GET["q"], $COMMANDS )) {
		$COMMAND = $COMMANDS[$_GET["q"]];
	} else $COMMAND = "PING";
}

if( array_key_exists( "q", $_POST )) {						
	if( array_key_exists( $_POST["q"], $COMMANDS )) {
		$COMMAND = $COMMANDS[$_POST["q"]];
	} else $COMMAND = "PING";
}


global $REMOTE_DATA;
$REMOTE_DATA = array();
$REMOTE_FORM = NULL;

$INTERNAL_COMMANDS = explode( ",", "fn,rn");

foreach( $INTERNAL_COMMANDS as $SERVER_COMMAND ) {
	if( array_key_exists( $SERVER_COMMAND, $_POST )) 
		$_POST[$SERVER_COMMAND] = base64_decode( urldecode( $_POST[$SERVER_COMMAND] ));
}

if( array_key_exists( "pars", $_POST )) {
	parse_str($_POST["pars"], $REMOTE_DATA );
}

// Inclusione classi ed inizializzazione database...
include( __DIR__."/runtime_engine.php" );

if( !is_null( $REMOTE_FORM ) )
	$REMOTE_FORM->REMOTE_DATA = $REMOTE_DATA;

switch( $COMMAND ) {
	case "PING":
		ob_start();
		print_r($_POST);
		$dump = ob_get_clean();
		
		echo "PONG(".$dump.")";
		break;
	
	case "LOAD_FORM": 
		if( is_null( $REMOTE_FORM ) )
			remote_error( "Form non trovato #001 (".$REMOTE_FORM_CLASS.")" );
		
		$struttura_form = $REMOTE_FORM->render();
		
		$STRUCTURE = array();
		$STRUCTURE["FORM"]         = $struttura_form;
		$STRUCTURE["HANDLERS"]     = $REMOTE_HANDLERS;
		$STRUCTURE["COMMANDS"]     = $REMOTE_FORM->send_commands();
		$STRUCTURE["parameters"]   = $REMOTE_FORM->server_requests( "PARAMS" );
		$STRUCTURE["get_classes"]  = $REMOTE_FORM->server_requests( "CLASSES" );
		$STRUCTURE["element_attr"] = $REMOTE_FORM->server_requests( "ATTRS" );
	
		response( $STRUCTURE );
		
		break;
	
	case "CALL_METHOD":	
		if( array_key_exists( "fn", $_POST ))
			try {
				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #003 (".$REMOTE_FORM_CLASS.")" );

				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #001 (".$REMOTE_FORM_CLASS.")" );
				
				$metodo = $_POST["m"];
				
				$parameters = $_POST["mp"];
				if( $parameters != "" )
					$parameters = unserialize( $parameters );
				
				if( !method_exists( $REMOTE_FORM, $metodo ))
					remote_error( "Event handler <strong>".$metodo."()</strong> non presente nel form ".$REMOTE_FORM_CLASS );

				$REMOTE_FORM->structure();
				
				$retval = $REMOTE_FORM->$metodo( $parameters );	
				
				$STRUCTURE = array();
				$STRUCTURE["COMMANDS"]     = $REMOTE_FORM->send_commands();
				$STRUCTURE["parameters"]   = $REMOTE_FORM->server_requests( "PARAMS" );
				$STRUCTURE["get_classes"]  = $REMOTE_FORM->server_requests( "CLASSES" );
				$STRUCTURE["element_attr"] = $REMOTE_FORM->server_requests( "ATTRS" );
						
				response( $STRUCTURE, $REMOTE_FORM );
				
			} catch( Exception $e ) {
				remote_error( $e->getMessage() );
			}
		else remote_error( "Parametro Form non corretto" );
		break;
		
	case "REMOTE_EVENT":	
		if( array_key_exists( "fn", $_POST ))
			try {
				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #003 (".$REMOTE_FORM_CLASS.")" );

				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #001 (".$REMOTE_FORM_CLASS.")" );
						
				$remote_object = $_POST["bn"];
				
				$metodo = $remote_object."_on".$_POST["en"];
				
				if( !method_exists( $REMOTE_FORM, $metodo ))
					remote_error( "Event handler <strong>".$metodo."()</strong> non presente nel form ".$REMOTE_FORM_CLASS );

				$REMOTE_FORM->structure();
				
				if( !array_key_exists( "rkey", $_POST ) && !array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo();		
				
				if( array_key_exists( "rkey", $_POST ) && !array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( 0, base64_decode($_POST["rkey"]) );
				
				if( !array_key_exists( "rkey", $_POST ) && array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( $_POST["rid"] );
			
				if( array_key_exists( "rkey", $_POST ) && array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( $_POST["rid"], base64_decode($_POST["rkey"]) );
				
				$call = $metodo;
				
				if( $retval === false )
					$call .= "#FALSE";	
				
				if( $retval === true )
					$call .= "#TRUE";	
				
				if( $retval != "" )
					$call .= "#".$retval;
				
				$STRUCTURE = array();
				$STRUCTURE["COMMANDS"]     = $REMOTE_FORM->send_commands();
				$STRUCTURE["parameters"]   = $REMOTE_FORM->server_requests( "PARAMS" );
				$STRUCTURE["get_classes"]  = $REMOTE_FORM->server_requests( "CLASSES" );
				$STRUCTURE["element_attr"] = $REMOTE_FORM->server_requests( "ATTRS" );
						
				response( $STRUCTURE, $REMOTE_FORM );
				
			} catch( Exception $e ) {
				remote_error( $e->getMessage() );
			}
		else remote_error( "Parametro Form non corretto" );
		
		break;
		
	case "FORM_SAVE_DATA":
		if( array_key_exists( "fn", $_POST ))
			try {
				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #002 (".$REMOTE_FORM_CLASS.")" );
				
				// FORM SAVE...
				$retval = $REMOTE_FORM->save();

				switch( $retval ) {
					case -2:
					case 0:
						// ERRORE...
						remote_error( "Errore in fase di salvataggio dati (Codice: ".$retval.")" );
						break;
						
					case -1:
						// UPDATE...
						$messaggio = array();
						$messaggio["SAVE"]   = "OK";
						$messaggio["METHOD"] = "UPDATE";
						
						response( $messaggio, $REMOTE_FORM );
						break;
						
					default:
						// INSERT...
						$messaggio = array();
						$messaggio["SAVE"]      = "OK";
						$messaggio["METHOD"]    = "INSERT";
						$messaggio["INSERT_ID"] = $retval;
						
						response( $messaggio, $REMOTE_FORM );
						break;
				}
			} catch( Exception $e ) {
				remote_error( $e->getMessage() );
			}
		else {
			remote_error( "Parametro Form non corretto" );
		}
		break;
		
	case "BUTTON_CLICK":
		if( array_key_exists( "fn", $_POST ))
			try {
				if( is_null( $REMOTE_FORM ) )
					remote_error( "Form non trovato #003 (".$REMOTE_FORM_CLASS.")" );
				
				$button = $_POST["bn"];
				
				if( !method_exists( $REMOTE_FORM, $button."_onclick" ))
					remote_error( "Event handler <strong>".$button."_onclick()</strong> non presente nel form ".$REMOTE_FORM_CLASS );
				
				$REMOTE_FORM->structure();
				
				$metodo = $button."_onclick";
				
				if( !array_key_exists( "rkey", $_POST ) && !array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo();		
				
				if( array_key_exists( "rkey", $_POST ) && !array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( 0, base64_decode($_POST["rkey"]) );
				
				if( !array_key_exists( "rkey", $_POST ) && array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( $_POST["rid"] );
			
				if( array_key_exists( "rkey", $_POST ) && array_key_exists( "rid", $_POST ))
					$retval = $REMOTE_FORM->$metodo( $_POST["rid"], base64_decode($_POST["rkey"]) );
				
				$call = $button."_onclick";
				
				if( $retval === false )
					$call .= "#FALSE";	
				
				if( $retval === true )
					$call .= "#TRUE";	
				
				if( $retval != "" )
					$call .= "#".$retval;
				
				$STRUCTURE = array();
				$STRUCTURE["COMMANDS"]     = $REMOTE_FORM->send_commands();
				$STRUCTURE["parameters"]   = $REMOTE_FORM->server_requests( "PARAMS" );
				$STRUCTURE["get_classes"]  = $REMOTE_FORM->server_requests( "CLASSES" );
				$STRUCTURE["element_attr"] = $REMOTE_FORM->server_requests( "ATTRS" );
						
				response( $STRUCTURE, $REMOTE_FORM );
				
			} catch( Exception $e ) {
				remote_error( $e->getMessage() );
			}
		else remote_error( "Parametro Form non corretto" );
		
		break;
		
	case "GET_REPORT":

		if( array_key_exists( "print", $_POST ) || array_key_exists( "print", $_GET ))
			$PRINT = true;
		else
			$PRINT = false;
		
		if( isset( $REMOTE_REPORT_DATA ) && is_array( $REMOTE_REPORT_DATA ) ) {
			try {
				$messaggio = array();
				$messaggio["REPORT"]   = $REMOTE_REPORT_DATA["NAME"];
				$messaggio["TITOLO"]   = $REMOTE_REPORT_DATA["TITLE"];
				
				if( array_key_exists( "PDF_DATA", $REMOTE_REPORT_DATA )) {
					echo $REMOTE_REPORT_DATA["REPORT"];
					die();
				} else response( $messaggio );
			} catch( Exception $e ) {
				if( !$PRINT )
					remote_error( $e->getMessage() );
				else
					echo $e->getMessage();
			}
		} else {
			if( !$PRINT )
				remote_error( "Parametro Report non corretto" );
			else
				echo "ERRORE IN FASE DI INIZIALIZZAZIONE DEL REPORT";
		}
		break;
		
	case "FETCH_ELEMENT":
		if( is_null( $REMOTE_FORM ) )
			remote_error( "Form non trovato #001 (".$REMOTE_FORM_CLASS.")" );
		
		$field = $_POST["bn"];
		
		$struttura = $REMOTE_FORM->build_element( $field );
		
		$STRUCTURE = array();
		$STRUCTURE["FIELD"]        = $struttura;
		$STRUCTURE["FIELD_ID"]     = '#'.$REMOTE_FORM->form_id().' #'.$field;
		$STRUCTURE["HANDLERS"]     = $REMOTE_HANDLERS;
		$STRUCTURE["COMMANDS"]     = $REMOTE_FORM->send_commands();
		$STRUCTURE["parameters"]   = $REMOTE_FORM->server_requests( "PARAMS" );
		$STRUCTURE["get_classes"]  = $REMOTE_FORM->server_requests( "CLASSES" );
		$STRUCTURE["element_attr"] = $REMOTE_FORM->server_requests( "ATTRS" );
	
		response( $STRUCTURE );
			
		break;
}

// AGGIORNA SESSIONE
session_start( $_POST["s"] );
$_SESSION = $WEBFLY->SESSION;
session_write_close();
?>