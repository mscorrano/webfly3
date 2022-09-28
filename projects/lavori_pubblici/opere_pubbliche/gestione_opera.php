<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );
require_once( __DIR__ . "/../utility/analisi_opere.php" );

function elenco_campi( $nome_campo, $record ) {
	if( $nome_campo == "data" )
		return array( "CLASS" => "small text-nowrap" );
}

function giorni_scadenza( $record ) {
}


function gestione_chiusura( $record ) {
	if( array_key_exists( "flag_criticita", $record ) && $record["flag_criticita"] == 1 && $record["flag_risolta"] == 0 )
		return true;
	else
		return false;
}

function gestione_riapertura( $record ) {
	if( array_key_exists( "flag_criticita", $record ) && $record["flag_criticita"] == 1 && $record["flag_risolta"] == 1 )
		return true;
	else
		return false;
}
	
class GESTIONE_OPERA extends WEBFLY_REMOTE_FORM {
		
	function __construct() {
		parent::__construct( "opere_pubbliche" );
	}		
	
	function aggiorna_elenchi() {
		$this->refresh( $this->elenco_note );
		$this->refresh( $this->elenco_iter );
		$this->refresh( $this->scadenze_opere );
		
		$id_opera = $this->get_session( "id_opera" );
		$dati_opera = $this->DB_MANAGER->get_row( "opere_pubbliche", $id_opera );
		
		$dati_fase = $this->DB_MANAGER->get_row( "db_fasi", $dati_opera["id_fase"] );
		
		$this->innerHTML( "fase_opera", $dati_fase["descrizione"] );	
		
		$colore = 'background-color:'.$dati_fase["colore"];
		if( $dati_fase["colore_testo"] != "" )
			$colore .= '; color:'.$dati_fase["colore_testo"];
		
		$this->set_style( "fase_opera_container", $colore );
	}
	
	function aggiorna_badges() {
		
		$id_opera = $this->get_session( "id_opera" );
		
		// Note...
		$badge = $this->DB_MANAGER->exec_sql( "SELECT COUNT(*) AS NUMERO FROM note_opere WHERE id_opera='".$id_opera."'", true );

		if( array_key_exists( "NUMERO", $badge ) && $badge["NUMERO"] != "" && $badge["NUMERO"] != "0" )
			$txt_badge = '<span class="badge neutral-2-bg text-secondary">'.$badge["NUMERO"].'</span>';
		else
			$txt_badge = '';
		
		$this->refresh( "note_opera_badge", $txt_badge );	
	}
	
	function aggiorna_anomalie() {
		
		$id_opera = $this->get_session( "id_opera" );
		$dati_opera = $this->DB_MANAGER->get_row( "opere_pubbliche", $id_opera );
		
		$html_analisi = "";
		if( $dati_opera["flag_criticita"] == 1 ) {
			
			$testo = "";
			$messaggio = unserialize( $dati_opera["esito_controllo"] );
			if( array_key_exists( "MESSAGGI", $messaggio )) {
				foreach( $messaggio["MESSAGGI"] as $anomalia ) {
					if( $testo == "" )
						$testo = "<ul>";
					
					$testo .= '<li>'.$anomalia["DESCRIZIONE"]." (Codice ".str_pad( $anomalia["CODICE"], 3, "0", STR_PAD_LEFT ).")</li>";
				}
			}
			
			if( $testo != "" )
				$html_analisi = '<div class="alert alert-danger bg-danger text-white mt-4"><h4 class="alert-heading">ANOMALIA OPERA</h4>'.$testo.'</ul></div>';
		}
		
		$this->refresh( "messaggio_criticita", $html_analisi );			
	}
	
	function aggiungi_nota_onclick() {
		$this->load_popup_form( "opere_pubbliche/nota_opera", "AGGIUNGI NOTA" );
	}
	
	function salva_onclick() {
		$this->DB_MANAGER->update( "opere_pubbliche", $this->REMOTE_DATA );
		
		$descrizione = "";
		if( $this->REMOTE_DATA["codice_opera"] != "" )
				$descrizione .= '['.$this->REMOTE_DATA["codice_opera"].'] ';
		$descrizione .= $this->REMOTE_DATA["descrizione"];	
		
		$this->innerHTML( "descrizione_opera", $descrizione );
		
		analizza_opera( $this->DB_MANAGER, $this->REMOTE_DATA["id_opera"] );		
		$this->aggiorna_anomalie();
		$this->aggiorna_elenchi();		
		$this->open_popup( "I dati dell'opera sono stati aggiornati correttamente" );
	}
	
