<?php
require_once 'vendor/autoload.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use GuzzleHttp\Client;

// 阿里云接口秘钥信息
const accessKeyId = '*********';
const accessKeySecret = '*********';

// 新知天气秘钥信息
const seniverseKey = '******';
const seniverseUid = '******';

// 初始化阿里云 SDK 实例
AlibabaCloud::accessKeyClient(accessKeyId, accessKeySecret)
    ->regionId('cn-hangzhou')
    ->asDefaultClient();

try {
    // 你对小可爱的昵称 有多少就列多少
    $name_arr = [
        '小彭彭',
        '小傻瓜',
        '彭大长腿',
        '小桂花',
        '小短腿',
        '小瑀瑀',
        '彭怼怼',
        'LiquidNitrogen',
        '彭老板',
        '老彭',
        '搞黄彭',
    ];

    // 随机取一个昵称
    $name_str = $name_arr[array_rand($name_arr)];
    // format lucky number between 0~9
    $number_str = rand(0, 9);

    $sms_content = [
        'name' => $name_str,
        'day' => getDays(),
        'weather' => getWeather() . ' ' . getAirNow(),
        'number' => $number_str,
        'sender_name' => getFromName(),
    ];

    $result = AlibabaCloud::rpc()
        ->product('Dysmsapi')
        ->scheme('https')
        // use current version
        ->version('2017-05-25')
        ->action('SendSms')
        ->method('POST')
        ->host('dysmsapi.aliyuncs.com')
        ->options([
            'query' => [
                'RegionId' => "cn-hangzhou",
                // 填写你女朋友的手机号码[多个请用,分割(臭不要脸)]
                'PhoneNumbers' => "********",
                // 签名也需要申请
                'SignName' => "余大能耐",
                'TemplateCode' => "SMS_176539584",
                'TemplateParam' => json_encode($sms_content),
            ],
        ])
        ->request();
    print_r($result->toArray());
} catch (ClientException $e) {
    echo $e->getErrorMessage() . PHP_EOL;
} catch (ServerException $e) {
    echo $e->getErrorMessage() . PHP_EOL;
}

/**
 * 获取天气信息
 * https://www.seniverse.com/
 * @return string
 * @author yuzhihao <yu@vagh.cn>
 * @since 2020/2/24
 */
function getWeather()
{
    // 更改为你要获取天气的城市名称
    $location = "Beijing";

    $signed_key_name = getSignedKeyName();
    $url = "https://api.seniverse.com/v3/weather/daily.json?location=" . $location . "&days=1&" . $signed_key_name;
    $client = new Client();
    $response = $client->get($url);
    $body = $response->getBody()->getContents();

    $result = json_decode($body, true);
    $daily = $result['results'][0]['daily'][0];

    $str = $daily['text_day'] . '(' . $daily['low'] . '~' . $daily['high'] . '℃)';

    return $str;
}

/**
 * 获取当前温度信息
 * @return string
 * @author yuzhihao <yu@vagh.cn>
 * @since 2020/2/24
 */
function getAirNow()
{
    // 更改为你要获取天气的城市名称
    $location = "Beijing";

    $signed_key_name = getSignedKeyName();
    $url = "https://api.seniverse.com/v3/life/suggestion.json?location=" . $location . "&language=zh-Hans&" . $signed_key_name;
    $client = new Client();
    $response = $client->get($url);
    $body = $response->getBody()->getContents();
    $result = json_decode($body, true);

    $air_str = $result['results'][0]['suggestion']['dressing'];

    switch ($air_str['brief']) {
        case '炎热':
            $mess = '外面太热啦！';
            break;
        case '热':
            $mess = '外面比较热~';
            break;
        case '舒适':
            $mess = '天气太好啦！';
            break;
        case '较舒适':
            $mess = '气温还行~';
            break;
        case '较冷':
            $mess = '较冷别感冒~';
            break;
        case '冷':
            $mess = '很冷多穿衣服~';
            break;
        case '寒冷':
            $mess = '超冷一定穿厚些~';
            break;
        default:
            $mess = '';
            break;
    }

    return $mess;
}

/**
 * 计算在一起多长时间
 * @return false|float|int
 * @author yuzhihao <yu@vagh.cn>
 * @since 2020/2/24
 */
function getDays()
{
    // 在一起的时间
    $d1 = strtotime("2019-10-30 00:00:00");
    return ceil((time() - $d1) / 60 / 60 / 24) - 1;
}

/**
 * 获取自己的昵称
 * @return string
 * @author yuzhihao <yu@vagh.cn>
 * @since 2020/2/24
 */
function getFromName()
{
    $day = getDays();

    if ($day == 2) {
        return '余大长腿';
    }

    $name_arr = [
        '余大长腿',
        '余壮壮',
        '余大能耐',
        '爸爸',
        '余老板',
        '老余',
    ];

    return $name_arr[array_rand($name_arr)];
}

/**
 * 获取新知天气签名
 * @return string
 * @author yuzhihao <yu@vagh.cn>
 * @since 2020/2/24
 */
function getSignedKeyName()
{
    $key_name = "ts=" . time() . "&ttl=300&uid=" . seniverseUid;
    // 使用 HMAC-SHA1 方式，以 API 密钥（key）对上一步生成的参数字符串（raw）进行加密
    $sign = base64_encode(hash_hmac('sha1', $key_name, seniverseKey, true));
    // 将上一步生成的加密结果用 base64 编码，并做一个 urlencode，得到签名 sig
    return $key_name . "&sig=" . urlencode($sign);
}
