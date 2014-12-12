<?php
import('base.controller.BasePage');

class UsersPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'default'  => '(?:p(\d+))?: page',
            'map'      => '(location): null',
        ));
    }
    
    public function handleDefault(array $request = array()) 
    {
		
	   import('static.api.users.UsersApi');
        
        $query = array_get($request, 'q');
        if ($query) {
            $Api = new UsersApi;
            $Api->response_type = 'php';
            $page = array_get($request, 'p', 1);
            $this->T->response = $Api->handleGetSearch(array('q' => $query, 'p' => $page, 'pp' => 30));
            $this->T->query = $query;
            $this->T->current_page = $page;
        } else {
            $Users = new DataTable('users');
            $page = $this->ENV->get('page', 1);
            if (Access::loggedIn() && $page == 1) {
                // get users near by
                $select_key = "near-you:{$this->ENV->UID}:{$this->ENV->user->geo}";
                $near_you = $this->Memcache->get($select_key);
                if (!$near_you) {
                    $near_you_ids = $Users->select(array('id', 'distance' => SQL::quote('user_distance(geo, ?)', $this->ENV->user->geo)), SQL::quote('public and geo and id != ? and user_distance(geo, ?) <= ?', $this->ENV->UID, $this->ENV->user->geo, 300), 'distance', 10);
                    $near_you = UsersApi::selectUsers(arrays::list_fields($near_you_ids, 'id'));
                    $this->Memcache->set($select_key, $near_you, MEMCACHE_COMPRESSED, 3600);
                }
                $this->T->near_you = $near_you;
                
                // get similar users
                import('lib.ir');
                
                $Goals = new DataTable('goals');
                $goals = $Goals->select('title', SQL::quote('user = ?', $this->ENV->UID));
                
                $Charts = new DataTable('charts');
                $charts = $Charts->select('title', SQL::quote('user = ?', $this->ENV->UID));
                
                $words = array();
                $words = array_merge($words, ir::tokenize($this->ENV->user->bio));
                $words = array_merge($words, ir::tokenize($this->ENV->user->location));
                
                // words from goals
                foreach ($goals as $goal) {
                    $words = array_merge($words, ir::tokenize($goal));
                }
                
                // words from charts
                foreach ($charts as $chart) {
                    $words = array_merge($words, ir::tokenize($chart));
                }
                
                // get word frequencies
                $words_tf = array();
                foreach ($words as $word) {
                    if (!isset($words_tf[$word])) {
                        $words_tf[$word] = 0;
                    }
                    $words_tf[$word] ++;
                }
                
                import('static.api.users.UsersApi');
                list($similar, $tmp) = UsersApi::search($words_tf);
                $filtered = array();
                foreach ($similar as $user) {
                    if ($user['sim'] < 0.3) {
                        break;
                    }
                    $filtered[] = $user;
                }
                $this->T->similar = $filtered;
            }
            
            // get other users
            import('lib.page');
            $total = $Users->count('*', SQL::quote('public'));
            $perpage = 30;
            list($start, $pages) = page::calculate($total, $page, $perpage);
            
            $select_key = "recent-users:$start:$perpage";
            $recent_users = $this->Memcache->get($select_key);
            if (!$recent_users) {
                $recent_users = UsersApi::selectUsers($Users->select('id', SQL::quote('public'), 'id:desc', "$start:$perpage"));
                $this->Memcache->set($select_key, $recent_users, MEMCACHE_COMPRESSED, 3600);
            }
            
            $this->T->recent_users = $recent_users;
            $this->T->current_page = $page;
            $this->T->pages = page::generate($pages, $page);
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        
        $this->T->include('this.users-index', 'content');
        
        $this->T->page_title = $this->LNG->Users;
        $this->T->page_gray = true;
        $this->T->page_id = 'users-index-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleMap() 
    {
        $this->Conf->loadLanguage('site');
        
        $Users = new UsersModel;
        $users = $Users->select(array('name', 'thumbnail', 'geo'), SQL::quote('public and geo'), 'registered:desc', 100);
        $this->T->users = array_values($users);
        $this->T->include('this.users-map', 'content');
        
        $this->T->page_title = 'Users';
        $this->T->page_gray = true;
        $this->T->page_id = 'users-map-page';
        return $this->T->return('templates.inner');
    }
}
?>