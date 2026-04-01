# EasyID PHP SDK

Official PHP SDK for the EasyID identity verification API.

EasyID 易验云 focuses on identity verification and security risk control APIs, including real-name verification, liveness detection, face recognition, phone verification, and fraud-risk related capabilities.

中文文档： [README.zh-CN.md](README.zh-CN.md)

## Install

```bash
composer require easyid/easyid-php
```

## Quick Start

```php
<?php

use EasyID\EasyID;
use EasyID\APIError;

$client = new EasyID('ak_xxx', 'sk_xxx');

try {
    $result = $client->idcard->verify2('张三', '110101199001011234');
    var_dump($result->match);
} catch (APIError $error) {
    var_dump($error->codeValue, $error->requestId);
}
```

## Supported APIs

- IDCard: `verify2`, `verify3`, `ocr`
- Phone: `status`, `verify3`
- Face: `liveness`, `compare`, `verify`
- Bank: `verify4`
- Risk: `score`, `storeFingerprint`
- Billing: `balance`, `records`

## Configuration

- `base_url`
- `timeout`
- `http_client`

## Security Notice

This is a server-side SDK. Do not expose `secret` in browsers or mobile apps.

## Official Resources

- Official website: `https://www.easyid.com.cn/`
- GitHub organization: `https://github.com/easyid-com-cn/`
