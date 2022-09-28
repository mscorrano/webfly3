// CALL REMOTE METHOD...	
webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.METHOD_CALL, function( PARAMS ) {
	var form = PARAMS.form_name;
	
	webfly_engine.currentForm = null;
	
	webfly_engine.log( '<a href="#" onclick="webfly_engine.load_form('+"'"+form+"'"+')">FORM_LOAD='+form+'</a>' );

	var form_data = webfly_engine.get_form_parameters();
	
	var REMOTE_REQUEST = {};
	
	REMOTE_REQUEST.q 	= 'mc';
	REMOTE_REQUEST.pars = form_data;
	REMOTE_REQUEST.m    = PARAMS.method;
	REMOTE_REQUEST.mp	= PARAMS.params;
	
	// Called form
	REMOTE_REQUEST.fn = encodeURIComponent(btoa(form));
	
	if( WebFLY_DEBUG ) {
		console.log( "LOAD_FORM" );
		console.log( REMOTE_REQUEST );
	}
	
	webfly_engine.server_request( REMOTE_REQUEST, function( remote_form ) {						
		webfly_engine.exec_server_events( remote_form.MESSAGE.COMMANDS );
	});
});
	