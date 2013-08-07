<?php
/**
 * Sparkling sparkline PHP class
 * inspired by Edward Tufte (asket.net) and Sparkline Library (sparkline.org)
 * @package Sparkling 0.12
 * @author Patrick Albertini
 * @copyright Patrick Albertini
 * @version 0.12
 * @link http://www.kwondoo.de
 * @todo comments and code hints
 * 
 * This class can freely be used and distributed. 
 * It is licensed using Creative Commons Attribution 2.0 Germany license found here:
 * http://creativecommons.org/licenses/by/2.0/de/deed.en
 * If you improve the class, please let me know: patrick@kwondoo.de
 *  
 * Have fun
 */
class Drawing {
	
	function __construct () {
		
		
	}
	
	function Init ($w,$h,$hname = null) {
		
		if (isset($hname)) {
			$this->handle[$hname] = imagecreatetruecolor($w,$h);
			$this->hname = $hname;
		} else {
			$this->handle[0] = imagecreatetruecolor($w,$h);
			$this->hname = 0;
		}
		
		$this->palette = array(
			"green" => imagecolorallocate($this->handle[$this->hname],99,238,99),
			"red" => imagecolorallocate($this->handle[$this->hname],238,99,99),
			"blue" => imagecolorallocate($this->handle[$this->hname],0,119,204),
			"black" => imagecolorallocate($this->handle[$this->hname],0,0,0),
			"white" => imagecolorallocate($this->handle[$this->hname],255,255,255),
			"grey" => imagecolorallocate($this->handle[$this->hname],160,160,160),
		);	
		
		
		
	}
	
	public function getColor($name) {
		if (isset($this->palette[$name])) {
			return $this->palette[$name];
		} else {
			return false;
		}
	}
	
	public function imgFill($color,$x,$y,$border = null) {
		if (!isset($border)) {
			imagefill($this->handle[$this->hname],$x,$y,self::getColor($color));	
		} else {
		
			imagefilltoborder($this->handle[$this->hname],$x,$y,self::getColor($border),self::getColor($color));	
		}
	}
	
	public function imgEllipse ($xcenter,$ycenter,$width,$height,$color) {
		imageellipse($this->handle[$this->hname],$xcenter,$ycenter,$width,$height,self::getColor($color));
	}
	
	public function imgEllipseFilled ($xcenter,$ycenter,$width,$height,$color) {
		imagefilledellipse($this->handle[$this->hname],$xcenter,$ycenter,$width,$height,self::getColor($color));
	}
	
	public function imgLine ($x1,$y1,$x2,$y2,$color) {
		imageline($this->handle[$this->hname],$x1,$y1,$x2,$y2,self::getColor($color));
	}
	
	public function imgString ($font,$x,$y,$txt,$color){
		imagestring($this->handle[$this->hname],$font,$x,$y,$txt,self::getColor($color));
	}
	
	public function imgRect ($x,$y,$w,$h,$color,$fill=null) {
		if (isset($fill)) {
			imagefilledrectangle($this->handle[$this->hname],$x,$y,$x+$w,$y+$h,self::getColor($color));
		} else {
			imagerectangle($this->handle[$this->hname],$x,$y,$x+$w,$y+$h,self::getColor($color));
		}
	}
	
	public function imgRender ($filename = null) {
		if (isset($filename)) {
			imagepng($this->handle[$this->hname],$filename);
		} else {
			header("Content-type: image/png");
			imagepng($this->handle[$this->hname]);
		}
	}
	
	public function mergeHandles ($ha1,$ha2,$h2x,$h2y) {
		
		$w1 = imagesx($this->handle[$ha1]);
		$h1 = imagesy($this->handle[$ha1]);
		$w2 = imagesx($this->handle[$ha2]);
		$h2 = imagesy($this->handle[$ha2]);
		
		imagecopymerge($this->handle[$ha1],$this->handle[$ha2],$h2x,$h2y,0,0,$w2,$h2,100);
		
	}
	
	public function switchHandle ($name) {
		$this->hname = $name;
	}

}
?>