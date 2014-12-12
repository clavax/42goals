<?php
import('base.controller.BaseCommand');

class CreateIconDirsCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        $Users = new DataTable('users');
        $logins = $Users->select('login', SQL::quote('status in (?)', array('twitter', 'facebook')));
        
        foreach ($logins as $login) {
            $dir = $this->PTH->icons . $login;
            if (!file_exists($dir)) {
                file::mkdir($dir, 0775);
                $this->writeln("Creating {$dir}");
            }
        }
        
        $this->writeln('Done');
    }
}
?>