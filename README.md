# kber    WordPress SAE优化项目
![kb](http://zone.wooyun.org/upload/avatar/avatar_3839.jpg "kb")
## 使用教程
> * 下载Github的包，然后修改`sae_app_wizard.xml`文件的`<service name="kber" params="public">Storage</service>`的name值，值为你在sae所创建Storage的名称。
> * 修改`\wp-includes\sae.php`文件中的`define('SAE_STORAGE',kber);`中的kber替换成你在sae所创建Storage的名称。然后将`define('SAE_URL','http://kber-kber.stor.sinaapp.com/uploads');`的kber替换成你在sae所创建Storage的名称。如你创建的Storage的名称为AA：则修改成：
```
<?php
	/* 在SAE的Storage中新建的Domain名，比如“wordpress” */
	define('SAE_STORAGE',AA);
	/* 设置文件上传的路径和文件路径的URL，不要更改 */
	define('SAE_DIR', 'saestor://'.SAE_STORAGE.'/uploads');
	define('SAE_URL','http://AA-AA.stor.sinaapp.com/uploads');
?>
```
> * 修改gravatar头像缓存的storage名->如优化过程的第二步。
> * 最后一步：
在后台将wp的地址改成域名，然后在sae代码编辑增加下面2行即可。
```
	- rewrite: if(in_header["host"] ~ "^www.kber.org" && path ~ "^(.*)$") goto "http://kber.org$1 [L,QSA,R=301]"   
	- rewrite: if(in_header["host"] ~ "^kber.sinaapp.com" && path ~ "^(.*)$") goto "http://kber.org$1 [L,QSA,R=301]" 
```
## 优化过程如下：
## 1.本地化google字体：	
>    * google本地化于`\wp-includes\fonts\google\`目录下;
>    * 新增对应调用css于`\wp-includes\css\google-fonts.css`;
>    * 修改wp目录`\wp-includes\script-loader.php`文件的调用字体地址为本地：`607行`；
>
## 2.缓存化gravatar头像：
> * 主题`function.php`底部增加`get_avatar_cache`函数`大概558行左右`,代码如下：
```
/*  
* avatar头像缓存
* Gravatar Cache To SAE Storage  
*/  
function get_avatar_cache($avatar){   
if($_SERVER['PHP_SELF'] == '/wp-admin/options-discussion.php')   
return $avatar;   
$s = new SaeStorage();   
$tmp = strpos($avatar, 'avatar/') + 7;   
$avatar_id = substr($avatar, $tmp, strpos($avatar, '?') - $tmp);   
$tmp = strpos($avatar, 'avatar/') + 7;   
$pattern = "/(<img.* src=')([^']*)('.*)/";   
$avatar_url = preg_replace($pattern, "$2", $avatar);   
$avatar_url = str_replace("&", "&", $avatar_url);   
$avatar_file = 'avatars/' . $avatar_id . '.png';   
//echo '<!--' . $avatar_url . '-->   
//                <!--' . $s->getUrl('wordpress', $avatar_file) . '-->';   
if(!$s->fileExists('kber', $avatar_file)){   
$content = @file_get_contents($avatar_url);   
if(!$content)   
return $avatar;   
$attr = array('expires' => 'now plus 14 day');   
$result = $s->write('kber', $avatar_file, $content, -1, $attr);   
if ($result != true)   
var_dump($s->errno(), $s->errmsg());   
}   
$avatar = preg_replace($pattern, "$1" . $s->getUrl('kber', $avatar_file) . "$3", $avatar);   
return $avatar;   
}   
add_filter('get_avatar', 'get_avatar_cache');
```
将18行、23行、27行的kber替换成在sae中建立的`Storage`的名称即可；
>
##3.兼容SAE修改处：
> * 由于SAE中Wordpress无法上传图片，需要做下面修改，首先新建`sae_app_wizard.xml`的文件,代码如下：
```
<?xml version="1.0"?>
	<appwizard>
		<introduction>
			<name>WordPress 4.1 for SAE</name>
			<author>KB.compaq.lee</author>
			<homepage>http://www.kber.org</homepage>
			<description>
				<![CDATA[
				<p>基于官方原版 WordPress 4.1 定制，适用于新浪云计算。</p>
				]]>
			</description>
		</introduction>
		<platform>
			<services>
				<!‐‐ 例：初始化一个域名为:kber的Storage，域属性为“public”，更多参数配置请参考Storage的API文档 ‐‐>
				<service name="kber" params="public">Storage</service>
				<!‐‐ 例：初始化Mysql ‐‐>
				<service>Mysql</service>
			</services>
		</platform>
		<code>
			<!‐‐ 初始化页面地址：即应用安装成功后跳转的地址，可以将应用初始化脚本或数据库导入脚本写在该文件中 ‐‐>
			<initScript>wp-admin/install.php</initScript>
		</code>
</appwizard>
```
> * 新建`\wp-includes\sae.php`文件代码为：
```
<?php
	/* 在SAE的Storage中新建的Domain名，比如“wordpress” */
	define('SAE_STORAGE',kber);
	/* 设置文件上传的路径和文件路径的URL，不要更改 */
	define('SAE_DIR', 'saestor://'.SAE_STORAGE.'/uploads');
	define('SAE_URL','http://kber-kber.stor.sinaapp.com/uploads');
?>
```
> * 修改`wp-includes\functions.php`文件在
`require( ABSPATH . WPINC . '/option.php' );`附近新增一行（`第9行`），代码如下：
`include( ABSPATH . WPINC . '/sae.php' );  //调用SAE的Storage Domain设置`
> * 将`wp-includes\functions.php`中下面的代码进行替换（`第1470行`）
```
$wrapper = null;
//strip the protocol
if( wp_is_stream( $target ) ) {
	list( $wrapper, $target ) = explode( '://', $target, 2 );
}
//from php.net/mkdir user contributed notes
$target = str_replace( '//', '/', $target );
//put the wrapper back on the target
if( $wrapper !== null ) {
	$target = $wrapper . '://' . $target;
}
```
> * 替换成下面的代码：
```
if ( substr($target, 0, 10) == 'saestor://' ) {
	return true;
}
$target = str_replace( '//', '/', $target );
```
> * 在第1785行
`$basedir = $dir;
$baseurl = $url;
`的上方增加2行代码如下
`$dir = SAE_DIR;
$url = SAE_URL;`
> * 在"Send a HTTP header to limit rendering of pages to same origin iframes"注释行的上方（`第4412行`）增加函数
>
```
if ( !function_exists('utf8_encode') ) {
	function utf8_encode($str) {
		$encoding_in = mb_detect_encoding($str);
		return mb_convert_encoding($str, 'UTF-8', $encoding_in);
	}
}
```
> * 修改`\wp-admin\includes\file.php` 注释`345~349行`代码如下；
```
// Set correct file permissions.
/*
$stat = stat( dirname( $new_file ));
$perms = $stat['mode'] & 0000666;
@ chmod( $new_file, $perms );
*/
```
>
## 4.优化WP的URL在SAE的URL重写
在SAE根目录（即博客的根目录）新建一个名为：`config.yaml`的文件，代码如下：
```
handle:
	- rewrite:if (!is_file() && !is_dir() && path ~ "^/(.*)") goto "index.php/$1"
	- rewrite: if(in_header["host"] ~ "^www.kber.org" && path ~ "^(.*)$") goto "http://kber.org$1 [L,QSA,R=301]"   
	- rewrite: if(in_header["host"] ~ "^kber.sinaapp.com" && path ~ "^(.*)$") goto "http://kber.org$1 [L,QSA,R=301]" 
```
第一个是wp内部设置url调整URL格式时，将重写到index.php(类似nginx优化wp的重写。)
第二个是将裸域名www.kber.org全部301到kber.org下，这样有利于搜索引擎对权重的集中
第三个是将SAE默认的二级域名kber.sinaapp.com，301到kber.org，避免权重的分散。
