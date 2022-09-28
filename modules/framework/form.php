<?php

include( __DIR__ ."/wf_event_icons.php" );
include( __DIR__ ."/form_event.php" );
include( __DIR__ ."/form_element.php" );
include( __DIR__ ."/form_datatable.php" );


// FORM HTML...
class WEBFLY_FORM {
	protected  $name;
	protected  $method;
	protected  $action;
	protected  $elements;
	protected  $form_data;
	protected  $params;
	protected  $DB_MANAGER;
	protected  $hidden;
	protected  $collapse;
	protected  $source_table;
	protected  $form_events;
	
	public  $server;
	public  $upload_form;
	public  $vertical_style;
	public  $autosave;
	
	function __construct( $name="", $action = "", $method = "POST", $DB_MANAGER = null, $source_table="" ) {
		$this->name   = $name;
		$this->method = $method;
		$this->action = $action;
		
		$this->collapse     	= array();
		$this->elements   		= array();
		$this->params     		= array();
		$this->form_events     	= array();
		$this->form_data    	= array();
		$this->hidden			= array();
		$this->upload_form 		= false;
		$this->vertical_style	= false;
		$this->DB_MANAGER 		= $DB_MANAGER;
		
		$this->server			= "/runtime/server.php";
		
		if( $source_table != "" ) {
			$this->source_table = $source_table;
		} else $this->source_table = "";
		
		$this->autosave = false; // Inserisce automaticamente il bottone salva...
		
	}
	
	function form_id() {
		return $this->name;
	}
	
	function values( $form_data ) {
		if( !is_array( $form_data ))
			throw new Exception( "I Parametri forniti devono essere degli array (0x90001)", "90001" );

		$FORM_FIELD = false;
		if( is_array( $this->form_data )) {
			foreach( $form_data as $key => $value ) { 
				if( array_key_exists( $key, $this->form_data ) && $this->form_data[$key] != $value ) {
					$this->form_data[$key] = $value;
					$FORM_FIELD = true;
				}
			}
		}
		if( $FORM_FIELD ) {
			foreach( $form_data as $key => $value ) { 
				foreach( $this->elements as $id => $form_element ) {
					if( $form_element->name == $key && $this->elements[$id]->value != $value ) { 
						$this->elements[$id]->value = $value;
					}
				}
			}
		} else {
			if( is_array( $this->params )) {
				foreach( $form_data as $key => $value ) {
					if( array_key_exists( $key, $this->params ) && $this->params[$key] != $value ) {
						$this->params[$key] = $value;
					}
				}
			}
		}
	}
	
	function add_param( $name, $value = null ) {
		if( is_null( $value ) ) {
			if( array_key_exists( $name, $this->form_data ))
				$this->params[$name] = $this->form_data[$name];
			else
				$this->params[$name] = "";
		} else $this->params[$name] = $value;
	}
	
	function add_event( $event ) {
		if( !is_object($event) || get_class( $event ) != "FORM_EVENT" ) 
			throw new Exception( "L'evento deve essere un oggetto di classe FORM_EVENT (0x90010)", "90010" );
	
		$type = $this->element_type( $event->event_id() );
		if( $type > 0 )
			throw new Exception( "Il nome ".$field->name." è già presente nel form '".$this->name."'(0x92006-".$type.")", "92006" );

		
		return $this->form_events[] = $event;
	}
	
	function hide_fields( $list ) {
		$elenco = explode( ",", $list );
		foreach( $elenco as $campo )
			$this->hidden[trim($campo)] = true;
	}
	
	function remove_field( $name ) {
		foreach( $this->elements as $id => $element )
			if( $element->name == $name ) {
				unset( $this->elements[$id] );
				break;
			}
		
		if( array_key_exists( $name, $this->params ))
			unset( $this->params[$name] );
	}
	
