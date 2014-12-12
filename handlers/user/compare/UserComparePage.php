<?php
import('base.controller.BaseUserPage');

class UserComparePage extends BaseUserPage
{
    public function handleDefault()
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        if ($user['id'] != $this->ENV->UID) {
            return false;
        }
        
        $this->T->user = $user;
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        $this->Conf->loadLanguage('goals');
        
        // get goals
        import('model.Goals');
        $Goals = new GoalsModel();
        $goals = $Goals->select(array('id', 'title', 'type'), SQL::quote('user = ?', $this->ENV->UID), array('tab', 'position'));
        $this->T->goals = $goals;
        
        //get friends
        $Users = new UsersModel();
        $Connections = new DataTable('connections');
        $friends_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $user['id'], 'accepted'));
        if ($friends_ids) {
            $this->T->friends = $Users->select(array('id', 'login', 'name', 'thumbnail'), SQL::quote('id in (?)', $friends_ids), 'name');
        } else {
            $this->T->friends = array();
        }
        
        // get comparisons
        $Comparisons = new DataTable('comparisons');
        $ComparisonsItem = new DataTable('comparisons_item');

        $owned_comparisons = $Comparisons->select(array('id', 'user', 'comment'), SQL::quote('user = ?', $this->ENV->UID));
        if ($owned_comparisons) {
            $raw_owned_comparisons_item = $ComparisonsItem->select(array('id', 'user', 'goal', 'comparison', 'status'), SQL::quote('comparison in (?)', arrays::list_fields($owned_comparisons, 'id')));
            $owned_comparisons_item_goals = $Goals->select(array('id', 'title'), SQL::quote('id in (?)', arrays::list_fields($raw_owned_comparisons_item, 'goal')));
            foreach ($raw_owned_comparisons_item as &$item) {
                $item['goal_info'] = array_get($owned_comparisons_item_goals, $item['goal'], false);
            }
            $owned_comparisons_item = arrays::group_by_field($raw_owned_comparisons_item, 'comparison');
            foreach ($owned_comparisons as &$comparison) {
                $comparison['items'] = array_get($owned_comparisons_item, $comparison['id'], array());
            }
            $this->T->owned_comparisons = $owned_comparisons;
        } else {
            $this->T->owned_comparisons = array();
        }

        if ($owned_comparisons) {
            $invited_comparisons_ids = $ComparisonsItem->select('comparison', SQL::quote('user = ? and comparison not in (?) and status != ?', $this->ENV->UID, arrays::list_fields($owned_comparisons, 'id'), 'rejected'));
        } else {
            $invited_comparisons_ids = $ComparisonsItem->select('comparison', SQL::quote('user = ? and status != ?', $this->ENV->UID, 'rejected'));
        }
        if ($invited_comparisons_ids) {
            $invited_comparisons = $Comparisons->select(array('id', 'user', 'comment'), SQL::quote('id in (?)', $invited_comparisons_ids));
            $raw_invited_comparisons_item = $ComparisonsItem->select(array('id', 'user', 'goal', 'comparison', 'status'), SQL::quote('comparison in (?)', arrays::list_fields($invited_comparisons, 'id')));
            $invited_comparisons_item_goals = $Goals->select(array('id', 'title'), SQL::quote('id in (?)', arrays::list_fields($raw_invited_comparisons_item, 'goal')));
            foreach ($raw_invited_comparisons_item as &$item) {
                $item['goal_info'] = array_get($invited_comparisons_item_goals, $item['goal'], false);
            }
            $invited_comparisons_item = arrays::group_by_field($raw_invited_comparisons_item, 'comparison');
            foreach ($invited_comparisons as &$comparison) {
                $comparison['items'] = array_get($invited_comparisons_item, $comparison['id'], array());
            }
            $this->T->invited_comparisons = $invited_comparisons;
        } else {
            $this->T->invited_comparisons = array();
        }
        
                
        $this->T->include('this.users-compare-page', 'content');
        
        $this->T->page_title = $this->LNG->Compare . ' / ' . htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'me-compares-page';
        return $this->T->return('templates.inner');
    }
}