<?php
import('lib.file');

class Error extends Object
{
    private $log_file;
    private $format;
    private $line_end;
    private $show_errors;

    const
            TEMPLATE_YEAR    = '%year',
            TEMPLATE_MONTH   = '%month',
            TEMPLATE_DAY     = '%day',
            TEMPLATE_HOURS   = '%hours',
            TEMPLATE_MINUTES = '%minutes',
            TEMPLATE_SECONDS = '%seconds',
            TEMPLATE_MESSAGE = '%message',

            LINE_END_UNIX = "\n",
            LINE_END_WIN  = "\r\n",
            LINE_END_MAC  = "\r";

    public function __construct($log_file, $format, $line_end = LINE_END_UNIX)
    {
        if ($this->CNF->kernel->log_errors) {
            set_error_handler(array(&$this, 'handle'));
        }
        $this->log_file = $log_file;
        $this->format   = $format;
        $this->line_end = $line_end;
        $this->show_erros = $this->CNF->kernel->show_errors;
    }
    
    public function on()
    {
        $this->show_errors = true;
    }
    
    public function off()
    {
        $this->show_errors = false;
    }

    public function log($error)
    {
        if (!error_reporting()) {
            return false;
        }
        $search = array(
            self::TEMPLATE_YEAR,
            self::TEMPLATE_MONTH,
            self::TEMPLATE_DAY,
            self::TEMPLATE_HOURS,
            self::TEMPLATE_MINUTES,
            self::TEMPLATE_SECONDS,
            self::TEMPLATE_MESSAGE,
        );

        $replace = array(
            date('Y'),
            date('m'),
            date('d'),
            date('H'),
            date('i'),
            date('s'),
            is_scalar($error) ? $error : print_r($error, true)
        );

        $line = str_replace($search, $replace, $this->format);

        if (($f = fopen($this->log_file, 'ab')) !== false) {
            fwrite($f, $line . $this->line_end);
            fclose($f);
            return true;
        } else {
            return false;
        }

    }

    public static function trace()
    {
        $debug = debug_backtrace();
        $trace = '';
        if (count($debug) > 1) {
            $trace = "Trace:\n";
            foreach ($debug as $n => $info) {
                if ($n) {
                    if (isset($info['file'])) {
                        $trace .= "#{$n}: {$info['file']}:{$info['line']} - ";
                        $trace .= trim(file::get_line($info['file'], $info['line'])) . "\n";
                    } else {
                        $trace .= "#{$n}: ?\n";
                    }
                }
            }
        }
        return $trace;
    }
    
    public function handle($errno, $error, $file = '', $line = 0, $context = null)
    {
        if ($this->show_errors) {
            $message = "<pre>Error '$error'";
            
            if (strlen($file)) {
                $message .= " at $file:$line";
                $message .= ' - ' . trim(file::get_line($file, $line)) . "\n";
            }

            if ($this->CNF->kernel->trace_errors) {
                $message .= self::trace();
            }
            $message .= "</pre>\n";
            
            echo $message;
        }

        if ($this->CNF->kernel->log_errors) {
            $this->log("$errno: $error ($file, $line)");
        }
    }
}
?>