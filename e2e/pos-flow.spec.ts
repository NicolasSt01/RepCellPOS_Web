import { test, expect } from '@playwright/test';

test.describe('Flujo completo: productos, inventario y venta', () => {

  test('Alta de productos y venta con verificación de inventario', async ({ page }) => {
    const ts = Date.now();

    // ── 1. Registrar tenant ──
    await page.goto('/register');
    await page.fill('#business_name', `Taller E2E ${ts}`);
    await page.fill('#business_phone', '+52 55 1111 1111');
    await page.fill('#admin_name', 'Admin E2E');
    await page.fill('#admin_email', `admin${ts}@example.com`);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');
    await page.getByRole('button', { name: /Crear mi taller/i }).click();
    await page.waitForURL(/\/dashboard/, { timeout: 10000 });

    // ── 2. Crear categoría ──
    await page.goto('/categories/create');
    await page.fill('#name', 'Repuestos');
    await page.getByRole('button', { name: /Guardar/i }).click();
    await page.waitForURL(/\/categories/, { timeout: 5000 });
    await expect(page.getByText('Repuestos')).toBeVisible();

    // ── 3. Crear producto 1: Pantalla iPhone 11 ──
    await page.goto('/products/create');
    // Step 1: Already on "producto" type, fill name
    await page.fill('#name', 'Pantalla iPhone 11');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    // Step 2: Prices
    await page.fill('#purchase_price', '100');
    await page.fill('#sale_price', '200');
    await page.uncheck('#has_tax');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    // Step 3: Stock
    await page.fill('#stock', '10');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    // Step 4: Save
    await page.getByRole('button', { name: /Guardar artículo/i }).click();
    await page.waitForURL(/\/products/, { timeout: 5000 });
    await expect(page.getByText('Pantalla iPhone 11')).toBeVisible();

    // ── 4. Crear producto 2: Batería iPhone 11 ──
    await page.goto('/products/create');
    await page.fill('#name', 'Batería iPhone 11');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    await page.fill('#purchase_price', '25');
    await page.fill('#sale_price', '50');
    await page.uncheck('#has_tax');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    await page.fill('#stock', '15');
    await page.getByRole('button', { name: /Siguiente paso/i }).click();
    await page.getByRole('button', { name: /Guardar artículo/i }).click();
    await page.waitForURL(/\/products/, { timeout: 5000 });
    await expect(page.getByText('Batería iPhone 11')).toBeVisible();

    // ── 5. Abrir caja con $500 ──
    await page.goto('/cash_registers');
    // Click "Abrir caja" button to open modal
    await page.getByRole('button', { name: /Abrir caja/i }).first().click();
    // Fill opening amount in modal
    await page.locator('#open-modal input[name="opening_amount"]').fill('500');
    // Submit the form inside the modal
    await page.locator('#open-modal button[type="submit"]').click();
    await page.waitForURL(/\/cash_registers/, { timeout: 5000 });
    await expect(page.getByText(/Fondo inicial/i).first()).toBeVisible();

    // ── 6. POS: vender 2 pantallas + 1 batería ──
    await page.goto('/pos');

    // Add 2x "Pantalla iPhone 11" — click twice
    const pantallaCard = page.locator('button', { hasText: 'Pantalla iPhone 11' }).first();
    await pantallaCard.click();
    await pantallaCard.click();

    // Add 1x "Batería iPhone 11"
    await page.locator('button', { hasText: 'Batería iPhone 11' }).first().click();

    // Verify cart shows items
    await expect(page.getByText('Pantalla iPhone 11').first()).toBeVisible();
    await expect(page.getByText('Batería iPhone 11').first()).toBeVisible();

    // Click "Cobrar" — match button containing the total
    await page.getByRole('button', { name: /Cobrar/i, exact: false }).first().click();

    // Verify checkout modal opened
    await expect(page.locator('h2:has-text("Confirmar pago")')).toBeVisible();
    // Total should be 2*200 + 1*50 = 450
    await expect(page.getByText('450.00').first()).toBeVisible();

    // Click "Confirmar pago"
    await page.locator('button:has-text("Confirmar pago")').click();

    // ── 7. Verify success message ──
    await page.waitForURL(/\/pos/, { timeout: 5000 });
    await expect(page.getByText(/Venta #\d+ registrada exitosamente/)).toBeVisible();
    await expect(page.getByText('450.00').first()).toBeVisible();

    // ── 8. Verify stock decreased ──
    await page.goto('/products');
    // Find the table row for Pantalla and check stock
    const pantallaRow = page.locator('tr', { hasText: 'Pantalla iPhone 11' });
    await expect(pantallaRow).toContainText('8');
    // Find the table row for Batería and check stock
    const bateriaRow = page.locator('tr', { hasText: 'Batería iPhone 11' });
    await expect(bateriaRow).toContainText('14');

    // ── 9. Verify sale in history ──
    await page.goto('/sales');
    await expect(page.getByText('450.00').first()).toBeVisible();
  });
});
