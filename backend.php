<?php

function sanitize_input($s) {
    // strip control chars, etc.. but leave normal utf-8 like æøå!
    //$s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/u', '', $s);
    $s = strip_tags($s);
    return $s;
}

function list_users($cal) {
    $users = array();
    $events = $cal->events->listEvents('primary');
    foreach ($events['items'] as &$event) {
        if (array_key_exists('summary', $event)) {
            if (array_key_exists('dateTime', $event['start'])) {
                $ss = mb_split(" ", $event['summary']);
                $name = array();
                foreach ($ss as $s) {
                    if (ucfirst($s) == $s) {
                        $name[] = $s;
                    } else {
                        break;
                    }
                }
                $name = implode(" ", $name);
                if ((strlen($name) > 1) && ($name[0] != "?") && !in_array($name, $users)) {
                    $users[] = $name;
                }
            }
        }
    }
    print json_encode($users);
}


function mb_ucfirst($str){
    $str[0] = mb_strtoupper($str[0]);
    return $str;
} 

$known_names = array();
function name_from_summary($str) {
    global $known_names;

    $pos = mb_strpos($str, ' for ');
    if ($pos !== false) {
        // Eks: Hvis vi får "Dan Michael for Ingvild", kutter vi foran "for"
        $str = mb_substr($str, 0, $pos);
    }
    $str = trim($str);

    $parts = mb_split(' ', $str);
    if (count($parts) > 3) {
        // Eks: Vi antar at ingen har navn bestående av mer enn 3 ord
        return ''; 
    }

    $ucparts = array_map('mb_ucfirst', $parts);
    $ucname = implode($ucparts, ' ');
    $lcname = mb_strtolower($str);
    if (in_array($lcname, $known_names)) {
        // Navnet er kjent, så da trenger vi ikke å sjekke om navnet er kapitalisert riktig
        return $ucname;
    }

    if ($ucname == $str) {
        // Hvis alle ordene er skrevet med store forbokstaver, antar vi at det er et navn
        // Matcher "Dan Michael", men ikke "Dan michael"
        $known_names[] = $lcname;
        return $ucname;
    }

    return "";

}

function get_all_events($cal, $timeMin, $timeMax, $user = null) {
    //$date = new DateTime($RFC);
    $output = array(
        'action' => 'get_all_events',
        'start' => $timeMin->format('c'),
        'end' => $timeMax->format('c'),
        'events' => array()
    );
    if ($user !== null) {
        $output['user'] = $user;
    }
    $options = array(
        'singleEvents' => true, 
        'orderBy' => 'startTime',
        'timeMin' => $timeMin->format('c'),
        'timeMax' => $timeMax->format('c')
    );
    $events = $cal->events->listEvents('primary', $options);
    if (array_key_exists('items', $events)) {
        foreach ($events['items'] as &$event) {
            if (array_key_exists('summary', $event)) {
                if (!array_key_exists('dateTime', $event['start'])) {
                    //print "Full day event found!";

                } else {
                    $name = name_from_summary($event['summary']);
                    $dateStart = new DateTime($event['start']['dateTime']);
                    $dateEnd = new DateTime($event['end']['dateTime']);
                    $interval = $dateEnd->diff($dateStart);
                    $duration = $interval->h + $interval->i/60.;
                    $o = array(
                        'summary' => $event['summary'],
                        'start' => $dateStart->format('c'),
                        'end' => $dateEnd->format('c'),
                        'duration' => $duration,
                        'user' => $name
                    );
                    if (($user === null) || ($user === $name)) {
                        array_push($output['events'], $o);
                    }

                    //print $event['summary'] . ': ' . $event['start']['dateTime'] . ' - ' . $event['end']['dateTime'];
                    //if (array_key_exists('recurrence', $event)) {
                    //    foreach ($event['recurrence'] as &$rec) {
                    //        print ' # ' . $rec;
                    //    }
                    //}
                    //print '<br />';
                }
            } else if (array_key_exists('recurringEventId', $event)) {
                //print $event['status'];
            }
        }
    }
    return $output;
}

function summarize($events) {
    $output = array(
        'action' => 'summarize',
        'start' => $events['start'],
        'end' => $events['end'],
        'users' => array()
    );
    foreach ($events['events'] as $event) {
        $u = $event['user'];
        if (!array_key_exists($u, $output['users'])) {
            $output['users'][$u] = 0;
        }
        $output['users'][$u] += $event['duration'];
    }
    return $output;
}

function get_report($cal, $user, $startmonth, $startyear, $endmonth = -1, $endyear = -1) {
    $startmonth = intval($startmonth);
    $startyear = intval($startyear);
    if ($endmonth == -1 || $endyear == -1) {
        $endmonth = $startmonth;
        $endyear = $startyear;
    }
    $endmonth++;
    if ($endmonth == 13) {
        $endyear++;
        $endmonth = 1;
    }
    $startmonth = ($startmonth < 10) ? "0$startmonth" : "$startmonth";
    $endmonth = ($endmonth < 10) ? "0$endmonth" : "$endmonth";
    $timeMin = new DateTime("$startyear:$startmonth:01 00:00:00");
    $timeMax = new DateTime("$endyear:$endmonth:00 23:59:59"); 
    # dates can overflow, Day 0 means the last day of previous month

    if ($user == "_summary") {
        $ev = get_all_events($cal, $timeMin, $timeMax);
        $ev = summarize($ev);
        print json_encode($ev);
        exit();
    }
    $ev = get_all_events($cal, $timeMin, $timeMax, $user);
    print json_encode($ev);
    exit();
    $output = array();


    //$date = new DateTime($RFC);
    $options = array(
        'singleEvents' => true, 
        'orderBy' => 'startTime',
        'timeMin' => $timeMin->format('c'),
        'timeMax' => $timeMax->format('c')
    );
    $events = $cal->events->listEvents('primary', $options);
    if (array_key_exists('items', $events)) {
    foreach ($events['items'] as &$event) {
        if (array_key_exists('summary', $event)) {
            if (!array_key_exists('dateTime', $event['start'])) {
                //print "Full day event found!";

            } else {
                $ss = mb_split(" ", $event['summary']);
                $name = array();
                foreach ($ss as $s) {
                    if (ucfirst($s) == $s) {
                        $name[] = $s;
                    } else {
                        break;
                    }
                }
                $name = implode(" ", $name);
                if ($name == $user) {
                    array_push($output, array(
                        'summary' => $event['summary'],
                        'start' => $event['start']['dateTime'],
                        'end' => $event['end']['dateTime']
                    ));
                }

                //print $event['summary'] . ': ' . $event['start']['dateTime'] . ' - ' . $event['end']['dateTime'];
                //if (array_key_exists('recurrence', $event)) {
                //    foreach ($event['recurrence'] as &$rec) {
                //        print ' # ' . $rec;
                //    }
                //}
                //print '<br />';
            }
        } else if (array_key_exists('recurringEventId', $event)) {
            //print $event['status'];
        }
    }
    }
    print json_encode($output);
}

if (isset($_GET['get'])) {
    switch ($_GET['get']) {
        case 'users':
            list_users($cal);
            exit();
        
        case 'report':
            if (array_key_exists('endmonth', $_GET)) {
                get_report($cal, sanitize_input($_GET['user']), 
                    sanitize_input($_GET['month']), sanitize_input($_GET['year']), 
                    sanitize_input($_GET['endmonth']), sanitize_input($_GET['endyear']));
            } else {
                get_report($cal, sanitize_input($_GET['user']), sanitize_input($_GET['month']), sanitize_input($_GET['year']));
            }
            exit();
    }
}

?>
