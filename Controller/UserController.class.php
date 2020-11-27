<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends PublicController
{
	public function index()
	{
		$q = I('get.q');
        if (!empty($q)) {
            $where['username'] = array('like', '%' . $q . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $user = M('User');
        $count = $user->where($map)->count();
        $p = getpage($count, 10);
        $user_list = $user->where($map)->order('userid desc')->limit($p->firstRow, $p->listRows)->select();
        foreach ($user_list as $k => $v) {
            $user_list[$k]['username'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['username']);
        }
        $this->assign('q', $q);
        $this->assign('user_list', $user_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	
	//会员锁定
	public function status()
    {
		$userid = I('get.userid', 0, 'intval');
		$islock = intval(I('islock'));
        $islock = $islock ? 0 : 1;
       if (M('User')->where(array('userid'=>$userid))->save(array('islock' => $islock))) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
	//删除
	public function delete(){
		$userid = I('get.userid', 0, 'intval');
		if (!$userid) {
            $this->error('参数错误');
        }
		if (M('User')->where(array('userid'=>$userid))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
	}
	//修改
	public function edit(){
		$userid = I('get.userid', 0, 'intval');
		if (!$userid){
			$this->error('参数错误');
		}
		$detail = M('User')->where(array('userid' => $userid))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
		$this->assign('detail', $detail);
		$this->display();
	}
	public function update()
	{
		if (IS_POST){
			$data = I('post.');
			if (!$data['userid']){
                return false;
            }
			$data['point'] = intval($data['point']);
			$result = M('User')->where(array('userid' => $data['userid']))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('User/index'));
            } else {
                $this->error('修改失败');
            }
		}
	}
	
	//会员组列表
	public function group_list()
	{

        $user = M('user_group');
        $count = $user->where($map)->count();
        $p = getpage($count, 10);
        $user_list = $user->where($map)->order('sort desc,groupid desc')->limit($p->firstRow, $p->listRows)->select();

        $this->assign('user_list', $user_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	
	//会员组排序
	public function sortBatch()
    {
        if (IS_POST) {
            $sort_order = I('post.sort');
            foreach ($sort_order as $key => $val) {
                M('user_group')->where(array('groupid' => $key))->save(array('sort' => $val));
            }
            $this->success('排序成功');
        }
    }
	//会员组删除
	public function group_delete(){
		$groupid = I('get.groupid', 0, 'intval');
		if (!$groupid) {
            $this->error('参数错误');
        }
		if (M('user_group')->where(array('groupid'=>$groupid))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
	}
	
	
	public function group_add()
    {
        if (IS_POST) {
            $data = I('post.');
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入会员组名称');	
			}
            $data['point'] = intval($data['point']);
            $groupid = M('user_group')->add($data);
			if ($groupid > 0) {
                $this->success('添加成功', U('User/group_list'));
            } else {
                $this->error('添加失败');
            }
        } else {
            return $this->display();
        }
    }
	
	 public function group_edit()
    {
        $groupid = I('get.groupid', 0, 'intval');
        if (!$groupid) {
            $this->error('参数错误');
        }
        $detail = M('user_group')->where(array('groupid' => $groupid))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function group_update()
    {
        if (IS_POST) {
            $groupid = I('post.groupid', 0, 'intval');
            if (!$groupid) {
                return false;
            }
            $data = I('post.');
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入会员组名称');	
			}
			$data['point'] = intval($data['point']);
            $result = M('user_group')->where(array('groupid' => $groupid))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('User/group_list'));
            } else {
                $this->erroe('修改失败');
            }
        }
    }
	
	
	
}