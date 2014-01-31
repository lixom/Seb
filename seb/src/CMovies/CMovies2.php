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
  private $db; // Database object
  private $tableName; // Table name
  private $columnNames = array(); // Column names used for select, specified in that order they should apper in tableview
  private $hitsOptions = array(); // How many entries per page
  private $queryResults; // Results from query
  
  // Query parameters
  public $title;
  private $currentPage;
  private $year1;
  private $year2;
  private $columnOrderBy; // What colomn to order by
  
  
  private $sqlQuery;
  
  
  // Pagination vars
  private $hits; // How many rows to display per page.
  // private $page; // Which is the current page to display, use this to calculate the offset value
  private $max;  // Max pages in the table: SELECT COUNT(id) AS rows FROM VMovie
  private $min;  // Startpage, usually 0 or 1, what you feel is convienient
  
  
  
  /**
   * Constructor
   *
   * If no year and month speicfied, set to null,
   * this way the init method will set it to current date
   */
  public function __construct($db, $tableName, $columnNames, $hitsOptions=array(2,4,6)) {
  	   	 
  	 $this->db           = $db; // Assing CDatabase object
  	 $this->tableName    = $tableName;
  	 $this->columnNames  = $columnNames; 
  	 $this->hitsOptions = $hitsOptions;
  	 
  	 // Initilize based on url query
  	 $this->init(); 
  }
  
  /** 
   * Init
   *
   *
   */
  private function init() {
    
    // Init object with variables from querystring
    
    // Temp vars with default values if no set
    $title    = isset($_GET['title']) ? $_GET['title'] : null;
    $hits     = isset($_GET['hits'])  ? $_GET['hits']  : $this->hitsOptions[2];
    $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
    $year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
    $year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
    $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : $this->columnNames[0];
    $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'ASC';
    
    // Validate numeric values  
    is_numeric($hits) or die('Check: Hits must be numeric.');
    is_numeric($page) or die('Check: Page must be numeric.');
    is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
    is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');
    
    
    // Assign tempvars to object and sanitize
    $this->title = htmlentities($title);
    $this->hits = $hits;
    $this->page = $page;
    $this->year1 = $year1;
    $this->year2 = $year2;
    $this->columnOrderBy = htmlentities($orderby);
    $this->order = htmlentities($order);
    

  }
  
  /**
   * fetchMovies
   *
   * Gets all movies matching the querystring and saves result i instance variable $queryResults
   */
  public function fetchMovies() {
    
    $sql = $this->generateSQL();
  
    $db = $this->db;
    $this->queryResults = $db->ExecuteSelectQueryAndFetchAll($sql['query'], $sql['params']);
    
  }
  
  
  /**
   * GenerateSQL
   *
   * Generates sqlquery based on set $_GET
   *
   * @param integer $hits per page.
   * @param integer $page current page.
   * @param integer $max number of pages. 
   * @param integer $min is the first page number, usually 0 or 1. 
   * @return string with sqlquery
   */
  protected function generateSQL() {

    // SQL components
    $table	  = $this->tableName;
    $where    = null;
    $limit    = null;
    $sort     = " ORDER BY {$this->columnOrderBy} $this->order";
    $params   = array(); 

    // Loop trough all columnNames
    $selectColumns = '';
    $i = 1;
    foreach($this->columnNames as $column) {
      if($i==1) {
        $selectColumns .= $column;
      } else {
        $selectColumns .= ", {$column}";
      }
      $i++;
    }
    
    
    $sqlStart = "SELECT {$selectColumns} FROM {$table} ";
	 
    // Select by title
    if($this->title) {
      $where .= ' AND title LIKE ?';
      $params[] = $this->title;
    }
    
    // Select by year
    if($this->year1) {
      $where .= ' AND year >= ?';
      $params[] = $this->year1;
    } 

    if($this->year2) {
      $where .= ' AND year <= ?';
      $params[] = $this->year2;
    }
    
    // Pagination
    if($this->hits && $this->page) {
      $limit = " LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
    }  


    // Complete the sql statement
    $where = $where ? " WHERE 1 {$where}" : null;
    $sql = $sqlStart . $where . $sort . $limit;
  
    $sqlArray = array('query' => $sql, 'params' => $params);
    
    // Create query for total results
    $firstColumnName = $this->columnNames[0];
    $sqlMaxResults = "SELECT COUNT(*) AS rows FROM $table $where";
    
    $db = $this->db;
    $rowsRes = $db->ExecuteSelectQueryAndFetchAll($sqlMaxResults, $params);
    
    var_dump($params);
    
    if($rowRes == null) {
	    echo 'Is null';
    }
    
    var_dump($sqlMaxResults);
    var_dump($sql);
    
    return $sqlArray;
  } // end function

  

  
  /**
   * getTableContent
   *
   * Generates tablecells based on the results in $this->queryResults 
   */
  public function getTableContent() {
   
   $conentHTML = '';
   
   $i = 0;
   foreach($this->queryResults AS $key => $val) {
	   	   
	   $row = '<tr'; // New row
	   $row .= ($i % 2 == 0) ? ' class="odd">' : '>'; // If odd add class "odd" else close bracket.
	   
	   $row .= "<td width='201'><img src='{$val->image}' alt='{$val->title}' width='200' />";
	   $row .= "<td>{$val->title}</td>";
	   $row .= "<td>{$val->YEAR}</td>";
	   $row .= "</tr>";
	   
	   $i++;
	   $conentHTML .= $row;
   }
   return $conentHTML;
 }
 
 
 
 
  /**
   * Create links for hits per page.
   *
   * @param array $hits a list of hits-options to display.
   * @return string as a link to this page with different hits value.
   */
  public function getHitsPerPageLinks() {
    
   $nav = 'Träffar per sida: ';
  
      // Loop trough array
      foreach($this->hitsOptions AS $val) {
        // If selected values dont add link just value
        if($this->hits == $val) {
          $nav .= "$val ";
        } else {
          $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
        }
      } // end for  
    return $nav;

  }
  
  
  /**
   * Use the current querystring as base, modify it according to $options and return the modified query string.
   *
   * @param array $options to set/change.
   * @param string $prepend this to the resulting query string
   * @return string with an updated query string.
   */
  public function getQueryString($options=array(), $prepend='?') {
    
    // parse query string into array
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);

    // Modify the existing query string with new options
    $query = array_merge($query, $options);

    // Return the modified querystring
    return $prepend . htmlentities(http_build_query($query));
  }

  /**
   * Gets the number of results
   *
   * @return number of results
   */
  public function getNumberOfResults() {
	  
	  if($this->queryResults == null) {
		  return 0;
	  }
	  return count($this->queryResults); 
  }
  
  /**
   * Function to create links for sorting
   *
   * @param string $column the name of the database column to sort by
   * @return string with links to order by column.
   */
  public function orderby($column) {
    $nav  = "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
 	$nav .= "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";
 	return "<span class='orderby'>" . $nav . "</span>";
 }


}