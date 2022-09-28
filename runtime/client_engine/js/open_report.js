webfly_engine.add_command( WEBFLY_REMOTE_COMMANDS.OPEN_REPORT, function( params ) {	
	var form = params.report_name;
	
	// Apre il popup...
	$('#webfly_report_canvas').modal();
	var spinner = '<div id="webfly_report_spinner" class="progress-spinner progress-spinner-double progress-spinner-active webfly-event-spinner">';
	spinner += '<div class="progress-spinner-inner"></div>';
	spinner += '<div class="progress-spinner-inner"></div>';
	spinner += '<span class="sr-only">Caricamento...</span>';
	spinner += '</div>';
	
	$('#webfly_report_canvas').append( spinner );
	
	var form_data = "";
	
	var REMOTE_REQUEST = {};
	
	REMOTE_REQUEST.q 	= 'gr';
	REMOTE_REQUEST.rn   = btoa(params.report_name);
	REMOTE_REQUEST.pars = form_data;
	
	webfly_engine.server_request( REMOTE_REQUEST, function( messaggio ) {
		if( typeof messaggio.MESSAGE !== 'undefined' ) {
			$('#webfly_report_title').html( messaggio.MESSAGE.TITOLO );
			$('#webfly_report_content').attr( "src", WebFLY_SERVER + "?q=gr&print=1&rn="+btoa(params.report_name) );
			$('#webfly_report_spinner').hide();
		} else {
			console.log( "STRUTTURA REPORT NON RICONOSCIUTA" );
		}		
	});		
});