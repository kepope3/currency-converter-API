<?php
#############################################################
# file: 	currPut.php                                 #
# author:	Keith Pope                                  #
# purpose:	Handles HTTP PUT method                     #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

class currPut {

    private $code, $name, $rate, $countries, $dateTimeraw;
    private $xmlDocResponse, $currXMLDOM,$xmlCurrEl, $xmlCurrDoc;

    //assign local private variables from form
    public function currPut($_code, $_name, $_rate, $_countries, $_dateTime)
    {
        $this->code = $_code;
        $this->name = $_name;
        $this->rate = $_rate;
        $this->countries = $_countries;
        $this->dateTimeraw = $_dateTime;
        //load xmlDoc
        $this->xmlCurrDoc = new DOMDocument();
        if ($this->xmlCurrDoc->load('./xml/Currency.xml') === false)
		{
			echo '<pre>', htmlentities(ProduceXmlErr("Post", "2500")), '</pre>';
			exit;
		}
        //ensure xml in correct format        
        $this->xmlCurrDoc->preserveWhiteSpace = false;
        $this->xmlCurrDoc->formatOutput = true;


        //validate xml
        if ($this->validateInput())
        {
            if (!$this->CurrencyExist($this->code))
            {
                //set the xml values
                $this->setXMLValues();
                //add currency code and save serialized array
                addCurrCodeToAry($this->code);
                // Import the node, and all its children, to the document
                $this->xmlCurrEl = $this->xmlCurrDoc->importNode($this->xmlCurrEl, true);
                // And then append it to the "<root>" node
                $this->xmlCurrDoc->documentElement->appendChild($this->xmlCurrEl);
                //save xml
                $this->xmlCurrDoc->save("./xml/Currency.xml"); 

                echo "<h3><u>New Record Entry</u></h3>";
                echo '<pre>', htmlentities($this->xmlDocResponse), '</pre>';
            } else
            {
                //currency exists already, service error
                echo '<pre>', htmlentities(ProduceXmlErr("Put", "2500")), '</pre>';
            }
        } else
        {
            //error with xml
        }
    }

    private function setXMLValues()
    {
        //create response XML
        $xmldoc= new XMLresponse();        
        $xmldoc->createNewElement("method");
        $xmldoc->setAttribute("method", "type", "PUT");
        $xmldoc->addChildToElement("method","from", $this->dateTimeraw->format('Y-m-d H:i:s'));
        $xmldoc->addChildToElement("method", "rate", $this->rate);        
        $xmldoc->createNewElement("curr", "method");
        $xmldoc->addChildToElement("curr","code", $this->code);
        $xmldoc->addChildToElement("curr", "name", $this->name);
        $xmldoc->addChildToElement("curr", "loc", $this->countries);        
        $this->xmlDocResponse = $xmldoc->getDoc();
        
        //create Currency XML
        $xmldoc1= new XMLresponse();  
        $xmldoc1->createNewElement("curr");
        $xmldoc1->setAttribute("curr", "code", $this->code);
        $xmldoc1->addChildToElement("curr", "name", $this->name);
        $xmldoc1->addChildToElement("curr", "loc", $this->countries);
        $xmldoc1->createNewElement("rateAt", "curr");
        $xmldoc1->setAttribute("rateAt", "at", $this->dateTimeraw->getTimestamp());
        $xmldoc1->addChildToElement("rateAt", "rate",  $this->rate);
        $this->currXMLDOM=$xmldoc1->getDoc();
        
        $this->xmlCurrEl = $this->currXMLDOM->getElementsByTagName('curr')->item(0);

        //set xml to string for html output
        $this->xmlDocResponse = $this->xmlDocResponse->saveXML();
    }

    private function validateInput()
    {
        $uc = new updateCurrency();
        //check code is 3 long and upper case
        if ((strlen($this->code) != 3) || (!ctype_upper($this->code)))
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Put", "2200")), '</pre>';
            return false;
        }
        //check countries is not numeric,does not contain white spaces and is not missing
        else if (is_numeric ($this->countries) || $this->countries == "")
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Put", "2300")), '</pre>';
            return false;
        }
        //does that code exist in yahoo file
        else if ($uc->getRate($this->code) == "false")
        {
            echo '<pre>', htmlentities(ProduceXmlErr("Put", "2400")), '</pre>';
            return false;
        }
        else
        {
            return true;
        }
    }

    private function CurrencyExist()
    {
        $elements = $this->xmlCurrDoc->getElementsByTagName('curr');
        foreach ($elements as $node)
        {
            $curr = $node->getAttribute('code'); 
            if ($curr == $this->code)
            {
                return true;
            }
        }
        return false;
    }

}
