<?php
defined('ISTYLE_CACHE_PATH') or define('ISTYLE_CACHE_PATH',dirname(__FILE__).'/Cache/');
defined('ISTYLE_TMPL_PATH') or define('ISTYLE_TMPL_PATH',dirname(__FILE__).'/Tpl/');
/**
 * 执行模版引擎类
 * @author cc <348578429@qq.com> 2014-4
 */ 
class IStyle{
	//保存模板中的配置
	public static $C=array(
		'TMPL_L_DELIM'	=> '{',//模板左定界符
		'TMPL_R_DELIM'	=> '}',//模板右定界符
		'POSTFIX'		=> '.html',//模板后缀文件名
		'CACHE_PATH' 	=> ISTYLE_CACHE_PATH,//默认缓存目录
		'TMPL_PATH' 	=> ISTYLE_TMPL_PATH,//模板目录
		'IS_DEBUG'		=> false,//是否是调试模式
		'BZ'			=> array('"',"'"),//属性值分隔符
		'TMPL_TAGLIB_PATH' 	=> array(),//自定义标签库路径
		'IS_LOAD_SYSTAG'=>true,//是否载入系统标签库
		'TMPL_TAGLIB'	=>'TagLib',//标签库名称
		'TMPL_PARSE_STRING'=>array()//模板替换变量
		//'TMPL_PARSE_STRING'=>array('__PATH__'=>__FILE__)
	);
	/**   
	 * 设置或取得模板配置
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  array arr 要设置的参数 为空则返回所有配置  
	 * @return type result explain 
	 */  
	public static function Config($arr=false){
		if($arr==false){
			return IStyle::$C;
		}else{
			IStyle::$C=array_merge(IStyle::$C,$arr);
		}
		
	}
	/*保存assign变量*/
	public $_tpl_data=array();
	/**   
	 * 将变量传入到模板中		
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  string key 变量名 去掉$
	 * @param  string value 变量值 
	 * @return type result explain 
	 */  
	function assign($key,$value=false){
		$this->_tpl_data[$key]=$value;
		return $this;//返回自身引用，可以连贯操作
	}
	/**   
	 * 根据变量名取得模板中已设置的变量		
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  string key 变量名 去掉$ 为空则返回所有已设置的变量
	 * @return 变量值
	 */  
	function get($key=false){
		if($key==false){
			return $this->_tpl_data;
		}else{
			return $this->_tpl_data[$key];
		}
		
	}
	/**   
	 * 根据变量名判断模板中的变量是否已经设置	
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  string key 变量名 去掉$
	 * @return bool 模板中的变量是否已经设置
	 */  
	function is_set($key){
		return isset($this->_tpl_data[$key]);
	}
	/**   
	 * 显示模板
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  path 相对于模板目录的路径
	 * @param  echo 是否输出
	 * @return type result explain 
	 */  
	function display($style,$echo=true){
		//引入变量到模版中
		foreach ($this->_tpl_data as $key => $value) {
			$$key=&$this->_tpl_data[$key];
		}
        ob_start();//开启缓冲区
		//引入编译后的模版，执行它
		require(IStyle::cache_path(IStyle::$C['TMPL_PATH'].$style.IStyle::$C['POSTFIX']));
        $__tpl_str=ob_get_contents ();//取得缓冲区内容
        ob_end_clean();//删除缓冲区并关闭//若不清空会在请求结束时输出
        //执行模版替换
        foreach (IStyle::$C['TMPL_PARSE_STRING'] as $key => $value) {
        	$__tpl_str = str_replace($key, $value, $__tpl_str);
        }
        if($echo){
        	echo $__tpl_str;
        	return $this;//返回自身引用，可以连贯操作
        }else{
        	return $__tpl_str;
        }
	}

	/**   
	 * 取得编译后的缓存文件路径
	 * @author cc <348578429@qq.com> 2014-4 
	 * @return string 编译后的缓存文件路径
	 */  
	static function cache_path($path,$compile_path=false){
		//是否编译;
	    //取得编译路径
	    if(!$compile_path)
			$compile_path = IStyle::$C['CACHE_PATH'].md5($path).'.php';
	   	//模板是否存在
	    if(!file_exists($path)){
	        exit('不存在的模版：'.$path);
	    }
	    //调试模式 或 编译缓存不存在 或 模板有更新 则执行编译
	    if(IStyle::$C['IS_DEBUG']||!file_exists($compile_path)||filemtime($compile_path)<filemtime($path)){
	    	if(!is_dir(dirname($compile_path))){
	    		/*exit('不存在的编译路径:'.dirname($compile_path));*/
	    		mkdir(dirname($compile_path),777,true);//目录不存在则创建目录
	    	}
	        static $taglib = false;//保存标签库名称
	        //载入标签库
	        if($taglib == false){
	        	//载入模板引擎
	        	require 'IStyleEngine.class.php';
	            $taglib = IStyle::$C['TMPL_TAGLIB'];
	            //载入自定义标签库
	            foreach (IStyle::$C['TMPL_TAGLIB_PATH'] as  $v) {
	            	if(is_file($v)){
	            		require_once($v);
	            	}else{
	            		exit( '不存在的标签库：'.$v.' 请检查TMPL_TAGLIB_PATH设置是否有误');
	            	}
	            }
	            if(IStyle::$C['IS_LOAD_SYSTAG']){
	            	require_once('TagLib.class.php');
	            }
	        }  
	        //取得模版引擎对象
	        $view=call_user_func_array( $taglib.'::getobj',array());
	        //执行编译
	        $view -> compiletpl($path,$compile_path); 
	    }
	    return $compile_path;
	}
}