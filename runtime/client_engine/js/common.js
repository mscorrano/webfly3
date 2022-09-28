webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.SET_VALUE, function( params ) {
	if( $('#'+params.id).length ) {
		// Oggetto trovato...
		$('#'+params.id).val( params.value );
		if( !$("label[for='"+params.id+"']").hasClass( 'active' ) )
			$("label[for='"+params.id+"']").addClass( 'active' );
	}
});
	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.OPEN_POPUP, function( params ) {		
	if( params.title != "" )
		$('#WEBFLY_MODAL_TITLE').html( params.title );
	else
		$('#WEBFLY_MODAL_TITLE').html( "WebFLY 3.0" );
	
	$('#WEBFLY_MODAL_MESSAGE').html( params.message )
	$('#WEBFLY_MODAL').modal();
	
});
		
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.SET_PARAM, function( params ) {			
	var trovato = false;
	webfly_engine.local_params.forEach( function( param, index ) {
		if( param.name == params.name ) {
			webfly_engine.local_params[index].value = params.value;
			trovato = true;
		}
	});
	
	if( !trovato ) {
		var new_param = {};
		new_param.name  = params.name;
		new_param.value = params.value;
		
		if( webfly_engine.local_params.length > 0 ) {
			webfly_engine.local_params[ webfly_engine.local_params.length ] = new_param;
		} else {
			webfly_engine.local_params = [];
			webfly_engine.local_params[0] = new_param;
		}
	}
});	
	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.SET_ATTR, function( params ) {	
	// TODO
	if( params.replace_value == "" ) {
		$('#'+params.field_name).attr( params.attr, params.value );
	} else {
		valore = $('#'+params.field_name).attr( params.attr );
		valore.replace( params.replace_value, params.value );
		$('#'+params.field_name).attr( params.attr, valore );	
	}
});

webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.SET_CLASS, function( params ) {		
	$('#'+params.field_name).removeClass( params.attr, params.replace_class );
	$('#'+params.field_name).addClass( params.attr, params.value );
});

webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.CONSOLE, function( params ) {		
	webfly_engine.log( REMOTE_COMMAND.PARAMS.message, true );
});

webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.BACK_CANVAS, function( params ) {	
	// TODO
	webfly_engine.activate_canvas( webfly_engine.next_canvas() );
});