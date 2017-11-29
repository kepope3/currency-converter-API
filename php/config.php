<?php
#############################################################
# file: 	config.php                                  #
# author:	Keith Pope                                  #
# purpose:	config file for RESTful currency conversion #                              
#############################################################
if (PASSCODE != "#@#kmlksm567567566758sdasdSDSD")
{
    exit;
}
# set timezone
@date_default_timezone_set("GMT");
# set URL's constants for external data
define('RATES_URL', 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote?format=xml');
define('COUNTRIES_URL', 'http://www.currency-iso.org/dam/downloads/lists/list_one.xml');
define('RATES', 'data/rates.xml');
define('COUNTRIES', 'data/countries.xml');
define('UPDATE_INTERVAL', 43200);

# error_hash to hold error numbers and messages
$GLOBALS['error_hash'] = array(
    1000 => 'Currency type not recognized',
    1100 => 'Required parameter is missing',
    1200 => 'Parameter not recognized',
    1300 => 'Currency amount mustbe a decimal number',
    1400 => 'Error in service',
    2000 => 'Method not recognized or is missing',
    2100 => 'Rate in wrong format or is missing',
    2200 => 'Currency code in wrong format or is missing',
    2300 => 'Country name in wrong format or is missing',
    2400 => 'Currency code not found for update',
    2500 => 'Error in service'
);

//get ccodes ary
$filePath = './currency_backup/serializedAry.txt';
$GLOBALS['ccodes'] = unserialize(file_get_contents($filePath));

//allows new codes to be added to serialized array
function addCurrCodeToAry($currCode)
{
    //push item onto array
    array_push($GLOBALS['ccodes'], $currCode);
    $filePath = './currency_backup/serializedAry.txt';
    //save
    file_put_contents($filePath, serialize($GLOBALS['ccodes']));
}

//used for very first setup
function resetSerialAry()
{
    //country codes for initial setup
    $ccodes = array(
        'CAD', 'CHF', 'CNY', 'DKK',
        'EUR', 'GBP', 'HKD', 'HUF',
        'INR', 'JPY', 'MXN', 'MYR',
        'NOK', 'NZD', 'PHP', 'RUB',
        'SEK', 'SGD', 'THB', 'TRY',
        'USD', 'ZAR');
    $filePath = './currency_backup/serializedAry.txt';
    file_put_contents($filePath, serialize($ccodes));
}

############################################################
# Error Management                                         
# Create Error Handler

function crest_error_handler($e_number, $e_message, $e_file, $e_line, $e_vars)
{
    global $debug;

    $contact_email = 'prakash.chatterjee@uwe.ac.uk';

    # Build the error message.
    $message = "An error occurred in script '$e_file' on line $e_line: \n<br />$e_message\n<br />";

    # Add the date and time.
    $message .= "Date/Time: " . date('n-j-Y H:i:s') . "\n<br />";

    # Append $e_vars to the $message.
    $message .= "<pre>" . print_r($e_vars, 1) . "</pre>\n<br />";

    if ($debug)
    { # show the error.
        echo '<p class="error">' . $message . '</p>';
    } else
    {

        # Log the error:
        error_log($message, 1, $contact_email); #send email.
        # Only print an error message if the error isn't a notice or strict.
        if (($e_number != E_NOTICE) && ($e_number < 2048))
        {
            echo '<p class="error">A system error occurred. We apologize for the inconvenience.</p>';
        }
    } # End of $debug IF.
	set_error_handler ('crest_error_handler');
}


