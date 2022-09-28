<?php
/****************************************************/
/* COMMON FUNCTIONS - Ver 4.7                       */
/*-----------------------------------------         */
/* DIGITAL SOLUTIONS (C) 2019                       */
/****************************************************/

function inverti_data( $data, $formato = "A", $formato_partenza = "" ) {
   $data = trim( $data );

   if( $data == "" )
	   if( $formato = "I" ) return "00-00-0000";
	   else return "0000-00-00";
   if( $data == "0000-00-00" ) return "";
   if( $data == "0001-01-01" ) return "";

   $data = str_replace("/","-",$data);
   $data = str_replace(".","-",$data);
   $data = str_replace(" ","-",$data);

   if( strpos( $data, "-" )===false ) {
      if( strlen( $data == 6 ) ) {
        $data = substr( $data,0,2 )."-".substr( $data,2,2 )."-".substr( $data,4,2 );
      } else {
        $data_1 = substr( $data,0,2 )."-".substr( $data,2,2 )."-".substr( $data,4,4 );
        $data_separata_1 = explode("-",$data_1);
        
		if( $formato_partenza == "" ) { 
			if( checkdate( $data_separata_1[1], $data_separata_1[0], $data_separata_1[2] )) {
			   $data = $data_1;
			} else {
			   $data = substr( $data,0,4 )."-".substr( $data,4,2 )."-".substr( $data,6,2 );
			}
		} else {
			if( $formato_partenza == "I" )
				$data = $data_1;
			else
				$data = substr( $data,0,4 )."-".substr( $data,4,2 )."-".substr( $data,6,2 ); 
		}
      }
   }
   
   $data_separata = explode("-",$data);

   for( $i=0; $i<2; $i++ )
      if( strlen( $data_separata[$i] ) < 2 )
         $data_separata[$i] = "0".$data_separata[$i];
         
   switch( $formato ) {
      case "A":
         if( $data_separata[0] > 31 )
            $data_invertita = $data_separata[0]."-".$data_separata[1]."-".$data_separata[2];
         else
            $data_invertita = $data_separata[2]."-".$data_separata[1]."-".$data_separata[0];
         break;

      case "I":
         if( $data_separata[0] <= 31 )
            $data_invertita = $data_separata[0]."-".$data_separata[1]."-".$data_separata[2];
         else
            $data_invertita = $data_separata[2]."-".$data_separata[1]."-".$data_separata[0];

         break;

      default:
         $data_invertita = $data_separata[2]."-".$data_separata[1]."-".$data_separata[0];
   }

   return $data_invertita;
}

if( !function_exists( "is_date" )) {
	function is_date( $testo ) {
	   if( $testo == "" )
		  return false;

	   $testo = substr( $testo, 0, 10 );
	   
	   switch( $testo ) {
		  case "0000-00-00":
		  case "0001-01-01":
			 return true;
			 break;

		  default:
			 $testo = str_replace( "/", "-", $testo);
			 $testo = str_replace( ".", "-", $testo);

			 $elementi = explode( "-", $testo );

			 if( count($elementi)!= 3)
				return false;

			 for( $i=0; $i<3; $i++ )
				if( !is_numeric($elementi[$i]) )
				   return false;

			 return true;
	   }
	}
}

function sessione_attiva() {
	if( function_exists( "session_status") ) {
		// PHP > 5.4
		if( session_status() === PHP_SESSION_ACTIVE )
			return true;
		else
			return false;
	} else {
		// PHP 5.3
		if( is_array( $_SESSION )) {
			foreach( $_SESSION as $session_var )
				return true;
		}
		
		return false;
	}
}


// PARAMETRI...

abstract class FIELDTYPES {
    const TEXT_FIELD     = 9001;
    const DATA_FIELD     = 9002;
    const NUMERIC_FIELD  = 9003;
    const RADIO_BUTTON   = 9004;
    const LOOKUP         = 9005;
    const WIDE_TEXT      = 9006;
}

class TABLE_LOOKUP {
	public $tabella;
	public $campo_id;
	public $campo_descrizione;
	public $filtro;
}

class FIELD_PARAMS {
	public $tipo; // FIELDTYPES
	public $etichetta;
	public $decimali = 2;
	public $table_lookup; // TABLE_LOOKUP
	public $radio;
	public $wide;
}

class DATATABLE_PARAMS {
   // Tipi di campi...
   public $modifica         = true;		
   public $cancella         = true;
   public $seleziona        = false;
   public $target           = array();  // Array contenente il valore del parametro target per i link standard "modifica", "cancella", "seleziona"
   public $eventi_riga      = array();  // Array di eventi. Ogni evento presenta le chiavi: 
										// id_evento, etichetta, colore, callback, target, condizione
                                        // colore contiene il colore degli eventi codificato da bootstrap
                                        // callback rappresenta una funzione da chiamare ed ha come parametri il record...
										// condizione rappresenta una funzione da chiamare che restituisce TRUE / FALSE
										// e comanda l'inserimento dell'evento in sede di predisposizione del datatable
   public $filter           = "";
   public $postback_params  = "";
   public $prefix           = "";
   public $postback         = ""; 
   public $sort             = "";
   public $id				= "";
   public $col_width        = array();	// Array contenente il nome del campo come indice e la classe CSS da imporre sulla TH
   public $title            = "";       // Titolo della tabella inserito nella prima riga del THEAD
   public $parametri_campi  = array();  // Array dove l'id è il nome del campo ed il valore un oggetto di tipo FIELD_PARAMS;
   
   public $format_campi     = "";  		// Funzione con due parametri, $nome_campo, $record. Restituisce un 
										// array( "CLASS", "STYLE", "VALORE" ); 
										// Valori tutti opzionali
										// CLASS, STYLE e ATTR vengono associati alla TD padre. Valore, costituisce il valore del Campo...
										// CLASS, STYLE e ATTR vengono associati alla TD padre. Valore, costituisce il valore del Campo...
   public $row_eval			= "";  		// Function con parametro $record, restituisce un array( "CLASS", "STYLE", "ATTR" );
										// CLASS, STILE e ATTR vengono associati alla TR padre. (Se record viene passato per riferimento è possibile aggiornare i dati)...
								   
   public $hide             = "";  		// Nome dei campi da nascondere (in aggiunta a quelli identificati dal DB (HIDE nella proprietà Comment)
   public $dynamic          = 5;   		// Numero di record della tabella dopo i quali viene trasformata in dinamica (-1 = mai)
   public $record_visibili  = 5;   		// Numero di record visibili
   public $campi_aggiuntivi = array();  // Campi aggiuntivi...
										// Array con la seguente struttura: 
										// Array( "ID"            	=> IdCampo, 
										//        "ETICHETTA"     	=> Etichetta,
										//        "POSIZIONE" 	 	=> Posizione del campo (dove 0=fine),
										//		  "CALLBACK"		=> Funzione membro di calcolo... )
}

