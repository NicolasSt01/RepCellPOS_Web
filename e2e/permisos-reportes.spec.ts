import { test, expect } from '@playwright/test';

test.describe('Superadmin: permisos de reportes por plan', () => {
  test.setTimeout(120000);

  const adminEmail = `adminperm${Date.now()}@example.com`;
  const saEmail = 'superadmin@repcellpos.com';

  test('Superadmin desactiva un reporte en el plan y el tenant lo pierde', async ({ page }) => {
    // 1. Crear superadmin via e2e route
    await page.goto('/__e2e/create-superadmin');
    await expect(page.locator('pre')).toContainText('superadmin_created');

    // 2. Registrar tenant nuevo
    const ts = Date.now();
    await page.goto('/register');
    await page.fill('#business_name', `Taller Test ${ts}`);
    await page.fill('#business_phone', '+52 55 1111 1111');
    await page.fill('#admin_name', 'Admin Test');
    await page.fill('#admin_email', adminEmail);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');
    await page.getByRole('button', { name: /Crear mi taller/i }).click();
    await page.waitForURL(/\/dashboard/, { timeout: 15000 });

    // 3. Activar plan (salir del trial) para que las features del plan se apliquen
    await page.goto(`/__e2e/activate-plan?email=${adminEmail}&plan=premium`);
    await expect(page.locator('pre')).toContainText('plan_activated');

    // 4. Verificar que el tenant ve todos los reportes (por defecto todos activos)
    await page.goto('/reportes');
    await expect(page.locator('text=Ventas por Período').first()).toBeVisible();
    await expect(page.locator('text=Top Productos').first()).toBeVisible();

    // 5. Desloguearse (submit logout form via JS)
    await page.goto('/login');
    await page.evaluate(async () => {
      const token = (document.querySelector('input[name="_token"]') as any)?.value;
      await fetch('/logout', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '_token=' + encodeURIComponent(token) });
    });
    await page.goto('/login');

    // 6. Login como superadmin
    await page.fill('input[name="email"]', saEmail);
    await page.fill('input[name="password"]', 'password');
    await page.getByRole('button', { name: /Iniciar sesión/i }).click();
    await page.waitForURL(/\/admin/, { timeout: 15000 });

    // 6. Ir a la lista de planes y editar Premium
    await page.goto('/admin/plans');
    await expect(page.locator('h1')).toContainText('Planes');
    // Direct nav to Premium edit (id=3, seeded after Basic=1, Pro=2)
    await page.goto('/admin/plans/3/edit');
    await expect(page.locator('h1')).toContainText('Editar Plan');

    // 7. Desactivar "Reporte: Ventas por Período"
    await page.getByRole('checkbox', { name: 'Reporte: Ventas por Período' }).uncheck({ force: true });
    await page.getByRole('button', { name: /Guardar Cambios/i }).click();
    await expect(page.locator('text=Plan actualizado')).toBeVisible();

    // 8. Cerrar sesión superadmin
    await page.evaluate(async () => {
      const token = (document.querySelector('input[name="_token"]') as any)?.value;
      await fetch('/logout', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '_token=' + encodeURIComponent(token) });
    });
    await page.goto('/login');

    // 9. Login como tenant
    await page.fill('input[name="email"]', adminEmail);
    await page.fill('input[name="password"]', 'Password123');
    await page.getByRole('button', { name: /Iniciar sesión/i }).click();
    await page.waitForURL(/\/dashboard/, { timeout: 15000 });

    // 10. Ir a reportes — Ventas por Período no debe aparecer
    await page.goto('/reportes');
    await expect(page.locator('text=Ventas por Período').first()).not.toBeVisible();

    // 11. Acceder directamente al reporte debe dar error
    const resp = await page.goto('/reportes/ventas-periodo?date_from=2026-01-01&date_to=2026-12-31');
    expect(resp?.status()).toBe(403);
  });
});
