# Implementation Plan: Sistema de Comités IPUC

**Branch**: `001-sistema-comites-ipuc` | **Date**: 2026-10-18 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-sistema-comites-ipuc/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

Aplicación web para que una congregación de la IPUC maneje sus seis comités y diez sub-comités:
cuentas por cargo con información estrictamente separada por comité, aprobaciones en dos niveles,
cronograma anual, padrón de miembros con reportes, acompañamiento de simpatizantes, asistencia y
presupuesto. Diecisiete personas con cargo, en su mayoría desde el celular.

Enfoque técnico: Laravel 12 + Inertia 2 + React 19 sobre el kit inicial oficial, sin API pública.
La lógica vive en los controladores y se verifica con pruebas de funcionalidad que entran por ruta
HTTP como un cargo concreto, porque es lo que ejercita ruta, policy y filtro de alcance a la vez.
El filtro de alcance —la pieza crítica, principio no negociable I— se construye en tres capas
independientes (contexto por petición, global scope en el modelo, policy por acción) más una prueba
de arquitectura que falla si un modelo nuevo olvida el rasgo. El árbol de comités es una sola tabla
con `parent_id`, y las cinco clases de aprobación viven en una sola tabla polimórfica con el
escalamiento concentrado en un único archivo.

## Technical Context

**Language/Version**: PHP 8.4 (servidor), TypeScript/React 19 (interfaz). Versiones fijadas por la
constitución; no se actualizan sin decisión explícita.

**Primary Dependencies**: Laravel 12, Inertia.js 2, React 19, Tailwind CSS 4, Vite. Base: kit
inicial oficial de Laravel 12 para React. Tres dependencias añadidas con justificación escrita
(ver Constitution Check y [research.md](./research.md)): bitácora de cambios
(`spatie/laravel-activitylog`), generador de hojas de cálculo (`maatwebsite/excel`), generador de
PWA (`vite-plugin-pwa`).

**Storage**: MySQL 8. Las migraciones son la única fuente de verdad del esquema; la base no se
modifica a mano. Los archivos adjuntos —evidencias de actividad y soportes de gasto— van al **disco
del servidor del contrato**, fuera de la carpeta pública, y se sirven por una ruta que pasa por la
policy, nunca por URL directa (RF-078). No se copian a ningún servicio externo: la constitución
prohíbe que los datos del cliente salgan del servidor acordado. El respaldo diario debe ampliarse
para incluirlos (DEP-07).

**Testing**: Pest con factories sobre base de datos de pruebas. Tres familias: `Feature/` (la
mayoría, entran por ruta como un cargo concreto), `Unit/` (cálculos puros: compromiso, avance,
estadísticas, saldos) y `Architecture/` (cobertura del filtro de alcance y conteo de consultas por
petición).

**Target Platform**: navegador móvil primero (360 px), instalable como PWA. Servidor Linux del
contrato: 8 núcleos / 16 hilos Xeon-D, 16 GB RAM, 2 TB. Entorno local Laragon en
`C:\laragon\www\control-actividades`. Zona horaria `America/Bogota`, moneda COP.

**Project Type**: aplicación web monolítica con interfaz servida por Inertia. Sin API pública: una
superficie menos que proteger.

**Performance Goals**: pantalla principal en menos de 3 segundos sobre 3G simulada (RF-059);
reporte de la congregación en menos de un minuto (CE-07); ninguna petición por encima de 5
consultas a la base de datos, propiedades compartidas incluidas (constitución; criterio de medición
y reparto en Constitution Check), verificado por prueba automatizada.

**Constraints**: cero desplazamiento horizontal a 360 px; zonas pulsables de 48x48 px; texto de
contenido de 18 px; contraste AA (RF-058, constitución 2.1.0). Sin Livewire ni Filament. Sin `DB::` ni SQL crudo sobre tablas con
ámbito. Sin avisos externos: ni correo, ni WhatsApp, ni mensaje de texto (fuera de alcance).

**Scale/Scope**: 1 sede (modelo preparado para varias), ~140 miembros, 24 historias de usuario, 80
requisitos funcionales. La estructura de comités **no tiene cantidad fija**: tres niveles —comité,
sub-comité y equipo—, administrados por el pastor (RF-063 a RF-066), y las cuentas son una por cargo
vigente. Cuatro hitos semanales. Las
pantallas no se cuentan a mano: son las rutas GET que responden con Inertia en `routes/web.php`,
contratadas una por una en [contracts/pages.md](./contracts/pages.md), y es de ahí —de
`Route::getRoutes()`— de donde las pruebas derivan la lista.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Constitución v2.1.0 (ratificada 2026-10-14, enmendada 2026-10-18 y 2026-10-24). Evaluación del plan
contra los cinco principios y las restricciones. **Resultado: la puerta pasa, con dos correcciones
aplicadas al plan y cinco vacíos cerrados.** La gobernanza manda que, si un plan contradice la
constitución, se corrige el plan.

La enmienda 2.1.0 del 2026-10-24 amplió el alcance contratado y subió los mínimos del principio II.
Lo que eso exige de este plan está en la subsección «Lo que cambia con la ampliación del
2026-10-24», al final de esta puerta.

