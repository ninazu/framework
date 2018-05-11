<?php

namespace vendor\ninazu\framework\Component\Response;

use vendor\ninazu\framework\Core\Component;

abstract class Serializer extends Component {

	abstract public function serialize();
}