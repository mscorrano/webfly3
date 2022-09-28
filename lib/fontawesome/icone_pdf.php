<?php
	
	function icona( $icona ) {
		if (!extension_loaded('imagick'))
			die( 'Estensione imagick necessaria.Si prega di installarla' );
		
		$PATH_FA    = __DIR__ ."/../../components/fontawesome/svgs/";
		$PATH_CACHE = __DIR__ ."/cache/";
		
		$nome_svg = "";
		
		if( strpos( $icona, " " )===false )
			$icona = "fas ".$icona;
		
		list( $tipo, $icona_fa ) = explode( " ", $icona );
		
		switch( $tipo ) {
			case "fa-solid":
				$nome_svg = $PATH_FA."solid/";
				$prefisso = "fas_";
				break;
				
			case "fa-regular":
				$nome_svg = $PATH_FA."regular/";
				$prefisso = "far_";
				break;
				
			case "fa-brands":
				$nome_svg = $PATH_FA."brands/";
				$prefisso = "fab_";
				break;
				
			default:
				die( "ICONA $icona DI CATEGORIA NON TROVATA" );
		}
		
		$nome_file  = str_replace( "fa-", "", $icona_fa );
		
		$nome_svg   = $PATH_FA."solid/".$nome_file.".svg";
		$nome_icona = $PATH_CACHE.$prefisso.$nome_file.".jpg";
		
		if( !file_exists( $nome_svg ))
			die( "FILE SVG DELL'ICONA $icona NON TROVATO ($nome_file)" );
		
		$svg = file_get_contents($nome_svg);
		$convertitore = new Imagick();

		$convertitore->readImageBlob($svg);

		$convertitore->setImageFormat("jpeg");
		$convertitore->resizeImage(256, 256, imagick::FILTER_LANCZOS, 1);  

		$convertitore->writeImage($nome_icona);

		return $nome_icona;
	}
	
?>