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

## 优化过程如下：
## 1.本地化google字体：	
>    * google本地化于`\wp-includes\fonts\google\`目录下;
>    * 新增对应调用css于`\wp-includes\css\google-fonts.css`;
>    * 修改wp目录`\wp-includes\script-loader.php`文件的调用字体地址为本地：`607行`；

##2.缓存化gravatar头像：
> * 主题`function.php`增加`get_avatar_cache`函数`558行`,需要在sae中建立一个名为`cache`的`Storage`其属性为`Public`；

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

##优化WP的URL在SAE的URL重写
在SAE根目录（即博客的根目录）新建一个名为：`config.yaml`的文件，代码如下：
```
handle:
  - rewrite:if (!is_file() && !is_dir() && path ~ "^/(.*)") goto "index.php/$1"
```

