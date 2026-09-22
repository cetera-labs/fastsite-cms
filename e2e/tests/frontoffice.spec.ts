import { test, expect } from "./support"

test("главная страница сайта открывается", async ({ page, errors }) => {
    const response = await page.goto("/")
    expect(response?.status()).toBe(200)
    await expect(page.locator("body")).not.toContainText(/Fatal error|Warning:|Stack trace/)
})
