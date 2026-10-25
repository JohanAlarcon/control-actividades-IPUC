# Tareas — Sistema de Comités IPUC

Deriva de `constitucion-input.md` (ratificada 1.0.0), `spec-input.md` y `plan-input.md`.
Cubre el proyecto completo: los cuatro hitos del contrato, 162 tareas.

---

## Cómo se lee este documento

```
- [ ] Tnnn [P] [US01] · Título de la tarea
      Archivos: ruta/exacta/del/archivo.php · otra/ruta.tsx
      Verifica: el comando o la comprobación que la cierra
```

| Marca | Significado |
|---|---|
| `Tnnn` | Identificador. No se reutiliza ni se renumera, aunque una tarea se cancele. |
| `[P]` | No depende de ninguna tarea anterior del mismo bloque: se puede adelantar o reordenar. |
| `[US01]` | Historia de usuario del spec. `[US01]` es `HU-01`, `[US20]` es `HU-20`. |
| `Prueba:` | Tarea de prueba. Va inmediatamente después de lo que verifica (Principio V). |
| `<ts>` | Marca de tiempo que genera Laravel al crear la migración. |

**Criterio de terminado.** Una tarea se marca hecha cuando su comando de verificación pasa.
Las tareas de pantalla exigen además la revisión a 360 px de ancho sin desplazamiento
horizontal (Principio II, puerta de calidad 4). No hay tarea terminada «a falta de la prueba».

**Ejecución.** Una tarea a la vez, en el orden del documento. La marca `[P]` es información,
no instrucción: sirve para reordenar cuando algo se atasca.

---

## Política de alcance

El alcance de este documento **no se recorta**. Si el calendario no alcanza, se mueve la fecha
de entrega, nunca el alcance y nunca la calidad.

Esto **enmienda la mitigación del riesgo R-04** del plan técnico, que decía negociar con el
alcance. Consecuencia que conviene tener presente desde la semana 1: la Cláusula Tercera del
contrato fija cuatro semanas y el 50 % final está atado a la entrega, de modo que mover la fecha
exige un acuerdo escrito con el pastor antes del vencimiento, no después. El indicador temprano
es el cierre del Hito 1: si la semana 1 termina sin el filtro de alcance desplegado y probado,
la conversación va en la semana 2, no en la 4.

---

## Mapa de fases

| Fase | Hito del contrato | Tareas | Historias |
|---|---|---|---|
| 0 | Arranque e infraestructura | T001–T012 | — |
| 1 | Hito 1 · Cimiento | T013–T052 | US01, US02, US03, US04, US20 |
| 2 | Hito 2 · Decidir | T053–T089 | US05, US06, US07, US08, US09, US17 |
| 3 | Hito 3 · La gente | T090–T125 | US10, US11, US12, US13, US14, US18 |
| 4 | Hito 4 · Cerrar | T126–T153 | US15, US16, US19 |
| 5 | Entrega | T154–T162 | — |

**Camino crítico.** T013 → T016 → T019 → T022 → T024 → (todo lo demás).
El filtro de alcance bloquea el proyecto entero: ninguna pantalla con datos de comité se
construye antes de T024. Es deliberado — el Principio I no se agrega después.

---

# FASE 0 · Arranque e infraestructura

Días 1 y 2. El servidor se levanta ahora, no en la semana 4 (mitiga R-07).

- [ ] T001 · Crear el proyecto desde el kit inicial oficial de Laravel 12 para React
      Archivos: todo el árbol en `C:/laragon/www/control-actividades/`
      Verifica: `php artisan --version` imprime 12.x · `npm run build` termina sin error · `http://control-actividades.test` carga la pantalla de bienvenida.

- [ ] T002 · Configurar el entorno colombiano
      Archivos: `config/app.php` · `.env` · `.env.example`
      Verifica: `php artisan tinker --execute="echo now()->format('d/m/Y H:i');"` imprime la hora de Bogotá · `config('app.locale')` es `es`.

- [ ] T003 [P] · Instalar y fijar las tres dependencias justificadas del plan
      Archivos: `composer.json` · `package.json`
      Verifica: `composer show spatie/laravel-activitylog maatwebsite/excel` muestra versiones exactas, sin `^` ni `~` · `vite-plugin-pwa` fijado en `package.json`.

- [ ] T004 [P] · Escribir la guía de ejecución del proyecto
      Archivos: `CLAUDE.md`
      Verifica: contiene los cinco principios en una línea cada uno, las siete puertas de calidad, la prohibición de `DB::` y SQL crudo sobre tablas con ámbito, y la regla de idioma (interfaz en español, código en inglés).

- [ ] T005 [P] · Configurar Pest y la base de datos de pruebas
      Archivos: `tests/Pest.php` · `phpunit.xml` · `.env.testing`
      Verifica: `php artisan test` pasa en verde con la suite del kit inicial.

- [ ] T006 [P] · Repositorio, rama de trabajo y convención de commits
      Archivos: `.gitignore` · `.gitmessage` · `docs/convenciones.md`
      Verifica: `git log --oneline` muestra el primer commit en imperativo español · `main` exige revisión para fusionar.

- [ ] T007 · Aprovisionar el servidor de producción
      Archivos: `docs/despliegue.md`
      Verifica: `ssh` entra · `php -v` imprime 8.4 · `mysql --version` imprime 8 · el dominio responde por HTTPS con certificado válido.

- [ ] T008 · Script de despliegue propio
      Archivos: `deploy.sh`
      Verifica: corrido a mano en el servidor deja la aplicación respondiendo 200 · `php artisan migrate --force` corre dentro del script.

- [ ] T009 · Despliegue automático al fusionar a `main`
      Archivos: `.github/workflows/deploy.yml`
      Verifica: un commit en `main` dispara la acción y la URL de producción sirve el cambio en menos de 3 minutos.

- [ ] T010 · Respaldo diario de la base de datos
      Archivos: `app/Console/Commands/BackupDatabase.php` · `routes/console.php`
      Verifica: `php artisan db:respaldo` deja el archivo con la fecha en el nombre · `crontab -l` muestra la entrada diaria.

- [ ] T011 [P] · Prueba: el respaldo genera archivo y no escribe datos de personas en el registro
      Archivos: `tests/Feature/BackupCommandTest.php`
      Verifica: `php artisan test --filter=BackupCommandTest`

- [ ] T012 · Puerta de calidad automatizada en cada envío
      Archivos: `.github/workflows/pruebas.yml`
      Verifica: corre `php artisan test` y `npm run build` en cada push y en cada solicitud de fusión · un push con una prueba roja pone la acción en rojo.

---

# FASE 1 · Hito 1 — Cimiento

Semana 1. Cubre US01 a US04 y US20. **Si esto no queda sólido, nada de lo demás se sostiene.**

## 1.1 Esquema de identidad y organización

Bloquea todo lo demás. Ninguna tarea de este bloque va en paralelo.

- [ ] T013 · Migración, modelo y semilla de la sede
      Archivos: `database/migrations/<ts>_create_sites_table.php` · `app/Models/Site.php` · `database/seeders/SiteSeeder.php`
      Verifica: `php artisan migrate:fresh --seed` deja exactamente una fila en `sites`.

- [ ] T014 [US01] · Migración y modelo `users` adaptados a usuario sin correo
      Archivos: `database/migrations/<ts>_update_users_table.php` · `app/Models/User.php` · `database/factories/UserFactory.php`
      Añade `site_id`, `username` único por sede, `must_change_password`, `is_pastor`, `active`, `last_login_at`; retira la obligatoriedad de `email`.
      Verifica: `php artisan migrate:fresh` corre limpio · `php artisan test --filter=UserFactoryTest`

- [ ] T015 [US03] · Migración y modelo `committees` con `parent_id`
      Archivos: `database/migrations/<ts>_create_committees_table.php` · `app/Models/Committee.php` · `database/factories/CommitteeFactory.php`
      Relaciones `parent()` y `children()`; métodos `esPrincipal()` y `tieneSubcomites()`.
      Verifica: `php artisan test --filter=CommitteeHierarchyTest` — un comité con dos hijos, un comité sin hijos, y un hijo que conoce a su padre.

- [ ] T016 [US02] · Migración y modelo `committee_user` con vigencia
      Archivos: `database/migrations/<ts>_create_committee_user_table.php` · `app/Models/CommitteeUser.php` · `database/factories/CommitteeUserFactory.php`
      `user_id`, `committee_id`, `role` (`president`|`leader`), `started_at`, `ended_at`.
      Verifica: `php artisan test --filter=CommitteeUserTest`

- [ ] T017 [US02] · Prueba: dos cargos vigentes en una persona, y un relevo que cierra sin borrar
      Archivos: `tests/Feature/Committees/CommitteeUserAssignmentTest.php`
      Cubre CL-05 y RN-09.
      Verifica: `php artisan test --filter=CommitteeUserAssignmentTest`

## 1.2 El filtro de alcance — la pieza crítica

Principio I, NO NEGOCIABLE. Tres capas, de modo que ninguna falle sola.

