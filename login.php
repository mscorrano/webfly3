<?php

if( array_key_exists("logout", $_GET )) { 
	if( session_status() != PHP_SESSION_ACTIVE ) {
		session_start();
	}
	session_destroy();
	header("location:index.php");
	die();
}

	$NOLOGIN = true;
	include( "lib/layout/header.php" );

$MODAL_DIVS = '
<div id="recupera_password" class="modal fade hide" tabindex="-1" aria-labelledby="header_recupero_password" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
     <div class="modal-header">
        <h5 id="header_recupero_password">Recupera Password</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
     </div>
  <form method="post" action="login.php">	 
  <div class="modal-body">
       <input type="hidden" name="action" value="recover" />
       <label for="email_rec">Inserire l\'indirizzo e-mail fornito in fase di registrazione:</label>
       <input class="form-control" name="email_rec" id="email_rec" type="text" placeholder="Indirizzo e-mail..." />

  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Chiudi</button>
    <button class="btn btn-primary" type="submit">Recupera password</button>
  </div>
  </form>
  </div>
  </div>
</div>
<div id="cambia_password" class="modal fade hide" tabindex="-1" aria-labelledby="header_recupero_password" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
  <div class="modal-header">
    <h5 id="header_recupero_password">Cambia Password</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <form method="post" id="form_cambia" action="login.php">
  <div class="modal-body">
       <input type="hidden" name="action" value="change_password" />
       <div id="blocco_username" class="control-group" style="margin-bottom:10px">
          <label class="control-label" for="frwk_change_username">Username :</label>
          <div class="controls">
             <input class="form-control" id="frwk_change_username" name="frwk_username" type="text" placeholder="Username..." />
          </div>
       </div>
       <div id="blocco_old_password" class="control-group" style="margin-bottom:10px">
          <label class="control-label" for="frwk_change_password">Vecchia Password :</label>
          <div class="controls">
             <input class="form-control" id="frwk_change_password" name="frwk_password" type="password" placeholder="Vecchia Password..." />
          </div>
       </div>
       <div id="blocco_password" class="control-group" style="margin-bottom:10px">
          <label class="control-label" for="frwk_new_password">Password :</label>
          <div class="controls">
             <input class="form-control" id="frwk_new_password" name="password" type="password" placeholder="Nuova Password..." />
          </div>
       </div>
       <div id="blocco_verifica_password" class="control-group" style="margin-bottom:10px">
          <label class="control-label" for="repeat_password">Ripeti password :</label>
          <div class="controls">
             <input class="form-control" id="repeat_password" name="repeat_password" type="password" placeholder="Ripeti Password..." />
          </div>
       </div>
  </div>
  <div class="modal-footer">
    <span id="messaggio" style="color:#b94a48; float:left; font-weight:bold"></span>
    <button type="button" class="btn btn-danger"  data-bs-dismiss="modal" aria-hidden="true">Chiudi</button>
    <button type="button" class="btn btn-primary" onclick="cambia_password()">Cambia password</button>
  </div>
  </div>
  </div>
  </form>
</div>';
ob_start();

$NO_MENU = true;
$BODY_DARK = true;
	
if( array_key_exists("action", $_POST ) &&
    $_POST["action"]=="recover" ) {

      $TROVATO = false;

      $sql = "SELECT * FROM utenti WHERE attivo=1 AND email='".$_POST["email_rec"]."'";

      $rs = $_FRWK["DB"]->exec_sql( $sql );
      foreach( $rs as $dati_utente ) {
         $TROVATO = true;

         $nuova_password = genera_password();

         $sql = "UPDATE utenti SET pwd='".hash("sha256", $nuova_password)."' WHERE email='".$_POST["email_rec"]."'";
         $_FRWK["DB"]->exec_sql( $sql );

         invia_email( $_POST["email_rec"], "Recupero Password WebFLY", "La nuova password di accesso al tuo account (username: ".$dati_utente["username"].") sulla procedura ".$dati_procedura["descrizione"]." è: ".$nuova_password );
      }

      if( !$TROVATO ) {
         echo "<div class='alert alert-danger'>Utente non trovato!</div>";
      } else {
         echo "<div class='alert alert-success' style='margin-left:20px; padding-left:20px'>Password rigenerata. Riceverai a breve una e-mail con la nuova password.</div>";
      }
}

