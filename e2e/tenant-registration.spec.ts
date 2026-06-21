import { test, expect } from '@playwright/test';

test.describe('Registro de tenant desde landing page', () => {

  const uniqueId = Date.now();
  const testTenant = {
    business_name: `Taller Test ${uniqueId}`,
    business_phone: '+52 55 1234 5678',
    admin_name: 'Juan Pérez',
    admin_email: `juan${uniqueId}@example.com`,
    admin_password: 'Password123',
  };

  test('1. Landing page carga y muestra el CTA de registro', async ({ page }) => {
    await page.goto('/');

    await expect(page.locator('h1')).toContainText('sistema que tu taller');
    await expect(page.getByRole('link', { name: /Comenzar gratis/i }).first()).toBeVisible();
  });

  test('2. Click en "Comenzar gratis" lleva al formulario de registro', async ({ page }) => {
    await page.goto('/');

    await page.getByRole('link', { name: /Comenzar gratis/i }).first().click();

    await expect(page).toHaveURL(/\/register/);
    await expect(page.locator('h2')).toContainText('Crea tu cuenta');
  });

  test('3. El formulario de registro muestra todos los campos requeridos', async ({ page }) => {
    await page.goto('/register');

    await expect(page.locator('#business_name')).toBeVisible();
    await expect(page.locator('#business_phone')).toBeVisible();
    await expect(page.locator('#admin_name')).toBeVisible();
    await expect(page.locator('#admin_email')).toBeVisible();
    await expect(page.locator('#admin_password')).toBeVisible();
    await expect(page.locator('#admin_password_confirmation')).toBeVisible();

    await expect(page.getByRole('button', { name: /Crear mi taller/i })).toBeVisible();
  });

  test('4. Registro exitoso redirige al dashboard con mensaje de bienvenida', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#business_name', testTenant.business_name);
    await page.fill('#business_phone', testTenant.business_phone);
    await page.fill('#admin_name', testTenant.admin_name);
    await page.fill('#admin_email', testTenant.admin_email);
    await page.fill('#admin_password', testTenant.admin_password);
    await page.fill('#admin_password_confirmation', testTenant.admin_password);

    await page.getByRole('button', { name: /Crear mi taller/i }).click();

    await page.waitForURL(/\/dashboard/, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Panel de Control');
    await expect(page.getByText(testTenant.admin_name).first()).toBeVisible();
    await expect(page.getByText(testTenant.business_name).first()).toBeVisible();
  });

  test('5. Muestra error al intentar registrar con email duplicado', async ({ page }) => {
    const email = `dup${uniqueId}@example.com`;

    await page.goto('/register');

    await page.fill('#business_name', `Primer Taller ${uniqueId}`);
    await page.fill('#business_phone', '+52 55 1111 1111');
    await page.fill('#admin_name', 'Primer Admin');
    await page.fill('#admin_email', email);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');
    await page.getByRole('button', { name: /Crear mi taller/i }).click();
    await page.waitForURL(/\/dashboard/, { timeout: 10000 });

    await page.context().clearCookies();
    await page.goto('/register');

    await page.fill('#business_name', `Segundo Taller ${uniqueId}`);
    await page.fill('#business_phone', '+52 55 2222 2222');
    await page.fill('#admin_name', 'Segundo Admin');
    await page.fill('#admin_email', email);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');
    await page.getByRole('button', { name: /Crear mi taller/i }).click();

    await expect(page).toHaveURL(/\/register/);
    await expect(page.locator('.bg-red-50')).toContainText(/ya ha sido utilizado|already taken|unique/i);
  });

  test('6. Muestra error si las contraseñas no coinciden', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#business_name', `Taller Pass ${uniqueId}`);
    await page.fill('#business_phone', '+52 55 1111 2222');
    await page.fill('#admin_name', 'María García');
    await page.fill('#admin_email', `maria${uniqueId}@example.com`);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Diferente456');

    await page.getByRole('button', { name: /Crear mi taller/i }).click();

    await expect(page).toHaveURL(/\/register/);
    await expect(page.locator('.bg-red-50')).toContainText(/confirmation|confirmed|coinciden|match/i);
  });

  test('7. Muestra error si falta el nombre del taller', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#business_phone', '+52 55 1111 2222');
    await page.fill('#admin_name', 'Carlos Ruiz');
    await page.fill('#admin_email', `carlos${uniqueId}@example.com`);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');

    await page.evaluate(() => {
      document.querySelector('form').noValidate = true;
    });
    await page.getByRole('button', { name: /Crear mi taller/i }).click();

    await expect(page).toHaveURL(/\/register/);
    await expect(page.locator('.bg-red-50')).toContainText(/requerido|required|business name/i);
  });

  test('8. El formulario de registro es accesible desde la navegacion de la landing', async ({ page }) => {
    await page.goto('/');

    const headerRegisterBtn = page.locator('nav a', { hasText: 'Comenzar gratis' }).first();
    await headerRegisterBtn.click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('9. El link "Iniciar sesion" en el registro lleva al login', async ({ page }) => {
    await page.goto('/register');

    await page.getByRole('link', { name: /Iniciar sesión/i }).click();

    await expect(page).toHaveURL(/\/login/);
  });

  test('10. El tenant creado tiene acceso correcto al dashboard (sidebar con sus secciones)', async ({ page }) => {
    const email = `verificado${uniqueId}@example.com`;
    await page.goto('/register');

    await page.fill('#business_name', `Taller Verificado ${uniqueId}`);
    await page.fill('#business_phone', '+52 55 5555 5555');
    await page.fill('#admin_name', 'Admin Verificado');
    await page.fill('#admin_email', email);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');

    await page.getByRole('button', { name: /Crear mi taller/i }).click();

    await page.waitForURL(/\/dashboard/, { timeout: 10000 });
    const sidebar = page.locator('nav').first();
    await expect(sidebar).toContainText('Operaciones');
    await expect(sidebar).toContainText('Ventas');
    await expect(sidebar).toContainText('Inventario');
    await expect(sidebar).toContainText('Configuración');
    await expect(sidebar).not.toContainText('Superadmin');
    await expect(sidebar).not.toContainText('Plataforma');
  });
});