- [ ] T018 · Rasgo `BelongsToSite` con filtro automático de sede
      Archivos: `app/Models/Concerns/BelongsToSite.php`
      Verifica: `php artisan test --filter=SiteScopeTest` — un modelo de la sede 2 no aparece consultando desde la sede 1.

- [ ] T019 [US04] · `CurrentScope`: quién soy y qué nodos del árbol puedo ver
      Archivos: `app/Support/Scope/CurrentScope.php`
      pastor → todos los comités de la sede · presidente de X → X y sus hijos · líder de Y → solo Y · dos cargos → la unión.
      Verifica: `php artisan test --filter=CurrentScopeTest`

- [ ] T020 [US04] · Prueba: los cuatro casos de resolución de ámbito
      Archivos: `tests/Unit/Scope/CurrentScopeTest.php`
      Verifica: `php artisan test --filter=CurrentScopeTest` — cuatro casos en verde, incluido el de dos cargos (CL-05).

- [ ] T021 [US04] · Middleware `ResolveScope` registrado en el grupo web
      Archivos: `app/Http/Middleware/ResolveScope.php` · `bootstrap/app.php`
      Resuelve el ámbito una sola vez por petición.
      Verifica: `php artisan test --filter=ResolveScopeTest` · el contador de consultas del ámbito es 1 por petición, no una por consulta.

- [ ] T022 [US04] · Rasgo `BelongsToCommitteeScope` con filtro automático de comité
      Archivos: `app/Models/Concerns/BelongsToCommitteeScope.php`
      Verifica: `php artisan test --filter=CommitteeScopeTest`

- [ ] T023 [US04] · Prueba de aislamiento: tres cargos, tres conjuntos, cero filtros escritos a mano
      Archivos: `tests/Feature/Scope/CommitteeIsolationTest.php`
      Verifica: `php artisan test --filter=CommitteeIsolationTest` — ninguna consulta de la prueba escribe `where committee_id`.

- [ ] T024 [US04] · `ScopeCoverageTest`: recorre los modelos y falla si falta el rasgo
      Archivos: `tests/Architecture/ScopeCoverageTest.php`
      Verifica: `php artisan test --filter=ScopeCoverageTest` en verde · se crea a propósito un modelo con `site_id` sin el rasgo y la prueba se pone roja. **Esta comprobación negativa es obligatoria: una red que no se probó no es una red.**

- [ ] T025 · `QueryBudgetTest`: cuenta consultas por pantalla y falla pasando de cinco
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: `php artisan test --filter=QueryBudgetTest` · una pantalla con una consulta N+1 introducida a propósito pone la prueba roja.

- [ ] T026 [US04] · Respuesta estándar de «fuera de su alcance», sin revelar un solo dato
      Archivos: `app/Exceptions/OutOfScopeException.php` · `bootstrap/app.php` · `resources/js/pages/Errors/OutOfScope.tsx`
      El texto explica la situación en una frase y dice a quién solicitar la información (RF-008).
      Verifica: `php artisan test --filter=OutOfScopeResponseTest` — la respuesta no contiene nombres, montos ni nombres de comité ajenos · revisada a 360 px.

- [ ] T027 [US04] · Prueba: abrir por identificador directo lo ajeno responde el mensaje y no filtra nada
      Archivos: `tests/Feature/Scope/DirectAccessTest.php`
      Verifica: `php artisan test --filter=DirectAccessTest` — comité, miembro, actividad, aprobación, reunión y presupuesto ajenos, los seis.

- [ ] T028 [US04] · Propiedades compartidas en toda respuesta: ámbito, lo que queda fuera y el contador
      Archivos: `app/Http/Middleware/HandleInertiaRequests.php`
      `auth.user`, `auth.roles`, `scope.label`, `scope.hidden`, `pendingCount`, `flash` (RF-009, RF-055).
      Verifica: `php artisan test --filter=SharedPropsTest` — las seis llaves viajan en toda respuesta autenticada y `scope.hidden` trae números, no listas.

## 1.3 US01 · Entrar al sistema por primera vez

- [ ] T029 [US01] · Entrada con usuario asignado en vez de correo
      Archivos: `app/Http/Controllers/Auth/AuthenticatedSessionController.php` · `app/Http/Requests/Auth/LoginRequest.php` · `resources/js/pages/auth/Login.tsx` · `routes/auth.php` (ruta `/entrar`)
      Verifica: `php artisan test --filter=LoginTest` · revisada a 360 px, los campos y el botón por encima del teclado.

- [ ] T030 [P] [US01] · Retirar el registro público y la recuperación por correo
      Archivos: `routes/auth.php` · eliminar `resources/js/pages/auth/Register.tsx`, `ForgotPassword.tsx`, `ResetPassword.tsx`, `VerifyEmail.tsx`
      Verifica: `php artisan route:list | grep -iE "regist|password.request"` no devuelve nada · `curl -I .../register` responde 404.

- [ ] T031 [US01] · Cambio obligatorio de clave en el primer ingreso
      Archivos: `app/Http/Middleware/EnsurePasswordChanged.php` · `app/Http/Controllers/Auth/ChangePasswordController.php` · `resources/js/pages/auth/ChangePassword.tsx` · ruta `/clave-nueva`
      Verifica: `php artisan test --filter=FirstLoginTest` — con `must_change_password` en verdadero, cualquier otra ruta redirige · revisada a 360 px.

- [ ] T032 [US01] · Bloqueo de quince minutos tras cinco intentos
      Archivos: `app/Http/Requests/Auth/LoginRequest.php` · `lang/es/auth.php`
      El mensaje indica pedirle al pastor el restablecimiento (RF-004).
      Verifica: `php artisan test --filter=LoginLockoutTest` — el sexto intento responde con el bloqueo y el texto nombra al pastor.

- [ ] T033 [US01] · Prueba: los cuatro escenarios de HU-01
      Archivos: `tests/Feature/Auth/LoginTest.php` · `tests/Feature/Auth/FirstLoginTest.php` · `tests/Feature/Auth/LoginLockoutTest.php`
      Verifica: `php artisan test tests/Feature/Auth` — primer ingreso, ingreso posterior, bloqueo y clave restablecida.

## 1.4 US02 · Administrar las cuentas y los cargos

- [ ] T034 [US02] · Policy del pastor y compuerta `es-pastor`
      Archivos: `app/Policies/UserPolicy.php` · `app/Providers/AppServiceProvider.php`
      Verifica: `php artisan test --filter=UserPolicyTest` — un presidente no crea, no modifica y no retira cuentas.

- [ ] T035 [US02] · Pantalla y controlador de usuarios: crear con clave temporal, modificar, retirar
      Archivos: `app/Http/Controllers/UserController.php` · `app/Http/Requests/StoreUserRequest.php`, `UpdateUserRequest.php` · `resources/js/pages/Users/Index.tsx`, `Users/Form.tsx` · ruta `/usuarios`
      Verifica: `php artisan test --filter=UserManagementTest` · revisada a 360 px en tarjetas.

- [ ] T036 [US02] · Asignación de cargos con vigencia y relevo
      Archivos: `app/Http/Controllers/CommitteeUserController.php` · `resources/js/components/domain/RoleAssigner.tsx`
      El relevo cierra `ended_at` y abre una fila nueva; nunca borra (RN-09).
      Verifica: `php artisan test --filter=RoleHandoverTest` — tras el relevo quedan dos filas y la historia conserva el nombre anterior.

- [ ] T037 [P] [US02] · Restablecer clave, volviendo a exigir el cambio
      Archivos: `app/Http/Controllers/UserController.php` · `resources/js/pages/Users/Index.tsx`
      Verifica: `php artisan test --filter=PasswordResetByPastorTest` — solo el pastor; la persona vuelve a `/clave-nueva` al entrar (RF-005).

- [ ] T038 [US02] · Retiro inmediato del acceso, conservando el nombre en la historia
      Archivos: `app/Models/User.php` · `app/Http/Middleware/EnsureActiveUser.php`
      Verifica: `php artisan test --filter=RevokeAccessTest` — la sesión vigente se corta en la petición siguiente y el nombre sigue apareciendo en lo que aprobó (RF-006).

- [ ] T039 [US02] · Prueba: los cuatro escenarios de HU-02
      Archivos: `tests/Feature/Users/UserManagementTest.php` · `tests/Feature/Users/RoleHandoverTest.php` · `tests/Feature/Users/RevokeAccessTest.php`
      Verifica: `php artisan test tests/Feature/Users` — incluye asignar dos cargos a la misma persona y ver ambos ámbitos separados.

## 1.5 US03 · Ver la estructura de comités

- [ ] T040 [US03] · Policy de comités
      Archivos: `app/Policies/CommitteePolicy.php`
      Verifica: `php artisan test --filter=CommitteePolicyTest` — `view` cierto para el propio y sus hijos, falso para el ajeno.

