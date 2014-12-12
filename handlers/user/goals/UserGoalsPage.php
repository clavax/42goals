<?php
import('base.controller.BaseUserPage');

class UserGoalsPage extends BaseUserPage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'default' => '(?:(\d{8}))?: date',
        ));
        
    }
    
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        $this->Conf->loadLanguage('me');
        
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        import('model.Goals');
        import('model.Icons');

        $Users = new UsersModel;
        $Goals = new GoalsModel;
        $fields = array(
            'id', 
            'title', 
            'text', 
            'type', 
            'icon_item', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'position', 
            'tab',
            'privacy',
            'user'
        );
        if ($user['id'] == $this->ENV->UID) {
            $predicate = SQL::quote('user = ? and archived is NULL', $user['id']);
        } else {
            $predicate = SQL::quote('user = ? and privacy = ? and archived is NULL', $user['id'], 'public');
        }
        $goals = $Goals->select($fields, $predicate, array('position', 'id'));
        
        // get compared goals
        $ComparisonsItem = new DataTable('comparisons_item');
        $comparisons = $ComparisonsItem->select(array('comparison', 'user', 'goal'), SQL::quote('user = ? and status = ?', $user['id'], 'accepted'));
        if ($comparisons) {
            $all_comparisons = $ComparisonsItem->select(array('comparison', 'goal', 'user'), SQL::quote('comparison in (?) and user != ? and status = ?', arrays::list_fields($comparisons, 'comparison'), $user['id'], 'accepted'));
            $comparisons_goals = array();
            foreach ($comparisons as $comparison) {
                if ($comparison['user'] == $user['id']) {
                    $comparisons_goals[$comparison['comparison']] = $comparison['goal'];
                }
            }
            $compared_goals = array();
            $compared_goals_ids = array();
            $compared_users_ids = array();
            foreach ($all_comparisons as $comparison) {
                if ($comparison['user'] != $user['id']) {
                    $user_goal_id = $comparisons_goals[$comparison['comparison']];
                    $compared_goals[$user_goal_id][] = $comparison['goal'];
                    $compared_goals_ids[] = $comparison['goal'];
                    $compared_users_ids[] = $comparison['user'];
                }
            }
            $compared_goals_data = $Goals->select($fields, SQL::quote('id in (?) and privacy = ?', $compared_goals_ids, 'public'), array('position', 'id'));
            $compared_users_data = $Users->select(array('id', 'name', 'login', 'thumbnail'), SQL::quote('id in (?)', $compared_users_ids));
            foreach ($compared_goals_data as &$compared_goal) {
                $compared_goal['user_data'] = array_get($compared_users_data, $compared_goal['user']);
            }
            unset($compared_goal);
        } else {
            $compared_goals_data = array();
        }
        
        $all_goals = array();
        foreach ($goals as $goal) {
            $all_goals[] = $goal;
            if ($goal['privacy'] == 'public' && isset($compared_goals[$goal['id']])) {
                foreach ($compared_goals[$goal['id']] as $goal_id) {
                    $compared_goal = array_get($compared_goals_data, $goal_id);
                    if ($compared_goal) {
                        $compared_goal['icon_true'] = $goal['icon_true'];
                        $compared_goal['icon_false'] = $goal['icon_false'];
                        $compared_goal['icon_item'] = $goal['icon_item'];
                        $compared_goal['position'] = $goal['position'];
                        $all_goals[] = $compared_goal;
                    }
                }
            }
        }
        
        $goals = $all_goals;
        
        // dates
        $now = date('Y-m-d');
        $day = date('N') - 1;
        
        if ($this->ENV->date) {
            $start_time = strtotime($this->ENV->date);
        } else {
            $start_time = strtotime("$now -$day days");
        }
        
        $start = date('Y-m-d', $start_time);
        $prev_start_time = strtotime("$start -7 days");
        $prev_end_time   = strtotime("$start -1 days");
        $next_start_time = strtotime("$start +7 days");
        $next_end_time   = strtotime("$start +13 days");
        
        // get data
        $data_by_goal_date = array();
        $plan_by_goal = array();
        if ($goals) {
            $Data = new DataTable('data');
            $data = $Data->select(array('goal', 'date', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($data as $row) {
                $data_by_goal_date[$row['goal']][$row['date']] = array('value' => $row['value'], 'text' => $row['text']);
            }
        }
        
        $admin_id = $this->CNF->languages[$this->ENV->language]->admin;
        $admin_ids = array();
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        
        $Icons = new IconsModel;
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user in (?)', $user['id'], $admin_ids), array('position', 'id')), 'id', 'src');
        
        $this->T->user  = $user;
        $this->T->today = $now;
        $this->T->start = $start;
        $this->T->goals = array_values($goals);
        $this->T->icons = $icons;
        $this->T->data  = $data_by_goal_date;
        
        $this->T->prev_start = date('Ymd', $prev_start_time);
        $this->T->next_start = date('Ymd', $next_start_time);
        
        $this->T->prev_week = self::format_range($prev_start_time, $prev_end_time, '&mdash;', array('d' => 'j', 'm' => 'F'));
        $this->T->next_week = self::format_range($next_start_time, $next_end_time, '&mdash;', array('d' => 'j', 'm' => 'F'));
        
        $this->T->thread_type = 'goalsweek';
        $this->T->thread_id   =  $user['id'] . '-' . date('Ymd', $start_time);
        
        $this->T->include('this.users-goals-page', 'content');
        
        $this->T->page_title = $this->LNG->Goals . ' / ' . htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'users-goals-page';
        return $this->T->return('templates.inner');
    }
}