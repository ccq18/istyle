<?php
if(!function_exists('get_called_class')) {
	class class_tools
	{
		private static $i = 0;
		private static $fl = null;
		public static function get_called_class(){
			$bt = debug_backtrace();
			//使用call_user_func或call_user_func_array函数调用类方法，处理如下
			if (array_key_exists(3, $bt)
			&& array_key_exists('function', $bt[3])
			&& in_array($bt[3]['function'], array('call_user_func', 'call_user_func_array'))
			) {
				//如果参数是数组
				if (is_array($bt[3]['args'][0])) {
					$toret = $bt[3]['args'][0][0];
					return $toret;
				}else if(is_string($bt[3]['args'][0])) {//如果参数是字符串
					//如果是字符串且字符串中包含::符号，则认为是正确的参数类型，计算并返回类名
					if(false !== strpos($bt[3]['args'][0], '::')) {
						$toret = explode('::', $bt[3]['args'][0]);
						return $toret[0];
					}
				}
			}
			//使用正常途径调用类方法，如:A::make()
			if(self::$fl == $bt[2]['file'].$bt[2]['line']) {
				self::$i++;
			} else {
				self::$i = 0;
				self::$fl = $bt[2]['file'].$bt[2]['line'];
			}
			$lines = file($bt[2]['file']);
			preg_match_all('
				/([a-zA-Z0-9\_]+)::'.$bt[2]['function'].'/',
				$lines[$bt[2]['line']-1],
				$matches
				);
			return $matches[1][self::$i];
		}
	}
	function get_called_class()
	{
		return class_tools::get_called_class();
	}
}
 //替换定界符
function delimiter($str){
    $arr=explode('|', stripslashes($str));
    $flag=false;
    if(isset($arr[1])){
        $flag=true;
    }
    //判断风格
    if(!strpos($arr[0], ']')){
        //点的形式的代码
        $arr0=explode('.', $arr[0]);
        $arr[0]=array_shift($arr0);
        foreach ($arr0 as  $value) {
            $arr[0].="['{$value}']";
        }

    }
    if($flag){
        return '<?php $'.$arr[1].';if(empty('.$arr[0].')){echo $default;}else{echo '.$arr[0].';} ?>';
    }else{
        return '<?php echo ($'. $arr[0].');?>';
    } 
}
/**
 * IStyle模板引擎类
 * @author cc <348578429@qq.com> 2014-4
 */ 