- [ ] T041 [US03] · Árbol de comités
      Archivos: `app/Http/Controllers/CommitteeController.php` · `resources/js/pages/Committees/Index.tsx` · `resources/js/components/domain/CommitteeTree.tsx` · ruta `/comites`
      Los ajenos aparecen como no disponibles, sin un solo dato suyo.
      Verifica: `php artisan test --filter=CommitteeIndexTest` · a 360 px el árbol se recorre sin desplazamiento horizontal.

- [ ] T042 [US03] · Ficha de comité con hijos, responsable y miembros
      Archivos: `app/Http/Controllers/CommitteeController.php` · `resources/js/pages/Committees/Show.tsx` · ruta `/comites/{comite}`
      Si no tiene sub-comités lo dice expresamente y aclara que el presidente coordina directamente (CL-01, RF-012). No muestra una sección vacía.
      Verifica: `php artisan test --filter=CommitteeShowTest` · a 360 px.

- [ ] T043 [US03] · Prueba: los cuatro escenarios de HU-03 por los tres cargos, más CL-01
      Archivos: `tests/Feature/Committees/CommitteeVisibilityTest.php`
      Verifica: `php artisan test --filter=CommitteeVisibilityTest` — el líder ve su sub-comité y el nombre de su presidente, y nada más.

## 1.6 US20 · Primero el celular

Principio II. Estas cuatro tareas fijan el molde de todas las pantallas que vienen después.

- [ ] T044 [US20] · Diseño base: barra, menú con contador, cinta de alcance
      Archivos: `resources/js/layouts/AppLayout.tsx` · `resources/js/components/ui/` · `resources/css/app.css`
      Mínimos del Principio II: zona pulsable de 44x44 px, texto de contenido de 16 px, cero desplazamiento horizontal.
      Verifica: a 360 px sin barra horizontal en ninguna de las pantallas existentes · `npm run build` sin advertencias.

- [ ] T045 [P] [US20] · Componente tabla-tarjeta
      Archivos: `resources/js/components/ui/CardTable.tsx`
      Verifica: a 360 px una tabla de seis columnas se presenta en tarjetas legibles; a 1280 px como tabla. Ninguna tabla se corta.

- [ ] T046 [P] [US20] · Aplicación instalable en el celular
      Archivos: `vite.config.ts` · `public/manifest.webmanifest` · `public/iconos/`
      Verifica: Chrome en el celular ofrece «Instalar» · Lighthouse marca la aplicación como instalable.

- [ ] T047 [US20] · Aviso de versión nueva dentro de la aplicación
      Archivos: `resources/js/lib/actualizacion.ts` · `resources/js/layouts/AppLayout.tsx`
      Mitiga R-06: la PWA sirviendo una versión vieja después de un despliegue.
      Verifica: tras un despliegue, una pestaña ya abierta muestra «hay una versión nueva» y recarga al confirmar.

- [ ] T048 [US20] · Medición de apertura en red lenta
      Archivos: `docs/pruebas-rendimiento.md`
      Verifica: Lighthouse móvil con red 3G simulada, primera pintura con contenido por debajo de 3 segundos. El resultado queda anotado con fecha en el documento.

## 1.7 Semilla y cierre del hito 1

- [ ] T049 · Semilla de la organización: 6 comités, 10 sub-comités, 17 cargos
      Archivos: `database/seeders/CommitteeSeeder.php` · `database/seeders/UserSeeder.php` · `database/seeders/DatabaseSeeder.php`
      Los datos de la presentación que el pastor ya vio (D-14). Ficticios, como exige la constitución.
      Verifica: `php artisan migrate:fresh --seed` deja 16 nodos y 17 usuarios · entrar con cada uno de los tres cargos muestra su ámbito y nada más.

- [ ] T050 · Prueba de humo del hito 1
      Archivos: `tests/Feature/Scope/MilestoneOneSmokeTest.php`
      Verifica: `php artisan test --filter=MilestoneOneSmokeTest` — los tres cargos entran, cambian clave, ven lo suyo, y el ajeno les responde el mensaje de alcance.

- [ ] T051 · Presupuesto de consultas de las pantallas del hito 1
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: inicio, comités y ficha de comité por debajo de cinco consultas cada una.

- [ ] T052 · Puerta del hito 1
      Archivos: `docs/hitos/hito-1.md`
      Verifica: las siete puertas de calidad del plan pasan · la URL de producción sirve el hito · revisión en un celular real anotada en el documento.

---

# FASE 2 · Hito 2 — Decidir

Semana 2. Cubre US05 a US09 y US17.

## 2.1 Esquema del cronograma y las aprobaciones

- [ ] T053 [US08] · Migración y modelo `annual_plans`
      Archivos: `database/migrations/<ts>_create_annual_plans_table.php` · `app/Models/AnnualPlan.php` · `database/factories/AnnualPlanFactory.php`
      Verifica: `php artisan test --filter=AnnualPlanTest` — un plan por año y por sede, no dos.

- [ ] T054 [US09] · Migración y modelo `activities`
      Archivos: `database/migrations/<ts>_create_activities_table.php` · `app/Models/Activity.php` · `database/factories/ActivityFactory.php`
      Con `BelongsToSite` y `BelongsToCommitteeScope`. Cinco estados: esperando aprobación, aprobada, en curso, cumplida, no aprobada (RF-026).
      Verifica: `php artisan test --filter=ScopeCoverageTest` sigue en verde con el modelo nuevo · `php artisan test --filter=ActivityModelTest`

- [ ] T055 [US09] · Migración, modelo y recálculo de `activity_tasks`
      Archivos: `database/migrations/<ts>_create_activity_tasks_table.php` · `app/Models/ActivityTask.php` · `app/Models/Activity.php`
      `activities.progress` se recalcula desde las tareas cumplidas; nadie lo escribe a mano (RF-027).
      Verifica: `php artisan test --filter=ActivityProgressTest`

- [ ] T056 [US09] · Prueba: el avance con cero tareas, la mitad, todas, y el redondeo
      Archivos: `tests/Unit/Activities/ProgressCalculationTest.php`
      Verifica: `php artisan test --filter=ProgressCalculationTest` — cero tareas da 0 y no divide por cero.

- [ ] T057 [US06] · Migración y modelo `approvals` polimórfica
      Archivos: `database/migrations/<ts>_create_approvals_table.php` · `app/Models/Approval.php` · `database/factories/ApprovalFactory.php`
      Una sola bandeja para los cinco tipos: actividad, gasto, tarea, cambio de fecha, paso a miembro (RF-015).
      Verifica: `php artisan test --filter=ApprovalModelTest`

- [ ] T058 [US06] · `ApprovalFlow`: la cadena de escalamiento en un solo lugar
      Archivos: `app/Support/Approvals/ApprovalFlow.php`
      Las cinco reglas del plan, tal cual (RF-016 a RF-018, RN-02, RN-03, RN-04).
      Verifica: `php artisan test --filter=ApprovalFlowTest`

- [ ] T059 [US06] · Prueba: las cinco reglas de escalamiento, incluida la que no cierra
      Archivos: `tests/Unit/Approvals/ApprovalFlowTest.php`
      Verifica: `php artisan test --filter=ApprovalFlowTest` — el presidente aprueba una actividad y la solicitud sigue pendiente, ahora en el pastor. Un gasto de cualquier monto lo cierra el presidente (RN-04).

- [ ] T060 [US19] · Inmutabilidad de lo resuelto
      Archivos: `app/Models/Approval.php`
      El evento `updating` rechaza el cambio si la solicitud ya está resuelta (RF-021, RF-057, RN-07).
      Verifica: `php artisan test --filter=ApprovalImmutabilityTest` — editar una aprobación resuelta lanza excepción, por el modelo, no por la pantalla.

- [ ] T061 [US02] · Traspaso de pendientes al reemplazo
      Archivos: `app/Support/Approvals/TransferPending.php` · `app/Http/Controllers/CommitteeUserController.php`
      *Se construye en esta fase porque depende de la tabla `approvals`* (RF-023, CL-06).
      Verifica: `php artisan test --filter=PendingTransferTest` — al relevar, los pendientes quedan a nombre del nuevo responsable y `transferred_from_user_id` guarda de quién venían, con la fecha.

## 2.2 US05 · Proponer una actividad nueva

- [ ] T062 [US05] · Policy de actividades
      Archivos: `app/Policies/ActivityPolicy.php`
      Verifica: `php artisan test --filter=ActivityPolicyTest` — `view` cierto para cualquier cargo (RF-010), `update` solo para el comité dueño.

- [ ] T063 [US05] · Validación del formulario de propuesta
      Archivos: `app/Http/Requests/StoreActivityRequest.php` · `lang/es/validation.php`
      Título obligatorio; fecha dentro del año en curso y no pasada (CL-07); mensajes que señalan exactamente qué campo corregir.
      Verifica: `php artisan test --filter=ActivityValidationTest` — título vacío y fecha de 2025 se rechazan con el campo nombrado.

