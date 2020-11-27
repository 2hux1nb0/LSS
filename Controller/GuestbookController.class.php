<?php
namespace Admin\Controller;
use Think\Controller;
class GuestbookController extends PublicController
{
	public function index()
	{
		$status = I('get.status');
		if ($status != "" && !is_int($status)) {
			$map['status'] = array('eq', $status);
		}
       	$guest = M('Guestbook');
        $count = $guest->where($map)->count();
        $p = getpage($count,12);
        $book = $guest->where($map)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('book', $book);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('Guestbook')->where(array('id' => $id))->delete()) {
            $this->success('删除成功', U('Guestbook/index'));
        } else {
            $this->error('删除失败');
        }
    }
	public function edit()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('Guestbook')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此留言不存在');
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
			$data['replytime'] = NOW_TIME;
            $data['status'] = 1;
			$replycontent = I('post.replycontent');
			if ($replycontent == '') {
				$this->error('回复内容不能为空');	
			}
			if (!empty($_POST['replycontent'])) {
                $data['replycontent'] = auto_save_image($_POST['replycontent']);
            }
			$mailaddr= I('post.email');
			$title=I('post.title');
            $result = M('Guestbook')->where(array('id' => $id))->save($data);
            if ($result !== false) {
				send_email($mailaddr,$title,$replycontent);
                $this->success('回复成功', U('Guestbook/index'));
            } else {
                $this->erroe('回复失败');
            }
        }
    }
}
