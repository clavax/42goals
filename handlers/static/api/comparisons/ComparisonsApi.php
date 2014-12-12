<?php
import('base.controller.BaseApi');

class ComparisonsApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id',
            'accept' => '(accept)/(\d+): null/id',
            'reject' => '(reject)/(\d+): null/id',
        ));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Comparisons = new DataTable('comparisons');
        $ComparisonsItem = new DataTable('comparisons_item');
        
        $comparison_data = array(
            'user' => $this->ENV->UID,
            'comment' => array_get($request, 'comment')
        );
        $comparison = $Comparisons->insert($comparison_data);

        $goal = array_get($request, 'goal');
        if (!$goal) {
            return $this->respondOk(array('error' => array('goal' => 'empty')));
        }
        
        $owner_data = array(
            'comparison' => $comparison,
            'user'       => $this->ENV->UID,
            'goal'       => $goal,
            'status'     => 'accepted'
        );
        $owner_data['id'] = $ComparisonsItem->insert($owner_data);
        
        $items = array($owner_data);
        
        $users = array_get($request, 'user');
        if (!$users) {
            return $this->respondOk(array('error' => array('user' => 'empty')));
        }
        
        // add invitations
        import('lib.notification.notification');
        
        $Goals = new DataTable('goals');
        $notification_data = array('goal' => $Goals->select('title', SQL::quote('id = ?', $goal), null, 1));
        
        $invite_id = array();
        foreach ($users as $user) {
            $invite_data = array(
                'comparison' => $comparison,
                'user'       => $user,
                'status'     => 'requested'
            );
            $invite_data['id'] = $ComparisonsItem->insert($invite_data);
            $items[] = $invite_data;
            
            notification::send($user, 'comparison-request', $notification_data, '{@home}users/{$user["login"]}/compare/');
        }
        
        return $this->respondOk(array(
            'item' => array(
                'id'      => $comparison,
                'user'    => $this->ENV->UID,
                'comment' => $comparison_data['comment'],
                'items'   => $items
            )
        ));
    }
    
    public function handleDeleteItem()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Comparisons = new DataTable('comparisons');
        if ($this->ENV->UID != $Comparisons->select('user', SQL::quote('id = ?', $this->ENV->id), null, 1)) {
            return $this->respondOk(array('error' => 'no permission'));
        }
        
        $ComparisonsItem = new DataTable('comparisons_item');
        $Comparisons->delete_where(SQL::quote('id = ?', $this->ENV->id));
        $ComparisonsItem->delete_where(SQL::quote('comparison = ?', $this->ENV->id));
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    public function handlePostAccept(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $ComparisonsItem = new DataTable('comparisons_item');
        if ($this->ENV->UID != $ComparisonsItem->select('user', SQL::quote('id = ?', $this->ENV->id), null, 1)) {
            return $this->respondOk(array('error' => 'no permission'));
        }
        
        $comparison_id = $ComparisonsItem->select('comparison', SQL::quote('id = ?', $this->ENV->id), null, 1);
        $Comparisons = new DataTable('comparisons');
        $owner_id = $Comparisons->select('user', SQL::quote('id = ?', $comparison_id), null, 1);
        $goal_id = $ComparisonsItem->select('goal', SQL::quote('user = ? and comparison = ?', $owner_id, $comparison_id), null, 1);
        $Goals = new DataTable('goals');
        $notification_data = array('goal' => $Goals->select('title', SQL::quote('id = ?', $goal_id), null, 1));
                
        if (array_get($request, 'create_new')) {
            
            $fields = array('title', 'type', 'icon_true', 'icon_false', 'icon_item', 'aggregate', 'unit', 'prepend', 'template');
            $goal = $Goals->select($fields, SQL::quote('id = ?', $goal_id), null, 1);
            $goal['user'] = $this->ENV->UID;
            
//            // deal with icons
//            $Icons = new DataTable('icons');
//            $icons = array('icon_true', 'icon_false', 'icon_item');
//            foreach ($icons as $icon) {
//                if ($goal[$icon]) {
//                    $icon_id = $Icons->insert(array('user' => $this->ENV->UID))
//                }
//            }
            
            // insert goal
            $goal_id = $Goals->insert($goal);
        } else {
            $goal_id = array_get($request, 'goal');
        }
        
        $item_data = array('goal' => $goal_id, 'status' => 'accepted');
        if (!$ComparisonsItem->update_where($item_data, SQL::quote('id = ?', $this->ENV->id))) {
            return $this->respondOk(array('error' => 'sql error'));
        };
        
        // send notification
        import('lib.notification.notification');
        notification::send($owner_id, 'comparison-accepted', $notification_data, '{@home}users/{$user["login"]}/compare/');
        
        return $this->respondOk(array('item' => $item_data));
    }
    
    public function handlePostReject(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $ComparisonsItem = new DataTable('comparisons_item');
        if ($this->ENV->UID != $ComparisonsItem->select('user', SQL::quote('id = ?', $this->ENV->id), null, 1)) {
            return $this->respondOk(array('error' => 'no permission'));
        }
        
        $item_data = array('status' => 'rejected');
        if (!$ComparisonsItem->update_where($item_data, SQL::quote('id = ?', $this->ENV->id))) {
            return $this->respondOk(array('error' => 'sql error'));
        };
        
        return $this->respondOk(array('item' => $item_data));
    }
}
?>