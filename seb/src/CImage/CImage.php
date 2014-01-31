<?php


class CImage {
	
	public $showDebug;

	// Class settings
	protected $maxWidth;
	protected $maxHeight;
	protected $ignoreCache;
	
	// Paths
	protected $imgPath;
	protected $cachePath;

	// Info about an image or to be image
	protected $saveAs;
	protected $pathToImage;	
	protected $cacheFileName;
	protected $fileExtension;
	protected $cropToFit;
	protected $width;
	protected $height;
	protected $newWidth;
	protected $newHeight;
	protected $cropWidth;
	protected $cropHeight;
	protected $sharpen;
	protected $quality;

	
	/*
	 * Constructor, debugmode, max width and height.
	 *
	 */
	public function __construct($imgPath, $cachePath, $showDebug = FALSE, $ignoreCache = FALSE, $maxWidth = 2000, $maxHeight = 2000) {
  	
 
    $this->imgPath = $imgPath;
    $this->cachePath = $cachePath;
  
  	$this->showDebug = $showDebug;
  	$this->maxWidth = $maxWidth;
  	$this->maxHeight = $maxHeight;
  	$this->ignoreCache = $ignoreCache;
	}
	
	
	/* Output an image together with last modified header.
   *
   * @param string $file as path to the image.
   *
   */
	public function outputImage($fileName) {
		
		if($this->showDebug) {
			$this->printDebug("Opening file: {$fileName}");
		}
		
	  $info = getimagesize($fileName);
	  !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
	  $mime   = $info['mime'];
	 
	  $lastModified = filemtime($fileName);  
	  $gmdate = gmdate("D, d M Y H:i:s", $lastModified);
	 
	  if($this->showDebug) {
	    $this->printDebug("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
	    $this->printDebug("Memory limit: " . ini_get('memory_limit'));
	    $this->printDebug("Time is {$gmdate} GMT.");
	  }
	 
	  if(!$this->showDebug) {
			header('Last-Modified: ' . $gmdate . ' GMT'); 
	  } 
	  
	  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
	    if($this->showDebug) { 
	    	$this->printDebug("Would send header 304 Not Modified, but its debug mode."); 
	    	exit; 
			}
	    header('HTTP/1.0 304 Not Modified');
	  } else {  
	    if($this->showDebug) {
	    	 $this->printDebug("Would send header to deliver image with modified time: {$gmdate} GMT, but its debug mode."); 
	    	 exit; 
	    }

			header('Content-type: ' . $mime);  
	    readfile($fileName);
	  }
	  exit;
	}



	
	
	/*
	 * Gets the image with the right size from cache, 
	 * if the image doesent exists in cache, create one
	 *
	 * @param string $newFilename
	 * @param float  $newWidth the new width
	 * @param float  $newHeignt the new height
	 * 
	 */
	public function getImage($src, $saveAs, $newWidth, $newHeight, $cropToFit, $quality, $sharpen) {

		 $pathToImage = realpath($this->imgPath . $src);
		 $cropToFit = $this->cropToFit;
		
		// Save instance vars inside local ones to prevent calling $this->$maxX all the time
		$maxWidth = $this->maxWidth;
		$maxHeight = $this->maxHeight;	
		
		// Validate input
		is_dir($this->imgPath) or $this->errorMessage('The image dir is not a valid directory'); 
    is_writable($this->cachePath) or $this->errorMessage('The cache dir is not a writable directory.'); 
    isset($src) or $this->errorMessage('Must set src-attribute.');
    preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or $this->errorMessage('Filename contains invalid characters.');
        
    substr_compare($this->imgPath, $pathToImage, 0, strlen($this->imgPath)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
    
    is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension to save image as');
    is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or $this->errorMessage('Quality out of range');
    is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $maxWidth) or $this->errorMessage('Width out of range');
    is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $maxHeight) or $this->errorMessage('Height out of range');
    is_null($cropToFit) or ($cropToFit and $newWidth and $newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');

  	// Save instance vars inside local ones to prevent calling $this->$maxX all the time
		$maxWidth = $this->maxWidth;
		$maxHeight = $this->maxHeight;	
		
		
		//
		// Get information on the image
		//
		$imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);
		!empty($imgInfo) or errorMessage("The file doesn't seem to be an image.");
		$mime = $imgInfo['mime'];
		
