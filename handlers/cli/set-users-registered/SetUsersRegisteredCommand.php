<?php
import('base.controller.BaseCommand');

class SetUsersRegisteredCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        $Data = new DataTable('data');
        $Users = new PrimaryTable('users', 'id');
        
        $data = $Data->select(array('user', 'min' => 'min(created)'), null, 'user', null, 'user');
        foreach ($data as $row) {
            if ($row['user']) {
                $Users->update($row['user'], array('registered' => $row['min']));
            }
        }
    }
}
?>