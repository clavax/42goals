<?php
import('base.controller.BaseController');

class Captcha extends BaseController
{
    public function handleGetDefault() 
    {
        $digits = self::genId();     
        $w = 80;
        $h = 26;
        $font = $this->PTH->this . 'jokerman.ttf';
        $bg_file = $this->PTH->this . 'bg.jpg';
        $size = 12;
        $angle = rand(-5, 5);
        
        $img = imagecreatetruecolor($w, $h);
        $col = imagecolorallocate($img, 255, 255, 255);
        $bg  = imagecreatefromjpeg($bg_file);
        
        $bg_size = getimagesize($bg_file);
        $bg_x = rand(0, $bg_size[0] - $w);
        $bg_y = rand(0, $bg_size[1] - $h);
        
        imagecopy($img, $bg, 0, 0, $bg_x, $bg_y, $w, $h);
        $box = imagettfbbox($size, $angle, $font, $digits);
        $text_width  = $box[2] - $box[0];
        $text_height = $box[7] - $box[1];
        $x = ($w - $text_width) / 2;
        $y = ($h - $text_height) / 2; 
        imagettftext($img, $size, $angle, $x, $y, $col, $font, $digits);
        
        header('Content-Type: image/gif');
        ob_start();
        imagegif($img);
        return ob_get_clean();
    }
    
    public static function genId()
    {
        $digits = '';
        for ($i = 1; $i <= 5; $i ++) {
            $digits .= rand(0, 9);
        }
        Framework::get('Session')->digits = $digits;
        return $digits;
    }
    
    public static function checkId($id)
    {
        return !empty($id) && $id == Framework::get('Session')->digits;
    }
}

?>
