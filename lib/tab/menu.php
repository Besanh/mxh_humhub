<?
// make tab menus using html tables
// vedanta_dot_barooah_at_gmail_dot_com

class tabMenu{
function tabMenu($linkArray,$label,$activeTab=0,$menuAlign="center"){
	$tabCount=0;
	print "<table width=\"100%\" cellpadding=0 cellspacing=1>\n";
	print "<tr>\n";
	if($menuAlign=="right"){
		print "<td width=\"100%\" align=\"left\">&nbsp;$label</td>\n";
	}
	foreach ($linkArray as $k => $v) {
	if($tabCount==$activeTab){$menuStyle="navOn";}else{$menuStyle="navOff";}
	print "<td valign=\"top\">\n";
	print "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" height=\"100%\">\n";
	print "<tr>\n";

	print "<td valign=\"top\" class=\"$menuStyle\"><img src=\"../images/left_arc.gif\"></td>\n";
	print "<td width=\"100%\" height=\"20\"  align=\"center\" valign=\"middle\" class=\"$menuStyle\">\n";
	if($tabCount!=$activeTab){
	print "<a href=\"$v\">\n";
	}
	print $k;
	if($tabCount!=$activeTab){
	print "</a>\n";
	}
	print "</td>\n";
	print "<td valign=\"top\" class=\"$menuStyle\"><img src=\"images/right_arc.gif\"></td>\n";
	print "</tr>\n";
	print "</table>\n";
	print "</td>\n";
	$tabCount++;
	}
	if($menuAlign=="left"){
		print "<td width=\"100%\" align=\"right\">&nbsp;$label</td>";
	}
	print "</tr>\n";
	print "</table>\n";
}
}
?>
