<?php
namespace Admin\Controller;
use Think\Controller;
class DbakController extends PublicController
{
    protected $path = "./Data/Backup/";
    public function index()
    {
        $list = M()->query('SHOW TABLE STATUS');
        $list = array_map('array_change_key_case', $list);
        $this->assign('list', $list);
        $this->display();
    }
    public function export()
    {
        $name = date("Y-m-d-H-i-s", time());
        $this->Mydb();
        //连接数据库
        $sql = $this->sqlcreate();
        $sql2 = $this->sqlinsert();
        $data = $sql . $sql2;
        $result = file_put_contents($this->path . "{$name}.sql", $data);
        if ($result == false) {
            $this->error("备份失败！");
        } else {
            $this->success("备份成功！", U('Dbak/import'));
        }
    }
    public function import()
    {
        $list = $this->dir($this->path);
        $this->assign("list", $list);
        $this->display();
    }
    public function doReduction()
    {
        $time=intval(I('time'));
		if(!$time)$this->error("参数错误");
		$name=date("Y-m-d-H-i-s", $time).'.sql';
        $result = $this->reduction($this->path . $name);
        if ($result == false) {
            $this->error("还原失败！");
        } else {
            $this->success("还原成功！");
        }
    }
    protected function Mydb()
    {
        $host = C("DB_HOST");
        $user = C("DB_USER");
        $pass = C("DB_PWD");
        $dbname = C("DB_NAME");
        $m = mysql_connect($host, $user, $pass);
        mysql_select_db("{$dbname}");
        mysql_set_charset("utf8");
        return $m;
    }
    protected function sqlcreate()
    {
        $sql = '';
        $tb = $this->tblist();
        foreach ($tb as $v) {
            $rs = mysql_query("SHOW CREATE TABLE {$v}");
            $temp = mysql_fetch_row($rs);
            $sql .= "-- 表的结构：{$temp[0]} --\r\n";
            $sql .= "{$temp[1]}";
            $sql .= ";-- <xjx> --\r\n\r\n";
        }
        return $sql;
    }
    protected function tblist()
    {
        $list = array();
        $dbname = C("DB_NAME");
        $rs = mysql_query("SHOW TABLES FROM {$dbname}");
        while ($temp = mysql_fetch_row($rs)) {
            $list[] = $temp[0];
        }
        return $list;
    }
    protected function sqlinsert()
    {
        $sql = '';
        $tb = $this->tblist();
        foreach ($tb as $v) {
            $rs = mysql_query("SELECT * FROM {$v}");
            if (!mysql_num_rows($rs)) {
                //无数据返回
                continue;
            }
            $sql .= "-- 表的数据：{$v} --\r\n";
            $sql .= "INSERT INTO `{$v}` VALUES\r\n";
            while ($temp = mysql_fetch_row($rs)) {
                $sql .= '(';
                foreach ($temp as $v2) {
                    if ($v2 === null) {
                        $sql .= "NULL,";
                    } else {
                        $v2 = mysql_real_escape_string($v2);
                        $sql .= "'{$v2}',";
                    }
                }
                $sql = mb_substr($sql, 0, -1);
                $sql .= "),\r\n";
            }
            $sql = mb_substr($sql, 0, -3);
            $sql .= ";-- <xjx> --\r\n\r\n";
        }
        return $sql;
    }
    protected function dir($dir)
    {
        $files = array();
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != ".." && $file != ".") {
                    if (is_dir($dir . "/" . $file)) {
                        $files[$file] = dir($dir . "/" . $file);
                    } else {
                        $a = fopen($this->path . $file, "r");
                        $fstat = fstat($a);
                        $fstat['name'] = $file;
                        $fstat['size'] = round(filesize($this->path . $file) / 1024, 2);
                        $files[] = $fstat;
                    }
                }
            }
            closedir($handle);
            return $files;
        }
    }
    protected function reduction($filename)
    {
        $this->Mydb();
        //连接数据库
        //删除数据表
        $list = $this->tblist();
        $tb = '';
        foreach ($list as $v) {
            $tb .= "`{$v}`,";
        }
        $tb = mb_substr($tb, 0, -1);
        if ($tb) {
            $rs = mysql_query("DROP TABLE {$tb}");
            if ($rs === false) {
                return false;
            }
        }
        //执行SQL
        $str = file_get_contents($filename);
        $arr = explode('-- <xjx> --', $str);
        array_pop($arr);
        foreach ($arr as $v) {
            $rs = mysql_query($v);
            if ($rs === false) {
                return false;
            }
        }
        return true;
    }
    public function delbak()
    {
		$time=intval(I('time'));
		if(!$time)$this->error("参数错误");
		$name=date("Y-m-d-H-i-s", $time).'.sql';
		$result = @unlink($this->path . $name);
		if ($result == false) {
			$this->error("删除失败！");
		} else {
			$this->success("删除成功！");
		}
       
    }
    /**
     * 优化表
     * @param  String $tables 表名
     */
    public function optimize($tables = null)
    {
        if (IS_POST) {
            $tables = I('post.tables');
            if (empty($tables)) {
                $this->error("请指定要优化的表！");
            }
            $tables = implode('`,`', $tables);
            $list = M()->query("OPTIMIZE TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表优化完成！");
            } else {
                $this->error("数据表优化出错请重试！");
            }
        }
    }
    /**
     * 修复表
     * @param  String $tables 表名
     */
    public function repair($tables = null)
    {
        if (IS_POST) {
            $tables = I('post.tables');
            if (empty($tables)) {
                $this->error("请指定要修复的表！");
            }
            $tables = implode('`,`', $tables);
            $list = M()->query("REPAIR TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表修复完成！");
            } else {
                $this->error("数据表修复出错请重试！");
            }
        }
    }
}