<?php
import('base.controller.BaseCommand');

class DistributeBadgesCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        $type = array_get($request, 2);
        $distribute = array_get($request, 3) == 'distribute';
        
        $count = 0;
        switch ($type) {
        case 'newbie':
            $count = $this->handleNewbie($distribute);
            break;
            
        case 'old-newbie':
            $count = $this->handleOldNewbie($distribute);
            break;

        case 'diligent':
            $count = $this->handleDiligent($distribute);
            break;

        case 'progressive':
            $count = $this->handleProgressive($distribute);
            break;

        case 'premium':
            $count = $this->handlePremium($distribute);
            break;
            
        default:
            return "Specify type (newbie|diligent|premium)\n";
        }
        
        return "$count badges. Done in {!gen}s.\n";
    }
    
    public function handleNewbie($distribute)
    {
        $type = 'newbie';
        
        $Data = new DataTable('data');
        $Badges = new DataTable('badges');
        
        import('lib.notification.notification');
        
        $day_ago = date('Y-m-d H:i:s', strtotime('-1day'));
        $day_hour_ago = date('Y-m-d H:i:s', strtotime('-1day -1hour'));
        $users = $Data->select(array('user' => 'distinct user'), SQL::quote('modified between ? and ?', $day_ago, $day_hour_ago));
        $count = 0;
        foreach ($users as $user) {
            if (!$user) {
                continue;
            }
            if ($Badges->count('*', SQL::quote('user = ? and type = ?', $user, $type))) {
                continue;
            }
            $count ++;
            if ($distribute) {
                $Badges->insert(array(
                    'user' => $user, 
                    'type' => $type,
                    'date' => date('Y-m-d H:i:s')
                ));
                notification::send($user, 'badge-' . $type, null, '{@home}users/{$user["login"]}/');
            }
        }
        
        return $count;
    }
    
    public function handleOldNewbie($distribute)
    {
        $type = 'newbie';
        
        $Data = new DataTable('data');
        $Badges = new DataTable('badges');
        
        import('lib.notification.notification');
        
        $month_ago = date('Y-m-d', strtotime('-1month'));
        $today = date('Y-m-d H:i:s');
        $users = $Data->select(array('user' => 'distinct user'), SQL::quote('modified between ? and ?', $month_ago, $today));
        $count = 0;
        foreach ($users as $user) {
            if (!$user) {
                continue;
            }
            if ($Badges->count('*', SQL::quote('user = ? and type = ?', $user, $type))) {
                continue;
            }
            $count ++;
            if ($distribute) {
                $Badges->insert(array(
                    'user' => $user, 
                    'type' => $type,
                    'date' => date('Y-m-d H:i:s')
                ));
                notification::send($user, 'badge-' . $type, null, '{@home}users/{$user["login"]}/');
            }
        }
        
        return $count;
    }
    
    public function handleDiligent($distribute)
    {
        $type = 'diligent';
        
        $Data = new DataTable('data');
        $Badges = new DataTable('badges');
        
        import('lib.notification.notification');
        
        $week_ago = date('Y-m-d H:i:s', strtotime('-1week'));
        $now = date('Y-m-d H:i:s');
        $users = $Data->select(array('user', 'count' => 'count(distinct created)'), SQL::quote('created between ? and ?', $week_ago, $now), null, null, 'user');
        $count = 0;
        foreach ($users as $user) {
            if (!$user['user']) {
                continue;
            }
            if ($user['count'] < 5) {
                continue;
            }
            if ($Badges->count('*', SQL::quote('user = ? and type = ?', $user['user'], $type))) {
                continue;
            }
            $count ++;
            if ($distribute) {
                $Badges->insert(array(
                    'user' => $user['user'], 
                    'type' => $type,
                    'date' => date('Y-m-d H:i:s')
                ));
                notification::send($user['user'], 'badge-' . $type, null, '{@home}users/{$user["login"]}/');
            }
        }
        return $count;
    }
    
    public function handleProgressive($distribute)
    {
        $type = 'progressive';
        
        $Data = new DataTable('data');
        $Badges = new DataTable('badges');
        
        import('lib.notification.notification');
        
        $month_ago = date('Y-m-d H:i:s', strtotime('-1 month'));
        $now = date('Y-m-d H:i:s');
        $users = $Data->select(array('user', 'count' => 'count(distinct created)'), SQL::quote('created between ? and ?', $month_ago, $now), null, null, 'user');
        $count = 0;
        foreach ($users as $user) {
            if (!$user['user']) {
                continue;
            }
            if ($user['count'] < 20) {
                continue;
            }
            if ($Badges->count('*', SQL::quote('user = ? and type = ?', $user['user'], $type))) {
                continue;
            }
            $count ++;
            if ($distribute) {
                $Badges->insert(array(
                    'user' => $user['user'], 
                    'type' => $type,
                    'date' => date('Y-m-d H:i:s')
                ));
                notification::send($user['user'], 'badge-' . $type, null, '{@home}users/{$user["login"]}/');
            }
        }
        return $count;
    }

    public function handlePremium($distribute)
    {
        $type = 'premium';
        
        $Users = new DataTable('users');
        $Badges = new DataTable('badges');
        
        import('lib.notification.notification');
        
        $now = date('Y-m-d H:i:s');
        $users = $Users->select('id', SQL::quote('paid_till and paid_till > ?', $now));
        $count = 0;
        foreach ($users as $user) {
            if (!$user) {
                continue;
            }
            if ($Badges->count('*', SQL::quote('user = ? and type = ?', $user, $type))) {
                continue;
            }
            $count ++;
            if ($distribute) {
                $Badges->insert(array(
                    'user' => $user, 
                    'type' => $type,
                    'date' => date('Y-m-d H:i:s')
                ));
                notification::send($user, 'badge-' . $type, null, '{@home}users/{$user["login"]}/');
            }
        }
        return $count;
    }
}
?>