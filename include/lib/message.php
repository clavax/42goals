<?php
class message
{
    public static function email($to, $subj, $msg, $headers = null, $params = null)
    {
        $done = @mail($to, $subj, $msg, $headers, $params);
        if (!$done) {
            $input =
<<<XML
<?xml version="1.0"?>
<mail>
  <subject>
    $subj
  </subject>
  <headers>
    $headers
  </headers>
  <message>
    $msg
  </message>
  <parameters>
    $params
  </parameters>
</mail>
XML;
            $Conf = Framework::get('Conf');
            $dir = $Conf->PTH->tmp . 'email/' . $to . '/';
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            if (file_exists($dir))
            {
                $words = preg_split('/\W/', $subj);
                $fname = (strlen($words[0])) ? $words[0] : 'untitled';
                $uname = $fname; //unique file name
                $i = '';
                while (file_exists($dir . $uname)) {
                    $i = (int) $i;
                    $i ++;
                    $uname = $fname . '(' . $i .')';
                }
                $fname = $uname;
                $fh = fopen($dir . $fname, 'w');
                fwrite($fh, $input);
                fclose($fh);
            }
        }
        return $done;
    }
    
    public static function jabber($recepient, $message, $encoded = false)
    {
        $Conf = Framework::get('Conf');
        if (!$encoded) {
            $message = base64_encode($message);
        } 
        $success = exec("{$Conf->PTH->main}cli.php jabber send \"$recepient\" \"$message\" 0 1");
        return $success;
    }
    
    public static function icq($recepient, $message, $encoded = false)
    {
        $Conf = Framework::get('Conf');
        $recepient .= '@' . $Conf->CNF->jabberbot->icq_gate;
        return self::jabber($recepient, $message, $encoded);
    }
    
    public static function msn($recepient, $message, $encoded = false)
    {
        $Conf = Framework::get('Conf');
        $recepient = str_replace('@', '\\\\40', $recepient) . '@' . $Conf->CNF->jabberbot->msn_gate;
        return self::jabber($recepient, $message, $encoded);
    }
    
    public static function yahoo($recepient, $message, $encoded = false)
    {
        $Conf = Framework::get('Conf');
        $recepient .= '@' . $Conf->CNF->jabberbot->yahoo_gate;
        return self::jabber($recepient, $message, $encoded);
    }
    
    public static function sms($recepient, $message)
    {
        $Conf  = Framework::get('Conf');
        $Error = Framework::get('Error');
        
        $user     = $Conf->CNF->sms->user;
        $password = $Conf->CNF->sms->pass;
        $api_id   = $Conf->CNF->sms->api;
        $baseurl  = $Conf->CNF->sms->host;
        $text     = str::toUnicode($message);
        $to       = $recepient;
        
        $Error->log($text);
        
        // auth call
        $url = "{$baseurl}/http/auth?user={$user}&password={$password}&api_id={$api_id}";
        // do auth call
        $res = file::get_remote($url);
        $line = strtok($res, "\n\r");
        $ret = trim(strlen($line) ? $line : $res);
        // split our response. return string is on first line of the data returned
        $sess = split(':', $ret);
        if ($sess[0] != 'OK') {
            $Error->log('Authentication failure: ' . $res);
            return false;
        }
        
        $sess_id = trim($sess[1]); // remove any whitespace
        $url = "{$baseurl}/http/sendmsg?session_id={$sess_id}&to={$to}&text={$text}&unicode=1";
        
        // do sendmsg call
        $res = file::get_remote($url);
        $line = strtok($res, "\n\r");
        $ret = trim(strlen($line) ? $line : $res);
        $send = split(':', $ret);
        if ($send[0] != 'ID') {
            $Error->log('Send message failed ' . describe($send));
            return false;
        }
        
//        $Error->log('Success message ID: ' . $send[1]);
        return true;
    }
}
?>