- [ ] T064 [US05] · Detector de cruce de fecha, hora y lugar
      Archivos: `app/Support/Activities/ScheduleConflict.php`
      Usa `Activity::forSharedCalendar()`; devuelve qué actividad es y de qué comité (RF-028, CL-08, CE-04).
      Verifica: `php artisan test --filter=ScheduleConflictTest` — advierte el cruce con un comité ajeno sin revelar su presupuesto.

- [ ] T065 [US05] · Formulario de propuesta y aviso de a quién le llegó
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Form.tsx` · ruta POST `/cronograma`
      El aviso nombra a la persona: «Su propuesta le llegó al hermano…» (RF-016, RF-017).
      Verifica: `php artisan test --filter=ProposeActivityTest` · a 360 px el formulario se llena con una mano.

- [ ] T066 [US05] · Prueba: los cinco escenarios de HU-05, con líder y con presidente
      Archivos: `tests/Feature/Activities/ProposeActivityTest.php` · `tests/Feature/Activities/ScheduleConflictTest.php`
      Verifica: `php artisan test --filter="ProposeActivityTest|ScheduleConflictTest"`

## 2.3 US06 y US07 · Revisar y aprobar

- [ ] T067 [US06] · Policy de aprobaciones por nivel
      Archivos: `app/Policies/ApprovalPolicy.php`
      Verifica: `php artisan test --filter=ApprovalPolicyTest` — un presidente no resuelve lo de nivel pastor ni lo de otro comité.

- [ ] T068 [US06] · Bandeja de aprobaciones: por resolver, escaladas e historia
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/pages/Approvals/Index.tsx` · `resources/js/components/domain/ApprovalCard.tsx` · ruta `/aprobaciones`
      Cada tarjeta dice de qué comité viene, quién la pidió, por quién ya pasó, qué monto implica y qué efecto tiene en el cronograma.
      Verifica: `php artisan test --filter=ApprovalInboxTest` · a 360 px la tarjeta se lee completa sin abrir nada.

- [ ] T069 [US06] · Aprobar: aplica `ApprovalFlow` y el efecto sobre el objeto apuntado
      Archivos: `app/Http/Controllers/ApprovalController.php` · ruta POST `/aprobaciones/{id}/aprobar`
      Verifica: `php artisan test --filter=ApproveTest` — aprobar una actividad la deja aprobada en el cronograma con nombre y fecha.

- [ ] T070 [US06] · Rechazar con motivo obligatorio y visible
      Archivos: `app/Http/Requests/RejectApprovalRequest.php` · `app/Http/Controllers/ApprovalController.php` · ruta POST `/aprobaciones/{id}/rechazar`
      Verifica: `php artisan test --filter=RejectTest` — sin motivo no pasa (RF-020); el motivo le aparece a quien propuso.

- [ ] T071 [US06] · Prueba: los cuatro escenarios de HU-06
      Archivos: `tests/Feature/Approvals/PresidentReviewTest.php`
      Verifica: `php artisan test --filter=PresidentReviewTest` — incluye la aprobación que no cierra y escala al pastor.

- [ ] T072 [US07] · Resolución en lote del mismo tipo, con una sola confirmación
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/pages/Approvals/Index.tsx`
      Verifica: `php artisan test --filter=BulkApprovalTest` — tres gastos se resuelven en una acción y quedan tres registros separados en la historia (RF-022).

- [ ] T073 [US07] · Prueba: los cuatro escenarios de HU-07 desde el pastor
      Archivos: `tests/Feature/Approvals/PastorInboxTest.php`
      Verifica: `php artisan test --filter=PastorInboxTest` — la bandeja del pastor trae comité, solicitante, paso anterior, monto y efecto.

## 2.4 US08 · Aprobar el plan del año

- [ ] T074 [US08] · Aprobación del plan anual en un solo acto
      Archivos: `app/Http/Controllers/AnnualPlanController.php` · ruta POST `/plan-anual/{año}/aprobar`
      Deja `approved_by_user_id` y `approved_at`, y marca aprobadas todas sus actividades (RF-025, CE-02).
      Verifica: `php artisan test --filter=AnnualPlanApprovalTest` — 38 actividades quedan aprobadas con un solo acto y una sola fila de aprobación.

- [ ] T075 [US08] · Distinguir el plan original de la propuesta posterior
      Archivos: `app/Models/Activity.php` · `resources/js/components/domain/ActivityBadge.tsx`
      Verifica: `php artisan test --filter=AnnualPlanApprovalTest` · la distinción es visible a 360 px sin pasar el dedo por encima (RF-024, RN-03).

- [ ] T076 [US08] · Cambio de año calendario
      Archivos: `app/Http/Controllers/AnnualPlanController.php` · `resources/js/pages/Activities/Index.tsx`
      El plan del año anterior queda consultable; el nuevo empieza sin aprobar (CL-13).
      Verifica: `php artisan test --filter=YearRolloverTest`

- [ ] T077 [US08] · Prueba: los tres escenarios de HU-08 más CL-13
      Archivos: `tests/Feature/Activities/AnnualPlanApprovalTest.php` · `tests/Feature/Activities/YearRolloverTest.php`
      Verifica: `php artisan test --filter="AnnualPlanApprovalTest|YearRolloverTest"`

## 2.5 US09 · Consultar el cronograma — la única excepción sancionada

El RF-010 abre el cronograma a todos los cargos. Es la única salida autorizada del filtro de
alcance en todo el proyecto (D-15). Se construye antes de la pantalla, no después.

- [ ] T078 [US09] · `Activity::forSharedCalendar()`
      Archivos: `app/Models/Activity.php`
      Retira el filtro de comité y **selecciona a mano** las columnas compartidas: título, descripción, fecha, hora, lugar y comité responsable. Nunca `budget_amount`, nunca tareas.
      Verifica: `php artisan test --filter=SharedCalendarTest`

- [ ] T079 [US09] · Prueba obligatoria de la excepción
      Archivos: `tests/Feature/Scope/SharedCalendarTest.php`
      Mitiga R-03. Un presidente consulta el cronograma, ve la actividad de otro comité, y la respuesta **no contiene** presupuesto ni tareas.
      Verifica: `php artisan test --filter=SharedCalendarTest` — con `assertJsonMissing` sobre `budget_amount` y `tasks`, en la lista y en la ficha.

- [ ] T080 [US09] · Pantalla del cronograma: doce meses, lista, resumen y barra de cumplimiento
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Index.tsx` · `resources/js/components/domain/MonthGrid.tsx` · ruta `/cronograma`
      Distingue cumplidas, en camino, aprobadas sin empezar y esperando aprobación.
      Verifica: `php artisan test --filter=ScheduleIndexTest` · a 360 px los doce meses se recorren en vertical, sin barra horizontal.

