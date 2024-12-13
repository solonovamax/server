<!--
  - SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="system-tags-settings"
		class="section">
		<h2 class="inlineblock">
			{{ t('settings', 'SystemTags') }}
		</h2>

		<p class="settings-hint">
			{{ t('settings', 'Enable or disable system tag creation for non-admin users.') }}
		</p>

		<NcCheckboxRadioSwitch type="switch"
			:checked.sync="initialSystemTagsEnabledByDefault"
			@update:checked="onSystemTagsDefaultChange">
			{{ t('settings', 'Enable') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { showError } from '@nextcloud/dialogs'

import { validateBoolean } from '../../utils/validate.js'
import logger from '../../logger.ts'

import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

const systemTagsEnabledByDefault = loadState('settings', 'systemTagsEnabledByDefault', true)

export default {
	name: 'SystemTagsSettings',

	components: {
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			initialSystemTagsEnabledByDefault: systemTagsEnabledByDefault,
		}
	},

	methods: {
		async onSystemTagsDefaultChange(isEnabled) {
			if (validateBoolean(isEnabled)) {
				await this.updateSystemTagsDefault(isEnabled)
			}
		},

		async updateSystemTagsDefault(isEnabled) {
			try {
				const responseData = await saveSystemTagsDefault(isEnabled)
				this.handleResponse({
					isEnabled,
					status: responseData.ocs?.meta?.status,
				})
			} catch (e) {
				this.handleResponse({
					errorMessage: t('settings', 'Unable to update systemTags default setting'),
					error: e,
				})
			}
		},

		handleResponse({ isEnabled, status, errorMessage, error }) {
			if (status === 'ok') {
				this.initialSystemTagsEnabledByDefault = isEnabled
			} else {
				showError(errorMessage)
				logger.error(errorMessage, error)
			}
		},
	},
}
</script>
