<?php

//require_once 'Customweb/Database/Driver/AbstractDriver.php';
//require_once 'Customweb/Database/Driver/MySQL/Statement.php';
//require_once 'Customweb/Core/Assert.php';
//require_once 'Customweb/Database/IDriver.php';


/**
 * This driver implementation allows the handling MySQL. The driver requires a valid
 * connection to the database. 
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_Database_Driver_MySQL_Driver extends Customweb_Database_Driver_AbstractDriver implements Customweb_Database_IDriver {
	private $link;
	private $supportsSavePoints = null;

	/**
	 * The resource link is the connection link to the database.
	 *
	 * @param resource $resourceLink        	
	 */
	public function __construct($resourceLink){
		Customweb_Core_Assert::notNull($resourceLink);
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
		$statement = new Customweb_Database_Driver_MySQL_Statement($query, $this);
		return $statement;
	}

	public function quote($string){
		if (function_exists('mysql_real_escape_string')) {
			$string = mysql_real_escape_string($string, $this->getLink());
		} elseif (function_exists('mysql_escape_string')) {
			$string = mysql_escape_string($string);
		}
		
		return '"' . addslashes($string) . '"';
	}

	public function getLink(){
		return $this->link;
	}
	
	final protected function isServerSupportingSavePoints() {
		if ($this->supportsSavePoints === null) {
			$version = mysql_get_server_info($this->link);
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
