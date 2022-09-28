<?php

include( "tinn.php" );

$tinn = new TINN( "DBEUROCF.IDB" );

$elenco_uffici = $tinn->exec_sql( "SELECT * FROM FATTPA_CUFIPA_ENTE" );

echo '<pre>';
print_r($elenco_uffici);
echo '</pre>';