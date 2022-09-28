var WebFLY_DEBUG = false;

$(document).ready( function() {
	if( typeof WEBFLY_STARTUP_FORM !== 'undefined' ) {
		webfly_engine.load_form( WEBFLY_STARTUP_FORM );
	}

});

function formatErrorMessage(jqXHR, exception) {

    if (jqXHR.status === 0) {
        return ('Not connected.\nPlease verify your network connection.');
    } else if (jqXHR.status == 404) {
        return ('The requested page not found. [404]');
    } else if (jqXHR.status == 500) {
        return ('Internal Server Error [500].');
    } else if (exception === 'parsererror') {
        return ('Requested JSON parse failed.');
    } else if (exception === 'timeout') {
        return ('Time out error.');
    } else if (exception === 'abort') {
        return ('Ajax request aborted.');
    } else {
        return ('Uncaught Error.\n' + jqXHR.responseText);
    }
}

var WebFLY_SERVER = "/runtime/server.php";

class WebFLY {
	local_params    		= [];
	element_classes 		= [];
	element_attr    		= [];
	confirm_event   		= {};
	remote_command_handlers = {};
	remote_events			= [];
	currentForm 			= null;
	
	event_manager( event ) {
		
		var REMOTE_REQUEST = {};
		
		var target = event.currentTarget;
				
		var submit_form;
		if( typeof $(target).data("called_form") !== "undefined" )
			submit_form = $('#'+$(target).data("called_form"));
		else {
			submit_form = $(target).closest('form');
			
			if( submit_form.length == 0 ) {
				submit_form = $(target).parents('.webfly-canvas').find('form');
				
				if( submit_form.length > 0 )
					submit_form = submit_form[0];
				else
					throw 'Form chiamato non rilevabile automaticamente. Utilizzare il parametro data-called_form nel TAG';
			}
		}
		
		webfly_engine.currentForm = $(submit_form);
		
		REMOTE_REQUEST.pars = webfly_engine.get_form_data( submit_form );	

		$(submit_form).children('fieldset').prop("disabled", "disabled" );
		
		var spinner = '<div id="webfly_event_spinner" class="progress-spinner progress-spinner-double progress-spinner-active webfly-event-spinner">';
		spinner += '<div class="progress-spinner-inner"></div>';
		spinner += '<div class="progress-spinner-inner"></div>';
		spinner += '<span class="sr-only">Caricamento...</span>';
		spinner += '</div>';
		
		$(submit_form).append( spinner );	
		
		// Called form
		REMOTE_REQUEST.fn = encodeURIComponent(btoa($(submit_form).data("classname")));
		
		// Remote-KEY
		if( typeof $(target).data("remote_key") !== 'undefined' )
			REMOTE_REQUEST.rkey = $(target).data("remote_key");
		
		// Remote-ID
		if( typeof $(target).data("remote_id") !== 'undefined' )
			REMOTE_REQUEST.rid = $(target).data("remote_id");	
		
		REMOTE_REQUEST.q     = "ev";
		if( typeof $(target).attr("name") === 'undefined' )
			REMOTE_REQUEST.bn    = $(target).attr("id");
		else
			REMOTE_REQUEST.bn    = $(target).attr("name");
		
		REMOTE_REQUEST.en    = event.type;
		
		
		webfly_engine.server_request( REMOTE_REQUEST, function( remote_response ) {								
			webfly_engine.exec_server_events( remote_response.MESSAGE.COMMANDS );
		});
	}
	
