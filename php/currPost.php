<?php
#############################################################
# file: 	currPost.php                                #
# author:	Keith Pope                                  #
# purpose:	Handles HTTP Post method                    #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

class currPost {

    private $code, $rate, $xmlCurrDoc, $xmlCurrEl, $dateTimeraw, $xmlDocResponse,
            $xmlCurrElItem;

    public function currPost($_code, $_rate, $_dateTime)
    {
        $this->code = $_code;
        $this->rate = $_rate;
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

        //if currency xml does exist
        //validate xml
        if ($this->validateInput())
        {
            if ($this->CurrencyExist($this->code))
            {

                //set the xml values
                $this->setXMLValues();
                //delete child currency from currency xml
                $xmlParentNode = $this->xmlCurrDoc->documentElement;
                $xmlParentNode->removeChild($this->xmlCurrElItem);

                // Import the node, and all its children, to the document
                $this->xmlCurrEl = $this->xmlCurrDoc->importNode($this->xmlCurrEl, true);
                // And then append it to the "<root>" node
                $this->xmlCurrDoc->documentElement->appendChild($this->xmlCurrEl);
                //save xml
                $this->xmlCurrDoc->save("./xml/Currency.xml");

                echo "<h3><u>Record Edited</u></h3>";
                echo '<pre>', htmlentities($this->xmlDocResponse,ENT_QUOTES, "ISO-8859-1"), '</pre>';
            } else
            {
               //currency does not exist error
                echo '<pre>', htmlentities(ProduceXmlErr("Post", "2400")), '</pre>';
            }
        } else
        {
            //error with xml
            //echo '<pre>', htmlentities(ProduceXmlErr("PUT", "2500", "Error in service")), '</pre>';
        }
    }

    private function setXMLValues()
    {
        //get most recent rate
        $elements = $this->xmlCurrEl->getElementsByTagName('rateAt');
        //cycle through until last 'at' value reached
        foreach ($elements as $node)
        {
            $rate = $node->getElementsByTagName('rate')->item(0)->nodeValue;
        }
        //set values from current rates 
        $name = $this->xmlCurrEl->getElementsByTagName('name')->item(0)->nodeValue;
        $loc = $this->xmlCurrEl->getElementsByTagName('loc')->item(0)->nodeValue;

        //create response xml 
        $xmldoc = new XMLresponse();
        $xmldoc->createNewElement("method");
        $xmldoc->setAttribute("method", "type", "PUT");
        $xmldoc->addChildToElement("method", "at", $this->dateTimeraw->format('Y-m-d H:i:s'));
        $xmldoc->createNewElement("previous", "method");
        $xmldoc->addChildToElement("previous", "rate", $rate);
        $xmldoc->createNewElement("curr", "previous");
        $xmldoc->addChildToElement("curr", "code", $this->code);
        $xmldoc->addChildToElement("curr", "name", $name);
        $xmldoc->addChildToElement("curr", "loc", $loc);
        $xmldoc->createNewElement("new", "method");
        $xmldoc->addChildToElement("new", "rate", $this->rate);
        $xmldoc->createNewElement("curr1", "new");
        $xmldoc->addChildToElement("curr1", "code", $this->code);
        $xmldoc->addChildToElement("curr1", "name", $name);
        $xmldoc->addChildToElement("curr1", "loc", $loc);

        $this->xmlDocResponse = $xmldoc->getDoc();
        //set xml to string for html output
        $this->xmlDocResponse = $this->xmlDocResponse->saveXML();

        //turn dom element into doc for use in xmlresponse class
        $tempDoc = new DomDocument;
        $tempDoc->appendChild($tempDoc->importNode($this->xmlCurrEl, true));
        //add new rate and time to existing node
        $xmldoc1 = new XMLresponse();
        $xmldoc1->setDoc($tempDoc);
        $xmldoc1->createNewElement("rateAt", "curr");
        $xmldoc1->setAttribute("rateAt", "at", $this->dateTimeraw->getTimestamp());
        $xmldoc1->addChildToElement("rateAt", "rate", $this->rate);

        //edit old currency xml element to include new rate
        $this->xmlCurrEl = $tempDoc->getElementsByTagName('curr')->item(0);
    }

    private function validateInput()
    {
        //check if rate is numeric and entered
        if (!is_numeric($this->rate) || $this->rate == "")
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Post", "2100")), '</pre>';
            return false;
        } 
        //check code is 3 long and upper case
        else if ((strlen($this->code) != 3) || (!ctype_upper($this->code)))
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Post", "2200")), '</pre>';
            return false;
        }else
        {
            return true;
        }
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
                //select node and it's elements
                $this->xmlCurrEl = $node;
                //set element item for later deletion
                $this->xmlCurrElItem = $elements->item($x);
                return true;
            }
            $x++;
        }
        return false;
    }

}

