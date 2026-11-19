import { test, expect, type Locator, type Page } from '@playwright/test';

// La base de l'E2E est la base de dev, qui contient les vraies tâches : chaque test ne manipule
// que des tâches préfixées par `prefix` et les supprime en fin de test.
const prefix = `e2e-${Date.now().toString(36)}`;
let created: string[] = [];

function newTitle(name: string): string {
  const title = `${prefix}-${name}`;
  created.push(title);

  return title;
}

function taskItem(page: Page, title: string): Locator {
  return page
    .locator('[data-test="task-item"]')
    .filter({ has: page.locator('[data-test="task-title"]').getByText(title, { exact: true }) });
}

async function ownTitlesIn(page: Page, section: 'tasks-todo' | 'tasks-done'): Promise<string[]> {
  const titles = await page.locator(`[data-test="${section}"] [data-test="task-title"]`).allTextContents();

  return titles.map((title) => title.trim()).filter((title) => title.startsWith(prefix));
}

async function addTask(page: Page, title: string, typed: string = title): Promise<void> {
  await page.keyboard.type(typed);
  await page.keyboard.press('Enter');
  await expect(taskItem(page, title)).toBeVisible();
}

test.beforeEach(async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="_username"]', 'admin@example.com');
  await page.fill('input[name="_password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page.locator('h1')).toContainText('Tâches');
});

test.afterEach(async ({ page }) => {
  await page.goto('/');
  for (const title of created) {
    const item = taskItem(page, title);
    if (await item.count() > 0) {
      await item.locator('[data-test="task-delete"]').click();
      await expect(item).toHaveCount(0);
    }
  }
  created = [];
});

test('adding tasks from the keyboard keeps the focus and puts the newest on top', async ({ page }) => {
  const input = page.locator('[data-test="task-input"]');
  await expect(input).toBeFocused();

  const first = newTitle('first');
  const second = newTitle('second');
  const third = newTitle('third');
  await addTask(page, first);
  await expect(input).toHaveValue('');
  await expect(input).toBeFocused();
  await addTask(page, second);
  await addTask(page, third, `  ${third}  `);

  expect(await ownTitlesIn(page, 'tasks-todo')).toEqual([third, second, first]);
  await expect(input).toBeFocused();

  await page.keyboard.type('   ');
  await page.keyboard.press('Enter');
  await expect(page.locator('[data-test="task-error"]')).toHaveCount(0);
  expect(await ownTitlesIn(page, 'tasks-todo')).toEqual([third, second, first]);
});

test('checking a task moves it below the open tasks, unchecking brings it back, state survives a reload', async ({ page }) => {
  const older = newTitle('older');
  const newer = newTitle('newer');
  await addTask(page, older);
  await addTask(page, newer);

  await taskItem(page, older).locator('[data-test="task-toggle"]').click();
  await expect(page.locator('[data-test="tasks-done"]').getByText(older, { exact: true })).toBeVisible();
  await expect(taskItem(page, older).locator('[data-test="task-title"]')).toHaveClass(/line-through/);
  expect(await ownTitlesIn(page, 'tasks-todo')).toEqual([newer]);

  await page.reload();
  await expect(taskItem(page, older).locator('[data-test="task-toggle"]')).toBeChecked();
  expect(await ownTitlesIn(page, 'tasks-done')).toEqual([older]);

  await taskItem(page, older).locator('[data-test="task-toggle"]').click();
  await expect(page.locator('[data-test="tasks-todo"]').getByText(older, { exact: true })).toBeVisible();
  expect(await ownTitlesIn(page, 'tasks-todo')).toEqual([newer, older]);
  await expect(taskItem(page, older).locator('[data-test="task-title"]')).not.toHaveClass(/line-through/);
});

test('deleting a task removes it without confirmation, even after a reload', async ({ page }) => {
  const doomed = newTitle('doomed');
  await addTask(page, doomed);

  page.on('dialog', () => {
    throw new Error('Aucune boîte de confirmation ne doit apparaître.');
  });
  await taskItem(page, doomed).locator('[data-test="task-delete"]').click();
  await expect(taskItem(page, doomed)).toHaveCount(0);

  await page.reload();
  await expect(taskItem(page, doomed)).toHaveCount(0);
});
