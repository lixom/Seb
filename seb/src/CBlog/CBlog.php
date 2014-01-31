<?php

/**
 * CBlog, class that displays blog content
 *
 */
class CBlog {

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
   * Gets  blog posts for slug
   *
   */
  public function getBlogContentForSlug($slug) {
	  
	 // Get content
	 $sql = "SELECT * FROM {$this->tableName}
					WHERE type = 'post' AND slug = ? AND published <= NOW()
					ORDER BY updated DESC;";
	
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));
		return $result;
  }
  
    
  /*
   * Gets all blog posts
   *
   */
  public function getAllBlogContent() {
	  
	 // Get content
	 $sql = "SELECT * FROM {$this->tableName}
					WHERE type = 'post' AND published <= NOW()
					ORDER BY updated DESC;";
	
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql);
		return $result;
  }
  
  
  /**
	 * Creates a short text
	 *
	 */
	function getExcerpt($text, $cut = 100) {

		$shortText = substr($text, 0, $cut); // Cut to 200 charachters
		$spacePos = strrpos($shortText,	" "); // Finds the last space position
		$finalText = substr($text, 0, $spacePos);
		$finalText = trim($finalText, '');
		
		return  $finalText;
	}

}
?>