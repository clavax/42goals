<?php
import('base.controller.BasePage');

class GoalsPage extends BasePage
{
    public function handlePostDefault(array $request = array())
    {
        import('static.api.upload.UploadApi');
        $Api = new UploadApi();
        $xml = $Api->handlePostDefault($request);
        $data = xml::to_array($xml);

        import('lib.headers');
        headers::content('text/html');
        return $this->handleGetDefault(array('data' => array_get($data, 'root')));
    }
    
    public function handleGetDefault(array $request = array())
    {
        $this->Conf->loadLanguage('me');
        $this->T->title = '';
        $this->T->data = array_get($request, 'data');
        $this->T->include('this.upload-head', 'head');
        $this->T->include('this.upload-content', 'content');
        return $this->T->return('templates.blank');
    }
}
?>