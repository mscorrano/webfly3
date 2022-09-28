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

if( array_key_exists( "save_params", $_POST )) {
	$sql = "UPDATE progetti SET percorso_classi='".addslashes($_POST["percorso_classi"])."', db_progetto='".addslashes($_POST["db_progetto"])."' WHERE id_progetto='".$id_progetto."'";
			
	$_FRWK["DB"]->exec_sql( $sql );
}

$dati_progetto = $_FRWK["DB"]->get_row( "progetti", $id_progetto );

echo '<div class="col-5 mt-4">';
	$form = new WEBFLY_FORM( "parametri_progetto", 'gestione_progetto.php?id_progetto='.$id_progetto, "POST", $_FRWK["DB"] );
	$form->add_param( "active_tab",  "parametri" );
	$form->add_element( (new FORM_ELEMENT( "percorso_classi", "Percorso Classi" ) ));
	$form->add_element( (new FORM_ELEMENT( "db_progetto", "Database" ) ));
	$form->add_event( new FORM_EVENT( "save_params", "Aggiorna Parametri", "btn btn-success", EVENT_ICONS::SAVE, true ));
	$form->values( $dati_progetto );
	echo $form->build();
echo '</div>';
			
?>