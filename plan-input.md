# Plan técnico — Sistema de Comités IPUC

Documento de arquitectura. Deriva de `constitucion-input.md` (ratificada 1.0.0) y de
`spec-input.md`. Toda decisión aquí está justificada y referenciada contra el principio o el
requisito que la obliga.

---

## 1. Contexto técnico

### Stack (fijado por la constitución, no se discute)

| Pieza | Versión | Para qué |
|---|---|---|
| PHP | 8.4 | Lenguaje del servidor |
| Laravel | 12 | Framework de la aplicación |
| MySQL | 8 | Almacenamiento |
| Inertia.js | 2 | Puente entre servidor y navegador, sin API pública |
| React | 19 | Interfaz |
| Tailwind CSS | 4 | Estilos |
| Vite | — | Compilación de la interfaz |
| Pest | — | Pruebas |

Base: el **kit inicial oficial de Laravel 12 para React**, que ya trae Inertia 2, React 19 y
Tailwind 4 configurados, más las pantallas de autenticación y perfil.

### Dependencias añadidas (la constitución exige justificarlas)

| Dependencia | Por qué | Alternativa descartada |
|---|---|---|
| Bitácora de cambios (`spatie/laravel-activitylog`) | El Principio III y el RF-056 exigen conservar qué decía antes, quién lo cambió y cuándo. Escribirlo a mano son ~2 días y deja casos sin cubrir. | Tabla de auditoría propia |
| Generador de hojas de cálculo (`maatwebsite/excel`) | El RF-038 exige descarga en hoja de cálculo. El pastor lleva estas cifras a reuniones de distrito; un CSV plano no sirve para eso. | CSV nativo |
| Generador de PWA (`vite-plugin-pwa`) | El Principio II exige instalación en el celular y apertura en menos de 3 s en red lenta. El trabajador de servicio escrito a mano es fuente conocida de fallas difíciles. | Manifiesto a mano |

No se añade nada más. Cualquier dependencia futura requiere justificación escrita en este
documento.

### Entorno

- **Local:** Laragon, en `C:\laragon\www\control-actividades`.
- **Producción:** servidor del contrato (8 núcleos / 16 hilos, Xeon-D, 16 GB RAM, 2 TB),
  con un nombre de dominio.
- Zona horaria `America/Bogota`, moneda COP, idioma de interfaz español de Colombia.

---

## 2. Estructura de carpetas

Laravel estándar, sin capas adicionales. La lógica vive en los controladores; la verificación
del negocio se hace con pruebas de funcionalidad a través de rutas HTTP, que es lo que de verdad
ejercita ruta, autorización y filtro de alcance juntos.

```
app/
  Http/
    Controllers/
      Auth/                      del kit inicial, adaptado a usuario en vez de correo
      DashboardController.php
      CommitteeController.php
      ApprovalController.php
      ActivityController.php
      AnnualPlanController.php
      MemberController.php
      MemberImportController.php
      SympathizerController.php
      MeetingController.php
      BudgetController.php
      ReportController.php
      UserController.php         solo el pastor
    Middleware/
      ResolveScope.php           fija el ámbito del cargo activo en cada petición
    Requests/                    una por formulario, con sus reglas y mensajes en español
  Models/
    Concerns/
      BelongsToSite.php          filtro de sede (multi-sede desde el día 1)
      BelongsToCommitteeScope.php filtro del ámbito del cargo
    Site.php  User.php  Committee.php  CommitteeUser.php
    Member.php  Sympathizer.php  SympathizerStage.php  SympathizerVisit.php
    Activity.php  ActivityTask.php  Approval.php  AnnualPlan.php
    Meeting.php  MeetingAttendance.php
    Budget.php  BudgetMovement.php
  Policies/                      una por modelo con ámbito
  Support/
    Scope/CurrentScope.php       quién soy y qué puedo ver, resuelto una sola vez por petición
    Approvals/ApprovalFlow.php   la cadena de escalamiento, en un solo lugar
  Exports/                       una clase por reporte descargable

resources/js/
  pages/                         una carpeta por sección, nombres que coinciden con las rutas
    Dashboard/  Committees/  Approvals/  Activities/
    Members/  Sympathizers/  Meetings/  Budget/  Reports/  Users/
  components/
    ui/                          botones, campos, pastillas, tablas-tarjeta
    domain/                      StepPath, ApprovalCard, CommitteeTree, ScopeBanner
  layouts/AppLayout.tsx          barra, menú, cinta de alcance
  lib/                           formato de fechas, moneda, utilidades

database/
  migrations/
  seeders/DemoSeeder.php         los datos de la presentación
  factories/

tests/
  Feature/                       la mayoría: entran por ruta como un cargo concreto
    Scope/                       aislamiento entre comités
    Approvals/  Activities/  Members/  Sympathizers/  Budget/  Reports/
  Unit/                          cálculos puros: compromiso, avance, estadísticas
  Architecture/
    ScopeCoverageTest.php        recorre los modelos y falla si falta el rasgo
    QueryBudgetTest.php          cuenta consultas por pantalla, falla pasando de 5
```

