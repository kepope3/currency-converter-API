<?php
#############################################################
# file: 	updateCurrency.php                          #
# author:	Keith Pope                                  #
# purpose:	Uses API to update currencies               #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

class updateCurrency {

    private $currNode, $xmlCurrDoc, $currCode, $xmlCurrElItem;

    public function updateCurrency(&$xmlDOM = null)
    {
        $this->xmlCurrDoc = $xmlDOM;
    }

    public function getRate($currCode)
    {
        //1st method of downloading all xml currencies from yahoo 
        //get rate from api
        $to = $currCode;
        $yahooXML = new DOMDocument();
        $yahooXML->preserveWhiteSpace = false;
        $response_xml_data = "";
        if (($response_xml_data = file_get_contents(RATES_URL)) === false)
        {
            //service error!
        } else
        {
            $yahooXML->loadXML($response_xml_data);
        }
        $elements = $yahooXML->getElementsByTagName('field');
        $foundCurr = false;
        $staticFoundCurr = false;
        $foundGBP = false;
        $GBPRate = 0;
        $currRate = 0;
        foreach ($elements as $node)
        {
            if ($node->getAttribute('name') == "name")
            {
                if ($node->nodeValue == "USD/" . $to)
                {
                    $staticFoundCurr = true;
                    $foundCurr = true;
                }                    
                if ($node->nodeValue == "USD/GBP")
                    $foundGBP = true;
            }
            if ($foundCurr && $node->getAttribute('name') == "price")
            {
                $currRate = $node->nodeValue;
                $foundCurr = false; //stop cycling through
            }
            if ($foundGBP && $node->getAttribute('name') == "price")
            {
                $GBPRate = $node->nodeValue;
                $foundGBP = false; //stop cycling through
            }
            if ($foundGBP && $foundCurr)
                break;
        }
        if ($currCode == "USD")
        {
            return 1 / $GBPRate;
        } 
        //used if currency is not contained un yahoo xml
        else if (!$staticFoundCurr)
        {
            return "false";
        }else
        {
            //convert USD to pound, then calc curr exchange
            return (1 / $GBPRate) * $currRate;
        }
    }

    private function updateCurreny()
    {
        //1st method of downloading all xml currencies from yahoo 
        //get rate from api
        $to = $this->currCode;
        $yahooXML = new DOMDocument();
        $yahooXML->preserveWhiteSpace = false;
        $response_xml_data = "";
        if (($response_xml_data = file_get_contents(RATES_URL)) === false)
        {
            //service error!
        } else
        {
            $yahooXML->loadXML($response_xml_data);
        }
        $elements = $yahooXML->getElementsByTagName('field');
        $foundCurr = false;
        $foundGBP = false;
        $GBPRate = 0;
        $currRate = 0;
        foreach ($elements as $node)
        {
            if ($node->getAttribute('name') == "name")
            {
                if ($node->nodeValue == "USD/" . $to)
                    $foundCurr = true;
                if ($node->nodeValue == "USD/GBP")
                    $foundGBP = true;
            }
            if ($foundCurr && $node->getAttribute('name') == "price")
            {
                $currRate = $node->nodeValue;
                $foundCurr = false; //stop cycling through
            }
            if ($foundGBP && $node->getAttribute('name') == "price")
            {
                $GBPRate = $node->nodeValue;
                $foundGBP = false; //stop cycling through
            }
            if ($foundGBP && $foundCurr)
                break;
        }
        //convert USD to pound, then calc curr exchange
        if ($to == "USD")
        {
            $rate = 1 / $GBPRate;
        } else
        {
            //convert USD to pound, then calc curr exchange
            $rate = (1 / $GBPRate) * $currRate;
        }
        /*
          //get rate from api
          $from = 'GBP';
          $to = $this->currCode;
          $url = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=' . $from . $to . '=X';
          $handle = fopen($url, 'r');

          if ($handle)
          {
          $result = fgets($handle, 4096);
          fclose($handle);
          }

          $allData = explode(',', $result);
          $rate = $allData[1]; */


        //turn dom element into doc for use in xmlresponse class
        $tempDoc = new DomDocument;
        $tempDoc->appendChild($tempDoc->importNode($this->currNode, true));
        //add new rate and time to existing node
        $xmldoc1 = new XMLresponse();
        $xmldoc1->setDoc($tempDoc);
        $xmldoc1->createNewElement("rateAt", "curr");
        //get current time and date and format
        $unformattedDateTime = new DateTime('NOW');
        $xmldoc1->setAttribute("rateAt", "at", $unformattedDateTime->getTimestamp());
        $xmldoc1->addChildToElement("rateAt", "rate", $rate);

        //edit old currency xml element to include new rate
        $this->xmlCurrEl = $tempDoc->getElementsByTagName('curr')->item(0);

        //delete child currency from currency xml
        $xmlParentNode = $this->xmlCurrDoc->documentElement;
        $xmlParentNode->removeChild($this->xmlCurrElItem);

        // Import the node, and all its children, to the document
        $this->xmlCurrEl = $this->xmlCurrDoc->importNode($this->xmlCurrEl, true);
        // And then append it to the "<root>" node
        $this->xmlCurrDoc->documentElement->appendChild($this->xmlCurrEl);
        //save xml
        $this->xmlCurrDoc->save("./xml/Currency.xml");
    }

    public function checkCurrency($_currNode, $_currCode, $_elItem)
    {
        if ($_currCode == "GBP")
            return 0;
        $this->currCode = $_currCode;
        $this->xmlCurrElItem = $_elItem;
        $this->currNode = $_currNode;
        //cycle through at tages until last inpout value entered
        $elements = $this->currNode->getElementsByTagName('rateAt');
        foreach ($elements as $node)
        {
            $timestamp = $node->getAttribute('at');
        }

        //check currency was updated within last 12 hours
        $unformattedDateTime = new DateTime('NOW');
        $time = $unformattedDateTime->getTimestamp();
        if ($timestamp < ($time - UPDATE_INTERVAL))//less than 12 hours
        {
            $this->updateCurreny();
        } else
        {
            //echo "NO IT IS NOT OLDER THAN 12 HOURS";
        }
    }

}

