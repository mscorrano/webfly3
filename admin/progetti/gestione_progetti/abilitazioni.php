<?php

$id_progetto = -1;
if( array_key_exists( "id_progetto", $_SESSION ))
	$id_progetto = $_SESSION["id_progetto"];

if( array_key_exists( "id_progetto", $_GET ))
	$id_progetto = $_GET["id_progetto"];

if( array_key_exists( "id_progetto", $_POST ))
	$id_progetto = $_POST["id_progetto"];

if( $id_progetto == -1 )
	header( "location: progetti.php" );

$id_profilo = -1;

if( array_key_exists( "id_profilo", $_GET ))
	$id_profilo = $_GET["id_profilo"];

if( array_key_exists( "id_profilo", $_POST ))
	$id_profilo = $_POST["id_profilo"];

if( $id_profilo != -1 ) {
	$dati_profilo = $_FRWK["DB"]->get_row( "db_profili", $id_profilo );
	if( !array_key_exists( "id_profilo", $dati_profilo ) ) {
		$id_profilo = -1;
		$dati_profilo = $_FRWK["DB"]->get_empty_row( "db_profili" );
	}
} else
	$dati_profilo = $_FRWK["DB"]->get_empty_row( "db_profili" );

if( array_key_exists( "action", $_POST )) {
	switch( $_POST["action"] ) {
		case "create_profile":
			$sql = "INSERT INTO db_profili( id_progetto, descrizione ) VALUES( '".$id_progetto."', '".addslashes($_POST["descrizione"])."' )";
			
			$_FRWK["DB"]->exec_sql( $sql );
			break;
			
		case "save_profile":
		
			$sql = "UPDATE db_profili SET descrizione='".addslashes($_POST["descrizione"])."' WHERE id_progetto='".$id_progetto."'";
			
			$_FRWK["DB"]->exec_sql( $sql );
			break;
			
		case "dettaglio_profilo":
			if( array_key_exists( "profilo", $_POST )) {
				list( $id_menu, $livello ) = explode( "_", $_POST["profilo"] );
				$sql = "SELECT * FROM db_profili_dettagli WHERE id_menu='".$id_menu."' AND id_profilo='".$id_profilo."'";
				
				$dettaglio = $_FRWK["DB"]->exec_sql( $sql, true );
				if( array_key_exists( "id_dettaglio", $dettaglio )) {
					if( $dettaglio["livello_abilitazione"] != $livello ) {
						$sql = "UPDATE db_profili_dettagli SET livello_abilitazione='".$livello."' WHERE id_dettaglio='".$dettaglio["id_dettaglio"]."'";
						$_FRWK["DB"]->exec_sql( $sql );
					}
				} else {
					$sql = "INSERT INTO db_profili_dettagli( id_profilo, id_menu, livello_abilitazione ) VALUES( '".$_POST["id_profilo"]."', '".$_POST["id_menu"]."', '".$livello."' )";
					
					$_FRWK["DB"]->exec_sql( $sql );
				}
			}
			break;
			
		case "assegna_profilo":
			$chiavi = array();
			$valori = array();
			
			$chiavi["id_progetto"] = $id_progetto;
			$chiavi["id_utente"]   = $_POST["id_utente"];
			
			$valori["id_profilo"]  = $id_profilo;
			
			$_FRWK["DB"]->update( "utenti_abilitazioni", $valori, true, $chiavi );
			break;
	}
}

echo '<div class="row">';
	echo '<div class="col">';
		echo '<h5>PROFILI</h5>';
		
		$datatable = new DATATABLE( "db_profili", $_FRWK["DB"] );
		$datatable->postback_params = "id_progetto=".$id_progetto."&active_tab=abilitazioni";
		$datatable->filter = "id_progetto='".$id_progetto."'";
		$datatable->cancella = false;
		$datatable->hide	 = "id_progetto";
		$datatable->render();
	
		echo '<form method="post" action="gestione_progetto.php?id_progetto='.$id_progetto.'"/>';
			echo '<input type="hidden" name="active_tab" value="abilitazioni"/>';
			echo '<div class="form-row mt-4">';
				echo '<div class="form-group col">';
					echo '<label for="descrizione_profilo">Descrizione profilo</label>';
					echo '<input type="text" name="descrizione" id="descrizione_profilo" class="form-control" value="'.$dati_profilo["descrizione"].'"/>';
				echo '</div>';
				echo '<div class="form-group col">';
					echo '<button type="submit" class="btn btn-';
					if( $id_profilo == -1 )
						echo 'primary';
					else
						echo 'success';
					echo '" name="action" value="';
					if( $id_profilo == -1 )
						echo 'create_profile';
					else
						echo 'save_profile';
					echo '">';
					echo '<i class="fa-solid fa-user-plus fa-xl m-2 mb-2 d-block"></i>';
					if( $id_profilo == -1 )
						echo 'Aggiungi profilo';
					else
						echo 'Aggiorna profilo';
					echo '</button>';
				echo '</div>';
			echo '</div>';
		echo '</form>';
		
		if( $id_profilo != -1 ) {
			echo '<h5>UTENTI ABILITATI</h5>';
			$datatable = new DATATABLE( "SELECT utenti.nome_esteso FROM utenti INNER JOIN utenti_abilitazioni ON utenti.id_utente = utenti_abilitazioni.id_utente WHERE id_progetto='".$id_progetto."' AND id_profilo='".$id_profilo."'", $_FRWK["DB"] );
			$datatable->postback_params = "id_progetto=".$id_progetto."&active_tab=abilitazioni";
			$datatable->cancella = false;
			$datatable->hide	 = "id_progetto";
			
			echo '<div class="mb-4">';
			$datatable->render();
			echo '</div><br/>';
			
			echo '<div class="mt-4">';
			$form = new WEBFLY_FORM( "aggiungi_utente", 'gestione_progetto.php?id_progetto='.$id_progetto, "POST", $_FRWK["DB"] );
			$form->add_param( "active_tab",  "abilitazioni" );
			$form->add_param( "id_progetto", $id_progetto );
			$form->add_param( "id_profilo",  $id_profilo );
			$form->add_param( "action",  "assegna_profilo" );
			$form->active_tab = "abilitazioni";
			$form->add_element( (new FORM_ELEMENT( "id_utente", "Utente", FORM_ELEMENT_TYPE::SELECT, $_FRWK["DB"] ))->select( "utenti", "id_utente", "nome_esteso" ));
		
			$form->add_event( new FORM_EVENT( "add_user", "Aggiungi Utente", "btn btn-success", EVENT_ICONS::ADD_USER, true ));
			echo $form->build();
			echo '</div>';
		}
	echo '</div>';
	echo '<div class="col">';
		
		if( $id_profilo != -1 ) {
			echo '<h5>PROFILO : <span class="font-weight-bold">'.$dati_profilo["descrizione"].'</span></h5>';
			
			echo gestione_profili( $id_progetto, $id_profilo );
		}
	echo '</div>';
echo '</div>';

?>