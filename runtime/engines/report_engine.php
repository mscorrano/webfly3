<?php

	// EVENTUALE INIZIALIZZAZIONE CLASSE REMOTE FORM...
	try {
		$form_class = "";
		
		if( array_key_exists( "rn", $_GET )) {
			$form_class = base64_decode($_GET["rn"]);
			$PARAMETRI = $_GET;
		}
		
		if( array_key_exists( "rn", $_POST )) {
			$form_class = ($_POST["rn"]);
			$PARAMETRI = $_POST;
		}

		if( $form_class != "" ) { 
			$class_file = $_SERVER["DOCUMENT_ROOT"] . "/projects/".$WEBFLY->PROGETTO["percorso_classi"]."/".$form_class.".php";
	
			$REMOTE_FORM = null;
			$REMOTE_HANDLERS = array();	
			
			if( file_exists( $class_file )) {
				// CARICA IL FILE...
				
				include( $class_file );
				
				$elenco_classi = get_declared_classes();
				
				$nome_classe = "";
				for( $i = count( $elenco_classi ) - 1; $i > 0; $i-- ) {
					
					if( get_parent_class( $elenco_classi[$i] ) == "WEBFLY_PDF" ) {
						$nome_classe = $elenco_classi[$i];
						break;
					}
				}
				
				if( $nome_classe != "" ) {
				
					$REMOTE_REPORT = new $nome_classe;

					$REMOTE_REPORT->values( $PARAMETRI );
					
					$REMOTE_REPORT->structure();
					
					$REMOTE_REPORT_DATA = array();
					$REMOTE_REPORT_DATA["NAME"]  = $nome_classe;
					$REMOTE_REPORT_DATA["TITLE"] = $REMOTE_REPORT->report_title();
					
					
					if( array_key_exists( "print", $PARAMETRI )) {
						echo $REMOTE_REPORT->output();
						die();
					}
				}
			} else {
				if( !array_key_exists( "print", $PARAMETRI )) {
					echo "Report ".$form_class." non corretto";
					die();
					remote_error( "Report ".$form_class." non corretto" );
				} else {
					echo "Report ".$form_class." non corretto";
					die();
				}
			}
		}
	} catch( Exception $e ) {
		remote_error("ERRORE IN FASE DI INIZIALIZZAZIONE PROGETTO (RUNTIME-0x120):\n".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
?>