class DATATABLE {
	protected $sorgente;
	protected $DB_MANAGER;	
	protected $PARAMS;
	
	function __construct( $sorgente, $DB_MANAGER = null ) {
		$this->PARAMS = new DATATABLE_PARAMS;
	   
		if( $this->PARAMS->postback == "" )
			$this->PARAMS->postback = basename( $_SERVER["PHP_SELF"] );
	   
		if( $this->PARAMS->postback_params != "" )
			$this->PARAMS->postback_params = "&".$this->PARAMS->postback_params;
		
		$this->DB_MANAGER 	= $DB_MANAGER;
		$this->sorgente		= $sorgente;
	}
	
	function __set( $param, $value ) { if( $value == "flag_criticita" ) echo $value;
		$this->PARAMS->$param = $value;
	}
	
	function aggiungi_evento( $id_evento, $etichetta, $colore="primary", $callback="", $target="", $type="SERVER" ) {
		$this->PARAMS->eventi_riga[] = array( "id_evento" => $id_evento, "etichetta" => $etichetta, "colore" => $colore, "callback" => $callback, "target" => $target, "type" => $type );
	}

	function build() { // DATATABLE_PARAMS... parametro opzionale...
		// TIPOLOGIA DI SORGENTE...
		// TABELLA... 1
		// ARRAY... 2
		// FRASE SQL...3
		$TIPO_SORGENTE = -1;
		$tabella_DB = "";
	   
		$buffer = "";
		
		if( is_array( $this->sorgente ) || strpos( strtoupper($this->sorgente), "SELECT" ) !== false ) {
			if( is_array( $this->sorgente ) ) { 
				// SORGENTE -> ARRAY
				
				if( count( $this->sorgente ) == 0 )
					return;
				
				$TIPO_SORGENTE = 2;
				$rs            = $this->sorgente;
				$base_campi    = reset($this->sorgente);	
				
			} else {
				// SORGENTE -> FRASE SQL
				$TIPO_SORGENTE = 3;
				
				if( is_null( $this->DB_MANAGER ))
					throw new Exception( "Non è possibile costruire un datatable senza DB-MANAGER (0x90004)", "90004" );	
		
				$rs = $this->DB_MANAGER->exec_sql( $this->sorgente );
				
				if( count($rs)==0 ) {
					$sql_campi     = substr( $this->sorgente, 0, strpos( strtoupper($this->sorgente), "WHERE" ))." LIMIT 0,1";
					$base_campi    = $this->DB_MANAGER->exec_sql( $sql_campi, true );
				} else $base_campi = $rs[0];
			}
			
			$this->PARAMS->modifica = false;
			$this->PARAMS->cancella = false;
				
			$campi_tabella = array();
			foreach( $base_campi as $nome_campo => $valore ) {
				$campo = array();
				$campo["Key"]     = "";
				$campo["Comment"] = "";
				$campo["Field"]   = $nome_campo;
				if( is_date( $valore ))
					$campo["Type"] = "date";
				else
					$campo["Type"] = "varchar";
				
				if( array_key_exists( $nome_campo, $this->PARAMS->parametri_campi )) {
					$parametri_campo = $this->PARAMS->parametri_campi[$nome_campo];
					
					$campo["Comment"] = $parametri_campo->etichetta;
					switch( $parametri_campo->tipo ) {
						case FIELDTYPES::DATA_FIELD:
							$campo["Comment"] .= ";D";
							break;
						case FIELDTYPES::NUMERIC_FIELD:
							$campo["Comment"] .= ";N,".$parametri_campo->decimali;
							break;
						case FIELDTYPES::RADIO_BUTTON:
							$campo["Comment"] .= ";R,".$parametri_campo->radio;
							break;
						case FIELDTYPES::LOOKUP:
							$campo["Comment"] .= ";L,".$parametri_campo->table_lookup->tabella;
							$campo["Comment"] .= ",".$parametri_campo->table_lookup->campo_id;
							$campo["Comment"] .= ",".$parametri_campo->table_lookup->campo_descrizione;
							break;
						case FIELDTYPES::WIDE_TEXT:
							$campo["Comment"] .= ";W,".$parametri_campo->wide;
							break;
					}
				}
				$campi_tabella[] = $campo;
			}
		   
		} else {
			// SORGENTE -> TABELLA
			$TIPO_SORGENTE = 1;
			$tabella_DB = $this->sorgente;
			
			if( is_null( $this->DB_MANAGER ))
				throw new Exception( "Non è possibile costruire un datatable senza DB-MANAGER (0x90005)", "90005" );
			
			$campi_tabella = $this->DB_MANAGER->exec_sql( "SHOW FULL FIELDS FROM $tabella_DB" );

			$sql = "SELECT * FROM $tabella_DB";
			if( $this->PARAMS->filter != "" )
				$sql .= " WHERE ".$this->PARAMS->filter;
		
			if( $this->PARAMS->sort != "" )
				$sql .= " ORDER BY ".$this->PARAMS->sort;
				 
			$rs = $this->DB_MANAGER->exec_sql( $sql );
		}
		$_SESSION["FRWK_tbl_".sha1($tabella_DB)] = $tabella_DB;
	   
	   if( $this->PARAMS->title != "" ) {
		   $buffer .= '<h6>'.$this->PARAMS->title.'</h6>';
	   }
	   
	   $buffer .= '<table class="table table-striped table-bordered table-hover table-sm';
	   if( isset( $this->PARAMS->class ))
		  $buffer .= ' '.$this->PARAMS->class;
	   $buffer .= '"';
	   if( count($rs) >= $this->PARAMS->dynamic && $this->PARAMS->dynamic >= 0 ) {
		  $buffer .= 'data-dynamic="true"';
		  
		  $buffer .= 'data-righe_visibili="'.$this->PARAMS->record_visibili.'"';
		} 
		
	   $buffer .= '>';
	   $buffer .= '<thead class="thead-dark">';
	   /*
	   if( $this->PARAMS->title != "" ) {
		   $buffer .= '<tr>';
		   if( $this->PARAMS->modifica || $this->PARAMS->cancella || $this->PARAMS->seleziona || count($this->PARAMS->eventi_riga)>0 )
			   $OFFSET = 1;
		   else
			   $OFFSET = 0;
		   $buffer .= '<th colspan="'.(count($campi_tabella)+count($this->PARAMS->campi_aggiuntivi) + $OFFSET).'">'.$this->PARAMS->title.'</th>';
		   $buffer .= '</tr>';
	   }
	   */
	   $buffer .= '<tr>';
	   $num_campi=0;
	   
	   $i=0;
	   foreach( $this->PARAMS->campi_aggiuntivi as $campo_aggiuntivo ) {
		  $i++;
		  if( array_key_exists( "ID", $campo_aggiuntivo ))
			  $nome_campo_aggiuntivo = $campo_aggiuntivo["ID"];
		  else
			  $nome_campo_aggiuntivo = "added_".$i;
		  
		  if( array_key_exists( "POSIZIONE", $campo_aggiuntivo ))
			  $posizione = $campo_aggiuntivo["POSIZIONE"];
		  else
			  $posizione = 0;
		  
		  if( array_key_exists( "ETICHETTA", $campo_aggiuntivo ))
			  $etichetta = $campo_aggiuntivo["ETICHETTA"];
		  else
			  $etichetta = 0;

		  $campo = array( "Key" => "", "Type" => "text", "Field" => $nome_campo_aggiuntivo, "Comment" => $etichetta );
		  
		  if( $posizione == 0 )
			 $campi_tabella[] = $campo;
		  else {
			 $posizione--;
			 $campi_tabella = array_merge( array_slice( $campi_tabella, 0, $posizione+1, true ),  array(0 => $campo), array_splice( $campi_tabella, $posizione+1, count($campi_tabella)-$posizione , true ) ); 
		  }  
	   }
		  
	   if( $this->PARAMS->hide != "" ) { 
		  $campi_esclusi = explode( ",", $this->PARAMS->hide );
	   } else $campi_esclusi = array();

	   foreach( $campi_tabella as $campo ) {
		  $show_field = true;
		  
		  if( $campo["Key"]=="PRI" || $campo["Comment"]=="HIDE" )
			 $show_field = false;

		  if( array_search( $campo["Field"], $campi_esclusi )!==false )
			 $show_field = false;
				
		  if( $show_field ) {
			 $buffer .= '<th';
			 
			 $buffer .= ' id="field_header_'.$campo["Field"].'"';
			 if( array_key_exists( $campo["Field"], $this->PARAMS->col_width ))
				 $buffer .= ' class="'.$this->PARAMS->col_width[$campo["Field"]].'"';
			 $buffer .= '>';
			 if( $campo["Comment"]!="" ) {
				if( strpos( $campo["Comment"], ";" )!==false )
				   list( $etichetta, $parametri ) = explode( ";", $campo["Comment"] );
				else
				   $etichetta = $campo["Comment"];
					  
				$buffer .= $etichetta;
			 }
			 else
				$buffer .= $campo["Field"];   
			 $buffer .= '</th>';
			 $num_campi++;
		  }
	   }
	   
	   if( $this->PARAMS->modifica || $this->PARAMS->cancella || $this->PARAMS->seleziona || $this->row_events() >0 ) {
		  $dimensione = 5;
		  if( $this->PARAMS->modifica )  $dimensione += 92;
		  if( $this->PARAMS->cancella )  $dimensione += 92;
		  if( $this->PARAMS->seleziona ) $dimensione += 92;
		  $dimensione += 92 * (count($this->PARAMS->eventi_riga)); // 93
		  $dimensione += 25; 
		  $buffer .= '<th style="width:'.$dimensione.'px">&nbsp;</th>';
	   }   
	   $buffer .= '</tr></thead><tbody>';
	   $vuota = true;

	   foreach( $rs as $id_record => $row ) {
	   
	      $i=0;
	      foreach( $this->PARAMS->campi_aggiuntivi as $campo_aggiuntivo ) {	 
			 $i++;
			 if( array_key_exists( "CALLBACK", $campo_aggiuntivo ))
				$valore = call_user_func( $campo_aggiuntivo["CALLBACK"], $row );
			 else
				$valore = "";

			 $row[$campo_aggiuntivo["ID"]] = $valore;
		  }
		  
		  $buffer .= '<tr';
		  
		  if( $this->PARAMS->row_eval != "" &&  function_exists( $this->PARAMS->row_eval ) ) {
			  $user_function = $this->PARAMS->row_eval;
			  $retval = $user_function( $row );
			  
			  if( is_array( $retval )) {
				  if( array_key_exists( "CLASS", $retval ) )
					  $buffer .= ' class="'.$retval["CLASS"].'"';	
				  
				  if( array_key_exists( "STYLE", $retval ) )
					  $buffer .= ' style="'.$retval["STYLE"].'"';	
				  
				  if( array_key_exists( "ATTR", $retval ) )
					  $buffer .= ' '.$retval["ATTR"];
			  }
		  }		
		  
		  
		  $vuota = false;
			 
		  $buffer .= '>';
		  $key = "";
		  $key_numfields = 0;
		  $key_id = "";
		  foreach( $campi_tabella as $campo ) {
			 if( $campo["Key"]=="PRI") {
				if( $key != "" )
				   $key .= "&";
				$key .= $campo["Field"].'='.urlencode($row[$campo["Field"]]);   
				
				$key_numfields++;
				if( $key_numfields == 1 )
					$key_id = $row[$campo["Field"]];
			 } else {
				$show_field = true;
		  
				if( $campo["Comment"]=="HIDE" )
				   $show_field = false;

				if( array_search( $campo["Field"], $campi_esclusi )!==false )
				   $show_field = false;
				
				if( $show_field ) {
					
				   $buffer .= '<td';
				   if( $this->PARAMS->format_campi != "" ) {
					   $elaborazione = call_user_func( $this->PARAMS->format_campi, $campo["Field"], $row );
					   
					   if( !is_array( $elaborazione ))
						   $elaborazione = array();
					   
				   } else $elaborazione = array();
				   
				   if( array_key_exists( "CLASS", $elaborazione ))
					   $buffer .= ' class="'.$elaborazione["CLASS"].'"';	
				   
				   if( array_key_exists( "STYLE", $elaborazione ))
					   $buffer .= ' style="'.$elaborazione["STYLE"].'"';
				   
				   if( array_key_exists( "ATTR", $elaborazione ))
					   $buffer .= ' '.$elaborazione["ATTR"];	
				   
				   if( array_key_exists( "VALORE", $elaborazione ))
					   $row[$campo["Field"]] = $elaborazione["VALORE"];	   
				   
				   $buffer .= '>';
					
				   $buffer .= $this->decodifica_campo( $campo, $row );
				   
				   $buffer .= '</td>';
				}
			 }  
		  }
		  if( $this->PARAMS->modifica || $this->PARAMS->cancella || $this->PARAMS->seleziona || $this->row_events() > 0 ) {
			 if( $this->PARAMS->prefix == "" )
				$this->PARAMS->prefix = "FRWK";
			 
			 if( strpos( $this->PARAMS->postback, "?" )===false ) {
				$this->PARAMS->postback .= "?";
			 } else {
				if( substr( $this->PARAMS->postback, strlen( $this->PARAMS->postback ) -1, 1) !== "&" )
					$this->PARAMS->postback .= "&";
			 }
			 
			 if( $this->PARAMS->postback_params != "" ) {
			    if( strpos( $this->PARAMS->postback_params, 0, 1 ) != "&" )
			       $this->PARAMS->postback_params = "&" . $this->PARAMS->postback_params;
			 }
			 
			 $buffer .= '<td class=text-center">';
			 $buffer .= '<div class="row-fluid">';
			 if( $this->PARAMS->modifica ) {
				$buffer .= '<a style="width:92px" href="'.$this->PARAMS->postback.'t='.sha1($tabella_DB).'&'.$this->PARAMS->prefix.'_action=modify&'.$key.$this->PARAMS->postback_params.'" class="btn btn-primary btn-sm mr-1 mb-0 mt-0 pl-0 pr-0"';
				if( array_key_exists( "modifica", $this->PARAMS->target ))
					$buffer .= ' target="'.$this->PARAMS->target["modifica"].'"';
				$buffer .= '>Modifica</a>';
			 } if( $this->PARAMS->cancella ) {
				$buffer .= '<a style="width:92px; color:white" href="#" onclick=\'confirm_delete("'.$this->PARAMS->postback.'t='.sha1($tabella_DB).'", "'.$this->PARAMS->prefix.'", "'.$key.'", "'.$this->PARAMS->postback_params.'")\' class="btn btn-danger mr-1 btn-sm mb-0 mt-0 pl-0 pr-0"';
				if( array_key_exists( "cancella", $this->PARAMS->target ))
					$buffer .= ' target="'.$this->PARAMS->target["cancella"].'"';
				$buffer .= '>Cancella</a>'; 
			 }
			 if( $this->PARAMS->seleziona ) {
				$buffer .= '<a style="width:92px" href="'.$PARAMS->postback.'t='.sha1($tabella_DB).'&'.$this->PARAMS->prefix.'_action=select&'.$key.$this->PARAMS->postback_params.'" class="btn btn-success mr-1 btn-sm mb-0 mt-0 pl-0 pr-0"';
				if( array_key_exists( "seleziona", $this->PARAMS->target ))
					$buffer .= ' target="'.$this->PARAMS->target["seleziona"].'"';
				$buffer .= '>Seleziona</a>';
			 }  
			 
			 if( $key_numfields != 1 )
				 $key_id = $id_record;
			 
			 $buffer .= $this->add_row_events( $key, $key_id, $tabella_DB, $row );
			 
			 $buffer .= '</div>';
			 $buffer .= '</td>';
		  }
		  $buffer .= '</tr>';
	   }
	   if( $vuota )
		  $buffer .= '<tr><td colspan="'.($num_campi+1).'"><em>TABELLA VUOTA</em></td></tr>';
	   
	   $buffer .= "</tbody></table>";
	   
	   return $buffer;
	}
	
