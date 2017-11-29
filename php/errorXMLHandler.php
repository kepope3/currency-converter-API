<?php
#############################################################
# file: 	errorXMLHandler.php                         #
# author:	Keith Pope                                  #
# purpose:	Generates XML error responses               #
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}

function ProduceXmlErr($type, $code)
{
    $errXml = new DOMDocument();
	if ($errXml->load('./xml/err.xml') === false)
	{
		handleError("1400");
		exit;
	}
    //example of xpath to find attribute field
    $xpath = new DOMXPath($errXml);
    $elements = $xpath->query('//method[@type=""]');
    if ($elements->length >= 1)
    {
        $element = $elements->item(0);
        $element->setAttribute('type', $type);
    }

    $errXml->getElementsByTagName('code')->item(0)->nodeValue = $code;
    $errXml->getElementsByTagName('msg')->item(0)->nodeValue = $GLOBALS['error_hash'][$code];
    return $errXml->saveXML();
}

function handleError($code)
{
    $xml = new XMLresponse();
    $xml->createNewElement("conv");
    $xml->createNewElement("error", "conv");
    $xml->addChildToElement("error", "code", $code);
    $xml->addChildToElement("error", "msg", $GLOBALS['error_hash'][$code]);
    $errorxml = $xml->getDoc();
    $errorxml = simplexml_load_string($errorxml->saveXML());
    if ($_GET["format"] == "json")
	{
		$json = json_encode($errorxml,JSON_PRETTY_PRINT);
        header("Content-Type: application/json; charset=UCS-16");
        echo $json;
	}
	else
	{
		header("Content-Type: application/xml; charset=UCS-16");
		ob_clean();
		echo $errorxml->saveXML();
	}
}
	


