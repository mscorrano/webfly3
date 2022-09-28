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

$id_menu = -1;
if( array_key_exists( "id_menu", $_GET ))
	$id_menu = $_GET["id_menu"];

if( array_key_exists( "id_menu", $_POST ))
	$id_menu = $_POST["id_menu"];

if( array_key_exists( "m", $_GET ))
	$id_menu = $_GET["m"];

// POSTBACK
if( array_key_exists( "action", $_POST )) {
	if( $_POST["ordine"] == "" )
		$_POST["ordine"] = 0;
	
	switch( $_POST["action"] ) {
		case "save":
			$sql  = "UPDATE menu_procedura SET ";
			$sql .= "id_menu_padre = '".$_POST["id_menu_padre"]."', form = '".$_POST["form"]."', descrizione ='".addslashes($_POST["descrizione"])."', ordine ='".$_POST["ordine"]."' WHERE id_menu='".$_POST["id_menu"]."'";
			
			$_FRWK["DB"]->exec_sql( $sql );
			break;
			
		case "create":
			$sql  = "INSERT INTO menu_procedura( id_progetto, id_menu_padre, form, descrizione, ordine ) ";
			$sql .= "VALUES( '".$id_progetto."', '".$_POST["id_menu_padre"]."', '".$_POST["form"]."', '".addslashes($_POST["descrizione"])."', '".$_POST["ordine"]."' )";
			
			$_FRWK["DB"]->exec_sql( $sql );
			break;
	}
	
}

echo '<div class="row">';
	echo '<div class="col">';
	echo '<h5>MENU PROCEDURA</h5>';
	
	echo genera_menu( $id_progetto, "gestione_progetto.php?id_progetto=".$id_progetto."&active_tab=menu" );
	echo '</div>';
	
	echo '<div class="col pt-4">';
		if( $id_menu == -1 )
			$voce = $_FRWK["DB"]->get_empty_row( "menu_procedura" );
		else
			$voce = $_FRWK["DB"]->get_row( "menu_procedura", $id_menu );
		
		echo '<form method="post" action="gestione_progetto.php?id_progetto='.$id_progetto.'">';
		echo '<input type="hidden" name="active_tab" value="'.$TAB["id_tab"].'"/>';
		if( $id_menu != -1 )
			echo '<input type="hidden" name="id_menu" value="'.$voce["id_menu"].'"/>';
		echo '<div class="form-row">';
			echo '<div class="form-group col">';
				echo '<label for="descrizione">Voce Menu</label>';
				echo '<input type="text" class="form-control" id="descrizione" name="descrizione" value="'.$voce["descrizione"].'"/>';
			echo '</div>';
		echo '</div>';
		echo '<div class="form-row mb-4 pb-4">';
				echo '<div class="bootstrap-select-wrapper col">';
					echo '<label for="id_menu_padre">Menu Padre</label>';
					echo '<select id="id_menu_padre" name="id_menu_padre">';
					echo '<option value="0">Menu di primo livello...</option>';
					echo $_FRWK["DB"]->create_option( "menu_procedura", "id_menu", "descrizione", "id_menu!='".$id_menu."'", $voce["id_menu_padre"], "ordine, id_menu", "" );
					echo '</select>';
				echo '</div>';
		echo '</div>';
		echo '<div class="form-row">';
			echo '<div class="form-group col">';
				echo '<label for="form">Form Chiamato</label>';
				echo '<input type="text" class="form-control" id="form" name="form" value="'.$voce["form"].'"/>';
			echo '</div>';
		echo '</div>';
		echo '<div class="form-row">';
			echo '<div class="form-group col">';
				echo '<label for="ordine">Ordine</label>';
				echo '<input type="text" class="form-control" id="ordine" name="ordine" value="'.$voce["ordine"].'"/>';
			echo '</div>';
		echo '</div>';
		echo '<div class="form-row col">';
			echo '<button type="submit" class="btn btn-';
			if( $id_menu != -1 )
				echo 'success';
			else
				echo 'primary';
			echo '" name="action" value="';
			if( $id_menu != -1 )
				echo "save";
			else
				echo "create";
			echo '">';
			echo '<i class="fa-solid fa-square-plus mr-2"></i>';
			if( $id_menu != -1 )
				echo 'Aggiorna Voce Menu';
			else
				echo 'Aggiungi Voce Menu';
			echo '</button>';
			echo '<a href="gestione_progetto.php?id_progetto='.$_GET["id_progetto"].'&active_tab=menu" class="btn btn-danger ml-2"><i class="fa-solid fa-eraser mr-2"></i>Pulisci campi</a>';
		echo '</div>';
		echo '</form>';
	echo '</div>';
echo '</div>';
	
	

?>