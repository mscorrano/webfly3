<?php

/**********************************************************************/
/* Funzione di richiesta invio massivo                                */
/*                                                                    */
/* TIPO INVIO                                                         */
/* 1 = E-Mail                                                         */
/* 2 = PEC                                                            */
/* 3 = Posta Prioritaria                                              */
/* 4 = PEC Firmata                                                    */
/* 5 = Raccomandata                                                   */
/* 6 = SMS                                                            */
/* 7 = Messi Notificatori                                             */
/*                                                                    */
/* ================================================================== */
/* PARAMETRI E' UN ARRAY CHE DEVE CONTENERE I SEGUENTI VALORI         */
/*                                                                    */
/*  id_mittente     => mittente abilitato (tabella mittenti)          */
/*  lotto           => identifica il lotto (testo)                    */
/*  protocollo      => codice identificativo documento (testo)        */
/*  oggetto         => oggetto comunicazione (testo)                  */
/*                                                                    */
/* PARAMETRI OPZIONALI                                                */
/*                                                                    */
/* chiave_esterna_1 => id da utilizzare per la rendicontazione (int)  */
/* chiave_esterna_2 => altro id (int)                                 */
/* chiave_esterna_3 => altro id (testo)                               */
/* files_allegati   => BASE64 del contenuto dei files allegati        */
/* nomi_files       => nomi dei files allegati alla richiesta         */
/* testo            => testo della comunicazione                      */
/*                                                                    */
/*                                                                    */
/* In caso di E-Mail o PEC                                            */
/* ================================================================== */
/* indirizzo => indirizzo destinatario				    			  */
/*                                                                    */
/* In caso di Posta Prioritaria                                       */
/* ================================================================== */
/* allegati         => array di files allegati alla richiesta (base64)*/
/*                                                                    */
/* In caso di Raccomandata                                            */
/* ================================================================== */
/* codice_fiscale                                                     */
/* id_tipo_soggetto => 1 = Persona Fisica, 2 = Persona Giuridica      */
/* cognome                                                            */ 
/* nome                                                               */
/* ragione_sociale                                                    */
/* id_toponimo      => tabella toponimi                               */
/* indirizzo                                                          */
/* civico                                                             */
/* localita                                                           */
/* provincia                                                          */
/* cap                                                                */ 
/* allegati         => file pdf da trasmettere (base64)               */
/**********************************************************************/

abstract class TIPI_INVIO {
    const EMAIL     		 = 1;
    const PEC		         = 2;
    const POSTA_PRIORITARIA  = 3;
    const PEC_FIRMATA     	 = 4;
    const RACCOMANDATA       = 5;
    const SMS      			 = 6;
    const MESSI              = 7;
}

abstract class ESITI_INVIO {
    const OK     		     = 1;
    const MANCA_PARAMETRO	 = 2;
    const MITTENTE_ERRATO	 = 3;
}

class INVIO_MASSIVO {
	public $DB;
	public $ESITO_INVIO;
	public $MESSAGGIO_ERRORE;
	
	private function verifica_connessione() {
		if( !defined( "_SERVER" ) || !array_key_exists( "DOCUMENT_ROOT", $_SERVER ) | $_SERVER["DOCUMENT_ROOT"] == "" )
			$_SERVER["DOCUMENT_ROOT"] = "/var/www/html/";		
			
		if( !function_exists( "connect_db" )) {		
			// Esecuzione senza Apache...
			include( $_SERVER["DOCUMENT_ROOT"]."lib/database/dbmanager.php" );				
		}
		
		include( $_SERVER["DOCUMENT_ROOT"].'conf/config.php' );
		$this->DB = new DB_MANAGER( $_FRWK["PARAMS"]["DATABASE"]["DB_SERVER"], $_FRWK["PARAMS"]["DATABASE"]["USERNAME"], 
									$_FRWK["PARAMS"]["DATABASE"]["PASSWD"], "message_gateway" );
	}
	
	private function presenza_campi_obbligatori( $elenco_campi, $parametri ) {
		$elenco_campi = explode(",",$elenco_campi);
		
		foreach( $elenco_campi as $campo )
			if( !array_key_exists( trim($campo), $parametri )) {
				$this->ESITO_INVIO      = ESITI_INVIO::MANCA_PARAMETRO;
				$this->MESSAGGIO_ERRORE = "Manca parametro ".$campo;
				return false;
			}
			
		return true;
	}

	public function bonifica_indirizzo( $indirizzo ) {
		
		$this->verifica_connessione();
		
		$toponimi = $this->DB->exec_sql( "SELECT * FROM message_gateway.toponimi" );
		
		$id_toponimo          = -1;
		$via                  = "";
		$descrizione_toponimo = "";
		
		foreach( $toponimi as $toponimo ) {
			$posizione = strpos( $indirizzo, $toponimo["toponimo"] );
			
			if( $posizione !== false ) {
				// Trovato...
				$id_toponimo = $toponimo["id_toponimo"];
				$descrizione_toponimo = trim($toponimo["toponimo"]);
				$via = trim(substr( $indirizzo, strlen($toponimo["toponimo"])+1 ));
			}
		}
		
		if( $id_toponimo == -1 ) {
			// Toponimo non trovato...
			// Valore di DEFAULT : VIA => ID #20;
			$id_toponimo = 20;
			$descrizione_toponimo = "VIA";
			$via = $indirizzo;
		}
		
		$civico = "";
		if( strpos( $via, "," ))
			list( $via, $civico ) = explode( ",", $via );
		return array( "id_toponimo" => $id_toponimo, "toponimo" => trim($descrizione_toponimo), "via" => trim($via), "civico" => trim($civico) );
	}
	
