<?php

declare(strict_types=1);

namespace EasyID;

use GuzzleHttp\Client;

final class EasyID
{
    public readonly IDCardService $idcard;
    public readonly PhoneService $phone;
    public readonly FaceService $face;
    public readonly BankService $bank;
    public readonly RiskService $risk;
    public readonly BillingService $billing;

    public function __construct(string $keyId, string $secret, array $options = [])
    {
        if (!preg_match('/^ak_[0-9a-f]+$/', $keyId)) {
            throw new \InvalidArgumentException('easyid: keyId must match ak_<hex>, got: ' . $keyId);
        }
        if ($secret === '') {
            throw new \InvalidArgumentException('easyid: secret must not be empty');
        }
        $transport = new Transport(
            $keyId,
            $secret,
            $options['base_url'] ?? 'https://api.easyid.com',
            $options['timeout'] ?? 30,
            $options['http_client'] ?? null
        );
        $this->idcard = new IDCardService($transport);
        $this->phone = new PhoneService($transport);
        $this->face = new FaceService($transport);
        $this->bank = new BankService($transport);
        $this->risk = new RiskService($transport);
        $this->billing = new BillingService($transport);
    }
}
