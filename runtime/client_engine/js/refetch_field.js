// REFETCH FIELD...
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.REFETCH_FIELD, function( PARAMS ) {
	var form = PARAMS.form_name;
	
	webfly_engine.currentForm = null;

	var form_data = webfly_engine.get_form_parameters();
	
	var REMOTE_REQUEST = {};
	
	REMOTE_REQUEST.q 	= 'ff';
	REMOTE_REQUEST.pars = form_data;
	
	// Called form
	REMOTE_REQUEST.fn = encodeURIComponent(btoa(form));
	REMOTE_REQUEST.bn = PARAMS.field_name;
	
	if( WebFLY_DEBUG ) {
		console.log( "REFETCH_FIELD" );
		console.log( REMOTE_REQUEST );
	}
	
	webfly_engine.server_request( REMOTE_REQUEST, function( remote_form ) {
		// VISUALIZZA IL FORM...
		var id_canvas = webfly_engine.next_canvas();
		
		$( remote_form.MESSAGE.FIELD_ID ).html( remote_form.MESSAGE.FIELD  );

		webfly_engine.bootstrap_refresh();
		
		// AGGANCIA GLI EVENT-HANDLER...
		webfly_engine.remote_events = remote_form.MESSAGE.HANDLERS;
		webfly_engine.attach_handlers();								
		webfly_engine.exec_server_events( remote_form.MESSAGE.COMMANDS );
	});
});