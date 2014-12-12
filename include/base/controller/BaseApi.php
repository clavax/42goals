<?php
import('base.controller.BaseController');
import('lib.xml');
import('lib.json');

class BaseApi extends BaseController
{
    protected $response_types;
    protected $charset = 'UTF-8';

    public function __construct()
    {
        $this->response_type = '';
        
        $this->response_types = array(
            'xml'  => 'text/xml',
            'text' => 'text/plain',
            'json' => 'text/json'
        );
    }

    protected function respond($code, $data)
    {
        if ($this->response_type != 'php') {
            // determine accept format type
            $accept_types = explode(',', array_get($_SERVER, 'HTTP_ACCEPT'));
            $preferable = false;
            $preferable_type = arrays::first(array_keys($this->response_types));
            foreach ($this->response_types as $name => $type) {
                $pos = array_search($type, $accept_types);
                if ($pos !== false && ($pos < $preferable || $preferable === false)) {
                    $preferable = $pos;
                    $preferable_type = $name;
                }
            }
        } else {
            $preferable_type = 'php';
        }
        
        //$preferable_type = 'json';
        switch ($preferable_type) {
        default:
        case 'text':
            $content_type = 'text/plain';
            $content = serialize($data);
            break;
        case 'xml':
            $content_type = 'text/xml';
            $this->T->charset = $this->charset;
            $this->T->xml = xml::from_array($data, "\n    "); // 4 spaces
            $content = $this->T->return_template('templates.data~xml');
            break;
        case 'json':
            $content_type = 'text/plain';
            $content = json_encode($data);
            break;
        case 'php':
            $content = $data;
            break;
        }

        if ($this->response_type != 'php') {
            header("Content-Type: $content_type; charset={$this->charset}");
        }
        return $content;
    }
    
    protected function respondOk($data)
    {
        return $this->respond(HttpRouter::STATUS_OK, $data);
    }
}
?>