# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ✅ Mejora del Asistente de Workflows y Reutilización de Plantillas

**🎯 Problema Solucionado:**
El "Asistente de Workflows" era una gran idea pero no estaba bien integrada con el sistema de plantillas de email (`EmailTemplate`).
-   Al "usar una plantilla existente", en realidad copiaba el contenido, perdiendo la reutilización.
-   Al "crear un nuevo email", no generaba una plantilla real, lo que impedía su gestión futura.

**🔧 Solución Implementada:**
Se ha refactorizado por completo la lógica de creación de workflows en el asistente para que siga las mejores prácticas y se integre correctamente con la arquitectura del sistema.

1.  **Reutilización Real de Plantillas:**
    *   Cuando un usuario elige **"Usar plantilla existente"**, el sistema ahora solo guarda una **referencia** a esa plantilla.
    *   **Beneficio:** Si la plantilla original se actualiza, todos los workflows que la usan reflejarán automáticamente los cambios. Se acabaron las duplicaciones y el mantenimiento manual.

2.  **Creación Correcta de Nuevas Plantillas:**
    *   Cuando un usuario elige **"Crear nuevo email personalizado"**, el asistente ahora genera un **registro completo y reutilizable** en el `EmailTemplateResource`.
    *   **Beneficio:** Los emails creados desde el asistente ahora pueden ser gestionados, editados y reutilizados en otros workflows desde la interfaz de "Templates de Email".

3.  **Mejora de la Experiencia de Usuario (UX):**
    *   El selector de plantillas en el asistente ahora muestra **todas** las plantillas de email activas, no solo un subconjunto, maximizando las opciones de reutilización.
    *   Se ha reorganizado el menú de navegación de Filament para agrupar todos los recursos relacionados (`Asistente de Workflows`, `Templates de Email`, `Workflows Avanzados`) bajo un único grupo: **"Automatización"**, haciendo la navegación más lógica e intuitiva.

**Archivos Modificados:**

-   `app/Filament/Resources/WorkflowWizardResource.php`: Se amplió el selector de plantillas.
-   `app/Filament/Resources/WorkflowWizardResource/Pages/CreateWorkflowWizard.php`: Se refactorizó completamente la lógica de creación (`handleRecordCreation`) para implementar la funcionalidad descrita.
-   `app/Filament/Resources/EmailTemplateResource.php`: Se actualizó el grupo de navegación.
-   `app/Filament/Resources/AdvancedWorkflowResource.php`: Se actualizó el orden y grupo de navegación.

**✅ Beneficio Final:** El Asistente de Workflows es ahora más potente, intuitivo y se alinea perfectamente con la arquitectura robusta del sistema, promoviendo la reutilización (DRY) y facilitando el mantenimiento a largo plazo.

---

## Configuraciones de Seguridad

### Registro de Usuarios Deshabilitado

**Estado Actual:** El registro de nuevos usuarios está DESHABILITADO por seguridad.

**Archivos Modificados:**

1. `app/Providers/Filament/AdminPanelProvider.php` - Línea comentada: `// ->registration()`
2. `routes/auth.php` - Rutas de registro comentadas
3. `resources/js/helpers/route-helper.ts` - Helper para manejar rutas deshabilitadas
4. `resources/js/pages/welcome.tsx` - Condicional para mostrar botón de registro
5. `resources/js/pages/auth/login.tsx` - Condicional para mostrar enlace de registro
6. `resources/js/pages/auth/register.tsx` - Manejo de intento de registro deshabilitado

**Solución JavaScript Implementada:**

- Creado helper `safeRoute()` que verifica si una ruta existe antes de usarla
- Función `isRouteEnabled()` para verificar disponibilidad de rutas
- Los enlaces de registro se ocultan automáticamente cuando está deshabilitado
- No hay errores JavaScript al intentar usar rutas deshabilitadas

**Para HABILITAR registro nuevamente:**

1. En `app/Providers/Filament/AdminPanelProvider.php`:
    - Descomente la línea: `->registration()`
2. En `routes/auth.php`:
    - Descomente las rutas de registro GET y POST
3. Regenerar rutas de Ziggy: `php artisan ziggy:generate`
4. Recompilar assets: `npm run build`

**Razón del cambio:** Por seguridad, solo administradores deben crear usuarios nuevos.

---

## Development Commands

### Backend (Laravel/PHP)

- `composer dev` - Start full development environment (Laravel server, queue, logs, and Vite)
- `composer dev:ssr` - Start development with server-side rendering
- `composer test` - Run PHP tests with Pest
- `php artisan test` - Run tests directly
- `vendor/bin/pest` - Run Pest tests directly
- `vendor/bin/pint` - Format PHP code with Laravel Pint
- `php artisan filament:upgrade` - Upgrade Filament components (runs automatically after composer updates)

### Settings & Configuration Management

- `php artisan migrate --path=database/settings` - Run settings migrations

