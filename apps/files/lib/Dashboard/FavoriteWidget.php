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

class FavoriteWidget implements IIconWidget, IAPIWidget, IAPIWidgetV2, IButtonWidget {
	public function __construct(
		private readonly IL10N             $l10n,
		private readonly IURLGenerator     $urlGenerator,
		private readonly IMimeTypeDetector $mimeTypeDetector,
		private readonly IUserManager      $userManager,
		private readonly ITagManager       $tagManager,
		private readonly IRootFolder       $rootFolder,
		private readonly IPreview          $previewManager,
	) {

	}

	public function getId(): string {
		return Application::APP_ID.'-favorites';
	}

	public function getTitle(): string {
		return $this->l10n->t('Favorite files');
	}

	public function getOrder(): int {
		return 0;
	}

	public function getIconClass(): string {
		return 'icon-star-dark';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->imagePath('files', 'app-favorite.svg')
		);
	}

	public function getUrl(): ?string {
		return $this->urlGenerator->getAbsoluteURL('index.php/apps/files/favorites');
	}

	public function load(): void {
		return;
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
