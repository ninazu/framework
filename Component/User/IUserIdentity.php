<?php

namespace vendor\ninazu\framework\Component\User;

interface IUserIdentity {

	/**
	 * @return int
	 */
	public function getRole(): ?int;

	/**
	 * @param int $role
	 *
	 * @return $this
	 */
	public function setRole(int $role): IUserIdentity;

	/**
	 * @return int
	 */
	public function getId(): int;

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public function setId(int $id): IUserIdentity;
}