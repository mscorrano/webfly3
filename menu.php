<?php
	$TOOLBOX = array();
	$TOOLBOX[] = array( "icon" 		=> "fa-solid fa-arrow-right-from-bracket",
						"text" 		=> "ESCI",
						"link"		=> "/index.php" );
						
	include( "lib/layout/header.php" );

	if( is_null( $WEBFLY->PROGETTO ) || !property_exists( $WEBFLY, "PROGETTO" ) )
		header( "location: index.php" );
	
	if( array_key_exists( "m", $_GET )) {
		// CHIAMATA MENU...
		$_SESSION["FRWK_ABILITAZIONE"] = 0;
		
		// VERIFICA L'ABILITAZIONE DAL PROFILO...
		$sql = "SELECT * FROM db_profili_dettagli WHERE id_profilo='".$WEBFLY->UTENTE["id_profilo"]."' AND id_menu='".intval($_GET["m"])."'";
		
		$dettaglio_abilitazione = $_FRWK["DB"]->exec_sql( $sql, true );
		
		if( array_key_exists( "livello_abilitazione", $dettaglio_abilitazione ))
			$livello_abilitazione = $dettaglio_abilitazione["livello_abilitazione"];
		else
			$livello_abilitazione = 0;
		
		if( $livello_abilitazione > 0 ) {
			$_SESSION["FRWK_ABILITAZIONE"] = $livello_abilitazione;
			$WEBFLY->SESSION["FRWK_ABILITAZIONE"] = $livello_abilitazione;
			
			// Chiamata pagina...
			$dati_menu = $_FRWK["DB"]->get_row( "menu_procedura", $_GET["m"] );
			
			if( array_key_exists( "form", $dati_menu ) && $dati_menu["form"] != "" ) {
				$_SESSION["FRWK_FORM_CALL"] = $dati_menu["form"];
				header( "location: page.php" );
				die();
			} else $_SESSION["FRWK_FORM_CALL"] = "";
		} else {
			echo '<div class="alert alert-danger"><h5 class="alert-heading">Funzione non abilitata</h5>Attenzione! L\'utente non &egrave; abilitato alla funzione.<br/>Per accedere alla funzione &egrave; necessario contattare l\'amministratore per ottenere l\'abilitazione necessaria</div>';
		}
	}
	
	echo '<div class="container mt-4 p-4 bg-white">';
	echo '<h3>'.$WEBFLY->PROGETTO["nome"].'</h3>';
	
	include( "modules/framework/menu.php" );
	
	echo genera_menu( $WEBFLY->PROGETTO["id_progetto"] );
	include( "lib/layout/footer.php" );
?>