	function from_table( $table_name = "" ) {
		
		if( $table_name == "" )
			$table_name = $this->source_table;
		
		if( $table_name == "" )
			throw new Exception( "Non è possibile costruire un form dalla tabella senza specificare il nome della tabella di partenza (0x90099)", "90099" );			
		
		if( is_null( $this->DB_MANAGER ))
			throw new Exception( "Non è possibile costruire un form senza DB-MANAGER (0x90002)", "90002" );	
		
		$campi_tabella = $this->DB_MANAGER->exec_sql( "SHOW FULL FIELDS FROM $table_name" );

		foreach( $campi_tabella as $campo ) {
			$show_field = true;
			
			if( $campo["Key"]=="PRI" || $campo["Comment"]=="HIDE" ) {
				$show_field = false;
				
				if( $campo["Key"]=="PRI" ) {
					$this->add_param( $campo["Field"] );
					$this->form_data[$campo["Field"]] = "";
				}
			}
			
			if( array_key_exists( $campo["Field"], $this->hidden ))
				$show_field = false;
			
			if( $show_field ) {
				$elemento_form = new FORM_ELEMENT;
				
				$elemento_form->name = $campo["Field"];
				if( $campo["Comment"]!="" ) {
					$parametri_estesi = "";
					if( strpos( $campo["Comment"], ";" )!==false ) {
						if( substr_count($campo["Comment"], ";" ) >= 2 ) {
							list( $etichetta, $parametri, $parametri_estesi ) = explode( ";", $campo["Comment"] );
						} else {
							list( $etichetta, $parametri ) = explode( ";", $campo["Comment"] );
						}
					} else {
						$etichetta 			= $campo["Comment"];
						$parametri 			= "";
					}	  
					
					$elemento_form->label = $etichetta;
					
					$elenco_parametri = explode( ",",$parametri );
					switch( $elenco_parametri[0] ) {
						case "P":
							$elemento_form->type = FORM_ELEMENT_TYPE::PASSWORD;
							break;
							
						case "E":
							$elemento_form->type = FORM_ELEMENT_TYPE::EMAIL;
							break;
							
						case "N":
							$elemento_form->type = FORM_ELEMENT_TYPE::NUMERICO;
							
							if( substr($campo["Type"],0,7)=="decimal" ) { 
								$elemento_form->type = FORM_ELEMENT_TYPE::NUMERICO;
								
								$caratteri = substr($campo["Type"],8, strlen($campo["Type"])-9);
								
								list( $numero, $decimali ) = explode( ",", $caratteri );
								if( intval($numero) > 0 ) {
									$elemento_form->max_length = intval($numero);
									$elemento_form->decimali   = intval($decimali);
								}
							}							
							break;
							
						case "R":
							$elemento_form->type = FORM_ELEMENT_TYPE::RADIO;
							break;
							
						case "C":
							$elemento_form->type = FORM_ELEMENT_TYPE::CHECKBOX;
							break;
							
						case "T":
							$elemento_form->type = FORM_ELEMENT_TYPE::TEXTAREA;
							break;
							
						case "L":
							$elemento_form->type = FORM_ELEMENT_TYPE::SELECT;
							break;
							
						case "T":
							$elemento_form->type = FORM_ELEMENT_TYPE::DYNAMIC_SELECT;
							break;
							
						default:
							$elemento_form->type = FORM_ELEMENT_TYPE::TESTO;
							
							if( substr($campo["Type"],0,7)=="varchar" ) { 
								$caratteri = intval(substr($campo["Type"],8, strlen($campo["Type"])-9));

								if( $caratteri > 0 )
									$elemento_form->max_length = $caratteri;
							}
							
							if( substr($campo["Type"],0,7)=="decimal" ) { 
								$elemento_form->type = FORM_ELEMENT_TYPE::NUMERICO;
								
								$caratteri = substr($campo["Type"],8, strlen($campo["Type"])-9);
								
								list( $numero, $decimali ) = explode( ",", $caratteri );
								if( intval($numero) > 0 ) {
									$elemento_form->max_length = intval($numero);
									$elemento_form->decimali   = intval($decimali);
								}
							}
					
							switch( $campo["Type"] ) {
								case "date":
									$elemento_form->type = FORM_ELEMENT_TYPE::DATA;
									break;
									
								case "time":
									$elemento_form->type = FORM_ELEMENT_TYPE::ORARIO; 
									break;
									
								case "datetime":
									$elemento_form->type = FORM_ELEMENT_TYPE::DATA_ORA;
									break;
									
								default:
									$elemento_form->type  = FORM_ELEMENT_TYPE::TESTO;
							}
							break;
					}
					$elemento_form->structure = $elenco_parametri;
					
				} else {
					$elemento_form->label = $campo["Field"];
					
					switch( $campo["Type"] ) {
						case "date":
							$elemento_form->type = FORM_ELEMENT_TYPE::DATA;
							break;
							
						case "time":
							$elemento_form->type = FORM_ELEMENT_TYPE::ORARIO; 
							break;
							
						case "datetime":
							$elemento_form->type = FORM_ELEMENT_TYPE::DATA_ORA;
							break;
							
						default:
							$elemento_form->type  = FORM_ELEMENT_TYPE::TESTO;
					}
				}
				
				// Inserisce l'elemento nella struttura...
				if( $parametri_estesi == "NL" )
					$elemento_form->end_row = true;
				else
					$elemento_form->end_row = $this->vertical_style;
				$elemento_form->DB_MANAGER = $this->DB_MANAGER;
				
				$elemento_form->parent_form = $this;
				$this->elements[] = $elemento_form;
				if( !is_array( $this->form_data ) )
					$this->form_data = array();
				
				$this->form_data[$elemento_form->name] = "";
			}
	   }
	}
	
