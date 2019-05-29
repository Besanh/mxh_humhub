<?php

    error_reporting(E_ALL);

    // the very first time, all properties are set through GET and so we need
    // to convert them to POSTs as we'll be using that from that moment on
    if (
        isset($_GET["preselectedDate"]) &&
        isset($_GET["month"]) &&
        isset($_GET["year"]) &&
        isset($_GET["controlName"]) &&
        isset($_GET["dateFormat"]) &&
        isset($_GET["firstDayOfWeek"]) &&
        isset($_GET["clearDateButton"]) &&
        isset($_GET["language"]) &&
        isset($_GET["template"])
    ) {
        $_POST["preselectedDate"] = $_GET["preselectedDate"];
        $_POST["month"] = $_GET["month"];
        $_POST["year"] = $_GET["year"];
        $_POST["controlName"] = $_GET["controlName"];
        $_POST["dateFormat"] = $_GET["dateFormat"];
        $_POST["firstDayOfWeek"] = $_GET["firstDayOfWeek"];
        $_POST["clearDateButton"] = $_GET["clearDateButton"];
        $_POST["language"] = $_GET["language"];
        $_POST["template"] = $_GET["template"];
    }
    if (
        isset($_POST["preselectedDate"]) &&
        isset($_POST["month"]) &&
        isset($_POST["year"]) &&
        isset($_POST["controlName"]) &&
        isset($_POST["dateFormat"]) &&
        isset($_POST["firstDayOfWeek"]) &&
        isset($_POST["clearDateButton"]) &&
        isset($_POST["language"]) &&
        isset($_POST["template"])
    ) {

        // do some error checking
        if ($_POST["month"] == "") {
            $_POST["month"] = date("m");
        }
        if ($_POST["year"] == "") {
            $_POST["year"] = date("Y");
        }
        if ($_POST["month"] < 1) {
            $_POST["year"] = $_POST["year"] - 1;
            $_POST["month"] = 12;
        } elseif ($_POST["month"] > 12) {
            $_POST["year"] = $_POST["year"] + 1;
            $_POST["month"] = 1;
        }
        $_POST["year"] = ($_POST["year"] < 1970 ? 1970 : ($_POST["year"] > 2037 ? 2037 : $_POST["year"]));

        // compute the number of days in the selected month
        $daysInCurrentMonth = date("t", mktime(0, 0, 0, $_POST["month"], 1, $_POST["year"]));

        // what weekday is the first day of the month?
        $firstWeekDayInCurrentMonth = date("w", mktime(0, 0, 0, $_POST["month"], 1, $_POST["year"]));

        // how many days to display from previous month?
        $cmp = $firstWeekDayInCurrentMonth - $_POST["firstDayOfWeek"];
        $daysFromPreviousMonth = $cmp < 0 ? 7 - $cmp : $cmp;
        
        // include the XTemplate class
        include_once("./class.xtemplate.php");

        // instantiate a new template object with the given template
        $xtpl = new XTemplate("./".$_POST["template"]."/template.xtpl");

        // include the language file
        include_once("./".$_POST["language"].".php");

        // assign the language array
        $xtpl->assign("languageStrings", $languageStrings);

        // parse month names
        // upper row
        for ($i = 0; $i < 6; $i++) {
        
            $xtpl->assign("monthLiteral", substr($languageStrings["strLang_monthsNames"][$i], 0, 3));
            $xtpl->assign("monthNumeric", $i + 1);
            
            // if this is the current month
            if ($i + 1 == $_POST["month"]) {
            
                $xtpl->parse("main.months_names_row.months_names_item.month_name_selected");
            
            // if this is any other month
            } else {

                $xtpl->parse("main.months_names_row.months_names_item.month_name");

            }
            $xtpl->parse("main.months_names_row.months_names_item");
            
        }
        
        $xtpl->parse("main.months_names_row");

        // lower row
        for ($i = 6; $i < 12; $i++) {
        
            $xtpl->assign("monthLiteral", substr($languageStrings["strLang_monthsNames"][$i], 0, 3));
            $xtpl->assign("monthNumeric", $i + 1);
            
            // if this is the current month
            if ($i + 1 == $_POST["month"]) {

                $xtpl->parse("main.months_names_row.months_names_item.month_name_selected");

            // if this is any other month
            } else {

                $xtpl->parse("main.months_names_row.months_names_item.month_name");

            }
            
            $xtpl->parse("main.months_names_row.months_names_item");

        }
        
        $xtpl->parse("main.months_names_row");

        // parse day names
        for ($i = 0; $i < 7; $i++) {
        
            $xtpl->assign("dayName", substr($languageStrings["strLang_daysNames"][($i + $_POST["firstDayOfWeek"] > 6 ? $i + $_POST["firstDayOfWeek"] - 7 : $i + $_POST["firstDayOfWeek"])], 0, 1));
            $xtpl->parse("main.day_names");
            
        }

        // the calendar shows 42 days
        for ($i = 1; $i < 43; $i++) {
        
            // 7 days in a row
            if ($i % 7 == 0) {
                // if this isn't the last row
                if ($i < 42) {
                    $xtpl->parse("main.days.new_row");
                // if this is the last row
                } else {
                    $xtpl->parse("main.days.end_row");
                }
            }
            
            // days from previous month
            if ($i <= $daysFromPreviousMonth) {

                $timestamp = mktime(0, 0, 0, $_POST["month"], 0 - ($daysFromPreviousMonth - $i), $_POST["year"]);
                $xtpl->assign("day", date("d", $timestamp));
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                        // if this the preselected date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.previousMonth_weekend_preselected");
                        } else {
                            $xtpl->parse("main.days.previousMonth_weekend");
                        }
                        break;

                    // if the day to show is a weekday
                    default:
                        // if this the preselected date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.previousMonth_weekday_preselected");
                        } else {
                            $xtpl->parse("main.days.previousMonth_weekday");
                        }
                        
                }
                
            // days in current month
            } elseif ($i > $daysFromPreviousMonth && $i <= ($daysInCurrentMonth + $daysFromPreviousMonth)) {
            
                $timestamp = mktime(0, 0, 0, $_POST["month"], $i - $daysFromPreviousMonth, $_POST["year"]);
                $xtpl->assign("returnValue", date($_POST["dateFormat"], $timestamp));
                $xtpl->assign("day", date("d", $timestamp));
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                        // if this both the preselected date and current date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"]))) &&
                            $timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))
                        ) {
                            $xtpl->parse("main.days.currentMonth_currentDay_weekend_preselected");
                        // if this is just the preselected date
                        } elseif (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.currentMonth_weekend_preselected");
                        // if this the current date
                        } elseif ($timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
                            $xtpl->parse("main.days.currentMonth_currentDay_weekend");
                        } else {
                            $xtpl->parse("main.days.currentMonth_weekend");
                        }
                        break;
                    // if the day to show is a weekday
                    
                    default:
                        // if this both the preselected date and current date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"]))) &&
                            $timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))
                        ) {
                            $xtpl->parse("main.days.currentMonth_currentDay_weekday_preselected");
                        // if this is just the preselected date
                        } elseif (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.currentMonth_weekday_preselected");
                        // if this the current date
                        } elseif ($timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
                            $xtpl->parse("main.days.currentMonth_currentDay_weekday");
                        } else {
                            $xtpl->parse("main.days.currentMonth_weekday");
                        }
                        
                }
                
            // days in next month
            } else {
            
                $timestamp = mktime(0, 0, 0, $_POST["month"] + 1, $i - $daysFromPreviousMonth - $daysInCurrentMonth, $_POST["year"]);
                $xtpl->assign("day", date("d", $timestamp));
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                        // if this the preselected date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.nextMonth_weekend_preselected");
                        } else {
                            $xtpl->parse("main.days.nextMonth_weekend");
                        }
                        break;
                        
                    // if the day to show is a weekday
                    default:
                        // if this the preselected date
                        if (
                            $_POST["preselectedDate"] != "" &&
                            $timestamp == mktime(0, 0, 0, date("m", strtotime($_POST["preselectedDate"])), date("d", strtotime($_POST["preselectedDate"])), date("Y", strtotime($_POST["preselectedDate"])))
                        ) {
                            $xtpl->parse("main.days.nextMonth_weekend_preselected");
                        } else {
                            $xtpl->parse("main.days.nextMonth_weekday");
                        }
                        
                }
                
            }
            $xtpl->parse("main.days");

        }

        // if the clear date button is to be showed
        if ($_POST["clearDateButton"] == 1) {

            $xtpl->parse("main.clear_date_button");

        }

        $xtpl->parse("main");
        $xtpl->out("main");
        
    }

?>
