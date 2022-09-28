<?php
	$TOOLBOX = array();
	$TOOLBOX[] = array( "icon" 		=> "fa-solid fa-arrow-right-from-bracket",
						"text" 		=> "ESCI",
						"link"		=> "/menu.php" );
						
	include( "lib/layout/header.php" );
	
	$WEBFLY->engine_start();
	
	include( "lib/layout/footer.php" );
?>