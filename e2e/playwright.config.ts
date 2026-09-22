import { defineConfig, devices } from "@playwright/test"

// Запускается в контейнере e2e (dev/docker-compose.yml): сайт доступен по имени сервиса nginx
export default defineConfig({
    testDir: "./tests",
    fullyParallel: false,
    workers: 1,
    timeout: 60_000,
    reporter: [["list"], ["html", { open: "never" }]],
    use: {
        baseURL: process.env.CMS_TEST_URL ?? "http://nginx",
        trace: "retain-on-failure",
        screenshot: "only-on-failure",
        locale: "ru-RU",
    },
    projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"], viewport: { width: 1440, height: 900 } } }],
})