	function render() {
		echo $this->build();
	}

	function decodifica_campo( $campo, $row ) {
	   
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
				
				   $dato = $this->DB_MANAGER->exec_sql( $sql, true ); 
				   
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
			 
		  } else {
			  return $row[$campo["Field"]];
		  }
	   }	
	}
	
	protected function add_row_events( $key, $id_record, $tabella_DB, $record ) {
		$buffer = "";
		
		foreach( $this->PARAMS->eventi_riga as $evento ) { 
			$inserisci = true;
			
			if( is_object( $evento )) {
				if( $evento->condition_callback != "" && function_exists( $evento->condition_callback )) {
				}
			}
			
			if( $inserisci ) {
				if( $evento["type"] == "SERVER" ) {
					$buffer .= '<a style="width:92px" href="'.$this->PARAMS->postback.'t='.sha1($tabella_DB).'&'.$this->PARAMS->prefix.'_action='.$evento["id_evento"].'&'.$key.$this->PARAMS->postback_params.'" class="btn btn-'.$evento["colore"].' mr-1 mb-0 mt-0 btn-sm pl-0 pr-0"';
					if( array_key_exists( "target", $evento ))
					   $buffer .= ' target="'.$evento["target"].'"';
					   
					if( array_key_exists( "conferma", $evento ) && $evento["conferma"] ) {
					   if( !array_key_exists( "bottone_conferma", $evento ))
						  $evento["bottone_conferma"] = "";		    
					   if( !array_key_exists( "colore_bottone", $evento ))
						  $evento["colore_bottone"] = "";
						  
					   $buffer .= 'onclick=\'return confirm_custom("'.$evento["messaggio"].'", "'.$evento["bottone_conferma"].'", "'.$evento["colore_bottone"].'", "'.$evento["id_evento"].'", "'.$this->PARAMS->postback.'t='.sha1($tabella_DB).'", "'.$this->PARAMS->prefix.'", "'.$key.'", "'.$this->PARAMS->postback_params.'")\'';
					   
					}   
					$buffer .= '>'.$evento["etichetta"].'</a>';
					
					if( array_key_exists( "callback", $evento ))
					   $_SESSION["CALLBACK_".$evento["id_evento"]] = $evento["callback"];
					else
					   $_SESSION["CALLBACK_".$evento["id_evento"]] = "";
				} else {
					$buffer .= '<button style="width:92px" id="'.$evento["id_evento"].'_'.$id_record.'" data-params="'.$evento["params"].'" class="btn btn-'.$evento["colore"].' mr-1 mb-0 mt-0 btn-sm pl-0 pr-0">';
					   
					if( array_key_exists( "conferma", $evento ) && $evento["conferma"] ) {
					   if( !array_key_exists( "bottone_conferma", $evento ))
						  $evento["bottone_conferma"] = "";		    
					   if( !array_key_exists( "colore_bottone", $evento ))
						  $evento["colore_bottone"] = "";
						  
					   $buffer .= ' data-confirm="1" data-message="'.$evento["messaggio"].'" data-confirm_button="'.$evento["bottone_conferma"].'" data-confirm_color="'.$evento["colore_bottone"].'" data-confirm_id="'.$evento["id_evento"].'"';
					   
					} else $buffer .= ' data-confirm="0"'; 
					$buffer .= ' data-key="'.base64_encode($key).'" data-record="'.$id_record.'"';
					
					$buffer .= '>'.$evento["etichetta"].'</button>';					
				}
			}
		} 
		
		return $buffer;
	}
	
	protected function row_events() {
		return count($this->PARAMS->eventi_riga);
	}
	
	function set_param( $param, $value ) {
		$this->PARAMS->$param = $value;
	}
	
	
}

