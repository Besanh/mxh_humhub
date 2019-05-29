<?php

/**s
* @author soroush khosravi
* @aghasoroush@gmail.com
* @copyright 2011
* @Class Name : Mini HTML Helper
* @This class can generates links with a simple tooltip!
* @See the example in the package to got it! 
*/

class HTML
{
    private $img_id_echo;
    private $id_echo;
    private $class_echo = "";
    private $img_echo = "";
    private $img_tag = "";
    private $tooltip_echo = "";


    public function AddLink($address, $expr = "", $tooltip = "", $id = "", $class =
        "", $img = "", $img_id = "")
    {
        if ($tooltip != "")
        {
            $this->class_echo = $this->class_echo . " " . "tip";
            $this->AddTooltip($tooltip);

        }
        if ($id == "")
        {
            $this->id_echo = "";
        } elseif ($id != "")
        {
            $this->id_echo = "$id";
        }

        if ($class != "" && $tooltip == "")
        {
            $this->class_echo = $class;
        } elseif ($tooltip != "")
        {
            $this->class_echo = $class . " tip";
        }
        if ($img != "")
        {
            $this->img_echo = $img;
        }
        if ($img_id != "")
        {
            $this->img_id_echo = $img_id;
        }
        if ($img != "")
        {
            $this->img_tag = "<img $this->img_id_echo src=\"$img\">";
        }
        return "<a href='$address' id=\"$this->id_echo\" class=\"$this->class_echo\" > $this->img_tag $expr $this->tooltip_echo </a>";

    }
    private function AddTooltip($text)
    {
        $tt = "<span> $text </span>";
        $this->tooltip_echo = $tt;
    }

    /* this function should be used in HEAD tag
    imports a css file in a HTML document
    */
    function AddCss($name /*name of the css file*/ )
    {
        echo "<link href=\"$name\" rel=\"stylesheet\" type=\"text/css\" />";
    }
}

?>