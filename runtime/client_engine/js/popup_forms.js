// FORM LOAD...	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.LOAD_POPUP_FORM, function( PARAMS ) {
	var form = PARAMS.form_name;
	
	webfly_engine.currentForm = null;
	
	var form_data = webfly_engine.get_form_parameters();
	
	var REMOTE_REQUEST = {};
	
	REMOTE_REQUEST.q 	= 'gf';
	REMOTE_REQUEST.pars = form_data;
	
	// Called form
	REMOTE_REQUEST.fn = encodeURIComponent(btoa(form));
	
	if( WebFLY_DEBUG ) {
		console.log( "LOAD_POPUP_FORM" );
		console.log( REMOTE_REQUEST );
	}
	
	webfly_engine.server_request( REMOTE_REQUEST, function( remote_form ) {
		// VISUALIZZA IL FORM...
		if( PARAMS.title != "" )
			$('#WEBFLY_POPUP_FORM_TITLE').html( PARAMS.title );
	
		$('#WEBFLY_POPUP_BODY').html( remote_form.MESSAGE.FORM );
		
		$('#WEBFLY_POPUP_FORM').modal();
		
		webfly_engine.bootstrap_refresh();
		
		// AGGANCIA GLI EVENT-HANDLER...
		webfly_engine.remote_events = remote_form.MESSAGE.HANDLERS;
		webfly_engine.attach_handlers();								
		webfly_engine.exec_server_events( remote_form.MESSAGE.COMMANDS );
	});
	
});

// CLOSE FORM...	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.CLOSE_POPUP_FORM, function() {
	
	$('#WEBFLY_POPUP_FORM').modal('hide');
});
	