<?php
class notification
{
    public static function send($user_id, $message_id, $data = array(), $url = null)
    {
        $Conf = Framework::get('Conf');
        $T    = Framework::get('T');
        
        // send notification
        $Users = new UsersModel;
        $user = $Users->view($user_id, array('login', 'name', 'email', 'language', 'receive_emails', 'password'));
        
        $email = trim($user['email']);
        
        $language = trim($user['language']);
        if (!strlen($language) || !file_exists(path("lib.notification.{$language}.{$message_id}.text", '.tmpl'))) {
            $language = $Conf->ENV->get('language', 'en');
        }
        $T->language = $language;
        $host = 'http://' . $Conf->CNF->languages[$language]->domain;
        $T->host = $host;
        
        $T->user = $user;
        $T->data = $data;
        $T->url  = $url;
        
        // send an email
        if (strlen($email) && $user['receive_emails']) {
            $T->to = $email;
            $time = time();
            $hash = md5("unsubscribe-$time-{$user['password']}");
            
            $T->unsubscribe = "{$host}{$Conf->URL->home}settings/unsubscribe/?email={$email}&h={$hash}&t={$time}";
            
            $T->include("lib.notification.{$language}.{$message_id}.text", 'content');
            $text_message = $T->return('templates.email-txt');
            
            $T->include("lib.notification.{$language}.{$message_id}.html", 'content');
            $html_message = $T->return('templates.email');
        
            $subject = $T->return("lib.notification.{$language}.{$message_id}.sbj");
            
            import('lib.email');
            import('3dparty.Rmail.Rmail');
            
            $mail = new Rmail();
            $mail->setFrom("{$Conf->CNF->site->admin} <{$Conf->CNF->site->email}>");
            $mail->setTextCharset('utf-8');
            $mail->setText($text_message);
            $mail->setHTMLCharset('utf-8');
            $mail->setHTML($html_message);
            $mail->setSubject($subject);
            $mail->send(array($email));
        }
        
        // add a notification
        $Notifications = new DataTable('notifications');
        $Notifications->insert(array(
            'user' => $user_id,
            'text' => $T->return("lib.notification.{$language}.{$message_id}.msg"),
            'time' => date('Y-m-d H:i:s'),
            'is_read' => 'no',
            'url'  => $T->interpret_source($url)
        ));
    }
}
?>