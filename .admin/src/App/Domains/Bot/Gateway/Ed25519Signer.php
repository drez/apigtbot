<?php

namespace App\Domains\Bot\Gateway;

/**
 * Ed25519 request signer for Binance API keys registered with a public key
 * (the Spot testnet's registration flow, and Binance's recommended key type
 * on mainnet). The signature is the base64-encoded Ed25519 signature over
 * the exact query payload — same payload HMAC keys sign, different encoding.
 *
 * Loads a standard PKCS#8 PEM (openssl genpkey -algorithm ed25519): the DER
 * body's last 32 bytes are the seed; libsodium derives the keypair from it.
 */
class Ed25519Signer
{
    private function __construct(private readonly string $secretKey)
    {
    }

    public static function fromPem(string $pem): self
    {
        if (!str_contains($pem, 'PRIVATE KEY')) {
            throw new \InvalidArgumentException('not an Ed25519 private key PEM');
        }
        $der = base64_decode(preg_replace('/-----[^-]+-----|\s/', '', $pem), true);
        if ($der === false || strlen($der) < 32) {
            throw new \InvalidArgumentException('cannot parse Ed25519 private key PEM');
        }
        $seed = substr($der, -32);
        $pair = sodium_crypto_sign_seed_keypair($seed);
        return new self(sodium_crypto_sign_secretkey($pair));
    }

    public static function fromPemFile(string $path): self
    {
        $pem = @file_get_contents($path);
        if ($pem === false) {
            throw new \InvalidArgumentException("cannot read key file $path");
        }
        return self::fromPem($pem);
    }

    /** Base64 Ed25519 signature over the payload (Binance's expected format). */
    public function sign(string $payload): string
    {
        return base64_encode(sodium_crypto_sign_detached($payload, $this->secretKey));
    }

    /** PEM of the matching public key — what gets registered with Binance. */
    public function publicKeyPem(): string
    {
        $raw = sodium_crypto_sign_publickey(
            sodium_crypto_sign_seed_keypair(substr($this->secretKey, 0, 32))
        );
        // SubjectPublicKeyInfo prefix for Ed25519 (RFC 8410)
        $der = hex2bin('302a300506032b6570032100') . $raw;
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }
}
