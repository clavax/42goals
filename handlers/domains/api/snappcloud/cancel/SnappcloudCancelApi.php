<?php
import('base.controller.BaseApi');

class SnappcloudCancelApi extends BaseApi 
{
    public function handlePostDefault(array $request = array())
    {
        $response = array('ok' => 'ok');
        $this->Error->log(describe($request));
        return $this->respondOk($response);
    }
}
?>