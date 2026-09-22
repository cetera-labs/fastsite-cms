import { test, expect, loginAsAdmin } from "./support"

// Разделы дерева «Навигация» на чистой установке (модули ядра)
const sections = [
    "Структура и материалы",
    "Материалы",
    "Меню",
    "Почтовые шаблоны",
    "Темы",
    "Настройки",
    "Типы материалов",
    "Пользователи",
    "Группы пользователей",
    "Журнал",
    "Проверка и ремонт БД",
    "Установленные модули",
]

for (const section of sections) {
    test(`раздел «${section}» открывается`, async ({ page, errors }) => {
        await loginAsAdmin(page)
        await page.locator(".x-tree-node-text", { hasText: new RegExp(`^${section}$`) }).first().click()
        // Раздел открывается вкладкой с тем же названием
        await expect(page.locator(".x-tab", { hasText: section })).toBeVisible()
        // Даём разделу догрузить данные: ошибки запросов соберёт фикстура errors
        await page.waitForLoadState("networkidle")
        await page.screenshot({ path: `test-results/sections/${section}.png` })
    })
}
