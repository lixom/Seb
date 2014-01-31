<?php
class CNavigation {
	
  public static function GenerateMenu($menu) {
  
  	// Check if callback is specified
	  if(isset($menu['callback_selected'])) {
	 		
	 		// Set class
	 		if(isset($menu['class'])) {
		 		$html = "<nav class='{$menu['class']}'>\n";
	 		} else {
		 		$html = "<nav class='$class'>\n";
	 		}
	 	 	
	 	 	
			// Get each menu item
	    foreach($menu['items'] as $item) {
				
				// Check if current url by calling callback function with url,
				// if same the function will return true
				$isCurrentUrl = call_user_func($menu['callback_selected'], $item['url']);
				
				// Set class as selected
	      if($isCurrentUrl == true){
	      	$html .= "<a href='{$item['url']}' class='selected'>{$item['text']}</a>\n ";
	      } else {
					$html .= "<a href='{$item['url']}'>{$item['text']}</a>\n ";
	      }                
	    }
	    $html .= "</nav>";
	    
	    return $html;
		}
	}

}
?>