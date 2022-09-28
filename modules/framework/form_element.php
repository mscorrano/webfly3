<?php


// Tipologia di elementi costituenti un form...
abstract class FORM_ELEMENT_TYPE {
	const TESTO				= 9001;
	const DATA				= 9002;
	const CHECKBOX      	= 9003;
	const RADIO				= 9004;
	const PASSWORD      	= 9005;
	const SELECT        	= 9006;
	const EMAIL         	= 9007;
	const NUMERICO      	= 9008;
	const TEXTAREA      	= 9009;
	const DYNAMIC_SELECT 	= 9010;
	const BLOCK				= 9011;
	const DATATABLE			= 9012;
	const ORARIO			= 9013;
	const DATA_ORA			= 9014;
}

// ELEMENTO BASE DI UN FORM...
class FORM_ELEMENT {
	public	  $parent_form;
	
	protected $type;
	protected $name;
    protected $label;
	protected $structure   	= array();
    protected $attr 	   	= array();
    protected $col  		= "";
    protected $required 	= false;
	protected $readonly    	= false;
	protected $end_row;
	protected $collapse 	= "FRWK_HEADER";
	protected $value		= "";
	protected $parametri   	= "";
	protected $DB_MANAGER  	= null;
	protected $events      	= array();
	protected $max_length;
	protected $decimali;
	
	function __construct( $name = "", $label = "", $type = FORM_ELEMENT_TYPE::TESTO, $DB_MANAGER = null ) {
		$this->type       = $type;
		$this->name       = $name;
		$this->label      = $label;
		$this->DB_MANAGER = $DB_MANAGER;
		$this->max_length = 0;
		$this->decimali   = 0;
		
		if( $type == FORM_ELEMENT_TYPE::BLOCK )
			$this->end_row = true;
		else
			$this->end_row = false;
	}
	
	private function datasource() {
		switch( $this->type ) {
			case FORM_ELEMENT_TYPE::SELECT:
			
				if( !is_array( $this->structure[1] )) {
					$sql = "SELECT ".$this->structure[2].",".$this->structure[3];
					if( array_key_exists( 4, $this->structure ) && is_numeric(substr($this->structure[4],0,1)) ) {
						for( $i=5; $i<=(4+intval(substr($this->structure[4],0,1))); $i++ )
							$sql .= ",".$this->structure[$i];
					}
					$sql .= " FROM ".$this->structure[1];
					

					if( array_key_exists(5, $this->structure ) ) {
						if( $this->structure[5] != "" )
							$sql .= " WHERE ".$this->structure[5];
						
						if( array_key_exists(6, $this->structure ) )
							$sql .= " ORDER BY ".$this->structure[6];
					} else {
						if( array_key_exists(4, $this->structure ))
							$sql .= " ORDER BY ".$this->structure[4];
						else
							$sql .= " ORDER BY ".$this->structure[3];
					}

					if( is_null( $this->DB_MANAGER ))
						throw new Exception( "Non è possibile costruire un form con SELECT senza DB-MANAGER (0x90022)", "90022" );	

					return $this->DB_MANAGER->exec_sql( $sql ); 
				} else return $this->structure[1];
				break;
		}		
	}

	public function __set( $property, $value ) { 
		if( property_exists( $this, $property ) ) { 
			$this->$property = $value;
			
			if( $property == "value" && !is_null( $this->parent_form ) ) {
				$field_name = $this->name;
				$this->parent_form->$field_name = $value;
			}
		} else {  
			switch( $this->type ) {
				case FORM_ELEMENT_TYPE::DATATABLE:
					$this->structure[1]->$property = $value;
					break;
					
				case FORM_ELEMENT_TYPE::SELECT: 
					switch( $property ) {
						case "table":
							$this->structure[1] = $value;
							break;
						case "id":
							$this->structure[2] = $value;
							break;
						case "fields":
							$this->structure[3] = $value;
							break;
						case "order_by":
							$this->structure[4] = $value;
							break;
						case "filter":
							$this->structure[5] = $value;
							break;
					}
					break;
			}	
		}
	}
	
	public function __get( $property ) {
		if( property_exists( $this, $property ) )
			return $this->$property;
		
		if( $this->type == FORM_ELEMENT_TYPE::DATATABLE ) { 
			if( property_exists( $this->structure[1], $property )) {
				return $this->structure[1]->$property;
			}	
		}
	}
	
	public function radio_button( $values ) {
		$this->type = FORM_ELEMENT_TYPE::RADIO;
		
		if( !is_array( $valori ) || count( $values ) == 0 )
			throw new Exception( "Il parametro VALUES deve contenere un array con i valori del campo Radio button (0x93010)", "93010" );
		
		$id = 0;
		foreach( $valori as $id => $valore ) {
			$id++;
			$this->structure[$id] = $id.'='.$valore;
		}
		
		return $this;
	}
	
