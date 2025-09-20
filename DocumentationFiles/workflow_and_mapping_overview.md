
# Visión General de los Sistemas de Workflows y Variables

Este documento describe la arquitectura de los sistemas de automatización del proyecto, que se componen de tres partes principales: **Advanced Workflows**, **Model Variable Mappings** y el **Workflow Wizard**.

## 1. El Motor Principal: Advanced Workflows

El sistema de `AdvancedWorkflow` es el núcleo de toda la automatización. Es un motor potente y flexible basado en reglas que permite ejecutar acciones automáticas en respuesta a eventos que ocurren en los modelos de la aplicación (Modelos de Eloquent).

### Conceptos Clave:

- **Master Workflow (`AdvancedWorkflow`):** Es el contenedor principal de la lógica de automatización. Cada "Master Workflow" está asociado a un único modelo del sistema (ej. `App\Models\Documentation`). Su función es agrupar un conjunto de reglas para ese modelo.
- **Pasos (`WorkflowStepDefinition`):** Un workflow se compone de múltiples "pasos". Cada paso es una unidad de trabajo independiente que contiene una acción y las condiciones bajo las cuales debe ejecutarse.
- **Ejecución (`AdvancedWorkflowExecution`):** Es un registro que se crea cada vez que un paso de un workflow se ejecuta para un registro específico de un modelo. Sirve como un log de auditoría.

### Flujo de Ejecución (`AdvancedWorkflowEngine`):

El sistema no es una máquina de estados secuencial, sino un **evaluador de reglas no lineal**.

1.  **Disparador (Trigger):** Una acción ocurre en la aplicación (ej. un usuario actualiza un documento). Esto dispara un evento de Eloquent (`created`, `updated`, `deleted`, `state_changed`).
2.  **Invocación del Motor:** Se invoca al `AdvancedWorkflowEngine`.
3.  **Búsqueda del Workflow:** El motor busca el "Master Workflow" activo que corresponda al modelo del evento.
4.  **Evaluación de Pasos:** El motor itera sobre **todos los pasos** definidos dentro de ese workflow.
5.  **Verificación de Condiciones:** Para cada paso, el motor verifica si sus condiciones se cumplen en el contexto del evento actual. Las condiciones pueden ser:
    -   **Evento:** ¿El paso está configurado para este evento (ej. `updated`)?
    -   **Condiciones de Campo:** ¿Se cumple una condición sobre un campo del modelo (ej. `prioridad == 'alta'`)?
    -   **Condiciones de Estado (Spatie):** ¿El modelo está transicionando desde o hacia un estado específico (ej. de `borrador` a `aprobado`)?
6.  **Ejecución:** Si las condiciones de un paso se cumplen, la acción de ese paso se ejecuta de forma **independiente**. Múltiples pasos pueden ejecutarse a la vez si sus condiciones se satisfacen por el mismo evento.

### Tipos de Acciones en los Pasos:

-   **Notificación:** Enviar un email utilizando una plantilla.
-   **Acción:** Realizar una operación, como actualizar el modelo (`update_model`).
-   **Condición:** La propia evaluación de la condición es el propósito del paso.
-   **Espera:** Pausar la ejecución por un tiempo determinado.

---

## 2. El Puente de Datos: Model Variable Mappings

El sistema de `ModelVariableMapping` sirve para enriquecer los datos que pueden ser utilizados dentro de los workflows, especialmente en las plantillas de email. Permite crear **variables personalizadas y dinámicas** para cualquier modelo sin necesidad de modificar su código o la base de datos.

### Funcionamiento:

-   Cada `ModelVariableMapping` define una nueva variable (ej. `{{ dias_para_vencimiento }}`).
-   Se asocia a un modelo (`model_class`).
-   La clave es el `mapping_config`, que define cómo se calcula el valor de la variable:
    -   **Campo Directo:** `status` -> "approved"
    -   **Campo de Relación:** `creator.name` -> "Armando Reyes"
    -   **Método:** Llamar a una función en el modelo, como `getApprovalDeadline()`.
    -   **Computado:** Realizar cálculos, como contar relaciones (`count_relation`) o concatenar campos.
    -   **Condicional:** Devolver un valor u otro según una lógica. (ej. SI `status == 'approved'` DEVUELVE "Aprobado", SINO DEVUELVE "Pendiente").

### Integración:

-   El `ModelIntrospectionService` se encarga de inspeccionar un modelo y devolver **todas** las variables disponibles: sus atributos directos, sus relaciones y todas las variables personalizadas definidas en este sistema.
-   El `AdvancedWorkflowResource` (la interfaz de administración de workflows) utiliza este servicio para mostrar al administrador una lista completa de variables que puede usar en las plantillas de email, facilitando la personalización.

---

## 3. La Interfaz Amigable: Workflow Wizard

El `WorkflowWizardResource` es un **asistente paso a paso** diseñado para que usuarios no técnicos puedan crear automatizaciones comunes sin necesidad de entender la complejidad del `AdvancedWorkflow`.

### Funcionamiento:

-   Es una interfaz de Filament que utiliza el modelo `AdvancedWorkflow` pero lo presenta como un asistente guiado.
-   El usuario responde a preguntas sencillas:
    1.  **¿Qué quieres automatizar?** (Enviar un email).
    2.  **¿Cuándo debe ejecutarse?** (Cuando se crea un nuevo Usuario).
    3.  **¿A quién notificar?** (Al creador del registro).
    4.  **Personaliza el mensaje** (Asunto y cuerpo del email).

### La Magia del Asistente:

Al finalizar el asistente, este **genera en segundo plano todos los registros necesarios** para que el motor de `AdvancedWorkflow` pueda funcionar:

1.  Crea el registro del `AdvancedWorkflow`.
2.  Crea el `WorkflowStepDefinition` (el paso), configurando su `step_type` a `notification` y sus `conditions` según lo que el usuario eligió.
3.  Crea la plantilla de email (`EmailTemplate`) con el texto del usuario.
4.  Asocia la plantilla con el paso.

De esta forma, el asistente actúa como una **fachada simplificada** que traduce una intención de usuario simple en la estructura de datos compleja que el motor principal requiere.

## Analogía Final

-   **AdvancedWorkflowEngine:** El motor de un coche. Potente y complejo.
-   **ModelVariableMapping:** Un ordenador de a bordo que puedes programar para que te muestre cualquier dato que quieras (consumo, autonomía, etc.).
-   **WorkflowWizard:** El volante, los pedales y el botón de arranque. Permite a cualquiera conducir el coche sin ser mecánico.
