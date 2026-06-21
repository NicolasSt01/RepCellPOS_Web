import { test, expect } from '@playwright/test';

test.describe('Flujo completo: orden de trabajo, ticket y correo', () => {
  test.setTimeout(120000);

  const ts = Date.now();
  const testEmail = 'nicolas_salastrevino@hotmail.com';

  test('Creación de orden de trabajo, ticket de impresión y envío de correo', async ({ page }) => {
    // ── 0. Cleanup database from previous runs ──
    await page.goto('/__e2e/cleanup');

    // ── 1. Registrar tenant ──
    await page.goto('/register');
    await page.fill('#business_name', `Taller OT ${ts}`);
    await page.fill('#business_phone', '+52 55 1111 1111');
    await page.fill('#admin_name', 'Admin OT');
    await page.fill('#admin_email', `adminot${ts}@example.com`);
    await page.fill('#admin_password', 'Password123');
    await page.fill('#admin_password_confirmation', 'Password123');
    await page.getByRole('button', { name: /Crear mi taller/i }).click();
    await page.waitForURL(/\/dashboard/, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Panel de Control');

    // ── 2. Configurar SMTP del tenant ──
    // Con MAIL_MAILER=log los valores SMTP se ignoran (email → archivo)
    // Con MAIL_MAILER=smtp usar credenciales reales (ej. Gmail)
    await page.goto('/settings/company');
    await page.fill('#mail_host', 'smtp.example.com');
    await page.fill('#mail_port', '587');
    await page.fill('#mail_username', 'test@example.com');
    await page.fill('#mail_password', 'dummy_password');
    await page.fill('#mail_from_address', 'notificaciones@taller.com');
    await page.fill('#mail_from_name', 'Mi Taller');
    await page.getByRole('button', { name: /Guardar cambios/i }).click();
    await page.waitForURL(/\/settings/, { timeout: 5000 });
    await expect(page.getByText(/actualizados correctamente/i)).toBeVisible();

    // ── 3. Crear orden de trabajo ──
    await page.goto('/work_orders/create');

    // ---- Step 1: Cliente ----
    // Switch to "Nuevo Cliente" mode
    await page.getByRole('button', { name: /Nuevo Cliente/i }).click();

    // Fill client form (fields use x-model, select by x-model attribute)
    await page.locator('[x-model="newClient.name"]').fill('Juan Pérez');
    await page.locator('[x-model="newClient.phone"]').fill('+52 55 1234 5678');
    await page.locator('[x-model="newClient.email"]').fill(testEmail);
    await page.locator('[x-model="newClient.notification_preference"]').selectOption('email');

    // Click "Guardar y Seleccionar Cliente"
    await page.getByRole('button', { name: /Guardar y Seleccionar Cliente/i }).click();

    // Wait for client to save and selectedClient to appear
    await expect(page.locator('.rounded-lg.border').filter({ hasText: 'Juan Pérez' })).toBeVisible();

    // Next step
    await page.getByRole('button', { name: /Siguiente/i }).click();

    // ---- Step 2: Equipo ----
    await page.locator('[x-model="device_brand"]').fill('Apple');
    await page.locator('[x-model="device_model"]').fill('iPhone 15 Pro Max');
    await page.locator('[x-model="device_serial"]').fill('SN123456789');
    await page.locator('[x-model="device_imei"]').fill('IMEI9876543210');
    await page.getByRole('button', { name: /Siguiente/i }).click();
    await page.waitForTimeout(300);

    // ---- Step 3: Problema ----
    await page.locator('[x-model="problem_description"]').fill('Pantalla estrellada, no funciona el touch. También tiene la batería inflada.');
    await page.getByRole('button', { name: /Siguiente/i }).click();
    await page.waitForTimeout(300);

    // ---- Step 4: Seguridad ----
    // Submit via the Alpine form: click the submit button
    await page.getByRole('button', { name: /Crear Orden de Trabajo/i }).click();

    // Wait for redirect to print page
    await page.waitForURL(/\/work_orders\/\d+\/print/, { timeout: 10000 });
    await expect(page.getByText(/COMPROBANTE DE RECEPCI/i)).toBeVisible();
    await expect(page.getByText('Apple')).toBeVisible();
    await expect(page.getByText('iPhone 15 Pro Max')).toBeVisible();
    await expect(page.getByText('Pantalla estrellada')).toBeVisible();
    await expect(page.getByText('Juan Pérez')).toBeVisible();

    // ── 5. Verificar que la OT aparece en el listado ──
    await page.goto('/work_orders');
    await expect(page.getByText('Apple')).toBeVisible();
    await expect(page.getByText('iPhone 15 Pro Max')).toBeVisible();
    await expect(page.getByText('Juan Pérez')).toBeVisible();

    // ── 6. Verificar envío de correo ──
    // Con MAIL_MAILER=log el contenido queda en storage/logs/laravel.log
    // Con MAIL_MAILER=smtp se envía realmente por Gmail
    // Leemos el log para detectar el método usado
    const logContent = await page.evaluate(async () => {
      const response = await fetch('/__e2e/read-log');
      if (response.ok) return await response.text();
      return null;
    });

    if (logContent) {
      // Si el log contiene el contenido del email → MAIL_MAILER=log
      // Si NO contiene contenido del email y NO hay error SMTP → MAIL_MAILER=smtp (éxito)
      const isLogMailer = logContent.includes('Orden de Trabajo');
      const hasSmtpError = /Error enviando correo de OT|Connection could not be established/i.test(logContent);

      if (isLogMailer) {
        expect(logContent).toContain('Orden de Trabajo');
        expect(logContent).toContain('OT-00001');
        expect(logContent).toContain('Apple iPhone 15 Pro Max');
        expect(logContent).toContain('Pantalla estrellada');
        console.log('[E2E] ✅ Email verificado en log (MAIL_MAILER=log)');
      } else {
        // Modo SMTP — verificar que no hubo error
        if (hasSmtpError) {
          console.error('[E2E] ❌ Error SMTP: ' + logContent.match(/Error enviando correo de OT[^\n]*/)?.[0]);
        }
        expect(hasSmtpError).toBeFalsy();
        if (!hasSmtpError) {
          console.log('[E2E] ✅ Email enviado por SMTP sin errores');
        }
      }
    }

    // ── 7. Crear cotización, enviarla y aprobarla desde tracking ──
    // Navigate to work order show page
    await page.goto('/work_orders');
    await page.getByRole('link', { name: 'Ver' }).first().click();
    await page.waitForURL(/\/work_orders\/\d+$/, { timeout: 5000 });

    // Click "Crear cotización" link
    await page.getByRole('link', { name: /[Cc]re[ae]r cotizaci[oó]n/i }).click();
    await page.waitForURL(/\/work_orders\/\d+\/quote/, { timeout: 5000 });

    // Click "Servicio" type
    await page.getByRole('button', { name: /[Ss]ervicio/i }).click();
    await page.waitForTimeout(300);

    // Fill service item fields
    await page.locator('[x-model="itemDescription"]').fill('Mano de obra por cambio de pantalla');
    await page.locator('[x-model="itemQuantity"]').fill('1');
    await page.locator('[x-model="itemPrice"]').fill('350');

    // Submit the item
    await page.getByRole('button', { name: /Agregar a la cotizaci[oó]n/i }).click();
    await page.waitForTimeout(500);

    // Send quote to client (redirects to work order show page)
    await page.getByRole('button', { name: /Enviar al cliente/i }).click();
    await page.waitForURL(/\/work_orders\/\d+$/, { timeout: 5000 });

    // Get the tracking URL from the print page (it's rendered there)
    const workOrderId = page.url().match(/\/work_orders\/(\d+)/)?.[1] ?? '1';
    await page.goto(`/work_orders/${workOrderId}/print`);
    await expect(page.getByText(/COMPROBANTE DE RECEPCI/i)).toBeVisible();
    const trackingUrl = await page.evaluate(() => {
      const body = document.body.innerText;
      // Look for the full URL pattern in the print page
      const match = body.match(/https?:\/\/[^\s]+\/seguimiento\/[a-z0-9]+/i);
      return match ? match[0] : null;
    });
    expect(trackingUrl).not.toBeNull();

    if (trackingUrl) {
      // Navigate to the public tracking page
      await page.goto(trackingUrl);
      await expect(page.getByText(/Seguimiento de Orden/i)).toBeVisible();

      // Verify quote section is visible
      await expect(page.getByText('Mano de obra por cambio de pantalla')).toBeVisible();
      await expect(page.getByText('$350.00').first()).toBeVisible();

      // Approve the quote
      page.once('dialog', dialog => dialog.accept());
      await page.getByRole('button', { name: /Aprobar Cotizaci[oó]n/i }).click();

      // Verify approval message
      await expect(page.getByText(/Cotización aprobada/i).first()).toBeVisible();
    }
  });
});
