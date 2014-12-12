<?php
import('base.controller.BaseCommand');

class IndexUsersCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        import('3dparty.predis');
        import('lib.ir');
        
        $this->redis = new Predis_Client();
        $this->redis->select(1);
        /*
         * word : id
         * id   : word
         * id : df
         * user : [id]
         * id : [user]
         */
        
        $Users = new DataTable('users');
        $users_ids = $Users->select('id', SQL::quote('public'));

        // get goals
        $Goals = new DataTable('goals');
        $goals = arrays::group_by_field($Goals->select(array('title', 'user'), SQL::quote('user in (?) and privacy = ?', $users_ids, 'public')), 'user');
        
        // get charts
        $Charts = new DataTable('charts');
        $charts = arrays::group_by_field($Charts->select(array('title', 'user'), SQL::quote('user in (?)', $users_ids)), 'user');
        
        // get user info
        $index_fields = array('name', 'bio', 'location');
        $users = $Users->select(array('id', 'name', 'bio', 'location'), SQL::quote('id in (?)', $users_ids));
        
        // clear previous data
        $this->init_words();
        $this->init_users();
        
        foreach ($users as $user) {
            $words = array();
            
            // words from profile
            foreach ($index_fields as $field) {
                $words = array_merge($words, ir::tokenize($user[$field]));
            }
            
            // words from goals
            if (isset($goals[$user['id']])) {
                foreach ($goals[$user['id']] as $goal) {
                    $words = array_merge($words, ir::tokenize($goal['title']));
                }
            }
            
            // words from charts
            if (isset($charts[$user['id']])) {
                foreach ($charts[$user['id']] as $chart) {
                    $words = array_merge($words, ir::tokenize($chart['title']));
                }
            }
            
            // get word frequencies
            $words_tf = array();
            foreach ($words as $word) {
                if (!isset($words_tf[$word])) {
                    $words_tf[$word] = 0;
                }
                $words_tf[$word] ++;
            }

            // index words
            foreach ($words_tf as $word => $tf) {
                if (!strlen($word)) {
                    continue;
                }
                $word_id = $this->get_word_id($word);
                $this->add_word($user['id'], $word_id, $tf);
            }
        }
        
        $D = count($users);
        $this->redis->set('usr.len', $D);
        foreach ($users as $user) {
            $words_ids = $this->redis->hgetall("usr.{$user['id']}.wrd");
            
            foreach ($words_ids as $id => $tf) {
                $df = $this->redis->get("wrd.$id.df");
                $idf = round(log($D / $df), 3);
                $this->redis->hset("usr.{$user['id']}.vec", $id, $tf * $idf);
            }
        }
    }
    
    protected function init_user($user_id)
    {
        $this->redis->del("usr.$user_id.wrd");
        $this->redis->del("usr.$user_id.vec");
    }
    
    protected function init_users()
    {
        $keys = $this->redis->keys('usr.*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }        
    }
    
    protected function init_words()
    {
        $keys = $this->redis->keys('wrd.*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }
    
    protected function get_word_id($word)
    {
        $id = $this->redis->get("wrd.$word.id");
        if (!$id) {
            $id = $this->redis->incr("wrd.last_id");
            $this->redis->set("wrd.$word.id", $id);
            $this->redis->set("wrd.$id.body", $word);
        }
        return $id;
    }
    
    protected function add_word($user_id, $word_id, $tf)
    {
        $this->redis->incr("wrd.$word_id.df");
        $this->redis->sadd("wrd.$word_id.usr", $user_id);
        $this->redis->hset("usr.$user_id.wrd", $word_id, $tf);
    }
}
?>