<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends PublicController
{
	protected function _model(){
        return new \Think\Model();
    }
	
    public function summarize()
    {
       $system_info = array(
            'version' => '<a href="##" class="blue" target="_blank">某某信息有限公司</a> 1.1.1.100305版本',
            'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
            'server_os' => PHP_OS,
            'web_server' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'mysql_version' =>  $this->_mysql_version(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . '秒',
            'safe_mode' => (boolean) ini_get('safe_mode') ?  L('yes') : L('no'),
            'zlib' => function_exists('gzclose') ?  L('yes') : L('no'),
            'curl' => function_exists("curl_getinfo") ? L('yes') : L('no'),
            'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : L('no'),
			'Thinkphp' => THINK_VERSION,
			'yunxin' => php_sapi_name()
        );
        $this->assign('system_info', $system_info);
        $this->display();
    }
	
	 private function _mysql_version()
    {
        $Model = self::_model();
        $version = $Model->query("select version() as ver");
        return $version[0]['ver'];
    }

	public function updPwd()
    {
        $adminid = session('admin_auth.adminid');
        $detail = M('Admin')->where(array('adminid' => $adminid))->find();
        $this->assign('detail', $detail);
        $this->display();
    }
 
	//当前管理员修改资料,2017-05-15
	 public function updPasswd()
	 {
        if(IS_POST) {
			$data = I('post.');
			$email = I('post.email');
            if (is_email($email) == false) {
                $this->error('邮箱格式不正确');
            }
			$id = session('admin_auth.adminid');
			if($_POST['password_o'] != ''){
				$password_n = I('post.password_n');
				if (is_password($password_n) == false) {
					$this->error('密码必须在6-18个字符之间');
				}
				$r = M('admin')->where(array('id' => $id))->find();
				if($r['password'] != md5(md5(trim($data['password_o'])).$r['encrypt'])){
					$this->error('原密码错误');
				}
				if($data['password_n'] == $data['password_o']){
					$this->error('新密码与旧密码一致');
				}
				if($data['password_n'] != $data['password_r']){
					$this->error('新密码与重复密码不一致');
				}
				$encrypt = create_randomstr();
				$password=md5(md5(trim($data['password_n'])).$encrypt);
				M('admin')->where(array('id' => $id))->save(array('password' => $password ,'encrypt' => $encrypt,'email' => $email));
				session('admin_auth', null);
				$this->success('密码修改成功','Login/index');
			}else{
				M('admin')->where(array('id' => $id))->save(array('email' => $email));
				$this->success('资料修改成功');
			}	
 		}
    }
	
}