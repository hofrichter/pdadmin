<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); error_log("SECURITY-WARNING: Someone tried to access '" . __FILE__ . "' directly!", 0); exit(1); }

 /*
  * This is a very simple logging implementation. The main goal of this class,
  * was to configure LogLevels based on file and functions.
  *
  * @since 11/2015
  * @author Sven Hofrichter - 2015-11-16 - initial version
  */
define('TRACE', 20);
define('DEBUG', 30);
define('INFO',  40);
define('WARN',  50);
define('ERROR', 60);
define('FATAL', 70);

/**
 * This function enables this logger as a global exception handler. All
 * unhandled exceptions will be sent as a fatal message to the Logger. Unhandled
 * exceptions are not enclosed by a surrounding try-catch-block and is a ugly
 * codingstyle.
 */
function logger_exception_handler($exception) {
    // debug_print_backtrace();
    Logger::logit(FATAL, $exception->getFile(), $exception->getLine(), 'An unhandled exception occured! Change your coding to avoid this universal and not self explaining logging.', $exception);
}
/**
 * This function enables this logger as a global error handler. The value of 
 * $errorno get mapped to a valid Log-Level. There is a mapping for all
 * known errorcodes, but NOT ALL errors will be sent to the error-handler by
 * PHP. You'll find unknown/unmapped errors as a FATAL message in the log file.
 * The error message will be prefixed with the string representation of the
 * underlying error code too.
 */
function logger_error_handler($errno, $errstr, $errfile, $errline, $errcontext = NULL) {
    if (!(error_reporting() & $errno)) {
        // do not print this message
        return;
    }
    $error_codes = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_ALL => 'E_ALL'
    );
    $log_level = array(
        E_ERROR => ERROR,
        E_WARNING => WARN,
        E_PARSE => ERROR,
        E_NOTICE => INFO,
        E_CORE_ERROR => ERROR,
        E_CORE_WARNING => WARN,
        E_COMPILE_ERROR => FATAL,
        E_COMPILE_WARNING => WARN,
        E_USER_ERROR => ERROR,
        E_USER_WARNING => WARN,
        E_USER_NOTICE => INFO,
        E_STRICT => WARN,
        E_RECOVERABLE_ERROR => ERROR,
        E_DEPRECATED => DEBUG,
        E_USER_DEPRECATED => DEBUG,
        E_ALL => INFO
    );
    $logLevel = 'FATAL';
    if (array_key_exists($errno, $error_codes)) {
        $logLevel = preg_match('/_ERROR$/', $error_codes[$errno]) ? ERROR : '';
        $errstr = "[" . $error_codes[$errno] . "] $errstr";

    }
    Logger::logit($logLevel, $errfile, $errline, $errstr);
}

class Logger {

    private $caller = '<unknown-source>';
    
    private static $LOG_LEVEL_PRIORITY = null;

    private static $CONFIG = array();

    private static $LOG_LEVELS = array(
            TRACE => 'TRACE',
            DEBUG => 'DEBUG',
            INFO  => 'INFO ',
            WARN  => 'WARN ',
            ERROR => 'ERROR',
            FATAL => 'FATAL'
        );

    /**
     * This method returns a Logger instance and was build, to do migrations
     * from other logging frameworks much easier.
     * @return Logger instance
     */
    public static function getLogger($caller) {
        return new Logger($caller);
    }

    /**
     * this constructor initializes the Logging for the given $caller.
     * 
     * @param String $caller ist the value of __FILE__ or __CLASS__ of the
     *        instantiating class/file
     */
    public function __construct($caller) {
        $this->caller = $caller;
        self::configure(array());
    }

