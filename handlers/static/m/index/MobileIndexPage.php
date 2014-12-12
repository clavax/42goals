<?php
import('base.controller.BasePage');
import('lib.locale');
import('lib.json');

class MobileIndexPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'default' => '(\d{4}-\d{2}-\d{2})?: date'
        ));
    }
    
    public function handleDefault()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        import('model.Goals');
        import('model.Icons');
        import('model.Tabs');
        
        $Goals = new GoalsModel;

        $today = $this->ENV->get('date', date('Y-m-d'));
        
        // get goals
        $fields = array('id', 'title', 'text', 'type', 'icon_item', 'icon_zero', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate', 'tab');
        $goals = arrays::by_field($Goals->select($fields, SQL::quote('user = ? and archived is NULL', $this->ENV->UID), array('position', 'id')), 'id');
        
        // get data
        $Data = new DataTable('data');
        $data = $Data->select(array('goal', 'date', 'value', 'text'), SQL::quote('goal in (?) and date = ?', array_keys($goals), $today));
        
        // get icons
        $Icons = new IconsModel;
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user = 1', $this->ENV->UID), array('position', 'id')), 'id', 'src');

        foreach ($data as $row) {
            $row['value'] = substr($row['value'], 0, 8);
            $goals[$row['goal']]['data'] = $row;
        }
        
        foreach ($goals as &$goal) {
            foreach (array('icon_true', 'icon_false', 'icon_item') as $icon) {
                if ($goal[$icon] && isset($icons[$goal[$icon]])) {
                    $goal[$icon] = $icons[$goal[$icon]];
                }
            }
            if (!isset($goal['data'])) {
                $goal['data'] = array('value' => '', 'text' => '');
            } else if ($goal['type'] == 'time' || $goal['type'] == 'timer') {
                $time = isset($goal['data']['value']) ? $goal['data']['value'] : 0;
                $goal['h'] = floor($time / 3600);
                $time -= $goal['h'] * 3600;
                $goal['m'] = floor($time / 60);
                $time -= $goal['m'] * 60;
                $goal['s'] = $time;
            }
        }

        // get tabs
        $Tabs = new TabsModel;
        $tabs = $Tabs->select(array('id', 'title', 'position'), SQL::quote('user = ?', $this->ENV->UID), array('position', 'id'));
        
        
        $this->T->goals = $goals;
        $this->T->today = $today;
        $this->T->today_title = date('D M j', strtotime($today));
        $this->T->tabs = $tabs;
        
        $yesterday = strtotime($today . '-1 day');
        $tomorrow = strtotime($today . '+1 day');
        
        $this->T->yesterday = date('Y-m-d', $yesterday);
        $this->T->yesterday_title = date('M j', $yesterday);
        $this->T->tomorrow = date('Y-m-d', $tomorrow);
        $this->T->tomorrow_title = date('M j', $tomorrow);
        
        $this->T->include('this.content');
        $this->T->page_id = 'mobile-page';
        
        return $this->T->return('templates.mobile');
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            import('handlers.static.api.login.LoginApi');
            $LoginApi = new LoginApi;
            $result = $LoginApi->handlePostDefault($request);
            $xml = new DOMDocument();
            $xml->loadXML($result);
            if ($xml->getElementsByTagName('ok')->length) {
                $sid = $xml->getElementsByTagName('sid')->item(0)->nodeValue;
                if ($xml->getElementsByTagName('remember')->item(0)->nodeValue) {
                    $location = $this->CNF->site->sso . 'setcookie/' . $sid . '/?redirect=' . urlencode($this->URL->self);
                } else {
                    $location = $this->CNF->site->sso . 'setsession/' . $sid . '/?redirect=' . urlencode($this->URL->self);
                }
                header('Location: ' . $location);
                return true;
            } else {
                return $this->showLogin($request, true);
            }
        } else {
            $date = $this->ENV->get('date', date('Y-m-d'));
            
            if (isset($request['id'])) {
                $id = $request['id'];
                $decrement = false;
            } else if (isset($request['dec'])) {
                $id = $request['dec'];
                $decrement = true;
            } else {
                // error: no id
            }
            
            if (!isset($request['data'][$id])) {
                // error: no data
            }
            $data = $request['data'][$id];
            if (is_array($data)) {
                // time
                $data = $data['h'] * 3600 + $data['m'] * 60 + $data['s'];
            }
            if ($decrement) {
                $data -= 2;
            }
            
            import('handlers.static.api.goals.GoalsApi');
            $GoalsApi = new GoalsApi;
            $this->ENV->id = $id;
            $this->ENV->date = $date;
            $result = $GoalsApi->handlePostData(array('value' => $data));
            
            headers::location($this->URL->self);
            return true;
        }
    }    
    
    public static function pad($str)
    {
        return str_pad($str, 2, '0', STR_PAD_LEFT);
    }
}