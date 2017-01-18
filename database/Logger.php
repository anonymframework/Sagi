<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;

/**
 * Class Logger
 * @package Sagi\Database
 */
class Logger implements LoggerInterface
{


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(1, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(0, $message, $context);

    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(1, $message, $context);

    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(1, $message, $context);

    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(0, $message, $context);

    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(0, $message, $context);

    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(0, $message, $context);

    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(0, $message, $context);

    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $path = Loggable::$logFile;
        $fileName = date('H_i_s_d_m_Y') . '_' . $context['line'] . '_' . $context['code'] . '.log';

        $code = $context['code'];
        $file = $context['file'];
        $line = $context['line'];
        $trace_string = $context['traceAsString'];

        $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;

        $content = <<<CONTENT
        An error happen;
             'Code'    : $code,
             'Line'    : $line,
             'Message' : $message, 
             'File'    : $file,
             'Trace'   : $trace_string
CONTENT;


        echo $content;
        if ($level === 1) {
            file_put_contents($fullPath, $content);

            exit();
        }
    }

}