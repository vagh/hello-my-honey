# hello-my-honey
定期给女朋友发送问候短信（直男版）请适度使用，毕竟这是莫得感情的机器拼接出的冷冰冰的问候。如果真的爱你的女朋友，请用你自己的真心对待她吧~

## 安装本地环境

将项目部署在你的服务器上后，运行以下代码安装库。
```shell
$ composer install
```

## 初步配置
使用前请先确保你有一个阿里云的账号，并且有一定的余额。因为发短信是需要 RMB 的。不过也不贵，花几分钱博女朋友一笑还是很值得的！

把你自己的阿里云账户后台的 `accessKeyId` 和 `accessKeySecret` 替换到 `sender.php` 下面的代码中：
```php
const accessKeyId = '*********';
const accessKeySecret = '*********';
```

为了避免短信内容不够生动，你还需要去心知天气申请一个开发者账号，用来获取天气信息。好消息是这个接口在规定限制范围内是免费的。

同样，你需要把后台的 `key` 和 `uid` 填写到代码中
```php
const seniverseKey = '******';
const seniverseUid = '******';
```
