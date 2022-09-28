// *************************************************************** //
// WebFLY 3.0 - Client Engine                                      //
// =============================================================== //
// Remote Client COMMAND MANAGER				  				   //
// *************************************************************** //

<?php

	$files = scandir( __DIR__ . "/js" );
	
	foreach( $files as $file ) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if( strtoupper( $ext ) == "JS" ) {
			echo '// *************************************************************** //'.PHP_EOL;
			echo '// FILE : '.str_pad( $file, 56, " ").' //'.PHP_EOL;
			echo '// *************************************************************** //'.PHP_EOL;
			echo file_get_contents( __DIR__ . "/js/".$file );
			echo PHP_EOL .'// **************************** END ****************************** //'.PHP_EOL .PHP_EOL .PHP_EOL;
		}
	}
?>