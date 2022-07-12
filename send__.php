<?php
require_once("connection.php");
//определение рабочего дня
include('_functions.php');

$file='files/Debit.txt';
$file_kont='files/Kont.txt';
$file_kont_xls='files/Konteyner.xlsx';
$file_skl='1c/Raschet.txt';
$file_inv="files/invoice5.txt";
$file_inv_b="files/invoice5b.txt";
$date_t=date("Y-m-d");
//$hp = file_get_contents("https://isdayoff.ru/$date_t");
$hp=work_day($date_t);
if ($hp>0) {exit(0);}
$date_y=date("Y");
$date_m=date("m");
$date_d=date("d");
$i=1;
$hp=1;
while ($hp>0) {
$date=date("Y-m-d", mktime(0,0,0,$date_m,$date_d-$i,$date_y));
//$hp = file_get_contents("https://isdayoff.ru/$date");
$hp=work_day($date);
$i++;
}

//Приходы денег
//токен бота
define('TELEGRAM_TOKEN', '11111111:AAH5UzaDjTWqWzZ534rn0ussfd-pkG4ssdsdfhn');

// внутренний айдишник
define('TELEGRAM_CHATID_ANDY', '1111111111');
define('TELEGRAM_CHATID_MAX', '11111111111');
define('TELEGRAM_CHANNEL_MTTN', '-11111111111111');
$G=date("G");
//echo $G;
if (($G != 12) and ($G != 9) and ($G != 11)) {
$mysql="select sum(summ) as sm  from v_opl where dt_doc>'$date 00:00:00' and dt_doc<='$date 23:59:59' and beznal<2";
$result=mysqli_query($con,$mysql);
$row=mysqli_fetch_assoc($result);
$sm=$row['sm'];
$mysql="select sum(summ) as sm  from v_opl where dt_doc>'$date_t 00:00:00' and dt_doc<='$date_t 23:59:59' and beznal<2";
$result=mysqli_query($con,$mysql);
$row=mysqli_fetch_assoc($result);
$sm_t=$row['sm'];

if (is_null($sm_t)) {$sm_t=0;}


$sm=number_format($sm, 2, ',', ' ');
$sm_t=number_format($sm_t, 2, ',', ' ');
$txt="$date
$sm
$date_t
$sm_t";
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
}

//Проверка дебиторки

if ($G==11) {
$file_dt= date("Y-m-d", filemtime($file));
if ($file_dt != $date_t) {
$txt="Файл дебиторской задолженности
сегодня не загружался";
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
}

$file_dt= date("Y-m-d", filemtime($file_kont_xls));
if ($file_dt != $date_t) {
$txt="Файл движения контейнеров
сегодня не загружался";
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
}

}

//Дебиторка

$w=date("w");
if ($G==12) {
$file_dt= date("Y-m-d", filemtime($file));
$txt="Данные за $file_dt";
$txt1=file_get_contents($file);
$txt=$txt . "
 " . $txt1;
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
}


//Контейнеры
if ($G==12) {
//$file_dt= date("Y-m-d", filemtime($file_kont_xls));
//$txt="Данные за $file_dt";
$txt=file_get_contents($file_kont);
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
}

//$txt=file_get_contents($file_skl);

//Товары категорий А,В

if ($G==9) {
$f=fopen($file_skl, "r");
$txt1=fgets($f);
$txt1=explode(";",$txt1);
$txt2=fgets($f);
$txt2=explode(";",$txt2);
$txt3=fgets($f);
$txt3=explode(";",$txt3);
fclose($f);
$txt=" Категория АА: закончилось $txt1[1]; мало $txt1[2]
Категория А: закончилось $txt2[1]; мало $txt2[2]
Категория В: закончилось $txt3[1]; мало $txt3[2]";
//echo $txt;
message_to_telegram($txt, TELEGRAM_CHANNEL_MTTN);
$txt_inv="Расшифровка\r\nhttp://192.168.155.23/telega/invoice_.php\r\n";
$txt_inv.=file_get_contents($file_inv);
$txt_inv_b="Расшифровка\r\nhttp://192.168.155.23/telega/invoice1_.php\r\n";
$txt_inv_b.=file_get_contents($file_inv_b);
message_to_telegram($txt_inv, TELEGRAM_CHANNEL_MTTN);
message_to_telegram($txt_inv_b, TELEGRAM_CHANNEL_MTTN);

}

function message_to_telegram($text,$chat_id)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => $chat_id,
                'text' => $text,
            ),
        )
    );
    curl_exec($ch);
}



mysqli_close($con);

?>