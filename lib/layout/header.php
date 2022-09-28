<?php
	include( __DIR__."/../../conf/config.php" );
	include( __DIR__."/../../modules/framework/engine.php" ); 
	ob_start();
?>
<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Provincia Pescara - INTRANET LL.PP.">
	<meta name="author" content="">
	<meta name="generator" content="">
	<meta name="robots" content="noindex">

	<title>Provincia Pescara - INTRANET LL.PP.</title>

	<!-- Bootstrap Italia CSS -->
	<link href="/components/bootstrap_italia/css/bootstrap-italia.min.css" rel="stylesheet">

	<!-- Bootstrap Italia custom CSS -->
	<!-- <link href="/css/compiled/bootstrap-italia-custom.min.css" rel="stylesheet"> -->

	<!-- App styles -->
	<link href="/components/bootstrap_italia/css/main.css" rel="stylesheet">
	<link href="/components/provincia/css/portale.css" rel="stylesheet">
	<link href="/components/webfly/css/webfly.css" rel="stylesheet">

	<!-- Favicons -->
	<link rel="apple-touch-icon" href="/components/bootstrap_italia/img/favicons/apple-touch-icon.png">
	<link  rel="icon" href="/components/provincia/favicon-32x32.png">
	<link rel="manifest" href="/components/bootstrap_italia/img/favicons/manifest.webmanifest">
	<link rel="mask-icon" href="/components/bootstrap_italia/img/favicons/safari-pinned-tab.svg" color="#0066CC">
	<meta name="msapplication-config" content="/components/bootstrap_italia/img/favicons/browserconfig.xml">
	<meta name="theme-color" content="#0066CC">
	<link href="/components/fontawesome/css/fontawesome.css" rel="stylesheet">
	<link href="/components/fontawesome/css/brands.css" rel="stylesheet">
	<link href="/components/fontawesome/css/solid.css" rel="stylesheet">
	<!-- window.__PUBLIC_PATH__ points to fonts folder location -->
	<script>window.__PUBLIC_PATH__ = '/components/bootstrap_italia/fonts'</script>
	<script src="/components/bootstrap_italia/js/bootstrap-italia.bundle.min.js"></script>
	<!-- App scripts -->
	<?php
		echo '<script>';
		echo 'var WEBFLY_USER_SESSION_ID = "'.session_id().'"';
		echo '</script>';
	?>
	<!-- DataTables -->
	<link rel="stylesheet" type="text/css" href="/components/DataTables/datatables.min.css"/>
	<script type="text/javascript" src="/components/DataTables/datatables.min.js"></script>	
	
<?php
	if( isset( $WEBFLY ))
		$WEBFLY->init_runtime();
?>
</head>
<body class="background_procedura">
<div class="container-fluid">
	<div class="row bg-provincia">
		<div class="col-2 p-4">
			<a href="https://www.provincia.pescara.it"><img src="/components/img/logo-provincia-pescara-piccolo.png"/></a>
		</div>
		<div class="col-8">
<?php
		include( __DIR__ . "/toolbox.php" );
?>		
		</div>	
		<div class="col-2">
<?php
			// Gestione utente...
			echo '<div class="row">';
			echo '	<div class="col-3 text-white mt-4">';
			echo '		<i class="fa-solid fa-user fa-2xl mr-2 icona_utente ';
			if( array_key_exists( "frwk_session_SESSION_ID", $_SESSION ) )
				echo 'icona_utente_logged';
			else
				echo 'icona_utente_nolog';
			echo '"></i>';
			
			echo '	</div>';
			echo '	<div class="col">';
			echo '		<div class="row">';
			echo '			<div class="col text-white mt-4 font-weight-bold nome_utente">';
			if( array_key_exists( "frwk_session_SESSION_ID", $_SESSION ) ) {
				echo '				'.$WEBFLY->UTENTE["nome_esteso"].'<br/>';
				echo '				<a href="/login.php?logout" style="color:gold">Logout</a>';
			} else {
				echo '				<a href="/login.php" class="text-white">Accedi!</a>';
			}
			echo '			</div>';
			echo '		</div>';
			echo '		<div class="row">';
			echo '			<div class="col text-white mt-0 mb-0">';
			if( array_key_exists( "sess_FRWK_PROFILO", $_SESSION ) )
				echo $_SESSION["'sess_FRWK_PROFILO111"];
			echo '			</div>';
			echo '		</div>';
			echo '	</div>';
			echo '</div>';
?>
		</div>
	</div>
</div>
<div class="col-12 pl-5 pr-5" style="padding-bottom:200px">