# Documento de Especificaciones Técnicas y Funcionales
## Sistema de Gestión de Ferias, Eventos y Lealtad de Marcas (SerExperience)

---

## 1. Introducción y Objetivos
Este documento define las especificaciones funcionales, el modelo de datos y la arquitectura técnica para el desarrollo del sistema de gestión de eventos de **SerExperience**. 

El sistema tiene como objetivo principal facilitar la organización de ferias y eventos, cubriendo desde la creación del evento, el control de accesos mediante códigos QR, la administración de invitaciones/asistentes, y un módulo robusto de lealtad comercial para las Marcas patrocinadoras/expositoras.

---

## 2. Arquitectura Conceptual del Sistema
El sistema se construirá sobre la base tecnológica actual (**Laravel 10** y **Voyager Admin Panel**). Se estructurará en tres capas de interacción principales:

1. **Panel Administrativo Central (SuperAdmin / Organizadores):** Basado en Laravel Voyager para la gestión integral de eventos, marcas, generación masiva de invitaciones y reportes globales.
2. **Portal de Marcas (Expositores):** Una interfaz web optimizada para dispositivos móviles donde los usuarios de las marcas inician sesión para escanear/verificar cupones de asistentes y registrar su uso en tiempo real.
3. **Motor de Control de Accesos y API QR:** Servicios seguros para validar entradas en la entrada del evento y en los stands, garantizando un control libre de duplicaciones (anti-passback).

---

## 3. Modelo de Entidades y Relaciones (Base de Datos)

A continuación, se detalla el diseño de la base de datos estructurado en Laravel Eloquent.

### Diagrama de Relaciones (ERD)
```mermaid
erDiagram
    EVENT ||--o{ TICKET_TYPE : "define"
    EVENT ||--o{ BRAND : "alberga"
    EVENT ||--o{ REGISTRATION : "tiene"
    TICKET_TYPE ||--o{ REGISTRATION : "clasifica"
    ATTENDEE ||--o{ REGISTRATION : "realiza"
    BRAND ||--o{ BRAND_USER : "tiene"
    USER ||--o{ BRAND_USER : "es"
    BRAND ||--o{ LOYALTY_COUPON : "crea"
    REGISTRATION ||--o{ COUPON_REDEMPTION : "consume"
    LOYALTY_COUPON ||--o{ COUPON_REDEMPTION : "registra_uso"
    LOYALTY_COUPON ||--o{ COUPON_ALLOCATION : "asigna_a"
    REGISTRATION ||--o{ COUPON_ALLOCATION : "recibe"

    EVENT {
        bigint id PK
        string name
        string slug
        text description
        string banner_image
        string location_name
        text location_address
        datetime start_date
        datetime end_date
        string status "draft | active | completed | cancelled"
        integer capacity
        timestamps
    }

    TICKET_TYPE {
        bigint id PK
        bigint event_id FK
        string name "General, VIP, Prensa, Expositor"
        text description
        decimal price "Por defecto 0.00"
        integer quantity_total "Capacidad máxima de este tipo"
        integer quantity_available "Disponibles actualmente"
        datetime sales_start
        datetime sales_end
        boolean is_active
        json special_conditions "Ej. Incluye kit de bienvenida"
        timestamps
    }

    ATTENDEE {
        bigint id PK
        string first_name
        string last_name
        string email "Unique"
        string phone
        string company
        string job_title
        timestamps
    }

    REGISTRATION {
        bigint id PK
        bigint event_id FK
        bigint attendee_id FK
        bigint ticket_type_id FK
        string entry_code "Código QR Único para Control de Puerta"
        string loyalty_code "Código QR Único para Cupones/Stand"
        string status "pending | confirmed | checked_in | cancelled"
        datetime checked_in_at "Marca de entrada al evento"
        datetime checkout_at "Opcional para control de aforo"
        timestamps
    }

    BRAND {
        bigint id PK
        bigint event_id FK
        string name
        string logo
        string description
        string stand_number "Ej. Stand B-12"
        timestamps
    }

    BRAND_USER {
        bigint id PK
        bigint user_id FK
        bigint brand_id FK
        timestamps
    }

    LOYALTY_COUPON {
        bigint id PK
        bigint brand_id FK
        string title
        text description
        string discount_type "percentage | fixed_amount | freebie"
        decimal discount_value
        integer global_limit "Máximo total de redenciones de la campaña"
        integer usage_limit_per_attendee "Ej: 1, 2 o 3 veces"
        string allocation_strategy "general | selective"
        string validity_scope "during_event | post_event | both"
        boolean allow_brand_modification "Permite a la marca editar parámetros"
        boolean is_active
        datetime valid_from
        datetime valid_to
        timestamps
    }

    COUPON_ALLOCATION {
        bigint id PK
        bigint loyalty_coupon_id FK
        bigint registration_id FK
        timestamps
    }

    COUPON_REDEMPTION {
        bigint id PK
        bigint loyalty_coupon_id FK
        bigint registration_id FK
        bigint processed_by_user_id FK "ID del usuario de la marca"
        datetime redeemed_at
        text notes
        timestamps
    }
```

