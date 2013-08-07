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
 * @todo "feature points" on lines charts like sparkline 0.2 has them
 * 
 * This class can freely be used and distributed. 
 * It is licensed using Creative Commons Attribution 2.0 Germany license found here:
 * http://creativecommons.org/licenses/by/2.0/de/deed.en
 * If you improve the class, please let me know: patrick@kwondoo.de
 *  
 * Have fun
 */
class Sparkling extends Drawing {
  
  var $data;
  var $colors;
  var $imgname;
  var $width;
  var $padding;
  var $dataN;
  var $fps;
  
  /**
   * class constructor, not needed yet
   */
  public function __construct () {
    
  }
  
  
  /**
   * creates pie chart of given data
   * @param int     $ex   excentricity (width)
   * @param array   $data   data input
   * @param array   $dnames data field names
   * @param array     $dcolors  colros of data fields
   * 
   */
  public function pieChart($data,$dnames,$colors,$addPercent=null,$width=null,$padding=null) {
    
    $this->colors  = $colors;
    $this->dataN   = $dnames;
    $this->data    = $data;
    
    if ($addPercent == "true") {
      $addPercent = true;
    } else {
      $addPercent = false;
    }
    
    if (isset($width)) {
      $this->width = $width;
    } else {
      $this->width = 200;
    }
    if (isset($padding)) {
      $this->padding = $padding;
    } else {
      $this->padding = 10;
    }
    
    
    // 100 % = how much?
    $valueSum = array_sum($this->data);
    $converted = array();
    $convertedDeg = array();
    $convertedArc = array();
    $radius = $this->width / 2;
    $center = $radius;
    
    // INIT Image
    parent::Init($this->width,$this->width,"bars");

    // whats the percentual contingent of each dataPart?
    foreach ($data as $dataPart) {
      $procentual = round($dataPart * (100/$valueSum),2);
      $converted[] = $procentual;
    }
    
    // 100% of circles' degress = 360;
    $degSum = 360;
    $degDone = 0; // used space should be skipped
    $last = 0;
    foreach ($converted as $dataPart) {
      
      $deg = $dataPart * ($degSum/100);
      $deg += $degDone;
      $fdeg = $last + (($deg - $last)/2);

      $xy = self::getXYbogen($deg,$radius,$this->width);
      $fxy = self::getXYbogen($fdeg,$radius/2,$this->width);    
      
      $fontDeg[] = $fxy;
      $convertedDeg[] = $xy;
      $degDone = $deg;
      $last = $deg;
    }
    
    parent::imgFill("white",1,1);
    parent::imgEllipse($center,$center,$this->width,$this->width,"black");
    
    foreach ($convertedDeg as $xy) {
      parent::imgLine($center,$center,$xy[0],$xy[1],"black");
    }
    
    $i = 0;
    foreach ($fontDeg as $field) {
      $fontxy = $fontDeg[$i];
      if (isset($this->colors) && isset($this->colors[$i])) {
        $c = $this->colors[$i];
        parent::imgFill($c,$fontxy[0],$fontxy[1],"black");
      }
      $i++;
    }
    $i = 0;
    foreach ($fontDeg as $field) {
      $txt = $dnames[$i];
      if ($addPercent) {
        $txt .= " (".$converted[$i]."%)";
      }
      $fontxy = $fontDeg[$i];
      $hpl = imagefontheight(2) / 2;
      $wpl = (imagefontwidth (2) * (strlen($txt)/2));
      @parent::imgString(2,$fontxy[0]-$wpl,$fontxy[1]-$hpl,$txt,"black");
      $i++;
    }
    
    if (isset($this->padding)) {
      parent::Init($this->width+$this->padding,$this->width+$this->padding,"container");
      parent::imgFill("white",1,1);
      parent::mergeHandles("container","bars",$this->padding/2,$this->padding/2);
      parent::switchHandle("container");
      $center = ($this->width+$this->padding) /2;
      parent::imgEllipse($center,$center,$this->width,$this->width,"black");
    }
  }
  
  
  /**
   * creates bar chart of given data
   * @param array   $data   data input
   * @param   int     $width    graphic width
   * @param   int     $height   graphic height
   * @param   int     $barPadding spacing between bars
   * @param array       $dcolors  colors of data fields
   * @param int     $zLine    a "zero" line
   * @param   array   $legende  caption
   */
  public function barChart($data,$width,$height,$barPadding,$dsColors,$zLine = null, $legende = null) {
    
    $this->data    = $data;
    $this->numSets = count($data);
    $this->width   = $width;
    $this->height  = $height;
    $this->barPadding = $barPadding; 
    $this->colors = $dsColors;
    $this->dsLen = count($this->data[0]);

    if (isset($legende)) {
      $this->legende = $legende;
    } else {
      unset($this->legende);
    }
    if (isset($zLine)) {
      $this->zLine = $zLine;
    }else {
      unset($this->zLine);
    }
    
    // count data to find out width of single bar
    $num = 0; // number of bars
    $maxY = 0; // highest value
    $minY = 0; // lowest value
    foreach ($this->data as $dp) {
      $num += count($dp); 
      if (count($dp) != $this->dsLen) {
        echo "Datasets sind verschieden lang!!"; // Datasets are of different length
        pre($dp);
      } 
      // search for maximum value
      $max = max($dp);
      $min = min($dp);
      if ($max > $maxY) {
        $maxY = $max; 
      }
      if ($min < $minY) {
        $minY = $min; 
      }     
    }
    $pl = abs($minY);
    $h = $maxY + $pl;
    
    // mix up Arrays to group datasets
    if ($this->numSets > 1) {
      $i = 0;
      while ($i != $this->dsLen) {
        foreach ($this->data as $dp) {
          $dsAll[] = $dp[$i];
        }
        $i++;
      }
    } else {
      $dsAll = $this->data[0];
    }
    
    // numSets to gain double padding between groups
    $groupPadding = 4*$this->barPadding;
    $padAll = ($num * $this->barPadding) + (($this->numSets-1) * $groupPadding); 
    $wBar = ($this->width - $padAll) / $num;
      
    // Init Image handle
    parent::Init($this->width+1,$this->height,"bars");
    parent::imgFill("white",1,1);

    
    $i = 0; // number overall
    $b = 0; // to arrange groups 
    $d = 0;// to pick color
    $offs = 0; // offset is needed for spacing between groups
    $yz = ($this->height) - (($this->zLine + $pl) * ($this->height/$h)); // Zero Line
    foreach ($dsAll as $p) {
      
      if ($b == $this->dsLen && $this->numSets != 1) { // next group ...
        $offs += $groupPadding; // ... -> increase offset
        $b = 0;
      }
      if ($d == $this->numSets) {
        $d = 0;
      }
        
      $x = round(($i*$wBar) + ($i*$this->barPadding) + $offs);
        
      $y = round($p * ($this->height/$h)) - $this->zLine;
    
      parent::imgRect($x,$yz,$wBar,-$y,$this->colors[$d],"filled");             

      $i++;
      $b++;
      $d++;
    }
    if (isset($this->zLine)) {
      $y = ($this->height) - (($this->zLine + $pl) * ($this->height/$h));
      parent::imgLine(0,$y,$this->width,$y,"black");
    }
    
    if (isset($this->legende)) {

      $xw = 20;
      $yh = imagefontheight(2)+2;
      
      parent::imgLine(0,$this->height-1,$this->width,$this->height-1,"black");
      
      parent::Init($this->width+1,$yh,"legendeY");
      parent::imgFill("white",1,1);
      
      $i = 1;
      foreach ($this->legende as $txt) {

        $center = $this->width / $this->numSets;
        $fw = imagefontwidth(2) * strlen($txt);
        $x = $center*$i - $fw/2 - $center/2;
        parent::imgString(2,$x,1,$txt,"black");
        $lx = ($center*$i);
        parent::imgLine($lx,0,$lx,5,"black");
        $i++;

      }
      
      $fh = imagefontheight(2);
      if ($this->height > $fh*2) {
        parent::Init($xw,$this->height+$yh,"legendeX");
        parent::imgFill("white",1,1);
        parent::imgLine($xw-1,0,$xw-1,$this->height-1,"black");
        parent::imgString(2,2,-3,$maxY,"black");
        parent::imgString(2,2,$this->height-$fh,0,"black");
      } else {
        $xw = 0;
      }
      
      
      parent::Init($this->width+1+$xw,$this->height+$yh,"container");
      parent::imgFill("white",1,1);     
      
      @parent::mergeHandles("container","legendeX",0,0);
      parent::mergeHandles("container","bars",$xw,0);
      parent::mergeHandles("container","legendeY",$xw,$this->height);
      
      parent::switchHandle("container");
    } 

  }
  
  
  /**
   * creates a line chart
   * @param array   $data   data input
   * @param   int     $width    graphic width
   * @param   int     $height   graphic height
   * @param   array   $color    lines colors
   * @param   array   $style    lines style (filled or line) 
   * @param int     $zLine    a "zero" line
   * @param   array   $legende  caption
   */
  public function lineChart($data,$width,$height,$color=null,$style= null,$zLine = null,$legende = null) {
    
    // error if datatype is wrong
    if (!is_array($data)) {
      echo 'wrong datatype for variable $data';
      return false;
    } else if (!is_numeric($width)) {
      echo 'wrong datatype for variable $width';
      return false;
    } else if (!is_numeric($height)) {
      echo 'wrong datatype for variable $height';
      return false;
    }
    
    // globalize some optional variables
    if (isset($zLine)) {
      $this->zLine = $zLine;
    } else {
      unset($this->zLine);
    }
    if (isset($legende)) {
      $this->legende = $legende;
    } else {
      unset($this->legende);
    }
    if (isset($color)) {
      $this->lColors = $color;
    } else {
      unset($this->lColors);
    }
    if (isset($style)) {
      $this->fills = $style;
    } else {
      unset($this->fills);
    }
    
    // globalize some required variables
    $pad = 8; // standard top and bottom padding
    $this->width  = $width;
    $this->height = $height;
    $this->imgh = $height + $pad; // add padding to prevent elements from being ouside the image
    $this->data = $data;
    
    $this->numLines = count($this->data); // number of graphs = number of datasets in $data
    $this->lenSet   = 0;
    $this->yMax   = 0;
    
    foreach ($this->data as $set) {
      if (count($set)>$this->lenSet) {
        $this->lenSet = count($set) -1; // find out the number of datapoints in a set
      }
      if (max($set) > $this->yMax) {
        $this->yMax = max($set); // find out the highest value
      }
      if (!isset($this->yMin)) { // cant set this one before, since a sets lowest value may be 2 insted of -2
        $this->yMin = min($set); // find out the lowest value
      } else {
        if ($this->yMin > min($set)) {
          $this->yMin = min($set);
        }
      }
    }
    if ($this->yMin < 0) { // if the range of data values goes beyond zero ...
      $pl = abs($this->yMin); // we have to determine the whole range by this
    } else {
      $pl = 0; // otherwise we start by zero (which may not be a good idea ?!)
    }
    
    $h = $this->yMax + $pl; // $h effectively is the datasets range
    $h = ($h === 0) ? 1 : $h; // (modified by zhenglei: to set range to 1 when range is zero)
    
    // initiate drawing class
    parent::Init($this->width,$this->imgh,"lines");
    parent::imgFill("white",1,1);
    
    
    $dsnum = 0;
    
    if ($this->lenSet > 0) { // (modified by zhenglei: if lenSet smaller than 0, just plot a white picture)
      foreach ($this->data as $set) { // we are on a set level
        
        $i = 0;
        unset($lx,$ly);
        foreach ($set as $dp) { // we are on single data value level
          
          // X of course is the image width divided by set length multiplied by index of current value in set
          $x = $i * ($this->width/$this->lenSet);
          
          $yp = $dp + $pl; // since we tricked in getting the whol set range, we must add the $pl we added above to the Y value
          $y = ($this->height) - ($yp * ($this->height/$h)); // ..and continue analogue to X
          
          $y += $pad/2; // applies padding
  
          if (isset($lx)) { // if we are not at the first value in the set we start drawing the line from LX / LY to X / Y        
            parent::imgLine($lx,$ly,$x,$y,$this->lColors[$dsnum]);
          }
          
          self::featPoint($x,$y,$dsnum,$i); // add feature point here if they were given
          
          $lx = $x; // saves the current values x and y ...
          $ly = $y; // since we will need it to draw from there to the next values XY 
          $i++;
        }
        
        if (isset($this->fills)) {
          $s = $this->fills[$dsnum];
          if ($s == "filled") { // fill the space beneath the graph if needed
            parent::imgFill($this->lColors[$dsnum],1,$this->height,$this->lColors[$dsnum]);
                    
          }
        }
        
        $dsnum++;
      }
    }
        
    
    // add zero line if given
    if (isset($this->zLine)) {
      $y = ($this->height) - (($this->zLine + $pl) * ($this->height/$h));
      parent::imgLine(0,$y,$this->width,$y,"black");
    }
  }

  
  /**
   * globalizes the feature points
   * @param array $fps  feature points
   * @return  boolean
   */
  public function addFPS ($fps) {
    if (is_array($fps)) {
      $this->fps = $fps;
      return true;
    } else {
      return false;
    }
  }
  
  
  /**
   * adds feature points to a line chart
   * @param float $x    x value
   * @param float $y    y value
   * @param int   $ds   current dataset
   * @param int   $i    current data
   * @param string  $cap  caption
   */
  private function featPoint($x,$y,$ds,$i) {
    
    if (!isset($this->fps)) {
      return false;
    }
    if (!isset($this->fps[$ds][$i])) {
      return false;
    }
    parent::imgEllipseFilled($x,$y,4,4,$this->fps[$ds][$i][1]);
    parent::imgString(1,$x+5,$y,$this->fps[$ds][$i][0],$this->fps[$ds][$i][1]);
  }
  
  
  /**
   * outputs the graphic either inline or if filename is given to a file
   * @param string  $filename file name 
   */
  public function output ($filename = null) {
    if (isset($filename)) {
      parent::imgRender($filename);
    } else {
      parent::imgRender();
    }
  }
  
  
  /**
   * for circle operations: returns XY values for degree+radius
   * @param float $degree
   * @param float $radius
   * @param int   $width
   * @return  array $coordinates
   */
  private function getXYbogen ($deg,$radius,$width) {
    
    $bogen = M_PI/180*$deg;
      
    $x = cos($bogen)*$radius;
    $y = sin($bogen)*$radius;

    if ($deg < 90) {
      $y = $y*-1;
    }
    if ($deg > 90 && $deg < 180) {
      $y = $y*-1;
    }
    if ($deg > 180 && $deg < 270) {
      $y = $y*-1;
    }
    if ($deg > 270) {
      $y = $y*-1;
    }
    if ($deg == 90) {
      $x = 0;
      $y = $y*-1;
    }
    if ($deg == 180) {
      $y = 0;
    }
    if ($deg == 270) {
      $x = 0;
      $y = $y*-1;
    }
    if ($deg == 360) {
      $y = 0;
    }
    
    $x = $width/2+$x;
    $y = $width/2+$y;

    return array($x,$y);
  }


}
?>