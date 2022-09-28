<?php

include( __DIR__ . "/wf_remote_commands.php" );
	
class WEBFLY_REMOTE_FORM extends WEBFLY_FORM {
	protected $Me;
	protected $REMOTE_COMMANDS;
	protected $header;
	protected $footer;
	protected $session_data;
	
	public $REMOTE_DATA;
	
	private function insert_command( $TYPE ) {
		$NEW_COMMAND = array();
		$NEW_COMMAND["COMMAND"] = $TYPE;
		
		if( func_num_args() > 1 ) {
			$PARAMS = array();
			for( $num = 1; $num < func_num_args(); $num+=2 )
				$PARAMS[func_get_arg( $num )] = func_get_arg( $num+1 );
			
			$NEW_COMMAND["PARAMS"] = $PARAMS;
		} else {
			$NEW_COMMAND["PARAMS"] = array();
		}
		
		$this->REMOTE_COMMANDS[] = $NEW_COMMAND;
	}
	
	function form_back_onclick() {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::BACK_CANVAS );
	}
	
	function __construct( $source_table="", $name="", $DB_MANAGER = null ) {
		global $WEBFLY;
		
		if( $name == "" ) {
			$rf = new ReflectionClass( get_class( $this ));
			$name = $rf->getFileName();
			
			$fine_path = strpos( $name, $WEBFLY->PROGETTO["percorso_classi"] );
			if( $fine_path !== false )
				$name = substr( $name, $fine_path + strlen($WEBFLY->PROGETTO["percorso_classi"]) + 1 );
			
			if( strpos( strtoupper($name), ".PHP" )!==false )
				$name = substr( $name,0,strlen($name)-4 );
		}
		if( is_null($DB_MANAGER) ) {
			// Inizializza il DB Manager... con il DB di Default...
			$DB_MANAGER = $WEBFLY->DB_MANAGER;
		}
		
		parent::__construct( get_class( $this ), $name, "AJAX", $DB_MANAGER, $source_table );

		$this->REMOTE_COMMANDS 	= array();
		$this->header 			= "";
		$this->footer 			= "";
		$this->Me     			= $name;
		$this->session_data	 	= array();
	}
	
	public function __set( $field_name, $value ) {
		
		if( property_exists( $this, $field_name )) {
			$this->$field_name = $value;
			return;
		}

		if( array_key_exists( $field_name, $this->form_data )) {
			if( $this->form_data[$field_name] != $value ) {
				$this->form_data[$field_name] = $value;
				$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_VALUE, "id", $field_name, "value", $value );
			}
			
			foreach( $this->elements as $id => $form_element ) {
				if( $form_element->name == $field_name && $this->elements[$id]->value != $value ) {
					$this->elements[$id]->value = $value;
					echo $this->elements[$id]->value;
				}
			}
		} 
		
		if( array_key_exists( $field_name, $this->params )) {
			if( $this->form_data[$field_name] != $value ) {
				$this->params[$field_name] = $value;
				$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_VALUE, "id", $field_name, "value", $value );
			}
		}
	}	
	
	public function structure() {
		
		if( method_exists( $this, "onload" ))
			$this->onload();
		else
			parent::structure();
	}
	
	public function render() {
		$this->structure();
		
		return $this->header . parent::build() . $this->footer;
	}
	
	public function send_commands() {
		return $this->REMOTE_COMMANDS;
	}
	
	public function server_requests( $TYPE ) {
		switch( $TYPE ) {
			case "PARAMS":
				$params = array();
				foreach( $this->session_data as $key => $value ) {
					$new_param = array();
					$new_param["name"]  = $key;
					$new_param["value"] = $value;
					
					$params[] = $new_param;
				}
				return $params;
				break;
				
			case "CLASSES":
				return array();
				break;
				
			case "ATTRS":
				return array();
				break;
		}
	}
	
	public function create_server_request( $TYPE, $field_name, $param="" ) {
		switch( $TYPE ) {
			case "PARAMS":
				break;
				
			case "CLASSES":
				break;
				
			case "ATTRS":
				break;
		}
	}
	
	public function save() {
		if( method_exists( $this, "onsave" ) ) {
			if( $this->onsave() === false )
				return false;
		}
		return parent::save();
	}
	
	public function exit_event() {
		return new FORM_EVENT( "form_quit", "Esci...", "btn-danger", EVENT_ICONS::QUIT, "menu.php" );
	}	
	
	public function add_exit_event() {
		$this->add_event( $this->exit_event() );
	}	
	
	public function back_event( $etichetta = "Indietro..." ) {
		return new FORM_EVENT( "form_back", $etichetta, "btn-warning", EVENT_ICONS::QUIT );
	}	
	
	public function add_back_event( $etichetta = "Indietro..." ) {
		$this->add_event( $this->back_event( $etichetta ) );
	}
	
	public function open_report( $report_name ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::OPEN_REPORT, "report_name", $report_name );
	}
	
	public function load_form( $class_name = "" ) {
		if( $class_name == "" )
			$class_name = $this->Me;
		
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::LOAD_FORM, "form_name", $class_name );
	}
	
	public function load_popup_form( $class_name = "", $title = "" ) {
		if( $class_name == "" )
			$class_name = $this->Me;
		
		if( $title == "" )
			if( property_exists( $this, "title" ))
				$title = $this->title;
			
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::LOAD_POPUP_FORM, "form_name", $class_name, "title", $title );
	}	
	
	public function close_popup_form() {	
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::CLOSE_POPUP_FORM );
	}
	
	public function open_popup( $message, $title = "" ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::OPEN_POPUP, "message", $message, "title", $title );
	}
	
	public function refresh( $element = NULL, $html = "" ) {
		if( is_null( $element )) {
			// Refresh form...
			$this->insert_command( WEBFLY_REMOTE_COMMANDS::LOAD_FORM, "form_name", $this->Me );
		} else {
			if( is_object( $element )) {
				if( $html == "" )
					$element_html = $element->build();
				
				if( get_class( $element ) != "FORM_ELEMENT" )
					$this->insert_command( WEBFLY_REMOTE_COMMANDS::REFRESH_FIELD, "field_name", $element->FORM_ELEMENT->name, "field_html", $element_html );
				else
					$this->insert_command( WEBFLY_REMOTE_COMMANDS::REFRESH_FIELD, "field_name", $element->name, "field_html", $element_html );
			} else {				
				$this->insert_command( WEBFLY_REMOTE_COMMANDS::REFRESH_FIELD, "field_name", $element, "field_html", $html );
			}
		}
	}
	
	public function refresh_element( $form_name, $field_name ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::REFETCH_FIELD, "form_name", $form_name, "field_name", $field_name );		
	}
	
	public function innerHTML( $html_id, $html ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::REFRESH_FIELD, "field_name", $html_id, "field_html", $html );		
	}
	
	
	public function set_session( $name, $value ) {
		if( !is_array( $this->session_data ))
			$this->session_data = array();
		
		$this->session_data[$name] = $value;
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_PARAM, "name", $name, "value", $value );
	}
	
	public function get_session( $name ) {
		if( array_key_exists( $name, $this->session_data ))
			return $this->session_data[$name];
		else
			return "";
	}
	
	public function set_attr( $name, $attr, $value, $replace_value="" ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_ATTR, "field_name", $name, "attr", $attr, "value", $value, "replace_value", $replace_value );
	}
	
	public function set_class( $name, $value, $replace_value="" ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_CLASS, "field_name", $name, "class", $value, "replace_class", $replace_value );
	}
	
	public function set_style( $name, $value, $replace_value="" ) {
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::SET_ATTR, "field_name", $name, "attr", "style", "value", $value, "replace_value", $replace_value );
	}
	
	public function console( $var ) {
		ob_start();
		var_dump( $var );
		$esito = ob_get_clean();
		//$esito = '<pre>DEBUG'.$esito.'</pre>';
		
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::CONSOLE, "message", $esito );
	}
	
	public function remote_error( $message ) {
		$this->open_popup( $message, "ERRORE" );
	}
	
	public function set_values( $values ) {
		$this->values( $values );
	}
	
	public function call_method( $form_name, $method, $params=array() ) {
		$params_send = serialize( $params );
		$this->insert_command( WEBFLY_REMOTE_COMMANDS::METHOD_CALL, "form_name", $form_name, "method", $method, "params", $params_send );
		
	}
}