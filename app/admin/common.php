<?php
// 这是系统自动生成的公共文件
 //清理缓存公共函数

 if(!function_exists('delete_dir_file'))
 {
     /**
      * 循环删除目录和文件
      * 返回值: bool

      */
     function delete_dir_file($dir)
     {
         $result = false;
          if(is_dir($dir))
          {
                if($handle = opendir($dir))
                {
                    while (false !== ($item = readdir($handle)))
                    {
                        //判断当前目录下是否存在子目录,存在子目录时
                        if($item != '.' && $item != '..')
                        {
                            //判断子目录是否真实
                            if(is_dir($dir . '\\'.$item))
                            {
                                delete_dir_file($dir . '\\'.$item);
                            }else{
                                //unlink() 函数删除文件。
                                unlink($dir. '\\'. $item);
                            }
                           
                        }
                    }
                    //关闭目录句柄
                    closedir($handle);
                    //删除空目录
                    if(rmdir($dir))
                    {
                        $result = true;
                    }
                }
                
          }
          return $result;
     }
 }


  