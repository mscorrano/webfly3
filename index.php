<?php

	include( "lib/layout/header.php" );

	// MENU PRINCIPALE...
	
	echo '<div class="col-12 bg-white mt-5 p-4">';
	echo '<h4 class="mb-4 webfly-title">PROCEDURE ABILITATE</h4>';

	$sql = "SELECT * FROM progetti INNER JOIN db_stati_progetti ON progetti.id_stato=db_stati_progetti.id_stato INNER JOIN utenti_abilitazioni ON utenti_abilitazioni.id_progetto = progetti.id_progetto WHERE id_utente='".$WEBFLY->UTENTE["id_utente"]."'";
	if( $WEBFLY->UTENTE["admin"] != 1 ) 
		$sql .= " AND visibile_utenti=1";
	
	$procedure = $_FRWK["DB"]->exec_sql( $sql );
	
	$primo  = true;
	$column = 0;
	foreach( $procedure as $procedura ) {
		if( $primo || $column == 10 ) {
			if( !$primo )
				echo '</div>';
			echo '<div class="row">';
			$column = 1;
			$primo = false;
		}
		
		echo '<div class="col text-center">';
		echo '<a href="menu.php?p='.$procedura["id_progetto"].'" class="webfly-menu"><h5>';
		if( $procedura["icona"]!="" )
			echo '<i class="'.$procedura["icona"].'"></i><br/>';
		echo $procedura["nome"];
		echo '</h5></a>';
		echo '</div>';
		$column++;
	}
	
	if( $primo ) {
		echo '<em>Nessuna procedura abilitata...</em>';
	} else {
		for( $i=$column++; $i<10; $i++ )
			echo '<div class="col"></div>';
		echo '</div>';
	}
	
	if( $WEBFLY->UTENTE["admin"] == 1 ) {
		echo '<br/>';
		echo '<hr style="margin-top:100px"/>';
		echo '<h4 class="text-danger">FUNZIONI DI AMMINISTRAZIONE</h4>';
		echo '<div class="row">';
			echo '<div class="col-auto">';
				echo '<a href="admin/utenti/utenti.php" class="btn btn-success">';
				echo '<i class="fa-solid fa-users fa-2xl m-3 d-block"></i>';
				echo 'Gestione Utenti';
				echo '</a>';
			echo '</div>';
			echo '<div class="col">';
				echo '<a href="admin/progetti/progetti.php" class="btn btn-primary">';
				echo '<i class="fa-solid fa-diagram-project fa-2xl m-3 d-block"></i>';
				echo 'Gestione Progetti';
				echo '</a>';
			echo '</div>';
			
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
	include( "lib/layout/footer.php" );
?>
	
	
	