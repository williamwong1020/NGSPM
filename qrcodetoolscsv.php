<?php

//*************************************************************************************
// mime type is not set, get from server settings

define( "CSV", "qrcodetools.csv" );
$mtype = "";
if( function_exists('mime_content_type') ) {
	$mtype = mime_content_type( CSV );
} elseif( function_exists('finfo_file') ) {
	$finfo = finfo_open( FILEINFO_MIME );	// return mime type
	$mtype = finfo_file( $finfo, CSV );
	finfo_close( $finfo );
}
if( $mtype == "" ) {
	$mtype = "application/force-download";
	// $mtype = "text/plain";
}

// set headers
header( "Pragma: public" );
header( "Expires: 0" );
header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
header( "Cache-Control: public" );
header( "Content-Description: File Transfer" );
header( "Content-Type: ".$mtype );
header( "Content-Disposition: attachment; filename=".basename( CSV ) );
header( "Content-Transfer-Encoding: binary" );
header( "Content-Length: ".filesize( CSV ) );

// download & remove
@readfile( CSV );

?>
