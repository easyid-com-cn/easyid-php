# EasyID PHP SDK

EasyID PHP SDK 是易验云身份验证 API 的官方 PHP 客户端。

English README: [README.md](README.md)

EasyID 提供身份证核验、手机号核验、人脸识别、银行卡核验、风控评分等能力。本 SDK 适用于服务端 PHP 项目，自动完成签名、请求头注入和统一错误解析。

## 安装

```bash
composer require easyid/easyid-php
```

要求：

- PHP `>= 8.2`

## 快速开始

```php
<?php

use EasyID\APIError;
use EasyID\EasyID;

$client = new EasyID('ak_xxx', 'sk_xxx');

try {
    $result = $client->idcard->verify2('张三', '110101199001011234');
    var_dump($result->match);
} catch (APIError $error) {
    var_dump($error->codeValue, $error->getMessage(), $error->requestId);
}
```

## 已支持接口

- `$client->idcard->verify2()`：身份证二要素核验
- `$client->idcard->verify3()`：身份证三要素核验
- `$client->idcard->ocr()`：身份证 OCR
- `$client->phone->status()`：手机号状态查询
- `$client->phone->verify3()`：手机号三要素核验
- `$client->face->liveness()`：人脸活体检测
- `$client->face->compare()`：人脸比对
- `$client->face->verify()`：人脸核验
- `$client->bank->verify4()`：银行卡四要素核验
- `$client->risk->score()`：风控评分
- `$client->risk->storeFingerprint()`：存储设备指纹
- `$client->billing->balance()`：查询账户余额
- `$client->billing->records()`：查询账单记录

## 配置项

- `base_url`：自定义 API 地址
- `timeout`：超时时间，单位秒
- `http_client`：自定义 Guzzle Client

## 错误处理

服务端业务错误会抛出 `APIError`。

```php
try {
    $result = $client->phone->status('13800138000');
    var_dump($result->status);
} catch (APIError $error) {
    var_dump($error->codeValue, $error->getMessage(), $error->requestId);
}
```

## 安全说明

- 这是服务端 SDK，不要在浏览器、前端页面或移动端暴露 `secret`
- `keyId` 必须符合 `ak_[0-9a-f]+`
- SDK 会自动处理 `X-Key-ID`、`X-Timestamp`、`X-Signature`

## 官方资源

- 官网：`https://www.easyid.com.cn/`
- GitHub：`https://github.com/easyid-com-cn/`
