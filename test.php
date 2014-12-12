<?php

error_reporting(E_ALL);
            $url = 'http://blog.42goals.com/rss';

            $referer='';
        if (function_exists('curl_init')) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            if ($referer) {
                curl_setopt($c, CURLOPT_REFERER, $referer);
            }

            $contents = curl_exec($c);
            curl_close($c);
        } else {

            $url = str_replace(' ', '%20', $url);
            $contents = file_get_contents($url);
        }
echo file_get_contents($url);
        var_dump($contents);
die;
