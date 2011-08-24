<?php
class Reschedule extends \simp\Model
{
    const PENDING = 0;
    const APPROVED = 1;
    const DENIED = 2;

    const MORNING = 1;
    const AFTERNOON = 2;

    public static $tod_opts = array(
        MORNING => "Morning (before Noon)",
        AFTERNOON => "Afternoon (prior to 7:00p)",
    );

    public function Setup()
    {
    }

}
