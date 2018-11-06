<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 15:30
 */

namespace app\admin\service;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use think\Db;
use think\Exception;
use think\Session;
class ExcelServer extends BaseService
{
    public static function import($data){
        /**引入excel类文件*/
        vendor("Classes.PHPExcel");
        $format = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ];
        if(!$data->getInfo()){
            $msg = '请上传excel表格';
        }
        $dataInfo = $data->getInfo();
        if(!in_array($dataInfo['type'],$format)){
            $msg = '请上传xls,xlsx格式的表格';
        }

        if(!empty($msg)){
            self::setError([
                'message'    =>$msg,
            ]);
            return false;
        }
        /**转移文件到指定目录*/
        $path = ROOT_PATH.DS.'public'.DS.'uploads';
        $info = $data->validate(['ext' => 'xlsx,xls'])->move($path);

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
        $res = [];
        /**重新赋值*/
        $time = time();
        $add_num  = date('YmdHis');
        //var_dump($excel_array);die;

        foreach ($excel_array as $k=>$v){
            $res[$k]['title']       = $v[0];
            $res[$k]['content']      = $v[1];
            $res[$k]['answer']   = (int)$v[2];
            $res[$k]['difficulty']  = (int)$v[3];
            $res[$k]['show']        = (int)$v[4];
            $res[$k]['create_time'] = $time;
            $res[$k]['add_num']     = $add_num;
            if(empty($v[4])){
                $res[$k]['show'] = 1;
            }
            if(empty($res[$k]['title'])){
                unset($res[$k]);
            }

        }
        foreach ($res as $k=>$v){
            Db::name('question_n')->insert($v);
        }
        $log = Db::name('question_log')->insert([
            'add_num'=>$add_num,
            'create_time'=>$time,
            'num'       =>Db::name('question_n')->where(['add_num'=>$add_num])->count(),
            'path'      =>$filename,
        ]);
        if($log){
            return true;
        }else{
            self::setError(['message'=>'服务器错误']);
            return false;
        }
    }


    /**导入日志
     * @param $data
     * @return bool
     */
    public static function excel_log($data){
        $page = self::page($data);
        $list['data'] = Db::name('question_log')
            ->limit($page['page'],$page['limit'])
            ->order('create_time desc')
            ->select();
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        $list['count'] = Db::name('question_log')
            ->count();
        if(empty($list['data'])){
            self::setError([
                'status_code'=>500,
                'message'    =>'没有更多数据了',
            ]);
            return false;
        }

        return $list;
    }


    public static function excel_list($data){
        $page = self::page($data);
        $list['data'] = Db::name('excel')
            ->where('add_num',$data['add_num'])
            ->limit($page['limit'],$page['page_count'])
            ->select();
        $list['count'] = Db::name('excel')
            ->where('add_num',$data['add_num'])
            ->limit($page['limit'],$page['page_count'])
            ->count();
        if(empty($list['data'])){
            self::setError([
                'status_code'=>500,
                'message'    =>'没有更多数据了',
            ]);
            return false;
        }
        return $list;

    }


    public static function del($data){
        $id = $data['id'];
        $add_num = Db::name('question_log')->where(['id'=>$id])->find();
        $map=[
            'add_num'=>$add_num['add_num']
        ];
        $excel = Db::name('question_n')->where($map)->count();
        if($excel <= 0){
            $msg = '请输入正确上传表格编号';
        }
        if(!empty($msg)){
            self::setError([
                'message'    =>$msg,
            ]);
            return false;
        }
        if(!empty($add_num['path'])){
            unlink($add_num['path']);
        }
        $delLog = Db::name('question_log')->where($map)->delete();
        $delExcel = Db::name('question_n')->where($map)->delete();

        if($delExcel && $delLog){
            return true;
        }else{
            self::setError([
                'status_code'=>500,
                'message'    =>'服务器忙',
            ]);
            return false;
        }
    }
}