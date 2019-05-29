/**
 * Javascript library to extend xajax 
 * Project: tvpbx
  * @author Wolfgang Alper
 * @package tvpbx
 * @subpackage xajax
 */

/**
 * Use to create a custom response class in xajax
 * @param string  ID of select element to add item
 * @param string ID of option to add
 * @param string text of option
 * @param string text of value
 */
function addOption(selectId,optionId,txt,val)
{
  var objOption = new Option(txt,val);
  objOption.id = optionId;
  document.getElementById(selectId).options.add(objOption);
}


/**
 * Use to create a custom response class in xajax
 *
 * @param string ID of select element to add item
 */
function removeAllOptions(selectId)
{
  document.getElementById(selectId).options.length = 0;
}


/**
 * Get currently selected single element from a select element as array
 *
 * @param string ID of select Element
 * @return array Key/Value pair of selected element including name, value, id, index
 */
function getSelectedElement(selectId)
{
  var ret = new Array();
  // get the index in the list
  index = ret['index'] = document.getElementById(selectId).options.selectedIndex; 
  if (index >= 0) 
  {
    ret['text'] = document.getElementById(selectId).options[index].text;
    ret['value'] = document.getElementById(selectId).value; 
    ret['id'] = document.getElementById(selectId).options[index].id;
  }
    
  return ret; 
  
}

// Transfer array and function to exchange vars between browser and xajax backend 
// Allows to save informations between requests
var state = new Array();
function setState(mystate)
{
  state = mystate;
}
