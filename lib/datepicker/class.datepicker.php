<?php

/**
 *  A PHP class providing access to a date picker
 *
 *  -   provides a very nice and intuitive date picker
 *  -   you can set what month and/or year to show by default
 *  -   you can set mark a day as selected by default
 *  -   you can set the format of the date to be returned
 *  -   you can set any day to be taken as the starting day of the week
 *  -   everything is template driven and highly customizable
 *  -   supports localisation
 *  -   the code is heavily documented so you can easily understand every aspect of it
 *
 *  See the manual for more info.
 *
 *  This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivs 2.5 License.
 *  To view a copy of this license, visit {@link http://creativecommons.org/licenses/by-nc-nd/2.5/} or send a letter to
 *  Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @version    1.0.1 (last revision: September 04, 2006)
 *  @copyright  (c) 2006 Stefan Gabos
 *  @package    database
 *  @example    example.php
 */

error_reporting(E_ALL);

class datePicker
{

    /**
     *  Preselects a day in the calendar
     */
    var $preselectedDate;
    
    /**
     *  Format of the returned day
     *
     *  Any combination allowed by PHP date() function can be used
     *
     *  default is "m d Y"
     *
     *  @var string
     */
    var $dateFormat;
    
    /**
     *  What day should be taken as the first day of week
     *
     *  Possible values range from 0 (Sunday) to 6 (Saturday)
     *
     *  default is 0
     *
     *  @var    integer
     */
    var $firstDayOfWeek;
    
    /**
     *  Weather a "clear date" button should be displayed or not on the calendar
     *
     *  default is FALSE
     *
     *  @var boolean
     */
    var $showClearDate;
    
    /**
     *  Height of the calendar window
     *
     *  default is 210
     *
     *  @var integer
     */
    var $windowHeight;

    /**
     *  Width of the calendar window
     *
     *  default is 180
     *
     *  @var integer
     */
    var $windowWidth;

    /**
     *  Language file to use
     *
     *  The name of the php language file you wish to use from the /languages folder.
     *  Without the extension! (i.e. "german" for the german language not "german.php")
     *
     *  default is "english"
     *
     *  @var   string
     */
    var $language;

    /**
     *  Template folder to use
     *  Note that only the folder of the template you wish to use needs to be specified. Inside the folder
     *  you <b>must</b> have the <b>template.xtpl</b> file which will be automatically used
     *
     *  default is "default"
     *
     *  @var   string
     */
    var $template;

    /**
     *  Constructor of the class
     *
     *  @return void
     */
    function datePicker()
    {
//  global $cwBasePath,$cwlang,$cicUserInfo,$pbx;
//  require_once("$absBasePath/lib/xajax/xajax.inc.php");
//  require_once("$absBasePath/lib/datepicker/class.datepicker.php");    
        // default values to properties
        $this->preselectedDate = "";
        $this->dateFormat = "m d Y";
        $this->firstDayOfWeek = 0;
        $this->windowHeight = 210;
        $this->windowWidth = 180;
        $this->language = "english";
        $this->template = "default";
        $this->showClearDate = true;

        // get the absolute path of the class. any further includes rely on this
        // and (on a windows machine) replace \ with /
        $this->absolutePath = preg_replace("/\\\/", "/", dirname(__FILE__));

        // remove $_SERVER["DOCUMENT_ROOT"] from the path
        // this path is to be used from within HTML as it is a relative path
        $this->strippedPath = preg_replace("/".preg_replace("/\//", "\/", $_SERVER["DOCUMENT_ROOT"])."/i", "", $this->absolutePath);
    }
    
    /**
     *  Returns the JavaScript code that will open the pop-up window containing the calendar
     *
     *  @param  string  $controlID      the ID of the HTML element (textbox or textarea) where to return the selected value
     *  @param  integer $startMonth     (optional) which month to start the calendar from
     *  @param  integer $startYear      (optional) which year to start the calendar from
     *
     *  @return void
     */
    function show($controlID, $startMonth = "", $startYear = "")
    {
        return "javascript:var cw = null; cw = window.open('".$this->strippedPath."/datepicker.php?preselectedDate=".$this->preselectedDate."&month=".$startMonth."&year=".$startYear."&controlName=".$controlID."&dateFormat=".$this->dateFormat."&firstDayOfWeek=".$this->firstDayOfWeek."&clearDateButton=".$this->showClearDate."&language=".$this->language."&template=".$this->template."','datePicker','width=".$this->windowWidth.",height=".$this->windowHeight.",scrollbars=no,toolbar=no,menubar=no,location=no,alwaysraised=yes,modal=yes'); if (window.focus) { cw.focus() } return true";
    }

  }

?>
