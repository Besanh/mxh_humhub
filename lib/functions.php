<?
$dirServer = dirname(__FILE__) . '/';

  include($dirServer ."../../tools/fckeditor/fckeditor.php");

  // This function retrieve the already done groups and return the html for displaing it.
  // $type: the group type
  // $js: a return variable that contains the javascript for the ajax function
  // RETURN: this function return the html for display the groups

  function lastVersion(&$js) {
    global $pbx,$relBasePath,$cwLanguage,$tv_version,$tv_plugin_version;

 //   $html="Your current TVPBX version is <strong>v. $tv_version</strong> using plugin subsystem <strong>v. $tv_plugin_version</strong>";
    $html=$cwlang["setting"]["lastversion"].$tv_version.$cwlang["setting"]["plugversion"].$tv_plugin_version."</strong>";

    //------------------------------   CLIENT SIDE    --------------------------

    //html code
    ob_start()
    ?>
            <h2><?=$cwlang["group"][""]?>TVPBX Version</h2>
      <p class="blockintro">
        <?=$cwlang["group"][""]?>
        <!--
        <a href="#" onclick="return do_getLastVersion(1);" class="admin" ><?=$cwlang["group"][""]?>Check for newer version</a> (you'll send ONLY your current version information to our server)
        -->
      </p>
      <div class="block">
        <div id="versionTable"><?=$html?></div><div id="versionStatus"></div>
        <div style="clear: both"></div>
      </div>
    <?
    $contents=ob_get_contents();
    ob_end_clean();


    //javascript code
          ob_start();
      ?>
            function do_getLastVersion_cb(txt) {
        document.getElementById("versionStatus").innerHTML="";
        document.getElementById("versionTable").innerHTML=txt;
            }

            function do_getLastVersion(txt) {
        document.getElementById("versionStatus").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"][""]?>Checking in progress...";
        x_getLastVersion(do_getLastVersion_cb);
        return false;
            }
      <?

    $js.=ob_get_contents();
    ob_end_clean();

    //------------------------------   SERVER SIDE    --------------------------
          function getLastVersion() {
      global $relBasePath,$pbx,$tv_version;
      $lastVersion=$pbx->versionCheck();
      if ($err=$pbx->getError()) return ("Error: $err");
      if (is_array($lastVersion))
      foreach ($lastVersion as $v) $arr[$v["key"]]=$v["value"];

      if ($arr["last_version"]==$arr["current_version"])
        $ret="Your current TVPBX version is the <strong>most updated one</strong>. No newer versions available.";
      else
        $ret="Your current TVPBX version is the <strong>".$arr["current_version"]."</strong>. On the <a href=\"http://www.daivietcontrol.net\" target=\"_blank\">www.daivietcontrol.net</a> you can find the latest <strong>".$arr["last_version"]."</strong> TVPBX Version.";

      return $ret;
          }

          sajax_export("getLastVersion");
    return $contents;
  }

/*******************************************************************************
****************      Go To Main Page *****************************************/
  function goToMainPage($alert,$page="") {
    session_start();
    $_SESSION['alert']=$alert;
    if ($page=="") header("location: ../");
    else header("location: $page");
    die;
    return;
  }

  function getAlert() {
//    session_start();
    $alert=$_SESSION['alert'];
    unset($_SESSION['alert']);
    return $alert;
  }


/*******************************************************************************
****************      User informations *****************************************/

  function checkUserAccount($username,$account, $password) {
    global $pbx;
//    $result=$pbx->checkUser($username,$account,$password);
      $option=Array(
            "command" => "LOGINUSER",
            "userid" => $username,
            "username" => $username,
            "agentpwd" => $password,
            "agentid" => $account
          );
  // create the web interface
  	$result=$pbx->agents_info_gw($option);
    return $result;
}

  function getAvailLang() {
    global $absBasePath;
    if ($handle = opendir($absBasePath."public/lang")) {
       while (false !== ($file = readdir($handle))) {
           if ($file != "." && $file != ".." and substr($file,-4,4)==".php") {
               $ret[]=substr($file,0,strlen($file)-4);
           }
       }
       closedir($handle);
    }
    return $ret;
        }


/*******************************************************************************
*****************     VARIOUS                          ************************/

  function getIcon($tech) {
    global $relBasePath,$absBasePath;
    $icon="/public/img/tech/$tech.gif";
    if (file_exists($absBasePath.$icon))
      $ret=$relBasePath.$icon;
    else $ret=$relBasePath."public/img/tech/unknown.gif";
    $ret="<img src=\"$ret\" />";
    return $ret;
  }

/*******************************************************************************
*****************     Create an Html Version of Action ************************/


/**
 * Convert an XML data structure that is *read from file* into an array.
 *
 * Array stucture: Node:[0]['name]=TAG opened; [0]['text']=Value [0]['closetag']=TAG closed; [0]['elements']=Array of subnode
 * @param string $xml filename with xml data to read and convert.
 * @return array $xmlary reflecting structure
 * @todo separate file_get_contents from the function. This function should not read the file but take only the the xml string!
 */
  if (!function_exists("xml2array")) {
    function xml2array ($xml)
    {
       $xmlary = array();

       // TODO: should not be done here!
       if ((strlen ($xml) < 256) && is_file ($xml)) {
         $xml = file_get_contents ($xml);
       }

       $ReElements = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*?)<(\/\s*\1\s*)>)/s';
       $ReAttributes = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
       preg_match_all ($ReElements, $xml, $elements);

       foreach ($elements[1] as $ie => $xx) {

         $xmlary[$ie]["name"] = $elements[1][$ie];

         if ($attributes = trim($elements[2][$ie])) {
           preg_match_all ($ReAttributes, $attributes, $att);
           foreach ($att[1] as $ia => $xx) {
             // all the attributes for current element are added here
             $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
           }
         } // if $attributes

         // get text if it's combined with sub elements
         $cdend = strpos($elements[3][$ie],"<");

         if ($cdend > 0) {
           $xmlary[$ie]["text"] = substr($elements[3][$ie],0,$cdend -1);
         } // if cdend

         if (preg_match ($ReElements, $elements[3][$ie])) {
           $xmlary[$ie]["elements"] = xml2array ($elements[3][$ie]);
         } else if (isset($elements[3][$ie])) {
           $xmlary[$ie]["text"] = $elements[3][$ie];
         }

         $xmlary[$ie]["closetag"] = $elements[4][$ie];

       }//foreach elements[1]

       return $xmlary;
    }
  }



/**
 * Convert an xmlarray data structure build with xml2array to a simple hash with UNIQUE keys of one level
 *
 * Subnodes are kept in their orginal data stucture. This allows recusive use of this function
 * Note: Works only if nodes have unique TAG-Names per level.
 * Array stucture: Node:[0]['name]=TAG opened; [0]['text']=Value [0]['closetag']=TAG closed; [0]['elements']=Array of subnode
 * @param array $xmlArr reflecting structure
 * @return array $xmlHash with unique key-value pairs
 * @see xlm2array
 */
  if (!function_exists("xmlArray2Hash")) {
    function xmlArray2Hash ($xmlArr) {

      $xmlHash = "";
      foreach ($xmlArr as $node) {

        if (array_key_exists('elements', $node)) {
          if (is_array($node['elements'])) {
            // node has subnodes. keep them as they are
            $xmlHash[$node['name']] = $node['elements'];
          }
        }
        else {
          // final node without subnodes
          $xmlHash[$node['name']] = $node['text'];
        }

      } // foreach

      return $xmlHash;

    }
  }

  if (!function_exists("errArr")) {
    function errArr($arr) {
      if (count($arr)==0) { error_log("ok!"); }
      else {
        ob_start();
        print_r($arr);
        $contents=ob_get_contents();
        ob_end_clean();
        error_log($contents);
      }
      unset($arr);
      return;
    }
  }


/**
 * Get content from file.
 *
 * @param string $filename File to read  data from
 * @param integer $use_include_path Set to 1 if the include path should be used
 * @return string $data  read from file or "" if nothing to read or read failed
 */
  if (!function_exists("file_get_contents")) {
    function file_get_contents($filename, $use_include_path = 0) {
      $data = ""; // just to be safe. Dunno, if this is really needed
      $file = @fopen($filename, "rb", $use_include_path);
      if ($file) {
        while (!feof($file)) $data .= fread($file, 1024);
        fclose($file);
      }
      return $data;
    }
  }




/**
 * Put content to file.
 *
 * @param string $filename File to write  data to. If the file does exist, it will be deleteted!
 * @param string $data data to write to file
 * @param integer $use_include_path Set to 1 if the include path should be used
 * @return integer 1=ok 0=failure
 */
  if (!function_exists("file_put_contents")) {
    function file_put_contents($filename, $data, $use_include_path = 0) {
      $file = @fopen($filename, "wb", $use_include_path);
      if ($file) {
        fwrite($file, $data);
        fclose($file);
        return 1;
      } else {
        return 0;
      }
    }
  }

/**
 * Convert an array into an XML data structure
 *
 * @param array $array data to convert
 * @param integer $deep. Helper for recursion when indent is active. Matches deeps of structure.
 * @return string $result XML structure
 * @todo add a parameter to enable/disable/modify indent
*/
  if (!function_exists("array2xml")) {
    function array2xml($array, $deep = 1) {

      //$indent = str_repeat("\t", $deep);
      $indent = str_repeat("  ", $deep);
      $xmlstr = "";

      foreach ($array as $key => $value) {
        if (!is_array($value)) {
          $xmlstr .= $indent . "<". $key .">";
          //$xmlstr .= $indent . "\t<![CDATA[". $value ."]]>\n";
          $xmlstr .= "". $value ."";
          $xmlstr .= "</". $key .">\n";
        } else {
          $currentkey = $key;
          $xmlstr .= $indent . "<". $currentkey .">\n";
          $xmlstr .= array2xml($value, ++$deep);
          $xmlstr .= $indent . "</". $currentkey .">\n";
        }
      }
      return $xmlstr;
    }
  }


/**
 * Create a pluginCache based on a collection of VO-Objects and a plugin information description
 *
 * @param array $pli plugin information
 * @param array $pld xml description and content of plugin data
 * Format: array[type][id]. array['application'][id]
 * @return $plugin (=relative cache directory name) or "" if failure
 */
  if (!function_exists("createPluginCache")) {
    function createPluginCache($pli, $pld) {

      global $pbx, $absPluginPath, $cwLanguage;

      // contains xml serialized data and description of objects that are part of the plugin
      $xmlPld = "";
      // contains xml serialized data of plugin information to add to the plugin
      $xmlPli= "";
      // number of elements in plugin
      $ecount = 0;

      $indent = "  ";

      //
      // plugin data
      //

      // create a valid xml. so we need a unique start tag
      $xmlPld = "<plugindata>\n";

      foreach ($pld as $mytype=>$myopt) {

        foreach ($myopt as $mykey=>$myid) {

          switch ($mytype) {
            case APPLICATION:
              $data = $pbx->getApplication($myid);
              $xmlPld .= "<application>\n";
              $xmlPld .= array2xml($data[0]);
              $xmlPld .= "</application>\n";
              $ecount += 1;
            break;

            case MACRO:
              $data = $pbx->getMacro($myid);
              $xmlPld .= "<macro>\n";
              $xmlPld .= array2xml($data[0]);
              $xmlPld .= "</macro>\n";
              $ecount += 1;
            break;

            case QUEUE:
              $data = $pbx->getQueue($myid);
              $xmlPld .= "<queue>\n";
              $xmlPld .= array2xml($data[0]);
              $xmlPld .= "</queue>\n";
              $ecount += 1;
            break;

            // unsupported type
            default:
            break;
          } // switch

        } // foreach $myopt

      } // foreach $pld

      $xmlPld .= "</plugindata>\n";


      //
      // create the plugin package directory
      //

      // file and directory are build from name and version. so check for invalid entries like .. etc.
      // replace whitespaces and '.' with '_'
      $pli['name'] = preg_replace('/ /', '_', $pli['label']);
      $pli['name'] = preg_replace('/\./', '_', $pli['name']);

      // replace whitespaces and '.' with '_'
      $pli['version'] = preg_replace('/\.\.+/', '', $pli['version']);
      $pli['version'] = preg_replace('/\/+/', '', $pli['version']);

      // because so often needed, we add the packageName as field.
      $pli['packageName'] = $pli['name'] . "." . $pli['version'];

      // number of elements in plugin - also quite useful
      $pli['elementCount'] = $ecount;

      //
      // create the plugin package directory
      //

      // directory name without / for new plugin
      $plugin = $pli['name'] . "." . $pli['version'];
       // absolute path to new plugin directory
      $absPluginDir = $absPluginPath . $plugin . "/";

      // make directories if they do not exist
      // should work, because ../sandbox must be read/writable per definition
      if (!is_dir($absPluginPath)) { mkdir($absPluginPath); }
      if (!is_dir($absPluginDir)) { mkdir($absPluginDir); }

      // write the data files. need to do this first, because we add the md5sum to the plugininfo
      file_put_contents($absPluginDir . "plugindata.xml", $xmlPld, 0);
      $md5PluginData = md5_file($absPluginDir . "plugindata.xml");


      //
      // plugin information section
      //

      $xmlPli = "<plugininfo>" . "\n";
      $xmlPli .= array2xml($pli);
      $xmlPli .= $indent . "<content>" . "\n";
      $xmlPli .= $indent . $indent . "<file>" . "\n";
      $xmlPli .= $indent . $indent . $indent . "<filename>" . "plugindata.xml" . "</filename>" . "\n";
      $xmlPli .= $indent . $indent . $indent . "<md5>" . $md5PluginData . "</md5>" . "\n";
      $xmlPli .= $indent . $indent . "</file>" . "\n";

      $xmlPli .= $indent . "</content>" . "\n";
      $xmlPli .= "</plugininfo>" . "\n";

      // write the plugin information file
      file_put_contents($absPluginDir . "plugininfo.xml", $xmlPli, 0);

      // see if all went ok and return
      if (is_file($absPluginDir . "plugindata.xml")) {
        return $plugin;
      } else {
        return "";
      }

    }
  }



