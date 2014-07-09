<?php
/**   
 * 打印调用dump多个变量
 * @author cc <348578429@qq.com> 2014-4
 * @param param1 param2 param3 要打印的变量
 * @return void
 */  
function dumps(){
    $args = func_get_args();
    echo '<div style="margin:20px;border-style:solid;border-color:#F00;padding-left:20px">';
    foreach ($args as $key => $value) {
        echo '<p>param '.($key + 1).':<br>';
        dump($value);
        echo '</p><br>';
    }
    echo '</div>';
}
/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

class RUN{
    public static $init;
    public static $start;
    public static $data = array();
    //初始化
    static function  init(){
        RUN::$init = RUN::record();
        RUN::start();
    }
    
    /**   
     * 取得当前微秒数
     * @author cc <348578429@qq.com> 2014-4  
     * @return float 当前微秒数
     */  
    static function  get_microtime(){
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
    /**   
     * 记录当前内存及时间
     * @author cc <348578429@qq.com> 2014-4
     * @param  type param explain  
     * @return type result explain 
     */  
    static function  record(){
        $mem=memory_get_peak_usage();
        $time=RUN::get_microtime();
        return array('mem' => $mem,'time' => $time );
    }

    static function  start(){
        RUN::$start = RUN::record();
    }
    
    static function  stop(){
        $stop = RUN::record();
        array_push(RUN::$data,RUN::interval(RUN::$start,$stop));
        RUN::start();
    }
    /**   
     * 计算两个时间点的内存及时间信息
     * @author cc <348578429@qq.com> 2014-4
     * @param  object start 起始时间点
     * @param  object end 结束时间点
     * @return 
     *array(
     *       'mem_start' => $start['mem'],
     *       'mem_end' => $end['mem'],
     *       'mem_tl' => $mem_tl,
     *       'time_start' => $start['time'],
     *       'time_end' => $end['time'],
     *       'time_il' => $time_il
     *       ); 
     *  
     */  
    public static function interval($start,$end){
        $mem_il = $end['mem'] - $start['mem'];
        $time_il = $end['time'] - $start['time'];
        $time_il=round($time_il * 1000, 3);
        return array(
            'mem_start' => $start['mem'],
            'mem_end' => $end['mem'],
            'mem_il' => $mem_il,
            'time_start' => $start['time'],
            'time_end' => $end['time'],
            'time_il' => $time_il
            );
    }
    public static function show()
    {   
        $stop= RUN::record();
        $end = RUN::interval(RUN::$init,$stop);
        $str = '<div style="margin:20px;border-style:solid;border-color:#F00;padding-left:20px">';
        foreach (RUN::$data as $key => $value) {
            $i=$key+1;
            $str .= "<p> 时间点{$i}：<br>
            内存消耗：{$value['mem_il']}<br>
            时间消耗：{$value['time_il']}<br>
            </p>";
        }
        $str .= "<p>总内存消耗：{$end['mem_end']}<br>
        总耗时：{$end['time_il']}<br>
        初始内存：{$end['mem_start']}  额外内存消耗：{$end['mem_il']}</p>";      
        echo $str.'</div>';  

    }
   function __destruct(){
    }
}
RUN::init();
/*RUN::start();
$a=array();
for ($i=0; $i < 1000; $i++) { 
    array_push($a,1);
}
RUN::stop();
RUN::show();*/
//dumps(RUN::$data,RUN::$data,RUN::$data);
