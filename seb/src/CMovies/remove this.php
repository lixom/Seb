
  /**
   * Create links for hits per page.
   *
   * @param array $hits a list of hits-options to display.
   * @return string as a link to this page with different hits value.
   */
  public function getHitsPerPageLinks($hits) {
    
    $html = 'Träffar per sida: ';
  
      // Loop trough array
      foreach($hits AS $val) {
        // If selected values dont add link just value
        if($this->$currentPage == $val) {
          $nav .= "$val ";
        } else {
          $nav .= "<a href='" . getQueryString(array('hits' => $val)) . "'>$val</a> ";
        }
      } // end for  
    return $nav;

  }
  
  
  /* HELPERS */
  /******************************/
  
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
    $sort     = " ORDER BY {$this->columnOrderBy} $order";
    $params   = array(); 

    // Loop trough all columnNames
    $selectColumns = '';
    int x == 1;
    foreach($this->$columnNames as $column) {
      if(x==1) {
        $selectColumns .= $column;
      } else {
        $selectColumns .= " , {$column}"
      }
      x++;
    }
    
    
    $sqlStart = "SELECT {$selectColumns} FROM {$tableName} ";
	 
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
      $limit = " LIMIT $hits OFFSET " . (($this->page - 1) * $this->hits);
    }  


    // Complete the sql statement
    $where = $where ? " WHERE 1 {$where}" : null;
    $sql = $sqlStart . $where . $sort . $limit;
  
    $sqlArray = array('query' => $sql, 'params' => $params);
    
    return $sqlArray;
  } // end function

  
  
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
 * Create navigation among pages.
 *
 * @param integer $hits per page.
 * @param integer $page current page.
 * @param integer $max number of pages. 
 * @param integer $min is the first page number, usually 0 or 1. 
 * @return string as a link to this page.
 */
function getPageNavigation($hits, $page, $max, $min=1) {
  $nav  = ($page != $min) ? "<a href='" . getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
  $nav .= ($page > $min) ? "<a href='" . getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';

  for($i=$min; $i<=$max; $i++) {
    if($page == $i) {
      $nav .= "$i ";
    }
    else {
      $nav .= "<a href='" . getQueryString(array('page' => $i)) . "'>$i</a> ";
    }
  }

  $nav .= ($page < $max) ? "<a href='" . getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
  $nav .= ($page != $max) ? "<a href='" . getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';
  return $nav;
}
  
    
  function getMaxPagesForQuery($wherePart) {
  
  // Get max pages for current query, for navigation
  $sql = "
    SELECT
      COUNT(id) AS rows
      FROM 
  (
    $sqlOrig $where $groupby
  ) AS Movie
";
$res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);
$rows = $res[0]->rows;
$max = ceil($rows / $hits);

  
    $baseSQL = "SELECT COUNT(id) AS rows FROM"
    
    
  }
    
    



// Do it and store it all in variables in the Anax container.
$anax['title'] = "Visa filmer med sökalternativ kombinerade";

$hitsPerPage = getHitsPerPage(array(2, 4, 8), $hits);
$navigatePage = getPageNavigation($hits, $page, $max);
$sqlDebug = $db->Dump();
    
    

  
  
  

   
  /**
   * Function to generate html table
   *
   */
  function getTable() {

   
     // Get parameters 
   $title    = isset($_GET['title']) ? $_GET['title'] : null;
	 $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
	 $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
	 $year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
	 $year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
	 $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
	 $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
	   
	 // Check that incoming parameters are valid
	 is_numeric($hits) or die('Check: Hits must be numeric.');
	 is_numeric($page) or die('Check: Page must be numeric.');
	 is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
	 is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');
	 
	 // Setup sql parts
	 $table	   = $this->tableName;
	 $where    = null;
	 $limit    = null;
	 $sort     = " ORDER BY $orderby $order";
	 $params   = array(); 
	 
	 
  $sqlStart = "SELECT * FROM {$table} ";
	 
	// Select by title
	if($title) {
      $where .= ' AND title LIKE ?';
      $params[] = $title;
    }
    
    // Select by year
    if($year1) {
      $where .= ' AND year >= ?';
      $params[] = $year1;
    } 

    if($year2) {
      $where .= ' AND year <= ?';
      $params[] = $year2;
    }
    
    
    // Pagination
    if($hits && $page) {
      $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
    }  
    
    
    // Complete the sql statement
    $where = $where ? " WHERE 1 {$where}" : null;
    
    // Add sql parts to make the full sql
    $sql = $sqlStart . $where . $sort . $limit;
    dump($sql);
  
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);



    
    // Put results into a HTML-table
    $tableHead = <<<EOD
    <table>
      <tr>
        <th>Bild</th>
        <th>Titel</th>
        <th>År</th>
      </tr>
EOD;

  $tableContent = '';
  
  $x = 0;
  foreach($res AS $key => $val) {
    
    $row = '<tr';
    
    // Odd or even row
    if($x % 2 == 0) {
      $row .= ' class="odd">';
    } else {
      $row .= '>';
    }
    
    dump($val->title);
    
    $row .= "<td width='201'><img src='{$val->image}' alt='{$val->title}' width='200' />";
    $row .= "<td>{$val->title}</td>";
    $row .= "<td>{$val->YEAR}</td>";
    $row .= "</tr>";
    
    $tableContent .= $row;
    
    $x++;
     
    
  }
  
  $html = $tableHead . $tableContent . "</table>";
  
  return $html;

}




 
 /*
// Put results into a HTML-table
$tr = "<tr><th>Rad</th><th>Id " . orderby('id') . "</th><th>Bild</th><th>Titel " . orderby('title') . "</th><th>År " . orderby('year') . "</th><th>Genre</th></tr>";
*/