	button_click( event ) {
		
		var REMOTE_REQUEST = {};
		
		var button = event.currentTarget;
		
		var confirmed_event = false;
		
		if( typeof event.data.confirm != "undefined" ) {
			if( event.data.confirm == true ) {
				var button = webfly_engine.confirm_event.currentTarget;
				confirmed_event = true;
			}
		}
		
		if( typeof $(button).data("event_source") !== "undefined" ) {
			button_click( $(button).data("event_source") );
			return;
		}
		
		var submit_form;
		if( typeof $(button).data("called_form") !== "undefined" )
			submit_form = $('#'+$(button).data("called_form"));
		else	
			submit_form = $(button).closest('form');
		
		REMOTE_REQUEST.pars = webfly_engine.get_form_data( submit_form );
		
		var validate = true;
		
		if( $(button).data("no_validate") === true )
			validate = false;
		
		if( validate ) {
			// JavaSCRIPT Form Validation...
			var local_form = document.getElementById( $(submit_form).attr( "id" ) );
			
			if( local_form !== null ) {
				if( !local_form.reportValidity() ) {
					webfly_engine.log( "FORM VALIDATION ERROR" );
					return false;
				}
			}
		}
		
		// Evento con messaggio di conferma?
		if( !confirmed_event ) {
			if( typeof $(button).data("confirm") !== "undefined" && typeof $(button).data("confirm_title") !== "undefined" &&typeof $(button).data("confirm_message") !== "undefined" ) {
				if( $(button).data("confirm") == "1" ) {
					// Messaggio di conferma...
					webfly_engine.confirm_event = event;
					$('#WEBFLY_CONFIRM_TITLE').html( $(button).data("confirm_title") );
					$('#WEBFLY_CONFIRM_MESSAGE').html( $(button).data("confirm_message") );
					$('#WEBFLY_CONFIRM_EVENT').modal();
					return;
				}
			}	
		}		
		
		$(submit_form).children('fieldset').prop("disabled", "disabled" );
		
		var spinner = '<div id="webfly_event_spinner" class="progress-spinner progress-spinner-double progress-spinner-active webfly-event-spinner">';
		spinner += '<div class="progress-spinner-inner"></div>';
		spinner += '<div class="progress-spinner-inner"></div>';
		spinner += '<span class="sr-only">Caricamento...</span>';
		spinner += '</div>';
		
		$(submit_form).append( spinner );
		
		if( $(button).data("event") === "save_data" ) {			
			webfly_engine.log( "SAVE DATA EVENT" );
			
			REMOTE_REQUEST.q = "sf";
		} else {
			if( typeof $(button).data("event") === 'undefined' ) {
				REMOTE_REQUEST.q     = "bc";
				REMOTE_REQUEST.bn    = $(button).attr("name");
			} else {
				REMOTE_REQUEST.q     = "bc";
				REMOTE_REQUEST.bn    = $(button).data("event");
			}
		}	
		
		// Called form
		REMOTE_REQUEST.fn = encodeURIComponent(btoa($(submit_form).data("classname")));
		
		// Remote-KEY
		if( typeof $(button).data("remote_key") !== 'undefined' )
			REMOTE_REQUEST.rkey = $(button).data("remote_key");
		
		// Remote-ID
		if( typeof $(button).data("remote_id") !== 'undefined' )
			REMOTE_REQUEST.rid = $(button).data("remote_id");

		if( WebFLY_DEBUG ) {
			console.log( "BUTTON_CLICK" );
			console.log( REMOTE_REQUEST );
		}
		
		// Server
		var server = $(submit_form).data("server");
		if (typeof server === 'undefined') {
			server = "/runtime/server.php";
		}
		
		$.ajax( { url		: WebFLY_SERVER,
				  type		: 'POST',
				  data		: REMOTE_REQUEST
				})
			.done(function( risposta ) {
				// Evento completato...
				webfly_engine.processa_eventi_server( risposta );
			})
			
			.fail(function(xhr, err) {
				$('#WEBFLY_MODAL_TITLE')   = "ERRORE DI COLLEGAMENTO";
			
				var responseTitle= $(xhr.responseText).filter('title').get(0);
				var messaggio = $(responseTitle).text() + "\n" + formatErrorMessage(xhr, err); 
				$('#WEBFLY_MODAL_MESSAGE') = "ERRORE Server "+server+" : "+messaggio;
			})
			
			.always(function() {
				webfly_engine.unlock_form();
			});

	}

	
	visualizza_errore( messaggio, titolo = "" ) {
		
		if( messaggio == "AUTH_REQUIRED" )
			location.href="index.php";
		
		webfly_engine.log( messaggio, false );
		
		if( titolo == "" )
			titolo = "ERRORE APPLICAZIONE";
		
		$('#WEBFLY_MODAL_TITLE').html( titolo );
		$('#WEBFLY_MODAL_MESSAGE').html( "<pre>"+messaggio+"</pre>" );
		$('#WEBFLY_MODAL').modal();		
	}
	
