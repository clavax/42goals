<?php
class Timer
{
    private
            $start,
            $points,
            $time,
            $total,
            $precision;
    
    public function __construct($start = true, $precision = 3)
    {
        $this->points = array();
        $this->time = array();
        $this->precision = (is_int($precision) && $precision >= 0 && $precision < 16) ? $precision : 3;
        if ($start) {
            $this->start();
        }
    }
    
    public function start()
    {
        $this->start = $this->getmicrotime();
    }
    
    public function stop($label = 'end')
    {
        $this->reg($label);
        $prev = $this->start;
        foreach ($this->points as $label => $point) {
            $this->time[$label] = number_format($point - $prev, $this->precision);
            $prev = $point;
        }
        $this->total = number_format($prev - $this->start, $this->precision);
    }

    public function reg($label)
    {
        $this->points[$label] = $this->getmicrotime();
    }
    
    private function getmicrotime()
    { 
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec); 
    }

    public function report()
    {
        $report = '';
        $length = 20;
        $max_length = 0;
        $lines = array();
        foreach ($this->time as $label => $time) {
            $line = "$label: $time sec.";
            $lines[] = $line;
            if ($max_length < strlen($line)) {
                $max_length = strlen($line);
            }
        }
        
        foreach ($lines as $key => $line) {
            $lines[$key] = str_replace(':', str_pad(': ', $max_length - strlen($line) + $length + strlen("\n"), '.'), $line);
        }
        
        $report = implode("\n", $lines);
        $report .= str_pad("\n", $max_length + $length, '=');
        $total = "Total: $this->total sec.";
        $report .= str_replace(':', str_pad(': ', $max_length - strlen($total) + $length + strlen("\n"), '.'), "\n" . $total);
        return $report;
    }
    
    public function getTime() {
        return $this->total;
    }
}
?>