// PARAMETRI...
class EDIT_TABLE_PARAMS {
   public $postback_script 	= "";
   public $parametri       	= "";  // Funzione che restituisce per il singolo campo un array:
                                  //   array( "hidden"     => true / false,
                                  //          "lookup"     => ...vedi sotto...
                                  //          "default"    => valore di default del campo,
								  //		  "required"   => ture / false,
								  //          "divider"    => true / false,
								  //          "html_pre"   => html da inserire prima del campo,
								  //          "html_post"  => html da inserire dopo il campo,
								  //		  "html_parms" => array_contenente parametri html per il TAG...
								  //		  "inline"     => true / false -> true indica che il campo successivo è sulla stessa linea (default false)
								  //          "field_col"  => [1-12] -> dimensione in colonne del campo (default 12)
						
                                  // Parametri in ingresso:
                                  //   nome_campo, valore_campo (determinato dal DB o dal postback)
   public $lookup          	= "";  // Campo con tabella di lookup. Sintassi: <nome_tabella>,<campo_id>,<sql o nome campo descrizione>,[<campo/i order by>][<titolo per select dinamico>]
   public $div_columns     	= 5;
   public $form_class	    = "";
   public $form_style       = "";
   public $no_title			= false;
   public $layout_agid		= true;
   public $custom_title		= "";
   public $form_header      = "";
   public $form_footer      = "";
}

