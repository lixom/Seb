<?php
/**
 * A CCalendar class that represents a calendar.
 *
 */
class CCalendar {

  /**
   * Properties
   *
   */
  private $today;
  private $year;
  private $month;
  private $day;
  private $dayTitles;
  
  
  
  /**
   * Constructor
   *
   * If no year and month speicfied, set to null,
   * this way the init method will set it to current date
   */
  public function __construct($year = NULL, $month = NULL) {
  	 $this->month = $month;
     $this->year = $year;
     $this->init();
  }
  
   
  /**
   * Init
   *
   */
  public function init() {
  	$this->today = date("d");
  
  	// If not already set set to today (year and month)
  	if($this->year == null || $this->month == null) {
  		$this->year = date('Y');
	  	$this->month = date('n');
	  }
	  
	  
	  // Add locale weekdays
	  $this->dayTitles['sv_SE'] = array("Måndag", "Tisdag", "Onsdag", "Torsdag", "Fredag", "Lördag", "Söndag");
		
		// Init images array
  }
  
  
  /**
   * Show the calendar (calls all other generate methods)
	 *
	 */
  public function displayCalendar() {
	  
	  // Update from url parameters
	  $this->updateParametersFromUrl();
	  
	  // Temp vars
	  $title = $this->getTitle();
	  $links = $this->generatePreviousMonthLink(); 
	  $links .= ' ';
	  $links .= $this->generateNextMonthLink();
	  $dayHeader = $this->generateDayTitles();
	  $days = $this->generateDays();
	  
	
	  $htmlCal = <<<EOD
			<table class="calendar">
				<tbody>
					<tr class="imageView">
						<td colspan="7"><img src="img/kalender/img1.png" /><td>
					</tr>
						
					<tr class="monthLabel">
						<td colspan="7">
							<h3>{$title}</h3>
							{$links}
						</td>
					</tr>
						
					{$dayHeader}
					{$days}		
							
					</tbody>
			</table>
EOD;
	  
	  return $htmlCal;
  }
  
      
  /**
   * Returns the month and year as a string.
   *
   */
  public function getTitle() {
		// Create unix timestamp and format it with date();
  	return date('F Y', mktime(0,0,0, $this->month,1,$this->year));
  }
  
  
  /**
   * Generates output with weekday names based on locale
   * 
   */
  public function generateDayTitles($locale = null) {
  
  	// Use swedish locale, kind of a fix,
  	// really would want the locale to be determined automatically if no specified
  	if($locale == null) {
	  	$locale = 'sv_SE';
  	}
  	
  	// Generate html from locale
  	$html = '<tr class="dayLabel">';
		
		foreach($this->dayTitles[$locale] as $dayName) {
			$html .= "<td>{$dayName}</td>";
		}
		
		return $html;
  }
  
  
  /**
   * Returns a link to the previous month
   *
   */
  public function generatePreviousMonthLink() {
  
  	// Store in temp vars
  	$year = $this->year;
  	$month = $this->month;
		$html = null;

		// If last month of year, remove one year and set month to 12
  	if($month == 1) {
  		$year = $year -1;
  		$month = 12;
  	} else {
  		$month = $month -1;
  	}
  	
  	$html = "<a href=\"kalender.php?year={$year}&month={$month}\">&laquo; Föregående</a>";
  	return $html;
  }
  
  /**
   * Returns a link to the next month
   *
   */
  public function generateNextMonthLink() {
	 
	 	// Store in temp vars
  	$year = $this->year;
  	$month = $this->month;
		$html = null;

		// If last month of year, remove one year and set month to 12
  	if($month == 12) {
  		$year = $year +1;
  		$month = 1;
  	} else {
  		$month = $month +1;
  	}
  	
  	$html = "<a href=\"kalender.php?year={$year}&month={$month}\">Nästa &raquo;</a>";
  	return $html;
  }
  
  
   /**
   * Creates the calender output (only actuall days)
   *
   */
  public function generateDays() {
	  
		// Get days in current and last month
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
	  $daysInLastMonth = date("t", mktime(0 ,0 ,0, $this->month-1, 1, $this->year)); // Cannot use the same function as above without if-statments for month/year
	  
	  
	  $start = date("N", mktime(0,0,0,$this->month,1,$this->year)); // Starting day of current month as weekday (M = 1, T = 2 etc.)
		$finish = date("N", mktime(0,0,0,$this->month,$daysInMonth,$this->year)); // Finishing day of  current month as weekday 
		$laststart = $start - 1; // Days left form previous month
	  
	  $counter = 1; // Day counter
	  $nextMonthCounter = 1;

		$html = '';

		// If month starts on later than friday we need to show 6 rows, else 5 rows,
		if($start > 5) {	
			$rows = 6; 
		} else {
			$rows = 5; 
		}
		
		// Loop all rows
		for($i = 1; $i <= $rows; $i++){
			
			$html .= '<tr>';	
			
			// Loop each week (7 times)
			for($x = 1; $x <= 7; $x++){				
		
				// If day is less than the start weekday in the month add days from previous month
				if(($counter - $start) < 0) {
					$date = (($daysInLastMonth - $laststart) + $counter); 
					// Take days of last month - remaing month + counter for each iterationw
					
					$class = 'class="otherMonth"';
				// If days belong to next month
				} else if(($counter - $start) >= $daysInMonth) {
					$date = ($nextMonthCounter); // Date is same as nextMonthCounter for each iteration
					$nextMonthCounter++;
			
					$class = 'class="otherMonth"';
				
				// Day is in this month
				} else {
					$date = ($counter - $start + 1); // Get the day by removing the leftover days from counter and add 1
				
					$class = 'class="';
				
					// If counter is 7nth day of the week its sunday
					if($counter % 7 == 0) {
						$class .= 'sunday ';
					}
					
					// Check if today
					$yearToday	= date('Y');
					$monthToday = date('n');
					
				
					// Check if day is today by removing offset
					// alse check if month and year is the same.
					if(($this->today == $counter - $start + 1) && ($this->year == $yearToday) && ($this->month == $monthToday)){
						$class .= 'today';
					}
					
					$class .='"';
				} // end if
				
				
				$html .= "<td {$class}> {$date} </td>";
		
				$counter++;
				$class = '';
			} // end inner for
	
			$html .= '</tr>';
		} // end outer for
		
		return $html;
  }
  
  /*
   * Gets parameters from $_GET, checks month and year sepratly, this means user can set one only one and the other will use todays value.
   * ie. only specify year and the month will be current month and vice versa
   *
   */
  public function updateParametersFromUrl() {
  
  	// If year is set and numeric
  	if(isset($_GET['year']) && is_numeric($_GET['year'])) {
  		$this->year = $_GET['year'];
  	}
  	
  	// If month is set and numeric
  	if(isset($_GET['month']) && is_numeric($_GET['month'])) {
  		$this->month = $_GET['month'];
  	}
	  
  }
  
 
}