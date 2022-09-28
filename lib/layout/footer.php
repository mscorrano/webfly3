</div>

<div class="cookiebar bg-dark p-4 hide" aria-hidden="true">
  <div class="container">
    <div class="row d-flex align-items-center">
      <div class="col-12 col-md-7">
        <span class="text-white small">Questo sito utilizza cookie tecnici, analytics e di terze parti.<br>Proseguendo nella navigazione accetti l’utilizzo dei cookie.</span>
      </div>
      <div class="col-12 col-md-5 mt-4 mt-md-0 d-flex justify-content-end">
        <a class="btn btn-link" href="https://designers.italia.it/privacy-policy/">Privacy policy</a>
        <button class="btn btn-primary mr-2" data-accept="cookiebar">Accetto</button>
      </div>
    </div>
  </div>
</div>

<div class="fixed-bottom">
	<div id="portale_footer_mod" class="">
		<div id="jm-footer-mod-in" class="container-fluid">
			<div class="row-fluid jm-flexiblock jm-footer">
				<div class="span12">
					<div class="jm-module ">
						<div class="jm-module-in">
							<div class="jm-module-content clearfix notitle">
								<div class="custom col-12">
									<p style="margin:0px"><a href="#" onclick="webfly_engine.open_console()">
										<img style="display: block; margin-left: auto; margin-right: auto;" src="/components/img/logo-provincia-pescara-piccolo.png" alt="" /></a>
									</p>
									<hr class="footer_separatore">
									<p class="evidence1" style="text-align: center;">Piazza Italia,&nbsp;30 - 65121 Pescara PE - Telefono: 085 37241 - PEC:&nbsp;provincia.pescara@legalmail.it</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="footer-provincia">
		<div id="jm-footer-in" class="container-fluid pl-2">
			<div id="jm-copy-power" class="pull-left">
				<div id="portale_footer" class="pull-left ">
					<div class="custom pl-4">
						<p>Provincia di Pescara. &copy; Tutti i diritti riservati</p></div>
						<ul class="nav menu mod-list">
							<li class="item-81"><a href="https://www.provincia.pescara.it/index.php/note-legali">Note legali</a></li>
							<li class="item-76"><a href="https://www.provincia.pescara.it/index.php/trattamento-privacy">Trattamento Privacy</a></li>
							<li class="item-510"><a href="https://www.provincia.pescara.it/index.php/gestione-dei-cookie">Gestione dei Cookie</a></li>
							<li class="item-77"><a href="https://trasparenza.tinnvision.cloud/traspamm/00212850689/2/home.html" target="_blank" rel="noopener noreferrer" class="external-link">Trasparenza</a></li>
							<li class="item-82"><a href="https://www.provincia.pescara.it/index.php/mappa-del-sito">Mappa del sito</a></li>
							<li class="item-546"><a href="https://mail.provincia.pescara.it/zimbra/" target="_blank" rel="noopener noreferrer" class="external-link">Posta riservata</a></li>
							<li class="item-547"><a href="https://www.tinnvision.cloud/csihrmpc/?gestore=199&amp;action=actCSIHRMPC_pcmine" class="external-link">P@ycheck</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</footer>
</div>
<script id="bootstrap_engine" src="/components/bootstrap_italia/js/main.js"></script>