| Principio / restricción | Veredicto | Sustento |
|---|---|---|
| **I. Privacidad entre comités (NO NEGOCIABLE)** | **Corregido → pasa** | Tres capas + `ScopeCoverageTest` + prohibición de SQL crudo cumplen con holgura. **Corrección C-01**: el plan de entrada resolvía el caso de dos cargos como «unión de ambos conjuntos»; la aclaración del 2026-10-18 fijó **cargo activo** (RF-007). Se corrige: el ámbito de una petición es el de un solo cargo. Este plan ejerce la **única excepción enumerada** del principio —el cronograma compartido, `forSharedCalendar()`—, que la enmienda del 2026-10-18 incorporó a la constitución v2.0.0 con sus siete datos, su método de **solo lectura** y su prueba obligatoria de lista blanca (R-11 de [research.md](./research.md), D-15). La excepción está **amparada por la constitución**, no solo decidida por el plan, y no se usa ninguna otra salida del filtro. **La enmienda 2.1.0 obliga a reescribir este filtro para tres niveles**: es el trabajo más delicado de la ampliación y no se puede recortar (ver «Lo que cambia con la ampliación del 2026-10-24» y RG-14). Las evidencias y los soportes **no** amplían la excepción: siguen el filtro completo. |
| **II. Primero el celular** | Pasa, con revisión pendiente | 360 px primero, tablas como tarjetas, PWA por complemento de compilación, apertura bajo 3 s. Metas verificables en RF-058 y RF-059. La enmienda 2.1.0 subió los mínimos a **48x48 px** de zona pulsable, **18 px** de texto y contraste **AA**, por el público de adultos mayores: las pantallas ya construidas se revisan contra los mínimos nuevos antes de cerrar el hito en curso. |
| **III. Toda decisión deja rastro** | Pasa, con un vacío cerrado | Bitácora en los siete modelos que importan; `approvals` resueltas no se editan. **Vacío V-01**: la tabla del paquete no lleva `site_id`, así que queda fuera del filtro automático. Se cierra con una regla explícita (ver abajo). |
| **IV. El lenguaje es el de la congregación** | **Corregido → pasa** | Textos en español de Colombia, de «usted», vocabulario IPUC. **Corrección C-02**: el plan de entrada ponía las rutas en español; la sección de idioma de la constitución lista **rutas** entre lo que se escribe en inglés. Se corrige a rutas en inglés. Los textos visibles siguen en español. |
| **V. Ninguna tarea se cierra sin pruebas (NO NEGOCIABLE)** | Pasa | Pest con factories. Cobertura obligatoria cubierta: filtro de alcance (`Feature/Scope/` + `Architecture/`), flujo de aprobaciones, cálculos de estadísticas y presupuesto (`Unit/`), validación de formularios (una Request por formulario). |
| Stack con versiones exactas | Pasa, con verificación pendiente | PHP 8.4 · Laravel 12 · MySQL 8 · Inertia 2 · React 19 · Tailwind 4 · Vite · Pest. Las tres dependencias añadidas se fijan a versión exacta al instalar; la compatibilidad con Laravel 12 se confirmó para las dos de Composer ([research.md](./research.md), R-02). |
| Multi-sede desde el día 1 | Pasa | `site_id` con llave foránea en toda tabla de la congregación y global scope automático. |
| Autorización en policies y gates | Pasa | Una policy por modelo con ámbito. El frontend solo refleja permisos ya resueltos (`canEdit`, `can*` en las propiedades). |
| Dependencias justificadas por escrito | Pasa | Tres, cada una con su razón y su alternativa descartada. Ninguna más sin enmienda a este documento. |
| Migraciones como única fuente de verdad | Pasa | Sin cambios a mano en la base. |
| Simple antes que completo · alcance contratado | Pasa | Laravel estándar, sin capas extra. El alcance coincide con el contratado; nada fuera de él. |
| Máximo 5 consultas por pantalla | **Pasa, con dos excepciones declaradas** | `Architecture/QueryBudgetTest` cuenta y falla al pasarse. La constitución fija el número pero no dice qué se cuenta: el criterio de medición y el reparto quedan fijados abajo. Las dos excepciones declaradas son **`/reports`** —RF-036 pide ocho grupos de cifras y dos exigen agrupar— y **`/`, la pantalla principal**; las dos resuelven parte de sus cifras en propiedades diferidas, cada una con su propio presupuesto. El techo de 5 no se sube. |
| Datos del cliente y datos ficticios | Pasa, con un vacío cerrado | Semilla ficticia. **Vacío V-02**: faltaban los campos de autorización de tratamiento de datos (RF-061) y el aviso de privacidad (RF-062). Se agregan al modelo. |

### Cómo se mide el presupuesto de cinco consultas

El máximo de cinco consultas lo fija la constitución y no se sube sin enmienda. Desde la 2.3.1
la constitución dice también **qué se cuenta**. Queda fijado así.

- **Cuentan las consultas propias de la pantalla, middleware incluido**: `ResolveScope`,
  `HandleInertiaRequests`, policies y controlador. Un criterio que excluyera una de esas capas
  solo lograría que el costo se mudara allí.
- **No cuentan las tres fijas de Laravel** en cada petición: leer la sesión, cargar a la persona
  autenticada y guardar la sesión. Ninguna pantalla las puede agregar ni quitar, así que no se
  pueden esconder costos en ellas; `QueryBudgetTest` verifica aparte que sigan siendo tres.
- **El número no crece con los datos.** `QueryBudgetTest` mide cada pantalla como el pastor, un
  presidente y un líder, con datos en los tres niveles; luego multiplica los datos por diez y
  exige el mismo número. Una consulta dentro de un bucle la pone roja.
- **El bloque compartido cuesta 3 consultas y ninguna más**, con su propia aserción en
  `QueryBudgetTest`: (1) los cargos vigentes, (2) el comité de cada uno, por carga anticipada, y
  (3) el sub-árbol del cargo activo.
- **`scopeHidden` y `pendingCount` son propiedades diferidas de Inertia.** Dejan de viajar en
  toda respuesta y se resuelven solo donde se muestran —la cinta de alcance y la pantalla
  principal—, en una petición aparte. RF-009 pide informar cuánta información queda fuera, no
  informarlo en cada pantalla, y la regla de
  [contracts/shared-props.md](./contracts/shared-props.md) que obliga a mostrar el ámbito pide
  `scope.label`, no los conteos. La agregación de esos conteos se arma desde los propios modelos
  (`addSelect` sobre un constructor de Eloquent), de modo que los *global scopes* se aplican y no
  hace falta SQL crudo.
