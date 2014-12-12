<?php
import('base.controller.BasePage');

class BaseUserPage extends BasePage {

    public function getUser() 
    {
        $Users = new UsersModel;
        $user = $Users->select(array('id', 'login', 'name', 'picture', 'thumbnail', 'location', 'url', 'bio', 'public', 'paid_till'), SQL::quote('login = ?', $this->ENV->username), null, 1);
        if (array_get($user, 'public') || (Access::loggedIn() && $user['id'] == $this->ENV->UID)) {
            // get connection
            $Connections = new DataTable('connections');
            if (Access::loggedIn()) {
                $user['connection'] = $Connections->select('status', SQL::quote('user_from = ? and user_to = ?', $this->ENV->UID, $user['id']), null, 1);
            }
            
            // get friends
            $friends_ids = $Connections->select('user_to', SQL::quote('user_from = ? and status = ?', $user['id'], 'accepted'));
            if ($friends_ids) {
                $max_friends = 6;
                $user['friends'] = $Users->select(array('login', 'name', 'thumbnail'), SQL::quote('id in (?) and public', $friends_ids), null, $max_friends);
            }
            
            // get badges
            $Badges = new DataTable('badges');
            $user['badges'] = $Badges->select('type', SQL::quote('user = ?', $user['id']), 'date:desc');
            
            $this->ENV->current_user = $user;
            return $user;
        } else if (!array_get($user, 'public', 1)) {
            $this->ENV->current_user = 'private';
        }
        return false;
    }
    
    protected function getCommunities()
    {
        import('model.Communities');
        $Communities = new CommunitiesModel;
        $Membership = new DataTable('community_membership');
        
        // get communities
        $select = SQL::select('community', 'community_membership')
                ->join('communities', SQL::quote('id = community'))
                ->where(SQL::quote('community_membership.user = ? and (post_permission = ? or role = ?)', $this->ENV->UID, 'all', 'admin'));
                
        $this->db->query($select);
        $communities_ids = arrays::list_fields($this->db->fetch_all(), 'community');
        
        $communities = array('' => $this->LNG->Personal_blog);
        if ($communities_ids) {
            $communities += arrays::map($Communities->select(array('id', 'title', 'name'), SQL::quote('id in (?)', $communities_ids)), 'id', 'title');
        }
        
        return $communities;
    }

    public static function format_range($from, $to, $sep = '-', $format) {
        $Conf = Framework::get('Conf');
        $range = '';
        if (!$format) {
            $format = array(
                'd' => 'j',
                'm' => 'M'
            );
        }
        if (!isset($format['dm'])) {
            $format['dm'] = $Conf->ENV->language == 'ru' ? $format['d'] . ' ' . $format['m'] : $format['m'] . ' ' . $format['d'];
        }
        if (date('m', $from) == date('m', $to)) {
            if (date('d', $from) == date('d', $to)) {
                $range = date($format['dm'], $from);
            } else {
                if ($Conf->ENV->language == 'ru') {
                    $range = date($format['d'], $from) . $sep . date($format['d'], $to) . ' ' . date($format['m'], $from);    
                } else {
                    $range = date($format['dm'], $from) . $sep . date($format['d'], $to);
                }
            }
        } else {
            $range = date($format['dm'], $from) . $sep . date($format['dm'], $to);
        }
        
        return $range;
    }
}