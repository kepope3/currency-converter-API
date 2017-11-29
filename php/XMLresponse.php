<?php
#############################################################
# file: 	XMLresponse.php                                  #
# author:	Keith Pope                                  #
# purpose:	Makes new interface for DomDocument         #                          #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}
class XMLresponse {

    private $xmlDoc;
    private $element; //array of elements
    private $elementName;
    private $pointer = -1; //pointer to element ary

    function XMLresponse()
    {
        //create new DOM doc and assign to private var
        $this->xmlDoc = new DomDocument("1.0", "ISO-8859-1");
        //ensure clean format
        $this->xmlDoc->formatOutput = true;
        $this->xmlDoc->preserveWhiteSpace = false;
    }

    function createNewElement($elementName, $insideElement = "*rootElement*")
    {
        //increment ary pointer
        $this->pointer++;
        //create new DOM element (tag)
        if ($insideElement == "*rootElement*")
        {
            $this->element[$this->pointer] = $this->xmlDoc->appendChild($this->xmlDoc->createElement($elementName));
            $this->elementName[$this->pointer] = $elementName;
        } else
        {
            for ($x = 0; $x < sizeof($this->elementName); $x++)
            {
                if ($this->elementName[$x] == $insideElement)
                {
                    $this->element[$this->pointer] = $this->element[$x]->appendChild($this->xmlDoc->createElement($elementName));
                    $this->elementName[$this->pointer] = $elementName;
                    break;
                }
            }
        }
    }

    function setAttribute($elementName, $attName, $attVal)
    {
        //find element with given name
        for ($x = 0; $x < sizeof($this->elementName); $x++)
        {
            if ($this->elementName[$x] == $elementName)
            {
                //set attribute
                $this->element[$x]->setAttribute($attName, $attVal);
                break;
            }
        }
    }

    function addChildToElement($elementName, $childName, $childValue)
    {
        //find element with given name
        for ($x = 0; $x < sizeof($this->elementName); $x++)
        {
            if ($this->elementName[$x] == $elementName)
            {
                //make new child element
                $this->element[$x]->appendChild($this->xmlDoc->createElement($childName, $childValue));
                break;
            }
        }
    }

    function getDoc()
    {
        return $this->xmlDoc;
    }

    function setDoc(&$dom)
    {
        $this->xmlDoc = $dom;//point to passed dom
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('/*');
        //cycle through all elements
        foreach($nodes as $node)
        {
            $this->pointer++;
            $this->element[$this->pointer] = $node;
            $this->elementName[$this->pointer] = $node->nodeName;
        }
    }

}


