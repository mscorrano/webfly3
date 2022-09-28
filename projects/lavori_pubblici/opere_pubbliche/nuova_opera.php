<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );


class NUOVA_OPERA extends WEBFLY_REMOTE_FORM {
	
	function __construct() {
		parent::__construct( "opere_pubbliche" );
	}
	
	function esci_onclick() {
		$this->load_form( "opere_pubbliche/ricerca" );
	}
	
	function salva_onclick() {
		$this->DB_MANAGER->insert( "opere_pubbliche", $this->REMOTE_DATA );
		$this->open_popup( "Opera creata correttamente" );
		$this->load_form( "opere_pubbliche/ricerca" );	
		
	}
	
	function onload() {
		global $WEBFLY;
		$this->header = '<h4 class="ml-2 mb-4">Creazione nuova opera</h4>';
		
		$this->from_table( "opere_pubbliche" );
		//$this->add_element( (new FORM_ELEMENT( "id_utente", "Utente", $WEBFLY->DB ))->select( "utenti", "id_utente", "nome_esteso" ));
		
		$this->id_rup->end_row 				= true;
	
		$this->id_rup->required 			= true;
		$this->descrizione->required		= true;
		$this->id_categoria_opera->required = true;
		
		$this->descrizione->end_row     = true;
		$this->importo_finanziamento->col = 3;
		$this->id_comune->col = 4;
		$this->codice_opera->col = 2;
		//$this->CUP->attr["class"]   = "bg-info";
		
		$this->add_event( new FORM_EVENT( "salva", "Salva", "btn-success", EVENT_ICONS::SAVE ) );
		$esci = $this->add_event( new FORM_EVENT( "esci", "Esci...", "btn-danger", EVENT_ICONS::QUIT ) );
		
		$esci->form_validation = false;
	}
	
}

?>