/**
 * Create a plugin cache directory based on an activated plugin in the database.
 *
 * Used to ensure that a pluginCache exists for each activated plugin
 * If a cache directory or archive does not exist, it will be created automatically.
 * @param integer $id as used for plugin in database
 * @see createPluginCache
 * @see readPluginCache
 * @return $plugin (=relative cache directory name) if a cachedir were created or "" if plugin does not exist or nothing to do
 */
  if (!function_exists("createPluginCachefromDB")) {
    function createPluginCachefromDB($id) {

      global $pbx, $absPluginPath, $cwLanguage, $tv_plugin_version, $tv_version;

      $installedPlugin = $pbx->getPlugin($id);
      // exit if plugin with the given id does not exist
      If (!count($installedPlugin)) return "";

      // $plugin contains full name like "Wake-Up_call.1.1.10"
      $plugin = $installedPlugin[0]['name'] . "." . $installedPlugin[0]['version'];
      $plCache = readPluginCache($plugin);
      // exit if plugincache exist
      if (count($plCache)) return "";

      //
      // plugin cache is missing. create it.
      // should never happen by design, but you never know...
      // maybe the user has delete the files in the plugin dir...
      //

      // build entity array grouped by type to allow use of createPluginCache
      $entArr = $pbx->getPluginEntities($id);
      foreach ($entArr as $eNr) {
        // Bad hack. Will change anyway with the next plugin release
        if ($eNr['entity_type'] == "application" )  { $etype = APPLICATION;}
        if ($eNr['entity_type'] == "macro" )  { $etype = MACRO;}
        if ($eNr['entity_type'] == "queue" )  { $etype = QUEUE;}
        $pld[$etype][] = $eNr['entity_id'];
      }

      // build information array. map database fields
      $pli['name'] = $installedPlugin[0]['name'];
      $pli['label'] = $installedPlugin[0]['label'];
      $pli['packageName'] = $installedPlugin[0]['package_name'];
      $pli['version'] = $installedPlugin[0]['version'];
      // we also add the current date, because plugin is recreated. (format 2006-06-21)
      $pli['date'] = date("Y-m-d");
      $pli['description'] = $installedPlugin[0]['description'];
      $pli['authorName'] = $installedPlugin[0]['author_name'];
      $pli['authorEmail'] = $installedPlugin[0]['author_email'];
      $pli['authorWebsite'] = $installedPlugin[0]['author_website'];
      // Note we add the voVersion and voPlVersion from this system,
      // because we recreate the cache dir and therefore the entire plugin
      $pli['voVersion'] = $tv_version;
      $pli['voPlVersion'] = $tv_plugin_version;


      // create pluginCache
      $ret = createPluginCache($pli, $pld);
      // create pluginArchive
      if ($ret != "") { createPluginArchive($ret); }

      return $ret;

    }
  }



/**
 * Create a plugin zip compressed archive based on a plugin directory.
 *
 * Plugin archives are used if a plugin should be uploaded / downloaded
 * @param string $plugin ( equals to relative directory name. I.e. Wake_UP-Call.1.1.10)
 * @return $md5sum of pluginArchive
 */
  if (!function_exists("createPluginArchive")) {
    function createPluginArchive($plugin) {

      global $absPluginPath;
      $pluginFiles = $absPluginPath . $plugin . "/*";
      $pluginArchive = $plugin . ".zip";

      // create package in $absPluginPath. Zip file contains no path information
      // "zip -j wakeup.zip Wake-Up_Call.1.1.10/*"
      $cmd = "zip -j  " . $absPluginPath . $pluginArchive . " " . $pluginFiles;
      $output=shell_exec("$cmd");

      // see if all went ok
      if (is_file($absPluginPath . $pluginArchive)) {
        $md5sum = md5_file($absPluginPath . $pluginArchive);
      } else {
        $md5sum = "";
      }
      return $md5sum;
    }
  }



/**
 * Check wether a specific plugin exists in the cache, or get a list of all plugins in the plugin cache directory
 *
 * Note: Creates a pluginArchive in case it does not exist.
 * @param string $plugin or "" for a list of all plugin packages.
 * @return array $pluginList containing [0]=$plugin, a list of all plugins, or "" if the plugin does not exist
 */
  if (!function_exists("readPluginCache")) {
    function readPluginCache($plugin) {

      global $absPluginPath;

      $ret = array();

      // check if the plugin directory exist. If not create it and exit because they cannot be any plugins
      if (!is_dir($absPluginPath)) {
        mkdir($absPluginPath);
        return $ret;
      }

      $dh = opendir($absPluginPath);
      while (false !== ($pldir = readdir($dh))) {
      //add only subdirs
        if ( is_dir($absPluginPath ."/" .$pldir) ) {
          //avoid to list just stuff
          if ( ($pldir != ".") and ($pldir != "..") ) {
            // found a valid directory. check wether to add to list
            if (($plugin == $pldir) or ($plugin == "")) {
              $ret[] = $pldir;
              // check if pluginArchive exists. if not create it
              if (!is_file($absPluginPath . $pldir . ".zip")) {
                createPluginArchive($pldir);
              }
            }
          }
        }
      }// while
      closedir($dh);

      // sort the result, so packages stay together, looks better in any type of list
      if (is_array($ret)) { sort($ret); }

      return $ret;
    }
  }



/**
 * Read plugin information for a specific plugin from cache
 *
 * @param string $plugin
 * @return array $xmlPluginInfo or "" if failure
 */
  if (!function_exists("readPluginInfoAsXML")) {
    function readPluginInfoAsXML($pl) {
      global $absPluginPath;
      return file_get_contents($absPluginPath . "$pl" . "/plugininfo.xml" );
    }
  }



/**
 * Read plugin data for a specific plugin from cache  and return xml string
 *
 * @param string $plugin
 * @return string $xmlPluginData or "" if failure
 */
  if (!function_exists("readPluginDataAsXML")) {
    function readPluginDataAsXML($pl) {
      global $absPluginPath;
      return file_get_contents($absPluginPath . "$pl" . "/plugindata.xml" );
    }
  }



/**
 * Read plugin information for a specific plugin from cache, or get info about all plugins
 *
 * Creates the corresponding pluginCache and pluginArchive in case it does not exists.
 * Adds md5sum of pluginArchive to return array as arr[plugin]['md5']
 * @param string $plugin or "" for a list of all plugins
 * @return array $pluginList containing an array['plugin'] with content of plugininfo.xml for each entry or "" if no valid plugin exist
 */
  if (!function_exists("readPluginInfo")) {
    function readPluginInfo($plugin) {

      global $absPluginPath, $pbx;

      $pli = array();

      // first make sure that we have a cache entry for each plugin in the database
      // by design that should not be necessary, but you never know...
      $installedPlugins = $pbx->getPlugin("");
      foreach ($installedPlugins as $p=>$v) {
        // This is safe. createPluginCachefromDB checks wether the pluginCache exists automatically
        $ret = createPluginCachefromDB($v['id']);
      }

      $plList=readPluginCache($plugin);

      // walk through the list
      foreach ($plList as $pl) {

        // check wether this is a valid plugin cache. i.e skip scratch and the like
       if (is_file($absPluginPath . "$pl" . "/plugininfo.xml")) {
          $ret = file_get_contents($absPluginPath . "$pl" . "/plugininfo.xml" );

          $pli[$pl] = xml2array($ret);

          // add node for md5sum of archive . archive must exists because readPluginCache() would have created it if necessary
          $newNode['name'] = 'md5'; $newNode['closetag'] = '/md5';
          $newNode['text'] = md5_file($absPluginPath . "$pl" . ".zip");
          $pli[$pl][0]['elements'][] = $newNode;

          // add a node to indicate wether the plugin is installed
          $newNode['name'] = 'state'; $newNode['closetag'] = '/state';
          $installed = $pbx->getPluginByPackageName($pl);
          if (count($installed)) {
            $newNode['text'] = STATE_SELECTED;
          } else {
            $newNode['text'] = STATE_AVAILABLE;
          }
          $pli[$pl][0]['elements'][] = $newNode;

        } // if

      } // foreach plList

      return $pli;
    }
  }



/**
 * Read plugin data for a specific plugin from cache, or get info about all plugins
 *
 * Creates the corresponding pluginCache and pluginArchive in case it does not exists.
 * @param string $plugin or "" for a list of all plugins
 * @return array $pluginList containing an array['plugin'] with content of plugininfo.xml for each entry or "" if no valid plugin exist
 */
  if (!function_exists("readPluginData")) {
    function readPluginData($plugin) {

      global $absPluginPath, $pbx;

      $plc = "";

      // first make sure that we have a cache entry for each plugin in the database
      // by design that should not be necessary, but you never know...
      $installedPlugins = $pbx->getPlugin("");
      foreach ($installedPlugins as $p=>$v) {
        // This is safe. createPluginCachefromDB checks wether the pluginCache exists automatically
        $ret = createPluginCachefromDB($v['id']);
      }

      $plList=readPluginCache($plugin);
      // walk through the list

      foreach ($plList as $pl) {
        // check wether this is a valid plugin cache. i.e skip scratch and the like
        if (is_file($absPluginPath . "$pl" . "/plugindata.xml")) {
          $ret = file_get_contents($absPluginPath . "$pl" . "/plugindata.xml" );
          $plc[$pl] = xml2array($ret);
        }
      } // foreach plList

      return $plc;
    }
  }




/**
 * Get URL link to pluginArchive for download, or get URL links for all pluginArchives
 *
 * @param string $plugin or "" for links of all pluginArchives
 * @return array $pluginArchiveURL containing an array with links for each entry or an array with [0]="" if failure
 * @todo replace hardcoded part of url with a global var or a function
 */
  if (!function_exists("readPluginArchiveURL")) {
    function readPluginArchiveURL($plugin) {

      global $absPluginPath;

      $plList=readPluginCache($plugin);
      // walk through the list
      foreach ($plList as $plInfo) {
        // first check if plugin exists. if not create it
        if (!is_file($absPluginPath . $plInfo . "zip")) {
          // TODO: ugly hack to get URL.
          $plLink[$plInfo] = "../../../sandbox/plugins/" . "$plInfo" . ".zip";
        }
      } // foreach plList

      return $plLink;
    }
  }


/**
 * Create a pluginCache from uploaded pluginArchive that is zip compressed
 *
 * Validate archive, create pluginCache directory with content, rename the directory and archive to match the name and version of the plugin
 * Note: This function does not activate the plugin.
 * @param string $pluginArchive (I.e. Wake_UP-Call.1.1.10.zip)
 * @return array $ret arr['state'] = true | false, arr['msg'] = statusmsg;
 * @todo do more validation for plugin. check version number
 */
  if (!function_exists("createPluginCachefromArchive")) {
    function createPluginCachefromArchive($pluginArchive) {

      global $absPluginPath, $cwLanguage;

      // use scratch dir as a temporary directory to allow to validate the content
      $scratch = "scratch";
      $scratchDir = $scratch . "/";
      $ret = array();

      // make directory if it does not exist
      // should work, because ../sandbox must be read/writable per definition
      if (!is_dir($absPluginPath.$scratchDir)) { mkdir($absPluginPath.$scratchDir); }
      // remove any possible previous files
      $cmd = "rm " . $absPluginPath . $scratchDir . "*";
      shell_exec("$cmd");

      // unzip package in $absPluginPath. Zip file contains no path information
      $cmd = "unzip " . $absPluginPath . $pluginArchive . " -d " . $absPluginPath.$scratchDir;
      shell_exec("$cmd");


      // check if valid plugin content - must at least contain plugininfo.xml
      if (!is_file($absPluginPath . $scratchDir . "plugininfo.xml")) {
        $state = false;
        $msg = $cwlang["plugins"]["pluginNotUploaded"];
        $msg .= "No valid Plugin Archive.";
        // remove uploaded file and its content
        $cmd = "rm " . $absPluginPath . $pluginArchive;
        shell_exec("$cmd");
        $cmd = "rm " . $absPluginPath . $scratchDir . "*";
        shell_exec("$cmd");
        $cmd = "rm -R " . $absPluginPath . $scratchDir;
        shell_exec("$cmd");

        $ret['state'] = $state;
        $ret['msg'] = $msg;
        return $ret;
      }


      // seems to be plugin. read the content of plugininfo.xml
      $newPlInfo = array(); // array for new uploaded plugin
      $curPlInfo = array(); // array for a potential existing plugin with the same name and version
      $ret = readPluginInfo($scratch);
      $newPlInfo = xmlArray2Hash($ret[$scratch][0]['elements']);
      $newPackageName = $newPlInfo['packageName'];
      $newPackageMD5 = md5_file($absPluginPath . $pluginArchive);

      // readPluginInfo created "$scratch.zip" -  need to remove this file
      $cmd = "rm " . $absPluginPath . $scratch . ".zip";
      shell_exec("$cmd");

      if ($newPackageName != "") {
        // check wether we already have a plugin with the same name and version
        $ret = readPluginInfo($newPackageName);
        if (count($ret)) {
          $curPlInfo = xmlArray2Hash($ret[$newPackageName][0]['elements']);
        }

        if ($curPlInfo['packageName'] != "") {
          // we already have a plugin with the same name and version. Do nothing and inform the user
          $state = false;
          $msg = $cwlang["plugins"]["pluginNotUploaded"] . "<br />";
          $msg .= "This plugin already exists in the same version." . "<br />" . "<br />";
          $msg .= "Plugin Package: " . $newPackageName . "<br />";
          $msg .= "Existing plugin MD5: " . $curPlInfo['md5'] . "<br />";
          $msg .= "Uploaded plugin MD5: " . $newPackageMD5 . "<br /><br />";
          $msg .= "If you want to use the uploaded plugin, delete the existing plugin first and try again." . "<br />";

          // remove uploaded file and its content
          $cmd = "rm " . $absPluginPath . $pluginArchive;
          shell_exec("$cmd");
          $cmd = "rm " . $absPluginPath . $scratchDir . "*";
          shell_exec("$cmd");
          $cmd = "rm -R " . $absPluginPath . $scratchDir;
          shell_exec("$cmd");

          $ret['state'] = $state;
          $ret['msg'] = $msg;
          return $ret;
        } //if we have already that package
      }

      // ok it seems that we can go ahead. we still have the data of the plugin in our array
      // rename the scratch dir to the packageName
      $cmd = "mv " . $absPluginPath . $scratchDir . "  " . "\"" . $absPluginPath . "$newPackageName" ."\"";
      shell_exec("$cmd");
      // rename the archive back to the packageName
      $cmd = "mv " . $absPluginPath . $pluginArchive . "  " . "\"" . $absPluginPath . "$newPackageName" . ".zip" . "\"";
      shell_exec("$cmd");


      // This should not be necessary - but it is safe
      // remove the scratch Dir
      $cmd = "rm -R " . $absPluginPath . $scratchDir;
      shell_exec("$cmd");

      // build the ok message
      $state = true;
      $msg = "<strong>" . $cwlang["plugins"]["pluginUploaded"] . "</strong>" . "<br />";
      $msg .= "<strong>" . "Plugin Package name: " . "</strong>" . $newPackageName . "<br />";
      $msg .= "<strong>" . "Plugin MD5: " . "</strong>" . $newPackageMD5 . "<br />";

      $ret['state'] = $state;
      $ret['msg'] = $msg;

      return $ret;
    }
  }


/**
 * Delete a plugin with its archive and cache directory
 *
 * @param string $plugin (I.e. Wake_UP-Call.1.1.10)
 * @return integer $ret 1=ok 0=err
 * @todo check wether the plugin is active and handle the database together with vo-objects that are part of the plugin
 */
  if (!function_exists("deletePluginCache")) {
    function deletePluginCache($plugin) {

      global $absPluginPath, $cwLanguage;

      // does the cache exists
      if (!is_dir($absPluginPath . $plugin . "/")) {
        $ret = 0;
        return $ret;
      }

      // remove the cache Dir
      $cmd = "rm -R " . $absPluginPath . $plugin . "/";
      shell_exec("$cmd");

      // remove the archive
      $cmd = "rm " . $absPluginPath . $plugin . ".zip";
      shell_exec("$cmd");

      $ret = 1;

      return $ret;

    }
  }

    function _define_newline()
    {
         $unewline = "\r\n";

         if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win'))
         {
            $unewline = "\r\n";
         }
         else if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac'))
         {
            $unewline = "\r";
         }
         else
         {
            $unewline = "\n";
         }

         return $unewline;
    }

    /**
    * @desc Define the client's browser type
    * @access private
    * @return String A String containing the Browser's type or brand
    */
    function _get_browser_type()
    {
        $USER_BROWSER_AGENT="";

        if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
        {
            $USER_BROWSER_AGENT='OPERA';
        }
        else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
        {
            $USER_BROWSER_AGENT='IE';
        }
        else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
        {
            $USER_BROWSER_AGENT='OMNIWEB';
        }
        else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
        {
            $USER_BROWSER_AGENT='MOZILLA';
        }
        else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
        {
            $USER_BROWSER_AGENT='KONQUEROR';
        }
        else
        {
            $USER_BROWSER_AGENT='OTHER';
        }

        return $USER_BROWSER_AGENT;
    }

    /**
    * @desc Define MIME-TYPE according to target Browser
    * @access private
    * @return String A string containing the MIME-TYPE String corresponding to the client's browser
    */
    function _get_mime_type()
    {
        $USER_BROWSER_AGENT= $this->_get_browser_type();

        $mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
                       ? 'application/octetstream'
                       : 'application/octet-stream';
        return $mime_type;
    }



 // This function retrieve the already done groups and return the html for displaing it.
  // $type: the group type
  // $js: a return variable that contains the javascript for the ajax function
  // RETURN: this function return the html for display the groups

