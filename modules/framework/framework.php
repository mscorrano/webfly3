<?php

class WEBFLY_FRAMEWORK {
	public $DB;
	public $UTENTE;
	public $PROGETTO;
	public $SESSION;
	
	function __construct( $PARAMS ) {
		if( !class_exists( "DB_MANAGER" )) {
			include( __DIR__."/../../lib/database/dbmanager.php" );
		}
		
		$this->DB = new DB_MANAGER( $PARAMS["DATABASE"]["DB_SERVER"], $PARAMS["DATABASE"]["USERNAME"], $PARAMS["DATABASE"]["PASSWD"], $PARAMS["DATABASE"]["DATABASE"] );
	}
	
	function engine_start() {
		// WEBFLY_MODAL
		echo '<div class="modal fade" tabindex="-1" role="dialog" id="WEBFLY_MODAL" aria-labelledby="WEBFLY_MODAL_TITLE" data-bs-backdrop="static" style="z-index:1600">'.PHP_EOL;
		echo '	<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down" role="document">'.PHP_EOL;
		echo '  	<div class="modal-content">'.PHP_EOL;
		echo '      	<div class="modal-header">'.PHP_EOL;
		echo '        		<h5 class="modal-title" id="WEBFLY_MODAL_TITLE">TITOLO</h5>'.PHP_EOL;
		echo '        			<button class="close" type="button" data-dismiss="modal" aria-label="Close">'.PHP_EOL;
		echo '          			<svg class="icon">'.PHP_EOL;
		echo '            				<use href="/components/bootstrap_italia/svg/sprite.svg#it-close"></use>'.PHP_EOL;
		echo '          			</svg>'.PHP_EOL;
		echo '        			</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-body">'.PHP_EOL;
		echo '        		<p id="WEBFLY_MODAL_MESSAGE">MESSAGGIO</p>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-footer">'.PHP_EOL;
		echo '        		<button class="btn btn-primary btn-sm" data-dismiss="modal" type="button">Ok</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '    	</div>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;

		// WEBFLY CONFIRM EVENT
		echo '<div class="modal fade" tabindex="-1" role="dialog" id="WEBFLY_CONFIRM_EVENT" aria-labelledby="WEBFLY_CONFIRM_TITLE" data-remote_event="" data-key="" data-id="" data-called_form="" data-bs-backdrop="static">'.PHP_EOL;
		echo '	<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down" role="document">'.PHP_EOL;
		echo '  	<div class="modal-content">'.PHP_EOL;
		echo '      	<div class="modal-header">'.PHP_EOL;
		echo '        		<h5 class="modal-title" id="WEBFLY_CONFIRM_TITLE">TITOLO</h5>'.PHP_EOL;
		echo '        			<button class="close" type="button" data-dismiss="modal" aria-label="Close">'.PHP_EOL;
		echo '          			<svg class="icon">'.PHP_EOL;
		echo '            				<use href="/components/bootstrap_italia/svg/sprite.svg#it-close"></use>'.PHP_EOL;
		echo '          			</svg>'.PHP_EOL;
		echo '        			</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-body">'.PHP_EOL;
		echo '        		<p id="WEBFLY_CONFIRM_MESSAGE">MESSAGGIO</p>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-footer">'.PHP_EOL;
		echo '        		<button id="WEBFLY_CONFIRM_YES"    class="btn btn-success btn-sm" data-dismiss="modal" type="button" data-event_source="">S&igrave;</button>'.PHP_EOL;
		echo '        		<button id="WEBFLY_CONFIRM_NO"     class="btn btn-warning ml-2 btn-sm" data-dismiss="modal" type="button">No</button>'.PHP_EOL;
		echo '        		<button id="WEBFLY_CONFIRM_CANCEL" class="btn btn-danger ml-2 btn-sm" data-dismiss="modal" type="button">Annulla</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '    	</div>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		
		// REPORTS POPUP
		echo '<div class="modal" id="webfly_report_canvas" tabindex="-1" aria-labelledby="webfly_report_title">'.PHP_EOL;
		echo '	<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down" role="document" data-bs-backdrop="static">'.PHP_EOL;
		echo '		<div class="modal-content">'.PHP_EOL;
		echo '			<div class="modal-header">'.PHP_EOL;
        echo '				<h5 class="modal-title" id="webfly_report_title">...</h5>'.PHP_EOL;
        echo '				<button class="close" type="button" data-dismiss="modal" aria-label="Close">'.PHP_EOL;
		echo '          		<svg class="icon">'.PHP_EOL;
		echo '            			<use href="/components/bootstrap_italia/svg/sprite.svg#it-close"></use>'.PHP_EOL;
		echo '          		</svg>'.PHP_EOL;
		echo '              </button>'.PHP_EOL;
		echo '			</div>'.PHP_EOL;
		echo '			<div class="modal-body">'.PHP_EOL;
		echo '				<div class="embed-responsive embed-responsive-16by9">'.PHP_EOL;
		echo '					<iframe id="webfly_report_content"></iframe>'.PHP_EOL;
		echo '				</div>'.PHP_EOL;
		echo '			</div>'.PHP_EOL;
		echo '      	<div class="modal-footer">'.PHP_EOL;
		echo '        		<button class="btn btn-danger btn-sm" data-dismiss="modal" type="button">Chiudi</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '		</div>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		
		// APPLICATION CANVAS
		echo '<div class="container mt-4 p-4 bg-white webfly-canvas" id="webfly_canvas_0">'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		echo '<div class="container mt-4 p-4 bg-white webfly-canvas d-none" id="webfly_canvas_1">'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		
		// CONSOLE WINDOW
		echo '<div class="webfly-debug-console d-none"id="webfly_console_window">'.PHP_EOL;
		echo '<div class="webfly-console-title" onclick="webfly_engine.console_size();" >WebFLY DEBUG CONSOLE'.PHP_EOL;
		echo '<div class="float-right"><a href="#" onclick="return webfly_engine.close_console();"><i class="fa-solid fa-rectangle-xmark"></i></a></div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		echo '<div id="webfly_console" class="webfly-console-body">'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;		
		if( array_key_exists( "FRWK_FORM_CALL", $this->SESSION )) {
			echo '<script>'.PHP_EOL;
			echo 'var WEBFLY_STARTUP_FORM="'.$this->SESSION["FRWK_FORM_CALL"].'";'.PHP_EOL;
			echo '</script>'.PHP_EOL;
		}
		
		// POPUP FORM
		echo '<div class="modal fade" tabindex="-1" role="dialog" id="WEBFLY_POPUP_FORM" aria-labelledby="WEBFLY_POPUP_FORM_TITLE" data-bs-backdrop="static">'.PHP_EOL;
		echo '	<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down" role="document">'.PHP_EOL;
		echo '  	<div class="modal-content">'.PHP_EOL;
		echo '      	<div class="modal-header">'.PHP_EOL;
		echo '        		<h5 class="modal-title" id="WEBFLY_POPUP_FORM_TITLE">WEBFLY 3.0</h5>'.PHP_EOL;
		echo '        			<button class="close" type="button" data-dismiss="modal" aria-label="Close">'.PHP_EOL;
		echo '          			<svg class="icon">'.PHP_EOL;
		echo '            				<use href="/components/bootstrap_italia/svg/sprite.svg#it-close"></use>'.PHP_EOL;
		echo '          			</svg>'.PHP_EOL;
		echo '        			</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-body pb-4" id="WEBFLY_POPUP_BODY">'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '    	</div>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		
		// UPLOAD
		/*
		echo '<div class="modal fade" tabindex="-1" role="dialog" id="WEBFLY_UPLOAD" aria-labelledby="WEBFLY_UPLOAD_TITLE" data-bs-backdrop="static">'.PHP_EOL;
		echo '	<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-xl-down" role="document">'.PHP_EOL;
		echo '  	<div class="modal-content">'.PHP_EOL;
		echo '      	<div class="modal-header">'.PHP_EOL;
		echo '        		<h5 class="modal-title" id="WEBFLY_UPLOAD_TITLE">WEBFLY 3.0</h5>'.PHP_EOL;
		echo '        			<button class="close" type="button" data-dismiss="modal" aria-label="Close">'.PHP_EOL;
		echo '          			<svg class="icon">'.PHP_EOL;
		echo '            				<use href="/components/bootstrap_italia/svg/sprite.svg#it-close"></use>'.PHP_EOL;
		echo '          			</svg>'.PHP_EOL;
		echo '        			</button>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '      	<div class="modal-body pb-4" id="WEBFLY_UPLOAD_BODY">'.PHP_EOL;
		echo '				<form class="upload-dragdrop" method="post" action="" enctype="multipart/form-data" data-bs-upload-dragdrop>'.PHP_EOL;
		echo '				  <div class="upload-dragdrop-image">'.PHP_EOL;
		echo '				    <img src="/bootstrap-italia/dist/assets/upload-drag-drop-icon.svg" alt="descrizione immagine" aria-hidden="true">'.PHP_EOL;
		echo '				    <div class="upload-dragdrop-loading">'.PHP_EOL;
		echo '				      <div class="progress-donut" data-bs-progress-donut></div>'.PHP_EOL;
		echo '				    </div>'.PHP_EOL;
		echo '				    <div class="upload-dragdrop-success">'.PHP_EOL;
		echo '				      <svg class="icon" aria-hidden="true"><use href="/bootstrap-italia/dist/svg/sprites.svg#it-check"></use></svg>'.PHP_EOL;
		echo '				    </div>'.PHP_EOL;
		echo '				  </div>'.PHP_EOL;
		echo '				  <div class="upload-dragdrop-text">'.PHP_EOL;
		echo '				    <p class="upload-dragdrop-weight">'.PHP_EOL;
		echo '				      <svg class="icon icon-xs" aria-hidden="true"><use href="/bootstrap-italia/dist/svg/sprites.svg#it-file"></use></svg>'.PHP_EOL;
		echo '				    </p>'.PHP_EOL;
		echo '				    <h5>Trascina il file per caricarlo</h5>'.PHP_EOL;
		echo '				    <p>oppure <input type="file" name="WEBFLY_UPLOAD_FILE" id="WEBFLY_UPLOAD_FILE" class="upload-dragdrop-input" /><label for="WEBFLY_UPLOAD_FILE">selezionalo dal dispositivo</label></p>'.PHP_EOL;
		echo '				  </div>'.PHP_EOL;
		echo '				  <input value="Submit" type="submit" class="d-none" />'.PHP_EOL;
		echo '				</form>'.PHP_EOL;
		echo '      	</div>'.PHP_EOL;
		echo '    	</div>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
		echo '</div>'.PHP_EOL;		
		*/
	}
	
	function init_runtime() {
		WEBFLY_REMOTE_COMMANDS::generate_remote_commands();
		// WebFLY ENGINE
		echo '<script src="/runtime/webfly.js?seed='.md5(date("YmdHis")).'"></script>'.PHP_EOL;
		echo '<script src="/runtime/client_engine/webfly_client.php?seed='.md5(date("YmdHis")).'"></script>'.PHP_EOL;
	}
}