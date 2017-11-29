<?PHP
#############################################################
# file: 	index.php                                  #
# author:	Keith Pope                                  #
# purpose:	Main access point for API                                 #
#############################################################
//hide PHP files from outside world
define('PASSCODE', '#@#kmlksm567567566758sdasdSDSD');

//includes
require_once('./php/config.php');
require_once('./php/currDel.php');
require_once('./php/currPost.php');
require_once('./php/currPut.php');
require_once('./php/currGet.php');
require_once('./php/errorXMLHandler.php');
require_once('./php/XMLresponse.php');
require_once('./php/updateCurrency.php');
require_once('./php/addCurrencies.php');

//reset serialized array to hold only initial 22 currencies
if (isset($_GET["ARY"]))
{
    resetSerialAry();    
    exit;
}
//used for initial setup of xml, or incase xml fails to open/corrupt
else if (isset($_GET["XML"]))
{
    installCurrencies();
    exit;
}
    
//get current time and date and format
$unformattedDateTime = new DateTime();

$method = $_SERVER['REQUEST_METHOD']; //in HTTP header
//check content type $_SERVER['CONTENT_TYPE'] and treat accordingly
parse_str(file_get_contents("php://input"), $put_vars);
switch ($method)
{
    case 'PUT':
		if (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $put_vars['rate']))
		{				
			$putobj = new currPut($put_vars['code'], $put_vars['name'], $put_vars['rate'], $put_vars['countries'], 
			$unformattedDateTime);
		}
		else
		{
			echo '<pre>', htmlentities(ProduceXmlErr("Put", "2100")), '</pre>';
		}
        break;
    case 'POST':
		if (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $put_vars['rate']))
		{				
			$postobj = new currPost($put_vars['code'], $put_vars['rate'], $unformattedDateTime);
		}
		else
		{
			echo '<pre>', htmlentities(ProduceXmlErr("POST", "2100")), '</pre>';
		}        
        break;
    case 'GET':
		
        //stats mode
        if (isset($_GET["stats"]))
        {
			
            //ensure currency exists first!
            $xmlCurrDoc = new DOMDocument();
            $xmlCurrDoc->load('./xml/Currency.xml');
            $elements = $xmlCurrDoc->getElementsByTagName('curr');
            $found = false;
            foreach ($elements as $node)
            {
                $curr = $node->getAttribute('code');
                if ($curr == $_GET['code'])
                {
                    //to ensure img shown is not cached one
                    $rand = rand();
                    echo "<img src='./php/statMaker.php?code=" . $_GET['code'] . "&rand=" .
                    $rand . "' height='100%' width = '100%'/>";
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                //currency does not exist error
                echo '<pre>', htmlentities(ProduceXmlErr("Get", "2400")), '</pre>';
            }
        }
        //GET Request
        elseif (isset($_GET["amnt"]) && isset($_GET["from"]) && isset($_GET["to"]) &&
                isset($_GET["format"]))
        {
            $countNoParams = 0;
            foreach ($_GET as $key => $value)
            {
                $countNoParams++;
            }
            if ($countNoParams > 4)
            {
                //extra param in uri
                handleError("1200");
                break;
            }
            if (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $_GET["amnt"]))
            {
                try
                {
                    $getobj = new currGet($_GET['amnt'], $_GET['from'], $_GET['to'], $_GET['format'], $unformattedDateTime);
                } catch (Exception $e)
                {
                    handleError("1400");
                }
            } else
            {
                handleError("1300");
            }
        } else
        {
			$wrongPar = false;
			$countNoParams=0;
            foreach ($_GET as $key => $value)
            {
                if ($key != "from" || $key != "to" ||$key != "format" ||$key != "amnt")
					$wrongPar = true;
				$countNoParams++;
				//echo $key;
				
            }
			if ($countNoParams < 4)
				handleError("1100");
			else if ($wrongPar)
				handleError("1200");
			else
			{
				handleError("1100");
			}
        }
        break;
    case 'DELETE':
        $delobj = new currDel($put_vars['code'], $unformattedDateTime);
        break;
    default:
        echo '<pre>', htmlentities(ProduceXmlErr($method, "2000")), '</pre>';
        break;
}

