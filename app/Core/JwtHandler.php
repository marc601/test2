<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler {
    protected $jwt_secret;

    public function __construct() {
        $config = require __DIR__ . '/../../config/jwt.php';
        $this->jwt_secret = $config['secret'];
    }

    public function encode($data) {
        $token_payload = [
            'iat' => time(), // Issued at: time when the token was generated
            'exp' => time() + 3600, // Expire
            'data' => $data
        ];
        return JWT::encode($token_payload, $this->jwt_secret, 'HS256');
    }

    public function decode($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwt_secret, 'HS256'));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}