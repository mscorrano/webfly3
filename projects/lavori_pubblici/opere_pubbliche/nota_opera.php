<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );


class NOTA_OPERA extends WEBFLY_REMOTE_FORM {
			
	function __construct() {
		parent::__construct( "note_opere" );
	}
	
	function salva_onclick() {
		global $WEBFLY;
		
		$id_opera = $this->get_session( "id_opera" );
		
		$nuova_nota = $this->REMOTE_DATA;
		$nuova_nota["id_opera"] = $id_opera;		
		$nuova_nota["id_operatore"] = $WEBFLY->UTENTE["id_utente"];
		
		$this->DB_MANAGER->insert( "note_opere", $nuova_nota );
		
		$this->refresh_element( "opere_pubbliche/gestione_opera", "elenco_note" );
		$this->close_popup_form();
	}
	
	function onload() {
		$this->hide_fields( "data, id_operatore, data_fine_criticita, flag_risolta" );
		$this->from_table();
		
		$this->nota->end_row = true;
		$this->flag_criticita->end_row = true;
		
		$this->nota->required = true;
		$this->flag_criticita->required = true;
		
		$this->add_event( new FORM_EVENT( "salva", "salva", "btn-primary", EVENT_ICONS::SAVE ) );
		$esci = $this->add_event( new FORM_EVENT( "esci",  "Esci", "btn-danger", EVENT_ICONS::QUIT ) );
		$esci->dismiss_button = true;
	}
}

?>
