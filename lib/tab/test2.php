<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?
include("menu2.php");
$myMenu=array("One"=>"one.html","Two"=>"two.html","Three"=>"three.html","Four"=>"four.html","Five"=>"five.html","Six"=>"six.html");
$childArray=array("Ennie"=>"e.html","Minnie"=>"m.html","Miny"=>"m.html","Moe"=>"moe.html");
$label=date("F j, Y, g:i a");
// tabMenu($topMenu,$childMenu,$label,$activeTab,$topMenuAlign,$childMenuAlign);
$a=new tabMenu($myMenu,$childArray,$label,0,right,right);
?>