	private function array_insert(&$array, $position, $insert)
	{
		if (is_int($position)) {
			array_splice($array, $position, 0, $insert);
		} else {
			$pos   = array_search($position, array_keys($array));
			$array = array_merge(
				array_slice($array, 0, $pos),
				$insert,
				array_slice($array, $pos)
			);
		}
	}	
	
	public function add_element( $field, $position = -1 ) {
		if( get_class( $field ) != "FORM_ELEMENT" )
			throw new Exception( "Il campo passato deve essere di tipo 'FORM_ELEMENT' (0x92004)", "92004" );
		
		$type = $this->element_type( $field->name );
		if( $type > 0 )
			throw new Exception( "Il nome ".$field->name." è già presente nel form (0x92005-".$type.")", "92005" );
			
		$field->parent_form = $this;
		
		if( $position == -1 ) {
			$this->elements[] = $field;
		} else {
			$this->array_insert( $this->elements, array( $field ) );
		}
		
		$this->form_data[$field->name] = "";
		
		return $field;
	}

	public function element_type( $name ) {
		foreach( $this->elements as $id => $form_element ) {
			if( $form_element->name == $name ) {
				return 2;
			}
		}
		
		foreach( $this->form_events as $event ) { 
			if( $event->event_id() == $name ) {
				return 1;
			}
		}
		
		return 0;
	}
	
	public function __set( $field_name, $value ) { 
		if( !property_exists( $this, $field_name ) && is_null( $this->form_data ))
			throw new Exception( "Campi non inizializzati (0x90004)", "90004" );
		
		if( property_exists( $this, $field_name )) {
			$this->$field_name = $value;
			return;
		}
		if( array_key_exists( $field_name, $this->form_data ))
			$this->form_data[$field_name] = $value;
		
		if( array_key_exists( $field_name, $this->params ))
			$this->params[$field_name] = $value;
	}	
	
	public function __get( $field_name ) { 
		foreach( $this->elements as $element ) { 
			if( $element->name == $field_name ) {
				if( $element->type == FORM_ELEMENT_TYPE::DATATABLE )
					return $element->structure[1];
				else
					return $element;
			}
		}	
/*		
		if( array_key_exists( $field_name, $this->form_data )) {
			return $this->form_data[$field_name];
		}
		
		if( array_key_exists( $field_name, $this->params ))
			return $this->params[$field_name];
*/			
		throw new Exception( "Campo $field_name non presente nel form (0x90093)", "90093" );
	}
	