<script>
function enable_layout_components() {
	   // Scorre tutte le tabelle nella pagina in cerca delle tabelle da rendere dinamiche
   $('table').each( function() {
	  // Verifica se il datatable è già inizializzato... 
	  if( $(this).hasClass( "datatable" ) || $(this).hasClass( "dataTable" ))
		  return;
	  
      if( $(this).data("dynamic")===true ) {
		 lunghezza = $(this).data("righe_visibili");
		 if( lunghezza == "" )
			 lunghezza = 100;
         colonna = 0;
         ordine  = 'asc';
		 
		 if( $(this).data("select_page")===true || $(this).data("select_page")!=='undefined' )
			 length_change = false;
		 else
			 length_change = true;
		 
		 if (typeof $(this).data("select") !== 'undefined') {
			 select_style = $(this).data("select");
			 
			 var tabella = $(this).DataTable({
			   "order"         : [[ colonna, ordine ]],
			   "pageLength"    : lunghezza,
			   "bLengthChange" : length_change,
			   "language"      : { "url"   : "/components/DataTables/traduzione/italian.json" },
			   select		   : { style : select_style }
			 });
			 
			 if (typeof $(this).data("select_function") !== 'undefined') {
				tabella.on( 'select', function ( e, dt, type, indexes ) {
					if ( type === 'row' ) {
						callback = $(this).data("select_function");
						
						window[callback]( dt, true );
					}
				}); 				
				
				tabella.on( 'deselect', function ( e, dt, type, indexes ) {
					if ( type === 'row' ) {
						callback = $(this).data("select_function");
						
						window[callback]( dt, false );
					}
				}); 
			 }
		 } else {	 
			 var tabella = $(this).DataTable({
			   "order"         : [[ colonna, ordine ]],
			   "pageLength"    : lunghezza,
			   "bLengthChange" : length_change,
			   "language"      : { "url" : "/components/DataTables/traduzione/italian.json" }
			 });
		 }
      }   
   });
	
	$('[data-toggle="popover"]').popover();
   
	$('input[type=time]').each(function (index) { 
		//GLOBAL VARIABLES
		var valMin,
		  valMax,
		  valNow,
		  skipVal,
		  $spinnerInput,
		  timeH = '00',
		  timeM = '00'

		// wrapper el
		var $el = $(this)

		// get input field
		var $input = $el.find('.txtTime')
		// get bnt-time
		var $btnTime = $el.find('.btn-time')

		// get spinner
		var $spinner = $el.find('.spinner-control')
		var $spinnerH = $el.find('.spinnerHour')
		var $spinnerM = $el.find('.spinnerMin')

		var $btnHourUp = $el.find('.btnHourUp')
		var $btnHourDown = $el.find('.btnHourDown')
		var $btnMinUp = $el.find('.btnMinUp')
		var $btnMinDown = $el.find('.btnMinDown')

		// $input.attr('data-input', 'input-' + index)
		// $spinner.attr('data-spinner', 'spinner-' + index)

		var setDigit = (number) => {
		  if (number < 0) number = 0
		  return number < 10 ? '0' + number : number
		}

		var getValues = ($button) => {
		  // get spinner input
		  $spinnerInput = $button.closest('.spinner').find('input')
		  // get set values
		  valMin = parseInt($spinnerInput.attr('aria-valuemin'))
		  valMax = parseInt($spinnerInput.attr('aria-valuemax'))
		  valNow = parseInt($spinnerInput.attr('aria-valuenow'))
		  skipVal = parseInt($spinnerInput.attr('bb-skip'))
		}

		var handleClick = (action, $button) => {
		  getValues($button)
		  // manage up/down
		  switch (action) {
			case 'up':
			  if (!valMax || valNow < valMax) valNow++
			  break
			case 'down':
			  if (!valMin || valNow > valMin) valNow--
			  break
		  }

		  // manage skipVal
		  if (action && skipVal > -1) {
			switch (true) {
			  case action === 'up' && skipVal === valNow:
				valNow++
				break
			  case action === 'down' && skipVal === valNow:
				valNow--
				break
			}
		  }

		  switch (true) {
			case $button.hasClass('btnHourUp') || $button.hasClass('btnHourDown'):
			  timeH = setDigit(valNow)
			  break
			case $button.hasClass('btnMinUp') || $button.hasClass('btnMinDown'):
			  timeM = setDigit(valNow)
			  break
		  }

		  $spinnerInput.val(setDigit(valNow))
		  $spinnerInput.attr('value', setDigit(valNow))
		  $spinnerInput.attr('aria-valuenow', setDigit(valNow))

		  $input.val(timeH + ':' + timeM).change()
		}

		var handleType = ($spinnerInput, $button) => {
		  var value = setDigit($spinnerInput.val())

		  $spinnerInput.attr('aria-valuenow', value)
		  handleClick(null, $button)
		}
		
		if( typeof defLabels === 'undefined' ) {

			function allowKey(key) {
				return [8, 9, 13].includes(key)
			}

			function loadSpinner($spinner) {
				$spinner.toggleClass('is-open').attr('aria-hidden', 'false').fadeIn(100)
			}

			function hideSpinner($spinner, $input, $spinnerH, $spinnerM, index) {
				if ($spinner.hasClass('is-open')) {
				  $spinner.fadeOut(100).toggleClass('is-open').attr('aria-hidden', 'true')
				  if ($spinnerH && $spinnerM) {
					var newTime = $spinnerH.attr('value') + ':' + $spinnerM.attr('value')
					$input.val(newTime)
				  }
				  checkForm($input, index)
				}
			}

			  // save default labels
			var defLabels = {}

			  // TIME VALIDATION FOR DATA ENTRY
			function checkForm($input, index) {
				var newValue = $input.val()
				if (newValue) {
				  var $label = $input.siblings('label')

				  var matches = newValue != '' ? newValue.match(timeRegEx) : ''

				  if (matches) {
					$label.removeClass('error-label').html(defLabels[index])
				  } else {
					$label.addClass('error-label').html('Formato ora non valido (hh:mm)')
				  }
				}
			}
		}
		
		defLabels[index] = $input.siblings('label').text()

		$el.find('.spinner-control button').attr('aria-hidden', 'true').attr('tabindex', '-1')

		$btnTime.on('click', (e) => {
		  e.stopPropagation()
		  e.preventDefault()
		  if ($spinner.hasClass('is-open')) {
			hideSpinner($spinner, $input, $spinnerH, $spinnerM, index)
		  } else {
			loadSpinner($spinner)
		  }
		})

		//Direct Time Entry
		$input
		  .on('keyup', function (e) {
			var key = e.which || e.keyCode
			var val = $input.val()

			if (val.includes(':')) {
			  var hArr = val.split(':')
			  $spinnerH.attr('aria-valuenow', hArr[0].substring(0, 2))
			  $spinnerH.attr('value', hArr[0].substring(0, 2))
			  $spinnerH.val(hArr[0].substring(0, 2))
			  timeH = hArr[0].substring(0, 2)
			  $spinnerM.attr('aria-valuenow', hArr[1].substring(0, 2))
			  $spinnerM.attr('value', hArr[1].substring(0, 2))
			  $spinnerM.val(hArr[1].substring(0, 2))
			  timeM = hArr[1].substring(0, 2)
			} else {
			  $spinnerH.attr('aria-valuenow', val.substring(0, 2))
			  $spinnerH.attr('value', val.substring(0, 2))
			  $spinnerH.val(val.substring(0, 2))
			  timeH = val.substring(0, 2)
			}

			if (key === 13) {
			  return checkForm($input, index)
			}
		  })
		  .on('focus', (e) => {
			e.stopPropagation()
			if ($input.val()) {
			  checkForm($input, index)
			}
		  })
		  .on('blur', () => {
			// console.log('$input blur')
			if ($input.val()) {
			  checkForm($input, index)
			}
		  })

		$btnHourUp.on('click', (e) => {
		  handleClick('up', $btnHourUp, 'click hour up')
		  e.preventDefault()
		})

		$btnHourDown.on('click', (e) => {
		  handleClick('down', $btnHourDown, 'click hour down')
		  e.preventDefault()
		})

		$btnMinUp.on('click', (e) => {
		  handleClick('up', $btnMinUp, 'click min up')
		  e.preventDefault()
		})

		$btnMinDown.on('click', (e) => {
		  handleClick('down', $btnMinDown, 'click min down')
		  e.preventDefault()
		})

		$spinnerH
		  .on('keydown', (e) => {
			var key = e.which || e.keyCode
			var isNum = numbers.includes(key)
			switch (true) {
			  case key === 38: // up
				$btnHourUp.trigger('click')
				break
			  case key === 40: // down
				$btnHourDown.trigger('click')
				break
			  case allowKey(key) || isNum: // tab or numbers
				return true
			}
			return false
		  })
		  .on('keyup', (e) => {
			var key = e.which || e.keyCode
			var isNum = numbers.includes(key)
			if (isNum) {
			  handleType($spinnerH, $btnHourUp)
			}
		  })

		$spinnerM
		  .on('keydown', (e) => {
			var key = e.which || e.keyCode
			var isNum = numbers.includes(key)
			switch (true) {
			  case key === 38: // up
				$btnMinUp.trigger('click')
				break
			  case key === 40: // down
				$btnMinDown.trigger('click')
				break
			  case allowKey(key) || isNum: // tab or numbers
				return true
			}
			return false
		  })
		  .on('keyup', (e) => {
			var key = e.which || e.keyCode
			var isNum = numbers.includes(key)
			if (isNum) {
			  handleType($spinnerM, $btnMinUp)
			}
		  })

		$(document).on('click', () => {
		  hideSpinner($spinner, $input, $spinnerH, $spinnerM, index)
		})

		$spinner.on('click', (e) => {
		  e.stopPropagation()
		})
	});
}

$(document).ready( function() {
	enable_layout_components();
});
</script>
</body>
</html>
<?php
	echo ob_get_clean();
?>	