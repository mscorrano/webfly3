<?php

// EVENTO ALL'INTERNO DI UN FORM...	
class FORM_EVENT  {
	private $name;
	private $label;
	private	$submit_button;
	private $btn_type;
	private $remote_key;
	private $remote_id;
	private $parent_name;
	
	public  $called_form;
	public  $remote_event_name;
	public  $btn_size;
	public  $icon;
	public  $append;
	public  $form_validation;
	public  $confirm_event;
	public  $confirm_title;
	public  $confirm_message;
	public	$default;
	public  $dismiss_button;
	public  $condition_callback;
	
	function __construct( $name, $label, $btn_type="btn-primary", $icon="", $submit_type="button" ) {
		$this->name  			  = $name;
		$this->label 			  = $label;
		$this->submit_button 	  = $submit_type;
		$this->btn_size 		  = "btn-sm";
		$this->btn_type 		  = $btn_type;
		$this->append   		  = false;
		$this->icon				  = $icon;
		$this->remote_event_name  = "";
		$this->remote_id		  = "";
		$this->remote_key         = "";
		$this->form_validation    = true;
		$this->parent_name		  = "";
		$this->called_form		  = "";
		$this->confirm_event	  = false;
		$this->confirm_title	  = "";
		$this->confirm_message	  = "";
		$this->default			  = false;
		$this->dismiss_button	  = false;
		$this->condition_callback = "";
	}
	
	function event_array( $id, $key="" ) {
		if( $key == "" )
			$key = $id;
		
		$this->remote_id  = $id;
		$this->remote_key = $key;
		$this->remote_event_name = $this->name;
	}
	
	function inline_event( $parent_name ) {
		if( $this->parent_name != $parent_name ) {
			if( $this->parent_name != "" ) {
				// Rimuove il vecchio...
				$this->name					= str_replace( $this->parent_name . "_", "", $this->name );
				$this->remote_event_name 	= str_replace( $this->parent_name . "_", "", $this->remote_event_name );
			}
			
			$this->parent_name = $parent_name;
			
			// Aggiunge Parent name all'evento
			$this->name 				= $parent_name . "_" . $this->name;
			$this->remote_event_name 	= $parent_name . "_" . $this->remote_event_name;
		}
		
	}
	
	function build( $class = "", $parameters = array() ) {
		$inserisci = true;
		if( $this->condition_callback != "" && function_exists( $this->condition_callback )) {
			$retval = call_user_func( $this->condition_callback, $parameters );
			
			if( is_bool( $retval ))
				$inserisci = $retval;
		}
		
		$buffer = "";
		if( $inserisci ) {
			if( $this->append )
				$buffer .= '<div class="input-group-append">';
			
			if( $this->submit_button != "submit" && $this->submit_button != "button" ) {
				$buffer = '<a href="'.$this->submit_button.'"';
			} else {
				$buffer  = '<button type="';
			
				if( $this->submit_button == "submit" )
					$buffer .= 'submit';
				else
					$buffer .= 'button';
			}
			
			$buffer .= '" class="btn ';
			$buffer .= $this->btn_type;
			$buffer .= " ".$this->btn_size;
			if( $class != "" )
				$buffer .= " ".$class;
			
			if( $this->default )
				$buffer .= ' webfly-default-button';
			
			$buffer .= '"';
			if( $this->remote_event_name != "" ) {
				$buffer .= ' data-event="'.$this->remote_event_name.'"';
			}
			$buffer .= ' id="'.$this->name;
			if( $this->remote_id !== "" )
				$buffer .= '_'.$this->remote_id;
			$buffer .= '" name="'.$this->name;
			if( $this->remote_id !== "" )
				$buffer .= '_'.$this->remote_id;
			$buffer .= '"';
			
			if( $this->remote_id !== "" )
				$buffer .= ' data-remote_id="'.$this->remote_id.'"';	
			
			if( $this->remote_key !== "" )
				$buffer .= ' data-remote_key="'.base64_encode($this->remote_key).'"';
			
			if( $this->form_validation === false )
				$buffer .= ' data-no_validate="true"';
			
			if( $this->called_form !== "" )
				$buffer .= ' data-called_form="'.$this->called_form.'"';
				
			if( $this->confirm_event == true )
				$buffer .= ' data-confirm="1"';
			else
				$buffer .= ' data-confirm="0"';
			
			$buffer .= ' data-confirm_title="'.addslashes( $this->confirm_title ).'"';
			$buffer .= ' data-confirm_message="'.addslashes( $this->confirm_message ).'"';
			
			if( $this->default )
				$buffer .= ' data-default="1"';
			
			if( $this->dismiss_button )
				$buffer .= ' data-dismiss="modal"';
				
			$buffer .= '>';
			
			if( $this->icon != "" )
				$buffer .= '<i class="'.$this->icon.' mr-2"></i>';
			$buffer .= $this->label;
			
			if( $this->submit_button != "submit" && $this->submit_button != "button" )
				$buffer .= '</a>';
			else
				$buffer .= '</button>';
			
			if( $this->append )
				$buffer .= '</div>';	
		}
		return $buffer;
	}
	
	function event_id() {
		return $this->name;
	}
}

?>