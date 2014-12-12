<?php
import('base.controller.BaseApi');
import('domains.api.v1.oauth.OAuthApi');

class DataApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $date = '(\d{1,2})(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)(\d{2})';
        $this->addRule(array(
            'day'        => "$date: day/month/year",
            'goalday'    => "(\d+)/$date: goal/day/month/year",
            'period'     => "$date-$date: day1/month1/year1/day2/month2/year2",
            'goalperiod' => "(\d+)/$date-$date: goal/day1/month1/year1/day2/month2/year2",
        ));
    }
    
    public function handleGetDay(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authenticated'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Data  = new DataTable('data');
        
        $goals = $Goals->select('id', SQL::quote('user = ?', $this->ENV->UID), 'position');
        
        $date = self::normalizeDate($this->ENV->year, $this->ENV->month, $this->ENV->day);
        $data = $Data->select(array('goal', 'value', 'text', 'modified', 'created'), SQL::quote('date = ? and goal in (?)', $date, $goals), 'date');
        
        return $this->respondOk(array('date' => $date, 'data' => $data));
    }
    
    public function handleGetGoalDay(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authenticated'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Data  = new DataTable('data');
        
        $goal = $Goals->view($this->ENV->goal, array('id', 'type', 'user'));
        
        if ($goal['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $date = self::normalizeDate($this->ENV->year, $this->ENV->month, $this->ENV->day);
        $data = $Data->select(array('value', 'text', 'modified', 'created'), SQL::quote('goal = ? and date = ?', $this->ENV->goal, $date), 'date', 1);
        
        return $this->respondOk(array('date' => $date, 'goal' => $this->ENV->goal, 'data' => $data));
    }
    
    public function handlePostGoalDay(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authenticated'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Data  = new DataTable('data');
        
        $goal = $Goals->view($this->ENV->goal, array('id', 'type', 'user'));
        
        if ($goal['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $date = self::normalizeDate($this->ENV->year, $this->ENV->month, $this->ENV->day);
        
        // modify here
        $condition = SQL::quote('goal = ? and date = ?', $this->ENV->goal, $date);
        $exists = $Data->exists_where($condition);
        $data = array();
        if (!$exists) {
            $data = array('goal' => $this->ENV->goal, 'date' => $date);
        }
        if (isset($request['value'])) {
            $data['value'] = $request['value'];
        }
        if (isset($request['text'])) {
            $data['text'] = $request['text'];
        }
        $result = false;
        if ($exists) {
            $data['modified'] = date('Y-m-d H:i:s');
            $result = $Data->update_where($data, $condition);
        } else {
            $data['created'] = date('Y-m-d H:i:s');
            $data['modified'] = date('Y-m-d H:i:s');
            $result = $Data->insert($data);
        }
        if (!$result) {
            $this->respondOk(array('error' => 'sql error'));
        }        
        
        return $this->respondOk(array('date' => $date, 'goal' => $this->ENV->goal, 'data' => $data));
    }
    
    public function handleGetPeriod(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authenticated'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Data  = new DataTable('data');
        
        $goals = $Goals->select('id', SQL::quote('user = ?', $this->ENV->UID), 'position');
        
        $date1 = self::normalizeDate($this->ENV->year1, $this->ENV->month1, $this->ENV->day1);
        $date2 = self::normalizeDate($this->ENV->year2, $this->ENV->month2, $this->ENV->day2);
        $data = $Data->select(array('date', 'goal', 'value', 'text', 'modified', 'created'), SQL::quote('goal in (?) and date between ? and ?', $goals, $date1, $date2), 'date');
        
        return $this->respondOk(array('date' => "$date1--$date2", 'data' => $data));
    }
    
    public function handleGetGoalPeriod(array $request = array())
    {
        if (!OAuthApi::isAuthorized()) {
            return $this->respondOk(array('error' => 'not authenticated'));
        }
        
        import('model.Goals');
        $Goals = new GoalsModel;
        $Data  = new DataTable('data');
        
        $goal = $Goals->view($this->ENV->goal, array('id', 'type', 'user'));
        if ($goal['user'] != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $date1 = self::normalizeDate($this->ENV->year1, $this->ENV->month1, $this->ENV->day1);
        $date2 = self::normalizeDate($this->ENV->year2, $this->ENV->month2, $this->ENV->day2);
        $data = $Data->select(array('date', 'value', 'text', 'modified', 'created'), SQL::quote('goal = ? and date between ? and ?', $this->ENV->goal, $date1, $date2), 'date');
        
        return $this->respondOk(array('date' => "$date1--$date2", 'goal' => $this->ENV->goal, 'data' => $data));
    }
    
    public static function normalizeDate($year, $month, $day)
    {
        $months = array('jan' => 1, 'feb' => 2, 'mar' => 3, 
                        'apr' => 4, 'may' => 5, 'jun' => 6, 
                        'jul' => 7, 'aug' => 8, 'sep' => 9, 
                        'oct' => 10, 'nov' => 11, 'dec' => 12);
        
        $year  = ($year < 20 ? '20' : '19') . $year;
        $month = str_pad($months[$month], 2, '0', STR_PAD_LEFT);
        $day   = str_pad($day, 2, '0', STR_PAD_LEFT);
        $date  = "$year-$month-$day";
        
        return $date;
    }
}
?>