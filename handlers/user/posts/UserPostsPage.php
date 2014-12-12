<?php
import('base.controller.BaseUserPage');

class UserPostsPage extends BaseUserPage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'posts'    => '(?:p(\d+))?: page',
            'post'     => '(\d+): id',
            'postadd'  => '(add): null',
            'postedit' => '(\d+)/(edit): id/null'
        ));
    }
    public function handleDefault()
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        import('lib.url');
        import('lib.locale');
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('posts');
        
        import('model.Posts');
        $Posts = new PostsModel;
        
        import('lib.page');
        import('lib.output');
        
        $page = $this->ENV->page;
        $total = $Posts->count('*', SQL::quote('user = ?', $user['id']));
        $perpage = 30;
        list($start, $pages) = page::calculate($total, $page, $perpage);
        
        $select = SQL::select(array('posts.id', 'date', 'title', 'text', 'posts.type', 'posts.url'), 'posts')
                ->join('communities', SQL::quote('community = communities.id'), array('community_name' => 'name', 'community_title' => 'title'))
                ->where(SQL::quote('posts.user = ?', $user['id']))
                ->order('date', 'desc')
                ->order('id', 'desc')
                ->limit($start, $perpage);
                
        $this->db->query($select);
        $posts = $this->db->fetch_all();

        if ($posts) {
            $Comments = new DataTable('comments');
            $comments = arrays::map($Comments->select(array('thread_id', 'count' => 'count(*)'), SQL::quote('thread_type = ? and thread_id in (?)', 'post', arrays::list_fields($posts, 'id')), null, null, 'thread_id'), 'thread_id', 'count');
            
            $maxlen = 300;
            foreach ($posts as &$post) {
                $post['preview'] = output::text_preview($post['text'], $maxlen);
                $post['image'] = output::first_img($post['text']);
                $post['comments'] = array_get($comments, $post['id'], 0);
            }
        }
        
        $this->T->user  = $user;
        $this->T->current_page = $page;
        $this->T->pages = page::generate($pages, $page);
        $this->T->posts = array_values($posts);
        
        $this->T->include('this.users-posts-page', 'content');
        
        $this->T->page_title = $this->LNG->Posts . ' / ' . htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'users-posts-page';
        return $this->T->return('templates.inner');
    }
    
    public function handlePost()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('posts');
        
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        
        $select = SQL::select(array('posts.id', 'date', 'title', 'text', 'posts.type', 'posts.url'), 'posts')
                ->join('communities', SQL::quote('community = communities.id'), array('community_name' => 'name', 'community_title' => 'title'))
                ->where(SQL::quote('posts.id = ? and posts.user = ?', $this->ENV->id, $user['id']))
                ->limit(1);
                
        $this->db->query($select);
        $post = $this->db->fetch();
                
        if (!$post) {
            return false;
        }
        
        $this->T->user = $user;
        $this->T->post = $post;
        
        $this->T->thread_id = 'post-' . $post['id'];
        
        $this->T->include('this.users-post-page', 'content');
        
        $this->T->page_title = htmlspecialchars(strip_tags($post['title'])) . ' / ' . htmlspecialchars(strip_tags($user['name']));
        $this->T->page_gray = true;
        $this->T->page_id = 'users-post-page';
        return $this->T->return('templates.inner');
    }
    

    public function handlePostEdit()
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
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        $this->Conf->loadLanguage('posts');
        
        import('model.Posts');
        $Posts = new PostsModel;
        $post = $Posts->select(array('id', 'date', 'title', 'text', 'community'), SQL::quote('id = ? and user = ?', $this->ENV->id, $user['id']), null, 1);
        if (!$post) {
            return false;
        }
        $this->T->post = $post;
        
        $this->T->communities = $this->getCommunities();
        
        $this->T->user = $user;
        $this->T->include('this.me-posts-edit', 'content');
        
        $this->T->page_gray = true;
        $this->T->page_title = $this->LNG->Edit_post . ' / ' . htmlspecialchars(strip_tags($post['title']));
        $this->T->page_id = 'me-posts-edit';
        return $this->T->return('templates.inner');
    }
    
    public function handlePostAdd()
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
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        $this->Conf->loadLanguage('posts');
        
        $this->T->communities = $this->getCommunities();
        
        $this->T->user = $user;
        $this->T->include('this.me-posts-add', 'content');
        
        $this->T->page_gray = true;
        $this->T->page_title = $this->LNG->Add_post;
        $this->T->page_id = 'me-posts-add';
        return $this->T->return('templates.inner');
    }
}
?>