	processa_eventi_server( eventi ) {
		// Verifica se il server ha restituito un messaggio di errore...
		
		if( eventi.substr( 0,3 ) != "WF;" ) {
			var messaggio = "";
			if( eventi.substr( 0,4 ) == "ERR;" ) {
				messaggio = eventi.replace( "ERR;", "" );
				var messaggio_ascii = atob( messaggio );
				try {
					messaggio = JSON.parse( messaggio_ascii );
				} catch( e ) {
					messaggio = messaggio_ascii;
					webfly_engine.log( eventi );
				}
			} else messaggio = eventi;
			
			if( messaggio == "AUTH-REQUIRED" )
				window.location.href = "index.php";
			else
				webfly_engine.visualizza_errore( messaggio );

		} else {
			var messaggio = eventi.replace( "WF;", "" );
			var messaggio_ascii = atob( messaggio );
			try {
				messaggio = JSON.parse( messaggio_ascii );
						
				if( typeof messaggio.MESSAGE.COMMANDS !== 'undefined' ) {
							
					// ******************************************************************************************//
					// ** REMOTE COMMANDS HANDLER                                                              **//
					// ** Processa i comandi dal server.                                                       **//
					// ******************************************************************************************//
					webfly_engine.exec_server_events( messaggio.MESSAGE.COMMANDS );
				} else {
					console.log( "MESSAGGIO NON RICONOSCIUTO" );
					console.log( messaggio );
				}
				
			} catch( e ) {
				messaggio = messaggio_ascii;
				webfly_engine.log( "ERRORE IN FASE DI ESECUZIONE EVENTO" );
				webfly_engine.log( eventi );
				webfly_engine.log( e.message, true );
			}
		}
	}
	
	exec_server_events( REMOTE_COMMANDS ) {
		try {
			REMOTE_COMMANDS.forEach( function( REMOTE_COMMAND ) {
				webfly_engine.exec_command( REMOTE_COMMAND );
			});			
		} catch( e ) {
			webfly_engine.log( "ERRORE IN FASE DI ESECUZIONE EVENTO" );
			webfly_engine.log( e.message, true );
		}		
	}
	
	attach_handlers() {
		var id_canvas = webfly_engine.get_canvas();
		
		// KEYPRESS PER BOTTONE DI DEFAULT 		
		$(id_canvas +' form').unbind( "keypress" );
		$(id_canvas +' form').on( "keypress", function( ev ) {
			var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
				var form_id = ev.currentTarget.id;
				
				if( typeof form_id !== 'undefined' ) {
					$('#'+form_id + " button[data-default='1']").trigger( "click" );
				}
            }			
		});
		
		// EVENTI BUTTON...
		$(id_canvas +' button' ).each( function () {
			if( !$(this).hasClass( "dropdown-toggle" ) && !$(this).hasClass( "nav-link" )) {
				$(this).unbind( "click" );
				$(this).on( "click", { confirm : false }, webfly_engine.button_click );
			}
		});		
		
		// EVENTI BUTTON SU FORM POPUP...
		$('#WEBFLY_POPUP_FORM button' ).each( function () {
			if( !$(this).hasClass( "dropdown-toggle" ) && !$(this).hasClass( "nav-link" ) && !$(this).hasClass( "close" )) {
				if( typeof $(this).data("dismiss") === 'undefined' ) {
					$(this).unbind( "click" );
					$(this).on( "click", { confirm : false }, webfly_engine.button_click );
				}
			}
		});		
		
		// EVENTI DI CONFERMA...
		$('#WEBFLY_CONFIRM_YES' ).each( function () {
			$(this).unbind( "click" );
			$(this).on( "click", {confirm : true }, webfly_engine.button_click );
		});
		
