<?php
import('base.controller.BaseTag');

class FeedListTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('feed');
        
        $activities = array_get($params, 'activities', array());
        foreach ($activities as &$activity) {
            $this->T->activity = $activity;
            $this->T->data = json::decode($activity['data']);
            
            switch ($activity['type']) {
            case 'comment-added':
                $activity['text'] = $this->T->return('tag.feed-comment-added');
                break;
                
            case 'post-added':
                $activity['text'] = $this->T->return('tag.feed-post-added');
                break;
                
            case 'joined':
                $activity['text'] = $this->T->return('tag.feed-joined');
                break;
                
            case 'friend-added':
                $activity['text'] = $this->T->return('tag.feed-friend-added');
                break;
            }
        }
        $this->T->activities = $activities;
        return $this->T->return('tag.feed-list');
    }
}