	public function build() {
		$buffer = "";
		
		if( $this->method != "AJAX" )
			$buffer .= '<form id="'.$this->name.'" method="'.$this->method.'" action="'.$this->action.'"';
		else
			$buffer .= '<form id="'.$this->name.'" method="GET" data-classname="'.$this->action.'"';
		
		if( $this->upload_form )
			$buffer .= ' enctype="multipart/form-data"';
		
		if( $this->server != "" )
			$buffer .= ' data-server="'.$this->server.'"';
		$buffer .= '><fieldset>';
		
		// Campi hidden...
		if( is_array( $this->params )) {
			foreach( $this->params as $parametro => $valore ) {
				$buffer .= '<input type="hidden" id="'.$parametro.'" name="'.$parametro.'" value="';
				if( array_key_exists( $parametro, $this->form_data ))
					$buffer .= $this->form_data[$parametro];
				else
					$buffer .= $valore;
				$buffer .= '"/>';
			}
		}
		$in_row = false;
		$max_col = 6;
		$col = 0;
		if( is_array( $this->elements )) {
			if( is_array( $this->collapse ))
				$NUM_BLOCKS = 2 + count( $this->collapse );
			else {
				$NUM_BLOCKS = 2;
			}
			
			for( $step=1; $step<=$NUM_BLOCKS; $step++ ) {
				switch( $step ) {
					case 1:
						$COLLAPSE_LIST = array();
						$COLLAPSE_LIST[] = array( "ID" => "FRWK_HEADER", "LABEL" => "" );
						break;
						
					case $NUM_BLOCKS:
						$COLLAPSE_LIST = array();
						$COLLAPSE_LIST[] = array( "ID" => "FRWK_FOOTER", "LABEL" => "" );
						break;
						
					default: 
						$buffer .= '<div class="nav-tabs-wrapper">';
						$buffer .= '<ul class="nav nav-tabs nav-tabs-cards" id="'.$this->collapse[$step-2]["NAME"].'" role="tablist">';
						$buffer .= '<li class="webfly-nav-filler"></li>';
						
						foreach( $this->collapse[$step-2]["TABS"] as $id => $tab ) {
							$buffer .= '<li class="nav-item">';
							$buffer .= '<a class="nav-link';
							
							if( $tab["ID"] == $this->collapse[$step-2]["ACTIVE_TAB"] )
								$buffer .= ' active';
							
							$buffer .= '" id="'.$tab["ID"].'_tab" data-toggle="tab" href="#" data-target="#'.$tab["ID"].'" role="tab"';
							$buffer .= ' aria-controls="'.$tab["ID"].'"';
							$buffer .= ' aria-selected="';
							if( $tab["ID"] == $this->collapse[$step-2]["ACTIVE_TAB"] )
								$buffer .= 'true';
							else
								$buffer .= 'false';
							$buffer .= '"';
							$buffer .= '>'.$tab["LABEL"];
							$buffer .= '<span id="'.$tab["ID"].'_badge" class="ml-1">';
							if( array_key_exists( "BADGE", $tab ) && $tab["BADGE"] != "" )
								$buffer .= $tab["BADGE"];
							$buffer .= '</span>';
							$buffer .= '</a>';
							$buffer .= '</li>';			
						}
						$buffer .= '<li class="nav-item-filler"></li>';
						$buffer .= '</ul>';
						$COLLAPSE_LIST = $this->collapse[$step-2]["TABS"];
						break;
				}
				
				if( count( $this->collapse ) > 0 && $step != 1 && $step != $NUM_BLOCKS )
					$buffer .= '<div class="tab-content pt-4" id="'.$this->collapse[$step-2]["NAME"].'_content">'; 	
				
				$primo = true;
				foreach( $COLLAPSE_LIST as $CURRENT_COLLAPSE ) {
					if( $step != 1 && $step != $NUM_BLOCKS ) {
						if( $primo )
							$primo = false;
						else {
							if( $in_row ) { 
								$buffer .= '</div>';
								$in_row = false;
							}
						}
						
						if( count( $this->collapse ) > 0 ) {
							$buffer .= '<div class="tab-pane fade pt-4';
														
							if( $CURRENT_COLLAPSE["ID"] == $this->collapse[$step-2]["ACTIVE_TAB"] )
								$buffer .= ' show active';
							
							$buffer .= '" id="'.$CURRENT_COLLAPSE["ID"].'" role="tabpanel" aria-labelledby="'.$CURRENT_COLLAPSE["ID"].'_tab">';
						}
					}
					$CURRENT_COLLAPSE_NAME = $CURRENT_COLLAPSE["ID"];
					
					foreach( $this->elements as $element ) {
						if( $element->collapse == $CURRENT_COLLAPSE_NAME ) {
							if( array_key_exists( $element->name, $this->form_data ))
								$element->value = $this->form_data[$element->name];
							
							if( array_key_exists( $element->name, $this->params ))
								$element->value = $this->params[$element->name];
							
							$buffer_field = $element->build();
							if( $buffer_field != "" ) {
								if( !$in_row ) {
									$buffer .=  '<div class="form-row">';
									$in_row = true;
									$col = 0;
								}	
								
								$col++;
								if( $col > $max_col ) {
									$buffer .=  '</div><div class="form-row">';
									$in_row = true;
									$col = 1;
								}
								$buffer .= $buffer_field;
								
								if( $element->end_row ) {
									$buffer .= '</div>';
									$in_row = false;
								}
							}
						}
					}
					
					if( count( $this->collapse ) > 0 && $step != 1 && $step != $NUM_BLOCKS ) {
						$buffer .= '</div>';
					}
										
				}
				
				if( count( $this->collapse ) > 0 && $step != 1 && $step != $NUM_BLOCKS )
					$buffer .= '</div>';
 				
				if( count( $this->collapse ) > 0 && $step == $NUM_BLOCKS )
					$buffer .= '</div>'; 
				
				if( $in_row ) {
					$buffer .=  '</div>';	
					$in_row = false;
				}
			}
		}
		if( $in_row )
			$buffer .=  '</div>';
		
		
		// Eventi...
		$buffer .= '<div class="form-row justify-content-end" id="event_bar">';
		if( !is_array( $this->form_events ) || count($this->form_events) == 0 ) {
			// Evento base salva...
			
			if( $this->autosave ) {
				$buffer .= '<button type="button" class="btn btn-success btn-sm" data-event="save_data">';
				$buffer .= '<i class="fa-solid fa-floppy-disk mr-2"></i>';
				$buffer .= 'Salva';
				$buffer .= '</button>';
			}
		} else {
			$id_evento = 0;
			foreach( $this->form_events as $evento ) {
				$id_evento++;
				if( count( $this->form_events ) > 1 && $id_evento <= count( $this->form_events ) )
					$buffer .= $evento->build( "mr-2" );
				else
					$buffer .= $evento->build();
			}
		}
		$buffer .= '</div>';
		$buffer .= '</fieldset></form>';
		
		return $buffer;
	}
	
