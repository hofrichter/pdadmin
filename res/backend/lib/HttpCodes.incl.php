<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }
/**
 * <p>
 * This file defines all http-codes as php constants and uses "HTTP_" as prefix.
 * Additional you find the http protocol version, saved in constant HTTP_VERSION.
 * </p>
 * 
 * @see http://de.wikipedia.org/wiki/HTTP-Statuscode
 * @since 12/2014
 * @author Sven Hofrichter - 2014-12-11 - initial version
 */

define('HTTP_GET', 'get');
define('HTTP_PUT', 'put');
define('HTTP_POST', 'post');
define('HTTP_DELETE', 'delete');
define('HTTP_OPTIONS', 'options');


// the version of the HTTP-Protocol
define('HTTP_VERSION', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

// Informations
define('HTTP_100', '100 Continue');
define('HTTP_101', '101 Switching Protocols');
define('HTTP_102', '102 Processing');

// Success operations
define('HTTP_200', '200 OK');
define('HTTP_201', '201 Created');
define('HTTP_202', '202 Accepted');
define('HTTP_203', '203 Non-Authoritative Information');
define('HTTP_204', '204 No Content');
define('HTTP_205', '205 Reset Content');
define('HTTP_206', '206 Partial Content');
define('HTTP_207', '207 Multi-Status');
define('HTTP_208', '208 Already Reported');
define('HTTP_226', '226 IM Used');

// Redirects
define('HTTP_300', '300 Multiple Choices');
define('HTTP_301', '301 Moved Permanently');
define('HTTP_302', '302 Found');
define('HTTP_303', '303 See Other');
define('HTTP_304', '304 Not Modified');
define('HTTP_305', '305 Use Proxy');
define('HTTP_306', '306 Switch Proxy');
define('HTTP_307', '307 Temporary Redirect');
define('HTTP_308', '308 Permanent Redirect');

// client error
define('HTTP_400', '400 Bad Request');
define('HTTP_401', '401 Unauthorized');
define('HTTP_402', '402 Payment Required');
define('HTTP_403', '403 Forbidden');
define('HTTP_404', '404 Not Found');
define('HTTP_405', '405 Method Not Allowed');
define('HTTP_406', '406 Not Acceptable');
define('HTTP_407', '407 Proxy Authentication Required');
define('HTTP_408', '408 Request Time-out');
define('HTTP_409', '409 Conflict');
define('HTTP_410', '410 Gone');
define('HTTP_411', '411 Length Required');
define('HTTP_412', '412 Precondition Failed');
define('HTTP_413', '413 Request Entity Too Large');
define('HTTP_414', '414 Request-URL Too Long');
define('HTTP_415', '415 Unsupported Media Type');
define('HTTP_416', '416 Requested range not satisfiable');
define('HTTP_417', '417 Expectation Failed');
define('HTTP_418', '418 I’m a teapot');
define('HTTP_420', '420 Policy Not Fulfilled');
define('HTTP_421', '421 connections from your internet address');
define('HTTP_422', '422 Unprocessable Entity');
define('HTTP_423', '423 Locked');
define('HTTP_424', '424 Failed Dependency');
define('HTTP_425', '425 Unordered Collection');
define('HTTP_426', '426 Upgrade Required');
define('HTTP_428', '428 Precondition Required');
define('HTTP_429', '429 Too Many Requests');
define('HTTP_431', '431 Request Header Fields Too Large');
define('HTTP_444', '444 No Response');
define('HTTP_449', '449 The request should be retried after doing the appropriate action');
define('HTTP_451', '451 Unavailable For Legal Reasons');

// server error
define('HTTP_500', '500 Internal Server Error');
define('HTTP_501', '501 Not Implemented');
define('HTTP_502', '502 Bad Gateway');
define('HTTP_503', '503 Service Unavailable');
define('HTTP_504', '504 Gateway Time-out');
define('HTTP_505', '505 HTTP Version not supported');
define('HTTP_506', '506 Variant Also Negotiates');
define('HTTP_507', '507 Insufficient Storage');
define('HTTP_508', '508 Loop Detected');
define('HTTP_509', '509 Bandwidth Limit Exceeded');
define('HTTP_510', '510 Not Extended');

// propitare codes
define('HTTP_900', '900'); 
define('HTTP_901', '901'); 
define('HTTP_902', '902'); 
define('HTTP_903', '903'); 
define('HTTP_904', '904'); 
define('HTTP_905', '905');
define('HTTP_906', '906');
define('HTTP_907', '907');
define('HTTP_950', '950');
?>
