<?php
namespace Admin\Controller;
use Think\Controller;
class BannerController extends PublicController
{
    public function index()
    {
        $link = M('Banner');
        $count = $link->count();
        $p = getpage($count,12);
        $list = $link->order('sort desc,id desc')->limit($p->firstRow, $p->listRows)->select();
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
				$this->error('请输入幻灯片名称');	
			}
			$url = I('post.url');
			$reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
			if (!preg_match($reg, $url)) {
                $this->error('网址格式不正确');
            }
			$data['inputtime'] = NOW_TIME;
            $id = M('Banner')->add($data);
            if ($id > 0) {
                $this->success('添加成功', U('Banner/index'));
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
        $detail = M('Banner')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此记录不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function update()
    {
        if (IS_POST) {
            $id = I('post.id', 0, 'intval');
            if (!$id) {
                return false;
            }
            $data = I('post.');
			$name = I('post.name');
			if ($name == '') {
				$this->error('请输入幻灯片名称');	
			}
			$url = I('post.url');
			$reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
			if (!preg_match($reg, $url)) {
                $this->error('网址格式不正确');
            }
            $result = M('Banner')->where(array('id' => $id))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Banner/index'));
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
                M('Banner')->where(array('id' => $key))->save(array('sort' => intval($val)));
            }
            $this->success('排序成功');
        }
    }
    public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('Banner')->where(array('id' => $id))->delete()) {
            $this->success('删除成功', U('Banner/index'));
        } else {
            $this->error('删除失败');
        }
    }
}