	function id_categoria_opera_onchange() {
		$dati_categoria_opera = $this->DB_MANAGER->get_row( "db_categorie_opere", $this->REMOTE_DATA["id_categoria_opera"] );
		
		$html_icona = "";
		if( array_key_exists( "icona", $dati_categoria_opera ) && $dati_categoria_opera["icona"] != "" ) 
			$html_icona = '<i class="'.$dati_categoria_opera["icona"].' fa-2xl"></i>';		
		
		$this->innerHTML( "icona_tipologia", $html_icona );
	}
	
	function elenco_note_criticita_risolta_onclick( $id, $key ) {
		if( $key != "" ) {
			$dati_nota = $this->DB_MANAGER->get_row( "note_opere", $id );
			
			$this->DB_MANAGER->exec_sql( "UPDATE note_opere SET flag_risolta=1, data_fine_criticita='".date("Y-m-d h:i:s")."'" );
			
			$this->refresh( $this->elenco_note );
		}
	}	
	
	function elenco_note_riattiva_criticita_onclick( $id, $key ) {
		if( $key != "" ) {
			$dati_nota = $this->DB_MANAGER->get_row( "note_opere", $id );
			
			$this->DB_MANAGER->exec_sql( "UPDATE note_opere SET flag_risolta=0, data_fine_criticita=NULL" );
			
			$this->refresh( $this->elenco_note );
		}
	}
	
	function fase_opera_onclick() {
		global $WEBFLY;

		if( $WEBFLY->UTENTE["livello_abilitazione"] < 9 ) {
			$this->open_popup( "L'Aggiornamento dell'Iter deve essere effettuato utilizzando il TAB 'Iter/Note'" );
		} else {
			$this->set_session( 'modifica_iter_libera', 1);
			$this->load_popup_form('opere_pubbliche/iter');
		}
	}	
	
	function aggiungi_iter_onclick() {
		$this->set_session( 'modifica_iter_libera', 0);
		$this->load_popup_form('opere_pubbliche/iter');
	}
	
