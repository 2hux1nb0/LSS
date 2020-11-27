<?php
namespace Admin\Controller;

use Think\Controller;

class LpadController extends PublicController
{

    // 入口(统计信息)
    public function index()
    {
        $this->display();
    }

    //当前分配信息列表
    public function distribute(){
        
        $distribute = M('distribute');
        $count = $distribute->where($map)->count();
        $p = getpage($count, 44);
        $dist_list = $distribute->where($map)
            ->select();
        $this->listDist();
        $this->assign('dist_list', $dist_list);
        $this->assign('page', $p->show());
    	$this->display();
    }
    public function personal(){
        $personal = M('personal');
        $count = $personal->where($map)->count();
        $p = getpage($count, 44);
        $pers_list = $personal->where($map)
            ->select();
        $this->listRule();
        $this->assign('pers_list', $pers_list);
        $this->assign('page', $p->show());
        $this->display();
    }
    public function record()
    {
        $q = I('get.q');
        $catid = I('get.catid');
        if (! empty($q)) {
            $where['catid'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['inputtime'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if ($catid && is_int($catid)) {
            $map['catid'] = $catid;
        }
        // $map['status'] = 1;
        $article = M('distribute');
        $pers = M('personal');
        $count = $article->where($map)->count();
        $countt = $pers->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)
            // ->order('id desc')
            ->limit($p->firstRow, $p->listRows)
            ->select();
        foreach ($article_list as $k => $v) {
            $article_list[$k]['catid'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['catid']);
            $article_list[$k]['inputtime'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['inputtime']);
        }
        $pers_list = $pers->where($map)
            // ->order('id desc')
            ->limit($p->firstRow, $p->listRows)
            ->select();
        foreach ($pers_list as $k => $v) {
            $pers_list[$k]['catid'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['catid']);
            $pers_list[$k]['inputtime'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['inputtime']);
        }
        $cate_list = $this->listCate();
        $this->assign('q', $q);
        $this->assign('cate_list', $cate_list);
        $this->assign('article_list', $article_list);
        $this->assign('pers_list', $pers_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        // 赋值分页输出
        $this->display();
    }
    //add添加分配规则
    public function add(){
        $get = I('get.');
        if(isset($get['sort_id'])){
            $sort_id = $get['sort_id'];
        }else{
            $sort_id = 0;
        }
        if(isset($get['desc'])){
            $desc = $get['desc'];
        }else{
            $desc = 1;
        }
        $product = M('Lpa');
        $order = '';
        switch($sort_id){
            case 0:
                $order .= 'id';
                break;
            case 1:
                $order .= 'price';
                break;
            case 2:
                $order .= 'count';
                break;
            default:
                $order .= 'id';
                break;
        }
        $order .= ' '.($desc?'desc':'asc');
        $count = $product->where($map)->count();
        $p = getpage($count, 44);
        $product_list = $product->where($map)
            ->order($order)
            ->limit($p->firstRow, $p->listRows)
            ->select();
        $cate_list = $this->listCate();
        $this->assign('cate_list', $cate_list);
        $this->assign('sort_id', $sort_id);
        $this->assign('desc',$desc);
        $this->assign('product_list', $product_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        $this->display();  
    }
    public function insert(){
        if (IS_POST) {
            $data = I('post.');
            $bumen = I('post.bumen');
            if ($bumen == ''){
                $this->error('请选择所在部门');
            }
            $goodsname = I('post.goodsname');
            if ($goodsname == '') {
                $this->error('请选择用品名称');
            }
            $type = I('post.type');
            if ($type == ''){
                $this->error('请选择配备方式');
            }
            $cycle = I('post.cycle');
            if ($cycle == ''){
                $this->error('请选择配发周期');
            }
            $quantity = I('post.quantity');
            if ($quantity == ''){
                $this->error('请输入配发数量');
            }
            $inputtime = I('post.inputtime');
            if ($inputtime == ''){
                $this->error('请输入配发时间');
            }
            $manager = I('post.manager');
            if ($manager == '') {
                $this->error('请输入保管人姓名');
            }
            $distri = M('distribute');
            $bmid = $distri->add($data);
            if ($bmid > 0) {
                $this->success('添加成功', U('Lpad/distribute'));
            } else {
                $this->error('添加失败');
            }
        }
    }
    //rules.html添加个人分配
    public function rules(){
        $get = I('get.');
        if(isset($get['sort_id'])){
            $sort_id = $get['sort_id'];
        }else{
            $sort_id = 0;
        }
        if(isset($get['desc'])){
            $desc = $get['desc'];
        }else{
            $desc = 1;
        }
        $product = M('Lpa');
        $order = '';
        switch($sort_id){
            case 0:
                $order .= 'id';
                break;
            case 1:
                $order .= 'price';
                break;
            case 2:
                $order .= 'count';
                break;
            default:
                $order .= 'id';
                break;
        }
        $order .= ' '.($desc?'desc':'asc');
        $count = $product->where($map)->count();
        $p = getpage($count, 44);
        $product_list = $product->where($map)
            ->order($order)
            ->limit($p->firstRow, $p->listRows)
            ->select();

        $q = I('get.q');
        $catid = I('get.catid');
        if (! empty($q)) {
         
            $where['catid'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        if ($catid && is_int($catid)) {
            $map['catid'] = $catid;
        }
        $article = M('Article');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)
            ->limit($p->firstRow, $p->listRows)
            ->select();
        foreach ($article_list as $k => $v) {
            $article_list[$k]['catid'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['catid']);
        }
        $cate_list = $this->listCate();
        $this->assign('cate_list', $cate_list);
        $this->assign('sort_id', $sort_id);
        $this->assign('desc',$desc);
        $this->assign('product_list', $product_list);
        $this->assign('q', $q);
        $this->assign('article_list', $article_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        $this->display();


        if(IS_POST){
            $data = I('post.');
            // $_POST['nme'] = implode(',',$_POST['name']);
            // $model->name = $_POST['name'];
            $catid = I('post.catid');
            if ($catid == ''){
                $this->error('请选择所在部门');
            }
            $bumen = I('post.bumen');
            if ($bumen == ''){
                $this->error('请选择所在部门');
            }
            $goodsname = I('post.goodsname');
            if ($goodsname == ''){
                $this->error('请选择用品名称');
            }
            $type = I('post.type');
            if ($type == ''){
                $this->error('请选择配备方式');
            }
            $cycle = I('post.cycle');
            if ($cycle == ''){
                $this->error('请选择配发周期');
            }
            $quantity = I('post.quantity');
            if ($quantity == ''){
                $this->error('请输入配发数量');
            }
            $inputtime = I('post.inputtime');
            if ($inputtime == ''){
                $this->error('请输入配发时间');
            }
            $pers = M('personal');
            $title = $data['name'];
            $success = 0;
            $fail = 0;
            foreach ($title as $key => $value) {
               $data['name'] = $value;
               if($pers->add($data)){
                $success ++;
               }else{
                $fail ++;
               }
            }
            if ($fail = 0){
                $this->success('添加成功', U('Lpad/rules'));
            }else if($success==0){
                $this->error('添加失败');
            }else{
                $this->success('成功添加'.$success.'个,'.$fail.'个添加失败.');
            }
            $cate = M('category');
            $omg = $data['fuck'];
            $success = 0;
            $fail = 0;
            foreach ($omg as $key => $value) {
               $data['fuck'] = $value;
               if($cate->add($data)){
                $success ++;
               }else{
                $fail ++;
               }
            }
            if ($fail = 0){
                $this->success('添加成功', U('Lpad/rules'));
            }else if($success==0){
                $this->error('添加失败');
            }else{
                $this->success('成功添加'.$success.'个,'.$fail.'个添加失败.');
            }
        }
    }
    public function rule(){
        
    $q = I('get.q');
        $catid = I('get.catid');
        if (! empty($q)) {
         
            $where['catid'] = array(
                'like',
                '%' . $q . '%'
            );
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        if ($catid && is_int($catid)) {
            $map['catid'] = $catid;
        }
      
        $article = M('Article');
        $count = $article->where($map)->count();
        $p = getpage($count, 10);
        $article_list = $article->where($map)
           
            ->limit($p->firstRow, $p->listRows)
            ->select();
        foreach ($article_list as $k => $v) {
           
            $article_list[$k]['catid'] = str_replace($q, '<font color=red>' . $q . '</font>', $v['catid']);
        }
        
        $cate_list = $this->listCate();
        $this->assign('q', $q);
        $this->assign('cate_list', $cate_list);
        $this->assign('article_list', $article_list);
        
        $this->assign('page', $p->show());
        
        $this->display();
    }
    public function delete()
    {
        $bmid = I('get.bmid', 0, 'intval');
        if (! $bmid) {
            $this->error('参数错误');
        }
        $sub_cates = $this->listDist($bmid);
        if (empty($sub_cates)) {
            $cate = M('distribute');
            if ($cate->where(array(
                'bmid' => $bmid
            ))->delete()) {
                $this->success('删除成功', U('Lpad/distribute'));
            } else {
                $this->error('删除失败');
            }
        }
    }
    public function delete1()
    {
        $id = I('get.id', 0, 'intval');
        if (! $id) {
            $this->error('参数错误');
        }
        $sub_cates = $this->listRule($id);
        if (empty($sub_cates)) {
            $cate = M('personal');
            if ($cate->where(array(
                'id' => $id
            ))->delete()) {
                $this->success('删除成功', U('Lpad/personal'));
            } else {
                $this->error('删除失败');
            }
        }
    }
}