class IStyleEngine {
	//标签列表
	public $tags=array();
	//回调函数列表
	public $callback=array();
	//保存单例对象
	public static $obj;
	/**   
	 * 返回单例对象
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  type param explain  
	 * @return type result explain 
	 */
	public static function getobj(){
		if(!isset(Taglib::$obj)){
			$class=get_called_class();
			Taglib::$obj =  new $class();
		}
		return Taglib::$obj;
	}
	/**   
	 * 执行模版编译
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  string $path 要编译的模板的绝对路径
	 * @param  string $compile_path 模板的编译的文件路径 
	 * @return type 编译后的路径 
	 */
	function compiletpl($path,$compile_path=false){
		if(!$compile_path)
			$compile_path = IStyle::$C['CACHE_PATH'].md5($path).'.php';
		//取得内容
		$str=file_get_contents($path);
		//解析模板
		$data=$this->parse($str);
		//执行回调
		foreach ($this->callback as $key => $value) {
			$data=$this->$value[0]($value[1],$value[2],$data);
		}
		//保存内容
		file_put_contents($compile_path, $data);
		return $compile_path;
	}
	/**   
	 * 执行模版编译
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  string $path 要编译的模板的路径
	 * @param  string $compile_path 模板的编译的文件路径
	 * @param  bool $flag 是否强制编译 
	 * @return string  编译后的路径 
	 */
	function compile($path,$compile_path=false,$flag=false){
		if(!$compile_path)
			$compile_path = IStyle::$C['CACHE_PATH'].md5($path).'.php';
		//是否需要编译
		//编译模式 编译文件不存在 模板有更新
		if(IStyle::$C['IS_DEBUG']||
			!file_exists($compile_path)||
			filemtime($compile_path)<filemtime($path))
	     	$flag=true;
		if($flag){
			$str=file_get_contents($path);
			$data=$this->parse($str);
			file_put_contents($compile_path, $data);
			//执行回调
			foreach ($this->callback as $key => $value) {
				$data=$this->$value[0]($value[1],$value[2],$data);
			}
		}
		return $compile_path;
		
	}
	//标签解析器
	public function parse($tpl_str){
		$compile_str = '';
		$now = 0;
		$ks = 0;
		$ojs = 0;
		$js = 0;
		while(1){
			$ks = stripos($tpl_str,'<',$now);
			//判断是否解析完毕
			if($ks === false){
				//后面部分 从上一个预定义标签结束位置开始截取
				$compile_str .= $this->delimiter(substr($tpl_str, $ojs));
				break;
			}
			//取得标签名
			$js = strpos($tpl_str,'>',$ks);;
			if($js){
				$js1 = strpos($tpl_str,' ',$ks);
				if($js1 && $js1 < $js)$js = $js1;
			}else{
				$now = $ks + 1;
				continue;
			}
			$tagname = substr($tpl_str, $ks+1,$js-1-$ks);
			$tagname = strtolower($tagname);//转为小写
			$tagname = rtrim($tagname,'/');
			//是否是需要解析的标签，若是则进行解析
			if(isset( $this->tags[$tagname])){

				//前面部分 上个标签的结束部分到当前的开始部分
				$compile_str.=$this->delimiter(substr($tpl_str, $ojs,$ks-$ojs));
				//在函数内部会重新设置$now
				$compile_str.=$this->getTag($tpl_str,$now,$ks,$tagname);
				$ojs=$now;
				$tagname='';//设置当前标签为空
			}else{
				$now=$js;
			}
		}
		return $compile_str;
		
	}
	public function getTag(&$tpl_str,&$now,&$ks,$tagname){
		//当前标签的结束位置
		$now=stripos($tpl_str,'>',$ks);
		//取得标签值部分 包括参数和标签名
		$tagparam=rtrim(substr($tpl_str,$ks+1,$now-$ks-1),' /');
		//解析标签参数		
		$param=$this->parse_param($tagparam,$tagname);
		//是否有结束标签 是则取得content部分 否则content为空
		if($this->tags[$tagname][0]){
			//定位结束标签位置
			$end1=stripos($tpl_str,$this->tags[$tagname][0],$now);
			//定位下一个开始标签
			$end2=stripos($tpl_str,'<'.$tagname,$now);
			while ($end2<>false&&($end1>$end2)) {
				$end1=stripos($tpl_str,$this->tags[$tagname][0],$end1+strlen($this->tags[$tagname][0]));	
				//定位开始标签
				$end2=stripos($tpl_str,'<'.$tagname,$end2+strlen('<'.$tagname));							
			}

			if($end1==0){
				error('找不到结束标签：',$this->tags[$tagname][0]);
			}
			$content=substr($tpl_str,$now+1,$end1-$now-1);
			$now=$end1+strlen($this->tags[$tagname][0]);
		}else{
			$now=$now+1;
			$content='';
		}
		//设置默认参数
		foreach ($this->tags[$tagname][1] as $key => $value) {
			if(!isset($param[$key])){
				$param[$key]=$value;
			}
		}
		//有设置第三个参数 则加入回掉
		if(isset($this->tags[$tagname][2])&&is_string($this->tags[$tagname][2]))$this->callback[]=array($this->tags[$tagname][2],$param,$content);
		//定义了别名则调用别名函数 第四个参数
		if(isset($this->tags[$tagname][3])){
			$fun=$this->tags[$tagname][3];
		}else{
			$fun='_'.$tagname;
		}
		//调用解析函数
		return call_user_func_array(array($this,$fun), array($param,$content));
	}
	//替换定界符
	function delimiter($str){
		static $l=false;
		static $r;
		//定界符初始化
		if(!$l){
			$l=  preg_quote(IStyle::$C['TMPL_L_DELIM']) ;
			$r= preg_quote(IStyle::$C['TMPL_R_DELIM']) ;
		}
		//函数替换
		$str=preg_replace('/'.$l.':([^'.$r.']*)'.$r.'/', '<?php echo \1;?>', $str);
		//不输出函数
		$str=preg_replace('/'.$l.'~([^'.$r.']*)'.$r.'/', '<?php  \1;?>', $str);
		//输出变量
		$str=preg_replace('/'.$l.'\$([^'.$r.']*)'.$r.'/e', "delimiter('\\1')", $str);		
		return $str;
	}
	/**   
	 * 解析属性数组 必须是 key='value'形式
	 * @author cc <348578429@qq.com> 2014-4
	 * @param  type param explain  
	 * @return type result explain 
	 */
	public function parse_param($str,$tagname){
		preg_match_all('/([\S=]+)\s*[=]\s*([\'"])([^\2]*?)\2/',$str,$match);
		$params=$this->tags[$tagname][1];
		//$params=array('a'=>'a','v'=>'v');
		foreach ($match[1] as $key => $value) {
			$k=$value;
			$v=$match[3][$key];
			if(isset($params[$k])){
				$params[$k]=$v;
			}
		}
		return $params;
	}
}