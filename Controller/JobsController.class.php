<?php
namespace Admin\Controller;
use Think\Controller;
class JobsController extends PublicController
{
	 public function index(){
		$q = I('get.q');
        $catid = I('get.catid');
        $status = I('get.status');
        if (!empty($q)) {
            $where['title'] = array('like', '%' . $q . '%');
            $where['info'] = array('like', '%' . $q . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        if ($catid && is_int($catid)) {
            $map['catid'] = $catid;
        }
        if (is_int($status)) {
            $map['status'] = $status;
        }
        $article = M('Jobs');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $jobs_list = $article->where($map)->order('sort desc,id desc')->limit($p->firstRow, $p->listRows)->select();
        foreach ($jobs_list as $k => $v) {
            $jobs_list[$k]['title'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['title']);
            $jobs_list[$k]['info'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['field']);
        }
        $cate_list = $this->listCate();
        $this->assign('q', $q);
        $this->assign('cate_list', $cate_list);
        $this->assign('jobs_list', $jobs_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	public function add()
    {
		$cate_list = $this->listCate();
		$this->assign('cate_list', $cate_list);
        $this->display();
    }
	
	public function insert()
    {
        if (IS_POST) {
            $data = I('post.');
			$catid = I('post.catid', 0, 'intval');
			if (!$catid) {
				$this->error('请选择栏目');
			}
			$title = I('post.title');
			if ($title == '') {
				$this->error('请输入文章标题');
			}
			if (!empty($_POST['info'])) {
                $data['info'] = auto_save_image($_POST['info']);
            }
            if (!empty($_POST['content'])) {
                $data['content'] = auto_save_image($_POST['content']);
            }
            if ($_POST['description'] == '' && !empty($_POST['description'])) {
                $data['description'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 300);
            }
            $data['inputtime'] = $data['updatetime'] = NOW_TIME;
			$id = M('Jobs')->add($data);
			//$url = __ROOT__ . '/index.php/Index/show/catid/' . $catid . '/id/' . $id;
			M('Jobs')->where(array('id' => $id))->save(array('url' => $url));
			if ($id > 0) {
				if (isset($data['submit'])) {
					$this->success('添加成功', U('Jobs/index'));
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
        $detail = M('Jobs')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('招聘信息不存在');
        }
        $cate_list = $this->listCate();
        $this->assign('detail', $detail);
        $this->assign('cate_list', $cate_list);
        $this->display();
    }
	
	public function update()
    {
        if (IS_POST) {
            $data = I('post.');
            if (!$data['id']) {
                return false;
            }
			$catid = I('post.catid', 0, 'intval');
			if (!$catid) {
				$this->error('请选择栏目');
			}
			$title = I('post.title');
			if ($title == '') {
				$this->error('请输入文章标题');
			}
			if (!empty($_POST['info'])) {
                $data['info'] = auto_save_image($_POST['info']);
            }
            if (!empty($_POST['content'])) {
                $data['content'] = auto_save_image($_POST['content']);
            }
            if ($_POST['description'] == '' && !empty($_POST['content'])) {
                $data['description'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 300);
            }
            $data['updatetime'] = NOW_TIME;
            $result = M('Jobs')->where(array('id' => $data['id']))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Jobs/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }
	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('Jobs')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	public function sortBatch()
    {
        if (IS_POST) {
            $sort_order = I('post.sort');
            foreach ($sort_order as $key => $val) {
                M('Jobs')->where(array('id' => $key))->save(array('sort' => $val));
            }
            $this->success('排序成功');
        }
    }
	
	//简历
	public function jobjoin_index(){
		$jobsid = I('get.jobsid');
        if ($jobsid && is_int($jobsid)) {
            $map['jobsid'] = $jobsid;
        } //根据文章的ID列出该文章的评论
        $jobs = M('jobjoin');
        $count = $jobs->where($map)->count();
        $p = getpage($count, 10);
        $jobs_list = $jobs->where($map)->order('jobsid desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('jobs_list', $jobs_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
	}
	
	public function jobjoin_delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('jobjoin')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	public function Comment()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        $detail = M('jobjoin')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('此留言不存在');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
}