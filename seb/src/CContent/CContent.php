<?php

/**
 * CContent, class that represents content
 *
 */
class CContent {

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
	 * Creates database tables
	 *
	 */
	public function restoreDatabase() {
	
	}
	
	/*
	 * Gets all published content
	 *
	 * @return string with html to display content
	 */
	public function getAllPublishedContent() {
		$sql = "SELECT *, (published <= NOW()) AS available FROM {$this->tableName}";
		
		
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql);
		$listHTML = "";
		
		foreach($result AS $key => $val) {
			$listHTML .= $val->TYPE;
			$listHTML .= isset($val->available) ? " (publicerad) " : " (ej publicerad) ";
			$listHTML .= $val->title;
			$listHTML .= " (";
			$listHTML .= "<a href=\"edit.php?id=$val->id\">Editera</a>, ";
			$listHTML .= '<a href="' .$this->getUrlToContent($val). '">Visa</a>)';
			$listHTML .= "<br />";
		}

		return $listHTML;
	}
	
	
	
	/*
	 * Gets content with specific id
	 *
	 * @param 
	 * @return object for the specified id
	 */
	public function getContent($id) {
		
		$sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
		$params = array($id);
		
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
		
		return $result;
	}
	
	/*
	 * Creates new content in db
	 *
	 */
	public function insertContent($params) {
		$sql = "
						INSERT INTO {$this->tableName} (title, slug, url, data, type, filter, published, created)
						VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
		

	  $res = $this->db->ExecuteQuery($sql, $params);
		if($res) {
	    $output = 'Informationen sparades.';
	  } else {
	    $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
	  }
	  
	  return $output;
	}
	
	
	/*
	 * Updates content
	 *
	 */
	public function updateContent($params) {
		$sql = "
			     UPDATE {$this->tableName} SET
			     title   = ?,
			     slug    = ?,
			     url     = ?,
			     data    = ?,
			     type    = ?,
			     filter  = ?,
			     published = ?,
			     updated = NOW()
					 WHERE id = ?";
					 
	  $res = $this->db->ExecuteQuery($sql, $params);
	  
	  if($res) {
	    $output = 'Informationen sparades.';
	  } else {
	    $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
	  }
	  
	  return $output;
	}

	/*
	 * "Deletes" content. Actually sets the deleted timestamp to now.
	 *
	 */
	public function deleteContent($id) {
		$sql = "
			     UPDATE {$this->tableName} SET
			     deleted = NOW(),
			     published = NULL
					 WHERE id = ?";
					 
		$params = array($id);
	  $res = $this->db->ExecuteQuery($sql, $params);
	  
	  if($res) {
	    $output = 'Informationen sparades.';
	  } else {
	    $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
	  }
	  
	  return $output;
	}
		
		
	
	
	
  /**
   * Create a link to the content, based on its type.
   *
   * @param object $content to link to.
   * @return string with url to display content.
   */
	function getUrlToContent($content) {
	  switch($content->TYPE) {
	    case 'page': return "page.php?url={$content->url}"; break;
	    case 'post': return "blog.php?slug={$content->slug}"; break;
	    default: return null; break;
	  }
	}

	/**
	 * Create a slug of a string, to be used as url.
	 *
	 * @param string $str the string to format as slug.
	 * @returns str the formatted slug. 
	 */
	function slugify($str) {
	  $str = mb_strtolower(trim($str));
	  $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
	  $str = preg_replace('/[^a-z0-9-]/', '-', $str);
	  $str = trim(preg_replace('/-+/', '-', $str), '-');
	  return $str;
	}
	

}
?>