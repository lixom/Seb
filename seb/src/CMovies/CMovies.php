<?php
/**
 * A CMovies class that represents a results table and search.
 *
 */
class CMovies {

  /**
   * Properties
   *
   */
  private $db;
  public $tableName;
  public $rowsResult;

  /*
   * Constructor that accepts $db credentials and creates CDatabase object
   *
   */
  public function __construct($dbCredentials, $tableName) { 
  
        $this->db = new CDatabase($dbCredentials); 
        $this->tableName = $tableName;
  }
  
  
  /**
   * Use the current querystring as base, modify it according to $options and return the modified query string.
   *
   * @param array $options to set/change.
   * @param string $prepend this to the resulting query string
   * @return string with an updated query string.
   */
  function getQueryString($options=array(), $prepend='?') {
    
    // parse query string into array
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);

    // Modify the existing query string with new options
    $query = array_merge($query, $options);

    // Return the modified querystring
    return $prepend . htmlentities(http_build_query($query));
}



  /**
   * Create links for hits per page.
   *
   * @param array $hits a list of hits-options to display.
   * @param array $current value.
   * @return string as a link to this page.
   */
  function getHitsPerPage($hits, $current=null) {
    $nav = "Träffar per sida: ";
    
    foreach($hits AS $val) {
      if($current == $val) {
      	$nav .= "$val ";
      } else {
        $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
      }
    } // end for  
    return $nav;
  }
  
  /**
   * Create navigation among pages.
   *
   * @param integer $hits per page.
   * @param integer $page current page.
   * @param integer $max number of pages. 
   * @param integer $min is the first page number, usually 0 or 1. 
   * @return string as a link to this page.
   */
  function getPageNavigation($hits, $page, $max, $min=1) {
    $nav  = ($page != $min) ? "<a href='" . $this->getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
    $nav .= ($page > $min) ? "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';

    for($i=$min; $i<=$max; $i++) {
      if($page == $i) {
        $nav .= "$i ";
      } else {
        $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'>$i</a> ";
      }
    } // end for

    $nav .= ($page < $max) ? "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
    $nav .= ($page != $max) ? "<a href='" . $this->getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';
    return $nav;
  }



  /**
   * Function to create links for sorting
   *
   * @param string $column the name of the database column to sort by
   * @return string with links to order by column.
   */
  function orderby($column) {
    $nav  = "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
    $nav .= "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";
    return "<span class='orderby'>" . $nav . "</span>";
  }
  
  public function getTableContents() { 
    
    // Get parameters from $_GET
    $title    = isset($_GET['title']) ? $_GET['title'] : null;
    $genre    = isset($_GET['genre']) ? $_GET['genre'] : null;
    $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 4;
    $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
    $year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
    $year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
    $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
    $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';

    // Check that incoming parameters are numbers
    is_numeric($hits) or die('Check: Hits must be numeric.');
    is_numeric($page) or die('Check: Page must be numeric.');
    is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
    is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');
       
    // Prepare the query based on incoming arguments
    
    $sqlOrig = "SELECT * FROM {$this->tableName}";
    
    $where    = null;
    $limit    = null;
    $sort     = " ORDER BY $orderby $order";
    $params   = array();
    
    // Select by title
    if($title) {
      $where .= ' AND title LIKE ?';
      $params[] = $title;
    } 
  
    // Select by year
    if($year1) {
      $where .= ' AND movieYear >= ?';
      $params[] = $year1;
    } 
    if($year2) {
      $where .= ' AND movieYear <= ?';
      $params[] = $year2;
    } 
    
    // Select by genre
		if($genre) {
		  $where .= ' AND genre = ?';
		  $params[] = $genre;
		} 
    
    // Pagination
    if($hits && $page) {
      $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
    }
    
    // Complete the sql statement
    $where = $where ? " WHERE 1 {$where}" : null;
    $sql = $sqlOrig . $where . $sort . $limit;
    
    // Just to get the num of rows so we can show correct num of pages
    $sql_rows = "SELECT COUNT(*) AS rows FROM {$this->tableName} " .$where;
    $this->fetchNumberOfRows($sql_rows, $params);
    
    // Do query
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    

		$width = 160;
		$tdWidth = $width +1;
    $movieWidth = '&amp;width='. $width;
    $conentHTML = '';
   
    $i = 0;
    foreach($res AS $key => $val) {
      $row = '<tr'; // New row
      $row .= ($i % 2 == 0) ? ' class="odd">' : '>'; // If odd add class "odd" else close bracket.
  	   
      $row .= "<td width='{$tdWidth}'><a href='movie.php?id={$val->id}'><img src='img.php?src={$val->image}$movieWidth' alt='{$val->title}' /><a></td>";
      $row .= "<td><a href='movie.php?id={$val->id}'><h3>{$val->title}</h3></a>";
      $row .= "<p>{$val->plot}</p>";
      $row .= "<span class='genre'>Genre:</span> {$val->genre}</span>";
      
      $row .= "</td>";
      $row .= "<td width='50'>{$val->movieYear}</td>";
      $row .= "</tr>";
  	   
  	   $i++;
  	   $conentHTML .= $row;
     }
    
     return $conentHTML;
  }
  
  // Takes the same sql without the limit part to get number of pages needed.
  public function fetchNumberOfRows($sqlQuery, $params) {
    
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sqlQuery, $params);
    $temp = $res[0]->rows;
    $this->rows = $temp;
  }
  
  
  public function getMovie($id) {
  
	  $sql = "SELECT * FROM {$this->tableName} WHERE id = ? LIMIT 1";
		$params = array($id);
		
		$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
		
		return $result;
  }
  
  public function getGenres() {
		$genreSQL = "SELECT DISTINCT genre FROM {$this->tableName} ORDER BY genre ASC";
		$result = $this->db->ExecuteSelectQueryAndFetchAll($genreSQL);
		return $result;
  }
  
  /*
	 * "Deletes" content. Actually sets the deleted timestamp to now.
	 *
	 */
	public function deleteMovie($id) {
		$sql = "DELETE FROM {$this->tableName}
						WHERE id = ?
						LIMIT 1";
					 
		$params = array($id);
	  $res = $this->db->ExecuteQuery($sql, $params);
	  
	  if($res) {
	    $output = 'Filmen raderades.';
	  } else {
	    $output = 'Filmen kunde ej raderas.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
	  }
	  
	  return $output;
	}

  
    
  
}