function edit_table_form( $tabella_DB ) { // EDIT_TABLE_PARAMS parametro opzionale...
   
   if( func_num_args() == 1 ) {
      $PARAMS = new EDIT_TABLE_PARAMS;
      
   } else $PARAMS = func_get_arg(1);
      
   if( array_key_exists( "FRWK_action", $_POST ) && $_POST["FRWK_action"]=="insert" )
      $force_insert = true;
   else
      $force_insert = false;
         
   $campi_tabella = exec_sql( "SHOW FULL FIELDS FROM $tabella_DB" );
   $dati_form = array(); 

   $action = "NONE";
   if( array_key_exists( "FRWK_action", $_GET ))
	   $action = $_GET["FRWK_action"];
   
   if( array_key_exists( "FRWK_action", $_POST ))
	   $action = $_POST["FRWK_action"];
   
   if( $action != "NONE" ) {
      if( $action == "modify" || $action == "select" ) {
         $where = "";
         foreach( $campi_tabella as $campo ) {
			$valore_campo = "";
	  
			if( array_key_exists( $campo["Field"], $_POST ))
			   $valore_campo = $_POST[$campo["Field"]];
	   
			if( array_key_exists( $campo["Field"], $_GET ))
			   $valore_campo = $_GET[$campo["Field"]]; 
		
			if( $campo["Type"]=="date" || $campo["Type"]=="timestamp" )
			   $valore_campo = inverti_data( $valore_campo, "A" );
			   
            if( $campo["Key"]=="PRI" && $valore_campo != "" ) {
               if( $where != "" )
                  $where .= " AND ";
               
               $where .= $campo["Field"]."='".addslashes($valore_campo)."'";   
            }
         }
         if( $where != "" ) { 
            $dati_form = exec_sql( "SELECT * FROM $tabella_DB WHERE ".$where, true );
         }     
      } 
   }
   
   ob_start();
   echo '<div class="col-'.$PARAMS->div_columns;
   if( $PARAMS->form_class != "" )
	   echo ' '.$PARAMS->form_class;
   
   echo ' mb-4"';
   if( $PARAMS->form_style != "" )
	   echo ' style="'.$PARAMS->form_style.'"';   
   
   echo '>';
   if( $PARAMS->form_header != "" )
	   echo $PARAMS->form_header;
   
   echo '<form method="post" action="';
   if( $PARAMS->postback_script != "" ) { 
	  	if( strpos( $PARAMS->postback_script, "?" )===false ) {
			$PARAMS->postback_script .= "?";
		} else {
			if( substr( $PARAMS->postback_script, strlen( $PARAMS->postback_script ) -1, 1) !== "&" )
				$PARAMS->postback_script .= "&";
		} 
		echo ''.$PARAMS->postback_script.'t='.sha1($tabella_DB);
   } else
      echo $_SERVER["SCRIPT_NAME"];    
   echo '">';
   
   $_SESSION["FRWK_tbl_".sha1($tabella_DB)] = $tabella_DB;
   echo '<input type="hidden" name="FRWK_session" value="'.sha1($tabella_DB).'"/>';
    
   $UPDATE = true;
   
   echo '<div class="form-row">';
   foreach( $campi_tabella as $campo ) {
	  $valore_campo = "";
	 
	  $trovato = 0;
	  if( array_key_exists( $campo["Field"], $_POST )) {
		 $valore_campo = $_POST[$campo["Field"]];
		 $trovato++;
	  }
	   
	  if( array_key_exists( $campo["Field"], $_GET )) {
		 $valore_campo = $_GET[$campo["Field"]]; 
		 $trovato++;
	  }
	   
	  if( array_key_exists( $campo["Field"], $dati_form ))
		 $valore_campo = $dati_form[$campo["Field"]]; 
		 
	  if( $campo["Type"]=="date" || $campo["Type"]=="timestamp" ) {  
		 $valore_campo = inverti_data( $valore_campo, "I" );
	  }
	  $campo_hidden = false;

	  $parametri = "";      
	  if( $PARAMS->parametri != "" )
	      $parametri_campo = call_user_func( $PARAMS->parametri, $campo["Field"], $valore_campo );
	  else $parametri_campo = array();
	  
	  $parametri_manuali = array( "valore_campo" => "default", "campo_hidden" => "hidden", "divider" => "divider", "html_pre" => "html_pre", "html_post" => "html_post", "html_parms" => "html_parms", "inline" => "inline", "field_col" => "field_col", "readonly" => "readonly", "required" => "required" );
	  $default_campi     = array( "valore_campo" => $valore_campo, "campo_hidden" => false, "divider" => false, "html_pre" => "", "html_post" => "", "html_parms" => array(), "inline" => false, "field_col" => "", "readonly" => false, "required" => false );

	  foreach( $parametri_manuali as $nome_variabile => $indice )
		if( array_key_exists( $indice, $parametri_campo ))
			$$nome_variabile = $parametri_campo[$indice];
		else
			$$nome_variabile = $default_campi[$nome_variabile];
	  
	  if( trim($campo["Comment"])=="HIDE" )
	     $campo_hidden = true;
	  
	  if( $inline != true )
		  echo '</div><div class="form-row">';
	  echo '<div class="col';
	  if( $field_col != "" )
		  echo '-'.$field_col;
	  echo '">'; 
	  echo $html_pre;
	  if( $divider ) echo "<hr/>";
	  
      if( $campo["Key"]=="PRI" || $campo_hidden ) {
		 if( $trovato > 0 ) {
		    echo '<input type="hidden" name="'.$campo["Field"].'" value="';
		    echo $valore_campo;             
            echo '"';
			foreach( $html_parms as $param => $value )
			   echo ' '.$param.'="'.$value.'"';
			echo '/>';
		 } else {
			if( $campo["Key"]=="PRI" ) 
				$UPDATE = false; 
		 }
      } else { 
		 if( $campo["Comment"]!="" ) {
			if( strpos( $campo["Comment"], ";" )!==false )
			   list( $etichetta, $parametri ) = explode( ";", $campo["Comment"] );
			else {
			   $etichetta = $campo["Comment"];
			}	  
		 } else {
		    $etichetta = $campo["Field"];
		 }   
		   
	     if( $PARAMS->lookup != "" ) {
	        $parametri    = "L,".$parametri_campo["lookup"];
		 }
		 
		 $input      = true;
		 $tipo_input = "text";
		 
		 if( $campo["Type"]=="date" || $campo["Type"]=="timestamp" )
			 $tipo_input = "date";
		 
         if( $parametri != "" ) {
            $elenco_parametri = explode( ",",$parametri );
            switch( $elenco_parametri[0] ) {
               case "P":
                  $tipo_input = "password";
                  break;
               case "E":
                  $tipo_input = "email";
                  break;
               case "N":
                  $tipo_input = "number";
                  break;
               case "R":
                  $tipo_input = "radio";
               case "C":
                  if( $tipo_input == "text" )
                     $tipo_input = "checkbox";
                  
				  $input = false;
				  echo '<div class="form-row">';
				  echo '<label class="font-weight-bold" for="'.$campo["Field"].'" class="mr-1">'.$etichetta.':</label>';
				  echo '</div>';

				  echo '<div class="form-row mb-4">';
				  for( $i=1; $i<count($elenco_parametri); $i++ ) {
					 list($id,$valore)=explode( "=", $elenco_parametri[$i] );
					 echo '<div class="form-check form-check-inline">';
					 echo '<input type="'.$tipo_input.'" class="form-check-input';
					 if( array_key_exists( "class", $html_parms ))
						 echo ' '.$html_parms["class"];
					 echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
					 echo ' name="'.$campo["Field"].'" id="'.$campo["Field"].'_'.$id.'" value="'.$id.'"';
					 if( $id == $valore_campo )
						echo ' checked';
					
					 if( $readonly )
						 echo ' readonly';
					 
					 if( $required )
						 echo ' required';
					 echo '/>';
					 echo '<label for="'.$campo["Field"].'_'.$id.'" class="form-check-label">'.$valore.'</label></div>';
				  }
                  break;
				  
               case "T":
			      if( !array_key_exists( 1, $elenco_parametri ))
					  $elenco_parametri[1] = 5;
				  
                  echo '<div class="form-row">';
				  echo '<div class="col">';
                  echo '<div class="form-group">';
				  if( !$PARAMS->layout_agid )
						echo '<label class="font-weight-bold" for="'.$campo["Field"].'" class="mr-1">'.$etichetta.':</label>';
				  echo '<textarea rows="'.$elenco_parametri[1].'" class="w-100';
				  if( array_key_exists( "class", $html_parms ))
					 echo ' '.$html_parms["class"];
					 echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
				  echo ' name="'.$campo["Field"].'"';
				  					 
			      if( $readonly )
				    echo ' disabled';
				
			      if( $required )
				    echo ' required';
				
				  echo '/>'.$valore_campo.'</textarea>';
				  if( $PARAMS->layout_agid )
					echo '<label class="font-weight-bold" for="'.$campo["Field"].'" class="mr-1">'.$etichetta.':</label>';
				  echo '</div>';
				  echo '</div>';
                  $input = false;			   
				  break;
				  
               case "L":
                  echo '<div class="form-row"><div class="bootstrap-select-wrapper col-12 mb-4';
				  if( $PARAMS->layout_agid ) echo ' pb-3';
				  echo '">'; 
                  echo '<label class="font-weight-bold" for="'.$campo["Field"].'">'.$etichetta.'</label>';
                  echo '<select id="'.$campo["Field"].'" name="'.$campo["Field"].'"';
                  if( array_key_exists(5, $elenco_parametri ) && !is_numeric( $elenco_parametri[4] )) {
                     // Dinamico
                     echo 'title="'.$elenco_parametri[4].'" class="selectpicker form-control';
					 if( array_key_exists( "class", $html_parms ))
					    echo ' '.$html_parms["class"];
					 echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
					 
					 if( $readonly )
						 echo ' disabled';
					 
					 if( $required )
						 echo ' required';
					 
					 echo ' data-live-search="true">';
                  } else {
					 echo ' class="form-control';
					 if( array_key_exists( "class", $html_parms ))
					    echo ' '.$html_parms["class"];
					 echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
						
					 if( $readonly )
						 echo ' disabled';
					 
					 if( $required )
						 echo ' required';
					 
					 echo '><option value="">Selezionare...</option>';
				  }
 
                  $sql = "SELECT ".$elenco_parametri[2].",".$elenco_parametri[3];
				  if( array_key_exists( 4, $elenco_parametri ) && is_numeric(substr($elenco_parametri[4],0,1)) ) {
					for( $i=5; $i<=(4+intval(substr($elenco_parametri[4],0,1))); $i++ )
					  $sql .= ",".$elenco_parametri[$i];
				  }
				  $sql .= " FROM ".$elenco_parametri[1];
				  
				  if( array_key_exists(5, $elenco_parametri ) ) {
						if( array_key_exists(6, $elenco_parametri ) )
						   $sql .= " ORDER BY ".$elenco_parametri[6];
                  } else {
					  if( array_key_exists(4, $elenco_parametri ))
						$sql .= " ORDER BY ".$elenco_parametri[4];
					  else
                         $sql .= " ORDER BY ".$elenco_parametri[3];
				  }
                     
                  $elenco_option = exec_sql( $sql );   
			      foreach( $elenco_option as $option ) {
			         echo '<option value="'.$option[$elenco_parametri[2]].'"';
			         if( $valore_campo == $option[$elenco_parametri[2]] )
			            echo ' selected';
			         echo '>';
					 if( strtotime($option[$elenco_parametri[3]]) )
						 echo date("d-m-Y", strtotime($option[$elenco_parametri[3]]));
					 else 
						 echo $option[$elenco_parametri[3]];
					 
					 if( array_key_exists(4, $elenco_parametri) && is_numeric( $elenco_parametri[4] ))
						 for( $i=5; $i<=(4+intval(substr($elenco_parametri[4],0,1))); $i++ ) {
							 echo " ".$option[$elenco_parametri[$i]];							 
						 }
					 
					 '</option>';   
			      }
                  echo '</select></div>';
                  $input = false;  
				  break;
				  
               case "FL":
                  echo '<div class="form-row"><div class="bootstrap-select-wrapper col-12 mb-4';
				  if( $PARAMS->layout_agid ) echo 'mt-3';
				  echo '">';				  
                  echo '<label class="font-weight-bold" for="'.$campo["Field"].'">'.$etichetta.'</label>';
                  echo '<select id="'.$campo["Field"].'" name="'.$campo["Field"].'"';
                  if( array_key_exists(6, $elenco_parametri )) {
                     // Dinamico
                     echo 'title="'.$elenco_parametri[5].'" class="selectpicker w-100';
					 if( array_key_exists( "class", $html_parms ))
					    echo ' '.$html_parms["class"];
					    echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
					
					 if( $readonly )
						 echo ' disabled';
					 
					 if( $required )
			            echo ' required';
					
					 echo ' data-live-search="true">';
                  } else {
					 echo ' class="form-control';
					 if( array_key_exists( "class", $html_parms ))
					    echo ' '.$html_parms["class"];
					    echo '"';
					 foreach( $html_parms as $param => $value )
						if( strtolower($param) != "class" )
							echo ' '.$param.'="'.$value.'"';
					
                     if( $readonly )
						 echo ' disabled';
					 
					 if( $required )
			            echo ' required';
					
					 echo '><option value="">Selezionare...</option>';
				  }
 
				  if( array_key_exists( "FRWK_FILTER", $_SESSION ))
					  $valore_filtro = $_SESSION["FRWK_FILTER"];
				  else
					  $valore_filtro = "''";
				  
                  $sql = "SELECT ".$elenco_parametri[2].",".$elenco_parametri[3]." FROM ".$elenco_parametri[1]." WHERE ".$elenco_parametri[4]." IN (".$valore_filtro.")";			 
				  if( array_key_exists(5, $elenco_parametri ) ) {
					  if( array_key_exists(6, $elenco_parametri ))
						$sql .= " ORDER BY ".$elenco_parametri[6];
                  } else {
					  if( array_key_exists(4, $elenco_parametri ))
						$sql .= " ORDER BY ".$elenco_parametri[4];
					  else
                         $sql .= " ORDER BY ".$elenco_parametri[3];
				  }
                     
                  $elenco_option = exec_sql( $sql );   
			      foreach( $elenco_option as $option ) {
			         echo '<option value="'.$option[$elenco_parametri[2]].'"';
			         if( $valore_campo == $option[$elenco_parametri[2]] )
			            echo ' selected';
			         echo '>'.$option[$elenco_parametri[3]].'</option>';   
			      }
                  echo '</select></div>';
				
                  $input = false;      
            }
         }
         if( $input == true ) { 
            echo '<div class="form-row">';
			if( $PARAMS->layout_agid ) {
				if( $tipo_input == "date" )
					echo '<div class="it-datepicker-wrapper">';
			}
			echo '<div class="form-group col-12">';
            if( !$PARAMS->layout_agid ) {
				echo '<label class="font-weight-bold" for="'.$campo["Field"].'">'.$etichetta.'</label>';
			} else {
			}
			echo '<input type="'.$tipo_input.'" class="form-control';
			if( array_key_exists( "class", $html_parms ))
				echo ' '.$html_parms["class"];
				echo '"';
			foreach( $html_parms as $param => $value )
				if( strtolower($param) != "class" )
					echo ' '.$param.'="'.$value.'"';
			echo ' id="'.$campo["Field"].'" name="'.$campo["Field"].'" placeholder="Inserire '.$etichetta.'"';
			if( $valore_campo != "" && $force_insert == false ) {
			   echo ' value="';
			   
			   if( $tipo_input == "date")
			      echo inverti_data($valore_campo,"A","I");
			   else
			      echo $valore_campo;
			   echo '"';
		   	}				
			if( $readonly )
				echo ' readonly';
			
		    if( $required )
			    echo ' required';
			
			echo '>';
			if( $PARAMS->layout_agid ) {
				echo '<label class="font-weight-bold" for="'.$campo["Field"].'">'.$etichetta.'</label>';
				if( $tipo_input == "date" )
					echo '</div>';
			}
			echo '</div>';
         }
         echo '</div>';
      }
	  
	  echo $html_post;
	  
	  echo '</div>';
	  
   }   
   echo '</div>';
   
   if( $UPDATE )
      echo '<button type="submit" name="FRWK_action" value="save" class="btn btn-primary">SALVA';
   else
      echo '<button type="submit" name="FRWK_action" value="insert" class="btn btn-success">INSERISCI';   
   echo '</button>';
   echo '</form>';
   if( $PARAMS->form_footer != "" )
	   echo $PARAMS->form_footer;
   echo '</div>';
   $form = ob_get_clean();
   
   if( !$PARAMS->no_title ) {
	   if( $PARAMS->custom_title == "" ) {
		   echo '<div class="row mb-4 border-bottom">';
		   if( $UPDATE )
			  echo '<h4>AGGIORNA DATI</h4>';
		   else
			  echo '<h4>INSERISCI NUOVI VALORI</h4>';   
		   echo '</div><br/>';
	   } else {
		   echo '<div class="row mb-4 border-bottom"><h4>'.$PARAMS->custom_title.'</h4></div><br/>';
	   }
   }
   echo $form;
 
}



