<?php

class WEBFLY_DATATABLE extends DATATABLE {
	public $FORM_ELEMENT;
	
	function __construct( $sorgente, $DB_MANAGER = null, $parent_element = null ) {
		parent::__construct( $sorgente, $DB_MANAGER );
		
		$this->FORM_ELEMENT = $parent_element;
		
		$this->modifica  = false;
		$this->seleziona = false;
		$this->cancella  = false;
		$this->cancella  = false;
	}
	
	protected function row_events() {
		return count( $this->FORM_ELEMENT->events );
	}
	
	protected function add_row_events( $key, $id_record, $tabella_DB, $record ) {
		
		$buffer = "";
		$id_evento = 0;
		foreach( $this->FORM_ELEMENT->events as $evento ) {
			$evento->inline_event( $this->FORM_ELEMENT->name );
			$evento->event_array( $id_record, $key );
			$evento->form_validation = false;
			
			$id_evento++;
			if( count( $this->FORM_ELEMENT->events ) > 1 && $id_evento <= count( $this->FORM_ELEMENT->events ) )
				$buffer .= $evento->build( "mr-2", $record );
			else
				$buffer .= $evento->build( "", $record );
		}
		
		return $buffer;
	}
	
	public function __get( $property ) {
		if( property_exists( $this->FORM_ELEMENT, $property ))
			return $this->FORM_ELEMENT->$property;
	}	
	
	public function __set( $property, $value ) { 
		if( is_object( $this->FORM_ELEMENT ) && property_exists( $this->FORM_ELEMENT, $property )) {
			$this->FORM_ELEMENT->$property = $value;
		} else {
			if( property_exists( $this, $property )) {
				$this->$property = $value;
			} else {
				$this->PARAMS->$property = $value;
			}
		}
	}
}

?>