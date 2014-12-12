<?php
import('base.controller.BaseApi');

class UsersApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'search' => '(search): null'
        ));
    }
    
    public function handleGetSearch(array $request = array())
    {
        $query = array_get($request, 'q');
        $page = array_get($request, 'p');
        $perpage = array_get($request, 'pp');
        if (!$query) {
            return $this->respondOk(array('error' => 'No query'));
        }
        
        import('lib.ir');
        $words = ir::tokenize($query);
        $words_tf = array_combine($words, array_fill(0, count($words), 1));
        list($users, $pages) = self::search($words_tf, $page, $perpage);

        return $this->respondOk(array('users' => $users, 'pages' => $pages));
    }
    
    public static function search($words, $page = 1, $limit = 30)
    {
        $search_key = 'search:' . implode('-', array_keys($words)) . ':' . $page . ':' . $limit;
        $Memcache = Framework::get('Memcache');
        $results = $Memcache->get($search_key);
        if (!$results) {
            import('lib.ir');
            import('3dparty.predis');
            $redis = new Predis_Client();
            $redis->select(1);
            $users_ids = array();
            $query_vector = array();
            $D = $redis->get('usr.len');
            foreach ($words as $word => $tf) {
                $id = $redis->get("wrd.$word.id");
                if (!$id) {
                    continue;
                }
                $df = $redis->get("wrd.$id.df");
                $idf = log($D / $df);
                $query_vector[$id] = $tf * $idf;
                $users_ids = array_merge($users_ids, $redis->smembers("wrd.$id.usr"));
            }
            $users_ids = array_unique($users_ids);
            $users_sim = array();
            $Conf = Framework::get('Conf');
            foreach ($users_ids as $i => $user_id) {
                if ($user_id == $Conf->ENV->UID) {
                    unset($users_ids[$i]);
                    continue;
                }
                $user_vector = $redis->hgetall("usr.$user_id.vec");
                $users_sim[] = ir::cosine($query_vector, $user_vector);
            }
            
            array_multisort($users_sim, SORT_DESC | SORT_NUMERIC, $users_ids, SORT_DESC | SORT_NUMERIC);
            
            // paging
            import('lib.page');
            $total = count($users_ids);
            list($start, $pages) = page::calculate($total, $page, $limit);
            
            $sliced = array_slice($users_ids, $start, $limit);
            
            // get users
            $users = self::selectUsers($sliced);
            $n = 0;
            foreach ($sliced as $user_id) {
                $users[$n]['sim'] = $users_sim[$n];
                $n ++;
            }
            $results = array($users, page::generate($pages, $page));
            $Memcache->set($search_key, $results, MEMCACHE_COMPRESSED, 3600 * 24); // 1 day
        }

        return $results;
    }
    
    public static function selectUsers($ids)
    {
        if (!$ids) {
            return array();
        }
        
        // get users
        $Users = new UsersModel;
        $users = $Users->select(array('id', 'login', 'name', 'location', 'bio', 'thumbnail'), SQL::quote('id in (?)', $ids));
        
        // get goals
        $Goals = new DataTable('goals');
        $goals = arrays::group_by_field($Goals->select(array('user', 'title'), SQL::quote('user in (?) and privacy = ?', $ids, 'public')), 'user');
        
        // get connections
        if (Access::loggedIn()) {
            $Connections = new DataTable('connections');
            $Conf = Framework::get('Conf');
            $connections = arrays::map($Connections->select(array('user_to', 'status'), SQL::quote('user_from = ? and user_to in (?)', $Conf->ENV->UID, $ids)), 'user_to', 'status');
        }
        
        $sorted = array();
        $n = 0;
        foreach ($ids as $user_id) {
            $user = $users[$user_id];
            if (isset($goals[$user_id])) {
                $user['goals'] = array_values($goals[$user_id]);
            } else {
                $user['goals'] = array();
            }
            if (isset($connections[$user_id])) {
                $user['connection'] = $connections[$user_id];
            } else {
                $user['connection'] = false;
            }
            $n ++;
            $sorted[] = $user;
        }
        
        return $sorted;
    }
}
?>