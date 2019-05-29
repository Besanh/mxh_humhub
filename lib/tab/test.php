<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?
include("menu.php");
$myMenu=array("One"=>"one.html","Two"=>"two.html","Three"=>"three.html","Four"=>"four.html","Five"=>"five.html","Six"=>"six.html");
$label=date("F j, Y, g:i a");
$a=new tabMenu($myMenu,$label,0,"left");
?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin sodales, eros sed mattis facilisis, lorem ipsum ultricies leo, quis tincidunt magna felis id erat. Nunc ullamcorper, justo id congue fringilla, odio risus porta arcu, iaculis venenatis ante odio eu libero. In vehicula. Duis lacus mi, pretium eget, eleifend at, elementum placerat, sapien. Integer volutpat mauris non arcu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque ut enim. Nulla facilisi. Curabitur pede. Integer mollis. </p>
<p>Nam accumsan. Suspendisse neque velit, interdum sed, pulvinar quis, vestibulum non, lorem. Morbi eros libero, suscipit eget, rhoncus vel, porttitor vitae, urna. Sed faucibus facilisis orci. Donec hendrerit. Proin mi. Vestibulum aliquam, mauris at mollis sodales, ligula libero blandit libero, quis rutrum neque tortor vitae felis. Vivamus eget lectus. Curabitur eu quam eget sem facilisis elementum. Nullam ante lacus, facilisis vitae, venenatis quis, varius ut, tellus. </p>
<? $b=new tabMenu($myMenu,$label,1,"right"); ?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin sodales, eros sed mattis facilisis, lorem ipsum ultricies leo, quis tincidunt magna felis id erat. Nunc ullamcorper, justo id congue fringilla, odio risus porta arcu, iaculis venenatis ante odio eu libero. In vehicula. Duis lacus mi, pretium eget, eleifend at, elementum placerat, sapien. Integer volutpat mauris non arcu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque ut enim. Nulla facilisi. Curabitur pede. Integer mollis. </p>
<p>Nam accumsan. Suspendisse neque velit, interdum sed, pulvinar quis, vestibulum non, lorem. Morbi eros libero, suscipit eget, rhoncus vel, porttitor vitae, urna. Sed faucibus facilisis orci. Donec hendrerit. Proin mi. Vestibulum aliquam, mauris at mollis sodales, ligula libero blandit libero, quis rutrum neque tortor vitae felis. Vivamus eget lectus. Curabitur eu quam eget sem facilisis elementum. Nullam ante lacus, facilisis vitae, venenatis quis, varius ut, tellus. </p>
<? $c=new tabMenu($myMenu,$label,2);?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin sodales, eros sed mattis facilisis, lorem ipsum ultricies leo, quis tincidunt magna felis id erat. Nunc ullamcorper, justo id congue fringilla, odio risus porta arcu, iaculis venenatis ante odio eu libero. In vehicula. Duis lacus mi, pretium eget, eleifend at, elementum placerat, sapien. Integer volutpat mauris non arcu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque ut enim. Nulla facilisi. Curabitur pede. Integer mollis. </p>
<p>Nam accumsan. Suspendisse neque velit, interdum sed, pulvinar quis, vestibulum non, lorem. Morbi eros libero, suscipit eget, rhoncus vel, porttitor vitae, urna. Sed faucibus facilisis orci. Donec hendrerit. Proin mi. Vestibulum aliquam, mauris at mollis sodales, ligula libero blandit libero, quis rutrum neque tortor vitae felis. Vivamus eget lectus. Curabitur eu quam eget sem facilisis elementum. Nullam ante lacus, facilisis vitae, venenatis quis, varius ut, tellus. </p>
</body>
</html>
