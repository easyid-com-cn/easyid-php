<?php

declare(strict_types=1);

namespace EasyID;

final readonly class IDCardVerifyResult { public function __construct(public bool $result, public bool $match, public string $supplier, public float $score, public mixed $raw = null) {} }
final readonly class IDCardOCRResult { public function __construct(public string $side, public string $name = '', public string $id_number = '', public string $gender = '', public string $nation = '', public string $birth = '', public string $address = '', public string $issue = '', public string $valid = '') {} }
final readonly class PhoneStatusResult { public function __construct(public string $status, public string $carrier, public string $province, public bool $roaming) {} }
final readonly class PhoneVerify3Result { public function __construct(public bool $result, public bool $match, public string $supplier, public float $score) {} }
final readonly class LivenessResult { public function __construct(public bool $liveness, public float $score, public string $method, public int $frames_analyzed, public ?string $attack_type) {} }
final readonly class CompareResult { public function __construct(public bool $match, public float $score) {} }
final readonly class FaceVerifyResult { public function __construct(public bool $result, public string $supplier, public float $score) {} }
final readonly class BankVerify4Result { public function __construct(public bool $result, public bool $match, public string $bank_name, public string $supplier, public float $score, public string $masked_bank_card, public string $card_type) {} }
final readonly class RiskDetails { public function __construct(public ?int $rule_score, public ?int $ml_score) {} }
final readonly class RiskScoreResult { /** @param list<string> $reasons */ public function __construct(public int $risk_score, public array $reasons, public string $recommendation, public RiskDetails $details) {} }
final readonly class StoreFingerprintResult { public function __construct(public string $device_id, public bool $stored) {} }
final readonly class BillingBalanceResult { public function __construct(public string $app_id, public int $available_cents) {} }
final readonly class BillingRecord { public function __construct(public int $id, public string $app_id, public string $request_id, public int $change_cents, public int $balance_before, public int $balance_after, public string $reason, public string $operator, public int $created_at) {} }
final readonly class BillingRecordsResult { /** @param list<BillingRecord> $records */ public function __construct(public int $total, public int $page, public array $records) {} }
