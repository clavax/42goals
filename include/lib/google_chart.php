<?php
class google_chart
{
    public static function encode($values, $max = 4095, $min = 0)
    { 
        $extended_table = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
         
        //$chardata = 'e:';
        $chardata = '';
        $delta    = $max - $min + 1; 
        $size     = strlen($extended_table);
        $base     = $size * $size;
        
        foreach ($values as $value) { 
            if ($value >= $min && $value <= $max) {
                $value  = round(($value - $min) / $delta * $base);
                $first  = $extended_table[floor($value / $size)]; 
                $second = $extended_table[$value % $size];
                 
                $chardata .= $first . $second;
            } else { 
                $chardata .= '__'; // Value out of max range; 
            } 
        } 
        return($chardata);
    }    
}
?>