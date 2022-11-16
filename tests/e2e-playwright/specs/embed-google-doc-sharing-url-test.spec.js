/**
 * WordPress dependencies
 */
const { test, expect } = require("@wordpress/e2e-test-utils-playwright");

test.describe("Embeds google doc sharing URL preview test", () => {
  test.afterEach(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts()
	})
  test("Able to add the doc link and preview should be visible", async ({
    page,
    admin,
  }) => {
    await admin.createNewPost();

    await page.type(
      ".editor-post-title__input",
      "Test gooogle doc sharing URL preview"
    );

    await page.click('role=button[name="Add block"i]');

    await page.type("#components-search-control-0", "embed");

    await page.click('role=option[name="Embed"i]');

    await page
      .locator("input[placeholder='Enter URL to embed here…']")
      .fill(
        "https://docs.google.com/document/d/1MVE8ufd_52kR95AW4lV_j_5EubCQtWe9ECtRQCCVn6k/edit?usp=sharing"
      );

    await page.click('role=button[name="Embed"i] >> nth=1');

    const frameloc = page.frameLocator(
      "iframe[title='Embedded content from google.com']"
    );

    expect(frameloc.locator("a[title='Open the Shared Document']")).not.toBe(
      null
    );
  });
});
