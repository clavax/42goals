<?php
class Memory
{
    private $start;
    private $points;
    private $memory;
    
    public function __construct($start = true)
    {
        $this->points = array();
        $this->memory = array();
        if ($start) {
            $this->start();
        }
    }
    
    public function start()
    {
        $this->start = self::getUsage();
    }
    
    public static function getUsage()
    {
    	return memory_get_peak_usage(true);
    }
    
    public function stop($label = 'end')
    {
        $this->reg($label);
        $prev = $this->start;
        foreach ($this->points as $label => $point) {
            $this->memory[$label] = self::format($point);
            $prev = $point;
        }
    }
    
    public static function format($memory)
    {
        $i = 0;
        $iec = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        while (($memory / 1024) >= 1) {
            $memory /= 1024;
            $i ++;
        }
        return round($memory, 1) . $iec[$i];
    }

    public function reg($label)
    {
        $this->points[$label] = self::getUsage();
    }
    
    public function report()
    {
        $report = '';
        $length = 20;
        $max_length = 0;
        $lines = array();
        foreach ($this->memory as $label => $memory) {
            $line = "$label: $memory";
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
        $report .= str_replace(':', str_pad(': ', $max_length + $length + strlen("\n"), '.'), "\n");
        return $report;
    }
}
?>