	public function richiedi_invio( $tipo_invio, $oggetto, $messaggio="", $parametri=array() ) {
		
		$sql = "";
		$this->verifica_connessione();
				
		// Gestione allegati...
		$PATH_ALLEGATI = "/var/www/JOBS/scripts/allegati/";
		
		if( !is_dir( $PATH_ALLEGATI.date("Y_m") )) {
			mkdir( $PATH_ALLEGATI.date("Y_m") );
			chmod( $PATH_ALLEGATI.date("Y_m"), 0777 );
		}
		$PATH_ALLEGATI = $PATH_ALLEGATI.date("Y_m") ."/";
		
		if( array_key_exists( "files_allegati", $parametri ) && $parametri["files_allegati"] != "" ) {
			// Memorizza gli allegati nel filesystem...
			$PERCORSI = "JOBS_FS";
			
			if( !is_array( $parametri["files_allegati"] )) {
				$allegati = array();
				$allegati[0] = $parametri["files_allegati"];
				
				$nomi_allegati = array();
				$nomi_allegati[0] = $parametri["nomi_files"];
			} else {
				$allegati      = $parametri["files_allegati"];
				$nomi_allegati = $parametri["nomi_files"];
			}
			
			foreach( $allegati as $id => $allegato ) {
				$nome_file = tempnam( $PATH_ALLEGATI, "attach_" );
				file_put_contents( $nome_file, $allegato );
				$PERCORSI .= ",/".date("Y_m")."/".basename($nome_file);
			}
			
			$parametri["files_allegati"] = $PERCORSI;
			$parametri["nomi_files"]     = "JOBS_FS,".$parametri["nomi_files"];
		}
				
		switch( $tipo_invio ) {
			case TIPI_INVIO::EMAIL:
			case TIPI_INVIO::PEC:			
				if( !$this->presenza_campi_obbligatori( "id_mittente,lotto,protocollo,indirizzo_email", $parametri ) ) {
					
					$this->ESITI_INVIO = ESITI_INVIO::MANCA_PARAMETRO;
					$this->MESAGGIO_ERRORE = "Campo obbligatorio mancante (id_mittente,lotto,protocollo,indirizzo_email)";
					return false;
				}
				
				$dati_mittente = $this->DB->get_row( "message_gateway.mittenti", $parametri["id_mittente"] );

				if( !array_key_exists( "id_mittente", $dati_mittente )) {
					$this->ESITI_INVIO = ESITI_INVIO::MITTENTE_ERRATO;
					$this->MESAGGIO_ERRORE = "Mittente #".$parametri["id_mittente"]." non trovato"; 
					return false;
				}
				
				$sql = "INSERT INTO message_gateway.comunicazioni_massive( id_mittente, id_tipo_comunicazione, lotto, protocollo, indirizzo_email, oggetto, testo";
				
				$campi_opzionali = explode( ",", "codice_fiscale,id_tipo_soggetto,ragione_sociale,cognome,nome,files_allegati,nomi_files,chiave_esterna_1,chiave_esterna_2,chiave_esterna_3" );
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", ".$campo;
				$sql .= ") VALUES ('".$parametri["id_mittente"]."', '".$tipo_invio."', '".addslashes(trim($parametri["lotto"]))."',";
				$sql .= "'".addslashes(trim($parametri["protocollo"]))."', '".addslashes(trim($parametri["indirizzo_email"]))."', '".addslashes(trim($oggetto))."',";
				$sql .= "'".addslashes($messaggio)."'";
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", '".addslashes(trim($parametri[$campo]))."'";
				$sql .= ")";
				
				echo $sql;
				break;
			
			case TIPI_INVIO::POSTA_PRIORITARIA:
			case TIPI_INVIO::RACCOMANDATA:
			
				if( !$this->presenza_campi_obbligatori( "id_mittente,lotto,protocollo,files_allegati,nomi_files", $parametri ) ) {
					return false;
				}
				$sql = "INSERT INTO message_gateway.comunicazioni_massive( id_mittente, id_tipo_comunicazione, lotto, protocollo";
				
				$campi_opzionali = explode( ",", "codice_fiscale,id_tipo_soggetto,ragione_sociale,cognome,nome,id_toponimo,indirizzo,indirizzo_presso,civico,localita,cap,provincia,messaggio,files_allegati,nomi_files,chiave_esterna_1,chiave_esterna_2,chiave_esterna_3" );
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", ".$campo;
				$sql .= ") VALUES ('".$parametri["id_mittente"]."', '".$tipo_invio."', '".addslashes(trim($parametri["lotto"]))."',";
				$sql .= "'".addslashes(trim($parametri["protocollo"]))."'";
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", '".addslashes(trim($parametri[$campo]))."'";
				$sql .= ")";
				
				break;
			
			case TIPI_INVIO::SMS:
				if( !$this->presenza_campi_obbligatori( "id_mittente,lotto,protocollo,cellulare", $parametri ) ) {
					return false;
				}	
				$sql = "INSERT INTO message_gateway.comunicazioni_massive( id_mittente, id_tipo_comunicazione, lotto, protocollo, cellulare, testo";
				
				$campi_opzionali = explode( ",", "codice_fiscale,id_tipo_soggetto,ragione_sociale,cognome,nome,id_toponimo,indirizzo,civico,localita,cap,provincia,messaggio,files_allegati,nomi_files,chiave_esterna_1,chiave_esterna_2,chiave_esterna_3" );
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", ".$campo;
				$sql .= ") VALUES ('".$parametri["id_mittente"]."', '".$tipo_invio."', '".addslashes(trim($parametri["lotto"]))."',";
				$sql .= "'".addslashes(trim($parametri["protocollo"]))."',";
				$sql .= "'".addslashes(trim($parametri["cellulare"]))."',";
				$sql .= "'".addslashes(trim($oggetto))."'";
				foreach( $campi_opzionali as $campo )
				if( array_key_exists( $campo, $parametri ) && $parametri[$campo]!="" )
				   $sql .= ", '".addslashes(trim($parametri[$campo]))."'";
				$sql .= ")";				
				break;
		}
		
		if( $sql != "" )
			$this->DB->exec_sql( $sql );
		
		$this->ESITO_INVIO      = ESITI_INVIO::OK;
		$this->MESSAGGIO_ERRORE = "";
		return true;
	}
	
