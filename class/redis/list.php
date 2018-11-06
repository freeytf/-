<?php 
// 解耦案例：
// 	队列处理订单系统和配送系统
	
// order.php:
// 	插入到mysql表中，状态设置为未处理

// 定时任务:每两分钟执行一次，把一条或者多条变为待处理		//锁的机制

// dc.php:处理待处理的




// 流量削峰案例:

// 	Redis的List类型实现秒杀

// 	设置微秒的时间戳，
	
// 	根据顺序来插入到队列里面，然后sleep几秒钟取出一条来插入到mysql中
	
// 	如果插入失败重新插入到队列中	


//接收用户的uid
$uid = $_GET['uid'];
//获取Redis里面已有的数量
$num = 10;
//如果当天人数少于10的时候则假如redis队列
if($redis->lLen($redis_name) < $num){
	$redis->rPush($redis_name,$uid,microtime());	//毫秒级别	
}else{
	// 如果人数已经达到10人，则秒杀已完成
	echo '秒杀已结束';
}



 ?>