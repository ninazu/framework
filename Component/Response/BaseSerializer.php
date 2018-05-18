<?php

namespace vendor\ninazu\framework\Component\Response;

use vendor\ninazu\framework\Core\BaseComponent;

abstract class BaseSerializer extends BaseComponent {

	abstract public function serialize();
}