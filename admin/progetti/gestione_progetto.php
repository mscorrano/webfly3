<?php
	
	$TOOLBOX = array();
	$TOOLBOX[] = array( "icon" 		=> "fa-solid fa-arrow-right-from-bracket",
						"text" 		=> "ESCI",
						"link"		=> "/index.php" );
						
	include( "../../lib/layout/header.php" );
	include( "../../modules/framework/menu.php" );	
	
	$id_progetto = -1;
	if( array_key_exists( "id_progetto", $_GET ))
		$id_progetto = $_GET["id_progetto"];
	
	if( array_key_exists( "id_progetto", $_POST ))
		$id_progetto = $_POST["id_progetto"];
	
	if( $id_progetto == -1 )
		header( "location: progetti.php" );
	else $dati_progetto = $_FRWK["DB"]->get_row( "progetti", $id_progetto );
	
	$TABS = array();
	$TABS[] = array( "id_tab" => "abilitazioni",	"descrizione" => "Abilitazioni",			"content" => "gestione_progetti/abilitazioni.php" );
	$TABS[] = array( "id_tab" => "parametri", 		"descrizione" => "Parametri Procedura",		"content" => "gestione_progetti/parametri.php" );
	$TABS[] = array( "id_tab" => "menu",      		"descrizione" => "Menu",					"content" => "gestione_progetti/menu.php" );
	
	$ACTIVE_TAB = "abilitazioni";
	
	if( array_key_exists( "active_tab", $_SESSION ))
		$ACTIVE_TAB = $_SESSION["active_tab"];
	
	if( array_key_exists( "active_tab", $_GET ))
		$ACTIVE_TAB = $_GET["active_tab"];
	
	if( array_key_exists( "active_tab", $_POST ))
		$ACTIVE_TAB = $_POST["active_tab"];	
	
	$_SESSION["active_tab"] = $ACTIVE_TAB;
	
	echo '<div class="container-fluid bg-white m-4 p-4">';
		echo '<h4 class="bg-success text-white pl-3 pr-3 pt-1 pb-1">PROGETTO : '.$dati_progetto["nome"].'</h4>';
		
		echo '<ul class="nav nav-tabs" id="gestione_progetto" role="tablist">';
			foreach( $TABS as $id => $TAB ) {
				echo '<li class="nav-item"><a class="nav-link';
				if( $TAB["id_tab"] == $ACTIVE_TAB )
					echo ' active';
				echo '" id="'.$TAB["id_tab"].'-header" data-toggle="tab" href="#'.$TAB["id_tab"].'" role="tab" aria-controls="tab1" aria-selected="';
				if( $TAB["id_tab"] == $ACTIVE_TAB )
					echo 'true';
				else
					echo 'false';
				echo '">'.$TAB["descrizione"].'</a></li>';
			}
		echo '</ul>';
		echo '<div class="tab-content" id="contenuto_gestione">';
			foreach( $TABS as $id => $TAB ) {
				echo '<div class="tab-pane p-4 fade';
				if( $TAB["id_tab"] == $ACTIVE_TAB )
					echo ' show active';
				echo '" id="'.$TAB["id_tab"].'" role="tabpanel" aria-labelledby="'.$TAB["id_tab"].'-header">';
				if( $TAB["content"] != "" )
					include( $TAB["content"] );
				echo '</div>';
			}
		echo '</div>';
	echo '</div>';
	include( "../../lib/layout/footer.php" );
?>