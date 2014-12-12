<?php
import('base.controller.BaseApi');
import('model.Posts');

class PostsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id'
        ));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        $Posts = new PostsModel;
        $Communities = new DataTable('communities');
        
        $data = array_get($request, 'data');
        $data['user'] = $this->ENV->UID;
        $data['date'] = date('Y-m-d H:i:s');
        if (isset($data['text'])) {
            import('3dparty.htmlpurifier.HTMLPurifier');
            $purifier = new HTMLPurifier();
            $data['text'] = $purifier->purify($data['text']);
        }
        if (isset($data['community']) && !empty($data['community'])) {
            $Membership = new DataTable('community_membership');
            $role = $Membership->select('role', SQL::quote('user = ? and community = ?', $data['user'], $data['community']), null, 1);
            if (!$role) {
                return $this->respondOk(array('error' => 'not community member'));
            }
            if ($role != 'admin') {
                $permission = $Communities->select('post_permission', SQL::quote('id = ?', $data['community']), null, 1);
                if ($permission == 'admins') {
                    return $this->respondOk(array('error' => 'not community admin'));
                }
            }
        }
        if (($id = $Posts->add($data)) === false) {
            return $this->respondOk(array('error' => $Posts->errors));
        }
        $data['id'] = $id;
        if (isset($data['community']) && !empty($data['community'])) {
            $data['path'] = 'communities/' . $Communities->select('name', SQL::quote('id = ?', $data['community']), null, 1) . '/posts/' . $id . '/';
        } else {
            $data['path'] = 'users/' . $this->ENV->user->login . '/posts/' . $id . '/';
        }
        
        // add to feed
        if ($this->ENV->user->public || array_get($data, 'community')) {
            import('lib.output');
            $Feed = new DataTable('feed');
            
            $preview = output::text_preview($data['text'], 300);
            $image   = output::first_img($data['text']);
            
            $Feed->insert(array(
                'resource' => "post-{$id}",
                'user' => $this->ENV->UID,
                'type' => 'post-added',
                'community' => array_get($data, 'community', 0),
                'time' => date('Y-m-d H:i:s'),
                'data' => json::encode(array('path' => $data['path'], 'title' => $data['title'], 'url' => $data['url'], 'type' => $data['type'], 'preview' => $preview, 'image' => $image))
            ));
        }
        
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Posts = new PostsModel;
        $Communities = new DataTable('communities');
        
        $owner = $Posts->view($this->ENV->id, 'user');
        if ($this->ENV->UID != $owner) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $data = array_get($request, 'data');
        if (isset($data['text'])) {
            import('3dparty.htmlpurifier.HTMLPurifier');
            $purifier = new HTMLPurifier();
            $data['text'] = $purifier->purify($data['text']);
        }
        if (isset($data['community']) && !empty($data['community'])) {
            $Membership = new DataTable('community_membership');
            $role = $Membership->select('role', SQL::quote('user = ? and community = ?', $owner, $data['community']), null, 1);
            if (!$role) {
                return $this->respondOk(array('error' => 'not community member'));
            }
            if ($role != 'admin') {
                $permission = $Communities->select('post_permission', SQL::quote('id = ?', $data['community'], null, 1));
                if ($permission == 'admins') {
                    return $this->respondOk(array('error' => 'not community admin'));
                }
            }
        }
        if ($Posts->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Posts->errors));
        }
        
        $item = $Posts->view($this->ENV->id);
        if (isset($data['community']) && !empty($data['community'])) {
            $item['path'] = 'communities/' . $Communities->select('name', SQL::quote('id = ?', $data['community']), null, 1) . '/posts/' . $this->ENV->id . '/';
        } else {
            $item['path'] = 'users/' . $this->ENV->user->login . '/posts/' . $this->ENV->id . '/';
        }
        
        // add to feed
        $resource_id = "post-{$this->ENV->id}";
        $Feed = new DataTable('feed');
        $feed = $Feed->select('data', SQL::quote('resource = ?', $resource_id), null, 1);
        $feed_data = json::decode($feed['data']);
        
        $feed = array(
            'community' => array_get($data, 'community', 0),
        );
        $feed_data['path'] = $item['path'];
        if (isset($data['text'])) {
            import('lib.output');
            $feed_data['preview'] = output::text_preview($data['text'], 300);
            $feed_data['image']   = output::first_img($data['text']);
        }
        if (isset($data['url'])) {
            $feed_data['url'] = $data['url'];
        }
        if (isset($data['title'])) {
            $feed_data['title'] = $data['title'];
        }
        
        if ($feed_data) {
            $feed['data'] = json::encode($feed_data);
        }
        $Feed->update_where($feed, SQL::quote('resource = ?', $resource_id));
        return $this->respondOk(array('item' => $item));
    }
    

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Posts = new PostsModel;
        if ($this->ENV->UID != $Posts->view($this->ENV->id, 'user')) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        if ($Posts->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Posts->errors));
        }
        
        $Feed = new DataTable('feed');
        $Feed->delete_where(SQL::quote('resource = ?', "post-{$this->ENV->id}"));
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
}
?>