<?php

/**
 * CPage, class that displays content
 *
 */
class CPage {

	protected $db;
	protected $tableName;
 
  /*
   * Constructor that accepts $db credentials and creates CDatabase object
   *
   */
  public function __construct($dbCredentials, $tableName) { 
  
        $this->db = new CDatabase($dbCredentials); 
        $this->tableName = $tableName;
  }
  
	/*
	 * Gets the page for the specified url
	 *
	 * @return content
	 */
	public function getContentForURLPath($url) {
		$sql = "SELECT * FROM {$this->tableName} 
						WHERE type = 'page' AND url = ? AND published <= NOW();";
					
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));
		return $result[0];
	}

}
?>