- [ ] T081 [US09] · Filtros de comité, sub-comité, mes y estado, con recálculo de los totales
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Index.tsx`
      Los filtros viajan en la dirección, de modo que una pantalla filtrada se puede compartir (RF-029, RF-030).
      Verifica: `php artisan test --filter=ScheduleFilterTest` — al filtrar por un comité, el resumen y el porcentaje de cumplimiento cambian con él.

- [ ] T082 [US09] · Ficha de actividad: tareas, rastro de decisiones y marcar cumplida
      Archivos: `app/Http/Controllers/ActivityController.php` · `app/Http/Controllers/ActivityTaskController.php` · `resources/js/pages/Activities/Show.tsx` · ruta `/cronograma/{actividad}`
      La ajena muestra solo lo compartido; `canEdit` lo resuelve el servidor, nunca la pantalla.
      Verifica: `php artisan test --filter=ActivityShowTest` — marcar una tarea recalcula el avance sin que nadie lo escriba · a 360 px.

- [ ] T083 [US09] · Prueba: los cuatro escenarios de HU-09
      Archivos: `tests/Feature/Activities/ScheduleIndexTest.php` · `tests/Feature/Activities/ActivityShowTest.php` · `tests/Feature/Activities/ScheduleFilterTest.php`
      Verifica: `php artisan test tests/Feature/Activities`

## 2.6 US17 · Saber qué tengo pendiente

- [ ] T084 [US17] · Pantalla de inicio: primero lo que espera su decisión, y desde cuándo
      Archivos: `app/Http/Controllers/DashboardController.php` · `resources/js/pages/Dashboard/Index.tsx` · ruta `/`
      `kpis`, `pendingApprovals[]`, `committeeProgress[]`, `upcoming[]` (RF-054, S-01).
      Verifica: `php artisan test --filter=DashboardTest` · a 360 px lo pendiente queda por encima del pliegue, antes de cualquier otra cosa.

- [ ] T085 [US17] · Contador del menú y estado de lo que yo envié
      Archivos: `app/Http/Middleware/HandleInertiaRequests.php` · `resources/js/layouts/AppLayout.tsx` · `resources/js/pages/Dashboard/Index.tsx`
      El líder ve en qué va su solicitud, quién debe resolverla y, si ya se resolvió, el resultado con su motivo (RF-055).
      Verifica: `php artisan test --filter=PendingCountTest` — el contador es del cargo activo, no de la congregación.

- [ ] T086 [US17] · Prueba: los tres escenarios de HU-17 por los tres cargos
      Archivos: `tests/Feature/Dashboard/DashboardTest.php`
      Verifica: `php artisan test --filter=DashboardTest`

## 2.7 Cierre del hito 2

- [ ] T087 · Semilla del cronograma: 38 actividades, plan del año y solicitudes de ejemplo
      Archivos: `database/seeders/ActivitySeeder.php` · `database/seeders/ApprovalSeeder.php`
      Verifica: `php artisan migrate:fresh --seed` · el cronograma muestra los doce meses con datos y la bandeja de cada cargo trae solicitudes.

- [ ] T088 · Presupuesto de consultas de las pantallas del hito 2
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: cronograma, ficha de actividad, bandeja e inicio por debajo de cinco consultas cada una.

- [ ] T089 · Puerta del hito 2
      Archivos: `docs/hitos/hito-2.md`
      Verifica: las siete puertas pasan · producción sirve el hito · revisión en celular real anotada.

---

# FASE 3 · Hito 3 — La gente

Semana 3. Cubre US10 a US14 y US18.

## 3.1 US10 · Registrar y consultar miembros

- [ ] T090 [US10] · Migración y modelo `members` con fecha de nacimiento
      Archivos: `database/migrations/<ts>_create_members_table.php` · `app/Models/Member.php` · `database/factories/MemberFactory.php`
      D-09: se guarda `birth_date` y la edad se calcula. `committee_id` nulo significa sin comité (CL-03); si el nodo tiene padre, la persona está en un sub-comité (CL-02, RF-014).
      Verifica: `php artisan test --filter=MemberModelTest` · `ScopeCoverageTest` sigue en verde.

- [ ] T091 [US10] · Prueba: la edad se calcula de la fecha y no se guarda
      Archivos: `tests/Unit/Members/AgeCalculationTest.php`
      Verifica: `php artisan test --filter=AgeCalculationTest` — un miembro nacido ayer hace 18 años tiene 18; el que los cumple mañana tiene 17.

- [ ] T092 [US10] · Policy y validaciones del registro de miembros
      Archivos: `app/Policies/MemberPolicy.php` · `app/Http/Requests/StoreMemberRequest.php`, `UpdateMemberRequest.php`
      RF-032: estado civil imposible en menores de dieciocho. RF-033: fecha de bautismo exigida al marcar bautizado.
      Verifica: `php artisan test --filter=MemberValidationTest`

- [ ] T093 [US10] · Prueba: las dos reglas de validación, con su explicación
      Archivos: `tests/Feature/Members/MemberValidationTest.php`
      Verifica: `php artisan test --filter=MemberValidationTest` — un menor marcado casado o viudo se rechaza y el mensaje explica por qué, en una frase.

- [ ] T094 [US10] · Búsqueda que ignora mayúsculas y tildes
      Archivos: `app/Models/Member.php` · `database/migrations/<ts>_set_members_collation.php`
      Verifica: `php artisan test --filter=MemberSearchTest` — «hernandez» encuentra «Hernández», y «NIÑO» encuentra «Niño».

- [ ] T095 [US10] · Filtros combinables con conteo sobre el total
      Archivos: `app/Http/Controllers/MemberController.php` · `resources/js/components/domain/FilterBar.tsx`
      Comité, sub-comité, género, estado civil, bautizado y rango de edad; quitar todos de una vez (RF-035).
      Verifica: `php artisan test --filter=MemberFilterTest` — informa «32 de 140» y el conteo respeta el ámbito del cargo.

- [ ] T096 [US10] · Lista y formulario de miembros
      Archivos: `app/Http/Controllers/MemberController.php` · `resources/js/pages/Members/Index.tsx`, `Members/Form.tsx` · ruta `/miembros`
      Verifica: `php artisan test --filter=MemberIndexTest` · a 360 px la lista se presenta en tarjetas, no en tabla cortada.

- [ ] T097 [US10] · Miembro sin comité y miembro sin sub-comité
      Archivos: `app/Models/Member.php` · `resources/js/pages/Members/Index.tsx`
      El que no pertenece a ningún comité cuenta en los reportes de la congregación y se señala como oportunidad, no como error (CL-02, CL-03, CE-06).
      Verifica: `php artisan test --filter=MemberEdgeCasesTest`

- [ ] T098 [US10] · Prueba: los cinco escenarios de HU-10, más CL-02 y CL-03
      Archivos: `tests/Feature/Members/MemberIndexTest.php` · `tests/Feature/Members/MemberEdgeCasesTest.php` · `tests/Feature/Members/MemberFilterTest.php`
      Verifica: `php artisan test tests/Feature/Members`

- [ ] T099 [US04] · Aviso de cuánta información queda fuera del alcance del cargo
      Archivos: `resources/js/components/domain/ScopeBanner.tsx` · `app/Http/Middleware/HandleInertiaRequests.php`
      RF-009: «Hay 108 hermanos de la congregación que no aparecen aquí porque pertenecen a otros comités.» El número, nunca los nombres.
      Verifica: `php artisan test --filter=ScopeBannerTest` — el número es correcto y la respuesta no trae un solo nombre ajeno · a 360 px.

## 3.2 US11 · Reportes y estadísticas

- [ ] T100 [US11] · Cálculos de las estadísticas de la congregación
      Archivos: `app/Support/Reports/MemberStats.php`
      Total, bautizados con porcentaje, estado civil, género, rangos de edad, distribución por comité, bautismos por año, y cuántos no pertenecen a ningún comité (RF-036).
      Verifica: `php artisan test --filter=MemberStatsTest`

- [ ] T101 [US11] · Prueba: las estadísticas, incluido el grupo vacío
      Archivos: `tests/Unit/Reports/MemberStatsTest.php`
      Verifica: `php artisan test --filter=MemberStatsTest` — con cero miembros devuelve ceros y no una pantalla rota (CL-12); el promedio de edad de un grupo vacío no divide por cero.

- [ ] T102 [US11] · Pantalla de reportes con gráficos
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Index.tsx` · `resources/js/components/domain/BarChart.tsx`, `DonutChart.tsx` · ruta `/reportes`
      Gráficos en SVG propio: una librería de gráficos exigiría justificación escrita y no la necesita.
      Verifica: `php artisan test --filter=ReportIndexTest` · a 360 px los gráficos se leen sin girar el teléfono.

- [ ] T103 [US11] · Vista de tabla equivalente a cada gráfico
      Archivos: `resources/js/pages/Reports/Index.tsx` · `resources/js/components/ui/ChartTableToggle.tsx`
      RF-037: quien no distingue bien los colores o prefiere los números obtiene exactamente la misma información.
      Verifica: a 360 px el conmutador funciona y la tabla trae los mismos números que el gráfico, uno por uno.

- [ ] T104 [US11] · Reportes por ámbito, con la aclaración
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Index.tsx`
      El presidente y el líder obtienen los mismos reportes con su gente, y la pantalla aclara que los totales de la congregación los ve únicamente el pastor.
      Verifica: `php artisan test --filter=ReportScopeTest` — los números de un presidente suman su comité, no la congregación.

- [ ] T105 [US11] · Prueba: los cuatro escenarios de HU-11 por los tres cargos
      Archivos: `tests/Feature/Reports/ReportIndexTest.php` · `tests/Feature/Reports/ReportScopeTest.php`
      Verifica: `php artisan test tests/Feature/Reports`

## 3.3 US12 · Descargar e imprimir

- [ ] T106 [US12] · Descarga en hoja de cálculo, respetando filtros y ámbito
      Archivos: `app/Exports/MemberReportExport.php` · `app/Exports/StatsReportExport.php` · `app/Http/Controllers/ReportController.php` · ruta `/reportes/descargar`
      RF-038: el archivo trae lo que la persona está viendo, no todo.
      Verifica: `php artisan test --filter=ReportDownloadTest` — con un filtro de 32 personas, el archivo trae 32 filas, no 140.

- [ ] T107 [US12] · Vista de impresión en blanco y negro
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Print.tsx` · `resources/css/impresion.css` · ruta `/reportes/imprimir`
      Con el nombre de la iglesia, la fecha y los filtros aplicados en el encabezado.
      Verifica: Ctrl+P produce una hoja legible en blanco y negro, sin fondos de color y sin menús · cabe en una página por sección.

- [ ] T108 [US12] · Prueba: los tres escenarios de HU-12
      Archivos: `tests/Feature/Reports/ReportDownloadTest.php`
      Verifica: `php artisan test --filter=ReportDownloadTest` — el archivo descargado por un líder contiene únicamente su ámbito.

## 3.4 US13 · Registrar un simpatizante

