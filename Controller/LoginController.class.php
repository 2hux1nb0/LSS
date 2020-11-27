<?php
namespace Admin\Controller;

use Think\Controller;
class LoginController extends Controller
{
    public function index()
    {
        $this->display();
    }
    public function doLogin()
    {
        if (IS_POST) {
            $code = I('post.code');
            $verify = new \Think\Verify();
            if (!$verify->check($code)) {
                $this->error('验证码错误');
            }
            $username = I('post.username');
            $password = I('post.password');
            $r = M('Admin')->where(array('username' => $username, 'status' => array('eq', 1)))->find();
            if (!$r) {
                $this->error('该用户名不存在或已禁用');
            }
            $password = md5(md5(trim($password)) . $r['encrypt']);
            if ($r['password'] != $password) {
                $this->error('密码不正确');
            }
			
            M('Admin')->where(array('username' => $username))->save(array('lasttime' => NOW_TIME, 'lastip' => ip2long(get_client_ip())));
            $auth = array('adminid' => $r['adminid'], 'username' => $username, 'email' => $r['email'], 'authority' => $r['authority'], 'logintime' => NOW_TIME, 'lasttime' => $r['lasttime']);
            session('admin_auth', $auth);
			//行为记录
			action_log('user_login', 'Admin', session('admin_auth.adminid'), session('admin_auth.adminid'));
            $this->success('登录成功', U('Index/index'));
        }	
    }
    public function verify()
    {
        ob_clean();
        $Verify = new \Think\Verify();
        $Verify->fontSize = 30;
        $Verify->length = 4;
        $Verify->useNoise = true;
        $Verify->entry();
    }
}