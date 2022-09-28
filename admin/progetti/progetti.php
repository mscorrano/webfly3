<?php
	
	$TOOLBOX = array();
	$TOOLBOX[] = array( "icon" 		=> "fa-solid fa-arrow-right-from-bracket",
						"text" 		=> "ESCI",
						"link"		=> "/index.php" );
						
	include( "../../lib/layout/header.php" );
	
	// GESTIONE POSTBACK...
	if( array_key_exists( "save", $_POST )) {
		$REMOTE_FORM = new WEBFLY_FORM( "progetti", "", "", $_FRWK["DB"], "progetti" );
		$REMOTE_FORM->values( $_POST );
		$REMOTE_FORM->save();
	}
	
	function evidenzia( &$record ) {
		if( array_key_exists( "id_progetto", $_GET ) && $_GET["id_progetto"] == $record["id_progetto"] ) {
			$record["nome"] = '<strong>'.$record["nome"].'</strong>';
			return array( "CLASS" => "bg-danger text-white" );
		}
	}
	
	function seleziona_progetto() {
		header( "location: gestione_progetto.php?id_progetto=".$_GET["id_progetto"] );
	}
	
	$id_progetto = -1;
	if( array_key_exists( "id_progetto", $_GET ))
		$id_progetto = $_GET["id_progetto"];
	
	if( array_key_exists( "id_progetto", $_POST ))
		$id_progetto = $_POST["id_progetto"];
	
	echo '<div class="container-fluid bg-white m-4 p-4">';
		echo '<h4 class="bg-success text-white pl-3 pr-3 pt-1 pb-1">GESTIONE PROGETTI</h4>';
		echo '<div class="row">';
			echo '<div class="col">';
				$datatable = new DATATABLE( "progetti", $_FRWK["DB"] );
				$datatable->cancella = false;
				$datatable->hide	 = "icona";
				$datatable->row_eval = "evidenzia";
				$datatable->aggiungi_evento( 901, "Admin", "success", "seleziona_progetto" );
				$datatable->render();
				
				echo '<a href="progetti.php?new" class="btn btn-primary">';
				echo '<i class="fa-solid fa-diagram-project fa-xl m-2 mb-3 d-block"></i>';
				echo 'Aggiungi progetto';
				echo '</a>';
			echo '</div>';
			
			echo '<div class="col">';
			if( array_key_exists( "new", $_GET ) || $id_progetto > 0 ) {
				$dati_progetto = $_FRWK["DB"]->get_row( "progetti", $id_progetto );
				if( !array_key_exists( "id_progetto", $dati_progetto )) {
					$dati_progetto = $_FRWK["DB"]->get_empty_row( "progetti" );
					$id_progetto = -1;
				}
				
				echo '<h4 class="mb-4">';
				if( $id_progetto > 0 )
					echo $dati_progetto["nome"];
				else
					echo "NUOVO UTENTE";
				echo '</h4>';
				
				$action = "progetti.php";
				
				if( $id_progetto > 0 )
					$action .= '?id_progetto='.$id_progetto;
				
				$FORM_PROGETTO = new WEBFLY_FORM( "gestione_progetti", $action, "POST", $_FRWK["DB"], "progetti" );
				$FORM_PROGETTO->from_table();
				$FORM_PROGETTO->remove_field( "last_login" );
				$FORM_PROGETTO->nome->required = true;
				$FORM_PROGETTO->icona->end_row = true;
				$FORM_PROGETTO->values( $dati_progetto );
				$FORM_PROGETTO->add_event( new FORM_EVENT( "save", "Salva", "btn-primary", EVENT_ICONS::SAVE, true ) );
				
				$FORM_PROGETTO->render();
			}			
			echo '</div>';
		echo '</div>';				
	echo '</div>';
	include( "../../lib/layout/footer.php" );
?>