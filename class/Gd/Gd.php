<?php 
/**
 * @author    [袁天赋]
 * @version   [1.0，2018/11/07 16:27]
 * @see       [相关文件，功能类，函数]
 * @deprecated
 */

class Gd{

	private $image;

	private $info;

	//创建画布资源
	private function open_resource($src){
		$this->info = getimagesize($src);	//打开图片资源
		$type = image_type_to_extension($info[2],false);//判断图片类型
		$fun = "imagecreatefrom{$type}";
		$this->image = $fun($src);	//创建画布资源
		
	} 

	/**
	 * * 给图片添加文字
	 * @param [type]  $src      [图片路径]
	 * @param string  $content  [文字内容]
	 * @param [type]  $font     [字体路径]
	 * @param integer $rgb1     [字体颜色rgb]
	 * @param integer $rgb2     [字体颜色rgb]
	 * @param integer $rgb3     [字体颜色rgb]
	 * @param integer $luncency [透明度]
	 * @param integer $size     [字体大小]
	 * @param integer $angle    [旋转角度]
	 * @param integer $x        [x偏移量]
	 * @param integer $y        [y偏移量]
	 * @param string  $name     [生成图片的名字]
	 * @param string  $type     [图片格式]
	 * @param boolean $delete   [是否删除原图]
	 */
	public static function add_test($src,$content = '',$font,$rgb1 = 255,$rgb2 = 255,$rgb3 = 255,$luncency = 0,$size = 30,$angle = 0,$x = 0,$y = 0,$name = '',$type = 'jpeg',$delete = false){
		if( empty($src) or empty($font) or $content == ''){
			return '缺少参数';
		}
		$this->image = $this->open_resource($src);
		$color = iamgecolorallocatealpha($this->image,$rgb1,$rgb2,$rgb3,$luncency);//字体颜色
		imagettftext($this->image,$size,$angle,$x,$y,$color,$font,$content);
		$this->save($src,$name,$type,$delete);
	}

	/**
	 * * 给图片添加水印图
	 * @param [type]  $src      [图片路径]
	 * @param string  $pic_src  [水印图路径]
	 * @param [type]  $b_x      [底图x轴偏移量]
	 * @param integer $b_y      [底图y轴偏移量]
	 * @param integer $w_x      [水印y轴偏移量]
	 * @param integer $w_y      [水印y轴偏移量]
	 * @param integer $w 		[拷贝的宽]
	 * @param integer $h      	[拷贝的高]
	 * @param integer $luncency [透明度]
	 * @param string  $name     [生成图片的名字]
	 * @param string  $type     [图片格式]
	 * @param boolean $delete   [是否删除原图]
	 */
	public static function add_picture($src,$pic_src,$b_x = 0,$b_y = 0,$w_x = 0,$w_y = 0,$w = 0,$h = 0,$luncency = 0,$name = '',$type = 'jpeg',$delete = false){
		if(	empty($src) or empty($pic_src) ){
			return '缺少参数';
		}
		$this->image = $this->open_resource($src); 	//底图资源
		$water = $this->open_resource($pic_src);	//水印资源
		imagecopymerge($this->image,$water,$b_x,$b_y,$w_x,$w_y,$w,$h,$luncency);//合并图片
		imagedestroy($water);
		$this->save($src,$name,$type,$delete);
	}

	/**
	 * * 缩略图
	 * @param  [type]  $src    [图片路径]
	 * @param  integer $dst_w  [图片宽]
	 * @param  integer $dst_h  [图片高]
	 * @param  string  $name   [图片名]
	 * @param  string  $type   [图片格式]
	 * @param  boolean $delete [是否删除原图]
	 */
	public static function thumbnail($src,$dst_w = 150,$dst_h = 150,$name = '',$type = 'jpeg',$delete = false){
		if( empty($src) ) return '缺少参数';
		$this->image = $this->open_resource($src);
		list($src_w,$src_h) = $this->info;
		$ratio_orig = $src_w / $src_h;
		if($dst_w / $dst_h > $ratio_orig){
			$dst_w = $dst_h * $ratio_orig;
		}else{
			$dst_w = $dst_h / $ratio_orig;
		}
		$dst_image = imagecreatetruecolor($dst_w,$dst_h);
		imagecopyresampled($dst_image,$this->image,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);
		$this->save($src,$name,$type,$delete);
	}

	/**
	 * * 显示或者保存图片
	 * @param  [type]  $src    [原图路径]
	 * @param  string  $name   [新图片名字]
	 * @param  string  $type   [图片格式]
	 * @param  boolean $delete [是否删除原图片]
	 */
	private function save($src,$name = '',$type = 'jpeg',$delete = false){
		$fun = "image{$type}";
		if($name == ''){
			header('Content-type:image/'.$type);//声明图片格式
			ob_end_clean();	//清楚缓存
			$fun($this->image);
		}else{
			$fun($this->image,$name);
		}
		imagedestroy($this->image);
		if($delete)	unlink($src);
	}


}

?>