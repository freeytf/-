<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/12
 * Time: 11:00
 */
namespace redis;

class RedisConfig{

    const REDIS_CONFIG=[
        'timeout'   =>30,  //连接超时时间，redis配置文件中默认为300秒
        'db_id'     =>0, //选择的数据库。
        'persistent'=>false,//判断是否长连接
        'port'      =>6379,
        'host'      =>'127.0.0.1',
        'auth'      =>'weiju_redis',
        'expireTime'=>0//什么时候重新建立连接
    ];
}