function log_errori( $codice, $messaggio, $display = true ) {


   $LOG_FILE = $_SERVER["DOCUMENT_ROOT"].substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],"/"))."/log/error.log";

   $file = fopen( $LOG_FILE, "a" );

   $RECORD = date("Y-m-d H:i:s")." ".$codice."|".$messaggio."\n";

   fwrite( $file, $RECORD );
   fclose( $file );

   if( $display )
      echo "ERR:$codice|$messaggio";

   die();
}

function log_attivita( $codice, $messaggio, $display = false ) {
   global $LOG_ATTIVITA;

   date_default_timezone_set ( "Europe/San_Marino" );
   if( !$LOG_ATTIVITA )
     return;

   $LOG_FILE = $_SERVER["DOCUMENT_ROOT"].substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],"/"))."/log/activity-".date("Ymd").".log";

   $file = fopen( $LOG_FILE, "a" );

   $RECORD = date("Y-m-d H:i:s")." ".$codice."|".$messaggio."|".$_SERVER["REMOTE_ADDR"]."\n";

   fwrite( $file, $RECORD );
   fclose( $file );

   if( $display ) {
      echo "LOG:$codice|$messaggio";
      die();
   }
}

function splitQueryText($query) {
    // the regex needs a trailing semicolon
    $query = trim($query);

    if (substr($query, -1) != ";")
        $query .= ";";

    // i spent 3 days figuring out this line
    preg_match_all("/(?>[^;']|(''|(?>'([^']|\\')*[^\\\]')))+;/ixU", $query, $matches, PREG_SET_ORDER);

    $querySplit = array();

    foreach ($matches as $match) {
        // get rid of the trailing semicolon
        $querySplit[] = substr($match[0], 0, -1);
    }

    return $querySplit;
}

