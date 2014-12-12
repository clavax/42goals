<?php
import('base.controller.BaseTag');

class MeMenuTag extends BaseTag
{
    public function handle(array $params = array())
    {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('me');
        $this->Conf->loadLanguage('communities');
        if ($user = array_get($params, 'user')) {
            $this->T->menu = array(
                array(
                    'title' => $this->LNG->Profile,
                    'icon'  => 'profile',
                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/'
                ),
                array(
                    'title' => $this->LNG->Goals,
                    'icon'  => 'goals',
                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/goals/'
                ),
                array(
                    'title' => $this->LNG->Friends,
                    'icon'  => 'friends',
                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/friends/'
                ),
                array(
                    'title' => $this->LNG->Posts,
                    'icon'  => 'posts',
                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/posts/'
                ),
//                array(
//                    'title' => $this->LNG->Compare,
//                    'icon'  => 'compare',
//                    'hide'  => !Access::loggedIn() || $user['id'] != $this->ENV->UID,
//                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/compare/'
//                ),
//                array(
//                    'title' => 'Feed',
//                    'icon'  => 'feed',
//                    'hide'  => !Access::loggedIn() || $user['id'] != $this->ENV->UID,
//                    'url'   => $this->URL->home . 'users/' . $user['login'] . '/feed/'
//                ),
            );
        } else if (array_get($params, 'community')) {
            $this->T->menu = array(
                array(
                    'title' => $this->LNG->Overview,
                    'icon'  => 'profile',
                    'url'   => $this->URL->home . 'communities/' . $params['community']['name'] . '/'
                ),
                array(
                    'title' => 'Members',
                    'icon'  => 'friends',
                    'url'   => $this->URL->home . 'communities/' . $params['community']['name'] . '/members/'
                ),
                array(
                    'title' => $this->LNG->Posts,
                    'icon'  => 'posts',
                    'url'   => $this->URL->home . 'communities/' . $params['community']['name'] . '/posts/'
                ),
            );
        } else {
            return 'No user';
        }
        return $this->T->return('tag.me-menu');
    }
}