<?php

require_once( __DIR__ . "/../../../../modules/framework/remote_form.php" );

class PARAMETRI extends WEBFLY_REMOTE_FORM {
	
	function __construct() {
		parent::__construct();
	}
	
	function parametri_seleziona_onclick( $id, $key ) {
		$tabella = $this->get_session( "TABELLA" );
		
		if( $key != "" && $tabella != "" ) {
			$dati_parametro = $this->DB_MANAGER->exec_sql( "SELECT * FROM ".$tabella." WHERE ".$key, true );
			
			$chiave = $this->DB_MANAGER->get_keys( $tabella );
			
			if( array_key_exists( $chiave, $dati_parametro )) {
				$this->set_session( "STATO", "EDIT" );
				$this->set_session( "ID", $dati_parametro[$chiave] );
				
				$this->refresh();
			} else $this->remote_error( "ERRORE NELLA SELEZIONE DEL PARAMETRO ($key - PARAMETRO NON TROVATO)" );
		} else $this->remote_error( "ERRORE NELLA SELEZIONE DEL PARAMETRO ($key - $tabella)".$this->get_session( "TABELLA" ) );
	}
	
	function seleziona_onclick( $id, $key ) {
		$this->set_session( "STATO", "ELENCO" );
		$this->set_session( "TABELLA", $id );
		$this->set_session( "ID", -1 );
		$this->refresh();
	}
	
	function aggiorna_onclick() {
		$tabella = $this->get_session( "TABELLA" );
		
		if( $tabella != "" ) {
			$this->DB_MANAGER->update( $tabella, $this->REMOTE_DATA );
			$this->set_session( "STATO", "ELENCO" );
			$this->open_popup( "I dati sono stati aggiornati correttamente" );
			$this->load_form();	
		}
	}	
	
	function inserisci_onclick() {
		$tabella = $this->get_session( "TABELLA" );
		
		if( $tabella != "" ) {
			$this->DB_MANAGER->insert( $tabella, $this->REMOTE_DATA );
			$this->set_session( "STATO", "ELENCO" );
			$this->open_popup( "I dati sono stati inseriti correttamente" );
			$this->load_form();	
		}
	}
	
	function esci_onclick() {
		$this->set_session( "STATO", "ELENCO" );
		$this->set_session( "ID", -1 );
		$this->refresh();
	}
	
	function nuovo_parametro_onclick() {
		$this->set_session( "STATO", "NEW" );
		$this->set_session( "ID", -1 );
		$this->refresh();		
	}
	
	function render() {
		$this->onload();
		
		$buffer  = '<div class="row">';
		$buffer .= '<div class="col-2">';
		$buffer .= '<h5>Parametri</h5>';
		
		$tabelle = $this->DB_MANAGER->exec_sql( "SHOW TABLE STATUS WHERE NAME LIKE 'dB_%'" );
		
		foreach( $tabelle as $tabella ) {
			$evento = new FORM_EVENT( "seleziona", $tabella["Comment"], "btn-primary" );
			$evento->event_array( $tabella["Name"] ); 
			$evento->form_validation = false;
			$evento->called_form	 = "PARAMETRI";
			
			$buffer .= $evento->build( "w-100 mb-2" );
		}
		$buffer .= '</div>';
		$buffer .= '<div class="col">';
		$buffer .= $this->header;
		$buffer .= parent::build();
		$buffer .= '</div>';
		$buffer .= '</div>';
		$buffer .= $this->footer;
		return $buffer;
	}
	
	function onload() {
		
		$tabella = $this->get_session( "TABELLA" );
		
		if( $tabella == "" ) {
			$this->header = "<h5>Selezionare Tabella</h5>";
			return;
		}

		$tabella_selezionata = $this->DB_MANAGER->exec_sql( "SHOW TABLE STATUS WHERE NAME='".$tabella."'", true );
		
		if( array_key_exists( "Comment", $tabella_selezionata )) {
			$this->header = '<h5 class="mb-4">Gestione <span class="font-weight-bold">'.$tabella_selezionata["Comment"].'</span></h5>';
			
			
			switch( $this->get_session( "STATO" )) {
				case "EDIT":
					$dati_parametro = $this->DB_MANAGER->get_row( $tabella, $this->get_session( "ID" ) );										
					$this->from_table( $tabella );
					$this->set_values( $dati_parametro );

					$this->add_event( new FORM_EVENT( "aggiorna", "Salva...", "btn-success", EVENT_ICONS::SAVE ));
					
					$nuovo = new FORM_EVENT( "esci", "Esci...", "btn-danger" );
					$nuovo->form_validation = false;
					$this->add_event( $nuovo );
					break;
					
				case "NEW":				
					$this->from_table( $tabella );

					$this->add_event( new FORM_EVENT( "inserisci", "Salva...", "btn-success", EVENT_ICONS::SAVE ));
					
					$nuovo = new FORM_EVENT( "esci", "Esci...", "btn-danger" );
					$nuovo->form_validation = false;
					$this->add_event( $nuovo );					
					break;
					
				default:
					$elenco = new FORM_ELEMENT( "parametri", "Elenco" );
					$datatable = $elenco->datatable( $tabella );
					
					$campi = $this->DB_MANAGER->exec_sql( "SHOW FIELDS FROM ".$tabella );
					
					$hide = "";
					foreach( $campi as $campo ) { 
						if( strpos( strtoupper( $campo["Field"] ) , "FLAG_" ) !== false ) {
							if( $hide != "" )
								$hide .= ",";
							
							$hide .= $campo["Field"];
						}
					}
					
					$datatable->set_param( "hide", $hide );
					
					$elenco->add_event( new FORM_EVENT( "seleziona", "Seleziona...", "btn-primary" ) );
					
					$this->add_element( $elenco );
					
					$nuovo = new FORM_EVENT( "nuovo_parametro", "Nuovo...", "btn-warning", EVENT_ICONS::PLUS );
					$nuovo->form_validation = false;
					$this->add_event( $nuovo );
			}
		}
	}
}

?>