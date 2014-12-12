<?php
import('base.controller.BaseApi');

class UploadApi extends BaseApi
{
    public function handlePostDefault()
    {
        if (!isset($_FILES['Filedata'])) {
            return $this->error('No data' . describe($_FILES));
        }
        
        $file = $_FILES['Filedata'];       
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->Error->log(describe($file));
            return $this->error('Not uploaded ' . $file['tmp_name']);
        }
        
        // check filetype
        $allowed = '*';
        if (!$this->checkExt($file['name'], $allowed)) {
            return $this->error('Not allowed file type ' . file::get_ext($file['name']));
        }
        
        // move file 
        $upload_dir = $this->PTH->tmp . 'upload/';
        $tmp_name = time() . '_' . md5(uniqid('', true));
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $tmp_name)) {
            return $this->error('Not saved');
        }
        $_SESSION['upload'][$tmp_name] = $file['name'];
        file::chmod($upload_dir . $tmp_name, 0664);
        return $this->respondOk(array(
            'file' => array(
                'name' => $file['name'],
                'size' => $file['size'],
                'tmp_name' =>  $tmp_name
            )
        ));
    }
    
    private function checkExt($filename, $allowed)
    {
        if ($allowed == '*') {
            return true;
        }
        $ext = strtolower(file::get_ext($filename));
        return in_array($ext, $allowed);
    }
    
    public function error($error)
    {
        $this->Error->log($error);
        return $this->respondOk(array('error' => $error));
    }
}

?>