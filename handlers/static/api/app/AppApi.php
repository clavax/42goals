<?php
import('base.controller.BaseApi');

class AppApi extends BaseApi
{
    public function handlePostDefault($request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        import('model.Apps');
        $Apps = new AppsModel;
        
        $id = $Apps->select('id', SQL::quote('user = ?', $this->ENV->UID), null, 1);
        
        $item = array_get($request, 'data');
        if ($id) {
            $Apps->edit($id, $item);
        } else {
            $item['user'] = $this->ENV->UID;

            $time = floor((time() - mktime(0, 0, 0, 5, 29, 2010)) / (3600 * 24)); // days from May 29, 2010
            $rand1 = rand(1e+9, 1e+10);
            $rand2 = rand(1e+9, 1e+10);
        
            $item['appkey'] = md5($time . $rand1);
            $item['secret'] = md5($rand2);
            
            $id = $Apps->add($item);
        }
        
        return $this->respondOk(array('item' => $item));
    }
}
