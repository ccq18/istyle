<?php

require 'core/IStyle.class.php';

//默认不需配置直接使用,根据项目需要修改其配置
/*IStyle::Config(
	array(
		'TMPL_L_DELIM'	=> '{',//模板左定界符
		'TMPL_R_DELIM'	=> '}',//模板右定界符
		'POSTFIX'		=> '.html',//模板后缀文件名
		'CACHE_PATH' 	=> 'CACHE_PATH'=>dirname(__FILE__).'/cache/',//默认缓存目录
		'TMPL_PATH' 	=> 'TMPL_PATH'=>dirname(__FILE__).'/Tpl/',//模板目录
		'IS_DEBUG'		=> false,//是否是调试模式
		'BZ'			=> array('"',"'"),//属性值分隔符
		'TMPL_TAGLIB_PATH' 	=> array(),//自定义标签库路径
		'IS_LOAD_SYSTAG'=>true,//是否载入系统标签库
		'TMPL_TAGLIB'	=>'TagLib',//标签库名称
		'TMPL_PARSE_STRING'=>array('__PATH__'=>__FILE__)//模板替换变量
	));*/

/*IStyle::Config(
	array(
		'TMPL_L_DELIM'	=> '{',//模板左定界符
		'TMPL_R_DELIM'	=> '}',//模板右定界符
		'CACHE_PATH' 	=> 'CACHE_PATH'=>dirname(__FILE__).'/cache/',//默认缓存目录
		'TMPL_PATH' 	=> 'TMPL_PATH'=>dirname(__FILE__).'/Tpl/',//模板目录
	));*/
$v = new IStyle();
$path='style';
$a=array(1,2,3,4);
$v->assign('a',$a);
//函数替换
$v->display($path); 