	public function build_element( $element_name ) {
		foreach( $this->elements as $elemento ) {
			if( $elemento->name == $element_name )
				return $elemento->build();
		}
		
		foreach( $this->form_events as $evento ) {
			if( $evento->event_id() == $element_name )
				return $evento->build();
		}
		
		return "";
	}
	
	public function render() {
		echo $this->build();
	}
	
	public function structure() {
		$this->from_table();
	}
	
	public function save() {
		// Salva i dati nel database...
		if( is_null( $this->DB_MANAGER ))
			throw new Exception( "Non è possibile salvare un form senza DB-MANAGER (0x90003)", "90003" );	

		if( $this->source_table == "" )
			throw new Exception( "Non è possibile salvare un form senza abbinarlo ad una tabella del DB", "90004" );	
			
		$campi_tabella = $this->DB_MANAGER->exec_sql( "SHOW FULL FIELDS FROM ". $this->source_table );

		$SQL_INSERT = "";
		$SQL_VALUES = "";
		$SQL_UPDATE = "";
		$SQL_CHECK  = "";
		$SQL_WHERE  = "";
		
		foreach( $campi_tabella as $campo ) {			
			if( $campo["Key"]=="PRI" ) {
				if( $SQL_WHERE != "" )
					$SQL_WHERE .= " AND ";
				
				$SQL_WHERE .= $campo["Field"] . "='";
				if( array_key_exists( $campo["Field"], $this->form_data ))
					$SQL_WHERE .= $this->form_data[$campo["Field"]];
				$SQL_WHERE .= "'";
			} 

			if( $campo["Extra"] == "" ) {			
				if( array_key_exists( $campo["Field"], $this->form_data )) {
					if( $SQL_UPDATE != "" ) {
						$SQL_INSERT .= ", ";
						$SQL_VALUES .= ", ";
						$SQL_UPDATE .= ", ";
					}
					
					
					$valore = "";
					if( $campo["Type"] == "timestamp" || $campo["Type"] == "DATE" || $campo["Type"] == "DATETIME" ) {
						// Formattazione Campo data...
						$date_time = new DateTime($this->form_data[$campo["Field"]]);
							
						if( $campo["Type"] == "DATE" )
							$valore = $date_time->format("Y-m-d");
						else
							$valore = $dati_time->format("Y-m-d H:i:s");
					} else {
						$valore = $this->form_data[$campo["Field"]];
					}
					
					$SQL_UPDATE .= $campo["Field"]."='".addslashes($valore)."'";
					$SQL_INSERT .= $campo["Field"];
					$SQL_VALUES .= "'".addslashes($valore)."'";
				}
			}
		}
		
		if( $SQL_WHERE != "" ) {
			$SQL_WHERE = " WHERE ".$SQL_WHERE;
			$SQL_CHECK = "SELECT COUNT(*) AS NUMERO FROM ".$this->source_table.$SQL_WHERE;
			
			$row = $this->DB_MANAGER->exec_sql( $SQL_CHECK, true );
			if( array_key_exists( "NUMERO", $row ) && $row["NUMERO"] > 0 )
				$UPDATE = true;
			else
				$UPDATE = false;
			
		} else $UPDATE = false;
		
		if( $UPDATE ) {
			$SQL_UPDATE = "UPDATE ".$this->source_table." SET " . $SQL_UPDATE . $SQL_WHERE;
			$this->DB_MANAGER->exec_sql( $SQL_UPDATE );
			return -1;
		} else {
			if( $SQL_INSERT != "" ) {
				$SQL_INSERT = "INSERT INTO ".$this->source_table."(".$SQL_INSERT.") VALUES (". $SQL_VALUES .")";
				$insert_id = $this->DB_MANAGER->exec_sql( $SQL_INSERT );
				return $insert_id;
			} else return -2;
		}
		
		return 0;
	}
	