- [ ] T109 [US13] · Migraciones y modelos de simpatizantes
      Archivos: `database/migrations/<ts>_create_sympathizers_table.php`, `<ts>_create_sympathizer_stages_table.php`, `<ts>_create_sympathizer_visits_table.php` · `app/Models/Sympathizer.php`, `SympathizerStage.php`, `SympathizerVisit.php` · factories
      El nivel de compromiso no se guarda (D-10).
      Verifica: `php artisan test --filter=SympathizerModelTest` · `ScopeCoverageTest` en verde.

- [ ] T110 [US13] · Registro por cualquier cargo
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Http/Requests/StoreSympathizerRequest.php` · `resources/js/pages/Sympathizers/Form.tsx` · ruta POST `/simpatizantes`
      Nombre, edad, género, teléfono, barrio, cómo llegó, quién lo invitó y la fecha del primer contacto (RF-040, RF-041).
      Verifica: `php artisan test --filter=RegisterSympathizerTest` — el seguimiento queda asignado al Comité de Evangelismo · a 360 px.

- [ ] T111 [US13] · Aviso de duplicado, con continuar o cancelar
      Archivos: `app/Support/Sympathizers/DuplicateCheck.php` · `resources/js/pages/Sympathizers/Form.tsx`
      CL-09: dos personas registran al mismo visitante.
      Verifica: `php artisan test --filter=SympathizerDuplicateTest` — advierte por nombre y teléfono, y deja decidir sin perder lo escrito.

- [ ] T112 [US13] · El seguimiento es solo del Comité de Evangelismo
      Archivos: `app/Policies/SympathizerPolicy.php` · `resources/js/pages/Sympathizers/Index.tsx`
      Verifica: `php artisan test --filter=SympathizerScopeTest` — quien lo registró no ve su ficha de seguimiento y recibe una explicación que no parece un error.

- [ ] T113 [US13] · Prueba: los tres escenarios de HU-13
      Archivos: `tests/Feature/Sympathizers/RegisterSympathizerTest.php` · `tests/Feature/Sympathizers/SympathizerScopeTest.php`
      Verifica: `php artisan test --filter="RegisterSympathizerTest|SympathizerScopeTest"`

## 3.5 US14 · Acompañar a los simpatizantes

- [ ] T114 [US14] · Cálculo del nivel de compromiso
      Archivos: `app/Support/Sympathizers/Commitment.php`
      Asistencia reciente y días desde la última visita. Nadie lo escribe a mano (RF-043).
      Verifica: `php artisan test --filter=CommitmentTest`

- [ ] T115 [US14] · Prueba: creciendo, estable, alejándose, y el que vuelve meses después
      Archivos: `tests/Unit/Sympathizers/CommitmentTest.php`
      CL-10: su historia no se reinicia.
      Verifica: `php artisan test --filter=CommitmentTest` — el que vuelve conserva sus etapas y sus fechas anteriores.

- [ ] T116 [US14] · Pantalla de simpatizantes: embudo de cinco etapas y semáforo
      Archivos: `app/Http/Controllers/SympathizerController.php` · `resources/js/pages/Sympathizers/Index.tsx` · `resources/js/components/domain/StepPath.tsx` · ruta `/simpatizantes`
      Cuántos acompañamos, cuántos creciendo, cuántos estables, cuántos alejándose.
      Verifica: `php artisan test --filter=SympathizerIndexTest` · a 360 px el embudo se presenta en vertical, legible.

- [ ] T117 [US14] · Ficha con etapas fechadas y bitácora de visitas
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Http/Controllers/SympathizerVisitController.php` · `resources/js/pages/Sympathizers/Show.tsx` · ruta `/simpatizantes/{id}`
      Las cinco etapas con el vocabulario de la IPUC: visitó el culto; asiste con frecuencia; recibe estudio bíblico; se bautizó en el nombre de Jesucristo; recibió el Espíritu Santo (RF-042, RF-044).
      Verifica: `php artisan test --filter=SympathizerShowTest` — registrar una visita recalcula el compromiso · a 360 px.

- [ ] T118 [US14] · Paso a miembro, aprobado por el pastor
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Support/Approvals/ApprovalFlow.php` · `app/Support/Sympathizers/ConvertToMember.php`
      RF-019, RF-045, RN-06. Conserva la fecha de bautismo y la del primer contacto.
      Verifica: `php artisan test --filter=SympathizerToMemberTest` — sin la aprobación del pastor la persona no entra a la lista de miembros, por el servidor.

- [ ] T119 [US14] · Prueba: los cinco escenarios de HU-14
      Archivos: `tests/Feature/Sympathizers/SympathizerIndexTest.php` · `tests/Feature/Sympathizers/SympathizerShowTest.php` · `tests/Feature/Sympathizers/SympathizerToMemberTest.php`
      Verifica: `php artisan test tests/Feature/Sympathizers`

## 3.6 US18 · Cargar la lista inicial de miembros

> **Antes de empezar este bloque, confirmar con el pastor.** El supuesto S-02 del spec no está
> listado en la Cláusula Segunda del contrato (riesgo R-08). Si el cliente no lo pide, el bloque
> se registra como propuesta para cotizar y las 140 fichas se cargan con un comando de semilla.

- [ ] T120 [US18] · Plantilla de hoja de cálculo para la carga inicial
      Archivos: `app/Exports/MemberTemplateExport.php` · `public/plantillas/miembros.xlsx`
      La fecha de nacimiento es opcional (mitiga R-05); si falta, el miembro queda marcado para completar y no aparece en los reportes por edad.
      Verifica: `php artisan test --filter=MemberTemplateTest` — la plantilla descargada trae los encabezados exactos que lee la importación.

- [ ] T121 [US18] · Importación con validación previa, fila por fila
      Archivos: `app/Http/Controllers/MemberImportController.php` · `app/Imports/MemberImport.php` · `resources/js/pages/Members/Import.tsx` · ruta `/miembros/importar`
      Muestra cuántas filas están correctas y cuáles tienen error **antes de guardar nada**; permite cargar solo las correctas; advierte duplicados y deja actualizar u omitir (RF-039, CL-14).
      Verifica: `php artisan test --filter=MemberImportTest` · a 360 px la vista previa se recorre en tarjetas.

- [ ] T122 [US18] · Prueba: los tres escenarios de HU-18 con un archivo mixto
      Archivos: `tests/Feature/Members/MemberImportTest.php` · `tests/fixtures/miembros-mixto.xlsx`
      Verifica: `php artisan test --filter=MemberImportTest` — diez filas, tres con error: nada se guarda hasta confirmar, y al confirmar entran siete.

## 3.7 Cierre del hito 3

- [ ] T123 · Semilla de la gente: 140 miembros y 37 simpatizantes
      Archivos: `database/seeders/MemberSeeder.php` · `database/seeders/SympathizerSeeder.php`
      Los datos de la presentación (D-14). Ficticios, como exige la constitución. Incluye los casos límite: sin comité, sin sub-comité, menor de edad, no bautizado, y el simpatizante que se alejó.
      Verifica: `php artisan migrate:fresh --seed` · los reportes dan los mismos números que el pastor vio en la presentación.

- [ ] T124 · Presupuesto de consultas de las pantallas del hito 3
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: miembros, reportes, simpatizantes y la ficha de simpatizante por debajo de cinco consultas cada una.

- [ ] T125 · Puerta del hito 3
      Archivos: `docs/hitos/hito-3.md`
      Verifica: las siete puertas pasan · producción sirve el hito · revisión en celular real anotada.

---

# FASE 4 · Hito 4 — Cerrar

Semana 4. Cubre US15, US16 y US19, y los barridos de calidad de todo el sistema.

## 4.1 US15 · Registrar asistencia

- [ ] T126 [US15] · Migraciones y modelos de asistencia
      Archivos: `database/migrations/<ts>_create_meetings_table.php`, `<ts>_create_meeting_attendances_table.php` · `app/Models/Meeting.php`, `MeetingAttendance.php` · factories
      Una fila por convocado, con `present` verdadero o falso.
      Verifica: `php artisan test --filter=MeetingModelTest` · `ScopeCoverageTest` en verde.

- [ ] T127 [US15] · Registro de reunión con convocados y asistentes
      Archivos: `app/Http/Controllers/MeetingController.php` · `app/Http/Requests/StoreMeetingRequest.php` · `resources/js/pages/Meetings/Index.tsx`, `Meetings/Form.tsx` · ruta `/asistencia`
      Fecha, tipo, tema, convocados y asistentes. Los convocados solo pueden ser miembros de ese comité o sub-comité (RF-046, RF-047).
      Verifica: `php artisan test --filter=MeetingTest` — convocar a alguien de otro comité se rechaza en el servidor · a 360 px se marca la asistencia con el pulgar, una fila por persona.

- [ ] T128 [US15] · Porcentaje por persona sobre las reuniones a las que fue convocada
      Archivos: `app/Support/Meetings/AttendanceRate.php`
      Verifica: `php artisan test --filter=AttendanceRateTest`

- [ ] T129 [US15] · Prueba: el porcentaje, incluida la reunión sin ningún asistente
      Archivos: `tests/Unit/Meetings/AttendanceRateTest.php`
      CL-11: una reunión sin asistentes se registra, porque es información.
      Verifica: `php artisan test --filter=AttendanceRateTest` — quien fue convocado a cero reuniones no da error ni 0 %, da «sin convocatorias».

- [ ] T130 [US15] · Señal de acompañamiento por debajo del sesenta por ciento
      Archivos: `resources/js/pages/Meetings/Index.tsx` · `resources/js/components/domain/CarePill.tsx`
      RF-049: en tono de cuidado, nunca de reproche.
      Verifica: el texto dice «conviene buscarlo» o equivalente, y en ninguna parte aparece «incumplido», «falló» ni «ausente injustificado» · a 360 px.

- [ ] T131 [US15] · Corrección de asistencia conservando lo anterior
      Archivos: `app/Http/Controllers/MeetingController.php` · `app/Models/MeetingAttendance.php`
      Verifica: `php artisan test --filter=AttendanceCorrectionTest` — tras corregir, la bitácora conserva qué se había registrado antes, quién lo cambió y cuándo.

- [ ] T132 [US15] · Prueba: los tres escenarios de HU-15
      Archivos: `tests/Feature/Meetings/MeetingTest.php` · `tests/Feature/Meetings/AttendanceCorrectionTest.php`
      Verifica: `php artisan test tests/Feature/Meetings`

## 4.2 US16 · Manejar el presupuesto del comité

- [ ] T133 [US16] · Migraciones y modelos de presupuesto
      Archivos: `database/migrations/<ts>_create_budgets_table.php`, `<ts>_create_budget_movements_table.php` · `app/Models/Budget.php`, `BudgetMovement.php` · factories
      Aplica a comités y a sub-comités: el presidente reparte asignando filas a sus hijos.
      Verifica: `php artisan test --filter=BudgetModelTest` · `ScopeCoverageTest` en verde.

- [ ] T134 [US16] · Saldo calculado, no guardado
      Archivos: `app/Support/Budget/BalanceCalculator.php`
      Asignado, gastado, disponible y porcentaje usado (D-10).
      Verifica: `php artisan test --filter=BalanceTest`

- [ ] T135 [US16] · Prueba: el saldo, incluido el comité sin presupuesto asignado
      Archivos: `tests/Unit/Budget/BalanceTest.php`
      CL-04: las pantallas de presupuesto funcionan en cero, no fallan.
      Verifica: `php artisan test --filter=BalanceTest` — asignado 0 y gastado 0 da 0 %, no una división por cero.

- [ ] T136 [US16] · Asignación del pastor y reparto del presidente
      Archivos: `app/Http/Controllers/BudgetController.php` · `app/Http/Requests/AssignBudgetRequest.php` · `resources/js/pages/Budget/Index.tsx` · ruta `/presupuesto`
      RF-050. El presidente ve asignado, gastado, disponible, porcentaje usado y el reparto entre sus sub-comités.
      Verifica: `php artisan test --filter=BudgetAssignmentTest` — un presidente no asigna fuera de su comité · a 360 px.

- [ ] T137 [US16] · Advertencia por exceder el saldo, que permite continuar
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/components/domain/OverBudgetNotice.tsx`
      RF-051, RN-05: el presupuesto es guía, no tope rígido. Advierte, no bloquea, y queda registrado.
      Verifica: `php artisan test --filter=OverBudgetTest` — aprobar un gasto que deja el saldo en negativo exige confirmación y deja el registro del exceso.

