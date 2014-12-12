<?php
class ir 
{
    public static function tokenize($str)
    {
        $words = preg_split('/[^\w\pL]+/u', $str);
        $word_list = array();
        foreach ($words as $word) {
            if (!strlen($word)) {
                continue;
            }
            $word = str::tolower($word);
            $word_list[] = $word;
        }
        return $word_list;
    }

    public static function cosine($v1, $v2)
    {
        $dot = 0;
        $l1 = 0;
        $l2 = 0;
        foreach ($v1 as $k => $x) {
            $l1 += pow($x, 2);
            if (isset($v2[$k])) {
                $l2 += pow($v2[$k], 2);
                $dot += $x * $v2[$k];
            }
        }
        if (!$l1 || !$l2) {
            return 0;
        }
        return $dot / sqrt($l1 * $l2);
    }
    
    public static function vector_len($v)
    {
        $l = 0;
        foreach ($v as $k => $x) {
            $l += $x * $x;
        }
        return sqrt($l);
    }
}
?>