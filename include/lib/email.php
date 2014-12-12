<?php
class email {
    public static function file($to, $subject, $message, $filepath)
    {
        $boundary = "--" . md5(uniqid(time()));
    
        $headers = "MIME-Version: 1.0\n"
                 . "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
    
        $filename = basename($filepath);
        $message_part = "--$boundary\n"
                      . "Content-Type: application/octet-stream\n"
                      . "Content-Transfer-Encoding: base64\n"
                      . "Content-Disposition: attachment; filename=\"$filename\"\n\n"
                      . chunk_split(base64_encode(file_get_contents($filepath)))."\n";
    
        $multipart = "--$boundary\n"
                   . "Content-Type: text/html; charset=utf-8\n"
                   . "Content-Transfer-Encoding: Quot-Printed\n\n"
                   . "$message\n\n"
                   . $message_part
                   . "--$boundary--\n";
    
        return email::send($to, $subject, $multipart, $headers);
    }
    
    public static function send($to, $subj, $msg, $headers = null, $params = null)
    {
        $Conf = Framework::get('Conf');
        $sent = false;
        if (!$Conf->CNF->site->local) {
            $sent = @mail($to, $subj, $msg, $headers, $params);
        }
        if (!$sent) {
            $input =
<<<XML
<?xml version="1.0"?>
<mail>
    <subject>$subj</subject>
    <headers>$headers</headers>
    <message>$msg</message>
    <parameters>$params</parameters>
</mail>
XML;
            $dir = $Conf->PTH->tmp . 'email/' . $to . '/';
            if (!file_exists($dir)) {
                file::mkdir($dir, 0775);
            }
            if (file_exists($dir)) {
                $words = preg_split('/\W/', $subj);
                $fname = strlen($words[0]) ? implode('_', $words) : 'untitled';
                $fname = substr($fname, 0, 64);
                $uname = $fname; //unique file name
                $i = '';
                while (file_exists($dir . $uname)) {
                    $i = (int) $i;
                    $i ++;
                    $uname = $fname . '_' . $i;
                }
                $fname = $uname;
                file::put_contents($dir . $fname, $input);
            }
        }
        return $sent;
    }
        
}
?>