---

## 4. Especificaciones por Entidad y Módulos

### 4.1 Módulo de Eventos
Manejo del ciclo de vida de un evento ferial.
- **Funciones:**
  - Crear, Editar, Duplicar y Eliminar Eventos.
  - Gestión de Estado: Borrador, Activo (Visibilidad pública), Finalizado, Cancelado.
  - Configuración de Aforo Máximo y alertas al alcanzar el 90% de capacidad.
- **Control de Acceso en Puerta (Check-in del Evento):**
  - Cada registro genera un `entry_code` exclusivo para el control de entrada principal. Este código es completamente **independiente** del código de cupones.
  - El personal de seguridad en puerta escaneará este código QR al momento del ingreso al recinto de la feria.
  - Integración con Endpoint API de Accesos:
    - **Respuesta 200 (Éxito):** Acceso concedido al recinto. Actualiza `status` a `checked_in` y marca `checked_in_at`.
    - **Respuesta 409 (Conflicto):** Entrada ya utilizada (Anti-passback). El sistema previene re-ingresos no autorizados o fotocopias de la invitación.
    - **Respuesta 404/403:** Invitación inválida o cancelada.

### 4.2 Módulo de Invitaciones y Tipos de Tickets
Manejo de la oferta y distribución de accesos al evento.
- **Atributos Especiales Investigados (Estándar de Ticketing):**
  - **Condiciones Especiales:** Posibilidad de adjuntar beneficios específicos al ticket en formato JSON (ej. `{ "lunch_included": true, "backstage_pass": false }`).
  - **Control de Stock/Inventario Dinámico:** Un trigger o evento de Eloquent reducirá automáticamente `quantity_available` cada vez que se asigne o confirme una invitación.
  - **Tipos de Invitaciones:**
    - *General:* Acceso estándar.
    - *VIP:* Acceso prioritario, zonas especiales.
    - *Prensa/Medios:* Requiere aprobación administrativa adicional.
    - *Expositor:* Vinculado a una Marca, a menudo con accesos en horarios especiales de montaje.
    - *Regalo (Invitación de Regalo):* Acceso por cortesía diseñado para dinámicas promocionales que incluye beneficios o paquetes predefinidos gratuitos.
  - **Vigencia Programada:** Las invitaciones solo pueden registrarse entre `sales_start` y `sales_end`.

### 4.3 Módulo de Asistentes (Attendees)
Registro unificado de usuarios reales en la plataforma.
- **Independencia de Datos:** La entidad `Attendee` almacena los datos demográficos del usuario (Nombre, Email, Teléfono) de forma global, mientras que `Registration` representa la asistencia específica a un evento. Esto permite que un mismo asistente participe en múltiples ferias históricamente sin duplicar su registro base.
- **Búsqueda y Verificación:**
  - Filtro dinámico en panel admin por Email, Teléfono o Empresa.
  - Capacidad de re-enviar el código QR o el email de confirmación con un solo clic.

### 4.4 Módulo de Marcas y Stands
Gestión de empresas expositoras que forman parte de la feria.
- **Asignación de Stands:** Identificador de ubicación física (Ej. Stand A-01).
- **Gestión de Cuentas de Marca:**
  - El Administrador crea registros en `brands` y vincula cuentas de usuario normales (`users`) a través de la tabla pivote `brand_users`.
  - El rol de usuario asignado a estos usuarios tendrá permisos sumamente restringidos, orientados solo al Panel de Marca.

