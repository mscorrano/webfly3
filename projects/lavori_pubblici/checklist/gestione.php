<?php

require_once( __DIR__ . "/../../../modules/framework/remote_form.php" );


class GESTIONE_CHECKLIST extends WEBFLY_REMOTE_FORM {
			
	function __construct() {
		parent::__construct( "iter_opera" );
	}
	
	function salva_onclick() {
	}
}