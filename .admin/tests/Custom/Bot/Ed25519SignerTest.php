<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Gateway\Ed25519Signer;
use PHPUnit\Framework\TestCase;

class Ed25519SignerTest extends TestCase
{
    /** Throwaway openssl-generated fixture pair (never a real credential). */
    private const PRIV_PEM = <<<PEM
-----BEGIN PRIVATE KEY-----
MC4CAQAwBQYDK2VwBCIEINXXc1+TFp10o5okUrAWahOW9/8B+UtTgiZ3jukTEVHt
-----END PRIVATE KEY-----
PEM;
    private const PUB_PEM = <<<PEM
-----BEGIN PUBLIC KEY-----
MCowBQYDK2VwAyEAy08Azo9zDwQIdPDftmDQg/rF1pVVkX2xNDacBHg/yQw=
-----END PUBLIC KEY-----
PEM;

    private function rawPub(): string
    {
        $der = base64_decode(preg_replace('/-----[^-]+-----|\s/', '', self::PUB_PEM));
        return substr($der, -32);
    }

    public function testSignatureVerifiesAgainstOpensslPublicKey(): void
    {
        $signer = Ed25519Signer::fromPem(self::PRIV_PEM);
        $payload = 'symbol=BTCUSDT&side=BUY&timestamp=1499827319559';
        $sig = $signer->sign($payload);
        $this->assertTrue(
            sodium_crypto_sign_verify_detached(base64_decode($sig), $payload, $this->rawPub()),
            'signature must verify against the openssl-derived public key'
        );
    }

    public function testSignatureIsDeterministicAndBase64(): void
    {
        $signer = Ed25519Signer::fromPem(self::PRIV_PEM);
        $a = $signer->sign('payload');
        $this->assertSame($a, $signer->sign('payload'), 'Ed25519 is deterministic');
        $this->assertNotFalse(base64_decode($a, true));
    }

    public function testFromPemFileReadsDisk(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ed');
        file_put_contents($path, self::PRIV_PEM);
        try {
            $sig = Ed25519Signer::fromPemFile($path)->sign('x');
            $this->assertTrue(sodium_crypto_sign_verify_detached(base64_decode($sig), 'x', $this->rawPub()));
        } finally {
            unlink($path);
        }
    }

    public function testRejectsGarbagePem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Ed25519Signer::fromPem('not a pem');
    }

    public function testPublicKeyPemRoundTrips(): void
    {
        $signer = Ed25519Signer::fromPem(self::PRIV_PEM);
        $this->assertSame(
            trim(preg_replace('/\s+/', '', self::PUB_PEM)),
            trim(preg_replace('/\s+/', '', $signer->publicKeyPem()))
        );
    }
}
