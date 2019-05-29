<?php
class time_online {


   function time_online() {
      $this -> displayID = 0; // ID used to generate diferent script in case of multiple call of display() function
      $this->uniqueid = 0;	
   }
   function normalizare($secunde) {
	     $minute  = $secunde / 60;
	     $secunde = $secunde % 60;
	     $ore     = $minute  / 60;
	     $minute  = $minute  % 60;
	      $zile    = $ore     / 24;
	     $ore     = $ore     % 24;
	     return $timp = array("days" => (int)$zile, "hours" => $ore, "minutes" => $minute, "seconds" => $secunde);
   }

   function display_time($type,$uniqueid,$label){
      $this -> displayID++;
	$this->uniqueid = time();
	$uniqueid = $this->uniqueid;
      if ($type == "current_page") {
         $time_start_multiply = 0;
      }

      echo "
	           <script type=\"text/javascript\">
			document.writeln(\"<span id=\\\"time_ticker" . $this -> displayID . "\\\" style=\\\"position:absolute;left:\"+3*screen.availWidth/4+\"px;top:10;\\\" ></span>\");
	           zi_inceput" . $this -> displayID . " = new Date();
	           ceas_start" . $this -> displayID . " = zi_inceput" . $this -> displayID . ".getTime();

	           function initStopwatch" . $this -> displayID . "() {
               var timp_pe_pag" . $this -> displayID . " = new Date();
   	           return((timp_pe_pag" . $this -> displayID . ".getTime()+(1000*$time_start_multiply) - ceas_start" . $this -> displayID . ")/1000);
	           }
	           function getSecs" . $this -> displayID . "() {
            	  var tSecs" . $this -> displayID . " = Math.round(initStopwatch" . $this -> displayID . "());
	              var iSecs" . $this -> displayID . " = tSecs" . $this -> displayID . " % 60;
	              var iMins" . $this -> displayID . " = Math.round((tSecs" . $this -> displayID . "-30)/60);
	              var iHour" . $this -> displayID . " = Math.round((iMins" . $this -> displayID . "-30)/60);
	              var iMins" . $this -> displayID . " = iMins" . $this -> displayID . " % 60;
	              var iDays" . $this -> displayID . " = Math.round((iHour" . $this -> displayID . "-11)/24);
			var cookiename = \"time_clock\";
               if (iDays" . $this -> displayID . " == -0) {iDays" . $this -> displayID . " *= (-1)}; // Stupid Opera :)
	              var iHour" . $this -> displayID . " = iHour" . $this -> displayID . " % 24;
	              var sSecs" . $this -> displayID . " = \"\" + ((iSecs" . $this -> displayID . " > 9) ? iSecs" . $this -> displayID . " : \"0\" + iSecs" . $this -> displayID . ");
	              var sMins" . $this -> displayID . " = \"\" + ((iMins" . $this -> displayID . " > 9) ? iMins" . $this -> displayID . " : \"0\" + iMins" . $this -> displayID . ");
	              var sHour" . $this -> displayID . " = \"\" + ((iHour" . $this -> displayID . " > 9) ? iHour" . $this -> displayID . " : \"0\" + iHour" . $this -> displayID . ");

		document.cookie = \"time_clock".$uniqueid."\" + \"=\" + iHour" . $this -> displayID . "+ \":\" + sMins" . $this -> displayID . "+\":\" + sSecs" . $this -> displayID . ";
		var data = sHour". $this -> displayID . "+\":\"+sMins" . $this -> displayID . "+\":\"+sSecs".$this -> displayID.";
		var data1 = \"<strong><font size='2' face='Arial' >".$label." </font></strong>\" + \"<strong><font color='#00FF00' size='5' face='Arial' >\" + data + \"</font></strong>\";
               document.getElementById('time_ticker".$this->displayID."').innerHTML=data1;
               	g_intervalID = window.setTimeout('getSecs" . $this -> displayID . "()',1000);
	           }
               window.setTimeout('getSecs" . $this -> displayID . "()',1000)
	           </script>
      ";


   }
   function currentID(){
	return $this->uniqueid;
   }
}

?>