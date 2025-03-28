<?php

class Jwt
{
    private $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function createToken(array $payload, $expirationInSeconds = 3600)
    {
        // Header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        // Add expiration time to payload
        $payload['exp'] = time() + $expirationInSeconds;

        // Encode header and payload
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        // Create signature
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->secret,
            true
        );
        $signatureEncoded = $this->base64UrlEncode($signature);

        // Build token
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * @throws Exception
     */
    public function validateToken($token, $tokenService)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception("Invalid token structure");
        }

        list($headerEncoded, $payloadEncoded, $signatureProvided) = $parts;

        // Decode and validate header
        $header = json_decode($this->base64UrlDecode($headerEncoded), true);
        if (!$header || $header['alg'] !== 'HS256') {
            throw new Exception("Unsupported or missing algorithm");
        }

        // Verify signature
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->secret,
            true
        );
        $signatureEncoded = $this->base64UrlEncode($signature);

        if (!hash_equals($signatureProvided, $signatureEncoded)) {
            throw new Exception("Invalid signature");
        }

        // Decode and validate payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            throw new Exception("Invalid payload");
        }

        // Check if token is expired based on JWT payload
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            $tokenService->expireAndRevokeToken($token);
            throw new Exception("Token has expired");
        }

        // Check if token exists and is valid in database
        $storedToken = $tokenService->findValidToken($token);
        if (!$storedToken) {
            throw new Exception("Token has been revoked or expired");
        }

        return $payload;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}