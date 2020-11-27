<?php
return array(
    //'配置项'=>'配置值'
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => 'localhost', // 服务器地址
        'DB_NAME'   => 'xgcms', // 数据库名
        'DB_USER'   => 'xgcms', // 用户名
        'DB_PWD'    => 'xgcms666',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'xg_', // 数据库表前缀
        'SHOW_PAGE_TRACE' =>   false,  // false关闭 true开启
        'SHOW_ERROR_MSG'  =>   false,
        'URL_MODEL'             =>  1,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
        // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
        'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置
        'ERROR_MESSAGE'  =>    '发生错误！',
		
		//'TAGLIB_BUILD_IN' => 'Cx', //加载自定义标签
		//'TAGLIB_BEGIN'          =>  '{',  // 标签库标签开始标记
		//'TAGLIB_END'            =>  '}',  // 标签库标签结束标记
	
        'TAGLIB_BUILD_IN' => 'Cx,Common\Tag\My', //加载自定义标签
         );