- [ ] T138 [US16] · Movimientos con fecha, concepto, monto, soporte y quién autorizó
      Archivos: `app/Http/Controllers/BudgetMovementController.php` · `resources/js/pages/Budget/Index.tsx`
      RF-052.
      Verifica: `php artisan test --filter=BudgetMovementTest` — ningún movimiento se guarda sin quién lo autorizó.

- [ ] T139 [US16] · Vista del pastor: los seis comités, el total, y quien pasó del noventa por ciento
      Archivos: `app/Http/Controllers/BudgetController.php` · `resources/js/pages/Budget/Index.tsx`
      Verifica: `php artisan test --filter=BudgetPastorViewTest` · a 360 px los seis comités en tarjetas, el que se pasó destacado.

- [ ] T140 [US16] · Aclaración permanente sobre la tesorería
      Archivos: `resources/js/pages/Budget/Index.tsx` · `resources/js/components/domain/TreasuryNotice.tsx`
      RF-053: aquí solo se registra en qué se usaron los recursos y quién los autorizó.
      Verifica: visible sin desplazarse en las tres vistas de presupuesto (pastor, presidente, líder) · a 360 px.

- [ ] T141 [US16] · Prueba: los cinco escenarios de HU-16 por los tres cargos
      Archivos: `tests/Feature/Budget/BudgetAssignmentTest.php` · `tests/Feature/Budget/OverBudgetTest.php` · `tests/Feature/Budget/BudgetPastorViewTest.php`
      Verifica: `php artisan test tests/Feature/Budget`

## 4.3 US19 · Corregir un dato equivocado

- [ ] T142 [US19] · Bitácora activada en los siete modelos del plan
      Archivos: `app/Models/Member.php`, `Activity.php`, `Approval.php`, `BudgetMovement.php`, `MeetingAttendance.php`, `Sympathizer.php`, `CommitteeUser.php` · `config/activitylog.php`
      Principio III, RF-056: qué decía antes, quién lo cambió y cuándo.
      Verifica: `php artisan test --filter=ActivityLogCoverageTest` — cambiar un monto deja el valor anterior, el autor y la fecha, en los siete modelos.

- [ ] T143 [US19] · Pantalla de historial de un registro
      Archivos: `app/Http/Controllers/HistoryController.php` · `resources/js/components/domain/ChangeTrail.tsx` · ruta `/historial/{tipo}/{id}`
      En lenguaje de la congregación: «El hermano Rigoberto cambió el monto de $100.000 a $150.000 el 12/03/2027». Nunca nombres de columna.
      Verifica: `php artisan test --filter=HistoryTest` — la secuencia completa, en orden · a 360 px.

- [ ] T144 [US19] · La aprobación resuelta no se edita
      Archivos: `app/Policies/ApprovalPolicy.php` · `resources/js/pages/Approvals/Index.tsx`
      RF-057, RN-07: se registra una decisión nueva. El botón no existe **y** la ruta lo rechaza.
      Verifica: `php artisan test --filter=ApprovalImmutabilityTest` — la ruta responde que no se puede, aunque se llame directamente.

- [ ] T145 [US19] · Prueba: los tres escenarios de HU-19
      Archivos: `tests/Feature/History/HistoryTest.php`
      Verifica: `php artisan test --filter=HistoryTest`

- [ ] T146 · Pérdida de conexión sin perder lo escrito
      Archivos: `resources/js/lib/borrador.ts` · `resources/js/components/ui/Form.tsx`
      CL-15. Guarda el borrador en el dispositivo y lo repone al volver; avisa en una frase clara.
      Verifica: con la red apagada en el navegador, el formulario avisa, y al reconectar conserva lo que la persona escribió.

## 4.4 Barridos de calidad de todo el sistema

Estas seis tareas se corren sobre el sistema completo, no sobre una pantalla.

- [ ] T147 · Barrido de jerga técnica en la interfaz
      Archivos: `resources/js/` · `lang/es/`
      Principio IV, RN-08.
      Verifica: `grep -rniE "endpoint|query|dashboard|tenant|login|token|logout|submit" resources/js lang` no devuelve nada en texto visible al usuario.

- [ ] T148 · Barrido de vocabulario IPUC
      Archivos: `resources/js/` · `lang/es/` · `database/seeders/`
      Verifica: `grep -rniE "misa|sacerdote|p[áa]rroco|trinidad|padre, hijo y esp[íi]ritu" resources database lang` no devuelve nada · aparecen culto, bautismo en el nombre de Jesucristo, Escuela Dominical, REFAM, Damas Dorcas, vigilia, convención y Santa Cena.

- [ ] T149 · Barrido de `DB::` y SQL crudo sobre tablas con ámbito
      Archivos: `app/`
      Puerta de calidad 6.
      Verifica: `grep -rnE "DB::|->whereRaw|->selectRaw" app/` no devuelve nada sobre tablas con ámbito.

- [ ] T150 · Presupuesto de consultas de las catorce pantallas
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Puerta de calidad 5, y base del CE-07.
      Verifica: `php artisan test --filter=QueryBudgetTest` — las catorce por debajo de cinco consultas.

- [ ] T151 [US04] · Prueba de aislamiento por pantalla: catorce pantallas, catorce pruebas
      Archivos: `tests/Feature/Scope/ScreenIsolationTest.php`
      Puerta de calidad 3. Es la prueba que el cliente pediría ver.
      Verifica: `php artisan test --filter=ScreenIsolationTest` — un cargo ajeno no ve ninguna de las catorce, ni por lista ni por identificador directo.