////////////////////////// TIN add
  function listCampaign(&$js,$campaign=null,$project=null) {
    global $pbx,$relBasePath,$cwLanguage;

    //create the technology list
      $req=Array(
            "command" => "SEL",
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "oldid" => "",
            "update" => ""
          );
    $tech=$pbx->CampaignFunction($req);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$campaign) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }    		
    $html="<td  width=\"13%\" class=\"required\" >Campaign</td><td  width=\"34%\"> <select id=\"client[outboundcampaign]\" name=\"client[outboundcampaign]\" onchange=\"do_changeCampaign(this.value,'$campaign','$project')\"  style=\"width: 230px\">$html</select></td>";    
    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
<!--
      <p class="blockintro">
        <?=$cwlang["group"][""]?>
        <?=$html?>
      </p>
      <td  width="13%" class="required"  >Detail
      </td>
      <td width="317">
        <div id="campaignPanel"></div>
      </td>
     </tr>           
-->
       <fieldset style="color: #FFFF00; padding: 0; background-color: #CCCCCC">
     	<legend><p align="left"><font size="4" color="#000080"><?=$cwlang["menu"]["pbx-campaign"]?></font></p></legend>
	<table border="0" width="100%" align="left">
	<tr>
        	<?=$html?>
      		<td  class="required" width="13%" >Project</td>
	   	<td >
      	 	<div id="campaignPanel"></div>
      		</td>
	</tr>           
	</table>
      </fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();
    $contents.="<script>do_changeCampaign(document.getElementById('client[outboundcampaign]').value,'$campaign','$project')</script>";
    //javascript code
          ob_start();
      ?>
            function do_changeCampaign_cb(html) {
				var dest=document.getElementById("campaignPanel");
				dest.innerHTML=html;
            }

            function do_changeCampaign(campaignId,selcampaign,selSubcampaign) {
				document.getElementById("campaignPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
				x_changeCampaign(campaignId, selcampaign, selSubcampaign,do_changeCampaign_cb);
            }	    
      <?

    $js.=ob_get_contents();
    ob_end_clean();


    //------------------------------   SERVER SIDE    --------------------------
      function changeCampaign($campaignId,$selcampaign,$selSubcampaign) {
      global $pbx, $relBasePath,$pbx;

	      //a control
		  if ($campaignId=="") return "";
		  //get the configuration option
      $req=Array(
            "command" => "SEL",
            "campaign" => $campaignId,
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "oldid" => "",
            "update" => ""
          );

		  $optionArr=$pbx->ProjectFunction($req);
		 //create the HTML options
		  $html.=getSubCampaign($optionArr,$selSubcampaign);
			return $html;
       }
       sajax_export("changeCampaign");
	   return $contents;
  }
/////////////
  function getSubCampaign($optionArr,$selSubcampaign) {
    global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
			$element = "<select size=\"1\" id=\"client[outboundproject]\" name=\"client[outboundproject]\"  style=\"width: 230px\" >";

			foreach($optionArr as $v) {
				$cat=$v["name"];
				$catid=$v["id"];
				$s="";
				if ($cat==$selSubcampaign) 
				{
					$s=" selected=\"selected\" "; 
				} 
				else $s="";
//				$element.="<option value=\"$cat\" $s>$cat</option>";
				$element.="<option value=\"$catid\" $s>$cat</option>";
			}  //end of foreach
		}
		else {
			$element = "<select size=\"1\" id = name=\"client[outboundproject]\" name=\"client[outboundproject]\"  style=\"width: 230px\">";
		} //end of if is_array
		$element .= "</select>";
	$ret = $element;
    return $element;
  }
/////////////////////////////////
  function showDepartment(&$js,$departid=null,$agentid=null,$addblank = 1,$titleadd="",$editmode = 1,$agentaddblank = 1,$agenttitleadd="",$agenteditmode = 1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;
	$html = "";

	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html="<option value=\"$tId\" $s>$tName</option>";
	if ( $titleadd != "")
	{
	        $tId = "-100";
       	 $tName = $titleadd;
	        $html.="<option value=\"$tId\" $s>$tName</option>";
	}
    }

		$userid =  $_SESSION["cicUserId"];
    //create the technology list
	$qry = "select * from  lts_department  where userid='".$userid."' order by dept_name asc";
    $tech=$pbx->QueryData($qry);
    foreach($tech as $t) {
      $tId=$t["dept_id"];
      $tName = $t["dept_name"];
      if ($tId==$departid)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
//    $html="<select id=\"client[department]\" name=\"client[department]\"  style=\"width: 180px\" $disabled >".$html."</select>";
//    $html="<td  width=\"13%\" class=\"required\" >Campaign</td><td  width=\"34%\"> <select id=\"client[department]\" name=\"client[department]\" onchange=\"do_changeDepartment(this.value,'$departid','$agentid')\"  style=\"width: 230px\">$html</select></td>";    
//    $html="<select id=\"client[department]\" name=\"client[department]\" onchange=\"do_changeDepartment(this.value,'$departid','$agentid','$agentaddblank','$agenttitleadd','$agenteditmode')\"  style=\"width: 180px\">$html</select>";
    $html="<select id=\"department_id\" name=\"department_id\" onchange=\"do_changeDepartment(this.value,'$departid','$agentid','$agentaddblank','$agenttitleadd','$agenteditmode')\"  style=\"width: 180px\">$html</select>";

    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
	<td  class="required" width="81">
               <dt class="required"><label for="client[voffice]"><?=$cwlang["personal"]["department"]?>:</label></dt>
	</td>
	<td>
        	<?=$html?>
	</td>
	<td  class="required" width="81"><?=$cwlang["nr-menu"]["acd-col2"]?></td>
   	<td >
      	 	<div id="agentPanel"></div>
	</td>
    <?
    $contents=ob_get_contents();
    ob_end_clean();
    $contents.="<script>do_changeDepartment(document.getElementById('department_id').value,'$departid','$agentid','$agentaddblank','$agenttitleadd','$agenteditmode')</script>";
    //javascript code
          ob_start();
      ?>
            function do_changeDepartment_cb(html) {
				var dest=document.getElementById("agentPanel");
				dest.innerHTML=html;
            }

            function do_changeDepartment(departId,seldepart,selagentid,agentaddblank,agenttitleadd,agenteditmode) {
				document.getElementById("agentPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
				x_changeDepartment(departId, seldepart, selagentid,agentaddblank,agenttitleadd,agenteditmode,do_changeDepartment_cb);
            }	    
      <?

    $js.=ob_get_contents();
    ob_end_clean();


    //------------------------------   SERVER SIDE    --------------------------
      function changeDepartment($departId,$seldepart,$selagentid,$agentaddblank,$agenttitleadd,$agenteditmode) {
      global $pbx, $relBasePath,$pbx;

	      //a control
		  if ($departId=="") return "";
		  //get the configuration option
//error_log("showDEP: ".$departId);
		  $optionArr=$pbx->getUser(0,$departId);
		 //create the HTML options
		  $html.=getDepartAgent($optionArr,$selagentid,$agentaddblank,$agenttitleadd,$agenteditmode);
			return $html;
       }
       sajax_export("changeDepartment");
	   return $contents;
  }
/////////////
  function getDepartAgent($optionArr,$selagentid,$agentaddblank,$agenttitleadd,$agenteditmode) {
    global $pbx,$relBasePath;
	$element = "";
	$selectagent = "";
	$disabled = "disabled=\"disabled\"";
	if ( $agenteditmode == 1)
	{
		$disabled = "";
	}
    if ( $agentaddblank == 1)
    {
        $tId = "";
        $tName = "";
        $element="<option value=\"$tId\" $s>$tName</option>";
	if ( $agenttitleadd != "")
	{
	        $tId = "-100";
       	 $tName = $agenttitleadd;
	        $element.="<option value=\"$tId\" $s>$tName</option>";
	}
    }
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
		//	$element .= "<select size=\"1\" id=\"client[src_detail]\" name=\"client[src_detail]\"  style=\"width: 230px\" >";
			foreach($optionArr as $t) {
			      $tId=$t["agentid"];
			      $tName = $t["pbxname"];
			//	$cat=$v["name"];
			      if ($tId==$selagentid)
			      {
				        $s=" selected=\"selected\" ";
			        }
			        else $s="";
			//	$element .="<option value=\"$cat\" $s>$cat</option>";
			        $element.="<option value=\"$tId\" $s>$tName</option>";
			}  //end of foreach
		}
/*
		else {
			$element .= "<select size=\"1\" id = name=\"client[src_detail]\" name=\"client[src_detail]\"  style=\"width: 230px\">";
		} //end of if is_array
*/
	      $selectagent="<select id=\"staff_id\" name=\"staff_id\"  style=\"width: 180px\" $disabled >".$element."</select>";

//		$element .= "</select>";
	$ret = $selectagent;
    return $selectagent;
  }
///////////// end show department

  function listCategory(&$js,$category=null,$subcategory=null,$model=null) {
    global $pbx,$relBasePath,$cwLanguage;

    //create the technology list
    	$tech=$pbx->getciccategory(0);//getciccampaign("","1");
	foreach($tech as $t) {
      		$tId=$t["id"];
		$tName=$t["name"];

      		if ($tId==$category) {$s=" selected=\"selected\" "; } else $s="";
	      	$html.="<option value=\"$tId\" $s>$tName</option>";
	}    		
    	$html="<td class=\"required\" width=\"13%\">Category</td><td width=\"34%\"> <select id=\"client[category]\" name=\"client[category]\" onchange=\"do_changeCategory(this.value,'$category','$subcategory','$model')\"  style=\"width: 230px\">$html</select></td>";    

    	//------------------------------   CLIENT SIDE    --------------------------
    	ob_start();
?>
<table border="0" width="100%" align="left">
<tr>
        	<?=$html?>
      	<td  class="required" width="13%" >Sub category</td>
   	<td >
       	<div id="subcategoryPanel"></div>
      	</td>
     	</tr>           

	<tr >
   		<td  width="13%" class="required" >Model
      		</td>
   	<td  width="13%" >
       	<div id="modelPanel"></div>
      	</td>
      		<td   width="13%" >
      		</td>
      		<td >

				<?
				$param = "category=$category&subcategory=$subcategory&model=$model&product_2b_id=$product_2b_id";
				?>
				<b><a href="#" onClick="openmypage('<?=$param?>'); return false">Product Information</a></b>

      		</td>

</tr>
</table>
<table border="0" width="100%" align="left">
<tr>
	<td>
	<div id="modelInfoPanel"></div>
	</td>
</tr>
</table>

<?
	$contents=ob_get_contents();
    	ob_end_clean();
    	$contents.="<script>do_changeCategory(document.getElementById('client[category]').value,'$category','$subcategory','$model')</script>\n";
  	$contents.="<script>do_changeSubCategory('$subcategory','$category','$subcategory','$model')</script>\n";
  	$contents.="<script>do_changeModel('$subcategory','$category','$subcategory','$model')</script>\n";
?>

<?
    	//javascript code
       ob_start();
?>
	function do_changeCategory_cb(html) {
		var dest=document.getElementById("subcategoryPanel");
		dest.innerHTML=html;
	}
       function do_changeCategory(categoryid,selcategory,selsubcategory,selmodel) {
		document.getElementById("subcategoryPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
		x_changeCategory(categoryid,selcategory,selsubcategory,selmodel,do_changeCategory_cb);
	}	    

	function do_changeSubCategory_cb(html) {
		var dest=document.getElementById("modelPanel");
		dest.innerHTML=html;
	}
       function do_changeSubCategory(subcategoryId,selcat,selsubcategory,selmodel) {
		document.getElementById("modelPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
		x_changeSubCategory(subcategoryId,selcat,selsubcategory,selmodel,do_changeSubCategory_cb);
	}	    
	function do_changeModel_cb(html) {
		var dest=document.getElementById("modelInfoPanel");
		dest.innerHTML=html;
	}
       function do_changeModel(subcategoryId,selcat,selsubcategory,selmodel) {
		document.getElementById("modelInfoPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
		x_changeModel(subcategoryId,selcat,selsubcategory,selmodel,do_changeModel_cb);
	}	    

<?


	$js.=ob_get_contents();
    	ob_end_clean();

	//------------------------------   SERVER SIDE    --------------------------
      	function changeCategory($categoryid,$selcategory,$selsubcategory,$selmodel) {
      		global $pbx, $relBasePath,$pbx;
	      //a control
		if ($categoryid=="") return "";
		//get the configuration option
		$optionArr=$pbx->getcicsubcategory($categoryid,0);//getciccampaigndetail($campaignId);
		//create the HTML options
		$html.=getSubCategory($optionArr,$categoryid,$selsubcategory,$selmodel);

		return $html;
       }

      	function changeSubCategory($subcategoryId,$selcat,$selsubcategory,$selmodel) {
      		global $pbx, $relBasePath,$pbx;
	      //a control
		if ($subcategoryId=="") return "";
		//get the configuration option
		$optionArr=$pbx->getcicmodel($subcategoryId);//getciccampaigndetail($campaignId);

		//create the HTML options
		$html.=getModel($optionArr,$selcat,$subcategoryId,$selmodel);
		return $html;
       }

      	function changeModel($modelId,$selcat,$selsubcat,$selmodel) {
      		global $pbx, $relBasePath,$pbx;
	      //a control

		//$html	 .= "ROW 1: "."modelID: ".$modelId.", selcat: ".$selcat.", selsubcategory: ".$selsubcat.", selmodel: ".$selmodel;
/*
		$html	 .= "ROW 2: "."modelID: ".$modelId.", selcat: ".$selcat.", selsubcategory: ".$selsubcat.", selmodel: ".$selmodel;
		$html	 .= "ROW 3: "."modelID: ".$modelId.", selcat: ".$selcat.", selsubcategory: ".$selsubcat.", selmodel: ".$selmodel;
		$html	 .= "ROW 4: "."modelID: ".$modelId.", selcat: ".$selcat.", selsubcategory: ".$selsubcat.", selmodel: ".$selmodel;
*/
		$html	 .= "<strong>Current category: ".$selcat."</strong><br>";
		$html	 .= "<strong>Current sub category: ".$selsubcat."</strong><br>";
		$html	 .= "<strong>Current Model: ".$modelId."</strong>";

//		$html	 .= "<strong>ROW 1: "."modelID: ".$modelId.", selcat: ".$selcat.", selsubcategory: ".$selsubcat.", selmodel: ".$selmodel."</strong>";
		
		return $html;
       }

       sajax_export("changeCategory");
       sajax_export("changeSubCategory");
       sajax_export("changeModel");

	return $contents;
  }	/// END listcategory

/////////////

	function getSubCategory($optionArr,$selcat,$selSubCategory,$selmodel) {
		global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
			$element = "<select size=\"1\" id=\"client[subcategory]\" name=\"client[subcategory]\" onchange=\"do_changeSubCategory(this.value,'$selcat','$selSubCategory','$selmodel')\"  style=\"width: 230px\" >";

			foreach($optionArr as $v) {
				//$cat=$v["src_detail"];
				$cat=$v["subcategory"];
				$s="";
				if ($cat==$selSubCategory) 
				{
					$s=" selected=\"selected\" "; 
				} 
				else $s="";
				$element.="<option value=\"$cat\" $s>$cat</option>";
			}  //end of foreach
		}
		else 
		{
			$element = "<select size=\"1\" id = \"client[subcategory]\" name=\"client[subcategory]\"  style=\"width: 230px\">";
		} //end of if is_array
		$element .= "</select>";
		$ret = $element;
    		return $element;
  	}
	function getModel($optionArr,$selCategory,$selSubCategory,$selmodel) {
		global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
			$element = "<select size=\"1\" id=\"client[model]\" name=\"client[model]\"   onchange=\"do_changeModel(this.value,'$selCategory','$selSubCategory','$selmodel')\" style=\"width: 230px\" >";
			foreach($optionArr as $v) {
				//$cat=$v["src_detail"];
				$model=$v["model"];
				$s="";
				if ($model==$selmodel) 
				{
					$s=" selected=\"selected\" "; 
				} 
				else $s="";
				$element.="<option value=\"$model\" $s>$model</option>";
			}  //end of foreach
		}
		else 
		{
			$element = "<select size=\"1\" id = name=\"client[model]\" name=\"client[model]\"  style=\"width: 230px\">";
		} //end of if is_array
		$element .= "</select>";
		$ret = $element;
    		return $element;
  	}

  function BuildReferenceCode($source,$date,$userid,$agentid)
  {
    global $pbx,$relBasePath,$cwLanguage;
	$crn = "";
//	$crn = $pbx->getsourceamount($source,$date,$userid);

    $crninfo=Array(
	     "source" => $source,
            "date" => $date,
            "agentid" => $agentid,
	      "userid" => $userid
	);
	$crn=$pbx->getCRN($crninfo);
	return $crn;
 }	

/////////////
  function showNewCRN($optionArr,$currentcrn,$selSource) {
    global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");


		$element = "<input type=\"text\" id = \"client[contact_ref_no]\" name=\"client[contact_ref_no]\" value=\"$optionArr\" size=\"30\">";

		$element .= "<input type=\"hidden\" name=\"client[currentcrn]\" value=\"$currentcrn\">";
	$ret = $element;
    return $element;
  }

  function listbuyin($id,$addblank) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "1",
            "channel" => "1"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
       }
       else
        {
        		$s="";
        }
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[buyfrom]\" name=\"client[buyfrom]\"  style=\"width: 170px\" >".$html."</select>";
    return $html;
}

  function listclassification($id,$addblank) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "2",
            "channel" => "1"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[classifi]\" name=\"client[classifi]\"  style=\"width: 180px\" >".$html."</select>";
    return $html;
}

  function ListDepartment($id,$addblank,$titleadd,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage;
	$html = "";

	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}

    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
	if ( $titleadd != "")
	{
	        $tId = "-100";
       	 $tName = $titleadd;
	        $html.="<option value=\"$tId\" $s>$tName</option>";
	}
    }
	$qry = "select * from  lts_department where userid='".$_SESSION["cicUserId"]."' order by dept_name asc";
