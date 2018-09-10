<?php
// Including all required classes
require_once('class/BCGFontFile.php');
require_once('class/BCGColor.php');
require_once('class/BCGDrawing.php');

// Including the barcode technology
require_once('class/BCGi25.barcode.php');

// Loading Font
$font = new BCGFontFile('./font/Arial.ttf', 10);

// Don't forget to sanitize user inputs
$text = isset($_GET['text']) ? $_GET['text'] : '123456789012';

// The arguments are R, G, B for color.
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);

$drawException = null;
try {
    $code = new BCGi25();
    $code->setScale(1); // Resolution
    $code->setThickness(30); // Thickness
    $code->setForegroundColor($color_black); // Color of bars
    $code->setBackgroundColor($color_white); // Color of spaces
    $code->setFont($font); // Font (or 0)
    $code->parse($text); // Text
} catch(Exception $exception) {
    $drawException = $exception;
}

/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$mkdir = $_SERVER['DOCUMENT_ROOT'] .'/barcodefile/'. date('Ymd');
$vwdir = '/barcodefile/'. date('Ymd');
if( !is_dir($mkdir) ){
	mkdir($mkdir);
	chmod($mkdir, 0777);
}

$file = $text .'_'. date('Ymd') .'.jpg';
		
$drawing = new BCGDrawing($mkdir .'/'. $file, $color_white);
if($drawException) {
    $drawing->drawException($drawException);
} else {
    $drawing->setBarcode($code);
    $drawing->draw();
}

// Header that says it is an image (remove it if you save the barcode to a file)
//header('Content-Type: image/png');
//header('Content-Disposition: inline; filename="barcode.png"');

// Draw (or save) the image into PNG format.
$drawing->finish(BCGDrawing::IMG_FORMAT_JPEG);

?>
<img src="<?=$vwdir?>/<?=$file?>">