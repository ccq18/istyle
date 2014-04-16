<?php
/**
 * 基础标签库
 * @author cc <348578429@qq.com> 2014-4
 */ 
class TagLib extends IStyleEngine{
	//扩展标签库
	//标签库列表
	//结束标签名 参数列表 array(键=〉默认值)
	public $tags=array(
		'volist'=>array(
			'</volist>',
			array('id' 		=> 'id',
				'name' 		=> 'name',
				'key'		=>'key')
			),
		'if'=>array(
			'</if>',
			array('id'		=>'id',
				'name'		=>'name',
				'key'		=>'key')
			),
		'php'=>array(
			'</php>',
			array()
			),
		'extend'=>array(
			false,//无结束标签
			array('name'	=>'data'),
			'call_extend',//回调函数，所有标签结束时回调
			),
		'block'=>array(
			"</block>",//结束标签
			array('name'	=>'data'),
			),
		'foreach'=>array(
			"</foreach>",//结束标签
			array('name'	=>'data',
				'item'		=>'vo',
				'key'		=>'key'),
			),
		'else'=>array(
			false,//结束标签
			array(),
			),
		'present'=>array(
			"</present>",// 结束标签
			array('name'		=>'name'),
			),		
		);

	
	//实例化
	public function __construct() {
		//合并系统标签库和扩展标签库
		//$this->tags=array_merge($this->tags,$this->mytags);
	}
	
	
	//标签函数 参数列表 内容
	function _volist($param,$content){
		//dump($param);
		$content=$this->parse($content);
		return '<?php  if(is_array($'.$param['name'].'))foreach ($'.$param['name'].' as $'.$param['key'].' => $'.$param['id'].') { ?>'.$content.'<?php } ?>';
	}
	function _php($param,$content){
		return '<?php   '.$content.' ;?>';
	}
	function _extend($param,$content){
		return '';
	}
	//extend的回调函数
	function call_extend($param,$content,$data){
		$now=explode(':', $param['name']);
		switch (count($now)) {
			case 0:
				$now=array(C('DEFAULT_MODULE'),C('DEFAULT_ACTION'));
				break;
			case 1:
				array_unshift($now,C('DEFAULT_MODULE'));
				break;
			case 2:
				break;
			default :
				return $data;
				break;
		}
		$path=THEME_PATH.implode('/', $now).C('TMPL_TEMPLATE_SUFFIX');
		//dump($path);
		$path=$this->compile($path);

		return file_get_contents($path);
	}
	function _block($param,$content){
		static $block=array();
		//内容为空则调用之前已经缓存中的内容
		if($content==''){

			if(isset($block[$param['name']])){

				$content= $this->parse($block[$param['name']]);
			}
			
		}else{
			$block[$param['name']]=$content;
			$content='';
		}

		return $content;
		
		
	}
	//array('name'=>'data','item'=>'vo','key'=>'key'),
	function _foreach($param,$content){
		//dump($param);
		$content=$this->parse($content);
		return '<?php  if(is_array($'.$param['name'].'))foreach ($'.$param['name'].' as $'.$param['key'].' => $'.$param['item'].') { ?>'.$content.'<?php } ?>';
	}
	function notdo($param,$content){
		return '<!--'.$content.'-->';
	}
	function _else($param,$content){
		return '<?php }else{ ?>';
	}
	function _present($param,$content){
		$content=$this->parse($content);
		return '<?php if(isset($'.$param['name'].')){ ?>'.$content.'<?php } ?>';
	}
	

}