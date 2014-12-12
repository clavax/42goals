<?php
import('base.controller.BasePage');

class GoalsPage extends BasePage
{
    public function __construct() {
        parent::__construct();
        $this->addRule(array(
            'export' => '(export): null',
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
        ob_start();
        $filename = "export.csv";
        $headerArray = array('Tab', 'Goal', 'Value', 'Comment', 'Date');
        echo implode(",", $headerArray) . "\n";
        $goals = $this->Conf->parse($this->PTH->config . 'demo.ini');
        $data = array();
        $today = time();
        $day = 3600 * 24; // seconds in a day
        if (isset($goals[$this->ENV->language]['tab'])) {
            foreach ($goals[$this->ENV->language]['tab'] as $id => $tab) {
                $tabs[] = array('id' => $id + 1, 'title' => $tab, 'position' => $id);
            }
        }
        $allTabs = $goals[$this->ENV->language]['tab'];
        foreach ($goals as $id => &$goal) {
            $goal['id'] = $id;
            $goal['title'] = $goal['title_' . $this->ENV->language];
            $goal['text'] = $goal['text_' . $this->ENV->language];

            if (is_numeric($goal['tab']))
                $tabName = $allTabs[$goal['tab'] - 1];
            else
                $tabName = 'all';
            $goal['user'] = $this->ENV->get('UID', 0);
            foreach ($goal['data'] as $i => $value) {

//                $data[$id][date('Y-m-d', $today - $i * $day)] = array('tab'=>$tabName,'value' => $value, 'value' => $goal['title'], 'text' => '');
                $dataArr[] = array('tab' => $tabName, 'goal' => $goal['title'], 'value' => $value, 'text' => '', 'Date' => date('Y-m-d', $today - $i * $day));
            }
        }
        foreach ($dataArr as $valueArr) {
            echo implode(",", $valueArr) . "\n";
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
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('goals');
        
        import('model.Icons');
        import('model.Goals');
        import('model.Categories');
        import('model.Templates');
        
        $Goals = new GoalsModel;
        
        $goals = $this->Conf->parse($this->PTH->config . 'demo.ini');
        $data = array();
        $today = time();
        $day = 3600 * 24; // seconds in a day
        
        $tabs = array();
        if (isset($goals[$this->ENV->language]['tab'])) {
            foreach ($goals[$this->ENV->language]['tab'] as $id => $tab) {
                $tabs[] = array('id' => $id + 1, 'title' => $tab, 'position' => $id);
            }
        }
        foreach ($this->CNF->languages as $name => $lang) {
            unset($goals[$name]);
        }
    
        $icons = array();
        $icon_id = 0;
        
        foreach ($goals as $id => &$goal) {
            $goal['id'] = $id;
            $goal['title'] = $goal['title_' . $this->ENV->language];
            $goal['text'] = $goal['text_' . $this->ENV->language];
            $goal['user'] = $this->ENV->get('UID', 0);
            foreach ($goal['data'] as $i => $value) {
                $data[$id][date('Y-m-d', $today - $i * $day)] = array('value' => $value, 'text' => '');
            }
            foreach (array('icon_item', 'icon_true', 'icon_false') as $icon) {
                if (isset($goal[$icon]) && $goal[$icon]) {
                    $icon_id ++;
                    $icons[$icon_id] = $this->URL->img . 'demo/' . $goal[$icon] . '.png';
                    $goal[$icon] = $icon_id;
                }
            }
            unset($goal['data']);
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
        $icons += arrays::map($Icons->select(array('id', 'src'), SQL::quote('user = ? or user in (?)', $this->ENV->UID, $admin_ids), array('position', 'id')), 'id', 'src');
                
        $this->T->today = date('Y-m-d');
        $this->T->goals = array_values($goals);
        $this->T->tabs = $tabs;
        $this->T->icons = $icons;
        $this->T->categories = array_values($categories);
        $this->T->templates = array_values($templates);
        $this->T->data = $data;
        
        $this->T->include('this.demo', 'content');
        
        $this->T->page_title = $this->LNG->Demo;
        $this->T->page_id = 'demo-page';
        $this->T->page_gray = true;
        return $this->T->return('templates.app');
    }
}
?>