	public function select( $table, $id, $fields, $order_by="", $filter="" ) {
		$this->type = FORM_ELEMENT_TYPE::SELECT;
				
		if( is_null( $this->DB_MANAGER )) {
			global $WEBFLY;
			
			$this->DB_MANAGER = $WEBFLY->DB_MANAGER;
		}
			
		$this->structure[1] = $table;
		$this->structure[2] = $id;
		$this->structure[3] = $fields;
		$this->structure[4] = $order_by;
		$this->structure[5] = $filter;
		
		return $this;
	}	
	
	public function select_dynamic( $table, $id, $fields, $order_by="", $filter ) {
		$this->type = FORM_ELEMENT_TYPE::DYNAMIC_SELECT;
		
		if( is_null( $this->DB_MANAGER )) {
			global $WEBFLY;
			
			$this->DB_MANAGER = $WEBFLY->DB_MANAGER;
		}
			
		$this->structure[1] = $table;
		$this->structure[2] = $id;
		$this->structure[3] = $fields;
		$this->structure[4] = $order_by;
		$this->structure[5] = $filter;
		
		return $this;
	}
	
	public function datatable( $source ) {
		// PARAMETRI (VALIDI SOLO PER TABELLE DINAMICHE)
		// select_page = true/false.... tabella paginata

		$this->type = FORM_ELEMENT_TYPE::DATATABLE;
		
		$this->end_row = true;
		
		if( is_null( $this->DB_MANAGER )) {
			global $WEBFLY;
			
			$this->DB_MANAGER = $WEBFLY->DB_MANAGER;
		}
		$DT = new WEBFLY_DATATABLE( $source, $this->DB_MANAGER, $this );

		$this->structure[1] = $DT;
		
		return $this->structure[1];
	}
	
	function add_event( $event ) {
		if( !is_object($event) || get_class( $event ) != "FORM_EVENT" ) 
			throw new Exception( "L'evento deve essere un oggetto di classe FORM_EVENT (0x90010)", "90010" );
		
		$this->events[] = $event;
		
		return $event;
	}
	
