<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Wexample\SymfonyDesignSystem\Class\UploadRules;

/**
 * The address a browser sends files to names the directory they land in, and
 * the server signed it: the browser cannot choose where a file goes, only use
 * the place a page offered. The page that offers an upload — a controller, a
 * template — issues the token for the one directory it means.
 */
final class UploadTokenService
{
    // A page left open over a weekend still uploads on the Monday.
    public const int DEFAULT_TTL = 7 * 24 * 3600;

    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $secret,
    ) {
    }

    /**
     * What the page lets in — kinds of file, a size — is signed with the
     * directory: the browser can no more widen it than move it.
     */
    public function issue(string $directory, int $ttl = self::DEFAULT_TTL, ?UploadRules $rules = null): string
    {
        $payload = $this->encode((string) json_encode([
            'd' => rtrim($directory, '/'),
            'e' => time() + $ttl,
        ] + ($rules?->toArray() ?? [])));

        return $payload.'.'.$this->sign($payload);
    }

    /**
     * The directory a token was issued for, or null when it was not issued
     * here, was altered, or is past its time.
     */
    public function directory(string $token): ?string
    {
        return $this->read($token)['d'] ?? null;
    }

    // What a valid token lets in; nothing said, anything.
    public function rules(string $token): UploadRules
    {
        return UploadRules::fromArray($this->read($token) ?? []);
    }

    private function read(string $token): ?array
    {
        $parts = explode('.', $token, 2);

        if (2 !== count($parts) || ! hash_equals($this->sign($parts[0]), $parts[1])) {
            return null;
        }

        $data = json_decode($this->decode($parts[0]), true);

        if (! is_array($data) || ! is_string($data['d'] ?? null) || ($data['e'] ?? 0) < time()) {
            return null;
        }

        return $data;
    }

    private function sign(string $payload): string
    {
        return $this->encode(hash_hmac('sha256', $payload, $this->secret, true));
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
