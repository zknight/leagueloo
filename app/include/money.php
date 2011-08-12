<?php

// use .1 cents as scaling (div by 1000)
function ToDollars($amount)
{
    // convert to string
    // save sign
    $neg = false;
    if ($amount < 0) 
    {
        $neg = true;
        $amount = -$amount;
    }
    $c = (integer)round(($amount % 1000) / 10);
    $amount = $amount - ($amount % 1000);
    $d = $amount / 1000;
    $d = $neg ? -$d : $d;
    return sprintf("$%d.%02d", $d, $c);
}

function FromDollars($amount)
{
    // input is treated as string
    // remove leading dollar sign and check for "."
    $d = 0;
    $c = 0;
    $amount = str_replace('$', '', $amount);
    list ($d, $c) = explode('.', $amount);
    $new_amt = ($d * 1000) + ($c * 10);
    return $new_amt;
}
