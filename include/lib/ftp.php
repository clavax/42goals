<?php
class ftp {
    private static $link;

    private static function connect()
    {
        if (is_resource(self::$link)) {
            return true;
        }
        $Conf  = Framework::get('Conf');
        $Error = Framework::get('Error');
        
        $link = ftp_connect($Conf->CNF->ftp->host, $Conf->CNF->ftp->port);
        if (!is_resource($link)) {
            $Error->log('Could not connect to ' . $Conf->CNF->ftp->host . ':' . $Conf->CNF->ftp->port);
            return false;
        }
        
        if (!ftp_login($link, $Conf->CNF->ftp->user, $Conf->CNF->ftp->pass)) {
            $Error->log('Could not login as ' . $Conf->CNF->ftp->user);
            return false;
        }
        
        if ($Conf->CNF->ftp->passive) {
            if (!ftp_pasv($link, true)) {
                return false;
            }
        }
        
        self::$link = $link;
        return true;
    }

    public static function path($path)
    {
        $Conf  = Framework::get('Conf');
        $conv = str_replace('\\', '/', realpath(dirname($path))) . '/' . basename($path);
        $home = str_replace('\\', '/', realpath($Conf->PTH->main));
        /*if ($path[strlen($path) - 1] == '/' && $conv[strlen($conv) - 1] != '/') {
            $conv .= '/';
        }
        describe(array($conv, home), 1);*/        
        return $Conf->CNF->ftp->main . str_replace($home, '', $conv);
    }

    public static function rename($oldname, $newname)
    {
        $oldname = ftp::path($oldname);
        $newname = ftp::path($newname);
        
        if (!ftp::connect()) {
            return false;
        }
        return ftp_rename(self::$link, $oldname, $newname);
    }

    public static function delete($file)
    {
        $file = ftp::path($file);
        
        if (!ftp::connect()) {
            return false;
        }
        
        return ftp_delete(self::$link, $file);
    }

    public static function chmod($file, $mode)
    {
        $file = ftp::path($file);
        
        if (!ftp::connect()) {
            return false;
        }
        
        return ftp_chmod(self::$link, $mode, $file);
    }

    public static function fput($file, $handle)
    {
        $file = ftp::path($file);
        
        if (!ftp::connect()) {
            return false;
        }
        
        return ftp_fput(self::$link, $file, $handle, FTP_BINARY);
    }

    public static function rmdir($dir)
    {
        $dir = ftp::path($dir);
        
        if (!ftp::connect()) {
            return false;
        }
        
        return ftp_rmdir(self::$link, $dir);
    }

    public static function mkdir($dir)
    {
        $dir = ftp::path($dir);
        
        if (!ftp::connect()) {
            return false;
        }
        
        return ftp_mkdir(self::$link, $dir);
    }
}
?>