<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\File;

Class Text extends Controller{

	public function upload(){

		return $this->fetch();
	}
	
	public function index(){
		vendor('Excel.PHPExcel');
	  	$data = request()->file('pic');
	  	

	  	//设置格式类型
	  	$format = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ];

        //如果没有数据；
        if(!$data->getInfo()){
            $msg = '请上传excel表格';
        }
        $dataInfo = $data->getInfo();
        //如果格式类型没有在设置的格式类型里面,就不符合要求
        if(!in_array($dataInfo['type'],$format)){
            $msg = '请上传xls,xlsx格式的表格';
        }

        //判断有没有报错信息
        if(!empty($msg)){
            self::setError([
                'message'    =>$msg,
            ]);
            return false;
        }

        /**转移文件到指定目录*/
        $path = ROOT_PATH.DS.'public'.DS.'uploads';

        //判断后缀是不是excel文件;
        $info = $data->validate(['ext' => 'xlsx,xls'])->move($path);


        //获取文件路径
        $exclePath = $info->getSaveName();

        /**获取文件名*/
        $filename = $path. DS . $exclePath;
        /**获取文件后缀*/
        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );
        /**判断文件格式*/
        if($extension == 'xlsx') {
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }else if($extension == 'xls'){
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }

        /**转为数组*/
        $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式

        /**删除表格表头*/
        array_shift($excel_array);
      	
      	$time = time();
        $i = 0;    
         foreach($excel_array as $k=>$v){
            //file需要的数据
            $file = ['file_url'  => $v[7],   //路径
                     'status'    => 1,       //状态
                     'creat_time'=> $time,   //时间
                     'type'      => 2];      //数据格式1--pic,2--mp3 
             try{
                Db::startTrans();//开启事务
                //文件id
                $file_id = Db::name('file')->insertGetId($file);          
                //music需要的数据
                $music= ['file_id'      => $file_id,    //文件id
                         'grade'        => $v[3],       //歌曲等级
                         'status'       => $v[2],       //1--猜歌名2--猜歌手
                         'type'         => 1,           //状态
                         'creat_time'   => $time,       //创建时间
                         'update_time'  => $time,       //修改时间
                         'song'         => $v[0],       //歌名
                         'singer'       => $v[1]];      //歌手

                //音乐id                 
                $music_id = Db::name('music')->insertGetId($music);

                //关卡数据                
                $checkpoint= ['checkpoint'  => $v[4],       //第几关
                              'music_id'    => $music_id];  //音乐数据
                Db::name('checkpoint')->insert($checkpoint);

                //答案数据          
                $answer = ['answer'     => $v[6],       //错误答案
                           'creat_time' => $time,       //创建时间
                           'music_id'   => $music_id,   //音乐数据
                           'right_key'  => $v[5],       //正确答案
                           'file_id'    => $file_id];   //文件路径
                Db::name('answer')->insert($answer);                            
                Db::commit();//提交    
                $i++;       //计数
             } catch(\Exception $e){
                Db::rollback();
             }                                  
       }
            echo $i;
	}
}