- **A cada pantalla le quedan 3 consultas propias en la primera carga.** Se cumple con una
  agregación por eje (decisión R-08 de [research.md](./research.md)) y con paginación simple, no
  contando filas por tarjeta.
- **Los cargos vigentes se consultan en cada petición; no se guardan en la sesión.** Cachearlos
  ahorraría una consulta, pero un cargo cerrado seguiría abriendo puertas hasta el siguiente
  ingreso, y el principio I no se negocia por una consulta.
- **Si una pantalla no cabe**, en este orden: (1) consolidar en agregaciones; (2) partirla y dejar
  la parte secundaria como propiedad diferida que el usuario abre, servida por su propia petición
  parcial y con su propia entrada en `QueryBudgetTest`; (3) si ninguna de las dos alcanza, se sube
  al autor como enmienda a la constitución, con la medición adjunta. `QueryBudgetTest` no lleva
  lista de excepciones: las únicas son las dos que este documento declara.

Las dos excepciones declaradas, con nombre:

- **`/reports`.** RF-036 pide ocho grupos de cifras y dos de ellos —la distribución por comité y
  los bautismos por año— exigen `GROUP BY`, así que no se consolidan con los demás en una sola
  agregación condicional. Las cifras viajan en propiedades diferidas, cada una con su propio
  presupuesto de cinco consultas por respuesta; si los ocho ejes no caben en cinco, se separan en
  **dos** propiedades diferidas antes de tocar el límite.
- **`/`, la pantalla principal.** El reparto se declara igual: `kpis` y `pendingApprovals[]`
  viajan en la primera carga, y `myRequests[]`, `committeeProgress[]`, `upcoming[]` y
  `pendingProfiles` van diferidas.

Partir una pantalla se decide aquí, no en el controlador: cuesta un viaje más en 3G y eso pesa
contra RF-059. La lista de pantallas que `QueryBudgetTest` mide tampoco se escribe a mano: sale de
`Route::getRoutes()` —las rutas GET que responden con Inertia—, con lista blanca justificada para
`/login` y `/password/change`.

### Correcciones aplicadas al plan de entrada

- **C-01 · Cargo activo, no unión de ámbitos.** `CurrentScope` resuelve **un** cargo activo por
  petición, guardado en la sesión. Si la persona tiene dos cargos, un selector visible lo cambia y
  toda pantalla indica con cuál está actuando. El ámbito nunca es la unión. Afecta
  `Middleware/ResolveScope`, `Support/Scope/CurrentScope`, las propiedades compartidas
  (`scope.activeRole`, `scope.availableRoles`) y la prueba de aislamiento de CL-05.
- **C-02 · Rutas en inglés.** `/committees`, `/approvals`, `/activities`, `/annual-plan`,
  `/members`, `/sympathizers`, `/attendance`, `/budget`, `/reports`, `/users`, `/login`,
  `/password/change`. Los nombres de las pantallas y todo texto visible siguen en español. Si
  prefiere URLs en español, es una enmienda PATCH a la sección de idioma de la constitución, no una
  excepción del plan.

### Vacíos del plan de entrada que se cierran aquí

- **V-01 · La bitácora queda fuera del filtro automático.** `activity_log` es del paquete y no
  lleva `site_id`. Regla: ninguna pantalla consulta `activity_log` directamente. El historial se
  lee **siempre** a través del modelo apuntado, que sí carga el ámbito, y
  `ScopeCoverageTest` mantiene una lista blanca documentada con esa única tabla.
- **V-02 · Autorización de tratamiento de datos.** `members` y `sympathizers` reciben
  `data_consent` (bool), `data_consent_at` y `data_consent_means`, y las pantallas de registro
  muestran el aviso de privacidad (RF-061, RF-062, CE-12).
- **V-03 · Ficha pendiente de completar.** `members` recibe `profile_pending` (bool) para la ficha
  mínima creada al habilitar una cuenta (RF-003, RF-031, CL-16).
- **V-04 · Gasto del propio presidente.** `ApprovalFlow` codifica RF-018: el gasto del propio
  comité lo autoriza el presidente sin escalar al pastor, y queda registrado con su nombre.
- **V-05 · Transiciones de estado de una actividad.** El plan de entrada no decía cuándo una
  actividad aprobada pasa a «en curso» y a «cumplida». Queda definido como regla única y probada en
  [data-model.md](./data-model.md), sección de transiciones.

### Decisión cerrada respecto de la especificación

- **Fecha de nacimiento, con la edad declarada como respaldo.** RF-031 pedía «edad». El plan
  guarda `birth_date` y calcula la edad al consultar, porque una edad guardada se vuelve falsa al
  año siguiente y rompería los reportes (CE-07). RF-031 quedó corregido en la especificación el
  2026-10-18, así que el documento y el código ya dicen lo mismo: esto dejó de ser una desviación
  y es una decisión cerrada.
- **La ficha admite las dos entradas.** La congregación no tiene la fecha de nacimiento de todos,
  y lo que sí tiene escrito en el cuaderno es la edad. Por eso `members` lleva `birth_date` (nulo
  permitido) y también `age_reported` (entero, nulo) con `age_reported_at` (fecha, nulo): la edad
  que el hermano declaró y el día en que la declaró. El formulario pide la fecha de nacimiento y,
  cuando no se sabe, ofrece «No la sé — anote la edad». La edad se calcula de `birth_date` cuando
  existe y del par `age_reported` + `age_reported_at` cuando no, y los reportes por rango de edad
  usan las dos fuentes.
