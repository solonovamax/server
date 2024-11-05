<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Contacts\ContactsMenu\Actions;

use OCP\Contacts\ContactsMenu\IAction;

/**
 * @since 31.0.0
 */
class JavascriptAction implements IAction {
	public const TYPE = 'JavascriptAction';

	private string $icon = '';
	private string $name = '';
	private int $priority = 10;
	private string $appId = '';
	private string $hook = '';

	public function setIcon(string $icon) {
		$this->icon = $icon;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function setPriority(int $priority) {
		$this->priority = $priority;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	public function setAppId(string $appId) {
		$this->appId = $appId;
	}

	public function getAppId(): string {
		return $this->appId;
	}

	public function getHook(): string {
		return $this->hook;
	}

	public function setHook(string $hook): void {
		$this->hook = $hook;
	}

	function jsonSerialize() {
		return [
			'type' => self::TYPE,
			'title' => $this->name,
			'icon' => $this->icon,
			'appId' => $this->appId,
			'hook' => $this->hook,
		];
	}
}
