<?php

// ACCESSO BANCA DATI TINN
if( !isset($_SERVER["DOCUMENT_ROOT"] ) || $_SERVER["DOCUMENT_ROOT"] == "" )
	$_SERVER["DOCUMENT_ROOT"] = "/var/www/html";

include( $_SERVER["DOCUMENT_ROOT"]."/utility/lib/tinn.php" );
include( $_SERVER["DOCUMENT_ROOT"]."/lib/recapiti/interfaccia.php" );

$tinn_connection = new TINN( "DBEUROCF.IDB" );
$tinn_protocollo = new TINN( "AFGIB.FDB" );

$MAILER = new INVIO_MASSIVO;

$elenco_uffici = $tinn_connection->exec_sql( "SELECT * FROM FATTPA_CUFIPA_ENTE" );

$INVIO = "";

foreach( $elenco_uffici as $ufficio ) {
	
	$profili = $tinn_connection->exec_sql( "SELECT * FROM FATTPA_CUFIPA_PROFILO WHERE COD_UFFICIO_IPA='".$ufficio["COD_UFFICIO_IPA"]."'" );
	
	$INVIO .= '<h4><a href="http://10.0.117.253/utility/monitoraggio_fatture/monitoraggio.php?codice='.$ufficio["COD_UFFICIO_IPA"].'">'.$ufficio["DESCR_UFFICIO_IPA"].' ('.$ufficio["COD_UFFICIO_IPA"].')</a></h4>';
	$INVIO .= '<ul>';
	foreach( $profili as $profilo ) {
		$soggetti = $tinn_protocollo->exec_sql( "SELECT * FROM PRSOGGET WHERE NOME_LOGIN='".$profilo["PROFILO"]."'" );
		foreach( $soggetti as $soggetto ) {
			if( array_key_exists( "EMAIL", $soggetto ) && $soggetto["EMAIL"] != "" ) {
				$INVIO .= '<li>'.$soggetto["EMAIL"].'</li>';
				
				$oggetto = "MONITORAGGIO FATTURE E TEMPESTIVITA' PAGAMENTI DEL ".date("d-m-Y");
				$testo   = "Si trasmette il monitoraggio relativo alla tempestivita' dei pagamenti".PHP_EOL;
				$testo  .= "ed alle fatture non pagate per il settore ".$ufficio["DESCR_UFFICIO_IPA"].PHP_EOL;
				$testo  .= 'Apri il file con il link seguente'.PHP_EOL;
				$testo  .= '<strong><a href="http://10.0.117.253/utility/monitoraggio_fatture/monitoraggio.php?codice='.$ufficio["COD_UFFICIO_IPA"].'">MONITORAGGIO '.$ufficio["DESCR_UFFICIO_IPA"].' ('.$ufficio["COD_UFFICIO_IPA"].')</a></strong>'.PHP_EOL;

				$MAILER->invia_email( $soggetto["EMAIL"], $oggetto, $testo, 5, "", "MONITORAGGIO_".date("YmdHis"), $ufficio["COD_UFFICIO_IPA"], "MONITORAGGIO_".$ufficio["COD_UFFICIO_IPA"]."_".date("Y_m_d") );
			}
		}
	}
	$INVIO .= '</ul>';
}

$admins = array( "marco.scorrano@comune.montesilvano.pe.it", "angela.erspamer@gmail.com", "valentinadifelice79@gmail.com" );

$oggetto = "MONITORAGGIO FATTURE E TEMPESTIVITA' PAGAMENTI DEL ".date("d-m-Y");
$testo   = "Il monitoraggio relativo alla tempestivita' dei pagamenti scaricabile con questo link : ";
$testo  .= '<strong><a href="http://10.0.117.253/utility/monitoraggio_fatture/monitoraggio.php">MONITORAGGIO COMPLESSIVO ENTE</a></strong>'.PHP_EOL.PHP_EOL;
$testo  .= "E' stata inviata una copia, per singolo ufficio, ai seguenti indirizzi:";
$testo  .= $INVIO;
foreach( $admins as $admin ) {

	$MAILER->invia_email( $admin, $oggetto, $testo, 5, "", "MONITORAGGIO_ADMIN_".date("YmdHis"), "MONITORAGGIO_ENTE", "MONITORAGGIO_ENTE_".date("Y_m_d") );
}