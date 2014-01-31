<?php
/**
 * Theme related functions. 
 *
 */
 
/**
 * Get title for the webpage by concatenating page specific title with site-wide title.
 *
 * @param string $title for this page.
 * @return string/null wether the title_append is defined or not.
 */
function get_title($title) {
  global $seb;
  return $title . (isset($seb['title_append']) ? $seb['title_append'] : null);
}