<?php
// 应用公共文件
//跳转公共函数
function jumpTo($url)
{
    echo "<script type='text/javascript'>";
    echo "location.href='".$url."'";
    echo "</script>";
}

/***
*
*返回上一步
*$parma 提示信息
***/
function historyTo($parma){
	echo"<script>alert('" . $parma . "');history.go(-1);</script>";  
}
// history.go(-1);跳轉回上一頁；