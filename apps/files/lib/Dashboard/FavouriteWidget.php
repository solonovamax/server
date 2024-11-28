<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files\Dashboard;

use OCA\Files\AppInfo\Application;
use OCP\Dashboard\IAPIWidget;
use OCP\Dashboard\IAPIWidgetV2;
use OCP\Dashboard\IButtonWidget;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IWidget;
use OCP\Dashboard\Model\WidgetButton;
use OCP\Dashboard\Model\WidgetItem;
use OCP\Dashboard\Model\WidgetItems;
use OCP\Files\IMimeTypeDetector;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use OCP\IPreview;
use OCP\ITagManager;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;

class FavouriteWidget implements IWidget, IIconWidget, IAPIWidget, IAPIWidgetV2, IButtonWidget {
	private IUserSession $userSession;
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private IMimeTypeDetector $mimeTypeDetector;
	private IUserManager $userManager;
	private ITagManager $tagManager;
	private IRootFolder $rootFolder;
	private IPreview $previewManager;
	public const FAVORITE_LIMIT = 50;

	public function __construct(
		IUserSession $userSession,
		IL10N $l10n,
		IURLGenerator $urlGenerator,
		IMimeTypeDetector $mimeTypeDetector,
		IUserManager $userManager,
		ITagManager $tagManager,
		IRootFolder $rootFolder,
		IPreview $previewManager,
	) {
		$this->userSession = $userSession;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->mimeTypeDetector = $mimeTypeDetector;
		$this->userManager = $userManager;
		$this->tagManager = $tagManager;
		$this->rootFolder = $rootFolder;
		$this->previewManager = $previewManager;
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getTitle(): string {
		return $this->l10n->t('Favorites');
	}

	public function getOrder(): int {
		return 0;
	}

	public function getIconClass(): string {
		return 'icon-files-dark';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('files', 'app-dark.svg'));
	}

	public function getUrl(): ?string {
		return null;
	}

	public function load(): void {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return;
		}
		return;
		//Util::addScript(Application::APP_ID, 'recommendations-dashboard');
	}

	public function getItems(string $userId, ?string $since = null, int $limit = 7): array {
		$user = $this->userManager->get($userId);

		if (!$user) {
			return [];
		}
		$tags = $this->tagManager->load('files', [], false, $userId);
		$favorites = $tags->getFavorites();
		if (empty($favorites)) {
			return [];
		} elseif (isset($favorites[self::FAVORITE_LIMIT])) {
			return [];
		}
		$favoriteNodes = [];
		$userFolder = $this->rootFolder->getUserFolder($userId);
		foreach ($favorites as $favorite) {
			$node = $userFolder->getFirstNodeById($favorite);
			if ($node) {
				$url = $this->urlGenerator->linkToRouteAbsolute(
					'files.viewcontroller.showFile', ['fileid' => $node->getId()]
				);
				if ($this->previewManager->isAvailable($node)) {
					$icon = $this->urlGenerator->linkToRouteAbsolute('core.Preview.getPreviewByFileId', [
						'x' => 256,
						'y' => 256,
						'fileId' => $node->getId(),
						'c' => $node->getEtag(),
					]);
				} else {
					$icon = $this->urlGenerator->getAbsoluteURL(
						$this->mimeTypeDetector->mimeTypeIcon($node->getMimetype())
					);
				}
				$favoriteNodes[] = new WidgetItem(
					$node->getName(),
					'',
					$url,
					$icon,
					(string)$node->getCreationTime()
				);
			}
		}

		return $favoriteNodes;
	}

	public function getItemsV2(string $userId, ?string $since = null, int $limit = 7): WidgetItems {
		$items = $this->getItems($userId, $since, $limit);
		return new WidgetItems(
			$items,
			count($items) === 0 ? $this->l10n->t('No favorites') : '',
		);
	}

	public function getWidgetButtons(string $userId): array {
		return [
			new WidgetButton(
				WidgetButton::TYPE_MORE,
				$this->urlGenerator->getAbsoluteURL('index.php/apps/files/favorites'),
				$this->l10n->t('More favorites')
			),
		];
	}
}
