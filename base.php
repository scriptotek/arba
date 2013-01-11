<?php
// Check if magic_quotes_runtime is active
if(get_magic_quotes_runtime()) {
    // Deactivate
    set_magic_quotes_runtime(false);
}
date_default_timezone_set("Europe/Oslo");
mb_internal_encoding('utf8');

function my_error_handler($errno, $errstr, $errfile, $errline){
    $errno = $errno & error_reporting();
    if($errno == 0) return;
    if(!defined('E_STRICT'))            define('E_STRICT', 2048);
    if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
    print "<pre>\n<b>";
    switch($errno){
        case E_ERROR:               print "Error";                  break;
        case E_WARNING:             print "Warning";                break;
        case E_PARSE:               print "Parse Error";            break;
        case E_NOTICE:              print "Notice";                 break;
        case E_CORE_ERROR:          print "Core Error";             break;
        case E_CORE_WARNING:        print "Core Warning";           break;
        case E_COMPILE_ERROR:       print "Compile Error";          break;
        case E_COMPILE_WARNING:     print "Compile Warning";        break;
        case E_USER_ERROR:          print "User Error";             break;
        case E_USER_WARNING:        print "User Warning";           break;
        case E_USER_NOTICE:         print "User Notice";            break;
        case E_STRICT:              print "Strict Notice";          break;
        case E_RECOVERABLE_ERROR:   print "Recoverable Error";      break;
        default:                    print "Unknown error ($errno)"; break;
    }
    print ":</b> <i>$errstr</i> in <b>$errfile</b> on line <b>$errline</b>\n";
    if(function_exists('debug_backtrace')){
        //print "backtrace:\n";
        $backtrace = debug_backtrace();
        array_shift($backtrace);
        foreach($backtrace as $i=>$l){
            print "[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
            if($l['file']) print " in <b>{$l['file']}</b>";
            if($l['line']) print " on line <b>{$l['line']}</b>";
            print "\n";
        }
    }
    print "\n</pre>";
    if(isset($GLOBALS['error_fatal'])){
        if($GLOBALS['error_fatal'] & $errno) die('fatal');
    }
}
function error_fatal($mask = NULL){
    if(!is_null($mask)){
        $GLOBALS['error_fatal'] = $mask;
    }elseif(!isset($GLOBALS['die_on'])){
        $GLOBALS['error_fatal'] = 0;
    }
    return $GLOBALS['error_fatal'];
}

function exception_handler($exception) {
    echo "Uncaught exception: " , $exception->getMessage(), "\n";
}

error_reporting(E_ALL);      // will report all errors
set_error_handler('my_error_handler');
set_exception_handler('exception_handler');
error_fatal(E_ALL^E_NOTICE); // will die on any error except E_NOTICE

//set_exception_handler(moo);
//throw new Exception('Uncaught Exception occurred');

require_once('./google-api-php-client/src/Google_Client.php');
require_once('./google-api-php-client/src/contrib/Google_CalendarService.php');
session_start();

$client = new Google_Client();
$client->setApplicationName("DanmicholoTest");

$secrets_file = 'client_secrets.json';
if (!$file_contents = @file_get_contents($secrets_file)) {
    die("Fant ikke filen $secrets_file");
}
//print phpversion();
//print get_magic_quotes_runtime();
$secrets = json_decode($file_contents, true);
//print "<pre>";
//print_r($file_contents);
//print_r($secrets);

$client->setClientId($secrets['installed']['client_id']);
$client->setClientSecret($secrets['installed']['client_secret']);
$client->setRedirectUri('http://folk.uio.no/dmheggo/arbeidstid/');
$client->setDeveloperKey($secrets['api_key']);

$cal = new Google_CalendarService($client);
if (isset($_GET['logout'])) {
    unset($_SESSION['token']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    exit();
}

// If the user has been redirected back to our page with an authorization code, exchange the code for an access token. 
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    // Do note you should replace the session storage of the OAuth key with a real storage (on disk, in your mysql database, etc) before releasing your app. 
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

//if ($client->getAccessToken()) {
//    $_SESSION['token'] = $client->getAccessToken();
//}

$months = array('','Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember');

?>
