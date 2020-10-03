<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>QR Code Generator Tools</title>
<script src="./qrcodetools.js?<?php echo rand(); ?>"></script>
<link rel="stylesheet" type="text/css" href="./qrcodetools.css?<?php echo rand(); ?>">
</style>
</head>

<body>

<?php
define( "VERSION", "QR Code Generator Tools v1.7d" );
define( "PLACEHOLDER", "Paste QR Code string here..." );
define( "SAMPLE_QR", "0002010102121531479803444798034459803449399006326450012hk.com.hkicl0209166069526051229123123595952049399530334454042.005802HK5902NA6002HK6293011900-12348A10798425380218001-20200726084100032263ee92126d3fb9ff7bed98041802150821101001445D80560013com.swiftpass0212181570002009041900-12348A10798425386304F335" );
define( "CSV", "qrcodetools.csv" );

$outmon = "";
$tagname = array(
    "00" => "Payload Format Indicator",
    "01" => "Point of Initiation Method",
    "15" => "UnionPay Merchant Account Information",
	"26" => "Merchant Account Information",
	"2600" => "Global Unique Identifier",
	"2602" => "FPS Identifier",
	"2605" => "Merchant Time-out Time",
	"52" => "Merchant Category Code",
	"53" => "Transaction Currency Code",
	"54" => "Transaction Amount",
	"58" => "Merchant Country",
	"59" => "Merchant Name",
	"60" => "Merchant City",
	"62" => "Additional Data Template",
	"6201" => "Bill Number",
	"6202" => "QR Code Version",
	"6203" => "GuID",
	"6204" => "Tariff Information + CRC16",
	"80" => "Swiftpass Additional Template",
	"8000" => "Swiftpass Flag",
	"8002" => "Swiftpass ID",
	"8004" => "Bill Number (Copy)",
	"63" => "Global CRC"
);

if( file_exists( CSV ) ) unlink( CSV );
if( ! isset( $_POST["qrcode"] ) && ! isset( $_GET["qrcode"] ) )
{
?>

<center>
<B><?php echo VERSION; ?></B>
<BR>
<FORM action="./qrcodetools.php" method="POST" target="_self">
<textarea class="mon" NAME="qrcode" placeholder="<?php echo PLACEHOLDER; ?>" rows="5" cols="100"></textarea>
<BR><BR>
<INPUT type="image" src="go_btn.gif" alt="Submit" width="60">
</FORM>

<?php
}
else
{
	if( isset( $_POST["qrcode"] ) ) {
		// value from textarea
		$qrcode = (trim($_POST["qrcode"]) == "") ? SAMPLE_QR : trim( $_POST["qrcode"] );
	} else {
		// value from url parameter
		$qrcode = (trim($_GET["qrcode"]) == "") ? SAMPLE_QR : trim( $_GET["qrcode"] );
	}
	echo '<center><B>'.VERSION.'</B><BR>';
	echo '<table class="mon" width="1020"><tr><td>'.mouseovertext($qrcode,-1).'</td></tr></table><BR>';
	echo '<table><tr><th>tag name</th><th>tagID</th><th>sub-<BR>tagID</th><th>length</th><th>data</th>';
	echo '<th rowspan="0"><a href="./qrcodetoolscsv.php"><img src="csv_btn.gif" height="35" width="60"/></a><BR><BR>&nbsp;<img src="https://api.qrserver.com/v1/create-qr-code/?data='.$qrcode.'" />&nbsp;<BR><BR><a href="./qrcodetools.php"><img src="back_btn.png" height="60" width="60"/></a></th></tr>';
	subtag( $qrcode, -1 );
	echo '</table>';
	$outcsvf = $qrcode."\r\n\r\ntag name,tagID,sub-tagID,length,data\r\n";
	outcsv( $qrcode, -1 );
}

function subtag( $qrcode, $tagid ) {
	global $tagname;
	static $bcnt = 0;
	do{
		$bcnt++;
		echo '<tr height="25" onMouseOver="mon'.$bcnt.'()" onMouseOut="moff'.$bcnt.'()">';
		$tag = splittag( $qrcode );
		switch( $tag["id"] ){
			case "26":
			case "62":
			case "80":
				echo '<td class="tagn">&nbsp;'.$tagname[$tag["id"]].'&nbsp;</B></td><td>'.$tag["id"].'</td><td>&nbsp;</td><td>'.$tag["len"].'</td><td>&nbsp;</td>';
				subtag( $tag["val"], $tag["id"] );
				break;
			default:
				$tagval = splittagdata( $tagid, $tag );
				if( $tagid == "26" || $tagid == "62" || $tagid == "80" )
					echo '<td class="tagn">&nbsp;'.$tagname[$tagid.$tag["id"]].'&nbsp;</B></td><td>'.'&nbsp;</td><td>'.$tag["id"].'</td><td>'.$tag["len"].'</td><td>'.$tagval.'&nbsp;</td>';
				else
					if( $tag["id"] && array_key_exists( $tag["id"], $tagname ) ) {
						echo '<td class="tagn">&nbsp;'.$tagname[$tag["id"]].'&nbsp;</B></td><td>'.$tag["id"].'</td><td>&nbsp;</td><td>'.$tag["len"].'</td><td>'.$tagval.'&nbsp;</td>';
					}
				break;
		}
		$qrcode = substr( $qrcode, 4+$tag["len"] );
		echo '</tr>';
	}while( $qrcode );
}