- **Nunca se bloquea el registro por un dato que la congregación no tiene** (principio IV). Por
  eso RF-032 pide **confirmar que la persona es mayor de dieciocho años** cuando no hay
  `birth_date` y se marca un estado civil distinto de soltero, en vez de exigir la fecha: un
  hermano casado de cuarenta se registra igual. Queda abierto el riesgo RG-05: el campo de fecha
  es opcional en la importación y la ficha queda con `profile_pending` hasta completarla.

### Re-evaluación después de la Fase 1

Hecha sobre [data-model.md](./data-model.md), [contracts/](./contracts/) y
[quickstart.md](./quickstart.md). **La puerta sigue pasando; el diseño no introdujo violaciones
nuevas.**

| Principio | Qué lo sostiene en el diseño |
|---|---|
| I | Tabla de cobertura del filtro en data-model §9: 8 tablas con filtro de cargo, 8 que se consultan desde su padre, 1 lista blanca justificada. Una sola excepción con nombre (`forSharedCalendar`), **de solo lectura**, con los siete datos del cronograma enumerados y una prueba de **lista blanca** que compara el juego de llaves: una columna nueva en `activities` pone la prueba roja hasta que alguien decida si se comparte. La policy de `Activity` sigue negando toda escritura sobre una actividad ajena. `scopeHidden` lleva solo conteos y viaja como propiedad diferida. Policy más estrecha para simpatizantes. |
| II | `quickstart.md` §4 obliga a hacer cada comprobación a 360 px primero, y §4.9 mide la apertura con red limitada. |
| III | Bitácora en 7 modelos; `approvals` resueltas y `budget_movements` inmutables por regla de modelo, no de interfaz; transiciones de estado registradas; el historial se lee por la relación del modelo, nunca de la tabla del paquete. |
| IV | Rutas en inglés, tablas y columnas en inglés, textos y nombres de pantalla en español. `flash.message` explica en una frase qué pasó y qué hacer. |
| V | Cobertura obligatoria cubierta y nombrada: `Feature/Scope/` + 3 pruebas de arquitectura (filtro), `Feature/Approvals/` (cadena completa, incluida RF-018), `Unit/` (compromiso, avance, estadísticas, saldo), una Request por formulario (validación). Cada sección de contrato termina en «qué hay que probar». |
| 5 consultas | `QueryBudgetTest` con el criterio de medición y el reparto fijados arriba, más la estrategia de agregación por eje para la pantalla de reportes, que es la de mayor riesgo (decisión R-08 de [research.md](./research.md)) y una de las dos excepciones declaradas. |

Lo único que queda abierto son los puntos que [research.md](./research.md) enumera como pendientes,
ninguno bloqueante para empezar el hito 1.

### Lo que cambia con la ampliación del 2026-10-24

La enmienda 2.1.0 incorporó al alcance contratado cuatro módulos y subió los mínimos del principio
II. Doce decisiones del autor, registradas en `spec.md` → `## Clarifications` → Session 2026-10-24.
Lo que exige de este plan:

| Decisión | Qué toca en el plan |
|---|---|
| **Tres niveles** de estructura, administrables por el pastor | Es el cambio más caro, y cae dentro del principio no negociable I. `CurrentScope` pasa de «el nodo y sus hijos» a «el nodo y su sub-árbol»; el rasgo de comité compara contra descendientes; las cuatro policies cambian su condición; y **todas** las pruebas de aislamiento se rehacen. Sin consultas recursivas: `depth` está acotado a 3, así que el sub-árbol sale en una consulta (ver `data-model.md` §9) |
| **Cuatro cargos**: presidente, secretario, tesorero, líder | `committee_user.role` crece a cuatro valores. El ámbito **no** cambia: secretario y tesorero ven lo mismo que su presidente y se diferencian en lo que pueden hacer, que lo resuelven las policies |
| **Actas** enganchadas a la reunión, numeradas e inmutables | Tres piezas nuevas: `MinuteController`, `Support/Minutes/MinuteNumber.php` para el correlativo por comité y año, y la vista de impresión. La inmutabilidad se hace cumplir en el modelo, como en `approvals` |
| **Archivos** en el disco del servidor, 10 MB, comprimidos, no se borran | `AttachmentController` más `Support/Attachments/`. Se guardan fuera de la carpeta pública y se sirven por una ruta con policy: si estuvieran en `public/`, cualquiera con el enlace vería una evidencia ajena y el principio I se caería por la puerta de atrás |
| Evidencias y soportes **no** viajan por la excepción del cronograma | `forSharedCalendar()` no se toca. Sigue entregando los siete datos y nada más. No hace falta enmendar el principio I otra vez |
| **Mínimos de 48x48, 18 px y contraste AA** | Revisión de las pantallas ya construidas y actualización del diseño base |
| **Compromisos** del acta convertibles en tarea | `minute_commitments.activity_task_id` engancha con `activity_tasks`, que ya alimenta el avance calculado |
| Plan de trabajo = cronograma anual | **No hay módulo nuevo.** Se evitó construir un segundo lugar donde programar actividades, que habría contradicho al cronograma |

**Lo que NO cambia, y conviene decirlo:** los dos principios NO NEGOCIABLES siguen sin excepción, el
techo de cinco consultas por pantalla no sube, la única excepción enumerada al filtro de alcance
sigue siendo la del cronograma compartido, y las migraciones siguen siendo la única fuente de verdad
del esquema.

**El riesgo que esto crea está en RG-11.** El autor decidió que los cuatro módulos entran completos
**sin mover la fecha**, contra la recomendación de cotizarlos como fase 2. Como el plazo y el alcance
quedan fijos y la calidad no se negocia, lo que cede es la promesa de que cada hito cierre de punta
a punta.

## Project Structure

### Documentation (this feature)