	public function build() {
		$buffer = "";

		$parametri = "";      
		if( $this->parametri != "" && function_exists( $this->parametri ) )
			$parametri_campo = call_user_func( $this->parametri, $campo["Field"], $valore_campo );
		else $parametri_campo = array();

		$parametri_manuali = array( "valore_campo" => "default", "campo_hidden" => "hidden", "divider" => "divider", "html_pre" => "html_pre", "html_post" => "html_post", "html_parms" => "html_parms", "inline" => "inline", "field_col" => "field_col", "readonly" => "readonly", "required" => "required" );
		$default_campi     = array( "valore_campo" => $this->value, "campo_hidden" => false, "divider" => false, "html_pre" => "", "html_post" => "", "html_parms" => array(), "inline" => false, "field_col" => "", "readonly" => false, "required" => false );

		foreach( $parametri_manuali as $nome_variabile => $indice )
		if( array_key_exists( $indice, $parametri_campo ))
			$$nome_variabile = $parametri_campo[$indice];
		else
			$$nome_variabile = $default_campi[$nome_variabile];
		
		$standard = array( 	FORM_ELEMENT_TYPE::TESTO, FORM_ELEMENT_TYPE::EMAIL, FORM_ELEMENT_TYPE::NUMERICO, 
							FORM_ELEMENT_TYPE::PASSWORD, FORM_ELEMENT_TYPE::DATA, FORM_ELEMENT_TYPE::ORARIO, FORM_ELEMENT_TYPE::DATA_ORA );
		
		if( in_array( $this->type, $standard ) ) { 
			// Campi standard...
			
			$buffer .= '<div class="form-group col';
			if( $this->col != "" )
				$buffer .= '-'.$this->col;
			$buffer .= '">'.PHP_EOL;
			
			if( is_array($this->events) && count( $this->events ) > 0 )
				$buffer .= '	<div class="input-group">'.PHP_EOL;
			
			$buffer .= '      <label for="'.$this->name.'"';
			if( $valore_campo != "" || $this->type == FORM_ELEMENT_TYPE::DATA
									|| $this->type == FORM_ELEMENT_TYPE::ORARIO
									|| $this->type == FORM_ELEMENT_TYPE::DATA_ORA )
				$buffer .= ' class="active"';
			
			$buffer .= '>'.$this->label.'</label>'.PHP_EOL;
			$buffer .= '      <input type="';
			
			$time_format = "";
			
			switch( $this->type ) {
				case FORM_ELEMENT_TYPE::TESTO:
					$buffer .= "text";
					break;
					
				case FORM_ELEMENT_TYPE::EMAIL:
					$buffer .= "email";
					break;
					
				case FORM_ELEMENT_TYPE::NUMERICO:
					$buffer .= "number";
					break;	
					
				case FORM_ELEMENT_TYPE::PASSWORD:
					$buffer .= "password";	
					break;
					
				case FORM_ELEMENT_TYPE::DATA:
					$buffer .= "date";
					break;
					
				case FORM_ELEMENT_TYPE::ORARIO:
					$buffer .= "time";
					break;
					
				case FORM_ELEMENT_TYPE::DATA_ORA:
					$buffer .= "date";
					break;	
			}
			
			$buffer .= '" class="form-control';
			if( array_key_exists( "class", $this->attr ))
				$buffer .= ' '.$this->attr["class"];
			
			$buffer .= '" id="'.$this->name.'" name="'.$this->name.'" value="'.$valore_campo.'"';
			foreach( $this->attr as $attr => $value )
				if( strtoupper($attr) != "CLASS" )
					$buffer .= ' '.$attr.'="'.$value.'"';
			
			if( $this->type == FORM_ELEMENT_TYPE::NUMERICO ) {
				if( $this->max_length > 0 )
					$buffer .= ' max="'.str_repeat( "9", $this->max_length ).'"';
				$buffer .= ' NUMERICO('.$this->max_length.','.$this->decimali.')';
				if( $this->max_length > 0 )
					$buffer .= ' step="'.round( 1 / pow(10, $this->decimali), $this->decimali).'"';
				
				
			} else {
				if( $this->max_length > 0 )
					$buffer .= ' max_length="'.$this->max_length.'"';
			}
			
			if( $this->required )
				$buffer .= " required";
			
			$buffer .= '/>'.PHP_EOL;
						
			if( is_array($this->events) && count( $this->events ) > 0 ) {
				foreach( $this->events as $event )
					$buffer .= '<div class="input-group-append">'.$event->build().'</div>'.PHP_EOL;
					
				$buffer .= '	</div>'.PHP_EOL;
			}
			$buffer .= '</div>'.PHP_EOL;
		} else {
			// Altre tipologie di campo...
			
			$tipo_input = "text";
			
			switch( $this->type ) {				
				case FORM_ELEMENT_TYPE::CHECKBOX:
					$tipo_input = "checkbox";
				case FORM_ELEMENT_TYPE::RADIO:
					if( $tipo_input == "text" )
						$tipo_input = "radio";
					
					$input = false;
					
					$buffer .= '<div class="form-group col';
					if( $this->col != "" )
						$buffer .= '-'.$this->col;
					$buffer .= '">';
					$buffer .= '<div class="form-row">'.PHP_EOL;
					$buffer .= '<label class="active" for="'.$this->name.'" class="mr-1">'.$this->label.':</label>'.PHP_EOL;
			
					$buffer .= '</div>'.PHP_EOL;

					$buffer .= '<div class="form-row mb-4">'.PHP_EOL;
					for( $i=1; $i<count($this->structure); $i++ ) { 
						list($id,$valore)=explode( "=", $this->structure[$i] );
						$buffer .= '<div class="form-check form-check-inline">'.PHP_EOL;
						$buffer .= '<input type="'.$tipo_input.'" class="form-check-input';
						if( array_key_exists( "class", $this->attr ))
							$buffer .= ' '.$this->attr["class"];
						$buffer .= '"';
						foreach( $this->attr as $param => $value )
						if( strtolower($param) != "class" )
							$buffer .= ' '.$param.'="'.$value.'"';
						$buffer .= ' name="'.$this->name.'" id="'.$this->name.'_'.$id.'" value="'.$id.'"';
						
						if( $id == $valore_campo )
							$buffer .= ' checked';

						if( $this->readonly )
						 $buffer .= ' readonly';

						if( $this->required )
						 $buffer .= ' required';
						$buffer .= '/>';
						$buffer .= '<label for="'.$this->name.'_'.$id.'" class="form-check-label">'.$valore.'</label></div>';
					}	
					$buffer .= '</div>'.PHP_EOL;
					$buffer .= '</div>'.PHP_EOL;
					break;
					
				case FORM_ELEMENT_TYPE::DYNAMIC_SELECT:					
				case FORM_ELEMENT_TYPE::SELECT:
				
					$buffer .= '<div class="form-group col';
					if( $this->col != "" )
						$buffer .= '-'.$this->col;
					$buffer .= '">';
					
					$buffer .= '<div class="bootstrap-select-wrapper col-12 pl-0 pr-0 pb-3';
				    $buffer .= '">'; 
					$buffer .= '<label class="font-weight-bold" for="'.$this->name.'">'.$this->label.'</label>';
					$buffer .= '<select id="'.$this->name.'" name="'.$this->name.'"';
                  
					if( $this->type == FORM_ELEMENT_TYPE::DYNAMIC_SELECT ) {
						// Dinamico
						if( $this->structure[4] == "" )
							$this->structure[4] = "Selezionare...";
						
						$buffer .= 'title="'.$this->structure[4].'" class="selectpicker form-control';
						
						if( array_key_exists( "class", $html_parms ))
							$buffer .= ' '.$html_parms["class"];
					 
						$buffer .= '"';
						
						foreach( $html_parms as $param => $value )
							if( strtolower($param) != "class" )
								$buffer .= ' '.$param.'="'.$value.'"';
					 
						if( $readonly )
							$buffer .= ' disabled';
					 
						if( $required )
							$buffer .= ' required';
					 
						$buffer .= ' data-live-search="true">';
					} else {
						$buffer .= ' class="form-control border-bottom border-secondary';
						if( array_key_exists( "class", $html_parms ))
							$buffer .= ' '.$html_parms["class"];
						$buffer .= '"';
						
						foreach( $html_parms as $param => $value )
							if( strtolower($param) != "class" )
								$buffer .= ' '.$param.'="'.$value.'"';
						
						if( $readonly )
							$buffer .= ' disabled';
					 
						if( $required )
							$buffer .= ' required';
					 
						$buffer .= '><option value="">Selezionare...</option>';
					}
					
					$elenco_option = $this->datasource();
					
					foreach( $elenco_option as $option ) {
						$buffer .= '<option value="'.$option[$this->structure[2]].'"';
						if( $valore_campo == $option[$this->structure[2]] )
							$buffer .= ' selected';
						$buffer .= '>';
						
						if( strtotime($option[$this->structure[3]]) )
							$buffer .= date("d-m-Y", strtotime($option[$this->structure[3]]));
						else 
							$buffer .= $option[$this->structure[3]];
					 
						if( array_key_exists(4, $this->structure) && is_numeric( $this->structure[4] ))
							for( $i=5; $i<=(4+intval(substr($this->structure[4],0,1))); $i++ ) {
								$buffer .= " ".$option[$this->structure[$i]];							 
							}
					 
						$buffer .= '</option>';   
					}
					$buffer .= '</select></div>';
					//$buffer .= '</div>'.PHP_EOL;
					$buffer .= '</div>'.PHP_EOL;
					break;
					
				
				case FORM_ELEMENT_TYPE::BLOCK:
				
					$buffer .= '<div class="form-group col';
					if( $this->col != "" )
						$buffer .= '-'.$this->col;
					if( array_key_exists( "class", $this->attr ))
						$buffer .= ' '.$this->attr["class"];
					$buffer .= '"';
					foreach( $this->attr as $param => $value )
						if( strtolower($param) != "class" )		
							$buffer .= ' '.$param.'="'.$value.'"';						
					$buffer .= ' id="'.$this->name.'"';
					$buffer .= '>';
					$buffer .= '<div class="form-row justify-content-end" id="'.$this->name.'_event_bar">';
					// Inserisce tutti gli oggetti contenuti nel blocco...
					$id_evento = 0;
					if( is_array( $this->events ))
						foreach( $this->events as $evento ) {
							$id_evento++;
							if( count( $this->events ) > 1 && $id_evento <= count( $this->events ) )
								$buffer .= $evento->build( "mr-2" );
							else
								$buffer .= $evento->build();
						}	
					$buffer .= '</div>';
					$buffer .= '</div>'.PHP_EOL;
					break;	
					
				case FORM_ELEMENT_TYPE::TEXTAREA:
					break;
				
				case FORM_ELEMENT_TYPE::DATATABLE:
					$buffer .= '<div class="col';
					if( $this->col != "" )
						$buffer .= '-'.$this->col;
					if( array_key_exists( "class", $this->attr ))
						$buffer .= ' '.$this->attr["class"];
					$buffer .= '"';
					foreach( $this->attr as $param => $value )
						if( strtolower($param) != "class" )		
							$buffer .= ' '.$param.'="'.$value.'"';						
					$buffer .= ' id="'.$this->name.'"';
					$buffer .= '>';
										
					$buffer .= $this->structure[1]->build();

					$buffer .= '</div>'.PHP_EOL;
					break;
			}
		}
		
		return $buffer;
	}
}

?>