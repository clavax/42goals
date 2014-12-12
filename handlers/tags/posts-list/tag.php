<?php
import('base.controller.BaseTag');

class PostsListTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('me');
        $posts = array_get($params, 'posts');
        if (!$posts) {
            return 'No posts';
        }
        
        $this->T->baseurl = isset($this->ENV->community)
                          ? "{$this->URL->home}communities/{$this->ENV->community->name}/posts/"
                          : "{$this->URL->home}users/{$this->ENV->current_user->login}/posts/";
        
        $this->T->posts = $posts;
        return $this->T->return('tag.posts-list');
    }
}