```text
specs/001-sistema-comites-ipuc/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── README.md
│   ├── shared-props.md
│   └── pages.md
├── checklists/
│   └── requirements.md  # /speckit-specify + /speckit-clarify
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Console/Commands/BackupDatabase.php  # respaldo diario por tarea programada
├── Exceptions/OutOfScopeException.php   # «no disponible» ante lo ajeno (RF-008)
├── Exports/                             # una clase por reporte descargable
│   ├── MemberReportExport.php           # padrón con los filtros aplicados (RF-038)
│   ├── MemberTemplateExport.php         # plantilla de importación (HU-18)
│   └── StatsReportExport.php            # estadísticas de la congregación (CE-07)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                        # del kit inicial, adaptado a usuario en vez de correo
│   │   ├── DashboardController.php
│   │   ├── CommitteeController.php
│   │   ├── CommitteeUserController.php  # cargos: asignar, cerrar y relevar (HU-02)
│   │   ├── ApprovalController.php
│   │   ├── ActivityController.php
│   │   ├── ActivityTaskController.php
│   │   ├── AnnualPlanController.php
│   │   ├── MemberController.php
│   │   ├── MemberImportController.php
│   │   ├── SympathizerController.php
│   │   ├── SympathizerVisitController.php
│   │   ├── MeetingController.php
│   │   ├── MinuteController.php         # actas: borrador, cierre e impresión (RF-067 a RF-072)
│   │   ├── MinuteCommitmentController.php # compromisos y su paso a tarea (RF-073)
│   │   ├── AttachmentController.php     # subir, servir con policy y reemplazar (RF-075 a RF-080)
│   │   ├── CommitteeStructureController.php # solo el pastor: crear, renombrar, inactivar (HU-21)
│   │   ├── BudgetController.php
│   │   ├── BudgetMovementController.php # gastos, asignaciones e ingresos (RF-051)
│   │   ├── ReportController.php
│   │   ├── HistoryController.php        # historial leído por el modelo apuntado (V-01)
│   │   ├── RoleSwitchController.php     # C-01: cambiar el cargo activo
│   │   └── UserController.php           # solo el pastor
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php    # propiedades compartidas y su presupuesto
│   │   ├── ResolveScope.php             # fija el ámbito del cargo activo en cada petición
│   │   ├── EnsurePasswordChanged.php    # obliga el cambio de clave del primer ingreso
│   │   └── EnsureActiveUser.php         # corta la sesión de una cuenta desactivada
│   └── Requests/                        # una por formulario, reglas y mensajes en español
├── Imports/MemberImport.php             # carga de miembros con previsualización (HU-18)
├── Models/
│   ├── Concerns/
│   │   ├── BelongsToSite.php            # filtro de sede (multi-sede desde el día 1)
│   │   └── BelongsToCommitteeScope.php  # filtro del ámbito del cargo activo
│   ├── Site.php
│   ├── User.php
│   ├── Committee.php
│   ├── CommitteeUser.php
│   ├── Member.php
│   ├── Sympathizer.php
│   ├── SympathizerStage.php
│   ├── SympathizerVisit.php
│   ├── AnnualPlan.php
│   ├── Activity.php
│   ├── ActivityTask.php
│   ├── Approval.php
│   ├── Meeting.php
│   ├── MeetingAttendance.php
│   ├── Budget.php
│   └── BudgetMovement.php
├── Policies/                            # una por modelo con ámbito
├── Providers/AppServiceProvider.php     # registra policies y propiedades compartidas
└── Support/
    ├── Scope/CurrentScope.php           # quién soy y qué veo, resuelto una vez por petición
    ├── Approvals/ApprovalFlow.php       # la cadena de escalamiento, en un solo lugar
    ├── Approvals/TransferPending.php    # traslada lo pendiente en un relevo (CL-06)
    ├── Activities/ScheduleConflict.php  # aviso de cruce de fecha y lugar (RF-028)
    ├── Activities/ActivityStatus.php    # transiciones de estado (V-05, research.md R-04)
    ├── Sympathizers/CommitmentLevel.php # RF-043, cálculo puro
    ├── Sympathizers/DuplicateCheck.php  # avisa si la persona ya está registrada
    ├── Sympathizers/ConvertToMember.php # paso de simpatizante a miembro (HU-14)
    ├── Meetings/AttendanceRate.php      # porcentaje sobre reuniones convocadas (RF-048)
    ├── Budget/BalanceCalculator.php     # el saldo se calcula, no se guarda (D-10)
    ├── Minutes/MinuteNumber.php          # correlativo por comité y por año (RF-070)
    ├── Attachments/StoreAttachment.php   # valida tipo y peso, comprime y guarda (RF-076, RF-077)
    ├── Attachments/ServeAttachment.php   # entrega el archivo pasando por la policy (RF-080)
    ├── Committees/SubtreeResolver.php    # el sub-árbol de un nodo en una consulta (tres niveles)
    └── Reports/MemberStats.php          # agregaciones por eje (research.md R-08)

bootstrap/app.php                        # registra middleware y manejo de excepciones

resources/js/
├── pages/                               # una carpeta por sección, nombres que siguen las rutas
│   ├── Dashboard/  Committees/  Approvals/  Activities/
│   ├── Members/  Sympathizers/  Meetings/  Budget/  Reports/  Users/
│   ├── Minutes/                         # Form, Show y Print del acta (HU-22)
│   ├── Structure/                       # administración de los tres niveles, solo pastor (HU-21)
│   ├── Errors/OutOfScope.tsx            # «no disponible», sin revelar nada (RF-008)
│   └── Auth/
├── components/
│   ├── ui/                              # Form, CardTable, ChartTableToggle, campos, pastillas
│   └── domain/                          # StepPath, ApprovalCard, CommitteeTree, ScopeBanner,
│                                        # RoleSwitcher (C-01), PrivacyNotice (V-02), MonthGrid,
│                                        # ActivityBadge, FilterBar, CarePill, ChangeTrail,
│                                        # BarChart, DonutChart, OverBudgetNotice,
│                                        # TreasuryNotice, RoleAssigner, FileUpload,
│                                        # AttachmentGallery, CommitmentList, StructureTree
├── layouts/AppLayout.tsx                # barra, menú, cinta de alcance, contador de pendientes
└── lib/                                 # fechas, moneda, draft-storage, update-check

resources/css/
├── app.css                              # estilos base y los mínimos del principio II (T044)
└── print.css                            # vista de impresión de reportes y actas (T107, HU-22)

database/
├── migrations/
├── seeders/                             # datos de la presentación, ficticios
│   ├── DatabaseSeeder.php               # orquesta la semilla completa
│   ├── SiteSeeder.php  CommitteeSeeder.php  UserSeeder.php
│   ├── MemberSeeder.php  SympathizerSeeder.php
│   └── ActivitySeeder.php  ApprovalSeeder.php
└── factories/

routes/
├── web.php                              # rutas en inglés (C-02)
├── auth.php                             # del kit, sin registro público (D-05)
└── console.php                          # programación del respaldo diario

tests/
├── Pest.php                             # configuración común y ayudas de autenticación
├── Feature/                             # entran por ruta como un cargo concreto
│   ├── Scope/                           # aislamiento entre comités
│   ├── Auth/  Committees/  Users/  Dashboard/  Approvals/  Activities/
│   ├── Members/  Sympathizers/  Meetings/  Budget/  Reports/  History/
│   └── BackupCommandTest.php            # el respaldo no escribe datos de personas
├── Unit/                                # cálculos puros: compromiso, avance, estadísticas, saldo
│   ├── Scope/  Approvals/  Activities/  Members/
│   └── Sympathizers/  Meetings/  Budget/  Reports/
├── Architecture/
│   ├── ScopeCoverageTest.php            # falla si un modelo con site_id no usa los rasgos
│   ├── NoRawQueriesTest.php             # falla si aparece DB:: o SQL crudo sobre tablas con ámbito
│   └── QueryBudgetTest.php              # cuenta consultas por pantalla, falla pasando de 5
└── fixtures/members-mixed.xlsx          # hoja con errores a propósito (importación)

config/                                  # app.php (zona y moneda), activitylog.php
lang/es/                                 # auth.php y validation.php, español de Colombia

public/
├── manifest.webmanifest                 # instalable en el celular (principio II)
├── icons/                               # iconos de la aplicación instalada
└── templates/members.xlsx               # plantilla de importación de miembros (HU-18)

docs/                                    # documentación en español (principio IV)
├── convenciones.md  criterios-exito.md  puesta-en-marcha.md
├── despliegue.md  revision-360.md  pruebas-rendimiento.md
├── capacitacion.md  acta-entrega.md
├── manual/                              # manual de uso, una página por sección (T159)
└── hitos/hito-1.md  hito-2.md  hito-3.md  hito-4.md

.github/workflows/
├── pruebas.yml                          # puerta automatizada antes de fusionar
└── deploy.yml                           # despliegue al fusionar a main (D-11)

vite.config.ts                           # compilación e instalación como PWA (D-13)
phpunit.xml  .env.example                # base de pruebas y variables del despliegue
```

