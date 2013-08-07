<?php 
/* 
** PHP implementation of the Secure Hash Algorithm, SHA-1, as defined 
** in FIPS PUB 180-1 
* 
** Version 1.1 
** Copyright 2002 - 2003 Marcus Campbell 
** http://www.tecknik.net/sha-1/ 
* 
** This code is available under the GNU Lesser General Public License: 
** http://www.gnu.org/licenses/lgpl.txt 
* 
** Based on the JavaScript implementation by Paul Johnston 
** http://pajhome.org.uk/ 
*/ 
function str2blks_SHA1($str) { 
    $nblk = ((strlen($str) + 8) >> 6) + 1; 
    for($i=0; $i < $nblk * 16; $i++) $blks[$i] = 0; 
    for($i=0; $i < strlen($str); $i++) { 
        $blks[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8); 
    } 
    $blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8); 
    $blks[$nblk * 16 - 1] = strlen($str) * 8; 
    return $blks; 
} 
function safe_add($x, $y) { 
    $lsw = ($x & 0xFFFF) + ($y & 0xFFFF); 
    $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16); 
    return ($msw << 16) | ($lsw & 0xFFFF); 
} 
function rol($num, $cnt) { 
    return ($num << $cnt) | zeroFill($num, 32 - $cnt); 
} 
function zeroFill($a, $b) { 
    $bin = decbin($a); 
    if (strlen($bin) < $b) $bin = 0; 
    else $bin = substr($bin, 0, strlen($bin) - $b); 
    for ($i=0; $i < $b; $i++) { 
        $bin = "0".$bin; 
    } 
    return bindec($bin); 
} 
function ft($t, $b, $c, $d) { 
    if($t < 20) return ($b & $c) | ((~$b) & $d); 
    if($t < 40) return $b ^ $c ^ $d; 
    if($t < 60) return ($b & $c) | ($b & $d) | ($c & $d); 
    return $b ^ $c ^ $d; 
} 
function kt($t) { 
    if ($t < 20) { 
        return 1518500249; 
    } else if ($t < 40) { 
        return 1859775393; 
    } else if ($t < 60) { 
        return -1894007588; 
    } else { 
        return -899497514; 
    } 
} 
function calc_sha1($str) { 
    $x = str2blks_SHA1($str); 
    $a =  1732584193; 
    $b = -271733879; 
    $c = -1732584194; 
    $d =  271733878; 
    $e = -1009589776; 
    for($i = 0; $i < sizeof($x); $i += 16) { 
        $olda = $a; 
        $oldb = $b; 
        $oldc = $c; 
        $oldd = $d; 
        $olde = $e; 
        for($j = 0; $j < 80; $j++) { 
            if($j < 16) $w[$j] = $x[$i + $j]; 
            else $w[$j] = rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1); 
            $t = safe_add(safe_add(rol($a, 5), ft($j, $b, $c, $d)), safe_add(safe_add($e, $w[$j]), kt($j))); 
            $e = $d; 
            $d = $c; 
            $c = rol($b, 30); 
            $b = $a; 
             $a = $t; 
        } 
        $a = safe_add($a, $olda); 
        $b = safe_add($b, $oldb); 
        $c = safe_add($c, $oldc); 
        $d = safe_add($d, $oldd); 
        $e = safe_add($e, $olde); 
    } 
    return sprintf("%08s%08s%08s%08s%08s", dechex($a), dechex($b), dechex($c), dechex($d), dechex($e)); 
} 
?>
