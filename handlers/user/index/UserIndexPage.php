<?php
import('base.controller.BaseUserPage');

class UserIndexPage extends BaseUserPage
{
    public function handleDefault()
    {
        $user = $this->getUser();
        if (!$user) {
            if ($this->ENV->get('current_user') == 'private') {
                return $this->handleNinja();
            }
            return false;
        }
        
        if (Access::loggedIn() && $user['id'] == $this->ENV->UID) {
            return $this->handleMe();
        } else {
            return $this->handleUser($user);
        }
    }
    
    protected function handleNinja()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        
        $this->T->include('this.ninja-page', 'content');
        
        $this->T->page_title = $this->LNG->Ninja_detected;
        $this->T->page_gray = true;
        $this->T->page_id = 'ninja-page';
        return $this->T->return('templates.inner');
        
    }
    
    protected function handleMe()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Goals');
        import('model.Plan');
        import('model.Charts');
        import('lib.json');
        
        $Goals = new GoalsModel;
        $fields = array(
            'id', 
            'title', 
            'text', 
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'position', 
            'tab'
        );
        $predicate = SQL::quote('user = ? and archived is NULL', $this->ENV->UID);
        $goals = $Goals->select($fields, $predicate, array('position', 'id'));
        $data_by_goal_date = array();
        $plan_by_goal = array();
        if ($goals) {
            $Data = new DataTable('data');
            $data = $Data->select(array('goal', 'date', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($data as $row) {
                $data_by_goal_date[$row['goal']][$row['date']] = array('value' => $row['value'], 'text' => $row['text']);
            }
            
            $DataStart = new DataTable('data_start');
            $start = $DataStart->select(array('goal', 'date', 'start'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($start as $row) {
                $data_by_goal_date[$row['goal']][$row['date']]['start'] = $row['start'];
            }
            
            $Plan = new PlanModel;
            $plan = $Plan->select(array('id', 'goal', 'startdate', 'enddate', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'startdate'));
            foreach ($plan as $row) {
                $plan_by_goal[$row['goal']][$row['id']] = $row;
            }
        }
        
        $Charts = new ChartsModel;
        $charts = $Charts->select('*', SQL::quote('user = ?', $this->ENV->UID));
        
        $this->T->today = date('Y-m-d');
        $this->T->goals = array_values($goals);
        $this->T->data = $data_by_goal_date;
        $this->T->plan = $plan_by_goal;
        $this->T->charts = $charts;
        
        $this->T->user = $this->getUser();
        $this->T->include('this.me-index-page', 'content');
        
        $this->T->page_title = htmlspecialchars(strip_tags($this->ENV->user->name));
        $this->T->page_gray = true;
        $this->T->page_id = 'me-index-page';
        return $this->T->return('templates.inner');
    }
    
    protected function handleUser($user)
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        // get charts
        import('model.Charts');
        $Charts = new ChartsModel;
        $charts = $Charts->select('*', SQL::quote('user = ?', $user['id']));
        
        // get goals
        $goals_ids = arrays::list_fields($charts, 'goal');
        
        if ($goals_ids) {
            import('model.Goals');
            $Goals = new GoalsModel;
            $fields = array(
                'id', 
                'type', 
                'aggregate', 
            );
            $predicate = SQL::quote('user = ? and id in (?)', $user['id'], $goals_ids);
            $goals = arrays::by_field($Goals->select($fields, $predicate), 'id');
        } else {
            $goals = array();
        }
        
        // get goals' data
        $data_by_goal_date = array();
        if ($goals) {
            $Data = new DataTable('data');
            $data = $Data->select(array('goal', 'date', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($data as $row) {
                $data_by_goal_date[$row['goal']][$row['date']] = $row['value'];
            }
        }
        
        // get data for each chart
        foreach ($charts as &$chart) {
            $data = array_get($data_by_goal_date, $chart['goal'], array());
            $goal = $goals[$chart['goal']];
            
            // set period
            $earliest = strtotime(arrays::first(array_keys($data)));
            $today = time();
            $end = $today;
            $end_str = date('Y-m-d', $end);
                
            switch ($chart['period']) {
            case 'week':
                $start = strtotime("$end_str -7day");
                break;
                
            default:
            case 'month':
                $start = strtotime("$end_str -31day");
                break;
                
            case 'quarter':
                $start = strtotime("$end_str -122day");
                break;
                
            case 'year':
                $start = strtotime("$end_str -366day");
                break;
            }
            if ($start < $earliest) {
                $start = $earliest;
            }
            
            $start_str = date('Y-m-d', $start);
            
            // set aggregate
            $aggregate = '';
            if ($goal['type'] == 'boolean') {
                $aggregate = 'boolean';
            } else {
                $functions = array('sum', 'min', 'max', 'avg');
                $aggregate = array_get($functions, $goal['aggregate'], 'sum');
            }
            
            // group data
            if ($chart['type'] == 'pie') {
                $grouped = array(
                    array('extended' => 'no', 'title' => 'no', 'value' => 0),
                    array('extended' => 'yes', 'title' => 'yes', 'value' => 0)
                );
                for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                    $actual = array_get($data, $day_str, '');
                    if ($actual === '1') {
                        $grouped[1]['value'] ++;
                    } else if ($actual === '0') {
                        $grouped[0]['value'] ++;
                    }
                }
            } else if ($chart['accumulate']) {
                $grouped = array();
                $value = 0;
                for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                    $actual = array_get($data, $day_str, '');
                    if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                        $actual /= 3600;
                    }
                    
                    $value += $actual;
                    
                    $row = array(
                        'value' => (float) $value,
                        'title' => date('n/j', $day),
                        'extended' => date('l, F j', $day)
                    );
                    $grouped[] = $row;
                }                
            } else {
                $grouped = array();
                
                switch ($chart['groupby']) {
                default:
                case 'day':
                    $prev = 0;
                    for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                        $actual = array_get($data, $day_str, '');
                        if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                            $actual /= 3600;
                        }
                        
                        $value = $actual != '' ? $actual : ($chart['fill_empty'] ? $prev : 0);
                        
                        $row = array(
                            'value' => (float) $value,
                            'title' => date('n/j', $day),
                            'extended' => date('l, F j', $day)
                        );
    
                        $grouped[] = $row;
                        $prev = $value;
                    }
                    break;
                    
                case 'week':
                    $values = array();
                    $prev = 0;
                    $week_start = $start;
                    $week = date('W', $start);
                    for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                        $actual = array_get($data, $day_str, '');
                        if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                            $actual /= 3600;
                        }
                        
                        $value = $actual != '' ? $actual : ($chart['fill_empty'] ? $prev : 0);
                        if (date('W', $day) != $week) {
                            $row = array(
                                'value' => (float) self::$aggregate($values),
                                'title' => self::format_range($week_start, strtotime("$day_str -1day"), '-', array('d' => 'j', 'm' => 'M')),
                                'extended' => self::format_range($week_start, strtotime("$day_str -1day"), '-', array('d' => 'j', 'm' => 'M'))
                            );
                            
                            $grouped[] = $row;
                            $week = date('W', $day);
                            $week_start = $day;
                            $values = array($value);
                        } else {
                            $values[] = $value;
                        }
                        $prev = $value;
                    }
                    if (count($values)) {
                        $row = array(
                            'value' => (float) self::$aggregate($values),
                            'title' => self::format_range($week_start, strtotime("$day_str -1day"), '-', array('d' => 'j', 'm' => 'M')),
                            'extended' => self::format_range($week_start, strtotime("$day_str -1day"), '-', array('d' => 'j', 'm' => 'M'))
                        );
            
                        $grouped[] = $row;
                    }
                    break;
                    
                case 'month':
                    $values = array();
                    $prev = 0;
                    $month = date('m', $start);
                    for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                        $actual = array_get($data, $day_str, '');
                        if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                            $actual /= 3600;
                        }
                        
                        $value = $actual != '' ? $actual : ($chart['fill_empty'] ? $prev : 0);
                        if (date('m', $day) != $month) {
                            $row = array(
                                'value' => (float) self::$aggregate($values),
                                'title' => date('M', strtotime("$day_str -1day")),
                                'extended' => date('F', strtotime("$day_str -1day"))
                            );
    
                            $grouped[] = $row;
                            $month = date('m', $day);
                            $values = array($value);
                        } else {
                            $values[] = $value;
                        }
                        $prev = $value;
                    }
                    if (count($values)) {
                        $row = array(
                            'value' => self::$aggregate($values),
                            'title' => date('M', strtotime("$day_str -1day")),
                            'extended' => date('F', strtotime("$day_str -1day"))
                        );
    
                        $grouped[] = $row;
                    }
                    break;
    
                case 'weekday':
                    $grouped = array(
                        array('value' => array(), 'title' => 'Mon', 'extended' => 'Monday'),
                        array('value' => array(), 'title' => 'Tue', 'extended' => 'Tueday'),
                        array('value' => array(), 'title' => 'Wed', 'extended' => 'Wednesday'),
                        array('value' => array(), 'title' => 'Thu', 'extended' => 'Thursday'),
                        array('value' => array(), 'title' => 'Fri', 'extended' => 'Friday'),
                        array('value' => array(), 'title' => 'Sat', 'extended' => 'Saturday'),
                        array('value' => array(), 'title' => 'Sun', 'extended' => 'Sunday'),
                    );
                    $prev = 0;
                    for ($day = $start, $day_str = $start_str; $day < $end; $day = strtotime("$day_str +1day"), $day_str = date('Y-m-d', $day)) {
                        $actual = array_get($data, $day_str, '');
                        if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                            $actual /= 3600;
                        }
                        
                        $value = $actual != '' ? $actual : ($chart['fill_empty'] ? $prev : 0);
                        
                        $grouped[date('N', $day) - 1]['value'][] = $value;
                        $prev = $value;
                    }
                    foreach ($grouped as &$group) {
                        $group['value'] = self::$aggregate($group['value']);
                    }
                    break;
                }
            }
            $chart['data'] = $grouped;
        }
        
        $this->T->user = $user;
        $this->T->charts = $charts;
        
        $this->T->include('this.users-page', 'content');
        
        $this->T->page_title = htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'users-page';
        return $this->T->return('templates.inner');
    }
    
    public static function sum($array) {
        return array_sum($array);
    }
    
    public static function min($array) {
        return min($array);
    }
    
    public static function max($array) {
        return max($array);
    }
    
    public static function avg($array) {
        return array_sum($array) / count($array);
    }
    
    public static function boolean($array) {
        $yes = 0;
        $no = 0;
        foreach ($array as $value) {
            if ($value === '1') {
                $yes ++;
            } else if ($value === '0') {
                $no ++;
            }
        }
        return ($yes + $no > 0) ? $yes / ($yes + $no) : 0;
    }
}
?>