---

## 5. Especificaciones del Sistema de Lealtad y Cupones (Loyalty)
Siguiendo las mejores prácticas y estándares de la industria (inspirado en arquitecturas de Shopify Discounts, Stripe Promotion Codes y estándares POS):

### 5.1 Clasificación y Reglas de Cupones
Un Cupón de Lealtad (`loyalty_coupons`) puede configurarse de las siguientes formas:

1. **Tipo de Descuento / Beneficio:**
   - **Porcentaje (`percentage`):** Descuento del X% en los servicios/productos de la marca.
   - **Monto Fijo (`fixed_amount`):** Descuento de un monto específico de dinero (Ej: \$20 de regalo).
   - **Servicio Gratuito (`freebie`):** Canje directo por un producto o servicio (Ej: "Una sesión de prueba gratis", "Bebida de cortesía").

2. **Control de Uso Periódico / Frecuencia (Requerimiento Crítico):**
   - **`usage_limit_per_attendee`:** Define cuántas veces el **mismo asistente** puede canjear el cupón en el stand de la marca.
     - *Ejemplo 1 (Uso Único):* Valor = 1. Una vez consumido, el sistema deniega más redenciones.
     - *Ejemplo 2 (Uso Múltiple):* Valor = 3. El asistente puede canjear el beneficio en 3 visitas distintas. La tabla `coupon_redemptions` llevará el conteo acumulado.
   - **`global_limit`:** Máximo de redenciones permitidas a nivel de toda la feria para controlar el presupuesto de la marca (Ej: "Sólo para los primeros 100 asistentes en llegar al stand").

3. **Estrategia de Asignación (Asignación General vs. Específica):**
   - **Asignación General (`allocation_strategy: general`):** El cupón está disponible automáticamente para **TODOS** los asistentes con un registro activo en el evento. No requiere vinculación previa en la base de datos.
   - **Asignación Específica/Selectiva (`allocation_strategy: selective`):** El cupón se vincula únicamente a ciertos asistentes VIP o a una lista curada manualmente por el organizador o la marca. Esto se almacena en la tabla `coupon_allocation`.

### 5.2 Interfaz y Flujo del Portal de Marcas
El portal de la marca estará diseñado bajo los principios de un Web-App móvil simplificado (PWA ready) para facilitar el escaneo directo con la cámara de los teléfonos del personal del stand:

1. **Autenticación Segura:** Acceso con Email/Contraseña exclusivo para el personal asignado a la marca.
2. **Vista Principal (Dashboard del Stand):**
   - Resumen rápido: Total cupones redimidos hoy, número de clientes atendidos.
   - Botón gigante: **"Escanear QR de Asistente"**.
   - Campo de texto alternativo: **"Ingresar Código Manualmente"** (para casos donde la cámara falle).
3. **Flujo de Verificación y Redención:**
   - Al escanear el QR de Beneficios del asistente (que lee el `loyalty_code` de la tabla `registration`):
     - **Validación Interna:**
       1. Verifica que la `registration` exista y corresponda al evento en curso.
       2. Identifica qué cupones de la marca aplican al asistente (Generales + Selectivos asignados).
       3. Para cada cupón aplicable, cuenta el número de registros existentes en `coupon_redemptions` hechos por este asistente.
       4. Compara el conteo contra `usage_limit_per_attendee`.
     - **Pantalla de Resultados en Móvil:**
       - **CASO A (Cupón Pendiente / Disponible):**
         - Muestra en **Verde**: *"Cliente Verificado - [Nombre Asistente]"*.
         - Muestra detalle del beneficio: *"[Marca] ofrece: 15% de descuento"*.
         - Muestra contador de uso: *"Uso 1 de 2 consumidos"*.
         - Botón de Acción: **[REGISTRAR CANJE / CONSUMIR CUPO]**.
       - **CASO B (Cupón Agotado / Límite Alcanzado):**
         - Muestra en **Rojo**: *"Límite alcanzado"*.
         - Detalle: *"Este asistente ya consumió el máximo de beneficios permitidos (3/3) el día DD/MM a las HH:MM"*.
         - Botón de acción deshabilitado.
