<?php
#############################################################
# file: 	addCurrencies.php                           #
# author:	Keith Pope                                  #
# purpose:	Opens all stored currencies in the array    #
#   in config.php and adds them to the currency xml file    #                       
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}
error_reporting(E_ALL ^ E_NOTICE); //ignor unknown notification

function installCurrencies()
{
    //delete all child nodes in xml
    $xmlCurrDoc = new DOMDocument();
    $xmlCurrDoc->load('./xml/Currency.xml');
    $parentNode = $xmlCurrDoc->getElementsByTagName("Currencies")->item(0);
    while ($parentNode->hasChildNodes())
    {
        $parentNode->removeChild($parentNode->firstChild);
    }
    //save xml
    $xmlCurrDoc->save("./xml/Currency.xml");

    //get currency info from API
    $isoCurrXML = new DOMDocument();
    $isoCurrXML->preserveWhiteSpace = false;
    $response_xml_data = "";
    if (($response_xml_data = file_get_contents(COUNTRIES_URL)) === false)
    {
        //service error!
    } else
    {
        $isoCurrXML->loadXML($response_xml_data);
    }
    foreach ($GLOBALS['ccodes'] as &$value)
    {
        $currCode = $value;
        //cycle through iso code xml
        $elements = $isoCurrXML->getElementsByTagName('CcyNtry');
        //count no. of currencies        
        $foundCurrency = false;
        //reset country
        $countries = "";
        foreach ($elements as $node1)
        {
            $Ccy = $node1->getElementsByTagName('Ccy')->item(0)->nodeValue;
            if ($Ccy == $currCode)
            {
                $name = $node1->getElementsByTagName('CcyNm')->item(0)->nodeValue;
                $countries .= $node1->getElementsByTagName('CtryNm')->item(0)->nodeValue . ",";
                //allow all countries to be found in same country code 
                $foundCurrency = true;
            }
        }
        if (foundCurrency)
        {
            //cut end comma off string
            $countries = rtrim($countries, ",");
            saveXml($currCode, $name, $countries);
        }
    }
}

function saveXml($code, $name, $countries)
{
    //get time
    $unformattedDateTime = new DateTime();

    //get rate
    if ($code != "GBP")
    {
        $rate = new updateCurrency();
        $rate = $rate->getRate($code);
    } else
    {
        $rate = 1;
    }    

    //create Currency el for each currency
    $xmldoc1 = new XMLresponse();
    $xmldoc1->createNewElement("curr");
    $xmldoc1->setAttribute("curr", "code", $code);
    $xmldoc1->addChildToElement("curr", "name", $name);
    $xmldoc1->addChildToElement("curr", "loc", $countries);
    $xmldoc1->createNewElement("rateAt", "curr");
    $xmldoc1->setAttribute("rateAt", "at", $unformattedDateTime->getTimestamp()); //initial value
    $xmldoc1->addChildToElement("rateAt", "rate", $rate); //initial value

    $currXMLDOM = $xmldoc1->getDoc();
    $xmlCurrEl = $currXMLDOM->getElementsByTagName('curr')->item(0);

    //load currency xml
    $xmlCurrDoc = new DOMDocument();
    $xmlCurrDoc->load('./xml/Currency.xml');
    // Import the node, and all its children, to the document
    $xmlCurrEl = $xmlCurrDoc->importNode($xmlCurrEl, true);
    // And then append it to the "<root>" node
    $xmlCurrDoc->documentElement->appendChild($xmlCurrEl);
    //save xml
    $xmlCurrDoc->save("./xml/Currency.xml");
}

