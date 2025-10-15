<?php
require_once __DIR__ . '/Kavenegar/KavenegarApi.php';

use Kavenegar\KavenegarApi;


function imageUrl($image)
{
    return env('ADMIN_PANEL_URL') . env('PRODUCT_IMAGES_PATH') . $image;
}



function salePercent($price,$salePrice)
{
    return round((($price - $salePrice) / $price) * 100);
}

function sendOtpSms($cellphone, $otp)
{
    $sender = env('KAVENEGAR_SENDER');  // از .env می‌گیرد
    $receptor = $cellphone;
    $message = "کد تایید شما: $otp";

    $api = new KavenegarApi(env('KAVENEGAR_API_KEY'));

    try {
        $result = $api->send($sender, [$receptor], $message);
        return ['status' => 'success', 'result' => $result];
    } catch (\Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
