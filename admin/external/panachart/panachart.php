<?php
/*
    PanaChart - PHP Chart Generator -  October 2003

    Copyright (C) 2003 Eugen Fernea - eugenf@panacode.com
    Panacode Software - info@panacode.com
    http://www.panacode.com/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation;

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

define('HORIZONTAL', 0);
define('VERTICAL', 1);

define('SOLID', 0);
define('DASHED', 1);
define('DOTTED', 2);
define('MEDIUM_SOLID', 3);
define('MEDIUM_DASHED', 4);
define('MEDIUM_DOTTED', 5);
define('LARGE_SOLID', 6);
define('LARGE_DASHED', 7);
define('LARGE_DOTTED', 8);
define('MAX_MIN', 0.0000001);

class series{
        var $m_values, $m_seriesTitle, $m_strokeColor, $m_fillColor;
        var $m_chart, $m_type;

        function series(&$chart, $chartType, &$values, $title, $style, $strokeColor, $fillColor){
                $this->m_chart = &$chart;
                $this->m_type = $chartType;
                $this->m_style = (int)$style;
                $this->m_seriesTitle = $title;
                $this->m_values = &$values;
                $vStrokeColor = _decode_color($strokeColor);
                $vFillColor= _decode_color($fillColor);

                $this->m_strokeColor = imagecolorallocate ($this->m_chart->m_image, $vStrokeColor[0], $vStrokeColor[1], $vStrokeColor[2]);
                $this->m_fillColor = imagecolorallocate ($this->m_chart->m_image, $vFillColor[0], $vFillColor[1], $vFillColor[2]);
        }
}

class chart{
        var $m_title, $m_width, $m_height;
        var $m_strokeColor, $m_backgroundColor, $m_fillColor, $m_fontColor, $m_fontWidth, $m_fontHeight;
        var $m_maxFontWidth, $m_maxFontHeight;
        var $m_minValue, $m_maxValue;
        var $m_minCount, $m_maxCount;
        var $m_image, $m_series;
        var $m_labels, $m_labelsTextColor, $m_labelsFont, $m_labelsFontWidth , $m_labelsFontHeight, $m_labelsDirection;
        var $m_gridHColor, $m_gridVColor, $m_showHGrid, $m_showVGrid, $m_showXAxis, $m_showYAxis;
        var $m_numberOfDecimals, $m_thousandsSeparator, $m_decimalSeparator;
        var $m_style;
        var $m_withLegend, $m_legendStyle, $m_legendStroke, $m_legendFill, $m_legendFont;

        // Chart constructor
        function chart($width, $height, $margin, $backgroundColor){
                $this->m_title = "";
                $this->m_width = $width;
                $this->m_height = $height;
                $this->m_image = imagecreate ($this->m_width, $this->m_height);
                $this->m_margin = $margin;
                $vBackColor = _decode_color($backgroundColor);
                $this->m_backgroundColor = imagecolorallocate ($this->m_image, $vBackColor[0], $vBackColor[1], $vBackColor[2]);

                $this->m_minValue = false;
                $this->m_maxValue = 0;
                $this->m_style = SOLID;
                $this->m_strokeColor = $this->m_backgroundColor;
                $this->m_fillColor = $this->m_backgroundColor;

                $this->m_showHGrid = false;
                $this->m_showVGrid = false;

                $this->m_numberOfDecimals = 0;
                $this->m_thousandsSeparator = ',';
                $this->m_decimalSeparator = '.';

                $this->m_withLegend = false;
        }

        // Set number display format
        function setFormat($numberOfDecimals, $thousandsSeparator, $decimalSeparator){
                $this->m_numberOfDecimals = $numberOfDecimals;
                $this->m_thousandsSeparator = $thousandsSeparator;
                $this->m_decimalSeparator = $decimalSeparator;
        }

        function setLegend($position, $borderStyle, $borderColor, $fillColor, $font){
                //$this->m_legendPosition
                $this->m_legendStyle = $style;

                $vStrokeColor = _decode_color($strokeColor);
                $this->m_legendStroke = imagecolorallocate ($this->m_image, $vStrokeColor[0], $vStrokeColor[1], $vStrokeColor[2]);

                $vFillColor= _decode_color($fillColor);
                $this->m_legendFill = imagecolorallocate ($this->m_image, $vFillColor[0], $vFillColor[1], $vFillColor[2]);

                $this->m_legendFont = $font;
                $this->m_withLegend = true;
        }

        function setTitle($title, $textColor, $font){
                $this->m_title = $title;
                $vTextColor= _decode_color($textColor);
                $this->m_textColor = imagecolorallocate ($this->m_image, $vTextColor[0], $vTextColor[1], $vTextColor[2]);

                $this->m_font = $font;
                $this->m_fontWidth = imagefontwidth($font);
                $this->m_fontHeight = imagefontheight($font);
        }

        function setPlotArea($style, $strokeColor, $fillColor){
                $this->m_style = $style;
                if($strokeColor){
                        $vStrokeColor = _decode_color($strokeColor);
                        $this->m_strokeColor = imagecolorallocate ($this->m_image, $vStrokeColor[0], $vStrokeColor[1], $vStrokeColor[2]);
                }
                if($fillColor){
                        $vFillColor= _decode_color($fillColor);
                        $this->m_fillColor = imagecolorallocate ($this->m_image, $vFillColor[0], $vFillColor[1], $vFillColor[2]);
                }

        }

        function setXAxis($color, $style, $font, $title){
                if(strlen($color) > 0){
                        $this->m_showXAxis = true;
                        $vColor = _decode_color($color);
                        $this->m_axisXColor= imagecolorallocate ($this->m_image, $vColor[0], $vColor[1], $vColor[2]);
                        $this->m_axisXStyle = (int)$style;
                        $this->m_axisXFont = (int)$font;
                        $this->m_axisXFontWidth = imagefontwidth($font);
                        $this->m_axisXFontHeight = imagefontheight($font);
                        $this->m_axisXTitle = $title;
                }
        }

        function setYAxis($color, $style, $font, $title){
                if(strlen($color) > 0){
                        $this->m_showYAxis = true;
                        $vColor = _decode_color($color);
                        $this->m_axisYColor= imagecolorallocate ($this->m_image, $vColor[0], $vColor[1], $vColor[2]);
                        $this->m_axisYStyle = (int)$style;
                        $this->m_axisYFont = (int)$font;
                        $this->m_axisYFontWidth = imagefontwidth($font);
                        $this->m_axisYFontHeight = imagefontheight($font);
                        $this->m_axisYTitle = $title;
                }
        }

        // Set grid attributes
        function setGrid($colorHorizontal, $styleHorizontal, $colorVertical, $styleVertical){
                if(strlen($colorHorizontal) > 0){
                        $this->m_showHGrid = true;
                        $vColor = _decode_color($colorHorizontal);
                        $this->m_gridHColor= imagecolorallocate ($this->m_image, $vColor[0], $vColor[1], $vColor[2]);
                        $this->m_gridHStyle = $styleHorizontal;
                }
                if(strlen($colorVertical) > 0){
                        $this->m_showVGrid = true;
                        $vColor = _decode_color($colorVertical);
                        $this->m_gridVColor = imagecolorallocate ($this->m_image, $vColor[0], $vColor[1], $vColor[2]);
                        $this->m_gridVStyle = $styleVertical;
                }
        }

        // Add new series
        function addSeries(&$values, $plotType, $title, $style, $strokeColor, $fillColor){
                $this->m_series[] = new series($this, $plotType, $values, $title, $style, $strokeColor, $fillColor);
                if($this->m_minValue===false){
                        $this->m_minValue = @$values[0];
                }
                $minValue = _min($values);
                $maxValue = _max($values);
                if($minValue < $this->m_minValue) $this->m_minValue = $minValue;
                if($maxValue > $this->m_maxValue) $this->m_maxValue = $maxValue;

                $count = count($values);
                if($count < $this->m_minCount) $this->m_minCount = $count;
                if($count > $this->m_maxCount) $this->m_maxCount = $count;
        }

        // Set X labels
        function setLabels(&$labels, $textColor, $font, $direction){
                $this->m_labels = &$labels;
                $vTextColor = _decode_color($textColor);
                $this->m_labelsTextColor = imagecolorallocate ($this->m_image, $vTextColor[0], $vTextColor[1], $vTextColor[2]);
                $this->m_labelsFont = $font;
                $this->m_labelsFontWidth = imagefontwidth($font);
                $this->m_labelsFontHeight = imagefontheight($font);
                $this->m_labelsDirection = (int)$direction;

                $count = count($labels);
                if($count < $this->m_minCount) $this->m_minCount = $count;
                if($count > $this->m_maxCount) $this->m_maxCount = $count;

                $this->m_labelsMaxLength = _maxlen($labels);
        }

        // Plot all series
        function plot($file){
                $min = $this->m_minValue;
                $max = $this->m_maxValue + (($this->m_maxValue - $this->m_minValue)*0.1/5)*5;

                // margins
                $margin=$this->m_margin;
                $marginy = $margin;
                if($this->m_title){
                        $marginy += $this->m_fontHeight*1.5;
                }

                $marginbottom = $margin+5;
                if($this->m_labelsDirection == HORIZONTAL){
                        $marginbottom += $this->m_labelsFontWidth;
                }else{
                        $marginbottom += $this->m_labelsMaxLength * $this->m_labelsFontWidth;
                }

                if(@$this->m_axisXTitle){
                        $marginbottom += $this->m_axisXFontHeight*1.5;
                }

                $height = $this->m_height - $marginy - $marginbottom;
                if($this->m_withLegend){
                //
                }
                $maxvalues = floor($height / $this->m_labelsFontHeight / 1.5);  // max displayable values

                $marginx = $margin+5;
                $marginx += strlen(number_format($this->m_maxValue, $this->m_numberOfDecimals, ',', '.')) * $this->m_labelsFontWidth;
                if(@$this->m_axisYTitle){
                        $marginx += $this->m_axisYFontHeight*1.5;
                }

                $width = $this->m_width - $marginx - $margin;

                $w = $width / ($this->m_maxCount+0.2);
                $dx = $w * 0.8;
                $sx = $w - $dx;

                $width = $w * $this->m_maxCount+$sx;
                
                if(($max-$min) == 0) {
                  $maxMin = MAX_MIN;
                } else {
                  $maxMin = $max-$min;
                }

                $h = ($height / $maxvalues);
                $dy = $height / $maxMin;
                $vdy = ($max-$min) / $maxvalues;
                //plot border & background


                imagefilledrectangle($this->m_image, $marginx, $marginy, $marginx + $width, $marginy+$height , $this->m_fillColor);

                // plot title
                if($this->m_title){
                        imagestring ($this->m_image,
                                        $this->m_font,
                                        ($this->m_width-strlen($this->m_title)*$this->m_fontWidth)/2,
                                        $margin,
                                        $this->m_title,
                                        $this->m_textColor);
                }
                // plot values (Y)
                _set_style($this->m_image,$this->m_axisYStyle, $this->m_axisYColor, $this->m_fillColor);
                for($i=0; $i<=$maxvalues; $i++){
                        $yvalue = number_format($min+$vdy*$i, $this->m_numberOfDecimals, $this->m_decimalSeparator, $this->m_thousandsSeparator);
                        imageline($this->m_image,
                                $marginx-3,
                                $marginy+$height - $i*$h,
                                $marginx,
                                $marginy+$height - $i*$h, IMG_COLOR_STYLED);
                        imagestring ($this->m_image,
                                $this->m_labelsFont,
                                $marginx-strlen($yvalue)*$this->m_labelsFontWidth-4,
                                $marginy+$height - $i*$h - $this->m_labelsFontHeight/2,
                                $yvalue,
                                $this->m_labelsTextColor);
                }

                // plot grid
                if($this->m_showHGrid){
                        for($i=0; $i<=$maxvalues; $i++){
                                _set_style($this->m_image,$this->m_gridHStyle, $this->m_gridHColor, $this->m_fillColor);
                                imageline($this->m_image,
                                        $marginx,
                                        $marginy+$height - $i*$h,
                                        $marginx+$width,
                                        $marginy+$height - $i*$h,
                                        IMG_COLOR_STYLED);
                        }
                }
                if($this->m_showVGrid){
                        for($i=0; $i<count($this->m_labels); $i++){
                                $len = strlen($this->m_labels[$i]);
                                if($len > 0){
                                        _set_style($this->m_image,$this->m_gridVStyle, $this->m_gridVColor, $this->m_fillColor);
                                        imageline($this->m_image,
                                                $marginx+$i*$w+$dx/2+$sx,
                                                $height+$marginy,
                                                $i*$w+$marginx+$dx/2+$sx,
                                                $marginy,
                                                IMG_COLOR_STYLED);
                                }
                        }
                }

                _set_style($this->m_image,$this->m_style, $this->m_strokeColor, $this->m_fillColor);
                imagerectangle($this->m_image, $marginx, $marginy, $marginx + $width, $marginy+$height , IMG_COLOR_STYLED);


                // plot graph
                foreach($this->m_series as $series){
                        $cnt = count($series->m_values);
                        // LINE PLOT
                        if($series->m_type == 'line'){
                                _set_style($this->m_image,$series->m_style,$series->m_strokeColor, $this->m_fillColor);
                                $startx = $marginx+$dx/2+$sx ; $starty = $marginy+$height-$dy*($series->m_values[0]-$min);
                                for($i=1; $i<$cnt; $i++){
                                        $x = $marginx+$i*$w+$dx/2+$sx;
                                        $y = $marginy+$height-$dy*($series->m_values[$i]-$min);
                                        imageline($this->m_image,$startx, $starty, $x, $y,IMG_COLOR_STYLED);
                                        $startx = $x; $starty = $y;
                                }
                        // AREA PLOT
                        }else if($series->m_type == 'area'){
                                _set_style($this->m_image,$series->m_style,$series->m_strokeColor, $this->m_fillColor);
                                $vpoints = '';
                                $startx = $marginx+$dx/2+$sx ; $starty = $marginy+$height-$dy*($series->m_values[0]-$min);
                                $vpoints[] = $startx; $vpoints[] = $marginy+$height;
                                for($i=0; $i<$cnt; $i++){
                                        $x = $marginx+$i*$w+$dx/2+$sx;
                                        $y = $marginy+$height-$dy*($series->m_values[$i]-$min);
                                        $vpoints[]=$x; $vpoints[]=$y;
                                        $startx = $x; $starty = $y;
                                }
                                $vpoints[] = $x; $vpoints[] = $marginy+$height;
                                imagefilledpolygon ( $this->m_image, $vpoints, $cnt+2, $series->m_fillColor);
                                imagepolygon ( $this->m_image, $vpoints, $cnt+2, IMG_COLOR_STYLED);
                        // BAR PLOT
                        }else if($series->m_type == 'bar'){
                                _set_style($this->m_image,$series->m_style,$series->m_strokeColor, $this->m_fillColor);
                                $vpoints = '';
                                for($i=0; $i<$cnt; $i++){
                                        imagefilledrectangle($this->m_image,
                                                $sx + $marginx+$i*$w,
                                                $marginy+$height-$dy*($series->m_values[$i]-$min),
                                                $sx + $marginx+$i*$w+$dx,
                                                $marginy+$height,
                                                $series->m_fillColor);
                                        imagerectangle($this->m_image,
                                                $sx + $marginx+$i*$w,
                                                $marginy+$height-$dy*($series->m_values[$i]-$min),
                                                $sx + $marginx+$i*$w+$dx,
                                                $marginy+$height,
                                                IMG_COLOR_STYLED);
                                }
                        // IMPULS PLOT
                        }else if($series->m_type == 'impuls'){
                                _set_style($this->m_image,$series->m_style,$series->m_fillColor,$this->m_fillColor);
                                for($i=0; $i<$cnt; $i++){
                                        $x = $marginx+$i*$w+$dx/2+$sx;
                                        $y = $marginy+$height-$dy*($series->m_values[$i]-$min);
                                        imageline($this->m_image,$x, $y, $x, $marginy+$height, IMG_COLOR_STYLED);
                                }
                        // STEP PLOT
                        }else if($series->m_type == 'step'){
                                _set_style($this->m_image,$series->m_style, $series->m_strokeColor,$this->m_fillColor);
                                $cnt = $cnt; $vpoints = '';
                                $startx = $marginx+$sx/2 ; $starty = $marginy+$height-$dy*($series->m_values[0]-$min);
                                $vpoints[] = $startx; $vpoints[] = $marginy+$height;
                                $vpoints[] = $startx; $vpoints[] = $starty;
                                for($i=1; $i<$cnt; $i++){
                                        $x = $marginx+$i*$w+$sx/2;
                                        $y = $marginy+$height-$dy*($series->m_values[$i]-$min);
                                        $vpoints[]=$x; $vpoints[]=$starty;
                                        $vpoints[]=$x; $vpoints[]=$y;
                                        $startx = $x; $starty = $y;
                                }
                                $vpoints[] = $x+$w; $vpoints[] = $y;
                                $vpoints[] = $x+$w; $vpoints[] = $marginy+$height;
                                imagefilledpolygon ( $this->m_image, $vpoints, $cnt*2+2, $series->m_fillColor);
                                imagepolygon ( $this->m_image, $vpoints, $cnt*2+2, IMG_COLOR_STYLED);
                        // DOT PLOT
                        }else if($series->m_type == 'dot'){
                                _set_style($this->m_image,$series->m_style, $series->m_strokeColor,$this->m_fillColor);
                                for($i=0; $i<$cnt; $i++){
                                        $x = $marginx+$i*$w+$dx/2+$sx;
                                        $y = $marginy+$height-$dy*($series->m_values[$i]-$min);
                                        imagerectangle($this->m_image,$x-2, $y-2, $x+2, $y+2, IMG_COLOR_STYLED);
                                        imagefilledrectangle($this->m_image,$x-1, $y-1, $x+1, $y+1, $series->m_fillColor);
                                }
                        }
                }



                // plot X labels
                for($i=0; $i<count($this->m_labels); $i++){
                        $len = strlen($this->m_labels[$i]);
                        if($len > 0){
                                _set_style($this->m_image,$this->m_axisXStyle, $this->m_axisXColor, $this->m_fillColor);
                                imageline($this->m_image,
                                        $dx/2+$sx+$marginx+$i*$w,
                                        $height+$marginy,
                                        $dx/2+$sx+$i*$w+$marginx,
                                        $height+$marginy+3,
                                        IMG_COLOR_STYLED);

                                if($this->m_labelsDirection == HORIZONTAL){
                                        imagestring ($this->m_image,
                                                $this->m_labelsFont,
                                                $dx/2+$sx+$marginx+$i*$w-$len*$this->m_labelsFontWidth/2,
                                                $marginy+4+$height,
                                                $this->m_labels[$i],
                                                $this->m_labelsTextColor);
                                }else{
                                        imagestringup ($this->m_image,
                                                $this->m_labelsFont,
                                                $dx/2+$sx+$marginx+$i*$w-$this->m_labelsFontHeight/2,
                                                $marginy + $height + $len*$this->m_labelsFontWidth + 4,
                                                $this->m_labels[$i],
                                                $this->m_labelsTextColor);
                                }
                        }

                }

                // plot X axis
                if($this->m_showXAxis){
                        _set_style($this->m_image,$this->m_axisXStyle, $this->m_axisXColor, $this->m_fillColor);
                        imageline($this->m_image, $marginx, $marginy+$height, $marginx + $width, $marginy+$height, IMG_COLOR_STYLED);
                        if($this->m_axisXTitle){
                                imagestring($this->m_image,
                                        $this->m_axisXFont,
                                        $marginx + ($width - strlen($this->m_axisXTitle) * $this->m_axisXFontWidth)/2,
                                        $this->m_height - $margin - $this->m_axisXFontHeight,
                                        $this->m_axisXTitle,
                                        $this->m_axisXColor);
                        }
                }
                // plot Y axis
                if($this->m_showYAxis){
                        _set_style($this->m_image,$this->m_axisYStyle, $this->m_axisYColor, $this->m_fillColor);
                        imageline($this->m_image, $marginx, $marginy, $marginx, $marginy+$height, IMG_COLOR_STYLED);
                        if($this->m_axisYTitle){
                                $titlewidth = strlen($this->m_axisYTitle) * $this->m_axisYFontWidth;
                                imagestringup ($this->m_image,
                                        $this->m_axisYFont,
                                        $margin,
                                        $marginy + $titlewidth + ($height-$titlewidth)/2,
                                        $this->m_axisYTitle,
                                        $this->m_axisYColor);
                        }
                }

                $image_function = 'image' . osc_dynamic_image_extension();

                if(strlen($file) > 0){
                        $image_function($this->m_image, $file);
                }else{
                        $image_function($this->m_image);
                }


        }

}

function _min(&$vvalues){
        $min = $vvalues[0];
        foreach($vvalues as $value){
                if ($min > $value){
                        $min = $value;
                }
        }
        return $min;
}

function _max(&$vvalues){
        $max = $vvalues[0];
        foreach($vvalues as $value){
                if ($max < $value){
                        $max = $value;
                }
        }
        return $max;
}

function _maxlen(&$vvalues){
        $max = strlen($vvalues[0]);
        foreach($vvalues as $value){
                if ($max < strlen($value)){
                        $max = strlen($value);
                }
        }
        return $max;
}

function _decode_color($scolor){
        $istart = 0;
        if($scolor[0] == '#'){
                $istart++;
        }
        $r = hexdec(@substr($scolor, $istart   , 2));
        $g = hexdec(@substr($scolor, $istart +2, 2));
        $b = hexdec(@substr($scolor, $istart +4, 2));
        $vcolor = array($r, $g, $b);
        return ( $vcolor );
}

function _set_style($img,$style,$fore,$back){
        switch($style){
                case DASHED:
                        $thickness = 1;
                        $istyle = array ($fore,$fore,$fore,$fore,$fore,
                                                        $back,$back,$back,$back,$back);
                        break;
                case MEDIUM_DASHED:
                        $thickness = 2;
                        $istyle = array ($fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,
                                                        $back,$back,$back,$back,$back,$back,$back,$back);
                        break;
                case LARGE_DASHED:
                        $thickness = 3;
                        $istyle = array ($fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,$fore,
                                                        $back,$back,$back,$back,$back,$back,$back,$back,$back,$back,$back,$back);
                        break;
                case DOTTED:
                        $thickness = 1;
                        $istyle = array ($fore,$back,$back);
                        break;
                case MEDIUM_DOTTED:
                        $thickness = 2;
                        $istyle = array ($fore,$fore,$fore,$fore,
                                                        $back,$back,$back,$back);
                        break;
                case LARGE_DOTTED:
                        $thickness = 3;
                        $istyle = array ($fore,$fore,$fore,$fore,$fore,$fore,
                                                        $back,$back,$back,$back,$back,$back);
                        break;
                case SOLID:
                        $thickness=1;
                        $istyle = array ($fore,$fore);break;
                case MEDIUM_SOLID:
                        $thickness=2;
                        $istyle = array ($fore,$fore);break;
                case LARGE_SOLID:
                        $thickness=3;
                        $istyle = array ($fore,$fore);break;
                default:
                        $thickness=1;
                        $istyle = array ($fore,$fore);break;
        }
        imagesetthickness ($img, $thickness);
        imagesetstyle ($img, $istyle);
}


function _imageline($img,$x0,$y0,$x1,$y1,$style,$fore,$back){
        imageline($img, $x0,$y0,$x1,$y1,IMG_COLOR_STYLED);
}
?>
