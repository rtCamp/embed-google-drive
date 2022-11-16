/**
 * WordPress dependencies
 */
const { test, expect } = require('@wordpress/e2e-test-utils-playwright')

test.describe('Embeds google doc Private preview test', () => {
	test.afterEach(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts()
	})
	test('Able to add the doc link and Preview should not visible', async ({
		page,
		admin
	}) => {
		await admin.createNewPost()

		await page.type(
			'.editor-post-title__input',
			'Test gooogle doc Private URL preview'
		)

		await page.click('role=button[name="Add block"i]')

		await page.type('#components-search-control-0', 'embed')

		await page.click('role=option[name="Embed"i]')

		await page
			.locator("input[placeholder='Enter URL to embed here…']")
			.fill(
				'https://docs.google.com/document/d/1VecECeu8nHIYW2J1VaGN3C3NF1Iz770WCqe--Bd9Yag/edit'
			)

		await page.click('role=button[name="Embed"i] >> nth=1');

    page.on('console', msg => {
			if (msg.type() === 'error') console.log(msg.text)
		})

		await page.waitForTimeout(3000);
		expect(
			page.locator("div[class='components-placeholder__instructions']")
		).toHaveText('Sorry, this content could not be embedded.')
	})
})
