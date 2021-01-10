<?php
// 这是系统自动生成的公共文件(


if (!function_exists('test'))
{
    function test()
    {
        return 66;
    }
}

function GetTime($time)
{ 
	$oktime=time(); 
	if ($oktime-$time<60) 
	
	{ 
	return "刚刚"; 
	} 
	
	if (($oktime-$time>=60) && ($oktime-$time<3600) ) 
	
	{ 
	$a=trim(ceil(($oktime-$time)/60)); 
	return $a.'分钟前'; 
	} 
	if (($oktime-$time>=3600) && ($oktime-$time<86400) ) 
	
	{ 
	$a=trim(ceil(($oktime-$time)/3600)); 
	return $a.'小时前'; 
	} 
	if (($oktime-$time>=86400) && ($oktime-$time<864000)) 
	
	{ 
	$a=trim(ceil(($oktime-$time)/86400)); 
	return $a."天前"; 
	} 
	
	if ($oktime-$time>=864000 ) 
	{ 
	return Date("Y-m-d",$time); 
	} 
}