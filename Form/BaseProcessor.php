<?php

namespace vendor\ninazu\framework\Form;

abstract class BaseProcessor {

	abstract public function execute(array &$data, $field);
}