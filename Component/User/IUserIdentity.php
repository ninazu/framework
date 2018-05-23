<?php

namespace vendor\ninazu\framework\Component\User;

interface IUserIdentity {

	public function getRole();

	public function setRole($role);
}