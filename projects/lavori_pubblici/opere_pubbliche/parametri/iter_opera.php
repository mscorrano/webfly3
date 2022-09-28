<?php

require_once( __DIR__ . "/../../../../modules/framework/remote_form.php" );

class ITER_OPERA extends WEBFLY_REMOTE_FORM {
	
	function aggiungi_onclick() {
		$this->DB_MANAGER->insert( "workflow_opere", $this->REMOTE_DATA );
		$this->refresh( $this->elenco_passaggi );
	}
	
	function onload() {
		$this->header = '<h5 class="mb-4">Gestione Iter Opere</h5>';
		
		$partenza = new FORM_ELEMENT( "id_fase_partenza", "Fase di Partenza" );
		$partenza->select( "db_fasi", "id_fase", "descrizione" );
		$this->add_element( $partenza );
		
		$arrivo = new FORM_ELEMENT( "id_fase_arrivo", "Fase di Arrivo" );
		$arrivo->select( "db_fasi", "id_fase", "descrizione" );
		$this->add_element( $arrivo );
		
		$blocco_eventi = $this->add_element( new FORM_ELEMENT( "event_block", "EVENTI", FORM_ELEMENT_TYPE::BLOCK ));
		
		$blocco_eventi->add_event( new FORM_EVENT( "aggiungi", "Aggiungi Passaggio", "btn-primary", EVENT_ICONS::PLUS ) );
		$blocco_eventi->col = 2;
		
		$elenco = new FORM_ELEMENT( "elenco_passaggi", "Opere" );
		$elenco->datatable( "workflow_opere" );
		//$elenco->add_event( new FORM_EVENT( "seleziona", "Seleziona...", "btn-primary" ) );
		
		$this->add_element( $elenco );
		
		$this->add_exit_event();
	}
}