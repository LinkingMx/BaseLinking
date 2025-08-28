import { chromium } from 'playwright';

(async () => {
    console.log('🧪 TESTING: IT_Boss puede ver documentos que ya aprobó');

    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();

    try {
        // 1. Login como IT_Boss (Armando)
        console.log('1️⃣ Haciendo login como IT_Boss...');
        await page.goto('http://baselinking.test/admin/login');
        await page.fill('input[name="email"]', 'armando@armando.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2000);

        // 2. Ir a Documentation Resource
        console.log('2️⃣ Navegando a Documentation...');
        await page.goto('http://baselinking.test/admin/documentations');
        await page.waitForTimeout(3000);

        // 3. Verificar filtros disponibles para IT_Boss
        console.log('3️⃣ Verificando filtros disponibles...');
        const filtersButton = page.locator('button:has-text("Filtros")');
        if (await filtersButton.isVisible()) {
            await filtersButton.click();
            await page.waitForTimeout(1000);

            // Verificar que el filtro "Aprobados por mí" está disponible
            const approvedByMeFilter = page.locator('text=Aprobados por mí');
            if (await approvedByMeFilter.isVisible()) {
                console.log('✅ Filtro "Aprobados por mí" está disponible');
            } else {
                console.log('❌ Filtro "Aprobados por mí" NO está disponible');
            }

            // Verificar que el filtro "Pendientes de mi aprobación" está disponible
            const pendingFilter = page.locator('text=Pendientes de mi aprobación');
            if (await pendingFilter.isVisible()) {
                console.log('✅ Filtro "Pendientes de mi aprobación" está disponible');
            } else {
                console.log('❌ Filtro "Pendientes de mi aprobación" NO está disponible');
            }
        }

        // 4. Contar documentos visibles
        console.log('4️⃣ Contando documentos visibles...');
        await page.waitForTimeout(2000);

        const documentRows = page.locator('table tbody tr');
        const count = await documentRows.count();
        console.log(`📊 Documentos visibles para IT_Boss: ${count}`);

        // 5. Verificar estados de documentos
        console.log('5️⃣ Analizando estados de documentos...');
        for (let i = 0; i < Math.min(count, 10); i++) {
            const row = documentRows.nth(i);
            const title = await row.locator('td').nth(0).textContent();
            const status = await row.locator('td').nth(1).textContent();
            const creator = await row.locator('td').nth(2).textContent();
            const approver = await row.locator('td').nth(3).textContent();

            console.log(`   📄 ${title?.trim()} | Estado: ${status?.trim()} | Creador: ${creator?.trim()} | Aprobador: ${approver?.trim()}`);
        }

        // 6. Aplicar filtro "Aprobados por mí"
        console.log('6️⃣ Aplicando filtro "Aprobados por mí"...');
        if (await filtersButton.isVisible()) {
            await filtersButton.click();
            await page.waitForTimeout(1000);

            const approvedByMeToggle = page.locator('label:has-text("Aprobados por mí")');
            if (await approvedByMeToggle.isVisible()) {
                await approvedByMeToggle.click();
                await page.waitForTimeout(2000);

                const filteredCount = await documentRows.count();
                console.log(`📊 Documentos aprobados por mí: ${filteredCount}`);

                // Verificar que todos los documentos filtrados fueron aprobados por Armando
                for (let i = 0; i < Math.min(filteredCount, 5); i++) {
                    const row = documentRows.nth(i);
                    const approver = await row.locator('td').nth(3).textContent();

                    if (approver?.includes('Armando')) {
                        console.log(`   ✅ Documento ${i + 1}: Aprobado por Armando`);
                    } else {
                        console.log(`   ❌ Documento ${i + 1}: NO aprobado por Armando (${approver})`);
                    }
                }
            }
        }

        console.log('🎉 Test completado exitosamente!');
    } catch (error) {
        console.error('❌ Error durante el test:', error);
    } finally {
        await browser.close();
    }
})();
