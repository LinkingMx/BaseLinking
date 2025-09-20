
# Visión General del Sistema de Templates de Email

Este documento describe la arquitectura y funcionamiento del sistema de gestión de plantillas de correo electrónico, un componente central para las notificaciones y comunicaciones de la aplicación.

## Arquitectura y Componentes Clave

El sistema está diseñado de forma modular para separar la gestión de datos, la lógica de negocio y la configuración, facilitando su mantenimiento y extensibilidad.

### 1. El Modelo de Datos: `EmailTemplate`

Es el corazón del sistema. Un registro en la tabla `email_templates` representa una plantilla de email reutilizable.

-   **Atributos Principales:**
    -   `key`: Un identificador único y legible (ej. `bienvenida-nuevo-usuario`). Es la forma en que el código invoca un template.
    -   `name`: Nombre descriptivo para la interfaz de administración.
    -   `subject`: El asunto del correo.
    -   `content`: El cuerpo del email, que puede contener HTML y variables.
    -   `model_type`: (Opcional) Asocia la plantilla a un modelo de Eloquent específico (ej. `App\Models\Documentation`). Esto permite al sistema saber qué variables específicas de ese modelo están disponibles.
    -   `language`: Permite tener múltiples versiones de una misma plantilla para soporte multi-idioma.
    -   `is_active`: Permite activar o desactivar plantillas.

-   **Procesamiento de Variables:** El propio modelo contiene la lógica para reemplazar las variables. Utiliza una sintaxis de llaves dobles `{{ variable }}` y soporta:
    -   **Variables Simples:** `{{ app_name }}`
    -   **Variables Anidadas:** `{{ user.department.name }}` (accede a datos de relaciones).
    -   **Filtros de Formato:** `{{ created_at|date:d/m/Y }}` (formatea una fecha) o `{{ amount|currency:USD }}`.

### 2. El Cerebro: `EmailTemplateService`

Este servicio actúa como la fachada principal para interactuar con el sistema de plantillas. Ninguna otra parte de la aplicación (como los workflows) interactúa directamente con el modelo `EmailTemplate`, sino que lo hace a través de este servicio.

-   **Responsabilidades:**
    -   **`processTemplate(key, variables)`:** Es el método principal. Recibe la clave de una plantilla y un array de datos. Se encarga de:
        1.  Encontrar el `EmailTemplate` activo correcto por su `key`.
        2.  Fusionar las variables proporcionadas con un conjunto de **variables globales** (`app_name`, `current_date`, etc.) que están disponibles en todos los correos.
        3.  Invocar los métodos `processSubject` y `processContent` del modelo para obtener el asunto y cuerpo final.
        4.  Devolver un array con el contenido procesado y listo para ser enviado.
    -   **`getWrappedContent(content)`:** (Opcional) Envuelve el contenido del email en un layout maestro (`wrapper.blade.php`) para asegurar un diseño consistente (header, footer, estilos) en todos los correos enviados por el sistema.
    -   **Proveedores de Variables:** Contiene métodos (`getBackupVariables`, `getUserVariables`, etc.) que actúan como diccionarios de datos para contextos específicos.

### 3. La Interfaz de Gestión: `EmailTemplateResource`

Es el CRUD de Filament que permite a los administradores gestionar las plantillas de email sin tocar el código.

-   **Características Clave:**
    -   Utiliza un editor **TinyMCE (WYSIWYG)** para que la creación de emails en HTML sea visual e intuitiva.
    -   **Integración con `ModelIntrospectionService`:** Cuando un administrador asocia una plantilla a un modelo, la interfaz automáticamente muestra una lista de todas las variables disponibles para ese modelo (incluyendo las de `ModelVariableMapping`), facilitando enormemente la personalización.
    -   **Previsualización:** Permite previsualizar cómo se verá el email renderizado directamente desde el panel de administración.
    -   **Ayuda contextual:** Ofrece modales con guías de las variables disponibles.

### 4. La Configuración de Envío: `EmailConfiguration`

Este componente gestiona la configuración del **transporte** de correo (el "cómo" se envía el email).

-   Permite a un administrador configurar diferentes proveedores de correo (SMTP, Mailgun, SES, etc.) a través de la interfaz.
-   La configuración activa se guarda en la base de datos y el modelo `EmailConfiguration` tiene la lógica para **actualizar dinámicamente el archivo `.env`** de Laravel y la configuración en memoria.
-   Esto desacopla la creación de templates de la configuración del servidor de correo, permitiendo cambiar de proveedor sin tener que modificar el código.

### 5. El Cartero: `WorkflowNotificationMail`

Es una clase `Mailable` de Laravel. Su única responsabilidad es recibir el asunto y el contenido ya procesados por el `EmailTemplateService` y ponerlos en la cola de envío de Laravel. Es el último eslabón de la cadena.

## Flujo de Trabajo Completo

1.  **Creación:** Un administrador crea una `EmailTemplate` a través del `EmailTemplateResource` en Filament. Asocia la plantilla al modelo `Documentation` y en el contenido escribe: `"El documento '{{ document.title }}' ha sido aprobado."`. 
2.  **Disparo:** Un `AdvancedWorkflow` se ejecuta cuando un documento cambia su estado a `aprobado`.
3.  **Invocación del Servicio:** El `AdvancedWorkflowEngine` llama a `EmailTemplateService->processTemplate('documento-aprobado', ['document' => $documento])`.
4.  **Procesamiento:**
    -   `EmailTemplateService` encuentra la plantilla con la clave `documento-aprobado`.
    -   Combina las variables globales con los datos del objeto `$documento`.
    -   El modelo `EmailTemplate` reemplaza `{{ document.title }}` con el título real del documento.
    -   El servicio envuelve el HTML resultante en el layout maestro.
5.  **Envío:** El `AdvancedWorkflowEngine` recibe el HTML y asunto finales y los pasa al `Mailable` `WorkflowNotificationMail`, que es despachado a la cola.
6.  **Transporte:** Laravel procesa la cola y utiliza la `EmailConfiguration` activa para enviar el correo a través del proveedor configurado (ej. SMTP).
