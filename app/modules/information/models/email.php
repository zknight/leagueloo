<?php
require_once "category.php";
class Email extends \simp\Model
{

    // fields
    // - address
    // - category_id
    // - param

    public static $genders = array(0 => 'boys and girls', 1 => 'boys', 2 => 'girls');
    public static $ages = array(
        4 => 'u5', 5 => 'u6', 6 => 'u7', 7 => 'u8', 8 => 'u9',
        9 => 'u10', 10 => 'u11', 11 => 'u12', 12 => 'u13', 13 => 'u14',
        14 => 'u15', 15 => 'u16', 16 => 'u17', 17 => 'u18');

    public static $end_ages = array(
        0 => 'only',
        4 => 'to u5', 5 => 'to u6', 6 => 'to u7', 7 => 'to u8', 8 => 'to u9',
        9 => 'to u10', 10 => 'to u11', 11 => 'to u12', 12 => 'to u13', 13 => 'to u14',
        14 => 'to u15', 15 => 'to u16', 16 => 'to u17', 17 => 'to u18');
    public function Setup()
    {
    }

}