**Structure Decision**: Laravel estándar, sin capas adicionales (D-01 del registro de decisiones).
La lógica de cada pantalla vive en su controlador; lo que se repite o se puede equivocar
se extrae a `app/Support/` con nombre propio —el escalamiento de aprobaciones, el ámbito del cargo
activo, el nivel de compromiso, las agregaciones de reportes— precisamente para que haya **un solo
lugar** donde esas reglas puedan fallar y un solo lugar donde probarlas. No hay acciones por caso
de uso ni repositorios: con 20 historias y cuatro semanas, cada capa extra es tiempo que se le
quita al principio I. Las pruebas de funcionalidad por HTTP cubren el riesgo de tener la lógica en
el controlador, porque ejercitan ruta, autorización y filtro juntos.

## Registro de decisiones

Las quince decisiones de arquitectura de este plan, con lo que se descartó en cada una. Se citan
por su identificador en `tasks.md`, `data-model.md`, `research.md` y los contratos, así que viven
aquí y no en el documento de entrada. Tres familias de identificadores conviven en los artefactos
y cada una tiene prefijo propio, para que ninguna cita quede ambigua: **RG-xx** son los riesgos de
este plan (registro siguiente), **D-xx** son estas decisiones de arquitectura del plan y
**DEP-xx** son las dependencias del cliente ([spec.md](./spec.md), sección «Dependencias»), que
antes se llamaban D-01 a D-04 y chocaban con las cuatro primeras de este registro. Aparte quedan
los **R-xx**, que son las decisiones de investigación de [research.md](./research.md).

