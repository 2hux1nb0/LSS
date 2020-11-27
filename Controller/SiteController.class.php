<?php
namespace Admin\Controller;
use Think\Controller;
class SiteController extends PublicController
{
    public function index()
    {
        $site = M('Site');
        $info = $site->where(array('siteid' => 1))->find();
        $this->assign('info', $info);
		$this->assign('pc', templateList('pc'));
		$this->assign('wap', templateList('wap'));
        $this->display();
    }
    public function update()
    {
        if (IS_POST) {
            $data = I('post.');
			$email = I('post.site_email');
			if ($email != "") {
				if (is_email($email) == false) {
					$this->error('邮箱格式不正确');
				}
			}
			$url = I('post.url');
			$reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
			if (!preg_match($reg, $url)) {
                $this->error('网址格式不正确');
            }
			if ($data['template_pc'] == '') {
                $data['template_pc'] = 'default';
            }
			if (!$data['template_wap'] == '') {
                $data['template_wap'] = 'default';
            }
			$data['site_jstransfer'] = htmlspecialchars_decode($data['site_jstransfer']);
			$adminid = session('admin_auth.adminid');
            $result = M('Site')->where(array('siteid' => 1))->save($data);
            if ($result !== false) {
				action_log('update_config', 'Site', 1, $adminid);
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }
    }
}