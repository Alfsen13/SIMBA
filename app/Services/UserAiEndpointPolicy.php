<?php

namespace App\Services;

use InvalidArgumentException;

class UserAiEndpointPolicy
{
    /**
     * @return array<string, mixed>
     */
    public static function httpOptions(string $provider, ?string $endpoint): array
    {
        if (! in_array($provider, ['ollama', 'custom'], true)) {
            return [];
        }

        if ($endpoint === null) {
            if ($provider === 'custom') {
                throw new InvalidArgumentException('Endpoint AI tidak diizinkan.');
            }

            return ['allow_redirects' => false];
        }

        $parts = parse_url($endpoint);
        if (! is_array($parts)
            || ! isset($parts['scheme'], $parts['host'])
            || isset($parts['user'], $parts['pass'], $parts['fragment'])
        ) {
            throw new InvalidArgumentException('Endpoint AI tidak diizinkan.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower(trim($parts['host'], '[]'));
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $path = $parts['path'] ?? '';

        if ($provider === 'ollama'
            && $scheme === 'http'
            && $host === 'host.docker.internal'
            && $port === 11434
            && in_array($path, ['', '/'], true)
            && ! isset($parts['query'])
        ) {
            return ['allow_redirects' => false];
        }

        if ($scheme !== 'https' || $port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Endpoint AI harus menggunakan HTTPS.');
        }

        if ($provider === 'ollama' && ! in_array($path, ['', '/'], true)) {
            throw new InvalidArgumentException('Endpoint Ollama harus berupa alamat dasar server.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $addresses = [$host];
        } else {
            if (! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                throw new InvalidArgumentException('Host endpoint AI tidak valid.');
            }

            $records = dns_get_record($host, DNS_A | DNS_AAAA);
            $addresses = collect($records ?: [])
                ->flatMap(fn (array $record) => array_filter([
                    $record['ip'] ?? null,
                    $record['ipv6'] ?? null,
                ]))
                ->unique()
                ->values()
                ->all();
        }

        if ($addresses === []) {
            throw new InvalidArgumentException('Host endpoint AI tidak dapat divalidasi.');
        }

        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidArgumentException('Endpoint AI tidak boleh mengarah ke jaringan privat.');
            }
        }

        $ipv4Address = collect($addresses)
            ->first(fn (string $address) => filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));

        if ($ipv4Address === null) {
            throw new InvalidArgumentException('Host endpoint AI harus memiliki alamat IPv4 publik.');
        }

        return [
            'allow_redirects' => false,
            'curl' => [
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$ipv4Address}"],
            ],
        ];
    }
}
