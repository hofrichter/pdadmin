<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(0); }
 /*
  * @since 11/2015
  * @author Sven Hofrichter - 2015-11-16 - initial version
  */
require_once(__DIR__ . '/Logger.incl.php');

/**
 * @param $configuration is the configuration array 
 */
function configure(array $configuration) {
    Logger::configure($configuration);
}

/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function trace($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(TRACE, $caller, $line, $msg, $exception);
}
/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function debug($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(DEBUG, $caller, $line, $msg, $exception);
}
/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function info($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(INFO, $caller, $line, $msg, $exception);
}
/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function warn($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(WARN, $caller, $line, $msg, $exception);
}
/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function error($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(ERROR, $caller, $line, $msg, $exception);
}
/**
 * @param String, $caller is the value of __FILE__ or __CLASS__ in the calling php file
 * @param Number, $line the line number, where the message was generated
 * @param String, $msg is the message to write into the logfile
 * @param Exception, $exception (optional) the exception to write to logfile
 * @return boolean <code>true</code> as soon, as the configured loglevel was
 *         set to write the message AND the message was successfuly written
 *         to logfile, otherwise <code>false</code>.
 */
function fatal($caller, $line, $msg, $exception = NULL) {
    return Logger::logit(FATAL, $caller, $line, $msg, $exception);
}
?>
