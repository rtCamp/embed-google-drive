/**
 * WordPress dependencies
 */
const { test, expect } = require('@wordpress/e2e-test-utils-playwright')

test.describe('Embeds google sheet sharing URL preview test', () => {
	test.afterEach(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts()
	})
	test('Able to add the sheet link and preview should be visible', async ({
		page,
		admin
	}) => {
		await admin.createNewPost()

		await page.type(
			'.editor-post-title__input',
			'Test gooogle sheet sharing URL preview'
		)

		await page.click('role=button[name="Add block"i]')

		await page.type('#components-search-control-0', 'embed')

		await page.click('role=option[name="Embed"i]')

		await page
			.locator("input[placeholder='Enter URL to embed here…']")
			.fill(
				'https://docs.google.com/spreadsheets/d/1xdGNelhX35e37ye0v3TwohR366bPS7hNQ_F6Ea0B5mE/edit#'
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