| # | Decisión | Por qué | Qué se descartó |
|---|---|---|---|
| D-01 | Laravel estándar, lógica en controladores | Menos archivos y menos ceremonia; con 20 historias y 4 semanas, la velocidad manda. Las pruebas de funcionalidad por HTTP cubren el riesgo. | Acciones por caso de uso; modular por dominio |
| D-02 | Una tabla `committees` con `parent_id` | Un solo filtro de alcance, una sola policy, un solo conjunto de pantallas. El CL-01 sale gratis. | Dos tablas separadas |
| D-03 | Tabla `approvals` polimórfica | Una bandeja, un flujo, un sitio donde vive el escalamiento. Un sexto tipo no toca el flujo. | Una tabla por tipo |
| D-04 | Rasgos con *global scope* más prueba de cobertura | El principio I debe ser imposible de olvidar, no solo difícil. La prueba es la garantía. | Filtrar en cada consulta; repositorios |
| D-05 | Kit inicial oficial de Laravel 12 para React | Es exactamente el stack de la constitución, ya armado. Se adapta a entrar con usuario y se retira el registro público. | Breeze; autenticación a mano |
| D-06 | `committee_user` con vigencia | Único diseño que cumple CL-05 (dos cargos) y conserva la historia del relevo (RN-09). | Campo de cargo en `users`; paquete de roles |
| D-07 | Paquete de bitácora | El principio III exige valores anteriores. Escribirlo a mano son ~2 días y deja casos sin cubrir. | Tabla de auditoría propia |
| D-08 | Paquete de hojas de cálculo | El pastor lleva estas cifras a reuniones de distrito; necesita un archivo presentable. | CSV nativo |
| D-09 | Fecha de nacimiento, con la edad declarada como respaldo | La edad guardada envejece y rompe los reportes, y Serenatas necesita cumpleaños; pero la congregación no tiene la fecha de todos, así que `age_reported` con `age_reported_at` deja registrar igual (principio IV). | Guardar solo la edad; exigir la fecha |
| D-10 | Compromiso y saldo calculados, no guardados | Un dato guardado es un dato que envejece sin que nadie lo note. | Columnas con el valor |
| D-11 | Despliegue con script propio disparado al fusionar | Sin costo mensual, que pesa cuando el ingreso es $100.000. Respaldo diario por tarea programada. | Panel de pago; contenedores |
| D-12 | Prueba que cuenta consultas por pantalla | La constitución fija un máximo de 5; un límite que no se mide no se cumple. | Revisión a ojo |
| D-13 | PWA por complemento de compilación | El trabajador de servicio escrito a mano es fuente conocida de fallas difíciles de reproducir. | Manifiesto a mano |
| D-14 | Semilla con los datos de la presentación | Sirve para desarrollar, probar y capacitar; el pastor reconoce lo que ya vio. Datos ficticios, como exige la constitución. | Conjunto mínimo; solo generadores |
| D-15 | `forSharedCalendar()` como única excepción al filtro | RF-010 obliga a abrir el cronograma y la constitución v2.0.0 enumera la excepción. Mejor una puerta con nombre, de solo lectura, vigilada y probada, que filtros inconsistentes. | Quitar el filtro al modelo `activities` |
| D-16 | Tres niveles con `depth` guardado, sin consultas recursivas | La profundidad está acotada a 3 por RF-011, así que el sub-árbol de un nodo sale comparando `parent_id` y el del padre: una consulta. Mantiene el techo de cinco consultas por pantalla sin enmendar la constitución. | Árbol de profundidad libre con CTE recursivas; tabla de cierre transitivo |
| D-17 | Archivos en el disco del servidor, fuera de `public/`, servidos por una ruta con policy | La constitución prohíbe que los datos del cliente salgan del servidor acordado. Y en `public/` cualquiera con el enlace vería una evidencia ajena: el principio I se caería por la puerta de atrás, sin que ninguna prueba de aislamiento lo note. | Servicio externo tipo S3; servir desde `public/` con nombre difícil de adivinar |
| D-18 | El acta cuelga de la reunión, y no duplica fecha, lugar ni asistentes | Una sola verdad sobre quién asistió. Si el acta copiara los asistentes, el porcentaje de asistencia y el acta podrían contradecirse, y el acta es el documento que la congregación trata como definitivo. | Acta independiente con sus propios asistentes |
| D-19 | El compromiso del acta es texto; convertirlo en tarea es opcional | Decisión del autor el 2026-10-24. El acta conserva lo acordado palabra por palabra, y el seguimiento se agrega solo donde hace falta, reutilizando `activity_tasks` y el avance que ya se calcula. | Todo compromiso es tarea; compromiso solo como texto sin seguimiento |
| D-20 | Secretario y tesorero solo en el primer nivel, y sin ampliar el ámbito | Un sub-comité no lleva libro de actas propio ni maneja presupuesto aparte. Y hacer que un cargo amplíe el ámbito habría multiplicado los casos del principio I: aquí el ámbito lo da el nodo, y el cargo solo decide qué se puede hacer. | Secretario y tesorero en los tres niveles; cargos con ámbito propio |
| D-21 | El filtro del cargo se activa antes de buscar el registro de la ruta, y una cuenta de usuario se busca solo dentro de la sede | Laravel busca el registro de una dirección como `/committees/5` antes de pasar por los middleware agregados, y en ese momento el filtro todavía estaba apagado: el 2026-10-26 una presidenta abrió por su número un comité ajeno y lo vio. Hoy ninguna pantalla lo exponía, pero toda pantalla futura con un número en la dirección lo habría hecho. `EnsureActiveUser` y `ResolveScope` pasan antes de `SubstituteBindings` en `bootstrap/app.php`. La tabla de cuentas no lleva filtro de sede, porque la entrada al sistema busca a la persona antes de saber su sede, así que `User::resolveRouteBinding` lo aplica a mano. `DirectAccessTest` falla si el orden se pierde. |

## Registro de riesgos

Los diez riesgos del plan, en el orden en que importan. Se citan como **RG-xx** para no
confundirlos con las decisiones de investigación **R-xx** de [research.md](./research.md), que son
otra cosa. Al trasladarlos se corrigieron cuatro datos: RG-04 cuenta 62 requisitos, no 57, y lleva
la mitigación vigente de [tasks.md](./tasks.md); RG-05 recoge la doble entrada de edad; RG-08
nombra al pastor y dice qué pasa si no pide la importación; RG-09 cuenta las tres dependencias que
este plan justifica.

