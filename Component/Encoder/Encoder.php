<?php

namespace vendor\ninazu\framework\Component\Encoder;

use vendor\ninazu\framework\Core\BaseComponent;

class Encoder extends BaseComponent implements IEncoder {

	protected $key;

	protected $cipher = "AES-128-CBC";

	protected $option = 0;

	public function hasKey(): bool {
		return !empty($this->key);
	}

	public function encode(string $data): string {
		$ivLen = openssl_cipher_iv_length($this->cipher);
		$iv = openssl_random_pseudo_bytes($ivLen);
		$cipherText = openssl_encrypt($data, $this->cipher, $this->key, $this->option, $iv);

		return base64_encode($iv . $cipherText);
	}

	public function decode(string $data): string {
		$ivLen = openssl_cipher_iv_length($this->cipher);
		$data = base64_decode($data);
		$iv = substr($data, 0, $ivLen);
		$data = substr($data, $ivLen);

		return openssl_decrypt($data, $this->cipher, $this->key, $this->option, $iv);
	}
}