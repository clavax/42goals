<?php
import('base.controller.BaseApi');

class LoginApi extends BaseApi 
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(
            array(
                'setcookie' => '(setcookie)/([a-zA-Z0-9-,]{26,40}): null/sid',
                'deletecookie' => '(deletecookie)/([a-zA-Z0-9-,]{26,40}): null/sid',
                'getsession' => '(getsession): null',
                'setsession' => '(setsession)/([a-zA-Z0-9-,]{26,40}): null/sid'
            )
        );
    }
    
    public function handlePostDefault($request)
    {
        $login    = array_get($request, 'login');
        $password = array_get($request, 'password');
        if (Access::login($login, $password)) {
            return $this->respondOk(array('ok' => 'ok', 'remember' => isset($request['remember']), 'sid' => $this->Session->getId()));
        } else {
            return $this->respondOk(array('error' => Access::getError()));
        }
    }
    
    public function handleDeleteDefault($request)
    {
        Access::logout();
        return $this->respondOk(array('ok' => 'ok'));
    }
    
    public function handleSetCookie($request)
    {
        $this->Session->restart($this->ENV->sid);
        Access::setEnv(true);
        if (Access::loggedIn()) {
            Access::rememberUser($this->ENV->UID);
        }
        $location = array_get($request, 'redirect');
        if (empty($location)) {
            $location = $this->URL->home . 'users/' . $this->ENV->user->login . '/';
        }
        header('Location: ' . $location);
        return true;
    }

    public function handleDeleteCookie($request)
    {
        $this->Session->restart($this->ENV->sid);
        if (Access::loggedIn()) {
            Access::forgetUser($this->ENV->UID);
            Access::logout();
        }
        $location = array_get($request, 'redirect', array_get($_SERVER, 'HTTP_REFERER', $this->URL->home));
        header('Location: ' . $location);
        return true;
    }

    public function handleGetSession($request)
    {
        header('Content-Type: text/javascript');
        if (Access::loggedIn()) {
            $host = array_get($request, 'host', $this->URL->host);
            return 'window.location = "' . $host . $this->URL->home . 'api/login/setsession/' . $this->Session->getId() . '/?redirect=" + escape(window.location)';
        } else {
            return 'void(0);';
        }
    }
    
    public function handleSetSession($request)
    {
        $this->Session->restart($this->ENV->sid);
        Access::setEnv(true);
        $location = array_get($request, 'redirect');
        if (empty($location)) {
            $location = $this->URL->home . 'users/' . $this->ENV->user->login . '/';
        }
        header('Location: ' . $location);
        return true;
    }
}
?>
