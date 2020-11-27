<?php
namespace Admin\Controller;

use Think\Controller;
class PublicController extends Controller
{
	public $prefix;
    protected function _initialize()
    {
		$adminid = session('admin_auth.adminid');
		$username = session('admin_auth.username');
		$email = session('admin_auth.email');
		$logintime = session('admin_auth.logintime');
		$authority = session('admin_auth.authority');
        if (empty($adminid) || empty($username) || empty($email) || empty($authority)) {
            $this->redirect('/Login/index');
        }
        if (NOW_TIME - $logintime > 2 * 60 * 60) {
            session('admin_auth', null);
            $this->redirect('/Login/index');
        }
        $this->config = M('Site')->where(array('siteid' => 1))->find();
        $this->assign('config', $this->config);
		$this->adminauth = M('auth')->where(array('id' => $authority))->find();
        $this->assign('adminauth', $this->adminauth);
		$this->prefix = C('DB_PREFIX');
    }
    public function cache()
    {
        $path_cache = RUNTIME_PATH . 'Cache/';
        $path_logs = RUNTIME_PATH . 'Logs/';
        delDirAndFile($path_cache);
        delDirAndFile($path_logs);
        $this->success('清除缓存成功', U('index/summarize'));
    }
    public function logout()
    {
        session('admin_auth', null);
        $this->success('退出成功', U('Login/index'));
    }
    protected function _tree($arr, $pid = 0, $level = 0)
    {
        static $tree = array();
        foreach ($arr as $v) {
            if ($v['pid'] == $pid) {
                //$v['level'] = str_repeat('├', $level);
                $v['level'] = $level;
                $tree[] = $v;
                $this->_tree($arr, $v['catid'], $level + 1);
            }
        }
        return $tree;
    }
    protected function _treee($arr, $id = 0, $name = 0)
    {
        static $treee = array();
        foreach ($arr as $v) {
            if ($v['id'] == $id) {
                //$v['level'] = str_repeat('├', $level);
                $v['name'] = $name;
                $treee[] = $v;
                $this->_treee($arr, $v['id'], $name + 1);
            }
        }
        return $treee;
    }
    protected function _treeee($arr, $bmid = 0, $goodsname = 0)
    {
        static $treeee = array();
        foreach ($arr as $v) {
            if ($v['bmid'] == $bmid) {
                //$v['level'] = str_repeat('├', $level);
                $v['goodsname'] = $goodsname;
                $treeee[] = $v;
                $this->_treee($arr, $v['bmid'], $goodsname + 1);
            }
        }
        return $treee;
    }
    protected function _rules($arr, $id = 0, $type = 0)
    {
        static $rules = array();
        foreach ($arr as $v) {
            if ($v['id'] == $id){
                $v['type'] = $type;
                $rules[] = $v;
                $this->_rules($arr, $v['id'], $type + 1);
            }
        }
        return $rules;
    }
    protected function _article($arr, $id = 0, $title = 0)
    {
        static $article = array();
        foreach ($arr as $v) {
            if ($v['id'] == $id){
                $v['title'] = $title;
                $article[] = $v;
                $this->_article($arr, $v['id'], $title + 1);
            }
        }
        return $rules;
    }
    protected function listCate($pid = 0)
    {
        $cate = M('category');
        $list = $cate->order('sort desc')->select();
        return $this->_tree($list, $pid);
    }
    protected function listArti($id = 0)
    {
        $arti = M('article');
        $list = $arti->order('title')->select();
        return $this->_article($list, $title);
    }
    protected function listlpa($id = 0)
    {
        $lpam = M('lpa');
        $list = $lpam->order('name')->select();
        return $this->_treee($list, $name);
    }
    protected function listDist($bmid = 0)
    {
        $dist = M('distribute');
        $list = $dist->order('goodsname')->select();
        return $this->_treeee($list, $goodsname);
    }
    protected function listRule($id = 0)
    {
        $rule = M('personal');
        $list = $rule->order('type')->select();
        return $this->_rules($list, $type);
    }
    /*
	public function uploadEditor()
    {
        $upload = new \Think\Upload();
        $upload->maxSize = 209715200;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->savePath = './Editor/';
        $upload->autoSub = true;
        $info = $upload->upload();
        if ($info) {
            foreach ($info as &$v) {
                $file = $v['savepath'] . $v['savename'];
            }
            echo json_encode(array('url' => $file, 'state' => 'SUCCESS'));
        } else {
            echo json_encode(array('state' => $upload->getError()));
        }
    }
	*/
	//上传文件
    public function uploadAttach()
    {
        $site = M('Site');
		$size = $site->where('siteid = 1')->getField('file_size');
		$size = intval($size)*1048576;
		$exts = I('get.type');
		switch ($exts) {
			case 1: //图片
				$exts_arr = array('jpg', 'gif', 'png', 'jpeg');
				$path = './Images/';
				break;
			case 2: //音频
				$exts_arr = array('mp3', 'wam', 'wma', 'aac','mod','cd');
				$path = './Music/';
				break;
			case 3: //附件
				$exts_arr = array('rar', 'zip', 'doc', 'pdf');
				$path = './File/';
				break;
			case 4: //视频
				$exts_arr = array('mp4', 'avi', 'wmv', 'mov','flv','3gp','navi','mkv');
				$path = './Video/';
				break;
			case 5: //excel
			    $size = 2*1048576;
			    $exts_arr = array('xls', 'xlsx');
			    $path = './Excel/';
			    break;
		}
        $upload = new \Think\Upload();
        $upload->maxSize = $size;
        $upload->exts = $exts_arr;
        $upload->savePath = $path;
        $upload->autoSub = true;
        $info = $upload->upload();
        if (!$info) {
            // 上传错误提示错误信息
            $this->ajaxReturn(array('status' => 0));
        } else {
            // 上传成功 获取上传文件信息
            foreach ($info as $v) {
                $file = $v['savepath'] . $v['savename'];
				$name = $v['name'];
				$size = $v['size'];
            }
            $file = str_replace('./', __ROOT__ . '/Uploads/', $file);
            $this->ajaxReturn(array('status' => 1, 'file' => $file, 'name' => $name, 'size' => $size));
        }
    }
}