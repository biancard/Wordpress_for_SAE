<?php
/* 在SAE的Storage中新建的Domain名，比如“wordpress” */
define('SAE_STORAGE',kber);
/* 设置文件上传的路径和文件路径的URL，不要更改 */
define('SAE_DIR', 'saestor://'.SAE_STORAGE.'/uploads');
define('SAE_URL', 'http://kber-kber.stor.sinaapp.com/uploads');
?>