		// EVENTI GESTITI DA REMOTO...
		webfly_engine.remote_events.forEach( function (remote_event) {
			var oggetto = $('#'+remote_event.TARGET);
			
			if( oggetto.length > 0 ) {
				// Oggetto trovato... verifico se è necessario agganciare l'evento...
				var escludi = false;
				
				if( oggetto.is( 'button' ) !== false ) {
					if( !oggetto.hasClass( "dropdown-toggle" ) && !oggetto.hasClass( "nav-link" ))
						escludi = true;
				}	
				
				if( !escludi ) {
					$('#'+remote_event.TARGET).unbind( remote_event.EVENT );
					$('#'+remote_event.TARGET).on( remote_event.EVENT, webfly_engine.event_manager );
					
					// Puntatore...
					if( $('#'+remote_event.TARGET).css('cursor') != 'pointer' )
						$('#'+remote_event.TARGET).addClass('webfly-remote-event-managed');
				}
			}
		});
	}
	
	close_console() {
		if( !$('#webfly_console_window').hasClass( 'd-none' ) ) {
			$('#webfly_console_window').addClass( 'd-none' );
			$('#webfly_console_window').removeClass( 'webfly-debug-fullscreen' );
			$('#webfly_console').empty();
		}
		return false;
	}	
	
	open_console() {
		if( $('#webfly_console_window').hasClass( 'd-none' ) ) {
			$('#webfly_console_window').removeClass( 'd-none' );
			$('#webfly_console_window').removeClass( 'webfly-debug-fullscreen' );
		}
	}
	
	console_size() {
		if( !$('#webfly_console_window').hasClass( 'webfly-debug-fullscreen' ) ) {
			$('#webfly_console_window').addClass( 'webfly-debug-fullscreen' );
		} else {
			$('#webfly_console_window').removeClass( 'webfly-debug-fullscreen' );
		}
	}
	
	log( message, error = false ) {
		var messaggio = '<div';
		if( error )
			messaggio += ' class="bg-danger text-white p-1 overflow-auto"';
		
		messaggio += '>'+message+'</div>';
		
		$('#webfly_console').append( messaggio );
		
		if( error ) 
			this.open_console();
	}
	
	get_canvas() {
		if( $('#webfly_canvas_0').hasClass( 'd-none' ) ) {
			return '#webfly_canvas_1';
		}
		
		if( $('#webfly_canvas_1').hasClass( 'd-none' ) ) {
			return '#webfly_canvas_0';
		}	
		
		return '#webfly_canvas_1';
	}	
	
	next_canvas() {
		if( $('#webfly_canvas_0').hasClass( 'd-none' ) ) {
			return '#webfly_canvas_0';
		}
		
		if( $('#webfly_canvas_1').hasClass( 'd-none' ) ) {
			return '#webfly_canvas_1';
		}	
		
		return '#webfly_canvas_0';
	}
	
	activate_canvas( id_canvas ) {
		
		if( id_canvas == '#webfly_canvas_0' ) {
			if( $('#webfly_canvas_0').hasClass( 'd-none' ) ) {
				$('#webfly_canvas_0').removeClass( 'd-none' );
				$('#webfly_canvas_1').addClass( 'd-none' );
			}
		} else {
			if( $('#webfly_canvas_1').hasClass( 'd-none' ) ) {
				$('#webfly_canvas_1').removeClass( 'd-none' );
				$('#webfly_canvas_0').addClass( 'd-none' );
			}
		}
	
	}
	
	bootstrap_refresh() {
		// Select...
		$('.selectpicker').selectpicker('refresh');
		
		// Labels...
		$('input').each( function() {
			if( typeof $(this).attr('id') !== 'undefined' ) {
				if( $(this).val() != "" && !$(this).hasClass("form-check-input") ) {
					if( !$("label[for='"+$(this).attr('id')+"']").hasClass( 'active' ) )
						$("label[for='"+$(this).attr('id')+"']").addClass( 'active' );
				}
			}
		});
		
		// Tabs...
		
		var triggerTabList = [].slice.call(document.querySelectorAll("ul[role='tablist'] a"))
		triggerTabList.forEach(function (triggerEl) {
		  var tabTrigger = new bootstrap.Tab(triggerEl)

		  triggerEl.addEventListener('click', function (event) {
			event.preventDefault()
			tabTrigger.show()
		  })
		})

		enable_layout_components();
	}
	
	export_params() {
		return JSON.stringify( webfly_engine.local_params );
	}
	
	// REMOTE COMMANDS HANDLER...

	
	get_form_parameters() {
		var form_parameters = webfly_engine.export_params();
		
		if( form_parameters != "[]" ) {
			form_parameters = "WF_fp="+encodeURIComponent(btoa(form_parameters));
		}
		
		return form_parameters;
	}
	
	get_form_data( submit_form ) {
		var form_data = $(submit_form).serialize();
		
		// Recupera_parametri...
		var form_parameters = webfly_engine.get_form_parameters();
		
		if( form_parameters != "[]" ) {
			if( form_data != "" )
				form_data += "&"+form_parameters;
			else
				form_data = form_parameters;
		}
		
		// Recupera CLASSI richieste...
		
		// Recupera ATTR richiesti...
		
		return form_data;
	}

	server_request( REMOTE_REQUEST, callback ) {
		$.ajax( { url		: WebFLY_SERVER,
				  type		: 'POST',
				  data		: REMOTE_REQUEST
				} )
			.done(function( risposta ) {
				// Carica completato... visualizza il form ed attiva gli event-handlers
				
				if( risposta.substring( 0, 3 ) != "WF;" ) {
					var messaggio = "";
					if( risposta.substr( 0,4 ) == "ERR;" ) {
						risposta = risposta.replace( "ERR;", "" );
						var messaggio_ascii = atob( risposta );
						try {
							messaggio = JSON.parse( messaggio_ascii );
						} catch( e ) {
							messaggio = messaggio_ascii;
							webfly_engine.log( eventi );
						}
					} else messaggio = risposta;
					
					webfly_engine.log( messaggio, true );
				} else {
					// Risposta formalmente corretta...
					var struttura_base64 = risposta.substring( 3 );

					var struttura = atob( struttura_base64 );
					
					try {
						var remote_message = JSON.parse( struttura );						
						callback( remote_message );
					} catch( e )  {
						webfly_engine.log( e.message, true );
					}
				}
			})
			
			.fail(function(xhr, err) {
				$('#WEBFLY_MODAL_TITLE')   = "ERRORE DI COLLEGAMENTO";
			
				var responseTitle= $(xhr.responseText).filter('title').get(0);
				var messaggio = $(responseTitle).text() + "\n" + formatErrorMessage(xhr, err); 
				
				webfly_engine.visualizza_errore( "ERRORE Server "+server+" : "+messaggio );
			})
			
			.always(function() {
				webfly_engine.unlock_form();
			});	
	}

	add_command( command_id, handler ) {
		var new_command = {};
		
		new_command.id      = command_id;
		new_command.handler = handler;
		
		if( webfly_engine.remote_command_handlers.length > 0 ) {
			webfly_engine.remote_command_handlers[ webfly_engine.remote_command_handlers.length ] = new_command;
		} else {
			webfly_engine.remote_command_handlers = [];
			webfly_engine.remote_command_handlers[0] = new_command;
		}			
	}
	
	exec_command( REMOTE_COMMAND ) {
		var trovato = false;
		webfly_engine.remote_command_handlers.forEach( function( remote_manager ) {
			if( remote_manager.id == REMOTE_COMMAND.COMMAND ) {
				remote_manager.handler( REMOTE_COMMAND.PARAMS );
			}
		});
	}
	
	load_form( form_name ) {
				
		var STARTUP = {};
		STARTUP.COMMAND 			= WEBFLY_REMOTE_COMMANDS.LOAD_FORM;
		STARTUP.PARAMS 				= {};
		STARTUP.PARAMS.form_name 	= form_name;
		
		webfly_engine.exec_command( STARTUP );
	}
	
	unlock_form() {
		$('#webfly_event_spinner').remove();
		$('#webfly_report_spinner').remove();
		$('[disabled]').removeAttr('disabled');
	}
}

var webfly_engine = new WebFLY();