<?php
	
	$TOOLBOX = array();
	$TOOLBOX[] = array( "icon" 		=> "fa-solid fa-arrow-right-from-bracket",
						"text" 		=> "ESCI",
						"link"		=> "/index.php" );
						
	include( "../../lib/layout/header.php" );

	if( $WEBFLY->UTENTE["admin"] != 1 ){
		header( 'location: /index.php' );
	} else {
		$_SESSION["WEBFLY_ADMIN"] 		= true;
		$_SESSION["WEBFLY_PROJECT_ID"] 	= 0;
	}
	
	// GESTIONE POSTBACK...
	if( array_key_exists( "save", $_POST )) {
		$REMOTE_FORM = new WEBFLY_FORM( "utente", "", "", $_FRWK["DB"], "utenti" );
		$REMOTE_FORM->values( $_POST );
		$REMOTE_FORM->save();
	}
	
	function evidenzia( &$record ) {
		if( array_key_exists( "id_utente", $_GET ) && $_GET["id_utente"] == $record["id_utente"] ) {
			$record["nome_esteso"] = '<strong>'.$record["nome_esteso"].'</strong>';
			return array( "CLASS" => "bg-danger text-white" );
		}
	}
	
	// MENU PRINCIPALE...
	
	$id_utente = -1;
	if( array_key_exists( "id_utente", $_GET ))
		$id_utente = $_GET["id_utente"];
	
	if( array_key_exists( "id_utente", $_POST ))
		$id_utente = $_POST["id_utente"];
	
	echo '<div class="container-fluid bg-white m-4 p-4">';
		echo '<h4 class="bg-success text-white pl-3 pr-3 pt-1 pb-1">GESTIONE UTENTI</h4>';
		echo '<div class="row">';
			echo '<div class="col">';
			
				$datatable = new DATATABLE( "utenti", $_FRWK["DB"] );
				$datatable->cancella = false;
				$datatable->hide	 = "last_login,email";
				$datatable->row_eval = "evidenzia";
				$datatable->render();
			
				echo '<a href="utenti.php?new" class="btn btn-primary">';
				echo '<i class="fa-solid fa-user-plus fa-xl m-2 mb-3 d-block"></i>';
				echo 'Aggiungi utente';
				echo '</a>';
			
			echo '</div>';
			echo '<div class="col">';
			
			// Aggiungi o modifica utente...
			if( array_key_exists( "new", $_GET ) || $id_utente > 0 ) {
				$dati_utente = $_FRWK["DB"]->get_row( "utenti", $id_utente );
				if( !array_key_exists( "id_utente", $dati_utente )) {
					$dati_utente = $_FRWK["DB"]->get_empty_row( "utenti" );
					$id_utente = -1;
				}
				
				echo '<h4 class="mb-4">';
				if( $id_utente > 0 )
					echo $dati_utente["nome_esteso"];
				else
					echo "NUOVO UTENTE";
				echo '</h4>';
				
				$action = "utenti.php";
				
				if( $id_utente > 0 )
					$action .= '?id_utente='.$id_utente;
				
				$FORM_UTENTE = new WEBFLY_FORM( "gestione_utenti", $action, "POST", $_FRWK["DB"], "utenti" );
				$FORM_UTENTE->from_table();
				$FORM_UTENTE->server = "/admin/server.php";
				$FORM_UTENTE->remove_field( "last_login" );
				$FORM_UTENTE->nome_esteso->required = true;
				$FORM_UTENTE->username->required = true;
				$FORM_UTENTE->username->end_row = true;
				$FORM_UTENTE->email->end_row = true;
				$FORM_UTENTE->values( $dati_utente );
				$FORM_UTENTE->add_event( new FORM_EVENT( "save", "Salva", "btn-primary", EVENT_ICONS::SAVE, true ) );
				
				$FORM_UTENTE->render();
			}
			echo '</div>';
		echo '</div>';
	echo '</div>';
	
	include( "../../lib/layout/footer.php" );
?>