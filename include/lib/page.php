<?php
class page {
    public static function generate($pages, $page, $pages_from_start = 3, $pages_from_end = 3, $pages_to_left = 3, $pages_to_right = 3)
    {
        if ($pages < 25) {
            $set = $pages > 1 ? range(1, $pages) : array(1);
            $page_set = array_combine($set, $set);
        } else {
            $page_set = array();
            if ($page > $pages_from_start + $pages_to_left) {
                $set = range(1, $pages_from_start);
                $page_set = array_combine($set, $set);
    
                $page_set[$page - $pages_to_left - 1] = '&hellip;';
    
                $set = range($page - $pages_to_left, $page);
                $page_set += array_combine($set, $set);
            } else {
                $set = range(1, $page);
                $page_set += array_combine($set, $set);
            }
    
            if ($pages - $page > $pages_from_end + $pages_to_right) {
                $set = range($page + 1, $page + $pages_to_right);
                $page_set += array_combine($set, $set);
    
                $page_set[$page + $pages_to_right + 1] = '&hellip;';
    
                $set = range($pages - $pages_from_end, $pages);
                $page_set += array_combine($set, $set);
            } else {
                $set = range($page, $pages);
                $page_set += array_combine($set, $set);
            }
        }
    
        return $page_set;
    }
    
    public static function calculate($total, &$page, $perpage)
    {
        $pages = ceil($total / $perpage);
        if ($pages) {
            if ($page > $pages) {
                $page = $pages;
            } else if ($page < 1) {
                $page = 1;
            }
            $start = ($page - 1) * $perpage;
        } else {
            $start = 0;
        }
        return array($start, $pages);
    }
}
?>