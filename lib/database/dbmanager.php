<?php
/****************************************************/
/* DB MANAGEMENT FUNCTIONS - Ver 5.0                */
/*-----------------------------------------         */
/* DIGITAL SOLUTIONS (C) 2021                       */
/****************************************************/
if( !function_exists( "inverti_data" )) {
	include( __DIR__."/database.php" );
}

class DB_MANAGER {
	private $DB_SERVER;
	private $DB_USERNAME;
	private $DB_PASSWORD;
	private $DB_DATABASE;
	private $FRWK_DB_LINK;
	private $FRWK_DEBUG;
	
	// ******************************************************************** //
	// | FUNZIONI MEMBRO PUBBLICHE                                        | //
	// ******************************************************************** //
	
	function connect_db( $force = false ) {

	   if( gettype($this->FRWK_DB_LINK)=="object" && !$force )
		  return;

	   try {
		  $this->FRWK_DB_LINK = new PDO('mysql:host='.$this->DB_SERVER.';dbname='.$this->DB_DATABASE, $this->DB_USERNAME, $this->DB_PASSWORD );
		  $this->FRWK_DB_LINK->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $this->FRWK_DB_LINK->exec("set names utf8");
		  
	   } catch(PDOException $e) {
		  die("Errore durante la connessione al database!: ". $e->getMessage());
	   }
	}	

	function close_connection() {
	   global $FRWK_DB_LINK;

	   if( gettype($FRWK_DB_LINK)!="object" )
		  return;

	   unset( $FRWK_DB_LINK );  

	}
	
	// ******************************************************************** //
	// | COSTRUTTORI                                                      | //
	// ******************************************************************** //	
	
	function __construct( $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DATABASE ) {
		
		$this->FRWK_DEBUG  = false;
		$this->DB_SERVER   = $DB_SERVER;
		$this->DB_USERNAME = $DB_USERNAME;
		$this->DB_PASSWORD = $DB_PASSWORD;
		$this->DB_DATABASE = $DATABASE;
		
		$this->connect_db();
		
	}
	
	// ******************************************************************** //
	// | FUNZIONI MEMBRO PUBBLICHE                                        | //
	// ******************************************************************** //

	public function DEBUG(){
		$this->FRWK_DEBUG = !$this->FRWK_DEBUG;
	}
	
	// ******************************************************************** //
	// | Funzione membro  : get_param                                     | //
	// | Parametri        : $nome_parametro  = Nome parametro   		  | //
	// |                    $default         = Valore di default          | //
	// |                                                                  | //
	// | Descrizione      : Recupera i valori dal database dei parametri  | //
	// |					di default si aspetta una tabella parametri   | // 
	// |                    con il campo "nome" ed il campo "valore"      | //
	// | Parametri opzionali											  | //
	// |                        										  | //
    // | $tabella                = nome tabella contenente i parametri    | //
    // | $campo_nome_parametro   = campo contenente il nome del parametro | //
    // | $campo_valore_parametro = campo contenente il valore             | //	
	// ******************************************************************** //
	function get_param( $nome_parametro, $default="", $tabella="parametri", $campo_nome_parametro="nome", $campo_valore_parametro="valore" ) {
		
		$presente = exec_sql( "SHOW TABLES LIKE '".$tabella."';" );
		
		if( count( $presente ) > 0 ) {
			
			// TABELLA PRESENTE...
			$dati_parametro = exec_sql( "SELECT * FROM ".$tabella." WHERE ".$campo_nome_parametro."='".addslashes( $nome_parametro )."'", true );
			if( array_key_exists( $campo_valore_parametro, $dati_parametro ) && $dati_parametro[$campo_valore_parametro]!="" )
				return $dati_parametro[$campo_valore_parametro];
		}
		
		return $default;
	}