	function onload() {
		
		$id_opera = $this->get_session( "id_opera" );
		
		$dati_opera = $this->DB_MANAGER->get_row( "opere_pubbliche", $id_opera );
		
		if( !array_key_exists( "id_opera", $dati_opera )) {
			$this->open_popup( "Errore in fase di gestione Opera" );
			$this->load_form( "opere_pubbliche/ricerca" );
			return;
		}
		
		$dati_fase =  $this->DB_MANAGER->get_row( "db_fasi", $dati_opera["id_fase"] );
		
		$this->header  = '<div class="row" style="margin:-25px";>';
				
		$colore = 'background-color:'.$dati_fase["colore"];
		if( $dati_fase["colore_testo"] != "" )
			$colore .= '; color:'.$dati_fase["colore_testo"];
		
		$this->header .= '<div class="font-weight-bold text-uppercase" style="'.$colore.'"';
		$this->header .= ' id="fase_opera_container">';
		$this->header .= '<span id="fase_opera" style="width:50px; padding: 0px 10px 35px 10px; writing-mode: vertical-lr; transform: rotate(180deg); text-align: end;">'.$dati_fase["descrizione"].'</span>';
		$this->header .= '</div><div class="col p-4">';
		$this->header .= '<h4 class="ml-2 mb-4">';
		$this->header .= '<div class="row">';
		$this->header .= '<div class="col-1">';		
		$this->header .= '<div id="icona_tipologia" class="mt-3 text-primary">';
		
		$dati_categoria_opera = $this->DB_MANAGER->get_row( "db_categorie_opere", $dati_opera["id_categoria_opera"] );
		if( array_key_exists( "icona", $dati_categoria_opera ) && $dati_categoria_opera["icona"] != "" ) 
			$this->header .= '<i class="'.$dati_categoria_opera["icona"].' fa-2xl"></i>';
		$this->header .= '</div>';
		$this->header .= '</div>';
		$this->header .= '<div class="col" id="descrizione_opera">';
		if( $dati_opera["codice_opera"] != "" )
				$this->header .= '['.$dati_opera["codice_opera"].'] ';
		$this->header .= $dati_opera["descrizione"];		
		$this->header .= '</div>';
		$this->header .= '</div>';
		$this->header .= '</h4>';
		$this->header .= '<div id="messaggio_criticita"></div>';
		$this->header .= '<br/>';
		$this->footer = '</div></div>';
		$this->from_table();
		
		$this->add_collapse( "dati_generali", "Dati Generali" );		
		$this->add_collapse( "dati_finanziari", "Dati Finanziari" );	
		$this->add_collapse( "note_opera", "Iter / Note" );	
		$this->add_collapse( "incarichi", "Incarichi" );		
		$this->add_collapse( "atti", "Documenti" );			
		$this->add_collapse( "monitoraggio", "Monitoraggio" );	
		$this->add_collapse( "rendicontazione", "Rendiconto" );
		
		// TAB DATI GENERALI...
		$this->CUP->col = 4;	
		$this->lotto->col = 2;
		$this->DUP->col = 2;
		$this->codice_opera->col = 2;		
		$this->lotto->end_row = true;		
		$this->DUP->end_row = true;
		$this->descrizione->end_row = true;		
		$this->id_comune->col = 4;	
		$this->id_tipologia_finanziamento->end_row = false;
		
		
		// TAB ITER / NOTE...
		
		// SCADENZE...
		$elenco = new FORM_ELEMENT( "scadenze_opere", "attivita_tecnica" );
		$elenco->datatable( "scadenze_opere" );
		$elenco->title  = "ATTIVIT&Agrave; TECNICO/AMMINISTRATIVE OPERA <strong>E SCADENZE</strong>";
		$elenco->filter = "id_opera = '".$dati_opera["id_opera"]."'";	
		$elenco->hide   = "flag_completata,data_completamento";
		$elenco->col_width = array( "data_scadenza" => "col-2" );	
		
		$elenco->campi_aggiuntivi = array(
										array( 	"ID"		=> "giorni_scadenza",
												"ETICHETTA"	=> "Giorni restanti",
												"CALLBACK"	=> "giorni_scadenza" ));
		$this->add_element( $elenco );

		$evento = $this->add_element( new FORM_ELEMENT( "eventi_scadenze", "EVENTI", FORM_ELEMENT_TYPE::BLOCK ));
		$evento->add_event( new FORM_EVENT( "aggiungi_scadenza", "Aggiungi attività...", "btn-primary", EVENT_ICONS::PLUS ) );		
		
		// ITER...
		$elenco = new FORM_ELEMENT( "elenco_iter", "Iiter Opera" );
		$elenco->datatable( "iter_opera" );
		$elenco->title  = "ITER TECNICO/AMMINISTRATIVO OPERA";
		$elenco->filter = "id_opera = '".$dati_opera["id_opera"]."'";	
		$elenco->col_width = array( "data" => "col-1" );	
		$elenco->format_campi = "elenco_campi";
		
		$this->add_element( $elenco );

		$evento = $this->add_element( new FORM_ELEMENT( "eventi_ITER", "EVENTI", FORM_ELEMENT_TYPE::BLOCK ));
		$evento->add_event( new FORM_EVENT( "aggiungi_iter", "Aggiorna fase opera...", "btn-primary", EVENT_ICONS::PLUS ) );
		
		// NOTE...
		$elenco = new FORM_ELEMENT( "elenco_note", "Note Opera" );
		$elenco->datatable( "note_opere" );
		$elenco->title  = "NOTE / CRITICIT&Agrave; OPERA";
		$elenco->filter = "id_opera = '".$dati_opera["id_opera"]."'";
		$elenco->hide 	= "flag_criticita,flag_risolta,data_fine_criticita";
		$elenco->format_campi = "elenco_campi";
		
		$elenco->col_width = array( "data" 				=> "col-1",
									"id_operatore"		=> "col-2",
									"stato_criticita" 	=> "col-1" );

		
		$evento_chiusura = $elenco->add_event( new FORM_EVENT( "criticita_risolta", "Risolta...", "btn-success" ) );
		$evento_chiusura->condition_callback = "gestione_chiusura";	
		
		$evento_chiusura = $elenco->add_event( new FORM_EVENT( "riattiva_criticita", "Riattiva...", "btn-danger" ) );
		$evento_chiusura->condition_callback = "gestione_riapertura";
		
		$this->add_element( $elenco );
		
		$eventi_note = $this->add_element( new FORM_ELEMENT( "eventi_note", "EVENTI", FORM_ELEMENT_TYPE::BLOCK ));
		$eventi_note->add_event( new FORM_EVENT( "aggiungi_nota", "Aggiungi nota", "btn-primary", EVENT_ICONS::PLUS ) );
		
		// FINE NOTE...
		
		
		// TAB Atti..
		//...
		
		// Assegnazione campi ai TABS...
		$this->assign_collapse( "dati_generali", "CUP, lotto, CUI, DUP, codice_opera, descrizione, id_comune, flag_questionari" ); 
		$this->assign_collapse( "dati_finanziari", "id_tipologia_finanziamento,importo_finanziamento" );
		$this->assign_collapse( "note_opera", "elenco_note,eventi_note,elenco_iter,eventi_ITER,scadenze_opere,eventi_scadenze" );

		// Assegnazione badges...
		$this->aggiorna_badges();
		$this->aggiorna_anomalie();
		
		$this->set_values( $dati_opera );
		
		$this->add_event( new FORM_EVENT( "salva", "Salva...", "btn-success", EVENT_ICONS::SAVE ));
		$this->add_back_event();
		$this->add_exit_event();
	}
}