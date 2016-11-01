<?php
drupal_set_message ("hellooooooooooo");
/**
* The purpose of this central config file is configuring all examples
* in one place with minimal work for your working environment
* Just configure all the items in this config according to your environment
* and rename the file to config.php
*
* PHP Version 5
*
* @file config.php
* @category Authentication
* @package PhpCAS
* @author Joachim Fritschi <jfritschi@freenet.de>
* @author Adam Franco <afranco@middlebury.edu>
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
* @link https://wiki.jasig.org/display/CASC/phpCAS
*/

///////////////////////////////////////
// Basic Config of the phpCAS client //
///////////////////////////////////////
// Full Hostname of your CAS Server
$cas_host = 'sso-test.sch.gr';
// Context of the CAS Server
//$cas_context = '/cas';
$cas_context = '';
// Port of your CAS server. Normally for a https server it's 443
$cas_port = 443;
// Path to the ca chain that issued the cas server certificate
//$cas_server_ca_cert_path = '/path/to/cachain.pem';
$cas_server_ca_cert_path = '';
//////////////////////////////////////////
// Advanced Config for special purposes //
//////////////////////////////////////////
// The "real" hosts of clustered cas server that send SAML logout messages
// Assumes the cas server is load balanced across multiple hosts
$cas_real_hosts = array('cas-real-1.example.com', 'cas-real-2.example.com');
// Client config for cookie hardening
$client_domain = '127.0.0.1';
$client_path = 'phpcas';
$client_secure = true;
$client_httpOnly = true;
$client_lifetime = 0;
/*
// Database config for PGT Storage
$db = 'pgsql:host=localhost;dbname=phpcas';
//$db = 'mysql:host=localhost;dbname=phpcas';
$db_user = 'phpcasuser';
$db_password = 'mysupersecretpass';
$db_table = 'phpcastabel';
$driver_options = '';
*/
///////////////////////////////////////////
// End Configuration -- Don't edit below //
///////////////////////////////////////////
// Generating the URLS for the local cas example services for proxy testing
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
$curbase = 'https://' . $_SERVER['SERVER_NAME'];
} else {
$curbase = 'http://' . $_SERVER['SERVER_NAME'];
}
if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
$curbase .= ':' . $_SERVER['SERVER_PORT'];
}
$curdir = dirname($_SERVER['REQUEST_URI']) . "/";
// CAS client nodes for rebroadcasting pgtIou/pgtId and logoutRequest
$rebroadcast_node_1 = 'http://cas-client-1.example.com';
$rebroadcast_node_2 = 'http://cas-client-2.example.com';
// access to a single service
$serviceUrl = $curbase . $curdir . 'example_service.php';
// access to a second service
$serviceUrl2 = $curbase . $curdir . 'example_service_that_proxies.php';
$pgtBase = preg_quote(preg_replace('/^http:/', 'https:', $curbase . $curdir), '/');
$pgtUrlRegexp = '/^' . $pgtBase . '.*$/';
$cas_url = 'https://' . $cas_host;
if ($cas_port != '443') {
$cas_url = $cas_url . ':' . $cas_port;
}
$cas_url = $cas_url . $cas_context;
// Set the session-name to be unique to the current script so that the client script
// doesn't share its session with a proxied script.
// This is just useful when running the example code, but not normally.
session_name(
'session_for:'
. preg_replace('/[^a-z0-9-]/i', '_', basename($_SERVER['SCRIPT_NAME']))
);
// Set an UTF-8 encoding header for internation characters (User attributes)
header('Content-Type: text/html; charset=utf-8');



/**
* Example for a simple cas 2.0 client
*
* PHP Version 5
*
* @file example_simple.php
* @category Authentication
* @package PhpCAS
* @author Joachim Fritschi <jfritschi@freenet.de>
* @author Adam Franco <afranco@middlebury.edu>
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
* @link https://wiki.jasig.org/display/CASC/phpCAS
*/
// Load the settings from the central config file
//require_once 'config.php';
// Load the CAS lib
require_once $phpcas_path . '/CAS.php';
// Enable debugging
phpCAS::setDebug();
// Enable verbose error messages. Disable in production!
phpCAS::setVerbose(true);
// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();
// force CAS authentication
phpCAS::forceAuthentication();
// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().
//#########################################
drupal_set_message ("hellooooooooooo:".phpCAS::getUser());
echo "<br>USER: ".phpCAS::getUser()."<br>";
var_dump(phpCAS::getAttributes());

foreach (phpCAS::getAttributes() as $key => $value) {
if (is_array($value)) {
echo '<li>', $key, ':<ol>';
foreach($value as $item) {
      echo '<li><strong>', $item, '</strong></li>';
      drupal_set_message ($item);
    }
echo '</ol></li>';

} else {
    echo '<li>', $key, ': <strong>', $value, '</strong></li>';
  }
}


//##########################################
// logout if desired
if (isset($_REQUEST['logout'])) {
phpCAS::logout();
}
// for this test, simply print that the authentication was successfull
?>
<html>
<head>
<title>phpCAS simple client</title>
</head>
<body>
<h1>Successfull Authentication!</h1>
<?php require 'script_info.php' ?>
<p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
<p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
<p><a href="?logout=">Logout</a></p>
</body>
</html>