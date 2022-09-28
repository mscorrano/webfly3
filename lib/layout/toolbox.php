<?php

echo '<div class=row">';
echo '	<div class="col text-right text-white font-weight-bold mt-4 pr-4">';
echo '		<div class="container">';
if( isset( $TOOLBOX ) && is_array( $TOOLBOX ) ) {
	$primo = true;
	foreach( $TOOLBOX as $TOOL ) {
		$dati_tool = array();
		$dati_tool["bg-color"] 	= "ghostwhite";
		$dati_tool["color"]    	= "black";
		$dati_tool["link"]     	= "/index.php";
		$dati_tool["icon"]	  	= ""; 
		$dati_tool["text"]      = "TOOLBAR";
		$dati_tool["on-click"]  = "";
		foreach( $TOOL as $attribute => $value ) {
			if( array_key_exists( $attribute, $dati_tool ))
				$dati_tool[$attribute] = $value;
		}
		
		echo '<a href="'.$dati_tool["link"].'" style="background-color:'.$dati_tool["bg-color"].'; color:'.$dati_tool["color"].'"';
		if( $dati_tool["on-click"] != "" )
			echo ' onclick="'.$dati_tool["on-click"].'"';
		
		echo ' class="btn';
		if( $primo )
			$primo = false;
		else
			echo ' ml-2';
		
		echo '">';
		if( $dati_tool["icon"] != "" )
			echo '<i class="'.$dati_tool["icon"].' mr-2"></i>';
		echo $dati_tool["text"].'</a>';
	}
}
echo '		</div>';
echo '	</div>';
echo '</div>';
?>