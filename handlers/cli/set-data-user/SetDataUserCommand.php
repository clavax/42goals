<?php
import('base.controller.BaseCommand');

class SetDataUserCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        $Goals = new DataTable('goals');
        $Data = new DataTable('data');
        $goals = $Goals->select(array('id', 'user'));
        foreach ($goals as $goal) {
            $Data->update_where(array('user' => $goal['user']), SQL::quote('goal = ?', $goal['id']));
        }
    }
}
?>