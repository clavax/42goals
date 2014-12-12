<?php
import('base.model.BaseModel');

class PostsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct('posts');
    }
}
?>