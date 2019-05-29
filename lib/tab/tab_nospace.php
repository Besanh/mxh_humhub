<link href="style_alt.css" rel="stylesheet" type="text/css">

<?
include("tabMenu_nospace.php");
$myMenu=array("One"=>"?tab=One","Two"=>"?tab=Two","Three"=>"?tab=Three","Four"=>"?tab=Four","Five"=>"?tab=Five","Six"=>"?tab=Six");

if($tab!=""){
	switch($tab){
		case "One": 
		$childArray=array("Ennie"=>"e.html","Minnie"=>"m.html","Miny"=>"m.html","Moe"=>"moe.html");
		$activeTab=0;
		break;
		case "Two": 
		$childArray=array("Joe"=>"joe.html","Bill"=>"bill.html");
		$activeTab=1;
		break;
		case "Three": 
		$childArray=array("PHP"=>"php.html","MySQL"=>"sql.html");
		$activeTab=2;
		break;
		case "Four": 
		$childArray=array("Nokia"=>"nokia.html","Simens"=>"simens.html","BenQ"=>"benq.html");
		$activeTab=3;
		break;
		case "Five": 
		$childArray=array("Pizza"=>"pizza.html","Ham"=>"ham.html","Burger"=>"burger.html");
		$activeTab=4;
		break;
		case "Six": 
		$childArray=array("This"=>"this.html","Is"=>"is.html","a"=>"a.html","menu"=>"menu.html");
		$activeTab=5;
		break;
	}
}else{
	$childArray=array("Ennie"=>"e.html","Minnie"=>"m.html","Miny"=>"m.html","Moe"=>"moe.html");
	$activeTab=0;
}

$label=date("F j, Y, g:i a");
$a=new tabMenu($myMenu,$childArray,$label,$activeTab,right,right);
?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam porttitor vehicula ante. Vivamus fermentum risus tincidunt nunc. Sed purus pede, volutpat ac, suscipit sed, ullamcorper a, ligula. Phasellus auctor pretium leo. Vestibulum feugiat ante ut mauris. Quisque aliquam massa sit amet magna. Donec varius nulla tempor nulla. Fusce lorem. Fusce eu diam sit amet enim pulvinar eleifend. Nunc enim erat, aliquet id, elementum ac, vulputate vel, arcu. Phasellus non lectus. In a nunc. </p>
<?
$b=new tabMenu($myMenu,$childArray,$label,$activeTab);
?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam porttitor vehicula ante. Vivamus fermentum risus tincidunt nunc. Sed purus pede, volutpat ac, suscipit sed, ullamcorper a, ligula. Phasellus auctor pretium leo. Vestibulum feugiat ante ut mauris. Quisque aliquam massa sit amet magna. Donec varius nulla tempor nulla. Fusce lorem. Fusce eu diam sit amet enim pulvinar eleifend. Nunc enim erat, aliquet id, elementum ac, vulputate vel, arcu. Phasellus non lectus. In a nunc. </p>
<?
$b=new tabMenu($myMenu,$childArray,$label,$activeTab,left,left);
?>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam porttitor vehicula ante. Vivamus fermentum risus tincidunt nunc. Sed purus pede, volutpat ac, suscipit sed, ullamcorper a, ligula. Phasellus auctor pretium leo. Vestibulum feugiat ante ut mauris. Quisque aliquam massa sit amet magna. Donec varius nulla tempor nulla. Fusce lorem. Fusce eu diam sit amet enim pulvinar eleifend. Nunc enim erat, aliquet id, elementum ac, vulputate vel, arcu. Phasellus non lectus. In a nunc. </p>