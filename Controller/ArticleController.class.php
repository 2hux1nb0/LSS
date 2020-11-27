<?php
namespace Admin\Controller;

use Think\Controller;

class ArticleController extends PublicController
{

    public function index()
    {
        $q = I('get.q');
        $catid = I('get.catid');
        if (! empty($q)) {
            $where['title'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['info'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        if ($catid && is_int($catid)) {
            $map['catid'] = $catid;
        }
        $map['status'] = 1;
        $article = M('Article');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)
            ->order('sort desc,id desc')
            ->limit($p->firstRow, $p->listRows)
            ->select();
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
            if (! $catid) {
                $this->error('请选择部门');
            }
            $title = I('post.title');
            if ($title == '') {
                $this->error('请输入员工姓名');
            }
            if (! empty($_POST['editorValue'])) {
                $data['content'] = auto_save_image($_POST['editorValue']);
            }
            $data['content'] = htmlspecialchars_decode($data['editorValue']);
            if (empty($data['thumb']) && ! empty($_POST['editorValue'])) {
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\\.(gif|jpg|jpeg|bmp|png))\\2/i", $data['content'], $matches)) {
                    $data['thumb'] = $matches[3][0];
                }
            }
            if ($_POST['pdescription'] == '' && ! empty($_POST['editorValue'])) {
                $data['pdescription'] = str_cut(str_replace(array(
                    '\\r\\n',
                    '\\t'
                ), '', strip_tags($data['content'])), 300);
            }
            if ($_POST['info'] == '' && ! empty($_POST['editorValue'])) {
                $data['info'] = str_cut(str_replace(array(
                    '\\r\\n',
                    '\\t'
                ), '', strip_tags($data['content'])), 200);
            }
            $data['username'] = session('admin_auth.username');
            if ($_POST['inputtime'] == '') {
                $data['inputtime'] = $data['updatetime'] = NOW_TIME;
            } else {
                $data['inputtime'] = $data['updatetime'] = strtotime($_POST['inputtime']);
            }
            $data['gallery'] = array2string($data['gallery']);
            $adminid = session('admin_auth.adminid');
            $id = M('Article')->add($data);
            if ($id > 0) {
                if (isset($data['submit'])) {
                    action_log('add_article', 'Article', $id, $adminid);
                    $this->success('添加成功', U('Article/index'));
                }
                if (isset($data['submit_continue'])) {
                    action_log('add_article', 'Article', $id, $adminid);
                    $this->success('添加成功');
                }
            } else {
                $this->error('添加失败');
            }
        }
    }

    public function delete()
    {
        $id = I('get.id', 0, 'intval');
        if (! $id) {
            $this->error('参数错误');
        }
        if (M('Article')->where(array(
            'id' => $id,
            'status' => array(
                'eq',
                0
            )
        ))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function edit()
    {
        $id = I('get.id', 0, 'intval');
        if (! $id) {
            $this->error('参数错误');
        }
        $detail = M('Article')->where(array(
            'id' => $id,
            'status' => array(
                'eq',
                1
            )
        ))->find();
        if (! $detail) {
            $this->error('此记录不存在');
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
            if (! $data['id']) {
                return false;
            }
            /*$catid = I('post.catid', 0, 'intval');
            if (! $catid) {
                $this->error('请选择部门');
            }*/
            $catname = I('post.catname');
            $title = I('post.title');
            if ($title == '') {
                $this->error('请输入员工姓名');
            }
            if (! empty($_POST['editorValue'])) {
                $data['content'] = auto_save_image($_POST['editorValue']);
            }
            $data['content'] = htmlspecialchars_decode($data['editorValue']);
            if (empty($data['thumb']) && ! empty($_POST['editorValue'])) {
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\\.(gif|jpg|jpeg|bmp|png))\\2/i", $data['content'], $matches)) {
                    $data['thumb'] = $matches[3][0];
                }
            }
            if ($_POST['pdescription'] == '' && ! empty($_POST['editorValue'])) {
                $data['pdescription'] = str_cut(str_replace(array(
                    '\\r\\n',
                    '\\t'
                ), '', strip_tags($data['content'])), 300);
            }
            if ($_POST['info'] == '' && ! empty($_POST['editorValue'])) {
                $data['info'] = str_cut(str_replace(array(
                    '\\r\\n',
                    '\\t'
                ), '', strip_tags($data['content'])), 200);
            }
            $data['gallery'] = array2string($data['gallery']);
            $data['updatetime'] = NOW_TIME;
            if ($_POST['inputtime'] == '') {
                $data['inputtime'] = NOW_TIME;
            } else {
                $data['inputtime'] = strtotime($_POST['inputtime']);
            }
            $result = M('Article')->where(array(
                'id' => $data['id']
            ))->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Article/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }

    /*
     * public function deleteBatch()
     * {
     * if (IS_POST) {
     * $ids = I('post.id');
     * if (empty($ids)) {
     * $this->error('请选择删除文章');
     * }
     * $count = count($ids);
     * foreach ($ids as $key => $val) {
     * M('Article')->where(array('id' => $val))->delete();
     * }
     * $this->success('成功删除' . $count . '条数据');
     * }
     * }
     * public function statusBatch()
     * {
     * if (IS_POST) {
     * $ids = I('post.id');
     * if (empty($ids)) {
     * $this->error('请选择数据');
     * }
     * $conut = count($ids);
     * foreach ($ids as $key => $val) {
     * $status = M('Article')->where(array('id' => $val))->getField('status');
     * $status == 1 ? $data['status'] = 0 : ($data['status'] = 1);
     * M('Article')->where(array('id' => $val))->save($data);
     * }
     * $this->success('成功更新' . $conut . '条数据');
     * }
     * }
     */
    public function sortBatch()
    {
        if (IS_POST) {
            $sort_order = I('post.sort');
            foreach ($sort_order as $key => $val) {
                M('Article')->where(array(
                    'id' => $key
                ))->save(array(
                    'sort' => $val
                ));
            }
            $this->success('排序成功');
        }
    }

    public function status()
    {
        $id = I('get.id', 0, 'intval');
        $status = intval(I('status'));
        $status = $status ? 0 : 1;
        if (M('Article')->where(array(
            'id' => $id
        ))->save(array(
            'status' => $status,
            'updatetime' => NOW_TIME
        ))) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    public function recycle()
    {
        $article = M('Article');
        $count = $article->where(array(
            'status' => array(
                'eq',
                0
            )
        ))->count();
        $p = getpage($count, 10);
        $article_list = $article->where(array(
            'status' => array(
                'eq',
                0
            )
        ))
            ->order('sort desc,id desc')
            ->limit($p->firstRow, $p->listRows)
            ->select();
        $this->assign('article_list', $article_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
    }

    public function clear()
    {
        $res = M('Article')->where(array(
            'status' => array(
                'eq',
                0
            )
        ))->delete();
        if ($res !== false) {
            $this->success('清空回收站成功！');
        } else {
            $this->error('清空回收站失败！');
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