<?php
/**
 * Render content to theme.
 *
 */
 
// Extract the data array to variables for easier access in the template files.
// Kan ses som att man Skapar lokala varibler med namnet från keys
extract($seb);
 
// Include the template functions.
include(__DIR__ . '/functions.php');
 
// Include the template file.
include(__DIR__ . '/index.tpl.php');