<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends PublicController
{
    public function index()
    {
		$Admin = M('admin');
		$count = $Admin->count();
        $p = getpage($count,20);
        $list = $Admin->order('adminid desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $p->show());
        $this->display();
    }
    public function add()
    {
		$Auth = M('Auth');
		$list = $Auth->where(array('id' => array('neq', 1),'status' => array('eq', 1)))->order('id asc')->select();
		$this->assign('list', $list);
        $this->display();
    }
    public function insert()
    {
        if (IS_POST) {
            $data = I('post.');
			$authority = I('post.authority', 0, 'intval');
			$username = I('post.username');
			$password = I('post.password');
			if (!$authority) {
				$this->error('请选择角色组');
			}
			if (is_username($username) == false) {
				$this->error('用户名不符合规定');
			}	
			if (is_user($username) == false) {
				$this->error('该用户名已存在');
			}		
			if (is_password($password) == false) {
				$this->error('密码必须在6-18个字符之间');
			}
			if($password != $data['password_n']){
				$this->error('两次密码不一致');
			}
			$email = I('post.email');
            if (is_email($email) == false) {
                $this->error('邮箱格式不正确');
            }
			$data['encrypt'] = create_randomstr();
			$data['password'] = md5(md5(trim($password)).$data['encrypt']);
			$data['inputtime'] = NOW_TIME;
			$id = M('Admin')->add($data);
			if ($id > 0) {
				$this->success('添加成功', U('Admin/index'));
			} else {
				$this->error('添加失败');
			}
        }
    }
    public function delete()
    {
        $adminid = I('get.adminid', 0, 'intval');
        if (!$adminid) {
            $this->error('参数错误');
        }
		if(get_admin_auth($adminid,'authority') == 1){
            $this->error('不允许对超级管理员执行该操作!');
        }
        if (M('admin')->where(array('adminid' => $adminid))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	public function status()
    {
		$adminid = I('get.adminid', 0, 'intval');
		$status = intval(I('status'));
        $status = $status ? 0 : 1;
		if(get_admin_auth($adminid,'authority') == 1){
            $this->error('不允许对超级管理员执行该操作!');
        }
       if (M('Admin')->where(array('adminid'=>$adminid))->save(array('status' => $status))) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
	
    public function edit()
    {
        $adminid = I('get.adminid', 0, 'intval');
        if (!$adminid) {
            $this->error('参数错误');
        }
        $detail = M('Admin')->where(array('adminid' => $adminid))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
		$Auth = M('Auth');
		$list = $Auth->where(array('id' => array('neq', 1),'status' => array('eq', 1)))->order('id asc')->select();
        $this->assign('detail', $detail);
        $this->assign('list', $list);
        $this->display();
    }
    public function update()
    {
        if (IS_POST) {
            $data = I('post.');
            if (!$data['adminid']) {
                return false;
            }
			$authority = I('post.authority', 0, 'intval');
			$nickname = I('post.nickname');
			$password = I('post.password');
			if (!$authority) {
				$this->error('请选择角色组');
			}
			$email = I('post.email');
            if (is_email($email) == false) {
                $this->error('邮箱格式不正确');
            }
			if($password != ''){
				if (is_password($password) == false) {
					$this->error('密码必须在6-18个字符之间');
				}
				if($password != $data['password_n']){
					$this->error('两次密码不一致');
				}
				$data['encrypt'] = create_randomstr();
				$data['password'] = md5(md5(trim($password)).$data['encrypt']);
				$result = M('Admin')->where(array('adminid' => $data['adminid']))->save($data);
			}else{
				$result = M('Admin')->where(array('adminid' => $data['adminid']))->save(array('nickname' => $nickname ,'authority' => $authority,'email' => $email));
			}
            if ($result !== false) {
                $this->success('修改成功', U('Admin/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }
	public function auth_list()
    {
		$Admin = M('Auth');
		$count = $Admin->count();
        $p = getpage($count,20);
        $list = $Admin->where(array('id' => array('neq', 1)))->order('id asc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $p->show());
        $this->display();
    }
	public function auth_delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id || $id == 1) {
            $this->error('参数错误');
        }
        if (M('Auth')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	public function auth_add()
    {
        $this->display();
    }
	public function auth_insert()
    {
		if (IS_POST) {
			$data = I('post.');
            if (!$data['id'] || $data['id'] == 1) {
                return false;
            }
			$title = I('post.title');
			if (!$title) {
				$this->error('请输入用户组名称');
			}
			$data['inputtime'] = NOW_TIME;
			$id = M('Auth')->add($data);
            if ($id > 0) {
                $this->success('修改成功', U('Admin/auth_list'));
            } else {
                $this->error('修改失败');
            }
		}
	}
	public function auth_edit()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id || $id == 1) {
            $this->error('参数错误');
        }
        $detail = M('Auth')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function auth_update()
    {
        if (IS_POST) {
            $data = I('post.');
            if (!$data['id'] || $data['id'] == 1) {
                return false;
            }
			$title = I('post.title');
			if (!$title) {
				$this->error('请输入用户组名称');
			}
			$data['inputtime'] = NOW_TIME;
			$result = M('Auth')->where(array('id' => $data['id']))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/auth_list'));
            } else {
                $this->error('修改失败');
            }
        }
    }
}