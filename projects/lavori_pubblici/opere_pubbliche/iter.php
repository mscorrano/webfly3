<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );


class ITER_OPERA extends WEBFLY_REMOTE_FORM {
			
	function __construct() {
		parent::__construct( "iter_opera" );
	}
	
	function salva_onclick() {
		global $WEBFLY;
		
		$id_opera = $this->get_session( "id_opera" );
		
		$nuova_nota = $this->REMOTE_DATA;
		$nuova_nota["id_opera"] = $id_opera;		
		$nuova_nota["id_operatore"] = $WEBFLY->UTENTE["id_utente"];
		
		$this->DB_MANAGER->insert( "iter_opera", $nuova_nota );
		$this->DB_MANAGER->exec_sql( "UPDATE opere_pubbliche SET id_fase='".$this->REMOTE_DATA["id_fase"]."' WHERE id_opera='".$id_opera."'" );
		
		$this->close_popup_form();
		$this->call_method( "opere_pubbliche/gestione_opera", "aggiorna_elenchi" );
	}
	
	function onload() {
				
		$admin = $this->get_session( "modifica_iter_libera" );
		
		$id_opera = $this->get_session( "id_opera" );
		$dati_opera = $this->DB_MANAGER->get_row( "opere_pubbliche", $id_opera );
		
		if( $admin == 1 ) {
			$this->header = '<h5 class="mb-4">RICOSTRUZIONE ITER OPERA (Amministratore)</h5>';
			$this->hide_fields( "id_operatore" );
		} else {
			$this->header = '<h5 class="mb-4">AGGIORNA FASE OPERA</h5>';
			$this->hide_fields( "id_fase_precedente,id_operatore" );
		}
		
		$this->header .= "<br/>";
		$this->from_table();
		
		$this->data->required     = true;		
		$this->id_fase->required  = true;
		if( $admin != 1 ) {
			// Cerco nel workflow le possibili fasi successive...
			$elenco = $this->DB_MANAGER->exec_sql( "SELECT * FROM workflow_opere WHERE id_fase_partenza='".$dati_opera["id_fase"]."'" );
			$id_ammissibili = "";
			foreach( $elenco as $fase ) {
				if( $id_ammissibili != "" )
					$id_ammissibili .= ",";
				
				$id_ammissibili .= $fase["id_fase_arrivo"];
			}
			if( $id_ammissibili == "" )
				$id_ammissibili = "-1";
			$this->id_fase->filter = "id_fase IN (".$id_ammissibili.")";
			
		}
		
		$this->add_event( new FORM_EVENT( "salva", "salva", "btn-primary", EVENT_ICONS::SAVE ) );
		$esci = $this->add_event( new FORM_EVENT( "esci",  "Esci", "btn-danger", EVENT_ICONS::QUIT ) );
		$esci->dismiss_button = true;
	}
}

?>
