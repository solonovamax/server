<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Core\Controller;

use OC\Authentication\TwoFactorAuth\ProviderManager;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserManager;

class TwoFactorApiController extends \OCP\AppFramework\OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private ProviderManager $tfManager,
		private IRegistry $tfRegistry,
		private IUserManager $userManager,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get two factor provider states
	 *
	 * @param array<string> $users collection of system user ids
	 * 
	 * @return DataResponse<Http::STATUS_OK, array{userId: array{providerId: bool}}>
	 *
	 * 200: user/provider states
	 */
	#[PublicPage]
	#[ApiRoute(verb: 'POST', url: '/state', root: '/twofactor')]
	public function state(array $users = []): DataResponse {
		$states = [];
		foreach ($users as $userId) {
			$userObject = $this->userManager->get($userId);
			if ($userObject !== null) {
				$states[$userId] = $this->tfRegistry->getProviderStates($userObject);
			}
		}

		return new DataResponse($states);
	}

	/**
	 * Enable two factor providers
	 *
	 * @param array<string:array<string>> $users collection of system user ids and provider ids
	 * 
	 * @return DataResponse<Http::STATUS_OK, array{userId: array{providerId: bool}}>
	 *
	 * 200: user/provider states
	 */
	#[PublicPage]
	#[ApiRoute(verb: 'POST', url: '/enable', root: '/twofactor')]
	public function enable(array $users = []): DataResponse {
		$states = [];
		foreach ($users as $userId => $providers) {
			$userObject = $this->userManager->get($userId);
			if ($userObject !== null) {
				if (is_array($providers)) {
					foreach ($providers as $providerId) {
						$this->tfManager->tryEnableProviderFor($providerId, $userObject);
					}
				}
				$states[$userId] = $this->tfRegistry->getProviderStates($userObject);
			}
		}

		return new DataResponse($states);
	}

	/**
	 * Disable two factor providers
	 *
	 * @param array<string:array<string>> $users collection of system user ids and provider ids
	 * 
	 * @return DataResponse<Http::STATUS_OK, array{userId: array{providerId: bool}}>
	 *
	 * 200: user/provider states
	 */
	#[PublicPage]
	#[ApiRoute(verb: 'POST', url: '/disable', root: '/twofactor')]
	public function disable(array $users = []): DataResponse {
		$states = [];
		foreach ($users as $userId => $providers) {
			$userObject = $this->userManager->get($userId);
			if ($userObject !== null) {
				if (is_array($providers)) {
					foreach ($providers as $providerId) {
						$this->tfManager->tryDisableProviderFor($providerId, $userObject);
					}
				}
				$states[$userId] = $this->tfRegistry->getProviderStates($userObject);
			}
		}

		return new DataResponse($states);
	}

}
