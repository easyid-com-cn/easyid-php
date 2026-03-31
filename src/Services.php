<?php

declare(strict_types=1);

namespace EasyID;

final class IDCardService
{
    public function __construct(private readonly Transport $transport) {}
    public function verify2(string $name, string $idNumber, ?string $traceId = null): IDCardVerifyResult { return hydrate(IDCardVerifyResult::class, $this->transport->requestJson('POST', '/v1/idcard/verify2', null, compact('name') + ['id_number' => $idNumber] + ($traceId ? ['trace_id' => $traceId] : []))); }
    public function verify3(string $name, string $idNumber, string $mobile, ?string $traceId = null): IDCardVerifyResult { return hydrate(IDCardVerifyResult::class, $this->transport->requestJson('POST', '/v1/idcard/verify3', null, compact('name', 'mobile') + ['id_number' => $idNumber] + ($traceId ? ['trace_id' => $traceId] : []))); }
    public function ocr(string $side, string $image, string $filename = 'image.bin'): IDCardOCRResult { return hydrate(IDCardOCRResult::class, $this->transport->requestMultipart('/v1/ocr/idcard', ['side' => $side], [['name' => 'image', 'contents' => $image, 'filename' => $filename]])); }
}

final class PhoneService
{
    public function __construct(private readonly Transport $transport) {}
    public function status(string $phone): PhoneStatusResult { return hydrate(PhoneStatusResult::class, $this->transport->requestJson('GET', '/v1/phone/status', ['phone' => $phone], null)); }
    public function verify3(string $name, string $idNumber, string $mobile): PhoneVerify3Result { return hydrate(PhoneVerify3Result::class, $this->transport->requestJson('POST', '/v1/phone/verify3', null, ['name' => $name, 'id_number' => $idNumber, 'mobile' => $mobile])); }
}

final class FaceService
{
    public function __construct(private readonly Transport $transport) {}
    public function liveness(string $media, ?string $mode = null, string $filename = 'media.bin'): LivenessResult { return hydrate(LivenessResult::class, $this->transport->requestMultipart('/v1/face/liveness', $mode ? ['mode' => $mode] : [], [['name' => 'media', 'contents' => $media, 'filename' => $filename]])); }
    public function compare(string $image1, string $image2, string $filename1 = 'image1.bin', string $filename2 = 'image2.bin'): CompareResult { return hydrate(CompareResult::class, $this->transport->requestMultipart('/v1/face/compare', [], [['name' => 'image1', 'contents' => $image1, 'filename' => $filename1], ['name' => 'image2', 'contents' => $image2, 'filename' => $filename2]])); }
    public function verify(string $idNumber, ?string $mediaKey = null, ?string $callbackUrl = null): FaceVerifyResult { return hydrate(FaceVerifyResult::class, $this->transport->requestJson('POST', '/v1/face/verify', null, ['id_number' => $idNumber] + ($mediaKey ? ['media_key' => $mediaKey] : []) + ($callbackUrl ? ['callback_url' => $callbackUrl] : []))); }
}

final class BankService
{
    public function __construct(private readonly Transport $transport) {}
    public function verify4(string $name, string $idNumber, string $bankCard, ?string $mobile = null, ?string $traceId = null): BankVerify4Result { return hydrate(BankVerify4Result::class, $this->transport->requestJson('POST', '/v1/bank/verify4', null, ['name' => $name, 'id_number' => $idNumber, 'bank_card' => $bankCard] + ($mobile ? ['mobile' => $mobile] : []) + ($traceId ? ['trace_id' => $traceId] : []))); }
}

final class RiskService
{
    public function __construct(private readonly Transport $transport) {}
    /** @param array<string,mixed> $request */ public function score(array $request): RiskScoreResult { $data = $this->transport->requestJson('POST', '/v1/risk/score', null, normalizeKeys($request)); return new RiskScoreResult((int) $data['risk_score'], $data['reasons'] ?? [], (string) $data['recommendation'], new RiskDetails($data['details']['rule_score'] ?? null, $data['details']['ml_score'] ?? null)); }
    /** @param array<string,mixed> $fingerprint */ public function storeFingerprint(string $deviceId, array $fingerprint): StoreFingerprintResult { return hydrate(StoreFingerprintResult::class, $this->transport->requestJson('POST', '/v1/device/fingerprint', null, ['device_id' => $deviceId, 'fingerprint' => $fingerprint])); }
}

final class BillingService
{
    public function __construct(private readonly Transport $transport) {}
    public function balance(string $appId): BillingBalanceResult { return hydrate(BillingBalanceResult::class, $this->transport->requestJson('GET', '/v1/billing/balance', ['app_id' => $appId], null)); }
    public function records(string $appId, int $page = 1, int $pageSize = 20): BillingRecordsResult { $normalizedPageSize = $pageSize <= 0 ? 20 : min($pageSize, 100); $data = $this->transport->requestJson('GET', '/v1/billing/records', ['app_id' => $appId, 'page' => (string) max($page, 1), 'page_size' => (string) $normalizedPageSize], null); return new BillingRecordsResult((int) $data['total'], (int) $data['page'], array_map(fn(array $record): BillingRecord => hydrate(BillingRecord::class, $record), $data['records'] ?? [])); }
}

/**
 * @template T of object
 * @param class-string<T> $className
 * @param array<string,mixed> $data
 * @return T
 */
function hydrate(string $className, array $data): object
{
    $reflection = new \ReflectionClass($className);
    $arguments = [];
    foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
        if (array_key_exists($parameter->getName(), $data)) {
            $arguments[] = $data[$parameter->getName()];
            continue;
        }
        if ($parameter->isDefaultValueAvailable()) {
            $arguments[] = $parameter->getDefaultValue();
            continue;
        }
        $arguments[] = null;
    }
    return $reflection->newInstanceArgs($arguments);
}

/** @param array<string,mixed> $data @return array<string,mixed> */
function normalizeKeys(array $data): array
{
    $mapping = ['deviceFingerprint' => 'device_fingerprint', 'deviceId' => 'device_id', 'userAgent' => 'user_agent'];
    $normalized = [];
    foreach ($data as $key => $value) {
        $normalized[$mapping[$key] ?? $key] = $value;
    }
    return $normalized;
}
