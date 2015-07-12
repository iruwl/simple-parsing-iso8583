<?php
/* 
author: iruwl
date  : 20150713
*/

$iso = "080082200000800000000400000000000000000000590100000103123001";
// $iso = "0200F23A400108E11002000000000400000216421408999999999917100000000100000002040442152836511200170204020460140404269995040062629995            IBANK          8a8f97e628a9a7790128aa7d89490348        18435111111            TES NAMA PAM                  00200000080000000000020000003                                   201208                             AAAAAAAAAAAAAAAAA                  010010020014813 003AET15010010020014813005XMEGA";

echo "<pre>";
echo "ISO : {$iso}\n";

$mti  = substr($iso, 0, 4);
$sisa = substr($iso, strlen($mti));
echo "MTI : {$mti}\n";

$bitmap_length = base_convert($sisa[0], 16, 2);
$bitmap_length = ($bitmap_length[0] == 0) ? 16 : 32;
echo "Panjang Bit Map    : {$bitmap_length}\n";

$bitmap_hex = substr($sisa, 0, $bitmap_length);
echo "Bit Map (HEX)      : {$bitmap_hex}\n";

$bitmap = '';
for ($i=0; $i<$bitmap_length; $i++) {
    $bitmap .= sprintf("%04d", base_convert($bitmap_hex[$i], 16, 2));
}
echo "Bit Map            : {$bitmap}\n";

$active_data_element = array();
for ($i=0; $i<strlen($bitmap); $i++) {
    if($bitmap[$i]==1) $active_data_element[$i+1] = 'aktif';
}
echo "Data Element Aktif : ";
print_r ($active_data_element);

$sisa = substr($sisa, $bitmap_length);
$str_data = $sisa;
echo "Data : {$str_data}\n";

$iso_config = array(
    //bit ke => tipe, panjang, fix=0
    1 => array('b', 32, 0),
    7 => array('n', 10, 0),
    11 => array('n', 6, 0),
    33 => array('n', 11, 1),
    70 => array('n', 3, 0),
);

if(count($iso_config)!=count($active_data_element)) exit;

$data = array();
foreach ($active_data_element as $key=>$val) {
    if($iso_config[$key][0]!='b') {
        //fix length
        if($iso_config[$key][2]==0) {
            $tmp = substr($str_data, 0, $iso_config[$key][1]);
            if(strlen($tmp)==$iso_config[$key][1]) {
                if($iso_config[$key][0]=='n') {
                    $data[$key] = substr($str_data, 0, $iso_config[$key][1]);
                }
                else {
                    $data[$key]	= ltrim(substr($str_data, 0, $iso_config[$key][1]));
                }
                $str_data = substr($str_data, $iso_config[$key][1], strlen($str_data)-$iso_config[$key][1]);
            }
        }
        //dynamic length
        else {
            $len = strlen($iso_config[$key][1]);
            $tmp = substr($str_data, 0, $len);
            if (strlen($tmp)==$len ) {
                $num = (integer) $tmp;
                $str_data = substr($str_data, $len, strlen($str_data)-$len);
            
                $tmp2 = substr($str_data, 0, $num);
                if (strlen($tmp2)==$num) {
                    if ($iso_config[$key][0]=='n') {
                        $data[$key]	= (double) $tmp2;
                    }
                    else {
                        $data[$key]	= ltrim($tmp2);
                    }
                    $str_data = substr($str_data, $num, strlen($str_data)-$num);
                }
            }
        }
    }
}

echo "Ekstrak Data : ";
print_r ($data);

?>