#### 🚨 IMPORTANTE para Deployments:

Las Settings de Spatie requieren migraciones especiales. En producción ejecutar:

```bash
# Primero migraciones normales
php artisan migrate --force

# Luego migraciones de settings (OBLIGATORIO)
php artisan migrate --path=database/settings --force
```

### Backup System Commands

- `php artisan backup:run` - Execute manual backup
- `php artisan backup:scheduled` - Enhanced scheduled backup with proper email configuration
- `php artisan backup:debug-notifications [--test]` - Debug backup notification system
- `php artisan backup:fix-notifications [--enable-all]` - Fix common notification issues
- `php artisan backup:clean` - Clean old backups based on retention policy

### System Monitoring Commands

- `php artisan pulse:check` - Check Laravel Pulse configuration and status
- `php artisan pulse:clear` - Clear Pulse data and start fresh monitoring
- Access monitoring dashboard at `/admin/system-monitoring` or `/pulse`

### Frontend (React/TypeScript)

- `npm run dev` - Start Vite development server
- `npm run build` - Build for production

## 📋 Sistema de Permisos Granulares para Documentation

### 🎯 Funcionalidad Implementada

El sistema de documentación cuenta con un control de permisos granular que restringe el acceso basado en:

#### 👥 Roles y Permisos

**Creadores de Documentos (Users):**

- Pueden crear nuevos documentos
- Ven únicamente los documentos que ellos crearon
- Pueden editar sus documentos mientras estén en borrador o rechazados
- No pueden ver documentos de otros usuarios

**Autorizadores (IT_Boss):**

- Ven sus propios documentos creados
- Ven documentos pendientes de su aprobación
- **Ven documentos que ya aprobaron o rechazaron** (funcionalidad clave)
- Pueden aprobar o rechazar documentos en estado pendiente
- No pueden editar documentos, solo cambiar su estado

**Super Administradores:**

- Acceso completo a todos los documentos
- Pueden realizar cualquier acción

#### 🔒 Restricciones de Edición

**Documentos Aprobados:**

- Son **INMUTABLES** - nadie puede editarlos una vez aprobados
- Protege la integridad de documentos ya validados
- Solo se pueden ver para consulta

**Documentos en Borrador:**

- Solo el creador puede editarlos
- Pueden ser enviados para aprobación

**Documentos Pendientes:**

- Solo el creador puede editarlos (antes de la aprobación)
- Los autorizadores solo pueden aprobar/rechazar

**Documentos Rechazados:**

- Solo el creador puede editarlos para hacer correcciones
- Pueden ser reenviados para nueva aprobación

#### 🎯 Filtros Disponibles en la Interfaz

**Para IT_Boss:**

- "Aprobados por mí" - Ver documentos que ya aprobé
- "Pendientes de mi aprobación" - Ver documentos esperando mi autorización
- "Solo mis documentos" - Ver únicamente documentos que creé

**Para Usuarios:**

- "Solo mis documentos" - Ver únicamente documentos propios

#### 📊 Estados del Flujo de Aprobación

1. **Borrador** - Documento en creación
2. **Pendiente de Aprobación** - Enviado para revisión del IT_Boss
3. **Aprobado** - Validado y publicado (inmutable)
4. **Rechazado** - Devuelto para correcciones

### 🎯 Beneficios del Sistema

## Important Implementation Notes

- **Dual Interface Architecture**: This app has both a React SPA frontend (main user interface) and a Filament admin panel (administrative interface)
- **SSR Support**: The app supports both CSR and SSR - build commands handle both modes
- **Dynamic Configuration**: Settings from database automatically configure both frontend and Filament
- **Component Aliasing**: Uses `@/` alias for `resources/js/` directory
- **Ziggy Routes**: Route names are available as typed functions in React components
- **Shared Data**: Global props (user, app name, settings) available via `usePage().props`
- **Mobile-First**: Components include mobile navigation patterns
- **Filament Structure**: Admin resources, pages, and widgets are auto-discovered in `app/Filament/` directory
- **Settings Architecture**: Database-driven configuration with type safety and automatic application
- **Backup System**: Complete enterprise-grade backup solution with cloud storage
- **System Monitoring**: Real-time performance monitoring via Laravel Pulse with Filament integration
- **Activity Logging**: All admin panel actions are automatically logged via Filament Logger
- **Menu Management**: Dynamic navigation structure via Filament Menu Builder plugin
- **User Profile Management**: Complete user profile system via Filament Breezy with 2FA and API tokens
- **Role-Based Access**: Granular permissions system via Filament Shield
- **Email Testing**: Easy Mailtrap integration for development and testing
- **Email Templates**: Dynamic template system with variable replacement
- **Localization**: Spanish as primary language (APP_LOCALE=es) with English fallback

## Important Implementation Notes