| # | Riesgo | Impacto | Mitigación |
|---|---|---|---|
| RG-01 | Una consulta se salta el filtro de alcance | **Crítico.** Es la promesa que la congregación aprobó. | Tres capas más `ScopeCoverageTest`, prohibición de SQL crudo y una prueba de aislamiento por cada pantalla |
| RG-02 | Los controladores crecen sin control, sobre todo en aprobaciones | Medio. Código difícil de cambiar en el hito 4. | El escalamiento vive fuera, en `ApprovalFlow`. Revisión del tamaño al cerrar el hito 2 |
| RG-03 | El cronograma compartido abre más de lo debido | Alto. Contradiría el principio I delante del cliente. | Un único método de solo lectura con columnas escogidas a mano, más la prueba de lista blanca que compara el juego de llaves y el caso de escritura que debe ser negado |
| RG-04 | Cuatro semanas para 20 historias y 62 requisitos | **Alto.** Es el riesgo principal del proyecto. | Hitos del contrato; P1 completo antes que cualquier P2. **El alcance no se recorta**: si el calendario no alcanza, se mueve la fecha de entrega, con acuerdo escrito previo con el pastor. Indicador temprano: si la semana 1 termina sin el filtro de alcance desplegado y probado, la conversación va en la semana 2, no en la 4 |
| RG-05 | El cliente no tiene la fecha de nacimiento de todos los miembros | Medio. Bloquearía la carga inicial. | La ficha admite las dos entradas: fecha de nacimiento o edad declarada con la fecha de la declaración, y los reportes por rango de edad usan ambas fuentes. El campo de fecha es opcional en la importación y la ficha queda con `profile_pending` |
| RG-06 | La PWA sirve una versión vieja después de un despliegue | Medio. El cliente ve errores ya corregidos. | Versionado de los archivos compilados y aviso de «hay una versión nueva» dentro de la aplicación |
| RG-07 | El servidor de producción todavía no está aprovisionado | Medio. Bloquea el hito 4. | Levantarlo en la semana 1, no en la 4, y desplegar desde el primer hito |
| RG-08 | La importación de miembros no está listada en la Cláusula Segunda del contrato | Bajo en lo técnico, relevante en lo comercial. | Está marcada como supuesto S-02 en el spec. Confirmar con el pastor antes de construirla; si no la pide, la fase se registra como propuesta para cotizar |
| RG-09 | Tres dependencias de terceros que mantener | Bajo. | Versiones fijadas; no se actualizan sin decisión explícita (constitución) |
| RG-10 | El multi-sede nunca se usa y se paga su complejidad | Bajo. | Es una columna y dos rasgos. La constitución ya ponderó el costo de añadirlo después |
| RG-11 | Cuatro módulos nuevos entran al alcance **sin mover la fecha** | **Crítico.** Es hoy el riesgo principal del proyecto, por encima de RG-04. | Decisión del autor el 2026-10-24, tomada contra la recomendación de cotizarlos como fase 2. Los dos principios NO NEGOCIABLES se sostienen, así que lo que cede es la promesa de que cada hito cierre de punta a punta: los hitos entregan funcionalidad parcial y el cierre real se corre al final. Hay que decírselo al pastor por escrito **antes** del vencimiento, no después. Indicador temprano: si la semana 1 termina sin el filtro de alcance de tres niveles desplegado y probado, la conversación va en la semana 2 |
| RG-12 | El disco de 2 TB se llena con fotos y facturas | Medio. Bloquearía subir evidencias y, si el respaldo comparte disco, también el respaldo. | Límite de 10 MB por archivo y compresión de imágenes al recibirlas (RF-076, RF-077). Medición del espacio usado al cerrar cada hito, y aviso en lenguaje claro a quien sube y al pastor cuando el disco se acerque al límite (CL-24) |
| RG-13 | El respaldo diario no incluye los archivos | **Alto.** Una falla de disco perdería las evidencias y los soportes de factura aunque la base de datos se recupere. | DEP-07: ampliar el respaldo antes de que el módulo de archivos entre en producción. Hasta que eso esté hecho, el módulo no se despliega |
| RG-14 | Reescribir el filtro de alcance a tres niveles introduce una fuga | **Crítico.** Toca el principio no negociable I, que es lo primero que el cliente comprueba. | Las pruebas de aislamiento se rehacen **antes** de tocar `CurrentScope`, no después, y suman los casos nuevos: un líder de equipo que no ve a su sub-comité hermano, y un presidente que sí ve a sus nietos. `ScopeCoverageTest` cubre las tres tablas nuevas automáticamente |

La mitigación de RG-04 la enmendó la Política de alcance de [tasks.md](./tasks.md): el alcance no
se recorta, se mueve la fecha con acuerdo escrito previo.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

La puerta de la constitución pasa sin violaciones pendientes: las dos contradicciones se
corrigieron en el plan (C-01, C-02) en vez de justificarse. Lo que sigue son las tres adiciones de
complejidad que la constitución permite **solo con justificación escrita**, registradas aquí para
que quede el rastro.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Dependencia: bitácora de cambios (`spatie/laravel-activitylog`) | El principio III y RF-056 exigen conservar qué decía antes, quién lo cambió y cuándo, en siete modelos. | Una tabla de auditoría propia son ~2 días de trabajo y deja casos sin cubrir (relaciones, valores anteriores de campos derivados). |
| Dependencia: generador de hojas de cálculo (`maatwebsite/excel`) | RF-038 exige descarga en hoja de cálculo respetando filtros y ámbito; el pastor lleva esas cifras a reuniones de distrito. | Un CSV nativo no da un archivo presentable en una reunión de distrito, que es el propósito declarado de CE-07 y HU-12. |
| Dependencia: generador de PWA (`vite-plugin-pwa`) | El principio II exige instalación en el celular y apertura bajo 3 s en red lenta (RF-059). | Un trabajador de servicio y un manifiesto a mano son fuente conocida de fallas difíciles de reproducir, justo en el canal principal de uso. |
| Prueba de arquitectura que cuenta consultas (`QueryBudgetTest`) | La constitución fija un máximo de 5 consultas por pantalla; la unidad de medida es la petición completa (ver Constitution Check) y un límite que no se mide no se cumple. | La revisión a ojo no detecta una consulta N+1 introducida en una pantalla que ya existía. |
