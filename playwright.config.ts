import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './e2e',
    // 全テストが同じ開発DBと同じテストユーザーを触るため、並列にすると
    // お気に入り/フォローのトグルが互いの状態を壊す。直列で走らせる
    fullyParallel: false,
    workers: 1,
    reporter: [['list']],
    use: {
        // Docker の app コンテナが serve しているアプリを見る
        baseURL: 'http://localhost:8080',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
