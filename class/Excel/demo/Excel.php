<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 15:30
 */

namespace app\admin\controller;
use app\admin\service\ExcelServer;
use think\Request;

class Excel extends Base
{
    public function import(){
        $data = Request::instance()->file('excel');
        $res  = ExcelServer::import($data);
        if($res){
            return $this->responseSuccess($res);
        }else{
            return $this->responseError(ExcelServer::getError());
        }
    }


    public function excel_log(){
        $data = Request::instance()->post();
        $res  = ExcelServer::excel_log($data);
        if($res){
            return $this->responseSuccess($res);
        }else{
            return $this->responseError(ExcelServer::getError());
        }
    }


    public function del(){
        $data = Request::instance()->post();
        $res  = ExcelServer::del($data);
        if($res){
            return $this->responseSuccess($res);
        }else{
            return $this->responseError(ExcelServer::getError());
        }
    }


}