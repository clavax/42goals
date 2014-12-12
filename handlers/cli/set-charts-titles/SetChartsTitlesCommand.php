<?php
import('base.controller.BaseCommand');

class SetChartTitlesCommand extends BaseCommand
{
    public function handle(array $request = array())
    {
        $Charts = new PrimaryTable('charts', 'id');
        $charts = $Charts->select(array('id', 'goal'), SQL::quote('title in (?, ?)', 'Задать название', 'Set title'));
        
        $Goals = new DataTable('goals');
        foreach ($charts as $chart) {
            $goal = $Goals->select('title', SQL::quote('id = ?', $chart['goal']), null, 1);
            $Charts->update($chart['id'], array('title' => $goal));
        }
    }
}