	function set_active_tab( $tab_id, $name="COLLAPSE" ) {
		global $WEBFLY;
		
		if( !is_array( $this->collapse ))
			$this->collapse = array();
		
		$trovato = false;
		foreach( $this->collapse as $ID => $COLLAPSE )
			if( $COLLAPSE["NAME"] == $name ) {
				$ID_COLLAPSE = $ID;
				$trovato = true;
				break;
			}
			
		if( $trovato ) {
			foreach( $this->collapse[$ID_COLLAPSE]["TABS"] as $tab ) {
				if( $tab["ID"] == $tab_id )
					$this->collapse[$ID_COLLAPSE]["ACTIVE_TAB"] = $tab_id;
					$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name] = $tab_id; 
			}
		}
	}
	
	function add_collapse( $tabs_id, $tabs_label, $name="COLLAPSE" ) {
		global $WEBFLY;
		
		if( !is_array( $this->collapse ))
			$this->collapse = array();
		
		$trovato = false;
		foreach( $this->collapse as $ID => $COLLAPSE )
			if( $COLLAPSE["NAME"] == $name ) {
				$ID_COLLAPSE = $ID;
				$trovato = true;
				break;
			}
			
		if( !$trovato ) {
			$new_collapse = array();
			$new_collapse["NAME"] = $name;
			$new_collapse["TABS"] = array();
			
			if( array_key_exists( "WEBFLY_FORMS_ACTIVE_TAB", $WEBFLY->SESSION )) {
				if( array_key_exists( $this->name, $WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"] )) {
					if( !array_key_exists( $name, $WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name] ))
						$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name] = "";
					
					$new_collapse["ACTIVE_TAB"] = $WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name]; 
				} else {					
					$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name] = array();
					$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name] = "";
				}
			} else {
				$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"] = array();
				$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name] = array();
				$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name] = "";
			}
			
			$ID_COLLAPSE = count( $this->collapse );
			$this->collapse[] = $new_collapse;
		}
		
		$tabs_list = array();
		if( strpos( $tabs_id, "," ) === false )
			$tabs_list[0] = $tabs_id;
		else
			$tabs_list = explode( ",", $tabs_id );
		
		$labels_list = array();
		
		if( strpos( $tabs_label, "," ) === false )
			$labels_list[0] = $tabs_label;
		else
			$labels_list = explode( ",", $tabs_label );
		
		if( count( $tabs_list ) != count( $labels_list ))
			throw new Exception( "Il numero di ID ed il numero di etichette deve essere uguale (0x95010)", "95010" );
		
		foreach( $tabs_list as $id => $tab_id ) {

			foreach( $this->collapse[$ID_COLLAPSE]["TABS"] as $tab )
				if( $tab["ID"] == $tab_id )
					throw new Exception( "ID ".$tab_id." duplicato nella creazione dell'oggetto Collapse (0x95020)", "95020" );
				
			if( !array_key_exists( "ACTIVE_TAB", $this->collapse[$ID_COLLAPSE] ) || ($this->collapse[$ID_COLLAPSE]["ACTIVE_TAB"]=="") ) {
				$this->collapse[$ID_COLLAPSE]["ACTIVE_TAB"] = $tab_id;
				$WEBFLY->SESSION["WEBFLY_FORMS_ACTIVE_TAB"][$this->name][$name] = $tab_id; 
			}
			$new_tab = array();
			$new_tab["ID"]    = $tab_id;
			$new_tab["LABEL"] = $labels_list[$id]; 
			$this->collapse[$ID_COLLAPSE]["TABS"][] = $new_tab;
		}
	}
	
	function set_badge( $tab, $badge, $collapse = "COLLAPSE" ) {
		
		if( !is_array( $this->collapse ))
			$this->collapse = array();
		
		$trovato = false;
		foreach( $this->collapse as $ID => $COLLAPSE )
			if( $COLLAPSE["NAME"] == $name ) {
				$ID_COLLAPSE = $ID;
				$trovato = true;
				break;
			}

		if( !$trovato ) 
			throw new Exception( "Il TAB ".$tab." non esiste nell'elemento ".$COLLAPSE."' (0x96020)", "96020" );
		
		$trovato = false;
		foreach( $this->collapse[$ID_COLLAPSE]["TABS"] as $id => $tab ) {
			if( $tab["ID"] == $tab ) {
				$this->collapse[$ID_COLLAPSE]["TABS"][$id]["BADGE"] = '<span class="badge neutral-2-bg text-secondary">'.$badge.'</span>';
				$trovato = true;
				break;
			}
		}			

		if( !$trovato ) 
			throw new Exception( "Il TAB ".$tab." non esiste (0x96021)", "96021" );
	}
	
	function assign_collapse( $tab_id, $field_list, $collapse="" ) {
		if( strpos( $field_list, "," ) !== false )
			$elenco_campi = explode( ",", $field_list );
		else {
			$elenco_campi = array();
			$elenco_campi[0] = $field_list;
		}
		
		foreach( $elenco_campi as $campo ) {
			$nome_campo = trim($campo);
			if( $collapse != "" )
				$this->$nome_campo->collapse = $collapse . "." . $tab_id;
			else
				$this->$nome_campo->collapse = $tab_id;	
		}		
	}
}