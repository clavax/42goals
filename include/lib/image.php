<?php
class image
{
    const ALIGN_LEFT = 'left';
    const ALIGN_CENTER = 'center';
    const ALIGN_RIGHT = 'right';
    const VALIGN_TOP = 'top';
    const VALIGN_MIDDLE = 'middle';
    const VALIGN_BOTTOM = 'bottom';
    
    public static function string_box(&$image, $font, $left, $top, $right, $bottom, $align, $valign, $leading, $text, $color)
    {
       // Get size of box
       $height = $bottom - $top;
       $width = $right - $left;
     
       // Break the text into lines, and into an array
       $lines = wordwrap($text, floor($width / imagefontwidth($font)), "\n", true);
       $lines = explode("\n", $lines);
     
       // Other important numbers
       $line_height = imagefontheight($font) + $leading;
       $line_count = floor($height / $line_height);
       $line_count = ($line_count > count($lines)) ? (count($lines)) : ($line_count);
     
       // Loop through lines
       for ($i = 0; $i < $line_count; $i++)
       {
           // Vertical Align
           switch($valign)
           {
               case self::VALIGN_TOP: // Top
                   $y = $top + ($i * $line_height);
                   break;
               case self::VALIGN_MIDDLE: // Middle
                   $y = $top + (($height - ($line_count * $line_height)) / 2) + ($i * $line_height);
                   break;
               case self::VALIGN_BOTTOM: // Bottom
                   $y = ($top + $height) - ($line_count * $line_height) + ($i * $line_height);
                   break;
               default:
                   return false;
           }
         
           // Horizontal Align
           $line_width = strlen($lines[$i]) * imagefontwidth($font);
           switch($align)
           {
               case self::ALIGN_LEFT: // Left
                   $x = $left;
                   break;
               case self::ALIGN_CENTER: // Center
                   $x = $left + (($width - $line_width) / 2);
                   break;
               case self::ALIGN_RIGHT: // Right
                   $x = $left + ($width - $line_width);
                   break;
               default:
                   return false;
           }
         
           // Draw
           imagestring($image, $font, $x, $y, $lines[$i], $color);
       }
     
       return true;
    }
    
    public static function from_file($path) 
    {
        $type = false;
        
        $Error = Framework::get('Error');
        
        $Error->off();
        $image_info = @getImageSize($path);
        $Error->on();
        if ($image_info) {
            list($width, $height, $type) = $image_info;
        }
                
        if (!$image_info) {
            return;
        }
        
        switch ($type) {
        case IMAGETYPE_GIF:
            $image = ImageCreateFromGIF($path);
            break;
        case IMAGETYPE_JPEG:
            $image = ImageCreateFromJPEG($path);
            break;
        case IMAGETYPE_PNG:
            $image = ImageCreateFromPNG($path);
            break;
        default:
            $image = false;
            $Error->log('Unsupported image type ' . $type);
            return;
        }
        
        return $image;
    }
    
    public static function save($image, $path, $type) 
    {
        switch ($type) {
        case IMAGETYPE_GIF:
            imagegif($image, $path);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($image, $path, 99);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $path, 0);
            break;
        default:
            $Error = Framework::get('Error');
            $Error->log('Unsupported image type ' . $type);
            return;
        }
        
        return $image;
    }
    
    public static function create($width, $height) 
    {
        $image = ImageCreateTrueColor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        return $image;
    }
    
    public static function resize($image, $new_width = 0, $new_height = 0)
    {
        $width  = imagesx($image);
        $height = imagesy($image);
                
        if ($new_width && !$new_height) {
            $new_height = $height * ($new_width / $width);
        } else if (!$new_width && $new_height) {
            $new_width = $width * ($new_height / $height);
        } else if (!$new_width && !$new_height) {
            $new_width = $width;
            $new_height = $height;
        }
        
        if ($new_width == $width && $new_height == $height) {
            return;
        }
        
        $thumbnail_image = image::create($new_width, $new_height);
        
        $white = ImageColorAllocateAlpha($thumbnail_image, 255, 255, 255, 127);
        ImageFilledRectangle($thumbnail_image, 0, 0, $new_width, $new_height, $white);

        if ($width > $new_width || $height > $new_height) {
            if ($width / $new_width > $height / $new_height) {
                $factor = $new_width / $width;
            } else {
                $factor = $new_height / $height;
            }
            $w = $width * $factor;
            $h = $height * $factor;
            $x = ($new_width - $w) / 2;
            $y = ($new_height - $h) / 2;
            ImageCopyResampled($thumbnail_image, $image, $x, $y, 0, 0, $w, $h, $width, $height);
        } else {
            $x = ($new_width - $width) / 2;
            $y = ($new_height - $height) / 2;
            ImageCopy($thumbnail_image, $image, $x, $y, 0, 0, $width, $height);
        }
        
        return $thumbnail_image;
    }
    
    public static function resize_cut($image, $new_width, $new_height) 
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        
        $try_width = $height * ($new_width / $new_height);
        if ($try_width > $width) {
            $w = $width;
            $h = $width * ($new_width / $new_height);
        } else {
            $w = $try_width;
            $h = $height;
        }
        $x = ($width - $w) / 2;
        $y = ($height - $h) / 2;
        
        $cut = image::create($w, $h);
        imagecopy($cut, $image, 0, 0, $x, $y, $w, $h);
        $resized = image::resize($cut, $new_width, $new_height);
        return $resized;
    }
    
    public static function info($path)
    {
        return @getImageSize($path);
    }
}
?>