<?php
import('base.controller.BaseApi');
import('model.Comments');

class CommentsApi extends BaseApi
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
        $Comments = new CommentsModel;
        
        $data = $request;
        $data['user'] = $this->ENV->UID;
        $data['date'] = date('Y-m-d H:i:s');
        
        // checksum
        if (!isset($data['thread_type']) || !isset($data['thread_id']) || !isset($data['thread_chk'])) {
            return $this->respondOk(array('error' => 'Wrong thread'));
        }
        $checksum = md5('checksum' . $data['thread_type'] . $data['thread_id']);
        if ($checksum != $data['thread_chk']) {
            return $this->respondOk(array('error' => 'Wrong thread sum'));
        }
        unset($data['thread_chk']);
        
        // check reply author
        if (isset($data['reply_to']) && !empty($data['reply_to'])) {
            $user_to = $Comments->view($data['reply_to'], 'user');
            if (!$user_to) {
                return $this->respondOk(array('error' => 'Wrong reply to'));
            }
        }
        
        if (($id = $Comments->add($data)) === false) {
            return $this->respondOk(array('error' => $Comments->errors));
        }
        $data['id'] = $id;
        
        $Users = new PrimaryTable('users', 'id');
        
        // send notification
        switch ($data['thread_type']) {
        case 'post':
            import('model.Posts');
            $Posts = new PostsModel;
            $author = $Posts->view($data['thread_id'], 'user');
            $login  = $Users->view($author, 'login');
            $comment_url = "{$this->URL->home}users/{$login}/posts/{$data['thread_id']}/";
            break;
            
        case 'goalsweek':
            list($author, $week) = explode('-', $data['thread_id']);
            $login  = $Users->view($author, 'login');
            $comment_url = "{$this->URL->home}users/{$login}/goals/{$week}/";
            break;
            
        default:
            $author = 0;
        }
        
        if ($author) {
            import('lib.notification.notification');
            notification::send(isset($user_to) ? $user_to : $author, 'comment-notification', $data, $comment_url);
            
            $Feed = new DataTable('feed');
            $Feed->insert(array(
                'resource' => "comment-{$id}",
                'user' => $this->ENV->UID,
                'type' => 'comment-added',
                'time' => date('Y-m-d H:i:s'),
                'data' => json::encode(array('url' => $comment_url, 'comment' => $data['text']))
            ));
        }
        
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Comments = new CommentsModel;
        
        $data = array_get($request, 'data');
        if ($Comments->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Comments->errors));
        }
        
        $item = $Comments->view($this->ENV->id, array('id', 'user', 'thread_type', 'thread_id', 'reply_to', 'date', 'text'));
        return $this->respondOk(array('item' => $item));
    }
    

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Comments = new CommentsModel;
        
        if ($Comments->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Comments->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
}
?>