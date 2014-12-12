<?php
import('base.controller.BasePage');

class CommunitiesPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'community' => '(\w+): name',
            'posts' => '(\w+)/(posts)(?:/p(\d+))?: name/null/page',
            'post' => '(\w+)/(posts)/(\w+): name/null/id',
            'members' => '(\w+)/(members): name/null',
        ));
    }
    
    protected function getCommunity() 
    {
        // get community info
        import('model.Communities');
        $Communities = new CommunitiesModel;
        $community = $Communities->select(array('id', 'title', 'name', 'picture', 'overview', 'description', 'user'), SQL::quote('name = ?', $this->ENV->name), null, 1);
        if (!$community) {
            return false;
        }

        if ($this->ENV->UID) {
            $Membership = new DataTable('community_membership');
            $community['joined'] = $Membership->exists_where(SQL::quote('user = ? and community = ?', $this->ENV->UID, $community['id']));
        }
        
        $this->ENV->community = $community;
        $this->T->community = $community;
        
        return $community;
    }
    
    public function handleDefault()
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('communities');

        // get communities
        import('model.Communities');
        $Communities = new CommunitiesModel;
        $communities = $Communities->select(array('id', 'title', 'name', 'thumbnail', 'overview'), SQL::quote('language = ? or language = ?', $this->ENV->language, '*'));
        
        // get posts
        import('model.Posts');
        $Posts = new PostsModel;
        foreach ($communities as &$community) {
            $community['posts'] = $Posts->select(array('id', 'title'), SQL::quote('community = ?', $community['id']), 'date:desc', 3);
        }
        
        $this->T->communities = $communities;
        $this->T->include('this.communities-list', 'content');
        
        $this->T->page_title = $this->LNG->Communities;
        $this->T->page_gray = true;
        $this->T->page_id = 'communities-index-page';
        return $this->T->return('templates.inner');
    }
    
    public function handleCommunity()
    {
        $community = $this->getCommunity();
        if (!$community) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('communities');
        $this->Conf->loadLanguage('feed');
        
        import('static.api.users.UsersApi');
        $Membership = new DataTable('community_membership');
        $members_ids = $Membership->select('user', SQL::quote('community = ?', $community['id']), 'time:desc', 6);
        if ($members_ids) {
            $members = UsersApi::selectUsers($members_ids);
        } else {
            $members = array();
        }
        $this->T->members = $members;
        
//        list($posts, $pages) = $this->getPosts($community, 1, 5);
//        $this->T->posts = $posts;

        $select = SQL::select(array('id', 'user', 'time', 'type', 'data'), 'feed')
                ->join('communities', SQL::quote('communities.id = feed.community'), array('community_name' => 'name', 'community_title' => 'title'))
                ->join('users', SQL::quote('users.id = feed.user'), array('user_login' => 'login', 'user_name' => 'name', 'user_thumbnail' => 'thumbnail'))
                ->order('time', 'desc')
                ->where(SQL::quote('community = ?', $community['id']))
                ->limit(30);
            
        $this->db->query($select);
        $this->T->activities = $this->db->fetch_all();
        
        $this->T->include('this.communities-page', 'content');
        
        $this->T->page_title = htmlspecialchars(strip_tags($community['title'])) . ' / ' . $this->LNG->Communities;
        $this->T->page_gray = true;
        $this->T->page_id = 'communities-page';
        return $this->T->return('templates.inner');
    }
    
    public function handlePosts()
    {
        $community = $this->getCommunity();
        if (!$community) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('posts');
        
        $page = $this->ENV->get('page', 1);
        list($posts, $pages) = $this->getPosts($community, $page, 30);
        $this->T->posts = $posts;
        $this->T->pages = $pages;
        $this->T->current_page = $page;
        
        $this->T->include('this.communities-posts-list', 'content');
        
        $this->T->page_title = $this->LNG->Posts . ' / ' . htmlspecialchars(strip_tags($community['title']));
        $this->T->page_gray = true;
        $this->T->page_id = 'communities-posts-page';
        return $this->T->return('templates.inner');
    }
    
    protected function getPosts($community, $page = 1, $perpage = 10)
    {
        // get posts
        import('lib.url');
        import('lib.page');
        import('lib.output');
        
        $Posts = new DataTable('posts');
        $total = $Posts->count('*', SQL::quote('community = ?', $community['id']));
        list($start, $pages) = page::calculate($total, $page, $perpage);

        $select = SQL::select(array('posts.id', 'date', 'title', 'text', 'posts.type', 'posts.url'), 'posts')
                ->join('users', SQL::quote('user = users.id'), array('name', 'login', 'thumbnail'))
                ->where(SQL::quote('community = ?', $community['id']))
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
        
        return array($posts, page::generate($pages, $page));
    }
    
    public function handlePost()
    {
        $this->Conf->loadLanguage('site');
        
        $community = $this->getCommunity();
        if (!$community) {
            return false;
        }
        
        // get post
        import('lib.url');
        import('model.Posts');
        $Posts = new PostsModel;
        $select = SQL::select(array('posts.id', 'date', 'title', 'text', 'posts.type', 'posts.url'), 'posts')
                ->join('users', SQL::quote('user = users.id'), array('name', 'login', 'thumbnail'))
                ->where(SQL::quote('posts.id = ? and community = ?', $this->ENV->id, $community['id']))
                ->limit(1);
        $this->db->query($select);
        $post = $this->db->fetch();
        $this->T->post = $post;
        
        $this->T->thread_id = 'post-' . $post['id'];
        
        $this->T->include('this.communities-posts-page', 'content');
        
        $this->T->page_title = htmlspecialchars(strip_tags($post['title'])) . ' / ' . htmlspecialchars(strip_tags($community['title']));
        $this->T->page_gray = true;
        $this->T->page_id = 'communities-post-page';
        return $this->T->return('templates.inner');
    }

    public function handleMembers()
    {
        $community = $this->getCommunity();
        if (!$community) {
            return false;
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        
        $Membership = new DataTable('community_membership');
        $Users = new DataTable('users');
        
        import('static.api.users.UsersApi');
        
        // get members
        $members_roles = arrays::map($Membership->select(array('user', 'role'), SQL::quote('community = ?', $community['id'])), 'user', 'role');
        $members = array();
        $admins  = array();
        if ($members_roles) {
            $users = UsersApi::selectUsers(array_keys($members_roles));
            foreach ($users as $user) {
                $user['role'] = $members_roles[$user['id']];
                if ($user['role'] == 'admin') {
                    $admins[] = $user;
                } else {
                    $members[] = $user;
                }
            }
        }
        
        $this->T->members = $members;
        $this->T->admins = $admins;
        $this->T->include('this.community-members-page', 'content');
        
        $this->T->page_title = $this->LNG->Members . ' / ' . htmlspecialchars(strip_tags($community['title']));
        $this->T->page_gray = true;
        $this->T->page_id = 'community-members-page';
        return $this->T->return('templates.inner');        
    }
}