
<?php
    function _getStatusCodeMessage($status){
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            408 => 'Request Timeout',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    function _sendResponse($status = 200, $body = '', $content_type = 'text/html'){
        // set the status
        $status_header = 'HTTP/1.1 '.$status.' '._getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: '.$content_type);

        // pages with body are easy
        if($body != ''){
            // send the body
            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else{
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status){
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL '.$_SERVER['REQUEST_URI'].' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'].' Server at '.$_SERVER['SERVER_NAME'].' Port '.$_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>'.$status.' '._getStatusCodeMessage($status).'</title>
	</head>
	<body>
		<h1>'._getStatusCodeMessage($status).'</h1>
		<p>'.$message.'</p>
		<hr />
		<address>'.$signature.'</address>
	</body>
	</html>';

            echo $body;
            exit;
        }
    }


if (!function_exists('json_decode')) {
    function json_decode($content, $assoc=false) {
        require_once 'json/JSON.php';
        if ($assoc) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        }
        else {
            $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

if (!function_exists('json_encode')) {
    function json_encode($content) {
        require_once 'json/JSON.php';
        $json = new Services_JSON;
        return $json->encode($content);
    }
}


?>


<?php

	$source = $_GET["url"];
	$scheme = $_GET["scheme"];
	$format = $_GET["format"];
	$maxwidth = $_GET["maxwidth"];
    
    
	if ( $format == "json")
	{
		$ret["type"] = "video";
		//$ret["html"] = "<iframe width=\"450\" height=\"253\" src=\"".$source."\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>";
        $ret["html"] = "<iframe width=\"450\" height=\"253\" src=\"".$source."\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>";
		 _sendResponse(200, json_encode($ret),true);
		return 200;
	}
?>
<link rel="stylesheet" href="//releases.flowplayer.org/7.0.4/commercial/skin/skin.css">
    <style>

   </style>
   <script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
  <script src="//releases.flowplayer.org/7.0.4/commercial/flowplayer.min.js"></script>
  <script src="//releases.flowplayer.org/hlsjs/flowplayer.hlsjs.min.js"></script> 
  <script>
  flowplayer(function (api) {
    api.on("load", function (e, api, video) {
      $("#vinfo").text(api.engine.engineName + " engine playing " + video.type);
    }); });
  </script>

<div class="flowplayer fixed-controls no-toggle no-time play-button obj"
      style="    width: 100%;
    height: 100%;
    margin-left: 0%;
    margin-top: 0%;
    z-index: 1000;" data-key="$812975748999788" data-live="true" data-share="false" data-ratio="0.5625"  data-logo="">
      <video autoplay="false" stretch="true">

         <source type="application/x-mpegurl" src="<?php echo $source;?>">
      </video>   
   </div>

