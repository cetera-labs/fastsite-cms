import { test, expect, login, loginAsAdmin } from "./support"

test("вход в админку и выход", async ({ page, errors }) => {
    await loginAsAdmin(page)
    await expect(page.getByText("Добро пожаловать", { exact: true })).toBeVisible()

    await page.getByText("Выход", { exact: true }).click()
    await expect(page.locator("input[name=login]")).toBeVisible()
    await expect(page.getByText("Навигация", { exact: true })).toHaveCount(0)
})

test("неверный пароль не пускает в админку", async ({ page, errors }) => {
    await login(page, "wrong-password")
    await page.waitForTimeout(2000)
    await expect(page.locator("input[name=login]")).toBeVisible()
    await expect(page.getByText("Навигация", { exact: true })).toHaveCount(0)
})
