<?php
#############################################################
# file: 	currGet.php                                 #
# author:	Keith Pope                                  #
# purpose:	Handles HTTP GET method                     #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

class currGet {

    private $xmlCurrDoc, $xmlFromCurrEl, $xmlToCurrEl,
            $dateTimeraw, $amnt, $fromCurr, $toCurr, $format, $xmlCurrToElItem,
            $xmlCurrFromElItem, $convResult, $toRate, $fromRate, $XMLDOM;

    public function currGet($_amount, $_from, $_to, $_format, $_dateTime)
    {
        $this->amnt = $_amount;
        $this->fromCurr = $_from;
        $this->toCurr = $_to;
        $this->format = $_format;
        $this->dateTimeraw = $_dateTime;
        $this->xmlCurrDoc = new DOMDocument();
		//if failed to load then run error
        if ($this->xmlCurrDoc->load('./xml/Currency.xml') === false)
		{
			handleError("1400");
			exit;
		}
		
        //ensure xml in correct format        
        $this->xmlCurrDoc->preserveWhiteSpace = false;
        $this->xmlCurrDoc->formatOutput = true;
        $updCurr = new updateCurrency($this->xmlCurrDoc);


        if ($this->validateInput())
        {
            if ($this->CurrencyExist())
            {
                //try updating currecy values
                try
                {
                    $updCurr->checkCurrency($this->xmlFromCurrEl, $this->fromCurr, $this->xmlCurrFromElItem);
                    $updCurr->checkCurrency($this->xmlToCurrEl, $this->toCurr, $this->xmlCurrToElItem);
                } catch (Exception $e)
                {
                    //service error
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
                //reload possibly edited xml
                $this->xmlCurrDoc->load('./xml/Currency.xml');
                $this->CurrencyExist(); //set xml nodes again
                //get most recent rates
                $elements = $this->xmlFromCurrEl->getElementsByTagName('rateAt');
                //cycle through until last 'at' value reached
                foreach ($elements as $node)
                {
                    $this->fromRate = $node->getElementsByTagName('rate')->item(0)->nodeValue;
                }
                $elements = $this->xmlToCurrEl->getElementsByTagName('rateAt');
                //cycle through until last 'at' value reached
                foreach ($elements as $node)
                {
                    $this->toRate = $node->getElementsByTagName('rate')->item(0)->nodeValue;
                }
                $this->convResult = ($_amount / $this->fromRate) * $this->toRate; //value / fromrate * torate
                $this->setXMLValues();
                //format xml for output
                $xml = $this->XMLDOM->saveXML();
                $xml = simplexml_load_string($xml);

                if ($_format == "xml")
                {
                    header("Content-Type: application/xml; charset=ISO-8859-1");
                    ob_clean();
                    echo $this->XMLDOM->saveXML();
                } else if ($_format == "json")
                {
                    $json = json_encode($xml,JSON_PRETTY_PRINT);
                    header("Content-Type: application/json; charset=ISO-8859-1");
                    echo $json;
                }
                else
                {
					//error xml msg
                    handleError("1200");
                }
            } else
            {
                handleError("1000");
            }
        } else
        {
            
        }
    }

    private function CurrencyExist()
    {
        $foundToCurr = false;
        $foundFromCurr = false;
        $elements = $this->xmlCurrDoc->getElementsByTagName('curr');
        $x = 0;
        foreach ($elements as $node)
        {
            $curr = $node->getAttribute('code');
            if ($curr == $this->toCurr)
            {
                //select node and it's elements
                $this->xmlToCurrEl = $node;
                $this->xmlCurrToElItem = $elements->item($x);
                $foundToCurr = true;
            } else if ($curr == $this->fromCurr)
            {
                //select node and it's elements
                $this->xmlFromCurrEl = $node;
                $this->xmlCurrFromElItem = $elements->item($x);
                $foundFromCurr = true;
            }
            $x++;
        }
        if ($foundFromCurr && $foundToCurr)
        {
            return true;
        } else
        {
            return false;
        }
    }

    private function setXMLValues()
    {
        //set all values
        $name = $this->xmlFromCurrEl->getElementsByTagName('name')->item(0)->nodeValue;
        $loc = $this->xmlFromCurrEl->getElementsByTagName('loc')->item(0)->nodeValue;
        $name1 = $this->xmlToCurrEl->getElementsByTagName('name')->item(0)->nodeValue;
        $loc1 = $this->xmlToCurrEl->getElementsByTagName('loc')->item(0)->nodeValue;

        $xml = new XMLresponse();
        $xml->createNewElement("conv");
        $xml->addChildToElement("conv", "at", $this->dateTimeraw->format('Y-m-d H:i:s'));
        $xml->addChildToElement("conv", "rate", $this->toRate);
        $xml->createNewElement("from", "conv");
        $xml->addChildToElement("from", "code", $this->fromCurr);
        $xml->addChildToElement("from", "curr", $name);
        $xml->addChildToElement("from", "loc", $loc);
        $xml->addChildToElement("from", "amnt", $this->amnt);
        $xml->createNewElement("to", "conv");
        $xml->addChildToElement("to", "code", $this->toCurr);
        $xml->addChildToElement("to", "curr", $name1);
        $xml->addChildToElement("to", "loc", $loc1);
        $xml->addChildToElement("to", "amnt", $this->convResult);

        $this->XMLDOM = $xml->getDoc();
        //put into sensible text format
        //echo '<pre>', htmlentities($xmlDom->saveXML()), '</pre>';
    }

    private function validateInput()
    {
        //CHECK INPUT VARIABLES (turn into dom object)
        return true;
    }
}

