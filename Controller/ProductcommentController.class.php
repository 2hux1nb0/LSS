<?php
namespace Admin\Controller;
use Think\Controller;
class ProductcommentController extends PublicController
{
	//评论列表
    public function index()
    {
        $articleid = I('get.articleid');
        if ($articleid && is_int($articleid)) {
            $map['articleid'] = $articleid;
        } //根据文章的ID列出该文章的评论
		
        $article = M('Productcomment');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)->order('articleid desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('article_list', $article_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	
	//删除
	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('Productcomment')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	
	public function comment()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('Productcomment')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此评论不存在');
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
            $result = M('Productcomment')->where(array('id' => $id))->save($data);
            if ($result !== false) {
				/*
					$mailaddr= $_POST["email"];
					$title=$_POST["title"];
					$content=$_POST['replycontent'];
					SendMail($mailaddr,$title,$content);
					//发送到邮箱
				*/
                $this->success('回复成功', U('Productcomment/index'));
            } else {
                $this->erroe('回复失败');
            }
        }
    }
}