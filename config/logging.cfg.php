<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$LOGGING = array (
        // The value must be interpretable by strftime(...) and defines the
        // absolute path (directory and the name itselfs) to the logfile.
        'LOG_FILE' => '/var/www/apps/pd@min/logs/webfrontend-%Y-%m-%d.log',

        // This value defines the default-loglevel for all message, where no
        // matching definition was defined.
        // default: ERROR
        'DEFAULT_LOG_LEVEL' => ERROR,

        // Set the belowing variable to true to install the logger as a global
        // exception handler. This will be done by an interal call of
        // 'set_exception_handler(...)'.
        // The name of handler-implention: 'logger_exception_handler'
        'ENABLE_GLOBAL_ECXEPTION_HANDLER' => true,

        // Set the belowing variable to true to install the logger as a global
        // error handler. This will be done by an interal call of
        // 'set_error_handler(...)'.
        // The name of handler-implention: 'logger_error_handler'
        // Set the value of 'error_reporting' in php.ini to control the CALL
        // of this handler function. Use Logger::configure(...) for more
        // detailed configuration. There you can set a file specific properties.
        'ENABLE_GLOBAL_ERROR_HANDLER' => true,

        // This option is useful to get print all messages to the screen, which
        // will be logged to the log file too.
        'STDOUT_IN_CLI_MODE' => true,

        'index.php' => 'DEBUG'
    );

?>