	// ******************************************************************** //
	// | Funzione membro  : get_param                                     | //
	// | Parametri        : $nome_parametro  = Nome parametro   		  | //
	// |                    $valore          = Valore parametro           | //
	// |                                                                  | //
	// | Descrizione      : Recupera i valori dal database dei parametri  | //
	// |					di default si aspetta una tabella parametri   | // 
	// |                    con il campo "nome" ed il campo "valore"      | //
	// | Parametri opzionali											  | //
	// |                        										  | //
    // | $tabella                = nome tabella contenente i parametri    | //
    // | $campo_nome_parametro   = campo contenente il nome del parametro | //
    // | $campo_valore_parametro = campo contenente il valore             | //	
	// ******************************************************************** //
	function set_param( $nome_parametro, $valore, $tabella="parametri", $campo_nome_parametro="nome", $campo_valore_parametro="valore" ) {
		
		$presente = exec_sql( "SHOW TABLES LIKE '".$tabella."';" );
		
		if( count( $presente ) > 0 ) {
			
			// TABELLA PRESENTE...
			$dati_parametro = exec_sql( "SELECT * FROM ".$tabella." WHERE ".$campo_nome_parametro."='".addslashes( $nome_parametro )."'", true );
			if( array_key_exists( $campo_valore_parametro, $dati_parametro )) {
				exec_sql( "UPDATE ".$tabella." SET ".$campo_valore_parametro."='".addslashes($valore)."' WHERE ".$campo_nome_parametro."='".addslashes($nome_parametro)."'" );
				return true;
			} else {
				exec_sql( "INSERT IGNORE INTO ".$tabella."(".$campo_nome_parametro.", ".$campo_valore_parametro.") VALUES ('".addslashes($nome_parametro)."', '".addslashes($valore)."')" );
				return true;
			}
		}
		
		return "";
	}
	
	
	// ******************************************************************** //
	// | Funzione membro  : exec_sql                                    | //
	// | Parametri        : $query      = Nome frase (stringa)  		  | //
	// |                    $parametri  = Parametri SQL                   | //
	// |                                                                  | //
	// | Descrizione      : Recupera i valori dal database partendo dalla | //
	// |                    query registrata e dai relativi parametri     | //
	// |                                                                  | //
    // | Struttura parametri                                              | //
    // | ================================================================ | //
    // | Elenco Campi													  | //
    // | [<nome_database>].<nome_tabella>.nome	                          | //
	// |                                                                  | //
    // | E' possibile richiedere campi di diverse tabelle o database      | //
	// | purchè raggiungibili tramite la struttura delle Foreign Keys     | //
	// | L'insieme dei campi deve essere separato da virgole              | //
	// |                                                                  | //
    // | Filtro                                                           | //
	// | <porzione di sql relativo alla Where                             | //  
	// ******************************************************************** //
	public function exec_sql( $sql, $fetch_row=false, $parametri = array(), $style=PDO::FETCH_ASSOC, $dont_stop = false, $insert_ignore = false ) {
	   
	   if( $this->FRWK_DEBUG == true ) {
		  echo '<div class="alert alert-warning">'.$sql.'</div>';
	   } 
	   
	   if( gettype($this->FRWK_DB_LINK)!="object" ) {
		  $trace = debug_backtrace();
		  die('['.str_replace($_SERVER["DOCUMENT_ROOT"],"/", $trace[0]["file"]).":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore SQL. Connessione non effettuata ');
	   }
			   
	   try {
		  
		  if( $insert_ignore )
			 $sql = str_replace( "INSERT", "INSERT IGNORE", $sql );
		  
		  $sql = trim($sql);
		  
		  $frase = $this->FRWK_DB_LINK->prepare( $sql );
		  $frase->execute( $parametri );
		  
		  $fetch_db_data = false;
		  if( strpos( strtoupper($sql), "SELECT " ) !== false && strpos( strtoupper($sql), "SELECT " )==0 )
			 $fetch_db_data = true;
				
		  if( strpos( strtoupper($sql), "SHOW " ) !== false && strpos( strtoupper($sql), "SHOW " ) == 0 )
			 $fetch_db_data = true;
			 
		  if( $fetch_db_data ) { 
			 $rs = $frase->fetchAll($style);
		 
			 if( $this->FRWK_DEBUG == true ) {
				echo '<pre>';
				print_r( $rs );
				echo '</pre>';
			 }        
			 if( count($rs)>=1 && $fetch_row==true )
				return $rs[0];
			 else
				return $rs;   
		  }
		  
		  if( strpos( strtoupper($sql), "INSERT" ) !== false ) { 
			 return $this->FRWK_DB_LINK->lastInsertId(); 
		   } else $frase->rowCount();    

	   } catch(PDOException $e) {
		  $trace = debug_backtrace();
		  if( strpos( $e->getMessage(), "1062" )) {
			 echo '<div class="alert alert-danger">';
			 echo 'RECORD DUPLICATO (SQL: '.$sql.')';
			 echo '</div>';
			 return false;
		  }   
	   
		  echo '<div class="alert alert-danger"><small>['.str_replace($_SERVER["DOCUMENT_ROOT"],"/", $trace[0]["file"]).":".$trace[0]["function"].'#'.$trace[0]["line"].']</small><br/>Errore SQL : '. $e->getMessage() .'.<br/><strong>FRASE:</strong><br/>'.$sql.'</div>';
		  if( !$dont_stop )
			 die();
		  else return false;
	   }  
	   
	   return true;
	}
	
	
	// ******************************************************************** //
	// | Funzione membro  : update                                        | //
	// | Parametri        : $table       = Nome tabella    		          | //
	// |                    $chiavi      = Campi chiave                   | //
	// |                    $valori      = Campi da aggiornare            | //
	// |                    $flag_insert = True per inserire se record    | //
	// |                                   non presente                   | //
	// |					$force_where = WHERE passata come parametro   | //
	// |                                                                  | //
	// | Descrizione      : Costruisce una UPDATE IF EXISTS OR INSERT     | //
	// |                                                                  | //
    // | Struttura parametri                                              | //
    // | ================================================================ | //
    // | Elenco Campi													  | //
    // | Array( <nome_campo> => <valore> )  	                          | //
	// |                                                                  | //
    // | E' possibile richiedere campi di diverse tabelle o database      | //
	// | purchè raggiungibili tramite la struttura delle Foreign Keys     | //
	// | L'insieme dei campi deve essere separato da virgole              | //
	// |                                                                  | //
    // | Filtro                                                           | //
	// | <porzione di sql relativo alla Where                             | //  
	// ******************************************************************** //
	public function update( $table, $valori, $flag_insert = false, $chiavi = array(), $force_where = "" ) {
		
		$sql        = "UPDATE ".$table." SET ";
		$sql_insert = "INSERT INTO ".$table. "(";
		$sql_values = ") VALUES (";
		
		if( $force_where != "" ) {
			$WHERE = $force_where;
		} else {
			$WHERE = "";
			if( !is_array( $chiavi ))
				throw new Exception( "Il Parametro $chiavi deve contenere un array (0x80010) o deve essere lasciato vuoto per abilitare la ricerca automatica della chiave primaria", "80010" );
			
			if( count( $chiavi ) > 0 ) {
				foreach( $chiavi as $nome => $valore ) {
					if( $WHERE != "" ) {
						$WHERE .= " AND ";
						$sql_insert .= ", ";
						$sql_values .= ", ";
					}
					$WHERE .= $nome ."='". addslashes($valore) ."'";
					
					$sql_insert .= $nome;
					$sql_values .= "'". addslashes($valore) ."'";
				}
			} else {
				// Primary Key...
				$sql_chiavi = "SHOW KEYS FROM ".$table." WHERE Key_name='PRIMARY'";
				
				$elenco_chiavi = $this->exec_sql( $sql_chiavi );
				foreach( $elenco_chiavi as $chiave ) {
					if( array_key_exists( $chiave["Column_name"], $valori )) {
						if( $WHERE != "" ) {
							$WHERE .= " AND ";
							//$sql_insert .= ", ";
							//$sql_values .= ", ";
						}
						$WHERE .= $chiave["Column_name"] ."='". addslashes($valori[$chiave["Column_name"]]) ."'";
						
						//$sql_insert .= $chiave["Column_name"];
						//$sql_values .= "'". addslashes($valori[$chiave["Column_name"]]) ."'";						
					}						
				}
			}
			
			if( $WHERE == "" && !$flag_insert )
				throw new Exception( "Non è stato possibile individuare un insieme di campi chiavi per la tabella ".$table." (0x80012)", "80012" );
		}
		
		if( !is_array( $valori )) 
			throw new Exception( "Il Parametro valori deve contenere un array (0x80010)", "80010" );
		
		$elenco_campi = $this->exec_sql( "SHOW FIELDS FROM ".$table );
		
		$VALORI_TROVATI = false;
		$numero_campi   = 0;
		foreach( $valori as $nome_campo => $valore ) {
			$PRESENTE = false;
			foreach( $elenco_campi as $campo ) { 
				if( $campo["Field"] == $nome_campo ) {
					// Verifica se non si tratta di un campo chiave...
					$CHIAVE = false;
					foreach( $elenco_chiavi as $chiave ) {
						if( $chiave["Column_name"] == $nome_campo ) {
							$CHIAVE = true;
						}
					}
					
					if( !$CHIAVE ) {
						$PRESENTE = true;
						$VALORI_TROVATI = true;
					}
					
					if( substr($campo["Type"],0,3) == "int" ) {
						if( $valore == "" )
							$valore = 0;
					}
					break;
				}
			}
			
			if( $PRESENTE ) {
				$numero_campi++;
				
				if( $numero_campi>1 ){
					$sql        .= ", ";
				}
				
				if( $sql_values != ") VALUES (" ) {
					$sql_insert .= ", ";
					$sql_values .= ", ";
				}
				$sql_insert .= $nome_campo;
				
				if( is_date( $valore )) {
					$data = new DateTime( $valore );
					$sql .= $nome_campo ."='".$data->Format("Y-m-h H:i:s")."'";
					$sql_values .= "'".$data->Format("Y-m-h H:i:s")."'";
				} else {
					$sql .= $nome_campo ."='".addslashes( $valore )."'";
					$sql_values .= "'".addslashes( $valore )."'";
				}
			}
		}
		
		if( !$VALORI_TROVATI ) {
			throw new Exception( "Non è stato possibile individuare un insieme di campi per la tabella ".$table." tra i dati da aggiornare (0x80912)", "80912" );
		}
		
		if( $WHERE != "" ) {
			$sql = $sql . " WHERE ". $WHERE;
			
			$sql_search = "SELECT COUNT(*) as RECORDS FROM ".$table." WHERE ".$WHERE;
			
			$esito = $this->exec_sql( $sql_search, true );
	
			if( array_key_exists( "RECORDS", $esito ) && $esito["RECORDS"] > 0 ) {
				// UPDATE
				
				return $this->exec_sql( $sql );
			} else {
				if( $flag_insert ) {
					// INSERT...
					$insert = $sql_insert . $sql_values . ")";

					return $this->exec_sql( $insert );
				}
			}
		} else {
			$insert = $sql_insert . $sql_values . ")";
			
			return $this->exec_sql( $insert );
		}
	}
	
	// ******************************************************************** //
	// | Funzione membro  : insert                                        | //
	// | Parametri        : $table       = Nome tabella    		          | //
	// |                    $chiavi      = Campi chiave                   | //
	// |                    $valori      = Campi da aggiornare            | //
	// |                    $flag_insert = True per inserire se record    | //
	// |                                   non presente                   | //
	// |					$force_where = WHERE passata come parametro   | //
	// |                                                                  | //
	// | Descrizione      : Costruisce una INSERT IF NOT EXISTS OR INSERT | //
	// |                                                                  | //
    // | Struttura parametri                                              | //
    // | ================================================================ | //
    // | Elenco Campi													  | //
    // | Array( <nome_campo> => <valore> )  	                          | //
	// |                                                                  | //
    // | E' possibile richiedere campi di diverse tabelle o database      | //
	// | purchè raggiungibili tramite la struttura delle Foreign Keys     | //
	// | L'insieme dei campi deve essere separato da virgole              | //
	// |                                                                  | //
    // | Filtro                                                           | //
	// | <porzione di sql relativo alla Where                             | //  
	// ******************************************************************** //	
	public function insert( $table, $valori, $chiavi = array(), $force_where = "" ) {
		$this->update( $table, $valori, true, $chiavi, $force_where );
	}
	
	
	// ******************************************************************** //
	// | Funzione membro  : gey_keys                                      | //
	// | Parametri        : $table       = Nome tabella    		          | //
	// |                                                                  | //
	// | Descrizione      : Restituisce i campi che costituiscono la      | //
	// |                    chiave primaria                               | //
	// ******************************************************************** //		
	public function get_keys( $tabella ) {
		$sql_chiavi = "SHOW KEYS FROM ".$tabella." WHERE Key_name='PRIMARY'";
		
		$elenco_chiavi = $this->exec_sql( $sql_chiavi );
		
		$campi_chiave = array();
		foreach( $elenco_chiavi as $chiave ) {
			$campi_chiave[] = $chiave["Column_name"];					
		}
		
		if( count($campi_chiave ) == 1 )
			return $campi_chiave[0];
		else
			return $campi_chiave;
	}
	
	
	public function lookup( $tabella, $valore_id, $campo_descrizione="", $cache=true ) {
	   if( $cache && sessione_attiva() ) {
		  if( !array_key_exists( "LOOKUP_CACHE", $_SESSION ))
			 $_SESSION["LOOKUP_CACHE"] = array();
		  
		  if( !array_key_exists( $tabella, $_SESSION["LOOKUP_CACHE"] )) {
			 $_SESSION["LOOKUP_CACHE"][$tabella] = array();
		  }
		  
		  if( !array_key_exists( $valore_id, $_SESSION["LOOKUP_CACHE"][$tabella] )) {
			 $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["TIMESTAMP"] = time();
			 $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"] = "***NULL***";
		  }   
	   
		  if( time() - $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["TIMESTAMP"] > 10 )
			 $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"] = "***NULL***";
			 
		  if( $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"] != "***NULL***" )
			 return $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"];
	   }   
	   
	   if( $cache && sessione_attiva() && array_key_exists( "TBL_".$tabella."_".$campo_descrizione, $_SESSION["LOOKUP_CACHE"] )) {
		  $descrizione = $_SESSION["LOOKUP_CACHE"]["TBL_".$tabella."_".$campo_descrizione]["descrizione"];
		  $chiave      = $_SESSION["LOOKUP_CACHE"]["TBL_".$tabella."_".$campo_descrizione]["chiave"];
	   } else {
		  $rs_lookup = exec_sql( "SHOW FIELDS FROM $tabella" );
	   
		  $flag_descrizione = false;
	   
		  $chiave = "";
		  $descrizione = "";
	   
		  foreach( $rs_lookup as $campo ) {
			 if( $campo["Key"]=="PRI" )
				$chiave = $campo["Field"];
		  
			 if( $campo_descrizione=="" ) {
				if( $campo["Field"] == "descrizione" ) {
				   $flag_descrizione = true;
				   $descrizione = "descrizione";
				} else if( !$flag_descrizione ) $descrizione = $campo["Field"];   
			 } else $descrizione = $campo_descrizione;
		  }
		  
		  if( $cache ) {
			 $_SESSION["LOOKUP_CACHE"]["TBL_".$tabella."_".$campo_descrizione]["descrizione"] = $descrizione;
			 $_SESSION["LOOKUP_CACHE"]["TBL_".$tabella."_".$campo_descrizione]["chiave"] = $chiave;
		  }
	   }

	   if( $chiave=="" || $descrizione=="" )
		  return "";
	   
	   $sql = "SELECT ".$descrizione." FROM ".$tabella." WHERE ".$chiave."='".addslashes($valore_id)."'";
	;  
	   $rs_lookup = $this->exec_sql( $sql , true );
	   
	   if( array_key_exists( $descrizione, $rs_lookup )) {
		  if( $cache && sessione_attiva() ) 
			 $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"] = $rs_lookup[$descrizione];
		  return $rs_lookup[$descrizione];
	   } else {
		  if( $cache && sessione_attiva() ) 
			 $_SESSION["LOOKUP_CACHE"][$tabella][$valore_id]["VALORE"] = "";
		  return "";  
	   }       
	}

	public function get_row( $tabella, $valore_id, $cache=false ) {

	   if( $cache ) {
		  if( session_status() != PHP_SESSION_ACTIVE )
			 session_start();
			 
		  if( !array_key_exists( "GETROW_CACHE", $_SESSION ))
			 $_SESSION["GETROW_CACHE"] = array();
		  
		  if( !array_key_exists( $tabella, $_SESSION["GETROW_CACHE"] )) {
			 $_SESSION["GETROW_CACHE"][$tabella] = array();
			 $_SESSION["GETROW_CACHE"][$tabella]["CHIAVE"] = "";
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"] = array();
		  }
		  
		  if( !array_key_exists( "RECORD", $_SESSION["GETROW_CACHE"][$tabella] )) {
			 $_SESSION["GETROW_CACHE"][$tabella]["CHIAVE"] = "";
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"] = array();
		  }
		  
		  if( !array_key_exists( $valore_id, $_SESSION["GETROW_CACHE"][$tabella]["RECORD"] )) {
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id] = array();
			 
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["TIMESTAMP"] = time();
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"] = "***NULL***";
		  }
		  
		  $chiave = $_SESSION["GETROW_CACHE"][$tabella]["CHIAVE"];
		  
		  if( time() - $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["TIMESTAMP"] > 10 )
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"] = "***NULL***";
			 
		  if( $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"] != "***NULL***" ) {
			 return $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"]; 
		  }	 
	   } 
	   
	   if( !$cache || $_SESSION["GETROW_CACHE"][$tabella]["CHIAVE"] == "" ) {
		  $rs_lookup = $this->exec_sql( "SHOW FIELDS FROM $tabella" );
	   
		  $flag_descrizione = false;
	   
		  $chiave = "";
	   
		  foreach( $rs_lookup as $campo ) {
			 if( $campo["Key"]=="PRI" )
				$chiave = $campo["Field"];
		  }
	   }
	   
	   if( $chiave=="" )
		  return "";
		
	   if( $cache ) 
		  $_SESSION["GETROW_CACHE"][$tabella]["CHIAVE"] = $chiave;
		  
	   $rs_lookup = $this->exec_sql( "SELECT * FROM ".$tabella." WHERE ".$chiave."='".addslashes($valore_id)."'", true );
	   
	   if( array_key_exists( $chiave, $rs_lookup )) {
		  if( $cache ) {
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["TIMESTAMP"] = time();
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"]    = $rs_lookup;
		  }   
		  return $rs_lookup;
	   } else {
		  if( $cache )
			 $_SESSION["GETROW_CACHE"][$tabella]["RECORD"][$valore_id]["VALORE"] = "***NULL***";   
		  return array();   
	   }      
	}

	public function get_empty_row( $tabella, $cache=true ) {

	   if( $cache ) {
		  if( !array_key_exists( "GET_EMPTY_ROW_CACHE", $_SESSION ))
			 $_SESSION["GET_EMPTY_ROW_CACHE"] = array();

		  if( !array_key_exists( $tabella, $_SESSION["GET_EMPTY_ROW_CACHE"] )) {
			 $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["CAMPI"] = array();
			 $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["TIMESTAMP"] = 0;
		  }
		  
		  if( time() - $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["TIMESTAMP"] > 10 )
			 $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["CAMPI"] = "***NULL***";
			 
		  if( $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["CAMPI"] != "***NULL***" ) {
			 return $_SESSION["GET_EMPTY_ROW_CACHE"][$tabella]["CAMPI"]; 
		  }	 
	   } 

	   $rs_lookup = $this->exec_sql( "SHOW FIELDS FROM $tabella" );
	   $campi = array();
	   
	   foreach( $rs_lookup as $campo ) {
		  $campi[$campo["Field"]]="";
	   }
		
	   if( $cache ) {
			 $_SESSION["GETROW_CACHE"][$tabella]["TIMESTAMP"] = time();
			 $_SESSION["GETROW_CACHE"][$tabella]["CAMPI"]     = $campi;
	   }   

	   return $campi;    
	}


	public function create_option( $tabella, $campo_id, $campi_descrizione, $filtro="", $valore_corrente="", $ordine="", $empty_val = "Selezionare..." ) {
		
		$sql = "SELECT ".$campo_id.",".$campi_descrizione." FROM ".$tabella;
		if( $filtro != "" )
			$sql .= " WHERE ".$filtro;
		
		if( $ordine != "" )
			$sql .= " ORDER BY ".$ordine;
			
		$record = $this->exec_sql( $sql );
		
		if( count( $record ) > 1 && $empty_val != "" )
			$buffer = '<option value="">'.$empty_val.'</option>';
		else
			$buffer = "";
			
		$elenco_campi = explode( ",", $campi_descrizione );
		foreach( $record as $valore ) {
			$buffer .= '<option value="'.$valore[$campo_id].'"';
			if( $valore[$campo_id] == $valore_corrente )
			   $buffer .= " selected";
			$buffer .= ">";
			
			$valore_descrizione = "";
			foreach( $elenco_campi as $campo )
			   if( array_key_exists( trim($campo), $valore ))
				  $valore_descrizione .= " ".$valore[trim($campo)];
				  
			$buffer .= trim($valore_descrizione).'</option>';
		}
		
		return $buffer;
	}


	public function decodifica_campo( $campo, $row ) {
	   
	   $parametri = "";
	   
	   if( $campo["Comment"]!="" ) {
		  if( strpos( $campo["Comment"], ";" )!==false ) {
			 list( $etichetta, $parametri ) = explode( ";", $campo["Comment"] );
		  } else {
			 $etichetta = $campo["Comment"];
			 $parametri = "";
		  }
	   }
				   
	   if( $campo["Type"]=="date" || $campo["Type"]=="timestamp" )
		  return date("d-m-Y", strtotime($row[$campo["Field"]]));
	   else {	  
		  if( $parametri != "" ) { 
			if( strpos( $parametri, "," ) !== false )
				$elenco_parametri = explode( ",", $parametri );
			else
				$elenco_parametri = array( $parametri );
			
			 switch( $elenco_parametri[0] ) {
				case "N":
				   if( array_key_exists( 1, $elenco_parametri ))
					  $cifre = $elenco_parametri[1];
				   else
					  $cifre = 2;
				   return '<div style="white-space:pre;" class="text-right">'.number_format($row[$campo["Field"]],$cifre, ",", ".").'</div>';                                 
				   break;
				   
				case "R":
				   for( $i=1; $i<count($elenco_parametri); $i++ ) {
					  list($id,$valore)=explode( "=", $elenco_parametri[$i] );
					  if( $id==$row[$campo["Field"]] )
						 return $valore;
				   }      
				   break;  
				case "L":
				case "FL":
				   $sql  = "SELECT ".$elenco_parametri[3];
				   if( array_key_exists( 4, $elenco_parametri ) && is_numeric(substr($elenco_parametri[4],0,1)) ) {
					   for( $i=5; $i<=(4+intval(substr($elenco_parametri[4],0,1))); $i++ )
						  $sql .= ",".$elenco_parametri[$i];
				   }
				   
				   $sql .= " FROM ".$elenco_parametri[1]." WHERE ".$elenco_parametri[2]."='".addslashes($row[$campo["Field"]])."'";
				
				   $dato = exec_sql( $sql, true ); 
				   
				   $buffer = "";
				   for( $i=3; $i<count($elenco_parametri); $i++ )
					  if( array_key_exists( $elenco_parametri[$i], $dato )) 
						 $buffer .= " ".$dato[$elenco_parametri[$i]];
						 
				   return trim($buffer);
				   break; 
				case "T":
					if( array_key_exists( 2, $elenco_parametri ))
						return '<span style="white-space:pre;'.$elenco_parametri[2].'">'.wordwrap($row[$campo["Field"]], $elenco_parametri[1]).'</span>';
					else {
						if( array_key_exists( 1, $elenco_parametri ))
							return '<span class="'.$elenco_parametri[1].'">'.$row[$campo["Field"]].'</span>';
						else
							return $row[$campo["Field"]];
					}
				   break;
				   
				case "W": 
				   $buffer = '<span style="white-space:pre;';
				   if( array_key_exists( 2, $elenco_parametri ))
					   $buffer .= $elenco_parametri[2];
				   $buffer .=  '">'.chunk_split(wordwrap($row[$campo["Field"]], $elenco_parametri[1]), $elenco_parametri[1]+1).'</span>';
				   
				   return $buffer;
				   break;
				   
				case "WC": 
				   $buffer = '<span style="white-space:pre;';
				   if( array_key_exists( 2, $elenco_parametri ))
					   $buffer .= $elenco_parametri[2];
				   $buffer .=  '">'.chunk_split($row[$campo["Field"]], $elenco_parametri[1]).'</span>';
				   
				   return $buffer;
				   break;
			 }      
			 
		  } else return $row[$campo["Field"]];
	   }	
	}
}
