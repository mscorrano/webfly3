<?php

include( "../message_gateway/lib/interfaccia.php" );

$MAILER = new INVIO_MASSIVO();

$esito = $MAILER->invia_email( "marco.scorrano@dsolutions.it", "Prova", "Messaggio di prova", 1, 25 );

if( $esito === false ) {
	echo $MAILER->MESAGGIO_ERRORE;
} else echo "OK";
?>