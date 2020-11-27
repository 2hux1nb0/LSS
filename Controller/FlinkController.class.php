<?php
namespace Admin\Controller;
use Think\Controller;
class FlinkController extends PublicController
{
    public function index()
    {
        $link = M('Flink');
        $count = $link->count();
        $p = getpage($count,12);
        $list = $link->order('sort desc,linkid desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
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
				$this->error('请输入链接名称');	
			}
			$url = I('post.url');
			$reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
			if (!preg_match($reg, $url)) {
                $this->error('网址格式不正确');
            }
            $linkid = M('Flink')->add($data);
            if ($linkid > 0) {
                $this->success('添加成功', U('Flink/index'));
            } else {
                $this->error('添加失败');
            }
        }
    }
    public function edit()
    {
        $linkid = I('get.linkid', 0, 'intval');
        if (!$linkid) {
            $this->error('参数错误');
        }
        $detail = M('Flink')->where(array('linkid' => $linkid))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function update()
    {
        if (IS_POST) {
            $linkid = I('post.linkid', 0, 'intval');
            if (!$linkid) {
                return false;
            }
            $data = I('post.');
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入链接名称');	
			}
			$url = I('post.url');
			$reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
			if (!preg_match($reg, $url)) {
                $this->error('网址格式不正确');
            }
            $result = M('Flink')->where(array('linkid' => $linkid))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Flink/index'));
            } else {
                $this->erroe('修改失败');
            }
        }
    }
    public function listorder()
    {
        if (IS_POST) {
            $sort = I('post.sort');
            foreach ($sort as $key => $val) {
                M('Flink')->where(array('linkid' => $key))->save(array('sort' => intval($val)));
            }
            $this->success('排序成功');
        }
    }
    public function delete()
    {
        $linkid = I('get.linkid', 0, 'intval');
        if (!$linkid) {
            $this->error('参数错误');
        }
        if (M('Flink')->where(array('linkid' => $linkid))->delete()) {
            $this->success('删除成功', U('Flink/index'));
        } else {
            $this->error('删除失败');
        }
    }
}