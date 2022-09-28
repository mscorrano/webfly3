<?php

abstract class WEBFLY_REMOTE_COMMANDS {
	const SET_VALUE			= 89001;	// Params: NAME, VALUE
	const OPEN_REPORT   	= 89002;    // Params: REPORT_NAME
	const LOAD_FORM			= 89003;	// Params: Form classname
	const LOAD_FIELD		= 89004;	// Params: Field name
	const OPEN_POPUP		= 89005;	// Params: Message, Title
	const REFRESH_FIELD 	= 89006;	// Params: Field name, Field HTML
	const SET_PARAM			= 89007;    // Params: Param name, Param value
	const GET_ATTR			= 89008;	// Params: Field name, Attr
	const SET_ATTR			= 89009;	// Params: Field name, Attr, New Attr Value, Old Attr Value
	const GET_CLASSES		= 89010;	// Params: Field name
	const SET_CLASS			= 89011;	// Params: Field name, Class, Remove Class
	const CONSOLE			= 89012;	// Params: Message
	const BACK_CANVAS		= 89013;	// Params: NONE
	const LOAD_POPUP_FORM	= 89014;	// Params: Form classname, Title
	const CLOSE_POPUP_FORM	= 89015;	// Params: NONE
	const REFETCH_FIELD		= 89016;	// Params: Form clasname, Field name
	const METHOD_CALL		= 89017;	// Params: Form clasname, Method, Params...
	
	function generate_remote_commands() {
		echo '<script>'.PHP_EOL;
		echo 'const WEBFLY_REMOTE_COMMANDS = {'.PHP_EOL;
		
		$content = new ReflectionClass('WEBFLY_REMOTE_COMMANDS');
		$elenco = $content->getConstants();
		
		$lunghezza = 0;
		foreach( $elenco as $name => $value ) {
			if( strlen( $name ) > $lunghezza )
				$lunghezza = strlen( $name );
		}
		
		$primo = true;
		foreach( $elenco as $name => $value ) {
			if( $primo )
				$primo = false;
			else
				echo ','.PHP_EOL;
			echo str_repeat( ' ', 6 ).str_pad( $name, $lunghezza + 2, " " )." : ".$value;
		}	
		echo PHP_EOL.'}'.PHP_EOL;
		echo '</script>'.PHP_EOL;
	}
}

?>