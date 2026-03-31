<?php

declare(strict_types=1);

namespace EasyID\Tests;

use EasyID\APIError;
use EasyID\EasyID;
use EasyID\Signer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EasyIDTest extends TestCase
{
    private const KEY_ID = 'ak_3f9a2b1c7d4e8f0a';
    private const SECRET = 'sk_test';

    public function testHappyPathsAndHeaders(): void
    {
        $history = [];
        $client = $this->makeClient([
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['result' => true, 'match' => true, 'supplier' => 'aliyun', 'score' => 0.98])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['result' => true, 'match' => true, 'supplier' => 'tencent', 'score' => 0.95])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['side' => 'front', 'name' => '张三', 'id_number' => '110101199001011234'])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['status' => 'real', 'carrier' => '移动', 'province' => '广东', 'roaming' => false])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['result' => true, 'match' => true, 'supplier' => 'aliyun', 'score' => 0.99])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['liveness' => true, 'score' => 0.97, 'method' => 'passive', 'frames_analyzed' => 10, 'attack_type' => null])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['match' => true, 'score' => 0.92])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['result' => true, 'supplier' => 'aliyun', 'score' => 0.96])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['result' => true, 'match' => true, 'bank_name' => '工商银行', 'supplier' => 'aliyun', 'score' => 0.99, 'masked_bank_card' => '6222****1234', 'card_type' => 'debit'])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['risk_score' => 30, 'reasons' => ['new_device'], 'recommendation' => 'allow', 'details' => ['rule_score' => null, 'ml_score' => null]])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['device_id' => 'dev_abc', 'stored' => true])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['app_id' => 'app_001', 'available_cents' => 100000])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['total' => 1, 'page' => 1, 'records' => [['id' => 1, 'app_id' => 'app_001', 'request_id' => 'req_001', 'change_cents' => -100, 'balance_before' => 100100, 'balance_after' => 100000, 'reason' => 'idcard_verify2', 'operator' => 'system', 'created_at' => 1711900000]]])),
        ], $history);

        self::assertTrue($client->idcard->verify2('张三', '110101199001011234')->result);
        self::assertTrue($client->idcard->verify3('张三', '110101199001011234', '13800138000')->match);
        self::assertSame('张三', $client->idcard->ocr('front', 'image', 'id.jpg')->name);
        self::assertSame('real', $client->phone->status('13800138000')->status);
        self::assertTrue($client->phone->verify3('张三', '110101199001011234', '13800138000')->result);
        self::assertTrue($client->face->liveness('video', 'passive')->liveness);
        self::assertTrue($client->face->compare('img1', 'img2')->match);
        self::assertTrue($client->face->verify('110101199001011234', 'oss://bucket/key')->result);
        self::assertSame('debit', $client->bank->verify4('张三', '110101199001011234', '6222021234567890', '13800138000')->card_type);
        self::assertSame(30, $client->risk->score(['ip' => '1.2.3.4', 'deviceId' => 'dev_abc', 'action' => 'login'])->risk_score);
        self::assertTrue($client->risk->storeFingerprint('dev_abc', ['canvas' => 'hash123'])->stored);
        self::assertSame(100000, $client->billing->balance('app_001')->available_cents);
        self::assertSame(1, $client->billing->records('app_001')->total);

        foreach ($history as $transaction) {
            $request = $transaction['request'];
            self::assertSame(self::KEY_ID, $request->getHeaderLine('X-Key-ID'));
            self::assertNotSame('', $request->getHeaderLine('X-Timestamp'));
            self::assertNotSame('', $request->getHeaderLine('X-Signature'));
            self::assertStringStartsWith('easyid-php/', $request->getHeaderLine('User-Agent'));
        }
    }

    public function testQueryAndMultipartSigningAndErrors(): void
    {
        $history = [];
        $client = $this->makeClient([
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['status' => 'real', 'carrier' => '', 'province' => '', 'roaming' => false])),
            new Response(200, ['Content-Type' => 'application/json'], $this->ok(['side' => 'front', 'name' => '张三', 'id_number' => '110101199001011234'])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['code' => 1001, 'message' => 'invalid key_id', 'request_id' => 'err-rid', 'data' => null], JSON_THROW_ON_ERROR)),
        ], $history);

        $client->phone->status('13800138000');
        $queryRequest = $history[0]['request'];
        parse_str($queryRequest->getUri()->getQuery(), $query);
        self::assertSame(Signer::sign(self::SECRET, $queryRequest->getHeaderLine('X-Timestamp'), ['phone' => '13800138000'], ''), $queryRequest->getHeaderLine('X-Signature'));

        $client->idcard->ocr('front', 'image-bytes', 'id.jpg');
        $multipartRequest = $history[1]['request'];
        $multipartBody = (string) $multipartRequest->getBody();
        self::assertStringContainsString('name="side"', $multipartBody);
        self::assertStringContainsString('name="image"', $multipartBody);
        self::assertSame(Signer::sign(self::SECRET, $multipartRequest->getHeaderLine('X-Timestamp'), null, $multipartBody), $multipartRequest->getHeaderLine('X-Signature'));

        try {
            $client->phone->status('13800138000');
            self::fail('Expected APIError');
        } catch (APIError $error) {
            self::assertSame(1001, $error->codeValue);
        }
    }

    public function testHttpAndValidationErrors(): void
    {
        $history = [];
        $client = $this->makeClient([
            new Response(503, ['Content-Type' => 'text/html'], '<html>503</html>'),
            new Response(500, ['Content-Type' => 'application/json'], json_encode(['code' => 5000, 'message' => 'internal server error', 'request_id' => 'err-500', 'data' => null], JSON_THROW_ON_ERROR)),
        ], $history);

        try {
            $client->phone->status('13800138000');
            self::fail('Expected RuntimeException');
        } catch (\RuntimeException $error) {
            self::assertStringContainsString('http status 503', $error->getMessage());
        } finally {
            self::assertCount(1, $history);
        }

        try {
            $client->phone->status('13800138000');
            self::fail('Expected APIError');
        } catch (APIError $error) {
            self::assertSame(5000, $error->codeValue);
        }

        try {
            new EasyID('sk_abc', self::SECRET);
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException) {
            self::assertTrue(true);
        }
    }

    public function testEmptySecretValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EasyID(self::KEY_ID, '');
    }

    /** @param list<Response> $responses @param array<int, array{request: RequestInterface}> $history */
    private function makeClient(array $responses, array &$history): EasyID
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));
        return new EasyID(self::KEY_ID, self::SECRET, ['http_client' => new Client(['handler' => $stack])]);
    }

    /** @param array<string,mixed> $data */
    private function ok(array $data): string
    {
        return json_encode(['code' => 0, 'message' => 'success', 'request_id' => 'test-rid', 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