- [ ] T152 [US20] · Afinar la aplicación instalada
      Archivos: `vite.config.ts` · `public/manifest.webmanifest` · `resources/js/lib/actualizacion.ts`
      Iconos en todos los tamaños, pantalla de arranque, y lectura de lo ya cargado sin conexión.
      Verifica: instalada en un celular real, abre desde el icono, muestra la pantalla de arranque de la iglesia, y sin señal informa en lenguaje claro en vez de mostrar una página de error del navegador.

- [ ] T153 · Puerta del hito 4
      Archivos: `docs/hitos/hito-4.md`
      Verifica: las siete puertas pasan sobre el sistema completo · producción sirve el hito.

---

# FASE 5 · Entrega

- [ ] T154 · Migraciones sobre base de datos en limpio
      Puerta de calidad 2.
      Verifica: `php artisan migrate:fresh --seed` en una base vacía, sin un solo error ni advertencia.

- [ ] T155 · Suite completa en verde
      Puerta de calidad 1.
      Verifica: `php artisan test` · ninguna prueba omitida, saltada ni marcada como pendiente · `ScopeCoverageTest` y `QueryBudgetTest` incluidas.

- [ ] T156 · Revisión a 360 px de las catorce pantallas
      Archivos: `docs/revision-360.md`
      Puerta de calidad 4.
      Verifica: una captura de cada pantalla a 360 px anotada en el documento · cero desplazamiento horizontal · ninguna zona pulsable por debajo de 44x44 px.

- [ ] T157 · Medición final en producción
      Archivos: `docs/pruebas-rendimiento.md`
      Verifica: Lighthouse móvil sobre la URL de producción: instalable, y apertura de la pantalla principal por debajo de 3 segundos en 3G simulada.

- [ ] T158 · Acompañar al pastor en la carga de la información de la congregación
      Archivos: `docs/puesta-en-marcha.md`
      La información es propiedad del cliente: se carga en su servidor, no entra al repositorio y no sale de allí (constitución, datos y seguridad). Base del CE-05 y del CE-06.
      Verifica: los 140 miembros quedan cargados en producción · `php artisan db:respaldo` corre sobre los datos reales · ningún dato de la semilla queda en producción.

- [ ] T159 · Manual de uso en lenguaje de la congregación
      Archivos: `docs/manual/`
      Una página por sección, con capturas del celular.
      Verifica: una hermana que no participó en el desarrollo entra y registra un miembro sin que nadie le explique nada. Es la prueba de aceptación del Principio IV; si no pasa, la pantalla se corrige, no el manual.

- [ ] T160 · Capacitación de las diecisiete personas con cargo
      Archivos: `docs/capacitacion.md`
      Verifica: cada una entra, cambia su clave, y resuelve o envía una solicitud de prueba. Base del CE-01.

- [ ] T161 · Acta de entrega
      Archivos: `docs/acta-entrega.md`
      Cláusula Tercera del contrato: desde la firma corren los cinco días hábiles para observaciones.
      Verifica: firmada por el pastor, con la lista de los módulos entregados y la fecha.

- [ ] T162 · Línea base de los criterios de éxito
      Archivos: `docs/criterios-exito.md`
      CE-01 a CE-08 con su forma de medición y el valor del día uno, para poder medirlos a los seis meses.
      Verifica: los ocho criterios con un número de partida y una consulta o pantalla que los mide.

---

# Cobertura del spec

## Requisitos funcionales — los 57

| RF | Tareas | RF | Tareas |
|---|---|---|---|
| RF-001 | T029 | RF-030 | T081 |
| RF-002 | T031, T033 | RF-031 | T090 *(D-09: fecha de nacimiento)* |
| RF-003 | T034, T035, T036 | RF-032 | T092, T093 |
| RF-004 | T032 | RF-033 | T092, T093 |
| RF-005 | T037 | RF-034 | T094 |
| RF-006 | T038 | RF-035 | T095 |
| RF-007 | T018, T019, T021, T022, T024 | RF-036 | T100, T102 |
| RF-008 | T026, T027 | RF-037 | T103 |
| RF-009 | T028, T099 | RF-038 | T106, T107, T108 |
| RF-010 | T078, T079 | RF-039 | T121, T122 |
| RF-011 | T015 | RF-040 | T110, T112 |
| RF-012 | T042, T043 | RF-041 | T110 |
| RF-013 | T016, T017 | RF-042 | T117 |
| RF-014 | T090, T097 | RF-043 | T114, T115 |
| RF-015 | T057, T058 | RF-044 | T117 |
| RF-016 | T058, T065 | RF-045 | T118 |
| RF-017 | T058, T065 | RF-046 | T127 |
| RF-018 | T058, T059 | RF-047 | T127 |
| RF-019 | T118 | RF-048 | T128, T129 |
| RF-020 | T070 | RF-049 | T130 |
| RF-021 | T060, T142 | RF-050 | T136 |
| RF-022 | T072 | RF-051 | T137 |
| RF-023 | T061 | RF-052 | T138 |
| RF-024 | T075 | RF-053 | T140 |
| RF-025 | T074 | RF-054 | T084 |
| RF-026 | T054 | RF-055 | T028, T085 |
| RF-027 | T055, T056 | RF-056 | T142, T143 |
| RF-028 | T064 | RF-057 | T060, T144 |
| RF-029 | T081 | | |

## Casos límite — los 15

| CL | Tareas | CL | Tareas |
|---|---|---|---|
| CL-01 | T042, T043 | CL-09 | T111 |
| CL-02 | T097, T098 | CL-10 | T115 |
| CL-03 | T097, T098, T100 | CL-11 | T129 |
| CL-04 | T135 | CL-12 | T101 |
| CL-05 | T017, T019, T020 | CL-13 | T076, T077 |
| CL-06 | T061 | CL-14 | T121, T122 |
| CL-07 | T063 | CL-15 | T146 |
| CL-08 | T064 | | |

## Reglas de negocio — las 9

| RN | Tareas |
|---|---|
| RN-01 | T019, T020 |
| RN-02 | T019, T058 |
| RN-03 | T058, T075 |
| RN-04 | T058, T059 |
| RN-05 | T137 |
| RN-06 | T118 |
| RN-07 | T060, T142, T144 |
| RN-08 | T147, T148 |
| RN-09 | T017, T038 |

## Criterios de éxito — los 8

| CE | Lo que lo hace posible | Línea base |
|---|---|---|
| CE-01 | T160 capacitación | T162 |
| CE-02 | T074 aprobación del plan en un acto | T162 |
| CE-03 | T084, T085 pendientes visibles al entrar | T162 |
| CE-04 | T064 detector de cruce | T162 |
| CE-05 | T158 carga acompañada | T162 |
| CE-06 | T097 miembro sin comité señalado | T162 |
| CE-07 | T150 presupuesto de consultas · T157 medición | T162 |
| CE-08 | T127 registro de asistencia en el celular | T162 |

## Principios de la constitución

| Principio | Tareas que lo garantizan |
|---|---|
| I · Privacidad entre comités (NO NEGOCIABLE) | T018–T028, T040, T062, T067, T078, T079, T092, T099, T112, T149, T151 |
| II · Primero el celular | T044–T048, T152, T156, T157 · y la revisión a 360 px de cada tarea de pantalla |
| III · Toda decisión deja rastro | T060, T061, T142, T143, T144 |
| IV · El lenguaje de la congregación | T026, T130, T140, T143, T147, T148, T159 |
| V · Ninguna tarea sin pruebas (NO NEGOCIABLE) | Las 30 tareas `Prueba:` · T012, T024, T025, T050, T079, T150, T151, T155 |

## Historias de usuario — las 20

| HU | Tareas | HU | Tareas |
|---|---|---|---|
| US01 | T014, T029–T033 | US11 | T100–T105 |
| US02 | T016, T017, T034–T039, T061 | US12 | T106–T108 |
| US03 | T015, T040–T043 | US13 | T109–T113 |
| US04 | T019–T028, T099, T151 | US14 | T114–T119 |
| US05 | T062–T066 | US15 | T126–T132 |
| US06 | T057–T060, T067–T071 | US16 | T133–T141 |
| US07 | T072, T073 | US17 | T084–T086 |
| US08 | T053, T074–T077 | US18 | T120–T122 |
| US09 | T054–T056, T078–T083 | US19 | T060, T142–T145 |
| US10 | T090–T099 | US20 | T044–T048, T152 |

---

# Lo que este documento deja pendiente de confirmar

1. **S-02, importación de miembros (T120–T122).** No está listada en la Cláusula Segunda del
   contrato. Confirmar antes de la semana 3; si no se confirma, se registra como propuesta para
   cotizar y las fichas se cargan con un comando de semilla.
2. **S-01, avisos solo dentro del sistema (T084, T085).** El spec lo asume y el contrato excluye
   WhatsApp, correo y mensajes de texto. Vale decírselo al pastor con esas palabras en la
   capacitación, para que no lo espere.
3. **R-05, fechas de nacimiento (T090, T120).** Si el cliente no las tiene todas, el campo queda
   opcional y esos miembros no entran en los reportes por edad hasta completarlos.