/*
	if ( $id != "")
	{
//		$qry = "select * from  lts_department where dept_id=".$id." order by dept_name asc";
		$qry = "select * from  lts_department  order by dept_name asc";
	}
*/
    $tech=$pbx->QueryData($qry);
    foreach($tech as $t) {
      $tId=$t["dept_id"];
      $tName = $t["dept_name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[department]\" name=\"client[department]\"  style=\"width: 180px\" $disabled >".$html."</select>";
    return $html;
}

  function listAgents($agentid,$addblank,$titleadd,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";

	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}

    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
	if ( $titleadd != "")
	{
	        $tId = "-100";
       	 $tName = $titleadd;
	        $html.="<option value=\"$tId\" $s>$tName</option>";
	}
    }

    $tech=$pbx->getUser($agentid,0);
    foreach($tech as $t) {
      $tId=$t["agentid"];
      $tName = $t["pbxname"];
      if ($tId==$agentid)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"agentid\" name=\"agentid\"  style=\"width: 180px\" $disabled >".$html."</select>";
//    $html="<select id=\"client[agentid]\" name=\"client[agentid]\"  style=\"width: 180px\" >".$html."</select>";
    return $html;
}

//  function listcalltype($id,$addblank,$trantype,$searchid=0) {
  function listcalltype($id,$addblank,$trantype) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
            "optiontype" => "3",
		"userid" => $_SESSION["cicUserId"],
            "channel" => "1"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[calltypeid]\" name=\"client[calltypeid]\"  style=\"width: 180px\" >".$html."</select>";

    return $html;
}
  function listoutboundfeedback($id,$addblank,$trantype) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "1",
            "channel" => "3"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[calltypeid]\" name=\"client[calltypeid]\"  style=\"width: 180px\" >".$html."</select>";

    return $html;
}

  function listsatisfaction($id,$addblank) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "6",
            "channel" => "1"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[satisfactionid]\" name=\"client[satisfactionid]\"  style=\"width: 180px\" >".$html."</select>";
    return $html;
}

  function listresults($id,$addblank) {
    global $pbx,$relBasePath,$cwLanguage;
    //create the classification
	$html = "";
    if ( $addblank == 1)
    {
        $tId = "";
        $tName = "";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "5",
            "channel" => "1"
          );
    $tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$id)
      {
        $s=" selected=\"selected\" ";
        }
        else $s="";
        $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $html="<select id=\"client[callresult]\" name=\"client[callresult]\"  style=\"width: 180px\" >".$html."</select>";
    return $html;
}

  function listContactCode(&$js,$saleid,$subgroupid = null,$typeid = null,$ccsname = null,$contactid = null,$editmode=1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;

	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}

	$html = "";
      $sale=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "oldid" => "",
            "name" => "",
            "enable" => "",
            "update" => ""
          );
	$tech=$pbx->SaleTypeFunction($sale);
	foreach($tech as $t) {
      		$tId=$t["sale_type_id"];
		$tName=$t["name"];
      		if ($tId==$saleid) {$s=" selected=\"selected\" "; } else $s="";
	      	$html.="<option value=\"$tId\" $s>$tName</option>";
	}    		
    	$html="<td class=\"required\" width=\"13%\">Sale</td><td width=\"34%\"> <select id=\"client[saleid]\" name=\"client[saleid]\" onchange=\"do_changeSale(this.value,'$subgroupid','$typeid','$ccsname','$contactid')\"  style=\"width: 230px\" $disabled >$html</select></td>";    

	$htmltype = "";
    $sale=Array(
            "command" => "SEL",
            "id" => "",
            "groupid" => "",	//$subgroup_id
            "oldid" => "",
		"userid" => $_SESSION["cicUserId"],
            "name" => "",
            "enable" => "",
            "update" => ""
          );
	$ret=$pbx->ContactTypeFunction($sale);
	foreach($ret as $t) {
      		$tId=$t["id"];
		$tName=$t["name"];
      		if ($tId==$typeid ) {$s=" selected=\"selected\" "; } else $s="";
	      	$htmltype.="<option value=\"$tId\" $s>$tName</option>";
	}    		
    	$htmltype="<td class=\"required\" width=\"13%\">Type</td><td width=\"34%\"> <select id=\"client[typeid]\" name=\"client[typeid]\"  style=\"width: 230px\">$htmltype</select></td>";    

    	//------------------------------   CLIENT SIDE    --------------------------
    	ob_start();
?>
       <fieldset style="color: #FFFF00; padding: 0; background-color: #CCCCCC">
     	<legend><p align="left"><font size="4" color="#000080"><?=$cwlang["menu"]["contactcode"]?></font></p></legend>
<table border="0" width="100%" align="left">
<tr>
        	<?=$html?>
      	<td  class="required" width="13%" >Sub Group</td>
   	<td >
       	<div id="SubGroupPanel"></div>
      	</td>
</tr>           
<tr>           
<?
	echo $htmltype;
?>
      		<td >
COntact Code
      		</td>

      		<td >
       	<div id="ContactCodePanel"></div>
      		</td>

</tr>          
</tr>
</table>
                	</fieldset>
<?
	$contents=ob_get_contents();
    	ob_end_clean();
    	$contents.="<script>do_changeSale(document.getElementById('client[saleid]').value,'$subgroupid','$typeid','$ccsname','$contactid')</script>\n";
  	$contents.="<script>do_changeSubGroup('$subgroupid','$typeid','$ccsname','$contactid')</script>\n";
    	//javascript code
       ob_start();
?>
	function do_changeSale_cb(html) {
		var dest=document.getElementById("SubGroupPanel");
		dest.innerHTML=html;
	}
       function do_changeSale(saleid,subgroupid,typeid,ccsname,contactid) {
		document.getElementById("SubGroupPanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
		x_changeSale(saleid,subgroupid,typeid,ccsname,contactid,do_changeSale_cb);
	}	    

	function do_changeSubGroup_cb(html) {
		var dest=document.getElementById("ContactCodePanel");
		dest.innerHTML=html;
	}
       function do_changeSubGroup(subgroupid,typeid,ccsname,contactid) {
		document.getElementById("ContactCodePanel").innerHTML="<img src='<?=$relBasePath?>/public/img/ico-progress.gif' > <?=$cwlang["generic"]["working"]?>";
		x_changeSubGroup(subgroupid,typeid,ccsname,contactid,do_changeSubGroup_cb);
	}	    

<?
	$js.=ob_get_contents();
    	ob_end_clean();

	//------------------------------   SERVER SIDE    --------------------------
      	function changeSale($saleid,$subgroupid,$typeid,$ccsname,$contactid) {
      		global $pbx, $relBasePath,$pbx;
	      //a control
		if ($saleid=="") return "";
		//get the configuration option
	      $sale=Array(
	            "command" => "SEL",
       	     "id" => "",
	            "saleid" => $saleid,
		"userid" => $_SESSION["cicUserId"],
       	     "oldgroupid" => "",
	            "name" => "",
       	     "enable" => "",
	            "update" => ""
       	   );
		$optionArr=$pbx->SubGroupFunction($sale);
		//create the HTML options
		$html.=getSubGroup($optionArr,$subgroupid,$typeid,$ccsname,$contactid);
		return $html;
       }
	function getSubGroup($optionArr,$subgroupid,$typeid,$ccsname,$contactid) {
		global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
			$element = "<select size=\"1\" id=\"client[subgroup]\" name=\"client[subgroup]\" onchange=\"do_changeSubGroup(this.value,'$typeid','$ccsname','$contactid')\"  style=\"width: 230px\" >";
			foreach($optionArr as $v) {
				$cat=$v["subgroup_id"];
				$catname=$v["name"];
				$s="";
				if ($cat==$subgroupid) 
				{
					$s=" selected=\"selected\" "; 
				} 
				else $s="";
				$element.="<option value=\"$cat\" $s>$catname</option>";
			}  //end of foreach
		}
		else 
		{
			$element = "<select size=\"1\" id = \"client[subgroup]\" name=\"client[subgroup]\"  style=\"width: 230px\" $disabled >";
		} //end of if is_array
		$element .= "</select>";
		$ret = $element;
    		return $element;
  	}

      	function changeSubGroup($subgroupid,$typeid,$ccsname,$contactid) {
      		global $pbx, $relBasePath,$pbx;
	      //a control
		if ($subgroupid=="") return "";
		//get the configuration option
  		$sale=Array(
		            "command" => "SEL",
		            "id" => "",
		            "groupid" => $subgroupid,
	            "typeid" => "",
       	     "code" => "",
		"userid" => $_SESSION["cicUserId"],
	            "definition" => "",
       	     "oldid" => "",
	            "name" => "",
       	     "enable" => "",
	            "update" => ""
       	);
		$optionArr=$pbx->ContactCodeFunction($sale);

		//create the HTML options
		$html.=getContactCode($optionArr,$subgroupid,$typeid,$ccsname,$contactid);
		return $html;
       }
	function getContactCode($optionArr,$subgroupid,$typeid,$ccsname,$contactid) {
		global $pbx,$relBasePath;
		if ($err=$pbx->getError()) die("Error: $err");
		if (is_array($optionArr)) {
			$element = "<select size=\"1\" id=\"client[contactcode]\" name=\"client[contactcode]\"   style=\"width: 230px\"  $disabled >";
			foreach($optionArr as $v) {
				//$cat=$v["src_detail"];
				$model=$v["contact_id"];
				$modelcode=$v["code"];
				$modelname=$v["name"];
				$s="";
				if ($model==$contactid) 
				{
					$s=" selected=\"selected\" "; 
				} 
				else $s="";
				$model = $model."#".$modelcode;
				$element.="<option value=\"$model\" $s>$modelname</option>";
			}  //end of foreach
		}
		else 
		{
			$element = "<select size=\"1\" id = name=\"client[contactcode]\" name=\"client[contactcode]\"  style=\"width: 230px\" $disabled >";
		} //end of if is_array
		$element .= "</select>";
		$ret = $element;
    		return $element;
  	}
       sajax_export("changeSale");
       sajax_export("changeSubGroup");
	return $contents;
  }	/// END listcategory

/////////////


function writeComboTreeStatus($id,$param,$returnX = "",$parent=0,$level = 0) {
  global $pbx;

	$rettemp = "";
	$qry="SELECT * FROM crm_dial_status WHERE parent='$id' order by reason";
	$arrp=$pbx->querydata($qry);

	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$id=$v["id"];
			$parent=$v["parent"];
			$name=$v["reason"];
	    		//branch or leaf?
	    		$qry1="SELECT * FROM crm_dial_status WHERE `parent`='$id'";
    			$arrp1=$pbx->querydata($qry1);
    			if (count($arrp1) > 0 )
			{
				if ($param==$id) $sel=" selected=\"selected\""; else $sel="";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
				$rettemp.=writeComboTreeStatus($id,$param,$returnX,$parent,$ilevel);
				$nodeFolder=1;
			}
			else
			{
				if ($param==$id) $sel=" selected=\"selected\""; else $sel="";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
				$nodeFolder=1;
			}
		}

	return $returnX.$rettemp;
}

function writeComboTreeStatusX1($id,$param,$returnX = "",$parent=0,$level = 0) {
  global $pbx;

	$rettemp = "";
	$qry="SELECT * FROM tv_codetable WHERE parent='$id' order by reason";
	$arrp=$pbx->querydata($qry);

	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$id=$v["id"];
			$parent=$v["parent"];
			$name=$v["reason"];
	    		//branch or leaf?
	    		$qry1="SELECT * FROM tv_codetable WHERE `parent`='$id'";
    			$arrp1=$pbx->querydata($qry1);
    			if (count($arrp1) > 0 )
			{
				if ($param==$id) $sel=" selected=\"selected\""; else $sel="";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
				$rettemp.=writeComboTreeStatusX1($id,$param,$returnX,$parent,$ilevel);
				$nodeFolder=1;
			}
			else
			{
				if ($param==$id) $sel=" selected=\"selected\""; else $sel="";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
				$nodeFolder=1;
			}
		}

	return $returnX.$rettemp;
}

