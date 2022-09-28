<?php

function genera_menu( $id_progetto, $script="menu.php", $id_menu_padre=0 ) {
	global $_FRWK;
	
	$buffer = "";
	if( !array_key_exists( "DB", $_FRWK ))
		throw new Exception( "Per la generazione del Menu è necessario inizializzare la connessione al DB-Manager", "99001" );
	
	$sql = "SELECT * FROM menu_procedura WHERE id_progetto='".$id_progetto."' AND id_menu_padre='".$id_menu_padre."' ORDER BY ordine, id_menu";
	
	$voci_menu = $_FRWK["DB"]->exec_sql( $sql );
	
	if( $id_menu_padre == 0 )
		$id_menu = "menu_progetto_".$id_progetto;
	else
		$id_menu = "sottomenu_progetto_".$id_progetto."_".$id_menu_padre;
	
	if( strpos( $script, "?" ) === false )
		$script = $script . "?";
	else {
		if( substr( $script, strlen($script)-1, 1) != "?" )
			$script = $script . "&";
	}
	foreach( $voci_menu as $voce ) {
		$sottomenu = genera_menu( $id_progetto, $script, $voce["id_menu"] );
		
		if( $buffer == "" )
			$buffer = '<div id="'.$id_menu.'" class="collapse-div">';
		
		if( $sottomenu != "" ) {
			$buffer .= '<div class="collapse-header bg-primary" id="etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<button class="text-white font-weight-bold" data-toggle="collapse" data-target="#voce_'.$voce["id_menu"].'" aria-expanded="false" aria-controls="collapse1">';
			$buffer .= '<i class="fa-solid fa-circle-plus mr-2"></i>'.$voce["descrizione"];
			$buffer .= '</button>';
			$buffer .= '</div>';
			$buffer .= '<div id="voce_'.$voce["id_menu"].'" class="collapse" role="region" aria-labelledby="etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<div class="collapse-body">';
			$buffer .= $sottomenu;
			$buffer .= '</div>';
			$buffer .= '</div>';
		} else {
			$buffer .= '<div class="collapse-header border p-3 ';
			if( $voce["id_menu_padre"] != "0" && $voce["id_menu_padre"] != "" )
				$buffer .= 'bg-success';
			else
				$buffer .= 'bg-primary';
			$buffer .= '" id="etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<a href="'.$script.'p='.$id_progetto.'&m='.$voce["id_menu"].'" class="text-white font-weight-bold ml-2">';
			$buffer .= $voce["descrizione"];
			$buffer .= '</a>';
			$buffer .= '</div>';			
		}
	}
	
	if( $buffer != "" )
		$buffer .= '</div>';
	
	return $buffer;	
}

function gestione_profili( $id_progetto, $id_profilo, $id_menu_padre=0 ) {
	global $_FRWK;
	
	$buffer = "";
	if( !array_key_exists( "DB", $_FRWK ))
		throw new Exception( "Per la generazione del Menu è necessario inizializzare la connessione al DB-Manager", "99001" );
	
	$sql = "SELECT * FROM menu_procedura WHERE id_progetto='".$id_progetto."' AND id_menu_padre='".$id_menu_padre."' ORDER BY ordine, id_menu";
	
	$voci_menu = $_FRWK["DB"]->exec_sql( $sql );
	
	if( $id_menu_padre == 0 )
		$id_menu = "menu_progetto_".$id_progetto;
	else
		$id_menu = "sottomenu_progetto_".$id_progetto."_".$id_menu_padre;
	
	foreach( $voci_menu as $voce ) {
		$sottomenu = gestione_profili( $id_progetto, $id_profilo, $voce["id_menu"] );
		
		if( $buffer == "" )
			$buffer = '<div id="profili_'.$id_menu.'" class="collapse-div">';
		
		$buffer .= '<form method="post">';
		$buffer .= '<input type="hidden" name="id_progetto" value="'.$id_progetto.'"/>';
		$buffer .= '<input type="hidden" name="id_menu" value="'.$voce["id_menu"].'"/>';
		$buffer .= '<input type="hidden" name="id_profilo" value="'.$id_profilo.'"/>';
		$buffer .= '<input type="hidden" name="action" value="dettaglio_profilo"/>';
		if( $sottomenu != "" ) {
			$buffer .= '<div class="collapse-header bg-secondary" id="profili_etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<button class="text-white font-weight-bold" data-toggle="collapse" data-target="#profili_voce_'.$voce["id_menu"].'" aria-expanded="true" aria-controls="profili_etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<i class="fa-solid fa-circle-plus mr-2"></i>'.$voce["descrizione"];
			$buffer .= '</button>';
			$buffer .= '</div>';
			$buffer .= '<div id="profili_voce_'.$voce["id_menu"].'" class="collapse show" role="region" aria-labelledby="profili_etichetta_voce_'.$voce["id_menu"].'">';
			$buffer .= '<div class="collapse-body">';
			$buffer .= $sottomenu;
			$buffer .= '</div>';
			$buffer .= '</div>';
		} else {
			$buffer .= '<div class="collapse-header border p-3 bg-secondary';
			$buffer .= '" id="profili_etichetta_voce_'.$voce["id_menu"].'" style="background-color:lightgray!important">';
			$buffer .= '<a>';
			$buffer .= '<div class="font-weight-bold ml-2 mr-4">';
			$buffer .= $voce["descrizione"];
			$buffer .= '</div>';
			$buffer .= '<div class="btn-group" role="group" aria-label="Basic example">';
			
			// Verifica il profilo...
			$sql = "SELECT * FROM db_profili_dettagli WHERE id_menu='".$voce["id_menu"]."' AND id_profilo='".$id_profilo."'";
			
			$dati_profilo = $_FRWK["DB"]->exec_sql( $sql, true );
			if( array_key_exists( "id_dettaglio", $dati_profilo ) )
				$livello = $dati_profilo["livello_abilitazione"];
			else
				$livello = 0;
			
			for( $i=0; $i<=9; $i++ ) {
				
				$buffer .= '  <button type="submit" class="btn ';
				if( $i == $livello )
					$buffer .= 'btn-primary';
				else
					$buffer .= 'btn-secondary';
				$buffer .= ' pt-1 pb-1" name="profilo" value="'.$voce["id_menu"].'_'.$i.'">'.$i.'</button>';
			}
			$buffer .= '</div>';
			$buffer .= '</a>';
			$buffer .= '</div>';
		}
		$buffer .= '</form>';
	}
	
	if( $buffer != "" )
		$buffer .= '</div>';
	
	return $buffer;	
}