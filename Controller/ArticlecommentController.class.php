<?php
namespace Admin\Controller;
use Think\Controller;
class ArticlecommentController extends PublicController
{
	//评论列表
    public function index()
    {
		$articleid = I('get.articleid');
		if ($articleid != "" && !is_int($articleid)) {
			$map['articleid'] = array('eq', $articleid);
		} //根据文章的ID列出该文章的评论
        $article = M('Articlecomment');
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
        if (M('Articlecomment')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	
	public function status()
    {
		$id = I('get.id', 0, 'intval');
		$status = intval(I('status'));
        $status = $status ? 0 : 1;
       if (M('Articlecomment')->where(array('id'=>$id))->save(array('status' => $status))) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
	
	public function comment()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('Articlecomment')->where(array('id' => $id))->find();
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
			$data['replytime'] = NOW_TIME;
			$replycontent = I('post.replycontent');
			if ($replycontent == '') {
				$this->error('回复内容不能为空');	
			}
			if (!empty($_POST['replycontent'])) {
                $data['replycontent'] = auto_save_image($_POST['replycontent']);
            }
			/*
			$mailaddr=I('post.email');
			$title=I('post.title');*/
            $result = M('Articlecomment')->where(array('id' => $id))->save($data);
            if ($result !== false) {
				/*
					send_email($mailaddr,$title,$replycontent);
					//发送到邮箱
				*/
                $this->success('回复成功', U('Articlecomment/index'));
            } else {
                $this->erroe('回复失败');
            }
        }
    }
}

