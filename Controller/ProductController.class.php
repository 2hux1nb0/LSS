<?php
namespace Admin\Controller;
use Think\Controller;
class ProductController extends PublicController
{
    public function index()
    {
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
        $article = M('Product');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)->order('sort desc,id desc')->limit($p->firstRow, $p->listRows)->select();
        foreach ($article_list as $k => $v) {
            $article_list[$k]['title'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['title']);
            $article_list[$k]['info'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['field']);
        }
        $cate_list = $this->listCate();
        $this->assign('q', $q);
        $this->assign('cate_list', $cate_list);
        $this->assign('article_list', $article_list);
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
				$this->error('请输入产品名称');
			}
			if (!empty($_POST['info'])) {
                $data['info'] = auto_save_image($_POST['info']);
            }
            if (!empty($_POST['editorValue'])) {
                $data['content'] = auto_save_image($_POST['editorValue']);
            }
			$data['content'] = htmlspecialchars_decode($data['editorValue']);
            if (empty($data['thumb']) && !empty($_POST['editorValue'])) {
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\\.(gif|jpg|jpeg|bmp|png))\\2/i", $data['content'], $matches)) {
                    $data['thumb'] = $matches[3][0];
                }
            }
            if ($_POST['description'] == '' && !empty($_POST['editorValue'])) {
                $data['description'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 300);
            }
			if ($_POST['info'] == '' && !empty($_POST['editorValue'])) {
                $data['info'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 200);
            }
            $data['inputtime'] = $data['updatetime'] = NOW_TIME;
            $data['gallery'] = array2string($data['gallery']);
			$id = M('Product')->add($data);
			if ($id > 0) {
				if (isset($data['submit'])) {
					$this->success('添加成功', U('Product/index'));
				}
				if (isset($data['submit_continue'])) {
					$this->success('添加成功');
				}
			} else {
				$this->error('添加失败');
			}
        }
    }
	
	//删除产品
	public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误');
        }
        if (M('Product')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
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
        $detail = M('Product')->where(array('id' => $id))->find();
		if (!$detail) {
            $this->error('产品不存在');
        }
        $gallery = string2array($detail['gallery']);
        $cate_list = $this->listCate();
        $this->assign('detail', $detail);
        $this->assign('gallery', $gallery);
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
				$this->error('请输入产品名称');
			}
			if (!empty($_POST['info'])) {
                $data['info'] = auto_save_image($_POST['info']);
            }
            if (!empty($_POST['editorValue'])) {
                $data['content'] = auto_save_image($_POST['editorValue']);
            }
			$data['content'] = htmlspecialchars_decode($data['editorValue']);
            if (empty($data['thumb']) && !empty($_POST['editorValue'])) {
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\\.(gif|jpg|jpeg|bmp|png))\\2/i", $data['content'], $matches)) {
                    $data['thumb'] = $matches[3][0];
                }
            }
            if ($_POST['description'] == '' && !empty($_POST['editorValue'])) {
                $data['description'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 300);
            }
			if ($_POST['info'] == '' && !empty($_POST['editorValue'])) {
                $data['info'] = str_cut(str_replace(array('\\r\\n', '\\t'), '', strip_tags($data['content'])), 200);
            }
            $data['gallery'] = array2string($data['gallery']);
            $data['updatetime'] = NOW_TIME;
            $result = M('Product')->where(array('id' => $data['id']))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Product/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }
	
	//修改排序
	public function sortBatch()
    {
        if (IS_POST) {
            $sort_order = I('post.sort');
            foreach ($sort_order as $key => $val) {
                M('Product')->where(array('id' => $key))->save(array('sort' => $val));
            }
            $this->success('排序成功');
        }
    }
	
    public function uploadImg()
    {
        echo __ROOT__ . '/' . uploadPic();
    }
    public function uploadDel()
    {
        $src = I('get.src');
        $src = str_replace(__ROOT__ . '/', '', $src);
        if (file_exists($src)) {
            unlink($src);
        }
        echo $src;
    }
}