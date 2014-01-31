<?php
class CNavigation {
  public static function GenerateMenu($items) {
    if(isset($menu['callback'])) {
      $items = call_user_func($menu['callback'], $menu['items']);
    }
    $html = "<nav class='$class'>\n";
    foreach($items as $item) {
      $html .= "<a href='{$item['url']}' class='{$item['class']}'>{$item['text']}</a>\n";
    }
    $html .= "</nav>\n";
    return $html;
  }
};


$menu = array(
  'callback' => 'modifyNavbar',
  'items' => array(
    'home'  => array('text'=>'Hem',  'url'=>'?p=home', 'class'=>null),
    'away'  => array('text'=>'Away',  'url'=>'?p=away', 'class'=>null),
    'about' => array('text'=>'About', 'url'=>'?p=about', 'class'=>null),
  ),
);


<?php echo CNavigation::GenerateMenu($menu);
