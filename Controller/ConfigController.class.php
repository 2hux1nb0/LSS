<?php
namespace Admin\Controller;
use Think\Controller;
class ConfigController extends PublicController
{
	public function index(){
        $list = M("Config")->where(array('group' => 1))->field()->select();
        $this -> assign('list',$list);
        $this -> display();
	}
	
	public function save($config){
        if($config && is_array($config)){
            $Config = M('Config');
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $Config->where($map)->setField('value', $value);
            }
        }
        $this->success('修改成功');
    }
}