function writeComboTreePrintStatus($id,$last_status,$param,$returnX = "",$parent=0,$level = 0) {
  global $pbx;

	$rettemp = "";
	$qry="SELECT * FROM crm_dial_status WHERE parent='$id' order by reason";
	$arrp=$pbx->querydata($qry);

	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$id=$v["id"];
			$parent=$v["parent"];
			$code=$v["code"];
			$name=$v["reason"];
	    		//branch or leaf?
	    		$qry1="SELECT * FROM crm_dial_status WHERE `parent`='$id'";
    			$arrp1=$pbx->querydata($qry1);
    			if (count($arrp1) > 0 )
			{
				$rettemp.="<tr>\n";
				$rettemp.="<td>";
				$rettemp.=$code;
				$rettemp.="</td>\n";
				$rettemp.="<td>";
				$rettemp.=$name;
				$rettemp.="</td>\n";
				$rettemp.="</tr>\n";
				$ilevel = $level;
				$ilevel++;
				$rettemp.=writeComboTreePrintStatus($id,$param,$returnX,$parent,$ilevel);
				$nodeFolder=1;
			}
			else
			{
				$rettemp.="<tr>\n";
				$rettemp.="<td>";
				$rettemp.=$code;
				$rettemp.="</td>\n";
				$rettemp.="<td>";
				$rettemp.=$name;
				$rettemp.="</td>\n";
				$rettemp.="</tr>\n";
				$ilevel = $level;
				$ilevel++;
				$nodeFolder=1;
			}
		}

	return $returnX.$rettemp;
}

function writeComboTreeCodeStatus($id,$last_status,$param,$returnX = "",$parent=0,$level = 0) {
  global $pbx;

	$rettemp = "";
	$qry="SELECT * FROM crm_dial_status WHERE parent='$id' order by reason";
	$arrp=$pbx->querydata($qry);

	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$id=$v["id"];
			$parent=$v["parent"];
			$code=$v["code"];
			$name=$v["reason"];
	    		//branch or leaf?
	    		$qry1="SELECT * FROM crm_dial_status WHERE `parent`='$id'";
    			$arrp1=$pbx->querydata($qry1);
    			if (count($arrp1) > 0 )
			{
//				if ($last_status==$id) $sel=" selected=\"selected\""; else $sel="";
				if ($last_status==$code) $sel=" selected=\"selected\""; else $sel="";
//				$rettemp.="<option $sel value=\"".$code."\">".buildtabs($level).$name."</option>\n";
//				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."XXX1:".$idstatus."---".$last_status."</option>\n";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
				$rettemp.=writeComboTreeCodeStatus($id,$param,$returnX,$parent,$ilevel);
				$nodeFolder=1;
			}
			else
			{
//				if ($last_status==$code) $sel=" selected=\"selected\""; else $sel="";
				if ($last_status==$id) $sel=" selected=\"selected\""; else $sel="";
//				$rettemp.="<option $sel value=\"".$code."\">".buildtabs($level).$name."</option>\n";
//				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."XXX2:".$idstatus."---".$last_status."</option>\n";
				$rettemp.="<option $sel value=\"".$id."\">".buildtabs($level).$name."</option>\n";
				$ilevel = $level;
				$ilevel++;
//				$rettemp.=writeComboTreeCodeStatus($id,$param,$returnX,$parent,$ilevel);
				$nodeFolder=1;
			}
		}

	return $returnX.$rettemp;
}

	function buildtabs($level)
	{
		$ret = "";
		for ( $i = 0; $i < $level; $i++)
		{
			$ret .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		return $ret;
	}
 // This function retrieve the already done groups and return the html for displaing it.
  // $type: the group type
  // $js: a return variable that contains the javascript for the ajax function
  // RETURN: this function return the html for display the groups

function writeComboTreeMailCategory($id,$param,$returnX = "",$parent=0,$level = 0) {
  global $pbx;

	$rettemp = "";
	$qry="SELECT * FROM mail_category WHERE parent=".$id." order by name";
	$arrp=$pbx->querydata($qry);
//error_log("XXX000: ".$qry);
	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$idx=$v["id"];
			$parent=$v["parent"];
			$name=$v["name"];
	    		//branch or leaf?
	    		$qry1="SELECT * FROM mail_category WHERE `parent`='".$idx."'";
//error_log("XXX111: ".$qry1);
    			$arrp1=$pbx->querydata($qry1);
    			if (count($arrp1) > 0 )
    			{
    				if ($param==$id) $sel=" selected=\"selected\""; else $sel="";
    				$rettemp.="<option $sel value=\"".$idx."\">".buildtabs($level).$name."</option>\n";
    				$ilevel = $level;
    				$ilevel++;
    				$rettemp.=writeComboTreeMailCategory($idx,$param,$returnX,$parent,$ilevel);
    				$nodeFolder=1;
    			}
    			else
    			{
    				if ($param==$idx) $sel=" selected=\"selected\""; else $sel="";
    				$rettemp.="<option $sel value=\"".$idx."\">".buildtabs($level).$name."</option>\n";
    				$ilevel = $level;
    				$ilevel++;
    				$nodeFolder=1;
    			}
		}
//error_log("XXX: ".$rettemp);
	return $returnX.$rettemp;
}
function WriteFeedbackSelect()
{
		global $cwlang;
	$temp = "<select size=\"1\" name=feedback id=\"feedback\">";

	$text1 = $cwlang["outbound"]["feedback1"];
	$text = "0";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback2"];
	$text = "1";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback3"];
	$text = "2";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback4"];
	$text = "3";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback5"];
	$text = "4";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback6"];
	$text = "5";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback7"];
	$text = "6";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$text1 = $cwlang["outbound"]["feedback8"];
	$text = "7";
	if ($_SESSION["feedback"]==$text) $selected=" selected=\"selected\" "; else $selected="";
	$temp .= "<option value='".$text."'".$selected.">".$text1."</option>";

	$temp .="</select>";
    return $temp;
}

function GetCurrentLink($local,$ex)
{
	$ipaddress= $local;
    $exipaddress =$ex;

    $clientip = $_SERVER["REMOTE_ADDR"];

//echo "LOCAL".$ipaddress." EX:".$exipaddress."ACCC:".$clientip;
    $defineIP=split(".",$ipaddress);
    $accessIP=split(".",$clientip);
    $publicIP=split(".",$exipaddress);

    $ip =$exipaddress;
    if ( ( $defineIP[0] == $accessIP[0]))
    {
        $ip = $ipaddress;
        return $ip;
    }
    if ( ($accessIP[0] == "192") || ($accessIP[0] == "10") || ($accessIP[0] == "127"))
    {
        $ip = $ipaddress;
        return $ip;
    }
    return $ip;
}

  function listChannelDetail(&$js,$channeltype,$custype,$agencyid,$connecttypeid,$resultid,$feedbackid,$contractid,$contractdate,$productid,$connectdetail,$resultdetail,$trannote,$editmode = 1,$editmodereceived = 1,$editmoderesponse = 1,$customerid) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;
    //create the technology list
	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "2",
            "channel" => "1"
          );
  // create the web interface
	$tnameselected = "";
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$custype) {$s=" selected=\"selected\" "; $tnameselected = $tName;} else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}
    $htmlcustype="<select id=\"client[custype]\" name=\"client[custype]\" $disabled >$html </select>";
//////////////////////
	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "1",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$agencyid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlagencyid="<select id=\"client[agencyid]\" name=\"client[agencyid]\" $disabled>$html</select>";

	$html = "";
      $option=Array(
            "command" => "SEL",
            "enable" => "1",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "4",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      $linkurl= $t["linkurl"];

//echo $linkurl;
//$tId .=";".$linkurl;
      $param1= $t["param1"];
      $value1= $t["value1"];
      $param2= $t["param2"];
      $value2= $t["value2"];
      $param3= $t["param3"];
      $value3= $t["value3"];	
      if ($tId==$connecttypeid) {$s=" selected=\"selected\" "; } else $s="";

$tId .=";".$linkurl.";".$param1.";".$value1.";".$param2.";".$value2.";".$param3.";".$value3;
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
	$htmltransacttype="<select id=\"client[transacttype]\" name=\"client[transacttype]\"  onchange=\"do_changeTransactType(this.value,'".$customerid."');\" $disabled >$html</select>";

	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
            "optiontype" => "7",
		"userid" => $_SESSION["cicUserId"],
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["code"];
      $tName = $t["name"];
      if ($tId==$channeltype) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlsource="<select id=\"client[source]\" name=\"client[source]\" $disabled >$html</select>";
	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "5",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$resultid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlresult="<select id=\"client[result]\" name=\"client[result]\" $disabled >$html</select>";

	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
            "optiontype" => "6",
		"userid" => $_SESSION["cicUserId"],
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$feedbackid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlfeedback="<select id=\"client[feedback]\" name=\"client[feedback]\" $disabled >$html</select>";
    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
       <fieldset style="color: rgb(0, 83, 153); padding: 0; background-color: #CCCCCC">
     	<legend><p align="left"><font size="4" color="#000080"><?=$cwlang["personal"]["infortitle"]?></font>
	</p>
	</legend>
	

			<table border="0" width="100%" align="left">

					<tr>
						<td  class="required" width="81"><?=$cwlang["search"]["classification"]?></td>
						<td>
<?
		echo $htmlcustype;
?>
						</td>
						<td  class="required" width="81"><?=$cwlang["predefine"]["agentcy"]?></td>
						<td>
<?
		echo $htmlagencyid;
?>						</td>
                    				<td  class="required" width="81"><?=$cwlang["personal"]["transacttype"]?></td>
                    				<td>
<?
		echo $htmltransacttype;
?>				         

                          <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/document_into.png" onclick="do_changeTransactType(document.getElementById('client[transacttype]').value,'<?=$customerid?>');" />           		
						</td>

					</tr>

					<tr>
						<td  class="required" width="81"><?=$cwlang["camapaign"]["source"]?></td>
						<td>
<?
		echo $htmlsource;
?>
						</td>
						<td  class="required" width="81"><?=$cwlang["search"]["result"]?></td>
						<td>
<?
		echo $htmlresult;
?>						</td>
                    				<td  class="required" width="81"><?=$cwlang["personal"]["feedback"]?></td>
                    				<td>
<?
		echo $htmlfeedback;
?>				                    		
						</td>

					</tr>
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["transaction"]["trandaterequest"]?></td>
                    				<td colspan="5">
			<?
				if ( $editmode == 0 )
				{
					echo "<hr>".$connectdetail."<hr>";
				}
				else
				{
					$oFCKeditor = new CKEditor() ;
					$oFCKeditor->config['height'] = 100;
					$oFCKeditor->config['width'] = '@@screen.width * 0.72';
					$oFCKeditor->editor("FCKeditor1", $connectdetail);
				}
			?>
                    				</td>
					</tr>
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["transaction"]["trandateanswer"]?></td>
                    				<td colspan="5">
			<?
				if ( $editmode == 0 )
				{
					echo "<hr>".$resultdetail."<hr>";
				}
				else
				{
					$oFCKeditor = new CKEditor() ;
					$oFCKeditor->config['height'] = 100;
					$oFCKeditor->config['width'] = '@@screen.width * 0.72';
					$oFCKeditor->editor("FCKeditor2", $resultdetail);
				}
			?>
                    				</td>
					</tr>
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["smschannel"]["note"]?></td>
                    				<td colspan="5">
				                    <input type="text" size="150" maxlength="500" id="client[trannote]" name="client[trannote]" value="<?=$trannote?>" <?=$disabled?> />
       	<div id="subcategoryPanel"></div>

                    				</td>
					</tr>

                </table>
	</fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();

    	$contents.="<script>do_changeTransactType(document.getElementById('client[transacttype]').value,'".$customerid."')</script>\n";


    //javascript code
          ob_start();
      ?>
	function do_changeTransactType_cb(html) {
		var dest=document.getElementById("subcategoryPanel");
		dest.innerHTML=html;
	}
       function do_changeTransactType(trantypeid,cusid) {
		var linkurlarr=trantypeid.split(";");
		funcid = linkurlarr[0];
		linkurl = linkurlarr[1];
		param1 = linkurlarr[2];
		value1 = linkurlarr[3];
		param2 = linkurlarr[4];
		value2 = linkurlarr[5];
		param3 = linkurlarr[6];
		value3 = linkurlarr[7];
		if ( linkurl != "")
		{
			//$tId .=";".$linkurl.";".$param1.";".$value1.";".$param2.";".$value2.";".$param3.";".$value3;
			param = "customerid=" + cusid + "&funcid=" + funcid + "&transactid=" + value1 + "&transactchildid=" + value2 + "&uniqueid=" + value3;
			var mainP = null;
			openTransactInDetail(linkurl,param,linkurl,mainP);
		}
	}	    


      <?
    $js.=ob_get_contents();
    ob_end_clean();
/*
    //------------------------------   SERVER SIDE    --------------------------
      	function changeTransactType($trantypeid) {
      		global $pbx, $relBasePath,$pbx;
	      //a control
		if ($trantypeid=="") return "";
		return $html;
       }

       sajax_export("changeTransactType");
*/
	   return $contents;
  }
  function listChannelDetailData(&$js,$channeltype,$custype,$agencyid,$connecttypeid,$resultid,$feedbackid,$contractid,$contractdate,$productid,$connectdetail,$resultdetail,$note,$editmode = 1,$customerid) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;
    //create the technology list
	$html = "";
	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}


      $option=Array(
            "command" => "SEL",
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "optiontype" => "2",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$custype) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
//    $html="<div class=\"block\"> <select id=\"client[code_source]\" name=\"client[code_source]\" onchange=\"do_changeCodeSource(this.value,'$code_source','$callstage','$contact_ref_no','$start_date_time','$userid')\">$html</select></div>";

    $htmlcustype="<select id=\"client[custype]\" name=\"client[custype]\"  $disabled >$html</select>";
