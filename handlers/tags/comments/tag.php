<?php
import('base.controller.BaseTag');

class CommentsTag extends BaseTag
{
    public function handle(array $params = array())
    {
        import('model.Comments');
        import('lib.locale');
        
        $this->Conf->loadLanguage('posts');
        
        $thread_type = array_get($params, 'thread_type');
        $thread_id   = array_get($params, 'thread_id');
        if (!$thread_type || !$thread_id) {
            return 'Thread is not specified'; 
        }
        $this->ENV->thread_type = $thread_type;
        $this->ENV->thread_id   = $thread_id;
        $this->ENV->thread_chk  = md5('checksum' . $thread_type . $thread_id);
        
        $Comments = new CommentsModel;
        
        // get comments
        $comments = $Comments->select(array('id', 'date', 'reply_to', 'text', 'user'), SQL::quote('thread_type = ? and thread_id = ?', $thread_type, $thread_id), 'date:asc');
        
        // get users
        $users_ids = arrays::list_fields($comments, 'user');
        if ($users_ids) {
            $Users = new UsersModel;
            $users = arrays::by_field($Users->select(array('id', 'name', 'login', 'thumbnail'), SQL::quote('id in (?)', $users_ids)), 'id');
            $empty_user = array('name' => '?', 'thumbnail' => '?');
            foreach ($comments as &$comment) {
                $comment['user'] = array_get($users, $comment['user'], $empty_user);
            }
        }
            
        // make tree
        $comments = self::makeTree($comments, 'reply_to', 'replies');
        
        $this->T->comments = $comments;
        
        return $this->T->return('tag.comments');
    }
    
    public static function makeTree($containers, $parent_field, $children_field)
    {
        $parents = array();
        foreach ($containers as $id => &$container) {
            $top_level = false;
            if (!isset($container[$children_field])) {
                $container[$children_field] = array();
            }

            if ($container[$parent_field] != $id) {
                if (isset($parents[$container[$parent_field]])) {
                    $parents[$container[$parent_field]][$children_field][$id] = $containers[$id];
                    $parents[$id] = &$parents[$container[$parent_field]][$children_field][$id];
                } else if (isset($containers[$container[$parent_field]]) && $container[$parent_field] != $id) {
                    $containers[$container[$parent_field]][$children_field][$id] = $containers[$id];
                    $parents[$id] = &$containers[$container[$parent_field]][$children_field][$id];
                } else {
                    $top_level = true;
                }
            }

            if ($container[$parent_field] && !$top_level) {
                unset($containers[$id]);
            }
        }
        
        return $containers;
    }
    
    public static function buildTree($comments)
    {
        $T = Framework::get('T');
        $T->comments = $comments;
        return $T->return('tag.comments-tree');
    }
}