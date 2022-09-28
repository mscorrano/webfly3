<?php

class TINN {
	public  $DEBUG         = false;
	private $_TINN_LINK    = NULL;
	
	function __construct(  $database = "DBEUROCF.IDB" ) {
		$this->DEBUG = false;
		$this->_TINN_LINK = NULL;
		$this->connect( $database );
	}
	
	function connect( $database ) {
	
		$SERVER   = 'dbname=localhost:/DATABASE_TINN/'.$database;
		$USERNAME = "SYSDBA";
		$PWD      = "MASTERKEY";
		
		if( gettype($this->_TINN_LINK)!="object" )
			$this->close();
		
		try {
			$this->_TINN_LINK = new PDO( 'firebird:'.$SERVER, $USERNAME, $PWD, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION] );
		} catch( PDOException $e ) {
			$trace = debug_backtrace();
			die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore in fase di connessione al database (Codice #'.$e->getCode().'):'. $e->getMessage() );
		}
	}
	
	function close( $database = "DBEUROCF" ) {
		if( gettype($this->_TINN_LINK)!="object" ) {
			$this->_TINN_LINK = NULL;
		}	
	}

	function exec_sql( $sql, $fetch_row=false, $parametri = array(), $style=PDO::FETCH_ASSOC, $dont_stop = false, $insert_ignore = false ) {
		   
		if( $this->DEBUG == true ) {
			echo '<div class="alert alert-warning">'.$sql.'</div>';
		} 
		
		if( gettype($this->_TINN_LINK)!="object" )
			$this->connect();
		
		$resultset = array();
		
		try {
			$frase = $this->_TINN_LINK->prepare( $sql );
			$frase->execute( $parametri );
			
			$rs = $frase->fetchAll($style);
		 
			if( $this->DEBUG == true ) {
				echo '<pre>';
				print_r( $rs );
				echo '</pre>';
			}        
			
			if( count($rs)>=1 && $fetch_row==true )
				return $rs[0];
			 else
				return $rs; 
			
		}  catch( PDOException $e ) {
			$trace = debug_backtrace();
			die('['.$trace[0]["file"].":".$trace[0]["function"].'#'.$trace[0]["line"].'] Errore in fase di connessione al database (Codice #'.$e->getCode().'):'. $e->getMessage() );
		}
		
		return array();
	}
}

?>