//////////////////////
	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "1",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$agencyid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlagencyid="<select id=\"client[agencyid]\" name=\"client[agencyid]\" $disabled  >$html</select>";
	$html = "";
      $option=Array(
            "command" => "SEL",
            "enable" => "1",
            "id" => "",
		"userid" => $_SESSION["cicUserId"],
            "optiontype" => "4",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      $linkurl= $t["linkurl"];

      $param1= $t["param1"];
      $value1= $t["value1"];
      $param2= $t["param2"];
      $value2= $t["value2"];
      $param3= $t["param3"];
      $value3= $t["value3"];	
      if ($tId==$connecttypeid) {$s=" selected=\"selected\" "; } else $s="";

	$tId .=";".$linkurl.";".$param1.";".$value1.";".$param2.";".$value2.";".$param3.";".$value3;

      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
//	 $htmltransacttype="<select id=\"client[transacttype]\" name=\"client[transacttype]\"  onchange=\"SelectTransactType(this.value,'".$linkurl."');\" $disabled >$html</select>";
	$htmltransacttype="<select id=\"client[transacttype]\" name=\"client[transacttype]\"  onchange=\"do_changeTransactType(this.value,'".$customerid."');\" $disabled >$html</select>";

	$html = "";
      $option=Array(
            "command" => "SEL",
            "id" => "",
            "optiontype" => "7",
		"userid" => $_SESSION["cicUserId"],
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tId=$t["code"];
      $tName = $t["name"];
      if ($tId==$channeltype) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlsource="<select id=\"client[source]\" name=\"client[source]\"  $disabled >$html</select>";
//	$html = "";
	
	$html = "";
      $option=Array(
            "command" => "SEL",
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "optiontype" => "5",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$resultid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlresult="<select id=\"client[result]\" name=\"client[result]\"  $disabled >$html</select>";

	$html = "";
      $option=Array(
            "command" => "SEL",
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "optiontype" => "6",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tName = $t["name"];
      if ($tId==$feedbackid) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $html.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlfeedback="<select id=\"client[feedback]\" name=\"client[feedback]\" $disabled  >$html</select>";
    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
       <fieldset style="color: #FFFF00; padding: 0; background-color: #CCCCCC">
     	<legend><p align="left"><font size="4" color="#000080"><?=$cwlang["personal"]["infortitle"]?></font></p></legend>
			<table border="0" width="100%" align="left">

					<tr>
						<td  class="required" width="81"><?=$cwlang["search"]["classification"]?></td>
						<td>
<?
		echo $htmlcustype;
?>
						</td>
						<td  class="required" width="81"><?=$cwlang["predefine"]["agentcy"]?></td>
						<td>
<?
		echo $htmlagencyid;
?>						</td>
                    				<td  class="required" width="81"><?=$cwlang["personal"]["transacttype"]?></td>
                    				<td>
<?
		echo $htmltransacttype;
?>				                    		
                          <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/document_into.png" onclick="do_changeTransactType(document.getElementById('client[transacttype]').value,'<?=$customerid?>');" />           		

						</td>

					</tr>

					<tr>
						<td  class="required" width="81"><?=$cwlang["camapaign"]["source"]?></td>
						<td>
<?
		echo $htmlsource;
?>
						</td>
						<td  class="required" width="81"><?=$cwlang["search"]["result"]?></td>
						<td>
<?
		echo $htmlresult;
?>						</td>
                    				<td  class="required" width="81"><?=$cwlang["personal"]["feedback"]?></td>
                    				<td>
<?
		echo $htmlfeedback;
?>				                    		
						</td>

					</tr>
                </table>
	</fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();

    	$contents.="<script>do_changeTransactType(document.getElementById('client[transacttype]').value,'".$customerid."')</script>\n";
    //javascript code
          ob_start();
      ?>
       function do_changeTransactType(trantypeid,cusid) {
		var linkurlarr=trantypeid.split(";");
		funcid = linkurlarr[0];
		linkurl = linkurlarr[1];
		param1 = linkurlarr[2];
		value1 = linkurlarr[3];
		param2 = linkurlarr[4];
		value2 = linkurlarr[5];
		param3 = linkurlarr[6];
		value3 = linkurlarr[7];
		if ( linkurl != "")
		{
			//$tId .=";".$linkurl.";".$param1.";".$value1.";".$param2.";".$value2.";".$param3.";".$value3;
			param = "customerid=" + cusid + "&funcid=" + funcid + "&transactid=" + value1 + "&transactchildid=" + value2 + "&uniqueid=" + value3;
			var mainP = null;
			openTransactInDetail(linkurl,param,linkurl,mainP);
		}
	}	    
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }

  function listNextAction(&$js,$department_id,$agentid,$nextactchannel,$nextactsubject,$nextactto,$nextactcontent,$nextactnote,$nextacttime,$nextactattach,$nextactstatus,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;
    //create the technology list
	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}
/*
	$htmldepartment = ListDepartment($department_id,1,"",$editmode);
	$htmlagent = listAgents($agentid,1,"REPLY",$editmode);
*/
	$htmlactchannel = "";
     $option=Array(
            "command" => "SEL",
		"userid" => $_SESSION["cicUserId"],
            "id" => "",
            "optiontype" => "20",
            "channel" => "1"
          );
  // create the web interface
	$tech=$pbx->TransactionOption($option);
    foreach($tech as $t) {
      $tId=$t["id"];
      $tId=$t["code"];
      $tName = $t["name"];
      if ($tId==$nextactchannel) {$s=" selected=\"selected\" "; } else $s="";
      //$s="";
      $htmlactchannel.="<option value=\"$tId\" $s>$tName</option>";
    }
    $htmlactchannel="<select id=\"channelfeedback\" name=\"channelfeedback\"  $disabled >".$htmlactchannel."</select>";

	$transactionstatus = 0;
    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
                    <fieldset  style="color: #0000FF; padding: 0; background-color: #C0C0C0">
                    	<legend><font size="4" color="#800000"><?=$cwlang["personal"]["actiontitle"]?></font></legend>
			<table border="0" width="100%" align="left">
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["transaction"]["closestatus"]?>/<?=$cwlang["transaction"]["openstatus"]?></td>
                    				<td>
					            <input type="radio" id="client[transactionstatus]" name="client[transactionstatus]" value="1" <?if ($nextactstatus==1) echo "checked=\"checked\""; ?> /> <?=$cwlang["transaction"]["closestatus"]?>
					            <input type="radio" id="client[transactionstatus]" name="client[transactionstatus]" value="0" <?if ($nextactstatus!=1) echo "checked=\"checked\""; ?> /> <?=$cwlang["transaction"]["openstatus"]?>

                    				</td>
<!--
                    				<td  class="required" width="81"><?=$cwlang["answer"]["copy"]?></td>
                    				<td  colspan="3">
					            <input type="radio" id="client[copyfrom]" name="client[copyfrom]" value="1" /> <?=$cwlang["action"]["copyrequested"]?>
					            <input type="radio" id="client[copyfrom]" name="client[copyfrom]" value="0" /> <?=$cwlang["answer"]["copyanswer"]?>
                    				</td>
-->
                    				<td  colspan="4">
						</td>
					</tr>

					<tr>
<!--
						<td  class="required" width="81">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["personal"]["department"]?>:</label></dt>
						</td>
						<td>
<?
//		echo $htmldepartment;
?>
						</td>
						<td  class="required" width="81"><?=$cwlang["nr-menu"]["acd-col2"]?></td>
						<td>
<?
//		echo $htmlagent;
?>						</td>
-->

<?
					echo showDepartment($js,$department_id,$agentid,1,"",$editmode,1,"REPLY",1);
?>						
                    				<td  class="required" width="81"><?=$cwlang["channels"]["selectchannel"]?></td>
                    				<td>
<?
		echo $htmlactchannel;
?>				                    		
						</td>

					</tr>

					<tr>
						<td  class="required" width="81"><?=$cwlang["mail"]["templatesubject"]?></td>
						<td>
							<input type="text" size="40" maxlength="500" id="client[nextactsubject]" name="client[nextactsubject]" value="<?=$nextactsubject?>" <?=$disabled?> />
						</td>
						<td  class="required" width="81"><?=$cwlang["channels"]["smsto"]?></td>
						<td>
							<input type="text" size="30" maxlength="500" id="client[nextactto]" name="client[nextactto]" value="<?=$nextactto?>"  <?=$disabled?> />
						</td>
                    				<td  class="required" width="81"><?=	$cwlang["historycall"]["col1"]?></td>
                    				<td>
                    					<input type="text" size="10" maxlength="25" id="nextacttime" name="nextacttime" value="<?=$nextacttime?>" <?=$disabled?>  />
							<a href="javascript:show_calendar('document.mainform.nextacttime',document.mainform.nextacttime.value);"><img src="<?=$relBasePath?>/public/img/<?=$cwLanguage?>/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
						</td>
					</tr>
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["channels"]["content"]?></td>
                    				<td colspan="5">
			<?
/*
				$oFCKeditor = new CKEditor() ;
				$oFCKeditor->config['height'] = 100;
				$oFCKeditor->config['width'] = '@@screen.width * 0.72';
				if ( $editmode == 0 )
				{
					$oFCKeditor->config['readOnly'] = true;
				}
				$oFCKeditor->editor("FCKeditorActContent", $nextactcontent);
*/
				if ( $editmode == 0 )
				{
					echo "<hr>".$nextactcontent."<hr>";
				}
				else
				{
					$oFCKeditor = new CKEditor() ;
					$oFCKeditor->config['height'] = 100;
					$oFCKeditor->config['width'] = '@@screen.width * 0.72';
					$oFCKeditor->editor("FCKeditorActContent", $nextactcontent);
				}

			?>
                    				</td>
					</tr>
					<tr>
                    				<td  class="required" width="81"><?=$cwlang["smschannel"]["note"]?></td>
                    				<td colspan="5">
				                    <input type="text" size="150" maxlength="500" id="client[nextactnote]" name="client[nextactnote]" value="<?=$nextactnote?>" <?=$disabled?> />
                    				</td>
					</tr>

					<tr>
                    				<td  class="required" width="81"><?=$cwlang["channels"]["faxfile"]?></td>
                    				<td colspan="3">
							  <input name="file[]" type="file" <?=$disabled?>/> 
<!--
							<input type="file"   size="80" maxlength="130" name="file" id="file" onChange="jsUpload(this)" >
-->
                    				</td>
<!--
                    				<td  class="required" width="81"><?=$cwlang["smschannel"]["status"]?></td>
-->
                    				<td  colspan="2">
							<?=$nextactattach?><br />
<!--
							<input type="text" name="upload_status" id="upload_status"  value="not uploaded" size="34" disabled>	                    
-->
                    				</td>
					</tr>
			</table>
                	</fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();
//    $contents.="<script>do_changeCodeSource(document.getElementById('client[code_source]').value,'$code_source','$callstage','$contact_ref_no','$start_date_time','$userid')</script>";
    //javascript code
          ob_start();
      ?>
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }

  function ProductInquire(&$js,$manufacturer,$category,$subcategory,$product,$buyingname,$address,$mobile,$email,$orderid,$orderdate,$agencyid,$serial,$code,$reason,$note,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang;
    //create the technology list
	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}
//////////////////////
	$transactionstatus = 0;
    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>
                    <fieldset  style="color: #0000FF; padding: 0; background-color: #C0C0C0">
                    	<legend><font size="4" color="#800000"><?=$cwlang["webproduct"]["productinquire"]?></font></legend>
			<table border="0" width="100%" align="left">
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["manufacturer"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="manufacturer" name="manufacturer" value="<?=$manufacturer?>" <?=$disabled?> />
						</td>
						<td  class="required" width="111"><?=$cwlang["webproduct"]["category"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="category" name="category" value="<?=$category?>" <?=$disabled?> />
						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["subcategory"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="subcategory" name="subcategory" value="<?=$subcategory?>" <?=$disabled?> />
						</td>
						<td  class="required" width="111"><?=$cwlang["webproduct"]["product"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="product" name="product" value="<?=$product?>" <?=$disabled?> />
						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["personal"]["buyingname"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="buyingname" name="buyingname" value="<?=$buyingname?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('NAME',document.getElementById('buyingname').value);" />

						</td>
						<td  class="required" width="111"><?=$cwlang["personal"]["address"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="address" name="address" value="<?=$address?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('ADDRESS',document.getElementById('address').value);" />

						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["personal"]["mobile"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="mobile" name="mobile" value="<?=$mobile?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('mobile',document.getElementById('mobile').value);" />

						</td>
						<td  class="required" width="111"><?=$cwlang["personal"]["email"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="email" name="email" value="<?=$email?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('EMAIL',document.getElementById('email').value);" />

						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["orderid"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="orderid" name="orderid" value="<?=$orderid?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_ProductSearch('ORDERID',document.getElementById('orderid').value);" />

						</td>
						<td  class="required" width="111"><?=$cwlang["webproduct"]["orderdate"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="orderdate" name="orderdate" value="<?=$orderdate?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_ProductSearch('ORDERDATE',document.getElementById('orderdate').value);" />

						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["agency"]?>:</label></dt>
						</td>
						<td>
						<?
//							echo $htmlagencyid;
						?>
							<input type="text" size="60" maxlength="200" id="agencyid" name="agencyid" value="<?=$agencyid?>" <?=$disabled?> />
						</td>
						<td  class="required" width="111"><?=$cwlang["webproduct"]["serial"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="serial" name="serial" value="<?=$serial?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_ProductSearch('SERIAL',document.getElementById('serial').value);" />

						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["code"]?>:</label></dt>
						</td>
						<td>
							<input type="text" size="60" maxlength="200" id="code" name="code" value="<?=$code?>" <?=$disabled?> />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_ProductSearch('CODE',document.getElementById('code').value);" />

						</td>
						<td  class="required" width="111"><?=$cwlang["webproduct"]["reason"]?></td>
						<td>
							<input type="text" size="60" maxlength="200" id="reason" name="reason" value="<?=$reason?>" <?=$disabled?> />
						</td>
					</tr>
					<tr>
						<td  class="required" width="111">
				                <dt class="required"><label for="client[voffice]"><?=$cwlang["webproduct"]["note"]?>:</label></dt>
						</td>
						<td  colspan="3">
							<input type="text" size="140" maxlength="700" id="note" name="note" value="<?=$note?>" <?=$disabled?> />
						</td>
					</tr>
                    				
			</table>
                	</fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();
    //javascript code
          ob_start();
      ?>
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }

  function listCustomerDetail(&$js,$customerdata,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang,$ipaddress,$subdirprefix;
    //create the technology list

			$customerid=$customerdata["id"];
			$clientid=$customerdata["clientid"];
			$firstname=$customerdata["firstname"];
			$lastname=$customerdata["lastname"];
			$firstname=trim($firstname);
			$lastname=trim($lastname);			
			$fullname = $firstname." ".$lastname;
		  	$gender=$customerdata["gender"];
			$MaritalStatus=$customerdata["MaritalStatus"];
			$Dateofbirth=$customerdata["Dateofbirth"];
			$IdentityCard=$customerdata["IdentityCard"];
			$IdentityCarddate=$customerdata["IdentityCarddate"];
			$IdentityCardplace=$customerdata["IdentityCardplace"];
		  	$company=$customerdata["company"];
			$userid= $_SESSION["cicUserId"];
			$fax=$customerdata["fax"];
		  	$phone=$customerdata["phone"];
			$office=$customerdata["office"];
			$mobile=$customerdata["mobile"];
			$email=$customerdata["email"];
			$nickchat=$customerdata["nickchat"];
			$facebook=$customerdata["facebook"];
			$other_phone=$customerdata["other_phone"];
			$position=$customerdata["position"];
			$email=$customerdata["email"];
			$address_id=$customerdata["address_id"];
			$meta_tag=$customerdata["meta_tag"];
			$ip=$customerdata["ip"];
			$cretime=$customerdata["cretime"];
			$pic_image=$customerdata["pic_image"];
//			$customeridx=$customerdata["customerid"];
			$creby=$customerdata["creby"];

			$groupid=$customerdata["groupid"];
			$import_key=$customerdata["import_key"];
			$status=$customerdata["status"];
			$inbound=$customerdata["inbound"];
			$Regional=$customerdata["Regional"];
			$homenumber=$customerdata["homenumber"];
			$groupid=$customerdata["groupid"];
			$age=$customerdata["age"];
			$education=$customerdata["education"];
			$contactgroup=$customerdata["contactgroup"];
			$incomelevel=$customerdata["incomelevel"];
			$cusnote=$customerdata["note"];
			$last_update=$customerdata["last_update"];
			$newsletter=$customerdata["newsletter"];
			$address=$customerdata["address"];
/*
//$education=$customerid;
//print_r($customerdata);
	if ( $customerid != "")
	{
	////// getCicAddress 	//addressid
		$entry=Array('customerid'=>$customerid);
		    $ret=$pbx->getCicAddress ($entry);
		    if ($err=$pbx->getError()) die("Error: $err");
			$v=$ret[0];
			$address_id=$v["address_id"];
			$clientid=$v["clientid"];
			$address_type=$v["address_type"];
			$address=$v["address"];
			$district=$v["district"];
			$city=$v["city"];
			$postcode=$v["postcode"];
			$country_id=$v["country_id"];
			$zone_id=$v["zone_id"];
			$lat=$v["lat"];
			$lng=$v["lng"];
			$last_update=$v["last_update"];
	}
*/
	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}

    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>

                    <fieldset  style="color: #0000FF; padding: 0; background-color: #C0C0C0">
                    	<legend><font size="4" color="#800000"><?=$cwlang["personal"]["title"]?></font></legend>

			<DIV style="VERTICAL-ALIGN: top; OVERFLOW: auto;">
                    		<table border="0" width="100%" align="left">
                    			<tr>
                    				<td class="required"><?=$cwlang["accountinfo"]["gender"]?></td>
                    				<td>
                    					<input type="text" size="27" maxlength="50" id="gender" name="gender" value="<?=$gender?>" />
                    				</td>
                    				<td  class="required" width="81"><?=$cwlang["personal"]["name"]?></td>
						<td colspan="1">
                    					<input type="text" size="30" maxlength="150" id="fullname" name="fullname" value="<?=$fullname?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('FULLNAME',document.getElementById('fullname').value);" />
							<?
							}
							?>
						</td>
                    				<td  class="required" width="81"><?=$cwlang["edit-contact"]["MaritalStatus"]?></td>                    					
						<td>
                    					<input type="text" size="27" maxlength="150" id="MaritalStatus" name="MaritalStatus" value="<?=$MaritalStatus?>" />
						</td>
                    			</tr>
					<tr>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["IdentityCard"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="IdentityCard" name="IdentityCard" value="<?=$IdentityCard?>" />
						</td>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["Dateofbirth"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="Dateofbirth" name="Dateofbirth" value="<?=$Dateofbirth?>" />
						</td>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["Placeofbirth"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="IdentityCardplace" name="IdentityCardplace" value="<?=$IdentityCardplace?>" />
						</td>
					</tr>
					<tr>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["company"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="company" name="company" value="<?=$company?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('COMPANY',document.getElementById('company').value);" />
							<?
							}
							?>

						</td>
						<td  class="required" width="81"><?=$cwlang["accountinfo"]["chucvu"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="position" name="position" value="<?=$position?>" />
						</td>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["education"]?></td>
						<td>
                    				<input type="text"  size="27" maxlength="50"  id="education" name="education" value="<?=$education?>" />
						</td>
					</tr>

                    			<tr>
                    				<td  class="required" width="81">
							<a href="javascript:sip(document.mainform.phone.value);"><?=$cwlang["personal"]["home"]?></a>
							</td>
						<td>
                    					<input type="text"  size="20" maxlength="50"  id="phone" name="phone" value="<?=$phone?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('TEL',document.getElementById('phone').value);" />
							<?
							}
							?>
                    				</td>
                    				<td  class="required" width="81">
							<a href="javascript:sip(document.mainform.office.value);"><?=$cwlang["personal"]["mobile"]?></a>
						</td>
                    				<td>
                    				<input type="text"  size="20" maxlength="50"  id="office" name="office" value="<?=$office?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('TEL',document.getElementById('office').value);" />
							<?
							}
							?>

						</td>
						<td  class="required" width="81">
							<a href="javascript:sip(document.mainform.mobile.value);"><?=$cwlang["personal"]["office"]?></a>
						</td>
						<td>
                    				<input type="text"  size="20" maxlength="50"  id="mobile" name="mobile" value="<?=$mobile?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('TEL',document.getElementById('mobile').value);" />
							<?
							}
							?>

						</td>
                    			</tr>
                    			<tr>
						<td  class="required" width="81">
							<a href="javascript:sip(document.mainform.other_phone.value);"><?=$cwlang["edit-contact"]["other_phone"]?></a>
						</td>
						<td  class="required" width="81">
	                    				<input type="text"  size="20" maxlength="50"  id="other_phone" name="other_phone" value="<?=$other_phone?>" />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/icon_phone-call_16x16.png"  href="#" onclick="ActChannel(document.getElementById('other_phone').value,'TEL');" />
						</td>
						<td  class="required" width="81"><?=$cwlang["personal"]["fax"]?></td>
						<td>
       	             				<input type="text"  size="20" maxlength="50"  id="fax" name="fax" value="<?=$fax?>" />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/fax.gif"  href="#" onclick="ActChannel(document.getElementById('fax').value,'FAX');" />

                    				</td>
                    				<td    class="required" width="81"><?=$cwlang["personal"]["email"]?>
						</td>
                    				<td>
                    					<input type="text"  size="20" maxlength="50"  id="email" name="email" value="<?=$email?>" />
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/sendmail.png"  href="#" onclick="ActChannel(document.getElementById('email').value,'EML');" />

						</td>

                    			</tr>
                  			<tr>
						<td  class="required" width="81"><?=$cwlang["personal"]["nick"]?></td>
						<td>	
                    					<input type="text"  size="27" maxlength="50" id="nickchat" name="nickchat" value="<?=$nickchat?>" />
						</td>	
                    				<td  class="required" width="81"><?=$cwlang["edit-contact"]["facebook"]?></td>
                    				<td>
                    				<input type="text"  size="27" maxlength="50"  id="facebook" name="facebook" value="<?=$facebook?>" />
						</td>

						<td  class="required" width="81"><?=$cwlang["edit-contact"]["age"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="age" name="age" value="<?=$age?>" />
						</td>
                    			</tr>
					<tr>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["IncomeLevel"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="incomelevel" name="incomelevel" value="<?=$incomelevel?>" />
						</td>
<!--
						<td  class="required" width="81"><?=$cwlang["personal"]["groupid"]?></td>
						<td>
                    				<input type="text"  size="27" maxlength="50"  id="groupid" name="groupid" value="<?=$groupid?>" />
						</td>
-->
                    				<td    class="required" width="81"><?=$cwlang["personal"]["note"]?></td>
						</td>
						<td  colspan="3" class="required" width="81">
       	             				<input type="text"  size="105" maxlength="50"  id="cusnote" name="cusnote" value="<?=$cusnote?>" />
						</td>
					</tr>


					<tr>
                    				<td    class="required" width="81"><?=$cwlang["personal"]["groupid"]?></td>
                    				<td>
                    					<input type="text"  size="27" maxlength="50"  id="groupid" name="groupid" value="<?=$groupid?>" />
						</td>
                    				<td  class="required" width="81"><?=$cwlang["customerinfo"]["meta_tag"]?></td>
                    				<td colspan="3">
                    					<input type="text" size="105" maxlength="150" id="meta_tag" name="meta_tag" value="<?=$meta_tag?>" />
                    				</td>
                    			</tr>
<?
	print_r($address);
?>
<!--
                    			<tr>
						<td  class="required" width="81"><?=$cwlang["edit-contact"]["address"]?></td>
						<td>
                    				<input type="text"  size="27" maxlength="50"  id="address" name="address" value="<?=$address?>" />
                    				</td>
                    				<td  class="required" width="81"><?=$cwlang["edit-contact"]["district"]?></td>
                    				<td>
                    				<input type="text"  size="27" maxlength="50"  id="district" name="district" value="<?=$district?>" />
						</td>
						<td  class="required" width="81"><?=$cwlang["personal"]["city"]?></td>
						<td>
                    				<input type="text"  size="27" maxlength="50"  id="city" name="city" value="<?=$city?>" />
						</td>
                    			</tr>
					<tr>
						<td  class="required" width="81"><?=$cwlang["customerinfo"]["postcode"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="postcode" name="postcode" value="<?=$postcode?>" />
						</td>
						<td  class="required" width="81"><?=$cwlang["accountinfo"]["lat"]?></td>
						<td>
						<input type="text" size="27" maxlength="50" id="lat" name="lat" value="<?=$lat?>" />
						</td>
                    				<td  class="required" width="81"><?=$cwlang["edit-contact"]["lng"]?></td>
                    				<td>
                    					<input type="text"  size="27" maxlength="50"  id="lng" name="lng" value="<?=$lng?>" />
							<?
							if ( $editmode == 1 )
							{
							?>

							      <a href="#"><img src="<?=$relBasePath?>/public/img/download.gif" alt=<?=$cwlang["form"]["savebutton"]?> onclick=" document.getElementById('alert').innerHTML=''; xajax_roll(); xajax_CustomerProcessFormData(xajax.getFormValues('customerform')); "/></a>
							<?
							}
							?>

						</td>
					</tr>

-->
                    		</table>
			</div>
                	</fieldset>

    <?
    $contents=ob_get_contents();
    ob_end_clean();
    //javascript code
          ob_start();
      ?>
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }

  function EditContact(&$js,$companyid,$customerid,$editmode = 1) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang,$ipaddress,$subdirprefix,$cicUserInfo;
    //create the technology list

	$accountmode = 0;

	if ( ( $companyid == "") || ( $companyid == "-1") )
	{
		//load customer
		    $ret=$pbx->getcustomerinfo($customerid);
		    if ($err=$pbx->getError()) die("Error: $err");
		    $v=$ret[0];
		    $cusid=$v["id"];
		    $cusfirstname=$v["firstname"];
		    $cuslastname=$v["lastname"];
		    $cusgender=$v["gender"];
		    $Dateofbirth=$v["Dateofbirth"];
		    $cusphone=$v["phone"];
		    $IdentityCard=$v["IdentityCard"];
		    $homeaddress=$v["homeaddress"];
		    $businessaddress=$v["businessaddress"];
		    $addr_others=$v["others"];
		    $cusmobile=$v["mobile"];
		    $cusfax=$v["fax"];
		    $cuscretime=$v["cretime"];
		    $cuscreby=$v["creby"];
		    $cuscustomerid=$v["customerid"];
			$cusoldcustomerid = $customerid;
		    $cusgroupid=$v["groupid"];
		    $cusemail=$v["email"];
		    $cusoffice=$v["office"];
		    $cusother_phone=$v["other_phone"];
		    $cusMaritalStatus=$v["MaritalStatus"];
		    $cusprovince=$v["province"];
		    $cusRegional=$v["Regional"];
		    $cusIncomeLevel=$v["IncomeLevel"];
		    $cusimport_key=$v["import_key"];
	    //title
	    $title=$cwlang["contact"]["editcontact"]." &lt;".$cusfirstname." ".$cuslastname."&gt;";

	}
	else if ( ( $customerid == "") || ( $customerid == "-1") )
	{
		$accountmode = 1;
		//load company
			$entry=Array('customerid'=>$companyid,'contacttype'=>"ACCOUNT","userid" => GetCurrentUserID($cicUserInfo["privileged"],$_SESSION['cicUserId']));

		    $ret=$pbx->getCic($entry);
		    if ($err=$pbx->getError()) die("Error: $err");
		    $v=$ret[0];
		    $cusid=$v["id"];
		    $customer_id=$v["customer_id"];
		    $name=$v["name"];
		    $phone=$v["phone"];
		    $fax=$v["fax"];
//		    $address=$v["address"];
			$address=$v["address"][0]["address"];
		    $email=$v["email"];
		    $web=$v["web"];
		    $source=$v["source"];
		    $category=$v["category"];
		    $ipcode=$v["ipcode"];
		    $last_update=$v["last_update"];
		    $cretime=$v["cretime"];
		    $note=$v["note"];
		    $creby=$v["creby"];
		    $import_key=$v["import_key"];
//userid,company_id,customer_id,name,phone,fax,address,email,web,source,category,ipcode,last_update,note,cretime,creby,import_key
	    //title
		$title=$cwlang["contact"]["editcontact"]." &lt;".$name."&gt;";

	}

	$disabled = "disabled=\"disabled\"";
	if ( $editmode == 1)
	{
		$disabled = "";
	}

    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>

<?
		if ( $accountmode == 0 )
		{
?>

  <div id="basic_form">
    <form id="mainform"  name="mainform"  action="<?=$PHP_SELF?>" method="post">
	<input type="hidden" name="client[oldid]" id="client[oldid]"  value=<?=$id;?>>
	<input type="hidden" name="client[import_key]" id="client[import_key]"  value=<?=$cusimport_key;?>>
 
      <div class="block">

                <table>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["contact"]["firstname"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[firstname]" name="client[firstname]" value="<?=$cusfirstname?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["contact"]["lastname"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[lastname]" name="client[lastname]" value="<?=$cuslastname?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["gender"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[gender]" name="client[gender]" value="<?=$cusgender?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["MaritalStatus"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[MaritalStatus]" name="client[MaritalStatus]" value="<?=$cusMaritalStatus?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["Dateofbirth"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[Dateofbirth]" name="client[Dateofbirth]" value="<?=$Dateofbirth?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["IdentityCard"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[IdentityCard]" name="client[IdentityCard]" value="<?=$IdentityCard?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["phone"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[phone]" name="client[phone]" value="<?=$cusphone?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["mobile"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[mobile]" name="client[mobile]" value="<?=$cusmobile?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["office"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[office]" name="client[office]" value="<?=$cusoffice?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["other_phone"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[other_phone]" name="client[other_phone]" value="<?=$cusother_phone?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["fax"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[fax]" name="client[fax]" value="<?=$cusfax?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["email"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[email]" name="client[email]" value="<?=$cusemail?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["homeaddress"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[homeaddress]" name="client[homeaddress]" value="<?=$homeaddress?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["businessaddress"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[businessaddress]" name="client[businessaddress]" value="<?=$businessaddress?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["others"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[others]" name="client[others]" value="<?=$addr_others?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["province"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[province]" name="client[province]" value="<?=$cusprovince?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["Regional"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[Regional]" name="client[Regional]" value="<?=$cusRegional?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["IncomeLevel"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[IncomeLevel]" name="client[IncomeLevel]" value="<?=$cusIncomeLevel?>" /></td>
                  </tr>

		</table>
            </div>
<?
	if ( $editmode == 1)
	{
?>
    <div class="action">
      <?if ($_GET["id"]!="") {?> <input type="hidden" id="client[oldid]" name="client[oldid]" value="<?=$id?>" />

	<?}?>
      <a href="#"><img src="<?=$relBasePath?>/public/img/<?=$cwLanguage?>/b-cancel.gif" alt=<?=$cwlang["form"]["cancelbutton"]?> onclick="location.href='../'; return false;"/>
      <a href="#"><img src="<?=$relBasePath?>/public/img/<?=$cwLanguage?>/b-save.gif" alt=<?=$cwlang["form"]["savebutton"]?> onclick=" document.getElementById('alert').innerHTML=''; xajax_roll(); xajax_processFormData(xajax.getFormValues('mainform')); "/></a>
    </div>
<?
	}
?>
  </form>
 </div>

<?
		}
		else if ( $accountmode == 1 )
		{
?>
  <div id="basic_form">
    <form id="mainform"  name="mainform"  action="<?=$PHP_SELF?>" method="post">
 
      <div class="block">

                <table>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["edit-contact"]["company"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[name]" name="client[name]" value="<?=$name?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["crmcontact"]["phone"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[phone]" name="client[phone]" value="<?=$phone?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["personal"]["fax"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[fax]" name="client[fax]" value="<?=$fax?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["personal"]["address"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[address]" name="client[address]" value="<?=$address?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["crmcontact"]["email"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[email]" name="client[email]" value="<?=$email?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?>Web</label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[web]" name="client[web]" value="<?=$web?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["source"]["col3"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[source]" name="client[source]" value="<?=$source?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cat"]["category"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[category]" name="client[category]" value="<?=$category?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["personal"]["productcode"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[ipcode]" name="client[ipcode]" value="<?=$ipcode?>" /></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["personal"]["note"]?></label></td>
                    <td><input type="text" size="100" maxlength="100" id="client[note]" name="client[note]" value="<?=$note?>" /></td>
                  </tr>

                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["crm"]["dial-cus-info"]?></label></td>
<?
//userid,company_id,customer_id,name,phone,fax,address,email,web,source,category,ipcode,last_update,note,cretime,creby,import_key

			$info = $cwlang["edit-contact"]["cretime"].": ".$cretime."<br>";
			$info .= $cwlang["edit-contact"]["creby"].": ".$creby."<br>";
			$info .= $cwlang["edit-contact"]["import_key"].": ".$import_key."<br>";
			$info .= $cwlang["cdr_dial_detail"]["last_dial"].": ".$last_update."<br>";

?>			
                    <td><strong><?=$info?>"</strong></td>
                  </tr>
		</table>
            </div>
<?
	if ( $editmode == 1)
	{
?>
    <div class="action">
      <?if ($_GET["id"]!="") {?> <input type="hidden" id="client[oldid]" name="client[oldid]" value="<?=$id?>" />
	<?}?>
      <a href="#"><img src="<?=$relBasePath?>/public/img/<?=$cwLanguage?>/b-cancel.gif" alt=<?=$cwlang["form"]["cancelbutton"]?> onclick="location.href='../'; return false;"/>
      <a href="#"><img src="<?=$relBasePath?>/public/img/<?=$cwLanguage?>/b-save.gif" alt=<?=$cwlang["form"]["savebutton"]?> onclick=" document.getElementById('alert').innerHTML=''; xajax_roll(); xajax_processFormData(xajax.getFormValues('mainform')); "/></a>
    </div>
<?
	}
?>
  </form>
 </div>
<?
		}
?>
<!--
						<input type="text" size="27" maxlength="50" id="company" name="company" value="<?=$company?>" />
							<?
							if ( $editmode == 1 )
							{
							?>
			                            <img style="cursor:pointer;" src="<?=$relBasePath?>public/img/search.gif" onclick="xajax_CustomerSearch('COMPANY',document.getElementById('company').value);" />
							<?
							}
							?>
-->
    <?
    $contents=ob_get_contents();
    ob_end_clean();
    //javascript code
          ob_start();
      ?>
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }

  function PBXInfodetail(&$js,$uniqueid,$pbxid) {
    global $pbx,$relBasePath,$cwLanguage,$cwlang,$ipaddress,$exipaddress,$subdirprefix,$cicUserInfo;
    //create the technology list

	if ( $uniqueid == "") $uniqueid = "-1";
	$entry=Array('userid'=>$pbxid,'reporttype'=>"CDR1",'destination'=>"",'selectextension'=>"",'exten'=>"",'accountcode'=>"",'calltype'=>"",'uniqueid'=>$uniqueid);
//print_r($entry);
	$ret=$pbx->UniversalReport($entry);

		//load customer
//	    if ($err=$pbx->getError()) die("Error: $err");
	    $arr=$ret[0];
//print_r($arr);
		$uniqueid = $arr["uniqueid"];
		$src = $arr["src"];
		$dst = $arr["dst"];
		$userid= $arr["userid"];
		$username= $arr["username"];
		$userdelegate= $arr["userdelegate"];
		$userfield= $arr["userfield"];
		$accountcode= $arr["accountcode"];
		$dcontext= $arr["dcontext"];
		$clid= $arr["clid"];
		$channel= $arr["channel"];
		$lastdata= $arr["lastdata"];
		$lastapp= $arr["lastapp"];
		$calldate= $arr["calldate"];
		$answer= $arr["answer"];
		$end= $arr["end"];
		$duration= $arr["duration"];
		$disposition= $arr["disposition"];
		$unitprice= $arr["unitprice"];
		$servicename= $arr["servicename"];
		$queue= $arr["queue"];
		$telcotype= $arr["telcotype"];
	$agent = $arr["agent"];
	$monitor_date = $arr["calldate"];
	$dstchannel = $arr["dstchannel"];
	$monitorfile = $arr["monitorfile"];
	$telcocode = $arr["telcocode"];
	$longdistance= $arr["longdistance"];
	$status = $arr["status"];

   	    $wavefile = str_replace("/usr/local/tvcti/spool/monitor/", "",$monitorfile);
	$soundfile = "/monitor/".$wavefile;
	$remoteip = $_SERVER["REMOTE_ADDR"];
	$remote3 = substr($remoteip,0,3);
	$host3 = substr($ipaddress,0,3);
	$ipaddr = $ipaddress;
	if ( $remote3 != $host3)
	{
		$ipaddr = $exipaddress;
	}
	//echo "Playing..<a href=\"".$soundfile."\"</a>".basename($soundfile);

    //------------------------------   CLIENT SIDE    --------------------------
    ob_start();
    ?>

  <div id="basic_form">
    <form id="mainform"  name="mainform"  action="<?=$PHP_SELF?>" method="post">
 
      <div class="block">
                <table>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["crm_dial_detail"]["pbx_title"]?></label></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["monitor"]["uniqueid"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$uniqueid?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["monitor"]["called"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$dst?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["monitor"]["caller"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$src?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print9"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$dstchannel?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print8"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$channel?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print15"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$disposition?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print10"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$calldate?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print11"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$answer?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print12"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$end?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["cdr"]["print13"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$duration?></label></td>
                  </tr>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?=$cwlang["crm_dial_detail"]["agent_title"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?></label></td>
                  </tr>
<?
		if ( $wavefile != "")
		{
?>
                  <tr>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?= $cwlang["monitor"]["file"]?></label></td>
                    <td class="required" style=''><label for="name"><?=$cwlang["edit-contact"][""]?><?="<a  target=\"_blank\" href=\"".$soundfile."\"</a>".basename($soundfile)?></label></td>
                  </tr>
<?
		}
?>
		</table>

            </div>
  </form>
 </div>

    <?
    $contents=ob_get_contents();
    ob_end_clean();
    //javascript code
          ob_start();
      ?>
      <?
    $js.=ob_get_contents();
    ob_end_clean();
    //------------------------------   SERVER SIDE    --------------------------
	   return $contents;
  }


	function fileexist($wavefile)
	{
		    $file=$wavefile;
		    if ( filesize($file) > 0 )
			return 1;
	    return 0;
	}
/*
		function Encrypt($txt,$key)
		{
				srand((double)microtime()*1000000);
				$encrypt_key = md5(rand(0,32000));
				$ctr=0;
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++)
				{
				if ($ctr==strlen($encrypt_key)) $ctr=0;
				$tmp.= substr($encrypt_key,$ctr,1) .
				(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
				$ctr++;
				}
				return base64_encode($this->keyED($tmp,$key));
		}

		function Decrypt($txt,$key)
		{
				$txt = $this->keyED(base64_decode($txt),$key);
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++){
						$md5 = substr($txt,$i,1);
						$i++;
						$tmp.= (substr($txt,$i,1) ^ $md5);
				}
				return $tmp;
		} 
	function vtiger_encrypt_password($userid,$user_password, $crypt_type='')
	{
		// encrypt the password.
		$salt = substr($userid, 0, 2);

		if($crypt_type == '') {
			// Try to get the crypt_type which is in database for the user
//			$crypt_type = $this->get_user_crypt_type();
			$crypt_type = "MD5";
		}
		// For more details on salt format look at: http://in.php.net/crypt
		if($crypt_type == 'MD5') {
			$salt = '$1$' . $salt . '$';
		} else if($crypt_type == 'BLOWFISH') {
			$salt = '$2$' . $salt . '$';
		}

		$encrypted_password = crypt($user_password, $salt);	

		return $encrypted_password;

	} 
*/
	function OnelotusLang2Product($direction,$lang)
	{
		if ( $direction == 1 )
		{
			if ( $lang == "vn")
				return 2;
			return 1;
		}
		if ( $direction == 0 )
		{
			if ( $lang == "2")
				return "vn";
			return "en";
		}
		return 2;
	} 
	function EnableStage2Image($enablestage)
	{
		global $relBasePath,$cwlang;

		$ret = "<img src=\"$relBasePath/public/img/accept.png\"  alt=\"".$cwlang["contactcode"]["enable"]."\"  title=\"".$cwlang["contactcode"]["enable"]."\" />";
		if ( $enablestage == 0 )
		{
			$ret = "<img src=\"$relBasePath/public/img/reject.png\"  alt=\"".$cwlang["contactcode"]["enable"]."\"  title=\"".$cwlang["contactcode"]["enable"]."\" />";
		}
		return $ret;
	} 
	function FullName2FirstName($full)
	{
		$first = "";
		
		$names = split(" ",$full);
		$count = count($names) - 1;
		for ( $i = 0 ; $i < $count; $i++)
		{
			$first .= $names[$i];
		}
		return $first;
	}
	function FullName2LastName($full)
	{
		$first = "";
		
		$names = split(" ",$full);
		$count = count($names) - 1;
		$first = $names[$count];
		return $first;
	}
	function TransactUniqueID($userid,$accountid)
	{
//		$dateuniqueid = date("YmdHms");	
		$dateuniqueid = time ().microtime(true);	
		return $userid.$accountid.$dateuniqueid;
	}
	function WorkflowStage($stage)
	{
		global $cwlang;
		$test = $cwlang["smschannel"]["status0"];
		if ( $stage == TRANSACTION_CREATE)	$test = $cwlang["smschannel"]["status0"];
		else if ( $stage == TRANSACTION_AGENT_PROCESS)	$test = $cwlang["smschannel"]["status7"];
		else if ( $stage ==  TRANSACTION_APPROVAL)	$test = $cwlang["smschannel"]["status8"];
		else if ( $stage == TRANSACTION_SENDING)	$test = $cwlang["transaction"]["sending"];
		else if ( $stage == TRANSACTION_COMPLETED)	$test = $cwlang["smschannel"]["status9"];
		else if ( $stage == TRANSACTION_CANCEL)	$test = $cwlang["smschannel"]["status10"];
		else if ( $stage == TRANSACTION_FAIL)	$test = $cwlang["smschannel"]["status11"];
		return $test;
	}

	function WorkflowCommandText($stage)
	{
		global $cwlang;
		$test = $cwlang["workflow"]["notify"];
		if ( $stage == COMMAND_NOTIFY)	$test = $cwlang["workflow"]["notify"];
		else if ( $stage == COMMAND_COPY)	$test = $cwlang["workflow"]["copy"];
		else if ( $stage ==  COMMAND_MOVE)	$test = $cwlang["workflow"]["move"];
		else if ( $stage == COMMAND_DEL)	$test = $cwlang["workflow"]["remove"];
		else if ( $stage == COMMAND_REPLY_EMAIL)	$test = $cwlang["workflowfinish"]["notifyemail"];
		else if ( $stage ==  COMMAND_NOTIFY_LEADER)	$test = $cwlang["workflowfinish"]["notifyint"];

		return $test;
	}

function GetCurrentUserID($type,$userid)
{
//	if ( ( $type == USER_SUPERUSER ) || ( $type == USER_CONFIGURATOR ) )
//	if ( ( $type == 3 ) || ( $type == 4 ) )
	if (  ( $type == 4 ) )
	{
	//	return "-1";
		return "";
	}
	return $userid;

}

function ValidPermission($currentfile,$object,$defaultpermission)
{
  global $pbx,$cwlang, $cwLanguage,$cicUserInfo;
	$userid = $_SESSION["cicUserId"];
//echo "profile: ".$cwUserInfo["profilename"];
//print_r($cicUserInfo);
	$profile = $cwUserInfo["profilename"];
	$ignore = 0; 

return;

/*
	$ignore = 1;
	if ( $cwUserInfo["type"] ==CONTACTCENTERUSER )
	{
		$ignore = 0;
//		return $userid;
	}
*/
//	if (( $cicUserInfo["entity_id"] == 0 ) && ( $cicUserInfo["type"] == USER_SUPERUSER) )	return;
	if ( ( $cicUserInfo["privileged"] == 3) || ( $cicUserInfo["privileged"] == 4) )	return;


	$rettemp = "";
	$qry="SELECT * FROM ".PREFIX_TABLE."tv_user_profile WHERE object='$object' and name= '".$profile."' limit 1";
//echo $cicUserInfo["type"] .$qry;
	$arrp=$pbx->querydata($qry);
	$allownew = 0;
	$allowedit = 0;
	$allowdelete = 0;
	$allowview = 0;
	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$allownew=$v["allownew"];
			$allowedit=$v["allowedit"];
			$allowdelete=$v["allowdelete"];
			$allowview=$v["allowview"];
		}
	if ( ( $defaultpermission == "NEW") && ($allownew == 0 )  && ($ignore == 0 ))
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	else if ( ( $defaultpermission == "EDIT") && ($allowedit == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	else if ( ( $defaultpermission == "VIEW") && ($allowview == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	else if ( ( $defaultpermission == "DELETE") && ($allowdelete == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
/*
	else 
	{
		echo "<br><strong><b>*** ".$cwlang["system"]["notallow"].$object.",".$defaultpermission."<br>".$qry."</b></strong><br>";
		die;
	}
*/
//	return $currentfile;
/*
	$ignore = 1;
	if ( $cwUserInfo["type"] ==CONTACTCENTERUSER )
	{
		$ignore = 0;
//		return $userid;
	}
	$rettemp = "";
	$qry="SELECT * FROM ".PREFIX_TABLE."tv_user_permission WHERE object='$object' and userid= '".$userid."' limit 1";
	$arrp=$pbx->querydata($qry);
	$allownew = 0;
	$allowedit = 0;
	$allowdelete = 0;
	$allowview = 0;
	//get the IVR list
	if (is_array($arrp))
		foreach($arrp as $v) {
			$allownew=$v["allownew"];
			$allowedit=$v["allowedit"];
			$allowdelete=$v["allowdelete"];
			$allowview=$v["allowview"];
		}
	if ( ( $defaultpermission == "NEW") && ($allownew == 0 )  && ($ignore == 0 ))
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	if ( ( $defaultpermission == "EDIT") && ($allowedit == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	if ( ( $defaultpermission == "VIEW") && ($allowview == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
	if ( ( $defaultpermission == "DELETE") && ($allowdelete == 0 )  && ($ignore == 0 ) )
	{
		echo "<br><strong><b>".$cwlang["system"]["notallow"]."</b></strong><br>";
		die;
	}
//	return $currentfile;
*/
}

?>