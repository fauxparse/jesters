<Script>
function preview(page){
	w=800;  h=600;  x=(screen.width / 2) - (w / 2); y=(screen.height / 2) - (h / 2);
	sFlags='toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=' + w + ',height=' + h;
	if (navigator.appName.indexOf("Microsoft")>=0) {
                sFlags+=',left=' + x + ',top=' + y;
	} else {
                sFlags+=',screenX=' + x + ',screenY=' + y;
	}
	NewWindow=open( page, "popup", sFlags );
}
</Script>
<table width=100% border=0 cellpadding=5>
<tr>
<?
//$dir = "cmscontent/images";
$dir = "/var/httpd/htdocs/" . $HTTP_HOST . "/cmscontent/images/";
$count=0;
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
        	if( $file != "." and $file != ".." ){
        		$size=filesize( $dir . "/" . $file );
				if( substr( $file, 0, 14 ) == "Gallery" . $HTTP_GET_VARS["gallery"] . "thumb" ){
					$count++;
					if( $count > 2 ){
						$count=0;
						echo "</TR></TR>";
					}
					echo "<TD ALIGN=CENTRE><A HREF=\"#\" ONCLICK=\"preview('/cmscontent/images/" . 
						"Gallery" . $HTTP_GET_VARS["gallery"] . substr( $file, 14, 200 ) . 
						"')\"><IMG BORDER=0 SRC=\"/cmscontent/images/" . $file . "\"></A></TD>";
				}
			}
        }
        closedir($dh);
    }
}
?>
</tr>
</table>
