# EasyID PHP SDK

Official PHP SDK for the EasyID identity verification API.

## Install

```bash
composer require easyid/easyid-php
```

## Usage

```php
<?php

use EasyID\EasyID;

$client = new EasyID('ak_xxx', 'sk_xxx');
$result = $client->idcard->verify2('张三', '110101199001011234');

var_dump($result->match);
```

## Notes

- This is a server-side SDK. Do not expose `secret` in browsers or mobile apps.
- Pass `base_url` in the options array for private deployments.
- See `../docs/integration-guide.md` for end-to-end integration and troubleshooting.