		if($this->showDebug) {
		  $filesize = filesize($pathToImage);
		  $this->printDebug("Image file: {$pathToImage}");
		  $this->printDebug("Image information: " . print_r($imgInfo, true));
		  $this->printDebug("Image width x height (type): {$width} x {$height} ({$type}).");
		  $this->printDebug("Image file size: {$filesize} bytes.");
		  $this->printDebug("Image mime type: {$mime}.");
		}

		
		// Get image info
		$imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);
		!empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
		$mime = $imgInfo['mime'];
	
		
		// Calculate new width and height for the image
		$aspectRatio = $width / $height;
		$debugMsg = '';
		
		if($cropToFit && $newWidth && $newHeight) {
		  $targetRatio = $newWidth / $newHeight;
		  $cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
		  $cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
			$debugMsg 	.= "Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}. <br/>"; 
		  
		} else if($newWidth && !$newHeight) {
		  $newHeight = round($newWidth / $aspectRatio);
		  $debugMsg .="New width is known {$newWidth}, height is calculated to {$newHeight}.<br/>"; 
		
		} else if(!$newWidth && $newHeight) {
		  $newWidth = round($newHeight * $aspectRatio);
			$debugMsg .="New height is known {$newHeight}, width is calculated to {$newWidth}.<br/>";   
		
		} else if($newWidth && $newHeight) {
		  $ratioWidth  = $width  / $newWidth;
		  $ratioHeight = $height / $newHeight;
		  $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
		  $newWidth  = round($width  / $ratio);
		  $newHeight = round($height / $ratio);
		  $debugMsg 	.="New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}.<br/>"; 
		
		} else {
		  $newWidth = $width;
		  $newHeight = $height;
		  $debugMsg .="Keeping original width & height."; 
		  
		}
		
		// If show debug print the debug message
		if($this->showDebug) {
			$this->printDebug($debugMsg);
		}
		

		// Creating a filename for the cache
		$parts          = pathinfo($pathToImage);
		$fileExtension  = $parts['extension'];
		$saveAs         = is_null($saveAs) ? $fileExtension : $saveAs;
		$quality_       = is_null($quality) ? null : "_q{$quality}";
		$cropToFit_     = is_null($cropToFit) ? null : "_cf";
		$sharpen_       = is_null($sharpen) ? null : "_s";
		$dirName        = preg_replace('/\//', '-', dirname($src));
		$cacheFileName = CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$newWidth}_{$newHeight}{$quality_}{$cropToFit_}{$sharpen_}.{$saveAs}";
		$cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

		
		// Check if cache already exists, if so, use it.
		$imageModifiedTime = filemtime($pathToImage);
		$cacheModifiedTime = is_file($cacheFileName) ? filemtime($cacheFileName) : null;
		
		// If cached image is valid, output it.
		if(!$this->ignoreCache && is_file($cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
		  
		  if($this->showDebug) { 
		  	$this->printDebug("Cache file is valid, output it."); 
		  }
		  
		  // Output imgage (method return/exists).
		  $this->outputImage($cacheFileName);
		}
				
		// Sava data in instancevars (should be done earlier for some but this helps to keep track.
		$this->saveAs = $saveAs;
		$this->pathToImage  = $pathToImage;
		$this->fileExtension = $fileExtension;
		$this->width = $width;
		$this->height = $height;
		$this->newWidth = $newWidth;
		$this->newHeight = $newHeight;
		$this->cropToFit = $cropToFit;
		$this->sharpen = $sharpen;		
		$this->quality = $quality;
		$this->cacheFileName = $cacheFileName;
		$this->cropWidth = isset($cropWidth) ? $cropWidth : null;
		$this->cropHeight = isset($$cropHeight) ? $cropHeight : null;;
		


		
		// Create new image
		// -----------------------
		
		if($this->showDebug) { 
			$this->printDebug("Cache is not valid, process image and create a cached version of it."); 
		}
		// User want to ignore cache or theres one missing, create new cache.
		$this->saveNewImage(); 
	}
	
	
	
	
	
	/*
	 *
	 *
	 */
	private function saveNewImage() {	
				
		//
		// Open up the original image from file
		//
		if($this->showDebug) {
			$this->printDebug("File extension is: {$this->fileExtension}"); 
			$this->printDebug("Path to original is: {$this->pathToImage}"); 
		}
		
		switch($this->fileExtension) {  
		  case 'jpg':
		  case 'jpeg': 
		    $image = imagecreatefromjpeg($this->pathToImage);
		    if($this->showDebug) { 
		    	$this->printDebug("Opened the image as a JPEG image."); 
		    }
		    break;  
		  
		  case 'png':  
		    $image = imagecreatefrompng($this->pathToImage); 
		     if($this->showDebug) { 
		    	$this->printDebug("Opened the image as a PNG image.");
		    }
		    break;  
		  default: errorPage('No support for this file extension.');
		}
		
		// Make instance vars local for shorter rows and clarity
		$width = $this->width;
		$height = $this->height;
		$newWidth = $this->newWidth;
		$newHeight = $this->newHeight;
		$cropWidth = $this->cropWidth;
		$cropHeight = $this->cropHeight;
		
		//
		// Resize the image if needed
		//
		if($this->cropToFit) {
		  if($this->showDebug) { 
		  	$this->printDebug("Resizing, crop to fit."); 
		  }
		  $cropX = round(($width - $cropWidth) / 2);  
		  $cropY = round(($height - $cropHeight) / 2);    
		  $imageResized =   $imageResized = imagecreatetruecolor($newWidth, $newHeight); //$this->createImageKeepTransparency($newWidth, $newHeight);
		  imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
		  $image = $imageResized;
		  $width = $newWidth;
		  $height = $newHeight;
		
		}	else if(!($newWidth == $width && $newHeight == $height)) {
		  if($this->showDebug) { 
		  	$this->printDebug("Resizing, new height and/or width."); 
			}
		  $imageResized = imagecreatetruecolor($newWidth, $newHeight); //$this->createImageKeepTransparency($newWidth, $newHeight);
		  imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		  $image  = $imageResized;
		  $width  = $newWidth;
		  $height = $newHeight;
		}
		
		
		
		//
		// Apply filters and postprocessing of image
		//
		if($this->sharpen) {
		  $image = $this->sharpenImage($image);
		}
		
		
		
		//
		// Save the image
		//
		switch($this->saveAs) {
		  case 'jpeg':
		  case 'jpg':
		    if($this->showDebug) { 
		    	$this->printDebug("Saving image as JPEG to cache using quality = {$this->quality}."); 
		    }
		    imagejpeg($image, $this->cacheFileName, $this->quality);
		  break;  
		
		  case 'png':  
		    if($this->showDebug) { 
		    	$this->printDebug("Saving image as PNG to cache."); 
		    }
		    // Turn off alpha blending and set alpha flag
				//imagealphablending($image, false);
    		//imagesavealpha($image, true);
		    imagepng($image, $this->cacheFileName);  
		  break;  
		
		  default:
		    $this->errorMessage('No support to save as this file extension.');
		  break;
		}
		
		if($this->showDebug) { 
			$filesize = filesize($this->pathToImage);
		  $cacheFilesize = filesize($this->cacheFileName);
		  $this->printDebug("File size of cached file: {$cacheFilesize} bytes."); 
		  $this->printDebug("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
		}
		
		
		
		//
		// Output the resulting image
		//
		$this->outputImage($this->cacheFileName);
	}




	/*
	 * Display error message and return 404 status header.
	 *
	 * @param string $message the error message to display.
	*/
 	public function errorMessage($message) {
  	header("Status: 404 Not Found");
		die('img.php says 404 - ' . htmlentities($message));
	}

  /*
	 * Display log message.
   *
   * @param string $message the log message to display.
   */
  public  function printDebug($message) {
	  echo "<p>$message</p>";
	}
	
	/**
	 * Sharpen image as http://php.net/manual/en/ref.image.php#56144
	 * http://loriweb.pair.com/8udf-sharpen.html
	 *
	 * @param resource $image the image to apply this filter on.
	 * @return resource $image as the processed image.
	 */
	public function sharpenImage($image) {
	  
	  if($this->showDebug) {
		  $this->printDebug("Sharpening image");
	  }
	  
	  $matrix = array(
	    array(-1,-1,-1,),
	    array(-1,16,-1,),
	    array(-1,-1,-1,)
	  );
	  $divisor = 8;
	  $offset = 0;
	  imageconvolution($image, $matrix, $divisor, $offset);
	  return $image;
	}
	
	/**
	 * Create new image and keep transparency
	 *
	 * @param resource $image the image to apply this filter on.
	 * @return resource $image as the processed image.
	 */
	function createImageKeepTransparency($width, $height) {
	    $img = imagecreatetruecolor($width, $height);
	    imagealphablending($img, false);
	    imagesavealpha($img, true);  
	    return $img;
	}

	
}
?>