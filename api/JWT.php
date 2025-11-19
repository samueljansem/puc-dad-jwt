<?php
class JWT {
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function encode($payload, $secret, $algorithm = 'HS256') {
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function decode($token, $secret, $algorithm = 'HS256') {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $secret,
            true
        );

        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid signature');
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payload;
    }

    public static function generateAccessToken($userId, $email, $username) {
        $payload = [
            'iss' => 'jwt-demo',
            'sub' => $userId,
            'email' => $email,
            'username' => $username,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + ACCESS_TOKEN_EXPIRY
        ];

        return self::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    }

    public static function generateRefreshToken($userId) {
        $payload = [
            'iss' => 'jwt-demo',
            'sub' => $userId,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + REFRESH_TOKEN_EXPIRY,
            'jti' => bin2hex(random_bytes(16))
        ];

        return self::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    }

    public static function verify($token) {
        return self::decode($token, JWT_SECRET, JWT_ALGORITHM);
    }
}
