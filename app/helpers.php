<?

function GetArticlePath($action, $article)
{
    return Path::Relative("{$article->entity_name}/news/{$action}/{$article->short_title}");
}

function GetTeamPath($action, $team)
{
    return "{$team->program_name}/teams/{$team->gender}/{$team->division}/{$team->NameForLink()}"; 
}

function CalendarNavDates($date)
{
    $dates = array();
    $dates['this_month'] = array('text' => $date->format("F"), 'link' => $date->format("m/Y"));
    $dates['this_year'] = array('text' => $date->format("Y"), $date->format("m/Y"));
    $next_year_dt = clone $date;
    $next_year_dt->add(new DateInterval("P1Y"));
    $dates['next_year'] = array(
        'text' => $next_year_dt->format("Y"), 
        'link' => $next_year_dt->format("m/Y")
    );
    $next_month_dt = clone $date;
    $next_month_dt->add(new DateInterval("P1M"));
    $dates['next_month'] = array(
        'text' => $next_month_dt->format("F"),
        'link' => $next_month_dt->format("m/Y")
    );
    $prev_year_dt = clone $date;
    $prev_year_dt->sub(new DateInterval("P1Y"));
    $dates['prev_year'] = array(
        'text' => $prev_year_dt->format("Y"),
        'link' => $prev_year_dt->format("m/Y")
    );
    $prev_month_dt = clone $date;
    $prev_month_dt->sub(new DateInterval("P1M"));
    $dates['prev_month'] = array(
        'text' => $prev_month_dt->format("F"),
        'link' => $prev_month_dt->format("m/Y")
    );
    return $dates;
}

function ShowCalendar($date, $days, $can_add = false)
{
    $nav_dates = CalendarNavDates($date);
    $html  = "<h1>" . $date->format("F Y") . "</h1>";
    $html .= "<table class=\"calendar\">\n";
    $html .= "    <tr class='row-1'>\n";
    $html .= "        <td colspan='2'>\n";
    $html .= l("&lt;&lt; {$nav_dates['prev_year']['text']}", Path::content_event_calendar($nav_dates['prev_year']['link'])); 
    $html .= l("&lt; {$nav_dates['prev_month']['text']}", Path::content_event_calendar($nav_dates['prev_month']['link']));
    $html .= "\n       </td>\n";
    $html .= "        <td colspan='3'>&nbsp;</td>\n";
    $html .= "        <td colspan='2' class='rt'>\n";
    $html .= l("{$nav_dates['next_month']['text']} &gt;", Path::content_event_calendar($nav_dates['next_month']['link'])); 
    $html .= l("{$nav_dates['next_year']['text']} &gt;&gt;", Path::content_event_calendar($nav_dates['next_year']['link']));
    $html .= "\n       </td>\n";
    $html .= "    </tr>\n";
    $html .= "    <tr>\n";
    $html .= "        <th>Sun</th> <th>Mon</th> <th>Tue</th> <th>Wed</th> <th>Thu</th> <th>Fri</th> <th>Sat</th>\n";
    $html .= "    </tr>\n";
    $html .= "    <tr>\n";
    $d = 0;
    $numdays = count($days);
    foreach ($days as $day)
    {
        if ($d % 7 == 0)
        {
            $html .= "    </tr>\n";
            if ($d < $numdays) 
            {
                $html .= "    <tr>\n";
            }
        }
        $html .= "        <td class=\"{$day['class']}\">\n";
        $html .= "            <table class='day'>\n";
        $html .= "                <tr>\n";
        $html .= "                    <td class='date'>{$day['d']}</td>\n";
        $html .= "                    <td>\n";
        if ($can_add)
            $html .= l("add event", Path::content_event_add($day['y'], $day['m'], $day['d']));
        $html .= "                   </td>\n";
        $html .= "                </tr>\n";
        $html .= "            </table>\n";
        $html .= "            <table class='event'>\n";
        foreach ($day['events'] as $event)
        {
            $html .= "                <tr>\n";
            $time = $event->all_day ? "all day" : FormatDateTime($event->start_time, "H:i");
            $html .= "                    <td style=\"width:25px;text-align:left;\">$time</td>\n";
            $html .= "                    <td style=\"width:75px;\">";
            $html .= l($event->short_title, Path::content_event_edit($event->id)); 
            $html .= "                    </td>\n";
            $html .= "                </tr>\n";
        }
        $html .= "            </table>\n";
        $html .= "        </td>\n";
        $d++;
    }
    $html .= "</table>\n";
    return $html;
}
