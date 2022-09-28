<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );


class RICERCA_OPERA extends WEBFLY_REMOTE_FORM {

	function filtro_ricerca() {
		// Costruzione filtro ricerca...
		
		if( $this->get_session( "FILTRO_OPERE" ) != "" ) {
			return $this->get_session("FILTRO_OPERE");
		}
		
		$sql = "";
		
		// FILTRI SUI CAMPI DELLA TABELLA opere_pubbliche...
	
		$elenco_campi = $this->DB_MANAGER->exec_sql("SHOW FIELDS FROM opere_pubbliche");
		foreach( $this->REMOTE_DATA as $key => $value ) { 
			if( $value != "" ) {
				foreach( $elenco_campi as $campo ) 
					if( $campo["Field"] == $key ) {
						if( $sql != "" )
							$sql .= " AND ";
						
						if( substr( $campo["Type"], 0, 3 ) == "int" )
							$sql .= $campo["Field"] ." ='".addslashes($value)."'";
						else
							$sql .= $campo["Field"] ." LIKE '%".addslashes($value)."%'";
					}
			}
		}
		
		if( $this->REMOTE_DATA["anomalia"] > 0 ) {
			if( $sql != "" )
				$sql .= " AND ";
			
			$sql .= "flag_criticita='".($this->REMOTE_DATA["anomalia"]-1)."'";
		}
		
		return $sql;
	}
	
	function elenco_opere_seleziona_onclick( $id, $key ) {
		if( $key != "" ) {
			$dati_opera = $this->DB_MANAGER->get_row( "opere_pubbliche", $id );
			
			if( array_key_exists( "id_opera", $dati_opera )) {
				$this->set_session( "id_opera", $dati_opera["id_opera"] );
				$this->load_form( "opere_pubbliche/gestione_opera" );
			} else $this->remote_error( "ERRORE NELLA SELEZIONE DELL'OPERA (NON TROVATA)" );
		} else $this->remote_error( "ERRORE NELLA SELEZIONE DELL'OPERA" );
		
	}
	
	function cerca_onclick() { 
		$this->elenco_opere->filter = $this->filtro_ricerca();
		$this->refresh( $this->elenco_opere );
	}
	
	function report_onclick() {
		$this->open_report( "opere_pubbliche/stampa_elenco" );
	}
	
	function nuova_opera_onclick() {
		$this->load_form( "opere_pubbliche/nuova_opera" );
	}
	
	function onload() {
		global $WEBFLY;
		
		$opere = $this->DB_MANAGER->exec_sql( "SELECT COUNT(*) AS TOTALE FROM opere_pubbliche LEFT JOIN db_fasi ON opere_pubbliche.id_fase = db_fasi.id_fase WHERE flag_chiusa IS NULL OR flag_chiusa = 0", true );
		$this->header  = '<h4 class="mb-0">Ricerca Opera</h4>';
		$this->header .= '<div class="mb-4"><em >Totale opere censite : '.number_format($opere["TOTALE"],0,",",".").'</em></div>';

		$this->add_element( new FORM_ELEMENT( "descrizione", "Descrizione Opera" ))->end_row = true;
		$this->add_element( new FORM_ELEMENT( "CUP", "CUP" ));
		
		$rup = new FORM_ELEMENT( "id_rup", "RUP" );
		$rup->select( "rup", "id_rup", "nome_esteso" );
		$this->add_element( $rup );
		
		$categoria = new FORM_ELEMENT( "id_categoria_opera", "Categoria Opera" );
		$categoria->select( "db_categorie_opere", "id_categoria", "descrizione" );
		$this->add_element( $categoria );
		
		$criticita = new FORM_ELEMENT( "anomalia", "Anomalie" );
		
		$elenco = array( 	array( "id" => 0, "descrizione" => "" ),
							array( "id" => 1, "descrizione" => "NO" ),
							array( "id" => 2, "descrizione" => "SI" ));
							
		$criticita->select( $elenco, "id", "descrizione" );
		$this->add_element( $criticita );
		
		$criticita->end_row = true;
		
		$fase = new FORM_ELEMENT( "id_fase", "Fase Opera" );
		$fase->select( "db_fasi", "id_fase", "descrizione" );
		$this->add_element( $fase );
		
		$blocco_eventi = $this->add_element( new FORM_ELEMENT( "event_block", "EVENTI", FORM_ELEMENT_TYPE::BLOCK ));
		
		$cerca = $blocco_eventi->add_event( new FORM_EVENT( "cerca", "Ricerca", "btn-primary", EVENT_ICONS::SEARCH ) );
		$cerca->default = true;
		
		$blocco_eventi->add_event( new FORM_EVENT( "report", "Report", "btn-success", EVENT_ICONS::REPORT ) );
		
		$nuovo = new FORM_EVENT( "nuova_opera", "Nuova...", "btn-warning", EVENT_ICONS::PLUS );
		
		$nuovo->form_validation = false;
		$nuovo->confirm_event	= true;
		$nuovo->confirm_title	= "Nuova Opera";
		$nuovo->confirm_message  = "Si desidera creare una nuova Opera?";
				
		$blocco_eventi->add_event( $nuovo );
		$blocco_eventi->add_event( $this->exit_event() );
		
		// Risultato ricerca...
		
		$elenco = new FORM_ELEMENT( "elenco_opere", "Opere" );
		$elenco->datatable( "opere_pubbliche" );
		$elenco->add_event( new FORM_EVENT( "seleziona", "Seleziona...", "btn-primary" ) );
		
		$this->add_element( $elenco );
		$this->elenco_opere->filter = "id_opera=-1";
		$this->elenco_opere->hide = "CUP,CUI,DUP,id_comune,codice_opera";
				
	}
}

?>