---

## 3. Modelo de datos

Todas las tablas con información de la congregación llevan `site_id`. Todas las fechas de
auditoría (`created_at`, `updated_at`) son estándar de Laravel.

### Identidad y organización

**`sites`** — `id`, `name`, `city`, `active`
Una sola fila en operación. Existe desde el día 1 por mandato de la constitución.

**`users`** — `id`, `site_id`, `username` (único por sede), `name`, `password`,
`must_change_password` (bool), `is_pastor` (bool), `active`, `last_login_at`
Sin columna de correo obligatorio: se entra con usuario asignado (HU-01).
El pastor se identifica con `is_pastor`; no cuelga de ningún comité.

**`committees`** — `id`, `site_id`, `parent_id` (nulo = comité principal), `name`, `slug`,
`mission`, `color`, `icon`, `position`, `active`
Un sub-comité es un comité con padre. Un comité sin hijos resuelve el CL-01 sin código especial.

**`committee_user`** — `id`, `site_id`, `user_id`, `committee_id`, `role`
(`president` | `leader`), `started_at`, `ended_at` (nulo = vigente)
Una persona puede tener dos filas vigentes (CL-05). El relevo no borra: cierra la fila con
`ended_at` y abre otra (HU-02, RN-09).

### Miembros

**`members`** — `id`, `site_id`, `committee_id` (nulo = sin comité, CL-03), `name`,
`birth_date`, `gender` (`f`|`m`), `marital_status`
(`single`|`married`|`widowed`|`separated`|`common_law`), `baptized` (bool),
`baptized_at` (nulo si no), `joined_at`, `phone`, `neighborhood`, `active`

`committee_id` apunta al comité **o** al sub-comité: si el nodo tiene padre, la persona está en
un sub-comité; si no, está en el comité sin sub-comité (CL-02). Un solo campo cubre ambos casos.

> **Decisión que se aparta del spec.** El RF-031 dice «edad». Se guarda **fecha de nacimiento** y
> la edad se calcula. Guardar la edad como número la vuelve mentira al año siguiente y rompería
> los reportes (CE-07). Además el Comité de Serenatas trabaja con cumpleaños, así que el dato se
> necesita de todas formas. **Riesgo R-05:** puede que el cliente no tenga la fecha de todos.

### Simpatizantes

**`sympathizers`** — `id`, `site_id`, `committee_id` (sub-comité de Evangelismo que acompaña),
`name`, `birth_date`, `gender`, `phone`, `neighborhood`,
`arrival_source` (`invitation`|`refam`|`campaign`|`family`|`neighbor`|`media`),
`invited_by_user_id`, `invited_by_name`, `first_contact_at`, `stage` (1..5),
`assigned_to_user_id`, `converted_member_id` (nulo), `converted_at`

**`sympathizer_stages`** — `id`, `site_id`, `sympathizer_id`, `stage`, `reached_at`,
`recorded_by_user_id`
Solo se agregan filas. La etapa alcanzada conserva su fecha (RF-042).

**`sympathizer_visits`** — `id`, `site_id`, `sympathizer_id`, `visited_at`,
`type` (`visit`|`call`|`study`|`service`), `by_user_id`, `note`