4. **Confirmación del Canje:**
   - Al presionar "Registrar Canje", el backend crea un registro en `coupon_redemptions` capturando:
     - Quién redimió (Asistente).
     - Qué cupón fue.
     - Qué usuario del stand procesó la transacción (trazabilidad).
     - La marca de tiempo exacta (`redeemed_at`).
     - Campo de observaciones (opcional para que la marca anote qué compró el cliente o detalles adicionales).

### 5.3 Flujo Dinámico de "Pasaporte de Evento" y Beneficios Post-Evento
Para dinamizar la interacción física y la captación de leads calificados durante y después de la feria, se implementa el siguiente flujo de registro móvil automatizado:

1. **El "Pasaporte Físico":**
   - Los asistentes reciben un "Pasaporte de Evento" impreso para marcar en cada stand.
   - Este pasaporte contiene un **Código QR Promocional Único de Registro** (visible o escaneable).
2. **Escaneo del Asistente y Auto-Registro Fast-Track:**
   - El asistente escanea el QR con su propio dispositivo inteligente.
   - Esto lo redirige a una Landing Page ligera en el portal del evento (`/registro-pasaporte`).
   - **Formulario Simplificado:** El asistente ingresa únicamente **Nombre**, **Correo** y **Teléfono**.
3. **Procesamiento y Generación de Clave Única de Beneficios:**
   - El sistema valida los datos, crea al `Attendee` y genera su respectiva `Registration` activa en el evento.
   - Genera automáticamente su `loyalty_code` único (Clave Única de Cupones) y su `entry_code` (para control de accesos en caso de aplicar).
   - **Notificación Inmediata por Email:** El sistema dispara un correo electrónico de bienvenida que contiene:
     - Su **Código de Canje y Código QR Personal**.
     - Listado dinámico de marcas aliadas y los beneficios a los que tiene derecho.
4. **Consumo de Servicios Post-Evento (En Locales Comerciales de la Marca):**
   - Las marcas pueden configurar cupones con `validity_scope = "post_event"`.
   - **Acción del Cliente:** Días o semanas después del evento, el cliente acude al local físico o empresa de la marca y presenta el código QR/Clave Única que recibió en su correo.
   - **Acción de la Marca:** El personal en el local ingresa al mismo **Portal de Marcas**, ingresa o escanea el QR del cliente y el sistema verificará el beneficio pendiente.
   - Al confirmarlo, el sistema crea la `coupon_redemption` correspondiente, marcando al cliente como **"Atendido/Consumido"** en el registro histórico para que no pueda repetirse el canje más de las veces estipuladas.

### 5.4 Control de Permisos sobre Marcas
- **Campo Clave `allow_brand_modification`:** Para evitar discrepancias o retrasos operativos, el organizador decide si las marcas pueden o no editar los límites y configuraciones de sus cupones. 
- Si este check está en `false` (por defecto), la marca tendrá permisos de **Sólo Lectura** sobre su configuración de cupones, pudiendo únicamente escanear y redimir los códigos de los asistentes.

---

## 6. Diagnóstico y Estrategias de Optimización UX (Cero Fricción y Trabajo Masivo)

A continuación, se definen los mecanismos diseñados específicamente para minimizar la cantidad de clics de los organizadores y aliviar al máximo el trabajo administrativo de las Marcas expositoras.

### 6.1 Carga Masiva y Herramientas para el Organizador
Para agilizar el montaje de ferias de más de 100 stands o miles de asistentes, se integrarán herramientas de procesamiento en lote:

1. **Importador Inteligente Excel/CSV (1-Clic):**
   - **Importación de Marcas:** Subida de un archivo `.xlsx` que contiene: `Nombre de Marca`, `Número de Stand` y `Email de Contacto`.
     - *Acción Automática del Sistema:* Crea el registro de la `Brand`, genera simultáneamente la cuenta de usuario normal (`User`), le asigna una contraseña temporal segura y vincula la relación, todo en segundos.
   - **Importación de Asistentes/Invitados:** Subida masiva de bases de datos de invitados con detección automática de columnas (`Nombre`, `Email`, `Teléfono`, `Tipo de Ticket`). El sistema genera de inmediato sus códigos `entry_code` y `loyalty_code` correspondientes.