	function invia_email( $indirizzo_email, $oggetto, $messaggio, $id_mittente=1, $protocollo="", $lotto="", $chiave_1="", $chiave_2="", $chiave_3="") {
		if( $lotto == "" )
			$lotto = "Invio Massivo ".date("d-m-Y H:i");
		
		if( $protocollo == "" )
			$protocollo = "MASSIVO_".date("YmdHis");
		
		$PARAMETRI = array();
		$PARAMETRI["id_mittente"]     = $id_mittente;
		$PARAMETRI["lotto"]           = $lotto;
		$PARAMETRI["protocollo"]      = $protocollo;
		$PARAMETRI["indirizzo_email"] = $indirizzo_email;
		$PARAMETRI["chiave_esterna_1"] = $chiave_1;
		$PARAMETRI["chiave_esterna_2"] = $chiave_2;
		$PARAMETRI["chiave_esterna_3"] = $chiave_3;
		return $this->richiedi_invio( TIPI_INVIO::EMAIL, $oggetto, $messaggio, $PARAMETRI );
	}
	
	function invia_pec( $indirizzo_email, $oggetto, $messaggio, $id_mittente=1, $protocollo="", $lotto="", $chiave_1="", $chiave_2="", $chiave_3="") {
		if( $lotto == "" )
			$lotto = "Invio Massivo ".date("d-m-Y H:i");
		
		if( $protocollo == "" )
			$protocollo = "MASSIVO_".date("YmdHis");
		
		$PARAMETRI = array();
		$PARAMETRI["id_mittente"]     = $id_mittente;
		$PARAMETRI["lotto"]           = $lotto;
		$PARAMETRI["protocollo"]      = $protocollo;
		$PARAMETRI["indirizzo_email"] = $indirizzo_email;
		$PARAMETRI["chiave_esterna_1"] = $chiave_1;
		$PARAMETRI["chiave_esterna_2"] = $chiave_2;
		$PARAMETRI["chiave_esterna_3"] = $chiave_3;
		return $this->richiedi_invio( TIPI_INVIO::PEC, $oggetto, $messaggio, $PARAMETRI );
	}
	
	function invia_sms( $destinatario, $messaggio, $id_mittente=1, $protocollo="", $lotto="", $chiave_1="", $chiave_2="", $chiave_3="" ) {
		if( $lotto == "" )
			$lotto = "Invio Massivo ".date("d-m-Y H:i");
		
		if( $protocollo == "" )
			$protocollo = "MASSIVO_".date("YmdHis");
		
		$PARAMETRI = array();
		$PARAMETRI["id_mittente"]      = $id_mittente;
		$PARAMETRI["lotto"]            = $lotto;
		$PARAMETRI["protocollo"]       = $protocollo;
		$PARAMETRI["cellulare"]        = $destinatario;
		$PARAMETRI["chiave_esterna_1"] = $chiave_1;
		$PARAMETRI["chiave_esterna_2"] = $chiave_2;
		$PARAMETRI["chiave_esterna_3"] = $chiave_3;
		
		return $this->richiedi_invio( TIPI_INVIO::SMS, $messaggio, "", $PARAMETRI );		
	}
}