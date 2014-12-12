<?php
import('base.controller.BaseApi');
import('model.Communities');

class CommunitiesApi extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->addRule(array(
            'item' => '(\d+): id',
            'join' => '(\d+)/(join): id/null',
            'leave' => '(\d+)/(leave): id/null',
            'admin' => '(\d+)/(admin): id/null',
        ));
    }
    
    public function handlePostDefault(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        $Communities = new CommunitiesModel;
        
        $data = array_get($request, 'data');
        $this->handleImages($data);
        unset($data['id']);
        $data['user'] = $this->ENV->UID;
        if (($id = $Communities->add($data)) === false) {
            return $this->respondOk(array('error' => $Communities->errors));
        }
        $data['id'] = $id;
        return $this->respondOk(array('item' => $data));
    }

    public function handlePutItem(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Communities = new CommunitiesModel;
        
        $data = array_get($request, 'data');
        $this->handleImages($data);
        $old = $Communities->view($this->ENV->id, array('id', 'picture', 'thumbnail'));
        
        if ($Communities->edit($this->ENV->id, $data) === false) {
            return $this->respondOk(array('error' => $Communities->errors));
        }
        
        // delete old pictures
        if (isset($data['picture']) && !empty($old['picture'])) {
            if (file_exists($this->PTH->communitypics . $old['picture'])) {
                file::delete($this->PTH->communitypics . $old['picture']);
            }
        }
        if (isset($data['thumbnail']) && !empty($old['thumbnail'])) {
            if (file_exists($this->PTH->communitypics . $old['thumbnail'])) {
                file::delete($this->PTH->communitypics . $old['thumbnail']);
            }
        }
        
        $item = $Communities->view($this->ENV->id);
        return $this->respondOk(array('item' => $item));
    }
    
    protected function handleImages(&$data)
    {
        $Communities = new CommunitiesModel;
        import('lib.image');
        if (isset($data['picture_tmpname']) && !empty($data['picture_tmpname'])) {
            
            $tmp_name = $data['picture_tmpname'];
            $real_name = $data['picture_name'];
            
            // check image
            $ext = str::tolower(file::get_ext($real_name));
            if (!in_array($ext, array('gif', 'jpg', 'jpeg', 'png'))) {
                return $this->respondOk(array('error' => array('picture_name' => 'Wrong file type')));
            }
            
            $info = image::info($this->PTH->upload . $tmp_name);
            if ($info[0] > 3000 || $info[1] > 3000) {
                return $this->respondOk(array('error' => array('picture_name' => 'Image is too large')));
            }
            
            $new_name = array_get($data, 'name', $this->ENV->id) . '_' . uniqid() . '.' . $ext;
            if (!file::rename($this->PTH->upload . $tmp_name, $this->PTH->communitypics . $new_name)) {
                return $this->respondOk(array('error' => array('picture_name' => 'Cannot move uploaded file')));
            }
            
            $image = image::from_file($this->PTH->communitypics . $new_name);
            $resized = image::resize($image, 180, null);
            image::save($resized, $this->PTH->communitypics . $new_name, $info[2]);
            
            $data['picture'] = $new_name;
        }
        if (isset($data['thumbnail_tmpname']) && !empty($data['thumbnail_tmpname'])) {
            
            $tmp_name = $data['thumbnail_tmpname'];
            $real_name = $data['thumbnail_name'];
            
            // check image
            $ext = str::tolower(file::get_ext($real_name));
            if (!in_array($ext, array('gif', 'jpg', 'jpeg', 'png'))) {
                return $this->respondOk(array('error' => array('thumbnail_name' => 'Wrong file type')));
            }
            
            $info = image::info($this->PTH->upload . $tmp_name);
            if ($info[0] > 3000 || $info[1] > 3000) {
                return $this->respondOk(array('error' => array('thumbnail_name' => 'Image is too large')));
            }
            
            $new_name = array_get($data, 'name', $this->ENV->id) . '_small_' . uniqid() . '.' . $ext;
            if (!file::rename($this->PTH->upload . $tmp_name, $this->PTH->communitypics . $new_name)) {
                return $this->respondOk(array('error' => array('thumbnail_name' => 'Cannot move uploaded file')));
            }
            
            $image = image::from_file($this->PTH->communitypics . $new_name);
            $resized = image::resize($image, 50, null);
            image::save($resized, $this->PTH->communitypics . $new_name, $info[2]);
            
            $data['thumbnail'] = $new_name;
        }        
    }

    public function handleDeleteItem(array $request = array())
    {
        if (!Access::loggedIn() || !Access::isAdmin()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Communities = new CommunitiesModel;
        
        if ($Communities->delete($this->ENV->id) === false) {
            return $this->respondOk(array('error' => $Communities->errors));
        }
        
        return $this->respondOk(array('item' => $this->ENV->id));
    }
    
    public function handlePostJoin()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Membership = new DataTable('community_membership');
        if (!$Membership->exists_where(SQL::quote('user = ? and community = ?', $this->ENV->UID, $this->ENV->id))) {
            $Membership->insert(array('user' => $this->ENV->UID, 'community' => $this->ENV->id, 'role' => 'member', 'time' => date('Y-m-d H:i:s')));
        }
        
        $Feed = new DataTable('feed');
        $Feed->insert(array(
            'resource' => 'join-' . $this->ENV->UID . '-' . $this->ENV->id,
            'user' => $this->ENV->UID,
            'community' => $this->ENV->id,
            'type' => 'joined',
            'data' => '',
            'time' => date('Y-m-d H:i:s')
        ));
        
        return $this->respondOk(array('member' => 1));
    }
    
    public function handlePostLeave()
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Membership = new DataTable('community_membership');
        $Membership->delete_where(SQL::quote('user = ? and community = ?', $this->ENV->UID, $this->ENV->id));
        
        return $this->respondOk(array('member' => 0));
    }
    
    public function handlePostAdmin(array $request = array())
    {
        return $this->setAdmin($request, true);
    }
    
    public function handleDeleteAdmin(array $request = array())
    {
        return $this->setAdmin($request, false);
    }
    
    public function setAdmin(array $request = array(), $add = true)
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        $Communities = new CommunitiesModel;
        if ($Communities->view($this->ENV->id, 'user') != $this->ENV->UID) {
            return $this->respondOk(array('error' => 'not owner'));
        }
        
        $user = array_get($request, 'user');
        if (!$user) {
            return $this->respondOk(array('error' => array('user' => 'empty')));
        }
        
        $Membership = new DataTable('community_membership');
        $Membership->update_where(array('role' => $add ? 'admin' : 'member'), SQL::quote('user = ? and community = ?', $user, $this->ENV->id));
        
        return $this->respondOk(array('ok' => true));
    }
}
?>