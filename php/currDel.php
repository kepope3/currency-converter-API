<?php
#############################################################
# file: 	currDel.php                                 #
# author:	Keith Pope                                  #
# purpose:	Handles HTTP DELETE method                  #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

class currDel {

    private $code, $xmlCurrDoc, $dateTimeraw, $xmlCurrElItem, $xmlDocResponse;

    public function currDel($_code, $_dateTime)
    {
        $this->code = $_code;
        $this->dateTimeraw = $_dateTime;
        $this->xmlCurrDoc = new DOMDocument();
        if ($this->xmlCurrDoc->load('./xml/Currency.xml') === false)
		{
			echo '<pre>', htmlentities(ProduceXmlErr("Post", "2500")), '</pre>';
			exit;
		}
        //ensure xml in correct format        
        $this->xmlCurrDoc->preserveWhiteSpace = false;
        $this->xmlCurrDoc->formatOutput = true;

        if ($this->validateInput())
        {
            if ($this->CurrencyExist($this->code))
            {
                //set the xml values
                $this->setXMLValues();
                //delete child currency
                $xmlParentNode = $this->xmlCurrDoc->documentElement;
                $xmlParentNode->removeChild($this->xmlCurrElItem);
                //save xml
                $this->xmlCurrDoc->save("./xml/Currency.xml");
                echo "<h3><u>Record Deleted</u></h3>";
                echo '<pre>', htmlentities($this->xmlDocResponse), '</pre>';
            } else
            {
                //currency does not exist error
                echo '<pre>', htmlentities(ProduceXmlErr("Delete", "2400")), '</pre>';
            }
        } else
        {
            //error with xml
        }
    }

    private function setXMLValues()
    {
        //create response xml 
        $xmldoc = new XMLresponse();
        $xmldoc->createNewElement("method");
        $xmldoc->setAttribute("method", "type", "DELETE");
        $xmldoc->addChildToElement("method", "at", $this->dateTimeraw->format('Y-m-d H:i:s'));
        $xmldoc->addChildToElement("method", "code", $this->code);

        $this->xmlDocResponse = $xmldoc->getDoc();
        $this->xmlDocResponse = $this->xmlDocResponse->saveXML();
    }

    private function validateInput()
    {
        //check code is 3 long and upper case
        if ((strlen($this->code) != 3) || (!ctype_upper($this->code)))
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Delete", "2200")), '</pre>';
            return false;
        }
        return true;
    }

    private function CurrencyExist()
    {
        $elements = $this->xmlCurrDoc->getElementsByTagName('curr');
        $x = 0;
        foreach ($elements as $node)
        {
            $curr = $node->getAttribute('code');
            if ($curr == $this->code)
            {
                //set element item for later deletion
                $this->xmlCurrElItem = $elements->item($x);
                return true;
            }
            $x++;
        }
        return false;
    }

}

