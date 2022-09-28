// FORM LOAD...	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.LOAD_FORM, function( PARAMS ) {
	var form = PARAMS.form_name;
	
	webfly_engine.currentForm = null;
	
	webfly_engine.log( '<a href="#" onclick="webfly_engine.load_form('+"'"+form+"'"+')">FORM_LOAD='+form+'</a>' );

	var form_data = webfly_engine.get_form_parameters();
	
	var REMOTE_REQUEST = {};
	
	REMOTE_REQUEST.q 	= 'gf';
	REMOTE_REQUEST.pars = form_data;
	
	// Called form
	REMOTE_REQUEST.fn = encodeURIComponent(btoa(form));
	
	if( WebFLY_DEBUG ) {
		console.log( "LOAD_FORM" );
		console.log( REMOTE_REQUEST );
	}
	
	webfly_engine.server_request( REMOTE_REQUEST, function( remote_form ) {
		// VISUALIZZA IL FORM...
		var id_canvas = webfly_engine.next_canvas();
		
		$(id_canvas).html( remote_form.MESSAGE.FORM );
								
		webfly_engine.bootstrap_refresh();
		
		webfly_engine.activate_canvas( id_canvas );
		
		// AGGANCIA GLI EVENT-HANDLER...
		webfly_engine.remote_events = remote_form.MESSAGE.HANDLERS;
		webfly_engine.attach_handlers();								
		webfly_engine.exec_server_events( remote_form.MESSAGE.COMMANDS );
	});
});
	