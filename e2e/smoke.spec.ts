import { expect, test, type Page } from '@playwright/test';

/**
 * ページ上で発生したコンソールエラーと未処理例外を集める。
 * Vue のマウント失敗（画面が真っ白／真っ黒になるやつ）はここに出る。
 */
const collectPageErrors = (page: Page): string[] => {
    const errors: string[] = [];

    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(`console.error: ${message.text()}`);
        }
    });

    page.on('pageerror', (error) => {
        errors.push(`pageerror: ${error.message}`);
    });

    page.on('requestfailed', (request) => {
        errors.push(`requestfailed: ${request.url()} (${request.failure()?.errorText})`);
    });

    return errors;
};

/** 一覧に並ぶレシピカードのリンク（「レシピを投稿」ボタンなどを拾わないよう画像付きに限定する） */
const recipeCards = (page: Page) => page.locator('a[href*="/recipes/"]:has(img)');

const login = async (page: Page) => {
    await page.goto('/login');
    await page.getByLabel('Email address').fill('test@example.com');
    await page.getByLabel('Password', { exact: true }).fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();
    await page.waitForURL('**/dashboard');
};

test('ゲストでもTopのみんなのレシピが描画される', async ({ page }) => {
    const errors = collectPageErrors(page);

    await page.goto('/');

    await expect(page.getByRole('heading', { name: '今日は何を作る？' })).toBeVisible();
    await expect(recipeCards(page).first().locator('img')).toBeVisible();

    expect(errors).toEqual([]);
});

test('Topのタブを切り替えられる', async ({ page }) => {
    const errors = collectPageErrors(page);

    await page.goto('/');

    // ゲストのフォロー中タブはログイン導線が出る
    await page.getByRole('button', { name: 'フォロー中' }).click();
    await expect(page.getByText('ログインすると、フォロー中のユーザーのレシピが並びます。')).toBeVisible();

    // みんなのレシピに戻せる
    await page.getByRole('button', { name: 'みんなのレシピ' }).click();
    await expect(recipeCards(page).first().locator('img')).toBeVisible();

    expect(errors).toEqual([]);
});

test('ログインするとフォロー中タブにレシピが並ぶ', async ({ page }) => {
    const errors = collectPageErrors(page);

    await login(page);
    await page.goto('/?tab=following');

    // シードで test user は3人フォローしており、そのレシピが存在する
    await expect(recipeCards(page).first().locator('img')).toBeVisible();

    expect(errors).toEqual([]);
});

test('ゲストでもレシピ一覧が描画される', async ({ page }) => {
    const errors = collectPageErrors(page);

    await page.goto('/recipes');

    await expect(page.getByRole('heading', { name: 'レシピ', exact: true })).toBeVisible();
    // シード済みのレシピカードが並んでいること
    await expect(recipeCards(page).first().locator('img')).toBeVisible();
    await expect(page.getByText('まだレシピがありません。')).toHaveCount(0);

    expect(errors).toEqual([]);
});

test('ゲストでもレシピ詳細が描画される', async ({ page }) => {
    const errors = collectPageErrors(page);

    await page.goto('/recipes');
    await recipeCards(page).first().click();

    await expect(page.getByRole('heading', { name: '材料（' })).toBeVisible();
    await expect(page.getByRole('heading', { name: '作り方' })).toBeVisible();

    expect(errors).toEqual([]);
});

test('ログインするとレシピ投稿フォームが開く', async ({ page }) => {
    const errors = collectPageErrors(page);

    await login(page);
    await page.goto('/recipes/create');

    await expect(page.getByRole('heading', { name: 'レシピを投稿' })).toBeVisible();
    await expect(page.getByLabel('タイトル')).toBeVisible();
    await expect(page.getByRole('button', { name: '投稿する' })).toBeVisible();

    expect(errors).toEqual([]);
});

test('投稿者をフォロー・解除できる', async ({ page }) => {
    const errors = collectPageErrors(page);

    await login(page);

    // 他人のレシピを探す（自分のレシピにはフォローボタンが出ない）
    await page.goto('/recipes');
    for (const card of await recipeCards(page).all()) {
        if (!(await card.innerText()).includes('by Test User')) {
            await card.click();
            break;
        }
    }

    // 「フォロー」「フォロー中」どちらの状態でも同じボタンを掴む
    const toggle = page.getByRole('button', { name: /^フォロー/ });
    // isVisible() は待機しないので、状態を見る前に描画完了を待つ
    await expect(toggle).toBeVisible();

    // 直前の実行結果に関わらず未フォローから始める
    if ((await toggle.innerText()).includes('フォロー中')) {
        await toggle.click();
        await expect(toggle).not.toContainText('フォロー中');
    }

    await toggle.click();
    await expect(toggle).toContainText('フォロー中');

    await toggle.click();
    await expect(toggle).not.toContainText('フォロー中');

    expect(errors).toEqual([]);
});

test('お気に入りを登録して一覧に出し、解除できる', async ({ page }) => {
    const errors = collectPageErrors(page);

    await login(page);
    await page.goto('/recipes');

    const title = await recipeCards(page).first().locator('h2').innerText();
    await recipeCards(page).first().click();

    // 「お気に入り」「お気に入り解除」どちらの状態でも同じボタンを掴む
    const toggle = page.getByRole('button', { name: /^お気に入り/ });
    // isVisible() は待機しないので、状態を見る前に描画完了を待つ
    await expect(toggle).toBeVisible();

    // 直前の実行結果に関わらず未登録から始める
    if ((await toggle.innerText()).includes('解除')) {
        await toggle.click();
        await expect(toggle).not.toContainText('解除');
    }

    await toggle.click();
    await expect(toggle).toContainText('解除');

    await page.goto('/favorites');
    await expect(page.getByRole('heading', { name: 'お気に入り' })).toBeVisible();
    await expect(page.getByText(title, { exact: true })).toBeVisible();

    expect(errors).toEqual([]);
});
