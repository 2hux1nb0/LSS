<?php
namespace Admin\Controller;
use Think\Controller;
class ActionController extends PublicController
{
    public function index()
    {
		$Action = M('action_log');
		$count = $Action->count();
        $p = getpage($count,20);
        $list = $Action->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $p->show());
        $this->display();
    }

	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('action_log')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	
	public function del()
    {
		$ids=M('action_log');
		$sql = 'TRUNCATE `xg_action_log`';
		$row=$ids->execute($sql);
		if ($row !== false) {
			$this->success('清空成功');
		} else {
			$this->error('清空失败');
		}
    }
    public function view()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('action_log')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
}