- **Dual Interface Architecture**: This app has both a React SPA frontend (main user interface) and a Filament admin panel (administrative interface)
- **SSR Support**: The app supports both CSR and SSR - build commands handle both modes
- **Dynamic Configuration**: Settings from database automatically configure both frontend and Filament
- **Component Aliasing**: Uses `@/` alias for `resources/js/` directory
- **Ziggy Routes**: Route names are available as typed functions in React components
- **Shared Data**: Global props (user, app name, settings) available via `usePage().props`
- **Mobile-First**: Components include mobile navigation patterns
- **Filament Structure**: Admin resources, pages, and widgets are auto-discovered in `app/Filament/` directory
- **Settings Architecture**: Database-driven configuration with type safety and automatic application
- **Backup System**: Complete enterprise-grade backup solution with cloud storage
- **System Monitoring**: Real-time performance monitoring via Laravel Pulse with Filament integration
- **Activity Logging**: All admin panel actions are automatically logged via Filament Logger
- **Menu Management**: Dynamic navigation structure via Filament Menu Builder plugin
- **User Profile Management**: Complete user profile system via Filament Breezy with 2FA and API tokens
- **Role-Based Access**: Granular permissions system via Filament Shield
- **Email Testing**: Easy Mailtrap integration for development and testing
- **Email Templates**: Dynamic template system with variable replacement
- **Localization**: Spanish as primary language (APP_LOCALE=es) with English fallback

## Development Style Guidelines

### 🎯 **Problem-Solving Approach**

When encountering issues:

1. **✅ Identify Root Cause**: Analyze the specific error and its context
2. **✅ Provide Clear Summary**: Use structured format with before/after comparisons
3. **✅ Show Exact Changes**: Include code snippets showing what changed
4. **✅ Explain Benefits**: Detail what the fix accomplishes
5. **✅ Verify Completeness**: Ensure the solution is thorough and tested

### 📊 **Communication Style**

- **Use Emojis Strategically**: ✅ ❌ 🔧 🎯 📊 for visual clarity
- **Structured Reporting**: Clear sections with headers and bullet points
- **Before/After Comparisons**: Show exact changes made
- **Status Indicators**: Use checkmarks and X marks for clear status
- **Concise but Complete**: Thorough information in digestible format

### 🛠️ **Technical Standards**

- **Laravel 12 Compatibility**: Always ensure compatibility with latest Laravel
- **Filament v3.3 Native Components**: Use only native Filament components, avoid custom builders
- **Dependency Injection**: Use `app(Service::class)` or method injection, avoid typed properties that cause initialization issues
- **Error Prevention**: Validate all method signatures and component methods exist
- **Route Verification**: Always verify route names exist before using them
- **Icon Validation**: Ensure all heroicons used are valid in the current heroicons set
- **Line Icons Only**: NEVER use emojis anywhere in code, UI, or documentation - ALWAYS use line icons (heroicons-o-\*)
- **HasStateTransitions Trait**: Use the reusable trait for automatic state management in Filament resources
- **Dynamic Configuration**: Always read from database resources rather than hardcoding business logic

### 🎨 **UI/UX Consistency**

- **Native Filament Patterns**: Follow Filament's design system and component patterns
- **Consistent Navigation**: Use proper route names and navigation structure
- **Professional Styling**: Clean, consistent interfaces across all pages
- **Responsive Design**: Ensure all interfaces work on all device sizes
- **Semantic Icons**: Use appropriate heroicons that match the functionality

### 🔍 **Quality Assurance**

- **Test Critical Paths**: Verify functionality works without errors
- **Cross-Page Consistency**: Ensure similar pages follow the same patterns
- **Error Handling**: Implement proper error handling and user feedback
- **Performance**: Use efficient patterns that don't cause performance issues
- **Accessibility**: Follow accessibility best practices with proper ARIA labels

### ⚠️ **CRITICAL: Dynamic vs Hardcode Policy**

**ALWAYS ask the user before implementing hardcoded solutions for dynamic functionality:**

- ❌ **Never hardcode** business rules, permissions, state transitions, role mappings, or workflow logic
- ✅ **Always use database-driven** configuration from existing resources like `approval-states`, `state-transitions`, etc.
- 🤔 **When in doubt, ASK**: "Should this be configurable/dynamic or is hardcode acceptable?"
- 📋 **Examples of what to avoid hardcoding**:
    - Role permissions (`'super_admin'`, `'Travel'`, `'Treasury'`)
    - State transitions (`'draft' -> 'pending_supervisor'`)
    - Business logic rules (who can approve what)
    - Status mappings and labels

**Principle**: If it exists as a resource in the admin panel, it should be read from the database, not hardcoded.

This style ensures professional, consistent, and reliable development across the entire application.

# important-instruction-reminders

Do what has been asked; nothing more, nothing less.
NEVER create files unless they're absolutely necessary for achieving your goal.
ALWAYS prefer editing an existing file to creating a new one.
NEVER proactively create documentation files (\*.md) or README files. Only create documentation files if explicitly requested by the User.