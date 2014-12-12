<?php
import('base.controller.BasePage');

class GoalsPage extends BasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'default' => '((?:\d+)?): tab',
	     'export' => '(export): null'				

        ));
    }
public function cleanData($str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\n/", " ", $str);
    
    $str = preg_replace("/\r/", " ", $str);
    $str = preg_replace("/\r\n/", " ", $str);
    //$str = preg_replace("/,/", "", $str); 

    $str = html_entity_decode($str, ENT_QUOTES);
    //$str  = str_replace('&amp;','&',$str);
    $str = str_replace('&AMP;', '&', $str);
    $str = str_replace('<br>', ' ', $str);
    $str = addslashes($str);
    return $str;
//    $str = str_replace('"', chr(148), $str);
} 

  public function handleExport(array $request = array()) {
      
	  // Added by Kanhaiya dated sep-30 2013
	  if (!Access::loggedIn()) {
            return $this->showLogin();
        }
	  
	  import('model.Goals');
      import('model.Tabs');
       ob_start();
            $filename="export.csv";
            $headerArray = array('Tab', 'Goal', 'Value', 'Comment', 'Date');
            
            $Data = new DataTable('data');
            $Goals = new GoalsModel;
            $Tabs = new TabsModel;
			// condition added by Kanhaiya dated sep-30-2013 to make sure not to skip this point if user is null any way
			if(isset($this->ENV->UID) && $this->ENV->UID!='')
			{
				$data = $Data->select(array('"tab"','goal', 'value', 'text', 'date'), SQL::quote('user =?',$this->ENV->UID));
			   
				if(count($data)>0){
					$showTabs=false;
					$countTabs = $Goals->select(array('title'), SQL::quote('user =? and tab > 0 ',  $this->ENV->UID));
				   
					if(count($countTabs)>0)
					{
						$showTabs=true;
					}   
					else{
					 unset($headerArray[0]);
					}
		 
					echo implode(",", $headerArray) . "\n";
					
					foreach ($data as $row) {
						 $row['text']=$this->cleanData($row['text']);      
						$goals = $Goals->select(array('title', 'tab'), SQL::quote('id = ?',  $row['goal']));
						$goals = array_pop($goals);
						
						if(!$showTabs){
							unset($row['tab']);
						}else{
							if($goals['tab']>0)
							{
								$tabs = $Tabs->select(array('title'), SQL::quote('id = ?',  $goals['tab']));
								$tabs = array_pop($tabs);
							}  
							else
								$tabs ="";
							$row['tab']=$tabs;                        
						}//else
						

						$row['goal']=$goals['title'];

										  echo implode(",", $row) . "\n";

					}     
					
					
				}else{
					 echo implode(",", $headerArray) . "\n";
					
				}
			}else{
				echo implode(",", $headerArray) . "\n";
			
			}
header("Expires: 0");
                session_cache_limiter("must-revalidate");
                header('Content-type: application/octet-stream');
                header('Content-Type: application/csv');
                header('Expires: ' . gmdate('D, d M Y H:i:s') . 'GMT');
                header('Content-Disposition: attachment; filename=' . $filename);

                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                } else {
                    header('Pragma: no-cache');
                }  

  }     
    public function handle()
    {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }
        
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Goals');
        import('model.Tabs');
        import('model.Icons');
        import('model.Plan');
        import('model.Categories');
        import('model.Templates');
        
        $Users = new UsersModel;
        $Goals = new GoalsModel;
        $fields = array(
            'id', 
            'title', 
            'text', 
            'type', 
            'icon_item', 
            'icon_zero', 
            'icon_true', 
            'icon_false', 
            'unit', 
            'prepend', 
            'aggregate', 
            'position', 
            'tab',
            'user'
        );
        $predicate = SQL::quote('user = ? and archived is NULL', $this->ENV->UID);
        $goals = $Goals->select($fields, $predicate, array('position', 'id'));
        
        // get compared goals
        $ComparisonsItem = new DataTable('comparisons_item');
        $comparisons = $ComparisonsItem->select(array('comparison', 'user', 'goal'), SQL::quote('user = ? and status = ?', $this->ENV->UID, 'accepted'));
        if ($comparisons) {
            $all_comparisons = $ComparisonsItem->select(array('comparison', 'goal', 'user'), SQL::quote('comparison in (?) and user != ? and status = ?', arrays::list_fields($comparisons, 'comparison'), $this->ENV->UID, 'accepted'));
            $comparisons_goals = array();
            foreach ($comparisons as $comparison) {
                if ($comparison['user'] == $this->ENV->UID) {
                    $comparisons_goals[$comparison['comparison']] = $comparison['goal'];
                }
            }
            $compared_goals = array();
            $compared_goals_ids = array();
            $compared_users_ids = array();
            foreach ($all_comparisons as $comparison) {
                if ($comparison['user'] != $this->ENV->UID) {
                    $user_goal_id = $comparisons_goals[$comparison['comparison']];
                    $compared_goals[$user_goal_id][] = $comparison['goal'];
                    $compared_goals_ids[] = $comparison['goal'];
                    $compared_users_ids[] = $comparison['user'];
                }
            }
            $compared_goals_data = $Goals->select($fields, SQL::quote('id in (?)', $compared_goals_ids), array('position', 'id'));
            
            $compared_users_data = $Users->select(array('id', 'name', 'login', 'thumbnail'), SQL::quote('id in (?)', $compared_users_ids));
            foreach ($compared_goals_data as &$compared_goal) {
                $compared_goal['user_data'] = array_get($compared_users_data, $compared_goal['user']);
            }
            unset($compared_goal);
        } else {
            $compared_goals_data = array();
        }
        
        $all_goals = array();
        $icon_ids = array(0);
        foreach ($goals as $goal) {
            $all_goals[] = $goal;
            if (isset($compared_goals[$goal['id']])) {
                foreach ($compared_goals[$goal['id']] as $goal_id) {
                    $compared_goal = array_get($compared_goals_data, $goal_id);
                    if ($compared_goal) {
                        $compared_goal['tab'] = $goal['tab'];
                        $compared_goal['type'] = $goal['type'];
                        $compared_goal['icon_true'] = $goal['icon_true'];
                        $compared_goal['icon_false'] = $goal['icon_false'];
                        $compared_goal['icon_item'] = $goal['icon_item'];
                        $compared_goal['position'] = $goal['position'];
                        $compared_goal['unit'] = $goal['unit'];
                        $compared_goal['prepend'] = $goal['prepend'];
                        $compared_goal['aggregate'] = $goal['aggregate'];
                        $all_goals[] = $compared_goal;
                        
                        if ($goal['icon_true']) {
                            $icon_ids[] = $goal['icon_true'];
                        }
                        if ($goal['icon_false']) {
                            $icon_ids[] = $goal['icon_false'];
                        }
                        if ($goal['icon_item']) {
                            $icon_ids[] = $goal['icon_item'];
                        }
                    }
                }
            }
        }
        
        $goals = $all_goals;

        // get data and plan
        $data_by_goal_date = array();
        $plan_by_goal = array();
        if ($goals) {
            $Data = new DataTable('data');
            $data = $Data->select(array('goal', 'date', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($data as $row) {
                $data_by_goal_date[$row['goal']][$row['date']] = array('value' => $row['value'], 'text' => $row['text']);
            }
            
            $DataStart = new DataTable('data_start');
            $start = $DataStart->select(array('goal', 'date', 'start'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'date'));
            foreach ($start as $row) {
                $data_by_goal_date[$row['goal']][$row['date']]['start'] = $row['start'];
            }
            
            $Plan = new PlanModel;
            $plan = $Plan->select(array('id', 'goal', 'startdate', 'enddate', 'value', 'text'), SQL::quote('goal in (?)', arrays::list_fields($goals, 'id')), array('goal', 'startdate'));
            foreach ($plan as $row) {
                $plan_by_goal[$row['goal']][$row['id']] = $row;
            }
        }
        
        $admin_id = $this->CNF->languages[$this->ENV->language]->admin;
        $admin_ids = array();
        foreach ($this->CNF->languages as $name => $lang) {
            $admin_ids[] = $lang['admin'];
        }
        
        $Categories = new CategoriesModel;
        $categories = $Categories->select(array('id', 'title' => 'title_' . $this->ENV->language), null, array('position', 'id'));
        
        $Templates = new TemplatesModel;
        $templates = $Templates->select(array('id', 'title', 'preview', 'type', 'icon_item', 'icon_zero', 'icon_true', 'icon_false', 'unit', 'prepend', 'aggregate', 'category'), SQL::quote('user = ? and approved = ?', $admin_id, 'yes'), array('position', 'id'));
        
        $Icons = new IconsModel;
        $icons = arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user in (?) or id in (?)', $this->ENV->UID, $admin_ids, $icon_ids), array('position', 'id')), 'id', 'src');
        
        $Tabs = new TabsModel;
        $tabs = $Tabs->select(array('id', 'title', 'position'), SQL::quote('user = ?', $this->ENV->UID), array('position', 'id'));
        
        $this->T->today = date('Y-m-d');
        $this->T->goals = array_values($goals);
        $this->T->tabs = array_values($tabs);
        $this->T->icons = $icons;
        $this->T->categories = array_values($categories);
        $this->T->templates = array_values($templates);
        $this->T->data = $data_by_goal_date;
        $this->T->plan = $plan_by_goal;
        
        $this->T->include('this.page', 'content');
        
        $this->T->page_title = $this->LNG->Goals;
        $this->T->page_id = 'goals-page';
        return $this->T->return('templates.app');
    }
}
?>
