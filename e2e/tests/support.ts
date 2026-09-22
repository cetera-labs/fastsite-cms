import { test as base, expect, Page } from "@playwright/test"

export const ADMIN_LOGIN = process.env.DEV_ADMIN_LOGIN ?? "admin"
export const ADMIN_PASSWORD = process.env.DEV_ADMIN_PASSWORD ?? "admin"

/**
 * Фикстура errors собирает JS-ошибки страницы и ответы сайта с кодом >= 400;
 * после теста их список должен быть пуст. Внешние ресурсы (логотип fastsite.ru и т.п.)
 * в контейнере могут быть недоступны, поэтому учитываются только запросы к самому сайту.
 */
export const test = base.extend<{ errors: string[] }>({
    errors: async ({ page, baseURL }, use) => {
        const errors: string[] = []
        const own = (url: string) => url.startsWith(baseURL!)
        page.on("pageerror", (e) => errors.push(`JS: ${e.message}`))
        page.on("console", (m) => {
            if (m.type() === "error" && own(m.location().url || baseURL!)) errors.push(`console: ${m.text()}`)
        })
        page.on("response", (r) => {
            if (r.status() >= 400 && own(r.url())) errors.push(`HTTP ${r.status()}: ${r.url()}`)
        })
        await use(errors)
        expect(errors, "ошибки на странице").toEqual([])
    },
})

export { expect }

/** Вход в админку (старый UI, cms/index.php) */
export async function login(page: Page, password = ADMIN_PASSWORD) {
    await page.goto("/cms/index.php")
    await page.locator("input[name=login]").fill(ADMIN_LOGIN)
    await page.locator("input[name=pass]").fill(password)
    await page.getByRole("button", { name: "Вход" }).click()
}

/** Вход и ожидание, пока админка построит интерфейс */
export async function loginAsAdmin(page: Page) {
    await login(page)
    await expect(page.getByText("Навигация", { exact: true })).toBeVisible()
}
