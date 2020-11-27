<?php
namespace Admin\Controller;
use Think\Controller;
class HaviorController extends PublicController
{
    public function index()
    {
		$Havior = M('action');
		$count = $Havior->count();
        $p = getpage($count,20);
        $list = $Havior->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $p->show());
        $this->display();
    }
	public function status()
    {
		$id = I('get.id', 0, 'intval');
		$status = intval(I('status'));
        $status = $status ? 0 : 1;
       if (M('action')->where(array('id'=>$id))->save(array('status' => $status))) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('action')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	
    public function add()
	{
		$this->display();
	}
    public function insert()
    {
        if (IS_POST) {
            $data = I('post.');
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入行为标识');
			}
			$data['updatetime'] = NOW_TIME;
			$id = M('Action')->add($data);
			if ($id > 0) {
				if (isset($data['submit'])) {
					$this->success('添加成功', U('Havior/index'));
				}
				if (isset($data['submit_continue'])) {
					$this->success('添加成功');
				}
			} else {
				$this->error('添加失败');
			}
        }
    }
    public function edit()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('action')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function update()
    {
        if (IS_POST) {
            $data = I('post.');
            if (!$data['id']) {
                return false;
            }
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入行为标识');
			}
            $data['updatetime'] = NOW_TIME;
            $result = M('Action')->where(array('id' => $data['id']))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Havior/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }
}