2. **Generador de Cupones en Lote (Batch Creation Tool):**
   - **Problema Detectado:** Depender de que cada una de las 50 marcas configure su propio cupón lleva al caos operativo y falta de control de calidad.
   - **Solución UX Avanzada:** En el panel del Organizador, existirá un botón **"Crear Cupones Base para el Evento"**. El organizador ingresa una sola vez los parámetros (Ej: *"15% de Descuento, Límite 2 veces por asistente, Validez Post-Evento"*). El sistema replicará instantáneamente este cupón idéntico para **TODAS** las marcas activas en el evento.
   - **Control de Edición Centralizado:** Al crear el lote de cupones, el organizador marca con un check general si las marcas pueden editar estos datos (`allow_brand_modification = true`) o si queda estrictamente bloqueado por la organización.

3. **Reportes y Generación Automática de Credenciales:**
   - Herramienta para descargar un documento comprimido (ZIP) o tabla unificada con las credenciales temporales (usuario y clave) de todas las marcas del evento, listas para ser compartidas vía WhatsApp o correo masivo automatizado.

### 6.2 Interfaz del Stand: Fricción Cero para las Marcas
El personal de los stands suele estar muy ocupado atendiendo clientes cara a cara. El software debe ser transparente para ellos:

- **Acceso Simplificado:** La pantalla de Login del Portal de Marcas es sumamente limpia, con el teclado numérico optimizado si es necesario.
- **Auto-Redirección al Escáner:** Al iniciar sesión, el portal NO lleva a menús ni resúmenes estadísticos complejos. Lleva **directamente** a la cámara de escaneo activa.
- **Canje en 1 Tap:** Una vez escaneado el código del cliente, si el beneficio es válido, el botón verde de confirmación ("Registrar Canje") es gigantesco y requiere un solo clic/tap para finalizar, devolviendo la pantalla a modo escáner en menos de 1 segundo.
- **Nula Configuración Requerida:** La marca no necesita crear campañas, subir logos complicados o definir límites técnicos si el organizador ya hizo la precarga masiva. Solo ingresan su clave y empiezan a escanear.

---

## 7. Requerimientos No Funcionales y Seguridad
1. **Privacidad de Datos:** Encriptación en tránsito (SSL/TLS 1.3) para toda interacción de datos personales y escaneo de códigos.
2. **Integridad del Código QR (Anti-Fraude):**
   - Los códigos QR de acceso no deben contener información sensible en texto plano.
   - Se utilizarán strings UUID aleatorios y, opcionalmente, URLs firmadas temporalmente para evitar que los asistentes capturen capturas de pantalla y las transfieran ilegalmente si el evento es de alta seguridad.
3. **Rendimiento y Offline Tolerant:**
   - La respuesta de validación de QR debe ocurrir en menos de 500ms para evitar cuellos de botella en las colas de acceso del evento y de los stands.
   - Se implementará almacenamiento en caché (Redis/Memcached) para la verificación veloz de los códigos válidos de acceso.
4. **Diseño UX Responsivo (Mobile-First):**
   - Dado que el 95% de los escaneos de cupones ocurrirán desde teléfonos inteligentes en los stands del evento, la interfaz del portal de marcas estará optimizada estrictamente para pantallas móviles, con botones grandes y contrastes altos adaptados a la iluminación de recintos feriales.

---

## 8. Pasos Siguientes para la Implementación
Una vez aprobado este documento de especificaciones por el cliente:
1. **Fase 1:** Generación de Migraciones de Base de Datos y Modelos de Laravel Eloquent con sus relaciones correspondientes.
2. **Fase 2:** Configuración de Laravel Voyager BREAD (Browse, Read, Edit, Add, Delete) para Eventos, Invitaciones, Asistentes y Marcas.
3. **Fase 3:** Desarrollo del Portal de Marcas (Vistas Blade responsivas + Controladores personalizados para inicio de sesión y búsqueda/validación).
4. **Fase 4:** Motor de Códigos QR y APIs de validación para control de accesos y redención de cupones.
5. **Fase 5:** Herramientas Administrativas de Carga Masiva (Excel/CSV) y Herramientas Batch de Creación de Cupones.
6. **Fase 6:** Pruebas de Carga y Simulación de Redenciones concurrentes.