    /**
     * Use this method to set or override the configuration. The function merges
     * the new configuration into the existing by default. Set the value of
     * $merge to false in cases, when the settings should overwritten by the new
     * ones. Attention: The configuration is static and will be active in all
     * Logger-instances.
     * <br />
     * The configuration array should have this structure for a proper work:
     * $arr = array (
     *     'LOG_FILE' => '/path/to/<logfile-name-pattern>',
     *     '__default__' => WARN,
     *     'path/to/file.php' => INFO,
     *     'path/to/file.php#functionName' => DEBUG
     * );
     *
     * @param Array, $configuration is the configuration to use (to merge)
     * @param Boolean $merge defines, whether the new configuration should be
     *        merged into or overwrite the existing
     * @return the new configuration will be returned
     */
    public static function configure(array $configuration, $merge = true) {
        if ($merge) {
            self::$CONFIG = array_merge(self::$CONFIG, $configuration);
        } else {
            self::$CONFIG = $configuration;
        }
        if (!is_array(self::$CONFIG)) {
            self::$CONFIG = array();
        }
        if (!array_key_exists('DEFAULT_LOG_LEVEL', self::$CONFIG)) {
            self::$CONFIG['DEFAULT_LOG_LEVEL'] = ERROR;
        }
        if (!array_key_exists('LOG_FILE', self::$CONFIG)) {
            self::$CONFIG['LOG_FILE'] = '/var/log/sugarfree/sugarfree-%Y%m%d.log';
        }
        if (!array_key_exists('STDOUT_IN_CLI_MODE', self::$CONFIG)) {
            self::$CONFIG['STDOUT_IN_CLI_MODE'] = 'false';
        }
        if (array_key_exists('ENABLE_GLOBAL_ECXEPTION_HANDLER', self::$CONFIG)) {
            set_exception_handler('logger_exception_handler');
        }
        if (array_key_exists('ENABLE_GLOBAL_ERROR_HANDLER', self::$CONFIG)) {
            set_error_handler('logger_error_handler');
        }
        if (!@is_dir(@dirname(self::$CONFIG['LOG_FILE']))) {
            @mkdirs(@dirname(self::$CONFIG['LOG_FILE']), 0777, true);
        }
        if (!@is_dir(@dirname(self::$CONFIG['LOG_FILE']))) {
            error_log('Directory for LOG-Files not found and can not be created!');
            exit(1);
        }
        return self::$CONFIG;
    }

    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function trace($line, $msg, $exception = NULL) {
        return self::logit(TRACE, $this->caller, $line, $msg, $exception);
    }
    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function debug($line, $msg, $exception = NULL) {
        return self::logit(DEBUG, $this->caller, $line, $msg, $exception);
    }
    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function info($line, $msg, $exception = NULL) {
        return self::logit(INFO, $this->caller, $line, $msg, $exception);
    }
    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function warn($line, $msg, $exception = NULL) {
        return self::logit(WARN, $this->caller, $line, $msg, $exception);
    }
    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function error($line, $msg, $exception = NULL) {
        return self::logit(ERROR, $this->caller, $line, $msg, $exception);
    }
    /**
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public function fatal($line, $msg, $exception = NULL) {
        return self::logit(FATAL, $this->caller, $line, $msg, $exception);
    }

    /**
     * @param $level is one of the constants TRACE, DEBUG, INFO, WARN, ERROR, FATAL
     * @param $caller is the value of __FILE__ in the calling php file
     * @param Number, $line the line number, where the message was generated
     * @param String, $msg is the message to write into the logfile
     * @param Exception, $exception (optional) the exception to write to logfile
     * @return boolean <code>true</code> as soon, as the configured loglevel was
     *         set to write the message AND the message was successfuly written
     *         to logfile, otherwise <code>false</code>.
     */
    public static function logit($level, $caller, $line, $msg, $exception = NULL) {
        self::configure(array());

        $logPrio = 0;
        if (array_key_exists($caller, self::$CONFIG)) {
            $logPrio = self::$CONFIG[$caller];
        } elseif (array_key_exists(substr($caller, strlen(INST_DIR) + 1), self::$CONFIG)) {
            $logPrio = self::$CONFIG[substr($caller, strlen(INST_DIR) + 1)];
        } elseif (array_key_exists('DEFAULT_LOG_LEVEL', self::$CONFIG)) {
            $logPrio = self::$CONFIG['DEFAULT_LOG_LEVEL'];
        } else {
            $logPrio = ERROR;
        }
        if ($level >= $logPrio) {
            $logfile = strftime(self::$CONFIG['LOG_FILE'], time());

            $logMessage = date('Y-m-d H:i:s') . " <" . THREAD_ID . "> - ";
            //switch ($level) {
            //    case TRACE: $logMessage .= "TRACE"; break;
            //    case DEBUG: $logMessage .= "DEBUG"; break;
            //    case INFO:  $logMessage .= "INFO "; break;
            //    case WARN:  $logMessage .= "WARN "; break;
            //    case ERROR: $logMessage .= "ERROR"; break;
            //    case FATAL: $logMessage .= "FATAL"; break;
            //}
            $logMessage .= @self::$LOG_LEVELS[$level];
            $logMessage .= " [$caller:$line] $msg\n";
            if ($exception !== NULL) {
                $logMessage .= $exception->getTraceAsString();
            }
            if (self::$CONFIG['STDOUT_IN_CLI_MODE'] == 'true' && php_sapi_name() === 'cli') {
                print $logMessage;
            }
            $bytesWritten = @file_put_contents($logfile, $logMessage, FILE_APPEND);
            if (strlen($logMessage) != $bytesWritten) {
                if (php_sapi_name() === 'cli') {
                    print "CAN NOT WRITE MESSAGE TO LOGILE '$logfile' >>> $logMessage";
                } else {
                    error_log("CAN NOT WRITE MESSAGE TO LOGILE '$logfile' >>> \n$logMessage");
                }
                return false;
            }
            return true;
        }
        return false;
    }
}
?>