function backup_database( $database, $prefisso_tabelle, $path="" ) {
   
   $tables = exec_sql( "SHOW TABLES FROM ".$database." LIKE '".$prefisso_tabelle."%'" );
   
 	  
   $name = $_SERVER["DOCUMENT_ROOT"].$path."backup_".date("Y_m_d_H_i").".zip";
   $i=0;
   while( file_exists( $name ) ) {
	  $i++;
	  $name = $_SERVER["DOCUMENT_ROOT"].$path."backup_".date("Y_m_d_H_i")."_".$i.".zip";
   } 
   
   $zip = new ZipArchive();

   if ($zip->open($name, ZipArchive::CREATE)!==TRUE) {
	   exit("Errore in fase di creazione dell'archivio di backup <$name>\n");
   }
   foreach( $tables as $table ) {
	  $i=1;
	  $tabella = current($table);
	  
	  $bck  = "/* EXPORT TABLE $tabella ".date("d-m-Y"). " FROM ".$database." */\n\n";
	  $bck .= "DROP TABLE IF EXISTS ".$tabella.";\n\n";
	  $create = exec_sql( "SHOW CREATE TABLE ".$tabella, true );

	  $bck .= $create["Create Table"].";\n\n";
	  
	  $campi = exec_sql( "SHOW FIELDS FROM ".$tabella );
	  $dati  = exec_sql( "SELECT * FROM $tabella" );
	  
	  foreach( $dati as $row ) { 
	     $bck .= "INSERT INTO $tabella VALUES(";
	     $primo = true;
	     foreach( $campi as $campo ) {
	        $valore = $row[$campo["Field"]];
	        
	        $valore = addslashes( $valore );
	        $valore = str_replace( "\n", "\\n", $valore );
	        
	        if( $primo )
	           $primo = false;
	        else
	           $bck .= ", ";
	           
	        $bck .= "'".$valore."'";      
	     }
	     $bck .= ");\n";
	  }
	  
	  $zip->addFromString($tabella.".sql", $bck );
   } 
   $zip->close();
}