El nivel de compromiso **no se guarda**: se calcula (RF-043) con la asistencia reciente y los
días desde la última visita. Guardarlo sería un dato que envejece sin que nadie lo toque.

### Cronograma

**`annual_plans`** — `id`, `site_id`, `year`, `approved_by_user_id`, `approved_at`
Un acto único de aprobación por año (HU-08, RF-025).

**`activities`** — `id`, `site_id`, `committee_id`, `annual_plan_id` (nulo = propuesta durante
el año, RF-024), `title`, `description`, `scheduled_at`, `location`,
`type` (`service`|`rehearsal`|`event`|`outing`|`campaign`|`convention`|`meeting`|`visit`),
`status` (`pending`|`approved`|`in_progress`|`completed`|`rejected`),
`progress` (0-100), `budget_amount`, `proposed_by_user_id`, `proposed_at`,
`approved_by_user_id`, `approved_at`, `rejection_reason`

**`activity_tasks`** — `id`, `site_id`, `activity_id`, `description`, `done`,
`assigned_to_name`, `done_at`
`activities.progress` se recalcula desde estas filas (RF-027); nadie lo escribe a mano.

### Aprobaciones

**`approvals`** — `id`, `site_id`, `committee_id`, `approvable_type`, `approvable_id`,
`type` (`activity`|`expense`|`task`|`date_change`|`new_member`),
`level` (`president`|`pastor`), `status` (`pending`|`approved`|`rejected`),
`title`, `detail`, `amount` (nulo), `requested_by_user_id`, `requested_at`,
`resolved_by_user_id`, `resolved_at`, `reason`, `transferred_from_user_id` (CL-06)

Una sola bandeja para los cinco tipos. La regla de escalamiento vive únicamente en
`Support/Approvals/ApprovalFlow.php`:

```
líder     → level = president
presidente→ level = pastor
presidente aprueba type = activity  → no cierra: level pasa a pastor, status sigue pending
presidente aprueba expense|task|date_change → cierra
pastor aprueba                      → cierra, y aplica el efecto sobre el objeto apuntado
```

Las filas resueltas **no se editan** (RF-057). Corregir significa registrar una decisión nueva.

### Asistencia

**`meetings`** — `id`, `site_id`, `committee_id`, `held_at`, `type` (`rehearsal`|`meeting`|
`service`), `topic`, `recorded_by_user_id`
**`meeting_attendances`** — `id`, `site_id`, `meeting_id`, `member_id`, `present` (bool)

Una fila por convocado. El porcentaje por persona se calcula sobre las reuniones a las que fue
convocada (RF-048). Los convocados solo pueden pertenecer a ese comité o sub-comité (RF-047):
regla validada en el formulario y en una prueba.

### Presupuesto

**`budgets`** — `id`, `site_id`, `committee_id`, `year`, `assigned_amount`, `assigned_by_user_id`
Aplica a comités y a sub-comités: el presidente reparte asignando filas a sus hijos (RF-050).

**`budget_movements`** — `id`, `site_id`, `committee_id`, `activity_id` (nulo), `moved_at`,
`concept`, `amount`, `type` (`expense`|`allocation`|`income`), `receipt`,
`authorized_by_user_id`

El saldo **se calcula**, no se guarda. Excederlo advierte pero no bloquea (RN-05, RF-051).

### Bitácora

**`activity_log`** — la del paquete. Registra el modelo, el cambio, los valores anterior y nuevo,
el autor y la fecha. Se activa en: `members`, `activities`, `approvals`, `budget_movements`,
`meeting_attendances`, `sympathizers`, `committee_user`.

---

## 4. El filtro de alcance — la pieza crítica

El Principio I es NO NEGOCIABLE y es lo primero que un cliente comprueba. Se construye en tres
capas, de modo que ninguna falle sola.

**Capa 1 — Contexto por petición.** El middleware `ResolveScope` calcula una sola vez quién es el
usuario, qué cargos vigentes tiene y, de ahí, qué nodos del árbol de comités puede ver:

```
pastor                 → todos los comités de la sede
presidente de comité X → X y sus hijos
líder de sub-comité Y  → solo Y
dos cargos             → unión de ambos conjuntos (CL-05)
```

**Capa 2 — Filtro automático en el modelo.** Los modelos con ámbito usan los rasgos
`BelongsToSite` y `BelongsToCommitteeScope`, que aplican un *global scope* de Eloquent. No hay
que escribir el filtro en ninguna consulta: viene puesto.

**Capa 3 — Autorización explícita.** Cada acción pasa por su policy. El filtro evita *listar* lo
ajeno; la policy evita *abrir* lo ajeno por su identificador directo (RF-008).

**Red de seguridad.** `tests/Architecture/ScopeCoverageTest` recorre todos los modelos del
proyecto y falla si alguno con `site_id` no usa los rasgos. Un modelo nuevo mal hecho rompe la
construcción el mismo día.

**Prohibición.** En este proyecto no se usa `DB::` ni SQL crudo sobre tablas con ámbito: saltaría
el filtro sin que nadie se entere. Queda escrito aquí y se verifica en revisión.

### La única excepción sancionada: el cronograma

El RF-010 obliga a que cualquier cargo vea título, descripción, fecha, hora, lugar y comité
responsable de **cualquier** actividad, mientras presupuesto y tareas siguen reservados. Esto
contradice el filtro general, de modo que se resuelve con un camino explícito y único:

- `Activity::forSharedCalendar()` — método con nombre propio que retira el filtro de comité y
  **selecciona solo las columnas compartidas**. Nunca devuelve `budget_amount` ni tareas.
- Es la **única** salida autorizada del filtro en todo el proyecto. Cualquier otra se considera
  defecto.
- Prueba obligatoria: un presidente consulta el cronograma, ve la actividad de otro comité, y la
  respuesta **no contiene** presupuesto ni tareas.

---

## 5. Contratos de las pantallas

Con Inertia no hay API pública: el servidor entrega directamente las propiedades que la pantalla
necesita. El «contrato» es, entonces, la forma de esas propiedades. Esto es deliberado: sin API
abierta hay una superficie menos que proteger.

Toda respuesta incluye, de forma compartida:

```
auth.user        { id, name, username, isPastor }
auth.roles       [ { committeeId, committeeName, role } ]
scope.label      "Toda la congregación" | "Comité de Alabanza" | "Sub-comité de Sonido"
scope.hidden     { committees, subcommittees, members, activities }
pendingCount     número para el contador del menú (RF-055)
flash            { type, message }
```

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/entrar` | GET/POST | `Auth/Login` | — |
| `/clave-nueva` | GET/POST | `Auth/ChangePassword` | obligatoria si `must_change_password` |
| `/` | GET | `Dashboard/Index` | `kpis`, `pendingApprovals[]`, `committeeProgress[]`, `upcoming[]` |
| `/comites` | GET | `Committees/Index` | `tree[]`, `cards[]` |
| `/comites/{comite}` | GET | `Committees/Show` | `committee`, `children[]`, `president`, `members[]`, `budget`, `progress` |
| `/aprobaciones` | GET | `Approvals/Index` | `inbox[]`, `escalated[]`, `history[]`, `flow` |
| `/aprobaciones` | POST | — | crear solicitud |
| `/aprobaciones/{id}/aprobar` | POST | — | resolver; aplica `ApprovalFlow` |
| `/aprobaciones/{id}/rechazar` | POST | — | exige `reason` (RF-020) |
| `/cronograma` | GET | `Activities/Index` | `summary`, `months[]`, `list[]`, `filters`, `annualPlan` |
| `/cronograma/{actividad}` | GET | `Activities/Show` | `activity`, `tasks[]`, `trail[]`, `canEdit` |
| `/cronograma` | POST | — | proponer; devuelve aviso de cruce (RF-028) |
| `/plan-anual/{año}/aprobar` | POST | — | solo pastor |
| `/miembros` | GET | `Members/Index` | `members[]`, `stats`, `filters`, `committees[]` |
| `/miembros` | POST / PUT | — | registrar y corregir |
| `/miembros/importar` | GET/POST | `Members/Import` | `preview[]`, `errors[]` (HU-18) |
| `/simpatizantes` | GET | `Sympathizers/Index` | `stats`, `funnel[]`, `list[]`, `pendingMember[]` |
| `/simpatizantes/{id}` | GET | `Sympathizers/Show` | `sympathizer`, `stages[]`, `visits[]`, `commitment` |
| `/asistencia` | GET/POST | `Meetings/Index` | `meetings[]`, `perMember[]`, `kpis` |
| `/presupuesto` | GET | `Budget/Index` | `summary`, `byCommittee[]`, `movements[]` |
| `/reportes` | GET | `Reports/Index` | `stats`, `charts`, `tables` |
| `/reportes/descargar` | GET | — | archivo .xlsx con los filtros aplicados (RF-038) |
| `/reportes/imprimir` | GET | `Reports/Print` | vista de impresión |
| `/usuarios` | GET/POST/PUT | `Users/Index` | solo pastor (HU-02) |

Las rutas van en español porque son visibles para el usuario; el código interno, en inglés
(constitución, sección de idioma).

---

## 6. Decisiones justificadas

| # | Decisión | Por qué | Qué se descartó |
|---|---|---|---|
| D-01 | Laravel estándar, lógica en controladores | Menos archivos y menos ceremonia; con 20 historias y 4 semanas, la velocidad manda. Las pruebas de funcionalidad por HTTP cubren el riesgo. | Acciones por caso de uso; modular por dominio |
| D-02 | Una tabla `committees` con `parent_id` | Un solo filtro de alcance, una sola policy, un solo conjunto de pantallas. El CL-01 sale gratis. | Dos tablas separadas |
| D-03 | Tabla `approvals` polimórfica | Una bandeja, un flujo, un sitio donde vive el escalamiento. Un sexto tipo no toca el flujo. | Una tabla por tipo |
| D-04 | Rasgos con *global scope* + prueba de cobertura | El Principio I debe ser imposible de olvidar, no solo difícil. La prueba es la garantía. | Filtrar en cada consulta; repositorios |
| D-05 | Kit inicial oficial de Laravel 12 para React | Es exactamente el stack de la constitución, ya armado. Se adapta a entrar con usuario y se retira el registro público. | Breeze; autenticación a mano |
| D-06 | `committee_user` con vigencia | Único diseño que cumple CL-05 (dos cargos) y conserva la historia del relevo (RN-09). | Campo de cargo en `users`; paquete de roles |
| D-07 | Paquete de bitácora | El Principio III exige valores anteriores. Dos días de trabajo propio con casos que se escapan. | Tabla de auditoría propia |
| D-08 | Paquete de hojas de cálculo | El pastor lleva estas cifras a reuniones de distrito; necesita un archivo presentable. | CSV nativo |
| D-09 | `birth_date` en vez de `age` | La edad guardada envejece y rompe los reportes. Además Serenatas necesita cumpleaños. | Guardar la edad |
| D-10 | Compromiso y saldo calculados, no guardados | Dato guardado = dato que envejece sin que nadie lo note. | Columnas con el valor |
| D-11 | Despliegue con script propio disparado al fusionar | Sin costo mensual, que pesa cuando el ingreso es $100.000. Respaldo diario por tarea programada. | Panel de pago; contenedores |
| D-12 | Prueba que cuenta consultas por pantalla | La constitución fija un máximo de 5; un límite que no se mide no se cumple. | Revisión a ojo |
| D-13 | PWA por complemento de compilación | El trabajador de servicio a mano es fuente conocida de fallas raras. | Manifiesto a mano |
| D-14 | Semilla con los datos de la presentación | Sirve para desarrollar, probar y capacitar; el pastor reconoce lo que ya vio. Datos ficticios, como exige la constitución. | Conjunto mínimo; solo generadores |
| D-15 | `forSharedCalendar()` como única excepción al filtro | El RF-010 obliga a abrir el cronograma. Mejor una puerta con nombre, vigilada y probada, que filtros inconsistentes. | Quitar el filtro al modelo `activities` |

---

## 7. Riesgos

| # | Riesgo | Impacto | Mitigación |
|---|---|---|---|
| R-01 | Una consulta se salta el filtro de alcance | **Crítico.** Es la promesa que la congregación aprobó. | Tres capas + `ScopeCoverageTest` + prohibición de SQL crudo + prueba de aislamiento por cada pantalla |
| R-02 | Los controladores crecen sin control, sobre todo en aprobaciones | Medio. Código difícil de cambiar en el hito 4. | El escalamiento vive fuera, en `ApprovalFlow`. Revisión del tamaño al cerrar el hito 2 |
| R-03 | El cronograma compartido abre más de lo debido | Alto. Contradiría el Principio I delante del cliente. | Un único método con columnas seleccionadas a mano + prueba que verifica que no viaja el presupuesto |
| R-04 | Cuatro semanas para 20 historias y 57 requisitos | **Alto.** Es el riesgo principal del proyecto. | Hitos del contrato; P1 completo antes que cualquier P2; lo que no quepa se negocia con el alcance, no con la calidad |
| R-05 | El cliente no tiene la fecha de nacimiento de todos los miembros | Medio. Bloquearía la carga inicial. | Campo opcional en la importación; si falta, el miembro queda marcado para completar y no aparece en reportes por edad |
| R-06 | La PWA sirve una versión vieja después de un despliegue | Medio. El cliente ve errores ya corregidos. | Versionado de los archivos compilados y aviso de «hay una versión nueva» dentro de la aplicación |
| R-07 | El servidor de producción todavía no está aprovisionado | Medio. Bloquea el hito 4. | Levantarlo en la semana 1, no en la 4, y desplegar desde el primer hito |
| R-08 | La importación de miembros no está listada en la Cláusula Segunda del contrato | Bajo en lo técnico, relevante en lo comercial. | Está marcada como supuesto S-02 en el spec. Confirmar con el cliente antes de construirla |
| R-09 | Dos dependencias de terceros que mantener | Bajo. | Versiones fijadas; no se actualizan sin decisión explícita (constitución) |
| R-10 | El multi-sede nunca se usa y se paga su complejidad | Bajo. | Es una columna y dos rasgos. La constitución ya ponderó el costo de añadirlo después |

---

## 8. Orden de construcción

Sigue los cuatro hitos del contrato. Cada hito termina **funcionando de punta a punta en el
celular**, desplegado y probado.

**Hito 1 — Cimiento.** Sede, usuarios, cargos con vigencia, árbol de comités, el filtro de
alcance con sus tres capas, las policies, `ScopeCoverageTest`, entrada al sistema con cambio de
clave, semilla de datos y despliegue funcionando.
*Cubre:* HU-01 a HU-04, HU-20. *Si esto no queda sólido, nada de lo demás se sostiene.*

**Hito 2 — Decidir.** Aprobaciones completas con su cadena, cronograma anual, propuestas, aviso
de cruce, tareas y avance, bandeja y contadores.
*Cubre:* HU-05 a HU-09, HU-17.

**Hito 3 — La gente.** Miembros con filtros, importación, reportes con sus gráficos y su vista de
tabla, descarga e impresión, simpatizantes con etapas, visitas y paso a miembro.
*Cubre:* HU-10 a HU-14, HU-18.

**Hito 4 — Cerrar.** Asistencia, presupuesto, correcciones con bitácora, PWA afinada, pruebas de
consultas por pantalla, capacitación y acta de entrega.
*Cubre:* HU-15, HU-16, HU-19.

---

## 9. Puertas de calidad

Antes de fusionar a `main`, todas deben pasar:

1. Las pruebas de Pest pasan, incluidas `ScopeCoverageTest` y `QueryBudgetTest`.
2. Las migraciones corren sobre base de datos en limpio.
3. Cada pantalla nueva tiene al menos una prueba de aislamiento: un cargo ajeno **no** la ve.
4. La pantalla se verificó a 360 px de ancho.
5. Ninguna pantalla supera 5 consultas.
6. No hay `DB::` ni SQL crudo sobre tablas con ámbito.
7. Ningún texto de interfaz contiene jerga técnica (Principio IV).
