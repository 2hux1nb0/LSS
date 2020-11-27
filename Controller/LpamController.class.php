<?php
namespace Admin\Controller;

use Think\Controller;

class LpamController extends PublicController
{

    // 入口(概览)
    public function index()
    {
        $product = M('Lpa');
        $no_stock  = $product->where("count=0")->count();
        $this->assign('no_stock',$no_stock);
        $this->display();
    }
    
    // 列表（添加/编辑）
    public function list()
    {
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
            // case 3:
            //     $order .= 'name';
            //     break;
            default:
                $order .= 'id';
                break;
        }
        $order .= ' '.($desc?'desc':'asc');
        $count = $product->where($map)->count();
        $p = getpage($count, 10);
        $product_list = $product->where($map)
            ->order($order)
            ->limit($p->firstRow, $p->listRows)
            ->select();
        // $cate_list = $this->listCate();
        // $this->assign('cate_list', $cate_list);
        foreach ($product_list as &$item) {
            $sdunit = substr($item['seller_day'], strlen($item['seller_day']) - 1, 1);
            $utunit = substr($item['use_time'], strlen($item['use_time']) - 2, 1);
            $uttype = substr($item['use_time'], strlen($item['use_time']) - 1, 1);
            $sd = substr($item['seller_day'], 0, strlen($item['seller_day']) - 1);
            $ut = substr($item['use_time'], 0, strlen($item['use_time']) - 2);
            if ($sd == "0" || intval($sd) < 0) {
                $item['seller_day'] = "视情况而定";
            } else if ($sdunit == 'A') {
                $item['seller_day'] = $sd . "天";
            } else if ($sdunit == 'B') {
                $item['seller_day'] = $sd . "工作日";
            }
            if (empty($ut) || $ut == "0" || intval($ut) < 0) {
                $item['use_time'] = "以具体使用为限";
            } else if ($uttype == 'A') {
                $item['use_time'] = $ut . ($utunit == 'B' ? '年' : '月') . '强制更换';
            } else if ($uttype == 'B') {
                $item['use_time'] = '建议' . $ut . ($utunit == 'B' ? '年' : '月') . '更换';
            } else if ($uttype == 'C') {
                $item['use_time'] = '未拆封' . $ut . ($utunit == 'B' ? '年' : '月') . '更换';
            }
            if ($item['image'] && strpos($item['image'], 'Images') !== false) {
                $item['image'] = __ROOT__ . '/Uploads/' . $item['image'];
            }
        }
        $this->assign('sort_id', $sort_id);
        $this->assign('desc',$desc);
        $this->assign('product_list', $product_list);
        // 赋值数据集
        $this->assign('page', $p->show());
        $this->display();
    }

    // 添加
    public function add()
    {
        $this->display();
    }

    // 编辑
    public function edit()
    {
        $id = I('get.id', 0, 'intval');
        if (! $id) {
            $this->error('参数错误', U('Lpam/list'));
        }
        $p = M('Lpa')->where(array(
            'id' => $id
        ))->find();
        if (! $p) {
            $this->error('产品不存在', U('Lpam/list'));
        }
        $p['seller_day_unit'] = substr($p['seller_day'], strlen($p['seller_day']) - 1, 1);
        $p['use_time_unit'] = substr($p['use_time'], strlen($p['use_time']) - 2, 1);
        $p['use_time_type'] = substr($p['use_time'], strlen($p['use_time']) - 1, 1);
        $p['seller_day'] = substr($p['seller_day'], 0, strlen($p['seller_day']) - 1);
        $p['use_time'] = substr($p['use_time'], 0, strlen($p['use_time']) - 2);
        $this->assign('p', $p);
        $this->display();
    }

    // 导出
    public function export()
    {
        $this->display();
    }

    // 导入
    public function import()
    {
        $post = I('post.');
        $get = I('get.');
        if ($_SESSION['excel_tmp']) {
            if (! $_SESSION['excel_tmp_deal'] || $get['import']) {
                $excel_tmp = $_SESSION['excel_tmp'];
                for ($i = 0; $i < count($excel_tmp); $i ++) {
                    $vs = array_values($excel_tmp[$i]);
                    if (preg_match('/^[0-9]*$/', $vs[0])) {
                        $_SESSION['excel_tmp_deal'] = array_slice($excel_tmp, $i);
                        break;
                    }
                }
            }
            $excel_tmp = $_SESSION['excel_tmp_deal'];
            if ($get['submit']) {
                $success = 0;
                $fail = 0;
                $tmparr = [];
                foreach ($excel_tmp as $item) {
                    $reg1 = preg_match('/[0-9]{1,}/', $item['seller_day'], $matchs1);
                    $reg2 = preg_match('/[0-9]{1,}/', $item['use_time'], $matchs2);
                    $reg3 = preg_match('/工作日/', $item['seller_day'], $matchs3);
                    $reg4 = preg_match('/年/', $item['use_time'], $matchs4);
                    $reg5 = preg_match('/推荐|建议/', $item['use_time'], $matchs5);
                    $reg6 = preg_match('/((未|不)拆封)|((未|不)使用)/', $item['use_time'], $matchs6);
                    if ($reg1) {
                        if ($reg3) {
                            $item['seller_day'] = $matchs1[0] . 'B';
                        } else {
                            $item['seller_day'] = $matchs1[0] . 'A';
                        }
                    } else {
                        $item['seller_day'] = '0A';
                    }
                    if ($reg2) {
                        if ($reg4) {
                            $item['use_time'] = $matchs2[0] . 'B';
                        } else {
                            $item['use_time'] = $matchs2[0] . 'A';
                        }
                        if ($reg6) {
                            $item['use_time'] .= 'C';
                        } else if ($reg5) {
                            $item['use_time'] .= 'B';
                        } else {
                            $item['use_time'] .= 'A';
                        }
                    } else {
                        $item['use_time'] = '0AA';
                    }
                    if (! empty($item['name'])) {
                        if(empty($item['seller_day'])){
                            unset($item['seller_day']);
                        }
                        if(empty($item['use_time'])){
                            unset($item['use_time']);
                        }
                        if(empty($item['count'])){
                            unset($item['count']);
                        }
                        array_splice($item, 0, 1);
                        array_push($tmparr, $item);
                    }
                }
                $x = 0;
                foreach ($tmparr as $item2) {
                    $id = M('Lpa')->add($item2);
                    if ($id) {
                        $success ++;
                        array_splice($excel_tmp, $x, 1);
                    } else {
                        $fail ++;
                    }
                    $x ++;
                }
                if ($fail == 0) {
                    $this->success("添加成功!", U("Lpam/list"));
                    unset($_SESSION['excel_tmp']);
                    unset($_SESSION['excel_tmp_deal']);
                } else if ($success == 0) {
                    $this->error("添加失败!", U("Lpam/import"));
                } else {
                    $this->success("成功添加" . $success . "条数据," . $fail . "条数据添加失败!", U("Lpam/import"));
                }
            }
            if ($get['resort'] || $get['delete'] || $get['import']) {
                $data = [];
                $xid = 1;
                for ($i = 0; $i < count($excel_tmp); $i ++) {
                    $vs = array_values($excel_tmp[$i]);
                    $vs[0] = $xid;
                    if ($get['delete'] && ($id = $get['id']) && $id == $xid) {
                        $get['delete'] = false;
                        continue;
                    }
                    $xid ++;
                    $tmparr = array(
                        'id' => '',
                        'name' => '',
                        'specification' => '',
                        'price' => '',
                        'count' => '',
                        'seller_day' => '',
                        'use_time' => '',
                        'image' => '',
                        'detail' => '',
                        'attention' => ''
                    );
                    if ($get['resort']) {
                        $sort = json_decode($get['sort_data'], true);
                        $x = 0;
                        foreach ($tmparr as $key => $value) {
                            $tmparr[$key] = $vs[intval($sort[$x]) - 1];
                            $x ++;
                        }
                    } else {
                        $x = 0;
                        foreach ($tmparr as $key => $value) {
                            $tmparr[$key] = $vs[$x];
                            $x ++;
                        }
                    }
                    array_push($data, $tmparr);
                }
                $_SESSION['excel_tmp_deal'] = $data;
                header("Location:" . U('Lpam/import'));
            } else {
                $data = $_SESSION['excel_tmp_deal'];
            }
            foreach ($data as &$item) {
                if ($item['image'] && strpos($item['image'], 'Images') !== false) {
                    $item['image'] = __ROOT__ . '/Uploads/' . $item['image'];
                }
            }
            $count = count($data);
            $p = getpage($count, 10);
            $show_data = array_slice($data, $p->firstRow, $p->listRows);
            $this->assign('import_list', $show_data);
            $this->assign('import_list_ser', urlencode(serialize($show_data)));
            // 赋值数据集
            $this->assign('page', $p->show());
        }
        $this->display();
    }

    // 添加操作
    public function addDo()
    {
        if (IS_POST) {
            $data = I('post.');
            if (empty($data['name'])) {
                $this->error("名称不能为空!");
            } else if (strlen($data['name']) > 63) {
                $this->error("名称长度请控制在64字内!");
            }
            if (empty($data['specification'])) {
                $this->error("品牌规格不能为空!");
            } else if (strlen($data['specification']) > 63) {
                $this->error("品牌规格长度请控制在64字内!");
            }
            if (empty($data['price'])) {
                $this->error("单价不能为空!");
            } else if (! preg_match('/^([0-9]{1,10})|([0-9]{1,10}\.[0-9]{1,3})$/', $data['price'])) {
                $this->error("单价格式不正确!");
            }
            if (empty($data['seller_day'])) {
                $data['seller_day'] = 0;
            } else if (! preg_match('/^[0-9]{1,10}$/', $data['seller_day'])) {
                $this->error("货期格式不正确!");
            } else {
                $data['seller_day'] = $data['seller_day'] . ($data['seller_day_unit'] == '2' ? 'B' : 'A');
            }
            if (empty($data['use_time'])) {
                $data['use_time'] = 0;
            } else if (! preg_match('/^[0-9]{1,10}$/', $data['use_time'])) {
                $this->error("有效期格式不正确!");
            } else {
                $data['use_time'] = $data['use_time'] . ($data['use_time_unit'] == '2' ? 'B' : 'A') . ($data['use_time_type'] == '3' ? 'C' : $data['use_time_type'] == '2' ? 'B' : 'A');
            }
            $insertData = array(
                'name' => $data['name'],
                'specification' => $data['specification'],
                'price' => $data['price'],
                'count' => $data['count'],
                'seller_day' => $data['seller_day'],
                'use_time' => $data['use_time'],
                'image' => $data['thumb'],
                'detail' => $data['detail'],
                'attention' => $data['attention']
            );
            $id = M('Lpa')->add($insertData);
            if ($id > 0) {
                if (isset($data['submit'])) {
                    $this->success('添加成功', U('Lpam/list'));
                }
                if (isset($data['submit_continue'])) {
                    $this->success('添加成功');
                }
            } else {
                $this->error('添加失败');
            }
        }
    }

    // 编辑操作
    public function editDo()
    {
        if (IS_POST) {
            $data = I('post.');
            if (! $data['id']) {
                return false;
            }
            if (empty($data['name'])) {
                $this->error("名称不能为空!");
            } else if (strlen($data['name']) > 63) {
                $this->error("名称长度请控制在64字内!");
            }
            if (empty($data['specification'])) {
                $this->error("品牌规格不能为空!");
            } else if (strlen($data['specification']) > 63) {
                $this->error("品牌规格长度请控制在64字内!");
            }
            if (empty($data['price'])) {
                $this->error("单价不能为空!");
            } else if (! preg_match('/^([0-9]{1,10})|([0-9]{1,10}\.[0-9]{1,3})$/', $data['price'])) {
                $this->error("单价格式不正确!");
            }
            if (empty($data['seller_day'])) {
                $data['seller_day'] = 0;
            } else if (! preg_match('/^[0-9]{1,10}$/', $data['seller_day'])) {
                $this->error("货期格式不正确!");
            } else {
                $data['seller_day'] = $data['seller_day'] . ($data['seller_day_unit'] == '2' ? 'B' : 'A');
            }
            if (empty($data['use_time'])) {
                $data['use_time'] = 0;
            } else if (! preg_match('/^[0-9]{1,10}$/', $data['use_time'])) {
                $this->error("有效期格式不正确!");
            } else {
                $data['use_time'] = $data['use_time'] . ($data['use_time_unit'] == '2' ? 'B' : 'A') . ($data['use_time_type'] == '3' ? 'C' : $data['use_time_type'] == '2' ? 'B' : 'A');
            }
            $updateData = array(
                'name' => $data['name'],
                'specification' => $data['specification'],
                'price' => $data['price'],
                'count' => $data['count'],
                'seller_day' => $data['seller_day'],
                'use_time' => $data['use_time'],
                'image' => empty($data['thumb']) ? $data['image'] : $data['thumb'],
                'detail' => $data['detail'],
                'attention' => $data['attention']
            );
            $result = M('Lpa')->where(array(
                'id' => $data['id']
            ))->save($updateData);
            if ($result !== false) {
                $this->success('编辑成功', U('Lpam/list'));
            } else {
                $this->error('添加失败');
            }
        }
    }

    // 删除操作
    public function delete()
    {
        $get = I('get.');
        if ($get['id']) {
            $id = I('get.id', 0, 'intval');
            if (! $id) {
                $this->error('参数错误');
            }
            if (M('Lpa')->where(array(
                'id' => $id
            ))->delete()) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else if ($get['ids']) {
            $ids = json_decode($get['ids']);
            $sels = json_decode($get['sels']);
            $success = 0;
            $fail = 0;
            for ($i = 0; $i < count($ids); $i ++) {
                if ($sels[$i]) {
                    if (M('Lpa')->where(array(
                        'id' => $ids[$i]
                    ))->delete()) {
                        $success ++;
                    } else {
                        $fail ++;
                    }
                }
            }
            if ($fail == 0) {
                $this->success('删除成功!',U("Lpam/list"));
            } else if ($success == 0) {
                $this->error('删除失败!');
            } else {
                $this->success("成功删除" . $success . "条数据," . $fail . "条数据删除失败!",U("Lpam/list"));
            }
        }
    }

    // 导入操作
    public function importDo()
    {
        if (IS_POST) {
            $data = I('post.');
            if (! $data['xls_path']) {
                $this->error('请先选择文件!');
            }
            $path = $data['xls_path'];
            $ct = 0;
            while (true) {
                if (file_exists($path)) {
                    break;
                }else if($ct>0){
                    $path = ".".$path;
                }else if ($ct > 1) {
                    return $this->error('读取文件信息失败!');
                }else {
                    $ct ++;
                }
            }
            // $data2 = get_excel_data_img($path);
            // if($data2){
            // echo "<table>";
            // foreach($data2 as $key=>$value){
            // echo "<tr>";
            // foreach($value as $key2=>$value2){
            // echo "<td>".$value2."</td>";
            // }
            // echo "</tr>";
            // }
            // exit("</table>");
            // }
            $data = get_excel_data_img($path); // 获取带图片的excel数据
            if ($data) {
                $_SESSION['excel_tmp'] = $data;
                return $this->success("解析成功!", U("Lpam/import", array(
                    'import' => true
                )));
            }
        }
    }

    // 导出操作
    public function exportDo()
    {}
}
