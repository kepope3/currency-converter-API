<?php
#############################################################
# file: 	statMaker.php                               #
# author:	Keith Pope                                  #
# purpose:	Uses phplot: http://www.phplot.com/ to      #
#   create graph of rates                                   #
#############################################################
include('./phplot/phplot.php');

//Define the object
$plot = new PHPlot();
$xmlCurrDoc = new DOMDocument();
$xmlCurrDoc->load('../xml/Currency.xml');
$array_ = Array();
$array1 = Array();
$elements = $xmlCurrDoc->getElementsByTagName('curr');
$x = -1;
foreach ($elements as $node)
{
    $curr = $node->getAttribute('code');
    if ($curr == $_GET['code'])
    {        
        foreach ($node->childNodes as $child)
        {
            if ($child->nodeName == "rateAt")
            {
                $at = $child->getAttribute('at');
                foreach ($child->childNodes as $childRate)
                {
                    if ($childRate->nodeName == "rate")
                    {
                        $rate = $childRate->nodeValue;
                    }
                }
                array_push($array_, array(
                    "",
                    $rate
                        )
                );
                array_push($array1, array(
                    $at,
                    $rate
                        )
                );
                $x++;
            }
            
        }
    }
}

$plot->SetDataValues($array_);

//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

//format dates
$first = $array1[0][0];
$last = $array1[$x][0];
$startDate = gmdate("F j, Y, g:i a", $first);
$lastDate = gmdate("F j, Y, g:i a", $last);
$plot->SetTitle("$startDate - " . $lastDate);


$plot->DrawGraph();