if( array_key_exists("frwk_username", $_POST) &&
    array_key_exists("frwk_password", $_POST) ) {

	if( array_key_exists( "LDAP", $_FRWK ) && $_FRWK["LDAP"] == true ) {
		// LDAP LOGIN
		$LDAP_LOGIN = true;
		include( __DIR__."/modules/users/auth.php" );
		
	} else {
		// LOGIN TRADIZIONALE
		$LDAP_LOGIN = false;
		$sql = "SELECT *
			   FROM  utenti
			   WHERE attivo=1 AND pwd='".hash('sha256', $_POST["frwk_password"])."'";
			  
		$utenti = $_FRWK["DB"]->exec_sql( $sql );

		$SUCCESS = false;
		foreach( $utenti as $utente ) {
		  if( $utente["username"]==$_POST["frwk_username"] ) {
			 $SUCCESS = true;
			 $dati_utente = $utente;
			 break;
		  }   
		}
	}
   if( !$SUCCESS ) {
	   if( !$LDAP_LOGIN )
		   echo "<div class='alert alert-danger mt-2 bg-white'>Nome utente e/o password errata.</div>";
   } else {
      echo "<div class='alert alert-success mt-4' style='padding-left:20px'>Login eseguito con successo. Accesso alla pagina iniziale in corso...</div>";

      // CREO I DATI DI SESSIONE
      
		if( session_status() == PHP_SESSION_DISABLED   )
			die("WEBFLY RICHIEDE LA GESTIONE DELLE SESSIONI");

		if( session_status() != PHP_SESSION_ACTIVE )
			session_start();
		
		$_SESSION["frwk_session_SESSION_ID"]   = md5(microtime().$_SERVER["REMOTE_ADDR"]);
		$_SESSION["frwk_session_USER_ID"]      = $dati_utente["id_utente"];
		$_SESSION["frwk_session_USER_NOME"]    = $dati_utente["nome_esteso"];
		$_SESSION["frwk_session_EMAIL"]        = $dati_utente["email"];
      
		if( array_key_exists("action", $_POST) && $_POST["action"]=="change_password" ) {
			$sql = "UPDATE utenti SET pwd='".hash("sha256", $_POST["repeat_password"])."' WHERE id_utente='".$dati_utente["id_utente"]."'";

			$_FRWK["DB"]->exec_sql( $sql );

			$MSG_UTENTE = "PASSWD";

			header( "location: index.php?msg=pwd" );  
		} else header( "location: index.php" );
   }

   if( array_key_exists("msg", $_GET) ) {
      switch( $_GET["msg"]) {
         case 1:
            echo "<div class='success alert' style='margin-left:0px'>Logout eseguito correttamente.</div>";
            break;
      }
   }
}
?>
<form method="post">
<div class="container-fluid pt-3">
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-6 col-xl-4">
		<div class="card shadow-2-strong" style="border-radius: 1rem;">
			<div class="card-body pl-5 pr-5 pb-2">

				<h3 class="mb-3">Gestionale Settore Tecnico</h3>

				<div class="form-outline mb-4">
					<label class="form-label fw-bold" for="frwk_username" style="margin-left: 0px;">UTENTE</label>
<?php				echo '
					<input type="text" id="frwk_username" name="frwk_username" class="form-control form-control-lg" required';
					if( array_key_exists( "frwk_username", $_POST ))
						echo ' value="'.$_POST["frwk_username"].'"';
					echo '>';
?>
				</div>

				<div class="form-outline mb-4">
					<label class="form-label fw-bold" for="frwk_password" style="margin-left: 0px;">PASSWORD</label>
					<input type="password" id="frwk_password" name="frwk_password" class="form-control form-control-lg" required>
				</div>
				<div class="d-grid gap-2">
					<button class="btn btn-primary btn-block" type="submit">Accedi...</button>
<?php
					if( !array_key_exists( "LDAP", $_FRWK ) || $_FRWK["LDAP"] == false ) {
						echo '<a class="btn btn-warning btn-block" data-bs-toggle="modal" href="#recupera_password">Recupera password...</a>';
						echo '<a class="btn btn-success btn-block" data-bs-toggle="modal" href="#cambia_password">Cambia password...</a>';
					}
					if( !isset( $LAYOUT_PARMS ) || array_key_exists( "EXIT_URL", $LAYOUT_PARMS ) )
						$LAYOUT_PARMS["EXIT_URL"] = "https://www.google.it";
					
					echo '
					<a class="btn btn-danger btn-block" href="'.$LAYOUT_PARMS["EXIT_URL"].'">Esci...</a>'.PHP_EOL;
?>
				</div>
            </div>
          </div>
        </div>
      </div>
</div>
</div>
</form>
<script>
function cambia_password() {

  if( $('#repeat_password').val() != $('#password').val() ) {
     $('#blocco_verifica_password').addClass('error');
     $('#blocco_repeat_password').addClass('error');

     $('#messaggio').html( "Le due password non coincidono" );
  } else
     $('#form_cambia').submit();

}
</script>
<?php
	include( "lib/layout/footer.php" );
?>