<?php

//require_once 'Customweb/Database/Driver/AbstractDriver.php';
//require_once 'Customweb/Database/IDriver.php';
//require_once 'Customweb/Database/Driver/MySQLi/Statement.php';


/**
 * This driver implementation allows the handling MySQLi. The driver requires a valid
 * connection to the database. 
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_Database_Driver_MySQLi_Driver extends Customweb_Database_Driver_AbstractDriver implements Customweb_Database_IDriver {
	private $link;

	/**
	 * The resource link is the connection link to the database.
	 *
	 * @param resource $resourceLink        	
	 */
	public function __construct($resourceLink){
		$this->link = $resourceLink;
	}

	public function beginTransaction(){
		$this->query("START TRANSACTION;");
		$this->setTransactionRunning(true);
	}
	
	public function commit(){
		$this->query("COMMIT;");
		$this->setTransactionRunning(false);
	}
	
	public function rollBack(){
		$this->query("ROLLBACK;");
		$this->setTransactionRunning(false);
	}

	public function query($query){
		$statement = new Customweb_Database_Driver_MySQLi_Statement($query, $this);
		return $statement;
	}

	public function quote($string){
		if (function_exists('mysqli_real_escape_string')) {
			$string = mysqli_real_escape_string($this->getLink(), $string);
		} elseif (function_exists('mysqli_escape_string')) {
			$string = mysqli_escape_string($string);
		}
		
		return '"' . addslashes($string) . '"';
	}

	public function getLink(){
		return $this->link;
	}
	

	final protected function isServerSupportingSavePoints() {
		if ($this->supportsSavePoints === null) {
			$version = mysqli_get_server_info($this->link);
			if (version_compare($version, '5.0.3') >= 0) {
				$this->supportsSavePoints = true;
			}
			else {
				$this->supportsSavePoints = false;
			}
		}
		
		return $this->supportsSavePoints;
	}
	
		
}
