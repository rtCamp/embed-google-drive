/**
 * WordPress dependencies
 */
const { test, expect } = require('@wordpress/e2e-test-utils-playwright')

test.describe('Embeds google Drawing sharing URL preview test', () => {
	test.afterEach(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts()
	})
	test('Able to add the Drawing link and preview should be visible', async ({
		page,
		admin
	}) => {
		await admin.createNewPost()

		await page.type(
			'.editor-post-title__input',
			'Test gooogle Drawing sharing URL preview'
		)

		await page.click('role=button[name="Add block"i]')

		await page.type('#components-search-control-0', 'embed')

		await page.click('role=option[name="Embed"i]')

		await page
			.locator("input[placeholder='Enter URL to embed here…']")
			.fill(
				'https://docs.google.com/drawings/d/1nG2_dkoqMgi3py41Fs_ETvfvKd0O-S1wAkhwaS3dh8g/edit'
			)

		await page.click('role=button[name="Embed"i] >> nth=1')

		const frameloc = page.frameLocator(
			"iframe[title='Embedded content from google.com']"
		)

		expect(
			frameloc.locator("a[title='Open the Shared Document']")
		).not.toBe(null)

		// Publish a page and validate.

		//Click on publish button
		await page.click('.editor-post-publish-panel__toggle')

		//Double check, click again on publish button
		await page.click('.editor-post-publish-button')

		// A success notice should show up
		page.locator('.components-snackbar')

		await page.click('role=link[name="View Post"i] >> nth=1')

		expect(page.locator("a[title='Open the Shared Document']")).not.toBe(
			null
		)
	})
})
