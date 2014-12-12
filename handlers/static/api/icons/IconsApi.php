<?php
import('base.controller.BaseApi');

class IconsApi extends BaseApi
{
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        $data = array(
            'user' => $this->ENV->UID,
            'src' => '',
            'position' => 0
        );
        
        // insert into database
        import('model.Icons');
        $Icons = new IconsModel;
        if (($id = $Icons->add($data)) === false) {
            return $this->respondOk(array('error' => $Icons->errors));
        }
        
        // get data
        $tmp_name = $request['tmp_name'];
        $real_name = array_get($_SESSION['upload'], $tmp_name, 'dummy.png');
        
		// Changes done by Kanhaiya 
		// date 29 Jan 2013
		// we need to store files in central location 
		/*
		$new_name = $this->ENV->user->login . '/' . $id . '.' . file::get_ext($real_name);
        
		if (!file::rename($this->PTH->upload . $tmp_name, $this->PTH->icons . $new_name)) {
            return $this->respondOk(array('error' => 'cannot move uploaded file'));
        }

        $data = array('src' => $this->URL->icons . $new_name);
		*/
		
		$new_name =  strtotime('now').'_'.$id . '.' . file::get_ext($real_name);
        
		if (!file::rename($this->PTH->upload . $tmp_name, $this->PTH->public .'img/users/'. $new_name)) {
            return $this->respondOk(array('error' => 'cannot move uploaded file'));
        }

        $data = array('src' => $this->URL->public .'img/users/'. $new_name);
		
        if (($Icons->edit($id, $data)) === false) {
            return $this->respondOk(array('error' => $Icons->errors));
        }
        
        $item = $Icons->view($id, array('id', 'src', 'user'));
        return $this->respondOk(array('item' => $item));
    }
    
    public function handleDeleteDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        // insert into database
        import('model.Icons');
        $Icons = new IconsModel;
        
        $icons = $request['id'];
        $items = array();
        foreach ($icons as $id) {
            if ($Icons->view($id, 'user') != $this->ENV->UID) {
                continue;
            }
            
            $src = $Icons->view($id, 'src');
            if ($Icons->delete($id)) {
				if(is_file($this->PTH->icons . $src))
				{
                file::delete($this->PTH->icons . $src);
				}
				else
				{ // modified by kanhaiya if file not find there delete from here s
					file::delete($this->PTH->public .'img/users/'.$src);
				}
			   $items[] = $id;
            }
        }
        
        return $this->respondOk(array('item' => $items));
    }
    
    public function handlePutDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not logged in'));
        }
        
        // insert into database
        import('model.Icons');
        $Icons = new IconsModel;
        
        $icons = $request['id'];
        $items = array();
        $pos = 0;
        foreach ($icons as $id) {
            if ($Icons->view($id, 'user') != $this->ENV->UID) {
                continue;
            }
            $pos ++;
            if ($Icons->edit($id, array('position' => $pos))) {
                $items[] = $id;
            }
        }
        
        return $this->respondOk(array('item' => $items));
    }
}
?>