function splittag( $qrcode ) {
	$tag["id"] = substr( $qrcode, 0, 2 );
	$tag["len"] = substr( $qrcode, 2, 2 );
	$tag["val"] = substr( $qrcode, 4, (int)$tag["len"] );
	return $tag;
}

function splittagdata( $parent_tagid, $tagarray ) {
	$tagval = $tagarray["val"];
	if( $tagarray["id"] == "15" ) {
		$tagval = "<c1>".substr( $tagarray["val"], 0, 8 )."</c1><c2>".substr( $tagarray["val"], 8, 8 )."</c2><c3>".substr( $tagarray["val"], 16 )."</c3>";
	} elseif( $parent_tagid == "62" ) {
		switch( $tagarray["id"] ){
			case "01": $tagval = "<c1>".substr( $tagarray["val"], 0, 3 )."</c1><c2>".substr( $tagarray["val"], 3, 6 )."</c2><c3>".substr( $tagarray["val"], 9 )."</c3>"; break;
			case "02": $tagval = "<c1>".substr( $tagarray["val"], 0, 4 )."</c1><c2>".substr( $tagarray["val"], 4 )."</c2>"; break;
			case "04": 
				$offset = strlen( $tagarray["val"] ) - 4 - 8 - 2 - 2 - 2;	// from end to beginning: CRC16, Tariff, Tariff Len, LPP, -2
				$tagval = "<c1>".substr( $tagarray["val"], 0, 2 )."</c1><c2>".substr( $tagarray["val"], 2, 2+$offset )."</c2><c3>".substr( $tagarray["val"], 4+$offset, 2 )."</c3><c4>".substr( $tagarray["val"], 6+$offset, 8 )."</c4><c5>".substr( $tagarray["val"], 14+$offset )."</c5>"; break;
		}
	} elseif( $parent_tagid == "80" ) {
		switch( $tagarray["id"] ){
			case "04": $tagval = "<c1>".substr( $tagarray["val"], 0, 3 )."</c1><c2>".substr( $tagarray["val"], 3, 6 )."</c2><c3>".substr( $tagarray["val"], 9 )."</c3>"; break;
		}
	}
	return $tagval;
}

function mouseovertext( $qrcode, $tagid ) {
	global $outmon;
	static $bcnt = 0;
	do{
		$bcnt++;
		$tag = splittag( $qrcode );
		switch( $tag["id"] ){
			case "26":
			case "62":
			case "80":
				$outmon .= '<ani><b'.$bcnt.'>'.$tag["id"].$tag["len"].'</b'.$bcnt.'></ani>';
				mouseovertext( $tag["val"], $tag["id"] );
				break;
			default:
				$outmon .= '<ani><b'.$bcnt.'>'.$tag["id"].$tag["len"].$tag["val"].'</b'.$bcnt.'></ani>';
				break;
		}
		$qrcode = substr( $qrcode, 4+$tag["len"] );
	}while( $qrcode );
	return $outmon;
}

function outcsv( $qrcode, $tagid ){
	global $tagname, $outcsvf;
	do{
		$tag = splittag( $qrcode );
		switch( $tag["id"] ) {
			case "26":
			case "62":
			case "80":
				$outcsvf .= $tagname[$tag["id"]].",".$tag["id"].",,".$tag["len"].",\r\n";
				outcsv( $tag["val"], $tag["id"] );
				break;
			default:
				if( $tagid == "26" || $tagid == "62" || $tagid == "80" )
					$outcsvf .= $tagname[$tagid.$tag["id"]].",,".$tag["id"].",".$tag["len"].",".$tag["val"]."\r\n";
				else
					$outcsvf .= $tagname[$tag["id"]].",".$tag["id"].",,".$tag["len"].",".$tag["val"]."\r\n";
				break;
		}
		$qrcode = substr( $qrcode, 4+$tag["len"] );
	}while( $qrcode );
	
	$fh = fopen( CSV, "w+" );
	fwrite( $fh, $outcsvf );
	fclose( $fh );
}

?>

<BR><BR>
</body>
</html>