<?php
class xml {
    public static function quote($string)
    {
        $string = (string)$string;
        $quoted = '';
        for ($i = 0; $i < strlen($string); $i ++) {
            if (ord($string[$i]) > 122 && ord($string[$i]) < 160) {
                $quoted .= '&#' . ord($string[$i]);
            } else {
                $quoted .= $string[$i];
            }
        }
        return htmlspecialchars($quoted);
    }
    
    public static function escape($string)
    {
        $string = htmlspecialchars($string);
        return str_replace('"', '&quot;', $string);
    }
    
    public static function from_array($array, $padding = "\n", $pad_with = "\t", $name = '')
    {
        $xml = '';
        
        foreach ($array as $key => $value) {
            $attr = '';
            if (is_array($value) && isset($value['@attributes']) && arrays::nonempty($value['@attributes'])) {
                foreach ($value['@attributes'] as $attr_name => $attr_value) {
                    $attr .= ' ' . $attr_name . '="' . $attr_value . '"';
                }
                unset($value['@attributes']);
            }
            
            if (is_int($key)) {
                $tag = $name;
                //$attr .= ' ' . ' _key="' . $key . '"';
            } else {
                $tag = $key;
            }
            
            if (is_scalar($value) || is_null($value)) {
                $value = self::escape($value);
                $xml .= "$padding<$tag{$attr}>$value</$tag>"; // were <![CDATA[...]]>
            } else if (is_array($value)) {
                if (count($value) && arrays::keys_int($value)) {
                    $xml .= self::from_array($value, $padding, $pad_with, $tag);
                } else {
                    if ($value) {
                        $xml .= "$padding<$tag{$attr}>" . self::from_array($value, $padding . $pad_with, $pad_with, $key) . "$padding</$tag>";
                    } else {
                        $xml .= "$padding<$tag{$attr}></$tag>";
                    }
                }
            }
        }
        return $xml;
    }
    
    public static function to_array($xml)
    {
        $parser=xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values, $tags);
        $error=xml_get_error_code($parser);
        if($error)
        {
            $column=xml_get_current_column_number($parser);
            $line=xml_get_current_line_number($parser);
            $byte=xml_get_current_byte_index($parser);
            echo xml_error_string($error).' at line: '.$line.', column: '.$column.', byte: '.$byte."\n";
        }
        xml_parser_free($parser);
        $keys=array();
        $name='ini';
        foreach($values as $tag)
        {
            switch($tag['type'])
            {
                case 'open':
                case 'complete':
                    array_push($keys, $tag['tag']);
                    $e=&$$name;
                    $last=array_pop($keys);
                    foreach($keys as $key) $e=&$e[$key];
               
                      array_push($keys, $last);
                    if(isset($e[$last]))
                    {
                        $last_k=(is_array($e[$last])) ? max(array_keys($e[$last])) : '';
                        if(!is_int($last_k))
                        {
                            $e=&$e[$last];
                            $b=$e;
                            $last=1;
                            $last_k=1;
                            $e=null;
                            $e[$last]=$b;
                        }
                        else $e=&$e[$last];
                        $last=$last_k+1;
                        array_push($keys, $last);
                    }
                    if($tag['type']=='complete')
                    {
                        if(isset($tag['value']))
                        {
                            switch($tag['value'])
                            {
                                case 'false':
                                case 'False':
                                case 'FALSE':
                                    $e[$last]=false;
                                    break;
                                case 'true':
                                case 'True':
                                case 'TRUE':
                                    $e[$last]=true;
                                    break;
                                case 'null':
                                case 'Null':
                                case 'NULL':
                                    $e[$last]=null;
                                    break;
                                default:
                                $e[$last]=$tag['value'];
                            }
                        }
                        else $e[$last]=null;
                        if(is_int(end($keys))) array_pop($keys);
                        array_pop($keys);
                    }
                    break;
                case 'close':
                    if(is_int(end($keys))) array_pop($keys);
                    array_pop($keys);
                    break;
            }
        }
        return($ini);
    }
    
    public static function cleanup($xml) {
        $xmlOut = '';
        $inTag = false;
        $xmlLen = strlen($xml);
        for($i = 0; $i < $xmlLen; ++ $i) {
            $char = $xml [$i];
            // $nextChar = $xml[$i+1];
            switch ($char) {
            case '<' :
                if (! $inTag) {
                    // Seek forward for the next tag boundry
                    for ($j = $i + 1; $j < $xmlLen; ++ $j) {
                        $nextChar = $xml [$j];
                        switch ($nextChar) {
                        case '<' : // Means a < in text
                            $char = htmlentities($char);
                            break 2;
                        case '>' : // Means we are in a tag
                            $inTag = true;
                            break 2;
                        }
                    }
                } else {
                    $char = htmlentities($char);
                }
                break;
            case '>' :
                if (!$inTag) { // No need to seek ahead here
                    $char = htmlentities($char);
                } else {
                    $inTag = false;
                }
                break;
            default :
                if (!$inTag) {
                    $char = htmlentities($char);
                }
                break;
            }
            $xmlOut .= $char;
        }
        return $xmlOut;
    }
}

class xDOMDocument extends DOMDocument
{
    public function addDoctype($name, $publicId = null, $systemId = null)
    {
        $imp = new DOMImplementation();
        $dtd = $imp->createDocumentType($name, $publicId, $systemId);
        $this->insertBefore($dtd, $this->firstChild);
        $this->loadXML($this->saveXML());
    }
    
    public static function fromXmlFile($xml_file)
    {
        $doc = new xDOMDocument();
        $doc->load($xml_file);
        return $doc;
    }
}
?>