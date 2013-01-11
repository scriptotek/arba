<?php
require_once('base.php');
require_once('backend.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Arbeidstid</title>
    <link rel="stylesheet" href="common.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="datejs-read-only/build/date-nb-NO.js"></script>
    <script src="jQueryRotateCompressed.2.2.js"></script>
    <!--<script src="https://apis.google.com/js/client.js"></script>-->
    <?php
    if ($client->getAccessToken()) {
        print "<script src=\"main.js\"></script>\n";
    }
    ?>
</head>
<body>
<div id="header">
<!--<img id="uiologo" src="UiO_Segl_72dpi.png" style="float:right;" />-->
<h1>Arbeidstid fra Google Calendar</h1>
<?php
if ($client->getAccessToken()) {
    print "Hei fortapte vasall! ";
    $logoutUrl = './?logout';
    print "<a class='logout' href='$logoutUrl'>Logg ut</a>";
?>
<form method="post">
Viser
<select id="user" name="user">
    <option value="_summary">sammendrag</option>
    <option value="">uknyttede kalenderposter</option>
    <option disabled="disabled">----------------</option>
</select>
for
<select id="month" name="month">
<?php
    $cmonth = intval(date('n'));
    for ($month = 1; $month <= 12; $month++) {
        $def = ($month == $cmonth) ? " selected=\"selected\"" : "";
        echo "<option value=\"$month\"$def>$months[$month]</option>";
    }
?>
</select>
<select id="year" name="year">
<?php
    $cyear = intval(date('Y'));
    for ($year = $cyear -1; $year < $cyear + 2; $year++) {
        $def = ($year == $cyear) ? " selected=\"selected\"" : "";
        echo "<option value=\"$year\"$def>$year</option>";
    }
?>
</select>
<a href="#" class="expand">+</a>
<span id="enddate">
    til
    <select id="endmonth" name="endmonth">
    <?php
        $cmonth = intval(date('n'));
        for ($month = 1; $month <= 12; $month++) {
            $def = ($month == $cmonth) ? " selected=\"selected\"" : "";
            echo "<option value=\"$month\"$def>$months[$month]</option>";
        }
    ?>
    </select>
    <select id="endyear" name="endyear">
    <?php
        $cyear = intval(date('Y'));
        for ($year = $cyear -1; $year < $cyear + 2; $year++) {
            $def = ($year == $cyear) ? " selected=\"selected\"" : "";
            echo "<option value=\"$year\"$def>$year</option>";
        }
    ?>
    </select>
</span>

<span class="spinner"><img src="spinner.gif" style="width:15px; height:15px;" /></span>
</form>
<?php
    //$calList = $cal->calendarList->listCalendarList();
} else {
    $authUrl = $client->createAuthUrl();
    print "<a class='login' href='$authUrl'>Logg inn</a>";
}

?>
</div>
<table class="main" cellspacing="0" cellpadding="0">

</table>
</body>
</html>
