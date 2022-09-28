<?php

require_once( __DIR__ . "/../../../../modules/framework/remote_form.php" );

class RUP extends WEBFLY_REMOTE_FORM {
	
	function aggiorna_rup_onclick() {
		$this->DB_MANAGER->update( "rup", $this->REMOTE_DATA );
		$this->set_session( "STATO", "SEARCH" );
		$this->open_popup( "I dati del RUP ".$this->REMOTE_DATA["nome_esteso"]." sono stati aggiornati correttamente" );
		$this->load_form();
	}
	
	function crea_rup_onclick() {
		$this->DB_MANAGER->insert( "rup", $this->REMOTE_DATA );
		$this->set_session( "STATO", "SEARCH" );
		$this->open_popup( "I dati del RUP ".$this->REMOTE_DATA["nome_esteso"]." sono stati inseriti correttamente" );
		$this->load_form();		
	}
	
	function nuovo_rup_onclick() {
		$this->set_session( "STATO", "NEW" );
		$this->load_form();
	}
	
	function cerca_rup_onclick() {
		$this->elenco_rup->filter = "nome_esteso LIKE '".addslashes( $this->REMOTE_DATA["nome_esteso"] )."%'";
		$this->refresh( $this->elenco_rup );
	}
	
	function elenco_rup_seleziona_onclick( $id, $key ) {	
				
		if( $key != "" ) {
			$dati_rup = $this->DB_MANAGER->exec_sql( "SELECT * FROM rup WHERE ".$key, true );
			
			if( array_key_exists( "id_rup", $dati_rup )) {
				$this->set_session( "STATO", "EDIT" );
				$this->set_session( "id_rup", $dati_rup["id_rup"] );
				$this->refresh();
			} else $this->remote_error( "ERRORE NELLA SELEZIONE DEL RUP (RUP NON TROVATO)" );
		} else $this->remote_error( "ERRORE NELLA SELEZIONE DEL RUP" );
	}
	
	function esci_onclick() {
		$this->set_session( "STATO", "SEARCH" );
		$this->refresh();
	}
	
	function onload() { 
		global $WEBFLY;

		switch( $this->get_session( "STATO" )) {
			case "EDIT":
				$this->header = '<h4 class="ml-2 mb-4">Gestione dati RUP</h4>';
				$this->from_table( "rup" );
				
				$dati_rup = $this->DB_MANAGER->get_row( "rup", $this->get_session( "id_rup" ) );
				$this->set_values( $dati_rup );
				
				$this->nome_esteso->required = true;
				$this->codice_fiscale->end_row = true;

				$this->add_event( new FORM_EVENT( "aggiorna_rup", "Salva...", "btn-success", EVENT_ICONS::SAVE ));
				
				$nuovo = new FORM_EVENT( "esci", "Esci...", "btn-danger" );
				$nuovo->form_validation = false;
				$this->add_event( $nuovo );	
				break;
				
			case "NEW":
				$this->header = '<h4 class="ml-2 mb-4">Inserimento nuovo RUP</h4>';
				$this->from_table( "rup" );
				$this->nome_esteso->required = true;
				$this->codice_fiscale->end_row = true;

				$this->add_event( new FORM_EVENT( "crea_rup", "Salva...", "btn-success", EVENT_ICONS::SAVE ));
				
				$nuovo = new FORM_EVENT( "esci", "Esci...", "btn-danger" );
				$nuovo->form_validation = false;
				$this->add_event( $nuovo );	
				
				break;
				
			default:
				$this->header = '<h4 class="ml-2 mb-4">Gestione RUP</h4>';
				$nominativo = new FORM_ELEMENT( "nome_esteso", "Nominativo" );
				$nominativo->required = true;
				$nominativo->add_event( new FORM_EVENT( "cerca_rup", "Cerca...", "btn-primary", EVENT_ICONS::SEARCH ));
				
				$nominativo->end_row = true;
				
				$this->add_element( $nominativo );
				
				$elenco = new FORM_ELEMENT( "elenco_rup", "Elenco" );
				$elenco->datatable( "rup" );
				
				$elenco->add_event( new FORM_EVENT( "seleziona", "Seleziona...", "btn-primary" ) );
				
				$this->add_element( $elenco );
				
				$nuovo = new FORM_EVENT( "nuovo_rup", "Nuovo...", "btn-warning", EVENT_ICONS::PLUS );
				$nuovo->form_validation = false;
				$this->add_event( $nuovo );
				
				$this->add_exit_event();
				break;
			}
	}
}

?>