function random_string() {
	$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$pass = "";
	for ($i = 0; $i < 8; $i++) {
		$n = rand(0, strlen($alphabet)-1);
		$pass .= substr($alphabet, $n, 1);
	}
	return $pass;
}


if( array_key_exists( "FRWK_action", $_POST )) {
   if( $_POST["FRWK_action"] == "save" || $_POST["FRWK_action"] == "insert" ) {
      if( session_id() == '' )
         session_start();
     
      $trace = debug_backtrace();

      if( !array_key_exists( "FRWK_session", $_POST ))
         die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore nella gestione della Sessione (Tabella non definita#1)' );   
         
      if( !array_key_exists( "FRWK_tbl_".$_POST["FRWK_session"], $_SESSION ))
         die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore nella recupero dei dati dalla Sessione' );  
         
      $tabella_DB = $_SESSION["FRWK_tbl_".$_POST["FRWK_session"]];
      
      $campi_tabella = exec_sql( "SHOW FULL FIELDS FROM $tabella_DB" );
      
      $flag_update = true;  // Se i campi chiave hanno tutti un valore... UPDATE altrimenti INSERT
      
      $sql_update    = "UPDATE ".$tabella_DB." SET ";
      $sql_where     = "";
      $update_fields = 0;
      $sql_insert    = "INSERT INTO ".$tabella_DB."(";
      $sql_values    = "";
      $insert_fields = 0;
      
      foreach( $campi_tabella as $campo ) {
		 $valore_campo = "***!!!***NULL***!!!***";
		 $trovato = 0;
		 if( array_key_exists( $campo["Field"], $_POST )) {
			$valore_campo = $_POST[$campo["Field"]];
			
			if( strlen( $valore_campo ) > 50 && $campo["Type"] != "text" )
				$valore_campo = substr( $valore_campo,0,50 );
			$trovato++;
		 }
	   
		 if( array_key_exists( $campo["Field"], $_GET )) {
			$valore_campo = $_GET[$campo["Field"]]; 
			
			if( strlen( $valore_campo ) > 50 && $campo["Type"] != "text" )
				$valore_campo = substr( $valore_campo,0,50 );
			$trovato++;
		 }
	  
		
		 if( $valore_campo != "***!!!***NULL***!!!***" && ($campo["Type"]=="date" || $campo["Type"]=="timestamp") )
		    $valore_campo = inverti_data( $valore_campo, "A", "I" );

         if( $valore_campo != "***!!!***NULL***!!!***" ) {
            if( $campo["Key"]=="PRI" ) {
			   if( $sql_where != "" )
				  $sql_where .= " AND ";
			   
			   $sql_where .= $campo["Field"]."='".addslashes($valore_campo)."'";
			} else {
			   if( $update_fields > 0 )
			      $sql_update .= ", ";
			      
			   $sql_update .= $campo["Field"]."='".addslashes($valore_campo)."'";   
			   $update_fields++;
			}
			
            if( $insert_fields > 0 ) {
               $sql_insert .= ", ";
               $sql_values .= ", ";
            }
            
            $sql_insert .= $campo["Field"];
            $sql_values .= "'".addslashes($valore_campo)."'";
            
            $insert_fields++;   
         } else if( $campo["Key"]=="PRI" ) $flag_update = false;
      }  
      $sql_update = $sql_update . " WHERE " . $sql_where . ";";
      $sql_insert = $sql_insert . ") VALUES (" . $sql_values . ");"; 

      if( $flag_update )
         $sql = $sql_update;
      else {
         $sql = $sql_insert;
       
      }   
	
      if( exec_sql( $sql ) )
          $FRWK_MESSAGE = '<div class="alert alert-success">REGISTRAZIONE EFFETTUATA CON SUCCESSO!</div>';           
   }
}

if( array_key_exists( "FRWK_action", $_GET )) { 
   if( array_key_exists( "CALLBACK_".$_GET["FRWK_action"], $_SESSION ) && $_SESSION["CALLBACK_".$_GET["FRWK_action"]]!="" ) {
      $_GET["FRWK_action"]=="";

      if( function_exists( $_SESSION["CALLBACK_".$_GET["FRWK_action"]] )) {
         call_user_func( $_SESSION["CALLBACK_".$_GET["FRWK_action"]] );
      }
   }
   
   if( $_GET["FRWK_action"] == "delete" ) {
      if( session_id() == '' )
         session_start();
       
      $trace = debug_backtrace();
         
      if( !array_key_exists( "t", $_GET ))
         die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore nella gestione della Sessione (Tabella non definita#2)' );   

      if( !array_key_exists( "FRWK_tbl_".$_GET["t"], $_SESSION ))
         die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore nella recupero dei dati dalla Sessione' );  
         
      $tabella_DB = $_SESSION["FRWK_tbl_".$_GET["t"]];
      
      $campi_tabella = exec_sql( "SHOW FULL FIELDS FROM $tabella_DB" );
      
      $sql_where = ""; 
      foreach( $campi_tabella as $campo ) {
		 $valore_campo = "";
	  
		 $trovato = 0;
		 if( array_key_exists( $campo["Field"], $_POST )) {
			$valore_campo = $_POST[$campo["Field"]];
			$trovato++;
		 }
	   
		 if( array_key_exists( $campo["Field"], $_GET )) {
			$valore_campo = $_GET[$campo["Field"]]; 
			$trovato++;
		 }
	  
		
		 if( $valore_campo != "" && ($campo["Type"]=="date" || $campo["Type"]=="timestamp") )
		    $valore_campo = inverti_data( $valore_campo );

         if( $valore_campo != "" ) {
            if( $campo["Key"]=="PRI" ) {
			   if( $sql_where != "" )
				  $sql_where .= " AND ";
			   
			   $sql_where .= $campo["Field"]."='".addslashes($valore_campo)."'";
			} 
         } 
      }
      
      $sql = "DELETE FROM $tabella_DB WHERE ".$sql_where;
      exec_sql( $sql );     
   }
}
?>
