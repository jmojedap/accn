<?php
namespace App\Libraries;

require_once APPPATH . '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class JWT_Library
{
    public function encode($data, $secret_key)
    {
        return JWT::encode($data, $secret_key,'HS256');
    }
    
    public function decode($jwt, $secret_key, $verify = true)
    {
        return JWT::decode($jwt, $secret_key, $verify);
    }
}
