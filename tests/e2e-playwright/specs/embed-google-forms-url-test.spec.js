/**
 * WordPress dependencies
 */
const { test, expect } = require('@wordpress/e2e-test-utils-playwright')

test.describe('Embeds google Forms sharing URL preview test', () => {
	test.afterEach(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts()
	})
	test('Able to add the Forms link and preview should be visible', async ({
		page,
		admin
	}) => {
		await admin.createNewPost()

		await page.type(
			'.editor-post-title__input',
			'Test gooogle Forms sharing URL preview'
		)

		await page.click('role=button[name="Add block"i]')

		await page.type('#components-search-control-0', 'embed')

		await page.click('role=option[name="Embed"i]')

		await page
			.locator("input[placeholder='Enter URL to embed here…']")
			.fill(
				'https://docs.google.com/forms/d/1IR-lKwGCcJarNHhPFwIvckT76Z9eNOh5v-1z7Cxet8s/edit'
			)

		await page.click('role=button[name="Embed"i] >> nth=1')

		const frameloc = page.frameLocator(
			"iframe[title='Embedded content from google.com']"
		)

		expect(
			frameloc.locator("a[title='Open the Shared Document']")
		).not.toBe(null)

	})
})
