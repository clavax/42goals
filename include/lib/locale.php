<?php
class locales
{
    private static function convert($data, $format)
    {
        foreach ($data as $key => $value) {
            $format = preg_replace('/' . preg_quote($key) . '/i', $value, $format);
        }
        return $format;
    }
    
    public static function date($date)
    {
        $Conf = Framework::get('Conf');
        $format = $Conf->CNF->languages->get($Conf->ENV->language)->date_format;
        if (($time = strtotime($date)) !== false) {
            $date = date($format, $time);
        }
        
        $date = self::date_names($date);
        return $date;
    }
    
    public static function daymonth($date)
    {
        $Conf = Framework::get('Conf');
        $format = $Conf->CNF->languages->get($Conf->ENV->language)->daymonth_format;
        if (($time = strtotime($date)) !== false) {
            $date = date($format, $time);
        }
        
        $date = self::date_names($date);
        return $date;
    }
    
    public static function date_names($str)
    {
        $Conf = Framework::get('Conf');
        $Conf->loadLanguage('date');
        if ($Conf->ENV->language == 'ru') {
            $str = str::tolower($str);
        }
        foreach ($Conf->LNG->month_name as $name => $title) {
            $str = str_replace($name, $title, $str);
        }
        foreach ($Conf->LNG->weekday as $name => $title) {
            $str = str_replace($name, $title, $str);
        }

        return $str;
    }
    
    public static function datetime($datetime)
    {
        $Conf = Framework::get_object('Conf');
        $format = $Conf->CNF->languages->get($Conf->ENV->language)->datetime_format;
        $date = 'sometime';
        if (($time = strtotime($datetime)) !== false) {
            $date = date($format, $time);
        }
//        foreach ($GLOBALS['LNG']['month'] as $name => $title) {
//            $date = str_replace($name, $title, $date);
//        }
        return $date;
    }
    
    public static function time($time)
    {
        $Conf = Framework::get_object('Conf');
        $format = $Conf->CNF->languages->get($Conf->ENV->language)->time_format;
        if (($timestamp = strtotime($time)) !== false) {
            $time = date($format, $timestamp);
        }
        return $time;
    }
}
?>