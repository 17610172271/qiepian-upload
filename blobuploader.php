<?php
session_start();
date_default_timezone_set("Asia/Shanghai");
//文件分片上传
class uploader{
 
    //执行上传(的name属性,保存路径-相对当前路径)
    public function upload($name,$savedir = 'uploads'){
         
        $return_arr = array('0','');
        $userID = session_id(); //用户标识
         
        if(!empty($_POST['act']) && trim($_POST['act'])=='combine'){
            //-------------合并文件
            $chunks = intval($_POST['chunks']);//总分块个数
             
            //文件后缀
            $type = substr($_POST['filename'],strripos($_POST['filename'],'.'));
             
            //保存临时文件
            $tmppath = $savedir.'/tmp'; //临时目录,
            if(!file_exists($tmppath)){ @mkdir($tmppath,0777,true); }
             
            $filenamemd5 = md5($_POST['filename']);
             
            $savedir = $savedir.'/files/'.date('Ym',time());
            if(!file_exists($savedir)){ @mkdir($savedir,0777,true);}
            $newname = date('mdH',time()).rand(10000,99999).'_'.rand(100000,999999).$type;
             
            $writer = fopen("{$savedir}/{$newname}","ab"); //合并后的文件名
            for($i=0;$i<$chunks;$i++){
                $file2read = "{$tmppath}/{$userID}_{$filenamemd5}_{$i}";
                $reader = fopen($file2read,"rb");
                fwrite($writer,fread($reader,filesize($file2read)));
                fclose($reader);
                unset($reader);
                @unlink($file2read);//删除分块临时文件

            }
            fclose($writer);
            $return_arr[0]='1';
            $return_arr[1]="{$savedir}/{$newname}";
             
        }else{
            if(empty($_FILES[$name]) || $_FILES[$name]["error"] > 0){
                return array( '0','上传失败' );
            }
         
            //-------------保存临时文件
            $chunks = intval($_POST['chunks']);//总分块个数
            $chunk  = intval($_POST['chunk']);//当前分块索引
             
            //临时目录
            $tmppath = $savedir.'/tmp'; 
            if(!file_exists($tmppath)){ @mkdir($tmppath,0777,true); }
             
            $filenamemd5 = md5($_POST['filename']);
            $tmpname = "{$userID}_{$filenamemd5}_{$chunk}";//临时文件名
            @move_uploaded_file($_FILES[$name]["tmp_name"],"{$tmppath}/{$tmpname}");
             
            $return_arr[0]='1';
            $return_arr[1]='needmore';
        }
 
        return $return_arr;
    }
}
 
 
$res = array(
    'flag'        =>false,
    'url'        =>'',//上传得到的新路径
    'info'        =>'',        
);
 
$er = new uploader();
 
$arr = $er->upload("newfile");
if(empty($arr[0])){
    $res['info'] = $arr[1];
}else{
    $pathurl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $pathurl = substr($pathurl,0,strripos($pathurl,"/"));
    $res['flag'] = true;
    $res['url'] = $pathurl.'/'.$arr[1];
    $res['info'] = "success";
}
echo json_encode($res); die();
?>