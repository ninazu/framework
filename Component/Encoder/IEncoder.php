<?php

namespace vendor\ninazu\framework\Component\Encoder;

interface IEncoder {

	public function encode(string $data): string;

	public function decode(string $data): string;

	public function hasKey(): bool;
}