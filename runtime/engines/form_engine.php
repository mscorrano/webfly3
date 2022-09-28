<?php

	// EVENTUALE INIZIALIZZAZIONE CLASSE REMOTE FORM...
	try {
		if( array_key_exists( "fn", $_POST )) {
			$REMOTE_FORM_CLASS = $_POST["fn"];

			$class_file = $_SERVER["DOCUMENT_ROOT"] . "/projects/".$WEBFLY->PROGETTO["percorso_classi"]."/".$REMOTE_FORM_CLASS.".php";
	
			$REMOTE_FORM = null;
			$REMOTE_HANDLERS = array();	
			
			if( file_exists( $class_file )) {
				// CARICA IL FILE...
				
				include( $class_file );
				
				$elenco_classi = get_declared_classes();
				
				$nome_classe = "";
				for( $i = count( $elenco_classi ) - 1; $i > 0; $i-- ) {
					
					if( get_parent_class( $elenco_classi[$i] ) == "WEBFLY_REMOTE_FORM" ) {
						$nome_classe = $elenco_classi[$i];
						break;
					}
				}
				
				if( $nome_classe != "" ) {
				
					$REMOTE_FORM = new $nome_classe;
					
					// Event handlers...
					$elenco_metodi = get_class_methods( $REMOTE_FORM );
					
					$REMOTE_HANDLERS = array();
					
					foreach( $elenco_metodi as $metodo ) {
						// Un event handler inizia con on... o con il nome di un campo seguito da _on
						$HANDLER = array();
						
						if( substr( strtolower($metodo), 0, 2 ) == "on" ) {
							if( $metodo != "onsave" ) {
								$HANDLER["TARGET"] = "form";
								$HANDLER["EVENT"]  = substr( strtolower($metodo), 2 );
								$HANDLER["METHOD"] = $metodo;
							}
						}
						
						if( strpos( strtolower($metodo), "_on" ) !== false ) {
							list( $campo, $evento ) = explode( "_on", $metodo );
							
							$HANDLER["TARGET"] = $campo;
							$HANDLER["EVENT"]  = strtolower($evento);
							$HANDLER["METHOD"] = $metodo;
							
						}
						
						
						// Verifico se l'evento è da impostare o è automatico...
						if( array_key_exists( "TARGET", $HANDLER )) {
							$escluso = false;
							
							// Escludo gli eventi di sistema...
							$eventi_sistema = explode( ",", "onload,onupload" );
							foreach( $eventi_sistema as $evento )
								if( $HANDLER["METHOD"] == $evento )
									$escluso = true;

							if( !$escluso )
								$REMOTE_HANDLERS[] = $HANDLER;
						}
					}
					
					// Inizializzazione dati... dai parametri della chiamata...

					$REMOTE_FORM->values( $REMOTE_DATA );
					
					// Inizializzazione parametri... se presenti...

					if( array_key_exists( "WF_fp", $REMOTE_DATA )) {
						$parametri_form_txt = base64_decode( urldecode( $REMOTE_DATA["WF_fp"] ));
		
						$parametri_form = json_decode($parametri_form_txt);
						
						if( json_last_error() !== JSON_ERROR_NONE )
							throw new Exception( "Errore nella ricostruzione della sessione per il form. Valore {".$parametri_form_txt."} atteso JSON (0x90102)", "90102" );	
						
						if( is_array( $parametri_form ) && count( $parametri_form ) > 0 ) { 
							foreach( $parametri_form as $parametro ) { 
								$REMOTE_FORM->set_session( $parametro->name, $parametro->value );
							}
						}
						
						unset( $REMOTE_DATA["WF_fp"] );
					}
				}
			}
		}
	} catch( Exception $e ) {
		remote_error("ERRORE IN FASE DI INIZIALIZZAZIONE PROGETTO (RUNTIME-0x120):\n".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
?>