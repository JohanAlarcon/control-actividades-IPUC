---

description: "Task list template for feature implementation"
---

# Tasks: Sistema de Comités IPUC

**Input**: Design documents from `/specs/001-sistema-comites-ipuc/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Las pruebas **sí** se incluyen y no son opcionales en este proyecto. El principio V de la
constitución es NO NEGOCIABLE: ninguna tarea se marca terminada sin pruebas automatizadas que la
cubran y que pasen. No se exige TDD estricto —la prueba puede escribirse después de implementar—,
así que cada tarea `Prueba:` va inmediatamente después de lo que verifica.

**Organization**: una fase por historia de usuario, en el orden de prioridad de `spec.md`, para que
cada historia se pueda implementar y probar por separado. La columna «Hito» de cada fase indica a
qué entrega del contrato pertenece, de modo que la estructura comercial de los cuatro hitos
semanales se conserva.

## Format: `[ID] [P?] [Story] Description`

```
- [ ] Tnnn [P] [US01] · Título de la tarea
      Archivos: ruta/exacta/del/archivo.php · otra/ruta.tsx
      Verifica: el comando o la comprobación que la cierra
```

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes).
- **[Story]**: a qué historia pertenece. `[US01]` es `HU-01` del spec; `[US20]` es `HU-20`.
  Las fases de arranque y de cierre no llevan etiqueta de historia. En el cimiento la llevan las
  tareas que una sola historia exige, y **no** la llevan las del bloque 2.3, que sirven a varias:
  su trazabilidad vive en la tabla de cobertura por historia. Una tarea que sirve por igual a dos
  historias lleva **las dos etiquetas** (`[US10] [US13]`): se ejecuta una sola vez, en la fase de
  la primera, y cuenta como cobertura de las dos.
- **Rutas de archivo**: van en la línea `Archivos:`, no en el título. Es la convención de este
  proyecto: deja el título legible y permite listar varios archivos y su comando de verificación.
- **Identificadores**: `Tnnn` no se reutiliza ni se renumera, aunque una tarea se cancele. **El
  orden de ejecución es el orden del documento, no el del número**: T163 a T177 son las tareas que
  cerraron los vacíos detectados en `/speckit-plan` y están insertadas donde les corresponde
  ejecutarse.
- **`<ts>`**: marca de tiempo que genera Laravel al crear la migración.
- **Referencias cruzadas**: `RG-xx` es un riesgo del registro de riesgos de `plan.md`; `R-xx`, una
  decisión de investigación de `research.md`. `D-xx` es una decisión de arquitectura del plan y
  `DEP-xx` una dependencia del cliente del spec. `C-xx` es una corrección, `V-xx` un vacío
  cerrado, `A-xx` un supuesto sin confirmar y `S-xx` un supuesto del spec. Las familias comparten
  numeración, así que el prefijo es lo que las distingue.

**Criterio de terminado.** Una tarea se marca hecha cuando su comando de verificación pasa. Las
tareas de pantalla exigen además la revisión a 360 px de ancho sin desplazamiento horizontal
(principio II, puerta de calidad 4). No hay tarea terminada «a falta de la prueba».

## Path Conventions

Laravel estándar, sin capas adicionales (decisión D-01 del plan). Rutas desde la raíz del
repositorio:

- Servidor: `app/Http/Controllers/`, `app/Models/`, `app/Policies/`, `app/Support/`, `app/Exports/`
- Interfaz: `resources/js/pages/`, `resources/js/components/`, `resources/js/layouts/`
- Esquema: `database/migrations/`, `database/seeders/`, `database/factories/`
- Pruebas: `tests/Feature/`, `tests/Unit/`, `tests/Architecture/`
- Rutas HTTP: `routes/web.php` y `routes/auth.php`, **en inglés** (corrección C-02 del plan)

## Política de alcance

El alcance de este documento **no se recorta**. Si el calendario no alcanza, se mueve la fecha de
entrega, nunca el alcance y nunca la calidad.

Esto **enmienda la mitigación del riesgo RG-04** del plan, que decía negociar con el alcance.
Consecuencia a tener presente desde la semana 1: la Cláusula Tercera del contrato fija cuatro
semanas y el 50 % final está atado a la entrega, así que mover la fecha exige un acuerdo escrito con
el pastor **antes** del vencimiento, no después. El indicador temprano es el cierre del hito 1: si
la semana 1 termina sin el filtro de alcance desplegado y probado, la conversación va en la semana
2, no en la 4.

## Mapa de fases

| Fase | Historia | Prioridad | Hito | Tareas |
|---|---|---|---|---|
| 1 · Setup | — | — | Arranque | T001–T012 |
| 2 · Foundational | — | — | 1 · Cimiento | T013–T025, T028, T053–T055, T057, T163–T165 |
| 3 | US01 · Entrar al sistema | P1 🎯 MVP | 1 | T029–T033 |
| 4 | US02 · Cuentas y cargos | P1 | 1 | T034–T039, T170, T171, T061 |
| 5 | US03 · Estructura de comités | P1 | 1 | T040–T043 |
| 6 | US04 · Cada comité ve lo suyo | P1 | 1 | T026, T027, T099, T151 |
| 7 | US20 · Primero el celular | P1 | 1 | T044–T048, T152 |
| 8 | US06 · Revisar lo que proponen | P1 | 2 · Decidir | T058–T060, T067–T071 |
| 9 | US05 · Proponer una actividad | P1 | 2 | T062–T066 |
| 10 | US07 · Aprobar lo que sube al pastor | P1 | 2 | T072, T073 |
| 11 | US08 · Aprobar el plan del año | P1 | 2 | T074–T077 |
| 12 | US09 · Consultar el cronograma | P1 | 2 | T056, T166, T167, T078–T083 |
| 13 | US17 · Saber qué tengo pendiente | P2 | 2 | T084–T086 |
| 14 | US10 · Registrar y consultar miembros | P2 | 3 · La gente | T090–T098, T172–T175 |
| 15 | US11 · Reportes y estadísticas | P2 | 3 | T100–T105 |
| 16 | US12 · Descargar e imprimir | P2 | 3 | T106–T108 |
| 17 | US13 · Registrar un simpatizante | P2 | 3 | T109–T113, T176 |
| 18 | US14 · Acompañar a los simpatizantes | P2 | 3 | T114–T119 |
| 19 | US15 · Registrar asistencia | P2 | 4 · Cerrar | T126–T132 |
| 20 | US16 · Presupuesto del comité | P2 | 4 | T133–T141, T168, T169 |
| 21 | US18 · Cargar la lista inicial | P3 | 3 | T120–T122 |
| 22 | US19 · Corregir un dato equivocado | P3 | 4 | T142–T146, T177 |
| 23 | US21 · Administrar la estructura | **P1** | 1 | T181–T185 |
| 24 | US22 · Levantar el acta de una reunión | P2 | 4 | T187–T195 |
| 25 | US23 · Evidencias de cumplimiento | P2 | 4 | T196–T203 |
| 26 | US24 · Soporte de un gasto | P2 | 4 | T204, T205 |
| 27 · Polish | — | — | 4 y Entrega | T049–T052, T087–T089, T123–T125, T147–T150, T153–T162, T206 |

Las fases 23 a 26 y las tareas T178 a T206 entran por la **ampliación de alcance del 2026-10-24**
(constitución 2.1.0). Dos advertencias sobre el orden:

- **La Phase 23 es P1 y se ejecuta justo después de la Phase 2**, aunque aparezca al final del
  documento: se puso ahí para no renumerar las veinte fases anteriores. La semilla de demostración
  (T049) depende de ella.
- **T179 y T180 van en la Phase 2**, antes de T019, porque `SubtreeResolver` bloquea a `CurrentScope`
  y el filtro de tres niveles es lo primero que hay que tener sólido (RG-14).

**Camino crítico.** T013 → T016 → T019 → T022 → T024 → (todo lo demás). El filtro de alcance
bloquea el proyecto entero: ninguna pantalla con datos de comité se construye antes de T024. Es
deliberado — el principio I no se agrega después.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: arranque del proyecto e infraestructura compartida. Días 1 y 2. El servidor se levanta
ahora, no en la semana 4 (mitiga RG-07).

- [x] T001 · Crear el proyecto desde el kit inicial oficial de Laravel 12 para React
      Archivos: todo el árbol en `C:/laragon/www/control-actividades/`
      Verifica: `php artisan --version` imprime 12.x · `npm run build` termina sin error · `http://control-actividades.test` carga la pantalla de bienvenida.

- [x] T002 · Configurar el entorno colombiano
      Archivos: `config/app.php` · `.env` · `.env.example`
      `APP_TIMEZONE=America/Bogota`, `APP_LOCALE=es`, moneda COP.
      Verifica: `php artisan tinker --execute="echo now()->format('d/m/Y H:i');"` imprime la hora de Bogotá · `config('app.locale')` es `es`.

- [x] T003 [P] · Instalar y fijar las tres dependencias justificadas del plan
      Archivos: `composer.json` · `package.json` · `specs/001-sistema-comites-ipuc/research.md`
      Fijar versión **exacta**, sin `^` ni `~` (constitución). Anotar el número resuelto en R-02 de `research.md`.
      Verifica: `composer show spatie/laravel-activitylog maatwebsite/excel` muestra versiones exactas · `vite-plugin-pwa` fijado en `package.json` · `composer require` resolvió sin conflicto en PHP 8.4.

- [x] T004 [P] · Escribir la guía de ejecución del proyecto
      Archivos: `CLAUDE.md`
      **Hecho el 2026-10-24.** Cierra el TODO que dejó la constitución: la gobernanza designa este archivo como guía en tiempo de ejecución.
      Verifica: contiene los cinco principios en una línea cada uno, las nueve puertas de calidad, las prohibiciones (`DB::` y SQL crudo, Livewire y Filament, archivos desde `public/`, salidas del filtro), la regla de idioma, los límites de tamaño de los componentes de React, las tres familias de pruebas, el flujo de trabajo y las cuatro familias de identificadores.

- [ ] T207 · Configurar los límites de tamaño en el linter
      Archivos: `eslint.config.js` · `package.json`
      Puerta de calidad 8, automatizada: `max-lines` en 300 y `max-lines-per-function` en 70 sobre `resources/js/**/*.tsx` (constitución 2.2.0). Un componente que se pase rompe la construcción, no la revisión a ojo.
      Verifica: `npm run lint` falla con un componente de 310 líneas creado a propósito, y pasa al dividirlo · `find resources/js -name "*.tsx" | xargs wc -l | sort -rn | head` no muestra ningún archivo por encima de 300.

- [x] T005 [P] · Configurar Pest y la base de datos de pruebas
      Archivos: `tests/Pest.php` · `phpunit.xml` · `.env.testing`
      Verifica: `php artisan test` pasa en verde con la suite del kit inicial.

- [x] T006 [P] · Repositorio, rama de trabajo y convención de commits
      Archivos: `.gitignore` · `.gitmessage` · `docs/convenciones.md`
      Crear la rama `001-sistema-comites-ipuc`: todavía no existe, y la gobernanza exige trabajo por rama de funcionalidad.
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

- [ ] T010 · Respaldo diario de la base de datos **y de los archivos**
      Archivos: `app/Console/Commands/BackupDatabase.php` · `routes/console.php`
      Comando `db:backup` — en inglés, por la regla de idioma del código. **Incluye el directorio de archivos adjuntos**, no solo la base (RF-078, DEP-07, RG-13): sin eso, una falla de disco pierde las evidencias y los soportes de factura aunque la base se recupere. El módulo de archivos no se despliega hasta que esta tarea esté hecha.
      Verifica: `php artisan db:backup` deja el archivo con la fecha en el nombre y el respaldo contiene los adjuntos · `crontab -l` muestra la entrada diaria.

- [ ] T011 [P] · Prueba: el respaldo genera archivo y no escribe datos de personas en el registro
      Archivos: `tests/Feature/BackupCommandTest.php`
      Verifica: `php artisan test --filter=BackupCommandTest`

- [ ] T012 · Puerta de calidad automatizada en cada envío
      Archivos: `.github/workflows/pruebas.yml`
      Verifica: corre `php artisan test` y `npm run build` en cada push y en cada solicitud de fusión · un push con una prueba roja pone la acción en rojo.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: el esquema de identidad, **el filtro de alcance** y el esquema compartido que varias
historias necesitan a la vez: aprobaciones, actividades y plan anual. Es la infraestructura que
debe estar completa antes de cualquier historia.

**⚠️ CRÍTICO**: ninguna historia puede empezar antes de terminar esta fase. El principio I es NO
NEGOCIABLE y es lo primero que el cliente comprueba. Tres capas, de modo que ninguna falle sola.
Ninguna tarea del bloque 2.1 va en paralelo: bloquea todo lo demás.

### 2.1 · Esquema de identidad y organización

- [x] T013 · Migración, modelo y semilla de la sede
      Archivos: `database/migrations/<ts>_create_sites_table.php` · `app/Models/Site.php` · `database/seeders/SiteSeeder.php`
      Columnas: `name` obligatorio, `city` obligatorio, `active` por omisión verdadero.
      Verifica: `php artisan migrate:fresh --seed` deja exactamente una fila en `sites`.

- [x] T014 [US01] · Migración y modelo `users` adaptados a usuario sin correo
      Archivos: `database/migrations/<ts>_update_users_table.php` · `app/Models/User.php` · `database/factories/UserFactory.php`
      Añade `site_id`, `member_id` (nulo), `username` **único por sede** y sin espacios, `must_change_password`, `is_pastor`, `active`, `last_login_at`; retira la obligatoriedad de `email`.
      Verifica: `php artisan migrate:fresh` corre limpio · `php artisan test --filter=UserFactoryTest`

- [x] T015 [US03] · Migración y modelo `committees` con `parent_id` y `depth`
      Archivos: `database/migrations/<ts>_create_committees_table.php` · `app/Models/Committee.php` · `database/factories/CommitteeFactory.php`
      `parent_id` nulo = comité. `depth` (1, 2 o 3) se **calcula al guardar**, nunca se escribe a mano. `name` único por sede y por padre; `slug` único por sede; `active` y `position`. Relaciones `parent()`, `children()` y `descendants()`; métodos `isTopLevel()` y `hasChildren()`. **Tres niveles de profundidad** (RF-011, decisión del 2026-10-24): un `parent_id` que apunte a un nodo de `depth` 3 es inválido.
      Verifica: `php artisan test --filter=CommitteeHierarchyTest` — un comité con dos hijos, un nieto, un nodo sin hijos, `depth` correcto en los tres niveles, y un **cuarto** nivel rechazado (CL-17).

- [x] T016 [US02] · Migración y modelo `committee_user` con vigencia
      Archivos: `database/migrations/<ts>_create_committee_user_table.php` · `app/Models/CommitteeUser.php` · `database/factories/CommitteeUserFactory.php`
      `user_id`, `committee_id`, `role` (`president` | `secretary` | `treasurer` | `leader`), `started_at`, `ended_at` (nulo = vigente). **Un único titular vigente por nodo y por cargo** (RF-013): no puede haber dos filas con el mismo `committee_id`, el mismo `role` y `ended_at` nulo. `president`, `secretary` y `treasurer` solo si `depth` es 1; `leader` solo si es 2 o 3. **El ámbito lo da el nodo, no el cargo**: secretario y tesorero ven lo mismo que su presidente y se diferencian en lo que pueden hacer.
      Verifica: `php artisan test --filter=CommitteeUserTest`

- [x] T017 [US02] · Prueba: dos cargos vigentes en una persona, y un relevo que cierra sin borrar
      Archivos: `tests/Feature/Committees/CommitteeUserAssignmentTest.php`
      Cubre CL-05 y RN-09.
      Verifica: `php artisan test --filter=CommitteeUserAssignmentTest`

### 2.2 · El filtro de alcance — la pieza crítica

- [x] T018 · Rasgo `BelongsToSite` con filtro automático de sede
      Archivos: `app/Models/Concerns/BelongsToSite.php`
      Verifica: `php artisan test --filter=SiteScopeTest` — un modelo de la sede 2 no aparece consultando desde la sede 1.

- [x] T179 · `SubtreeResolver`: el sub-árbol de un nodo en una sola consulta
      Archivos: `app/Support/Committees/SubtreeResolver.php`
      Bloquea a T019 y por eso va antes. Devuelve los identificadores del nodo y de todos sus descendientes **sin recursión ni CTE**: `depth` está acotado a 3 por RF-011, así que basta comparar `parent_id` y el `parent_id` del padre. Es lo que mantiene el techo de cinco consultas por pantalla (D-16).
      Verifica: `php artisan test --filter=SubtreeResolverTest`

- [x] T180 · Prueba: el sub-árbol en los tres niveles, y el conteo de consultas
      Archivos: `tests/Unit/Committees/SubtreeResolverTest.php`
      Verifica: `php artisan test --filter=SubtreeResolverTest` — el sub-árbol de un comité trae sus sub-comités y sus equipos; el de un sub-comité, sus equipos; el de un equipo, solo él. **Y lo resuelve en una consulta**, verificado con el registro de consultas.

- [x] T019 [US04] · `CurrentScope`: quién soy y qué nodos del árbol puedo ver
      Archivos: `app/Support/Scope/CurrentScope.php`
      **Cargo activo, no unión** (corrección C-01, RF-007): pastor → todos los comités de la sede · un solo cargo vigente → ese es el activo, sin preguntar · dos o más cargos → el activo es el guardado en la sesión, y si no hay ninguno el sistema pide escoger antes de mostrar información. **El ámbito de un nodo es el nodo y todo su sub-árbol** (tres niveles, 2026-10-24): presidente de X → X, sus sub-comités y sus equipos · líder de Y → Y y sus equipos. Usa `Support/Committees/SubtreeResolver` (T179): **una sola consulta, sin recursión**, porque `depth` está acotado a 3.
      Verifica: `php artisan test --filter=CurrentScopeTest`

- [x] T020 [US04] · Prueba: los cinco casos de resolución de ámbito
      Archivos: `tests/Unit/Scope/CurrentScopeTest.php`
      Verifica: `php artisan test --filter=CurrentScopeTest` — pastor, presidente, líder, dos cargos con uno activo, y dos cargos sin escoger (CL-05). **Ningún caso devuelve la unión de dos ámbitos.**

- [x] T021 [US04] · Middleware `ResolveScope` registrado en el grupo web
      Archivos: `app/Http/Middleware/ResolveScope.php` · `bootstrap/app.php`
      Resuelve el ámbito una sola vez por petición.
      Verifica: `php artisan test --filter=ResolveScopeTest` · el contador de consultas del ámbito es 1 por petición, no una por consulta.

- [x] T022 [US04] · Rasgo `BelongsToCommitteeScope` con filtro automático de sub-árbol
      Archivos: `app/Models/Concerns/BelongsToCommitteeScope.php`
      El global scope compara contra el **conjunto de descendientes** del nodo activo, no contra `parent_id` (tres niveles, 2026-10-24).
      Verifica: `php artisan test --filter=CommitteeScopeTest`

- [x] T023 [US04] · Prueba de aislamiento en los tres niveles, cero filtros escritos a mano
      Archivos: `tests/Feature/Scope/CommitteeIsolationTest.php`
      Casos obligatorios de la ampliación (RG-14): un **líder de equipo no ve a su sub-comité hermano**; un **presidente sí ve a sus nietos**; un líder de sub-comité ve sus equipos y nada de otra rama.
      Verifica: `php artisan test --filter=CommitteeIsolationTest` — ninguna consulta de la prueba escribe `where committee_id`.

- [x] T024 [US04] · `ScopeCoverageTest`: recorre los modelos y falla si falta el rasgo
      Archivos: `tests/Architecture/ScopeCoverageTest.php`
      Lista blanca de **una sola** tabla: `activity_log`, la del paquete de bitácora, que no lleva `site_id` (vacío V-01, R-05). Cualquier otra entrada exige justificación en `research.md`.
      Verifica: `php artisan test --filter=ScopeCoverageTest` en verde · se crea a propósito un modelo con `site_id` sin el rasgo y la prueba se pone roja. **Esta comprobación negativa es obligatoria: una red que no se probó no es una red.**

- [x] T025 · `QueryBudgetTest`: cuenta consultas por pantalla y falla pasando de cinco
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Cuentan las consultas propias de la pantalla, middleware incluido; las tres fijas de Laravel se miden aparte (constitución 2.3.1). 3 consultas como máximo para el bloque compartido y 2 para la pantalla. Las propiedades diferidas se miden en su propia petición parcial.
      Verifica: `php artisan test --filter=QueryBudgetTest` · una pantalla con una consulta N+1 introducida a propósito pone la prueba roja.

- [x] T165 · `NoRawQueriesTest`: prohíbe `DB::` y SQL crudo sobre tablas con ámbito
      Archivos: `tests/Architecture/NoRawQueriesTest.php`
      Puerta de calidad 6, automatizada. El plan prometió esta tercera prueba de arquitectura; el documento de entrada solo tenía el barrido manual de T149.
      Verifica: `php artisan test --filter=NoRawQueriesTest` · se agrega a propósito un `DB::table('members')` y la prueba se pone roja.

- [x] T028 [US04] · Propiedades compartidas: el cargo activo y el ámbito siempre, el resto diferido
      Archivos: `app/Http/Middleware/HandleInertiaRequests.php`
      Según `contracts/shared-props.md`: `auth.user`, `auth.roles` (solo cargos vigentes), `scope.activeRole`, `scope.label`, `scope.mustChoose`, `scope.canSwitch` y `flash` viajan en **toda** respuesta. `scopeHidden` y `pendingCount` son **propiedades diferidas** de Inertia: se resuelven en su propia petición parcial, solo donde se muestran (la cinta de alcance, T099, y la pantalla de inicio, T084, T085). **`scopeHidden` lleva solo cantidades, nunca nombres ni identificadores** (RF-009).
      Verifica: `php artisan test --filter=SharedPropsTest` — las siete llaves permanentes viajan en toda respuesta autenticada y `scopeHidden` trae números, no listas · `php artisan test --filter=QueryBudgetTest` prueba que el bloque compartido cuesta **a lo sumo dos consultas** (la persona; sus cargos vigentes con su comité) y que `scopeHidden` y `pendingCount` se miden aparte, como propiedad diferida, no dentro de la primera carga.

- [x] T163 [US04] · Cargo activo: selector visible y cambio de cargo
      Archivos: `app/Http/Controllers/RoleSwitchController.php` · `routes/web.php` (`POST /role/switch`) · `resources/js/components/domain/RoleSwitcher.tsx` · `resources/js/layouts/AppLayout.tsx`
      Corrección C-01. Valida que el cargo pertenezca a la persona y esté vigente, lo guarda en la sesión y regresa a la pantalla anterior. Un `roleId` ajeno o cerrado responde «no disponible» sin revelar ningún dato.
      Verifica: `php artisan test --filter=RoleSwitchTest` — un `roleId` de otra persona no cambia nada · a 360 px el selector se alcanza con el pulgar y toda pantalla muestra con qué cargo se está actuando.

- [x] T164 [US04] · Prueba: el cargo activo aplica un solo ámbito, nunca la unión
      Archivos: `tests/Feature/Scope/ActiveRoleTest.php`
      CL-05 con la regla corregida. Persona con dos cargos, actuando con el cargo A: ninguna pantalla, ningún acceso directo por identificador y ninguna descarga de reporte devuelve algo del ámbito B.
      Verifica: `php artisan test --filter=ActiveRoleTest`

### 2.3 · Esquema compartido de decisiones y cronograma

Estas cuatro tablas las necesita más de una historia, así que son cimiento y no pertenecen a
ninguna fase de historia: `approvals` la usan US02, US06, US07, US14, US16, US17 y US19;
`activities`, US05, US06, US08, US09 y US17. **No llevan etiqueta de historia**, como corresponde
al cimiento: la trazabilidad vive en la tabla de cobertura por historia. Van después del bloque
2.2 porque cargan los rasgos del filtro de alcance y porque `ScopeCoverageTest` tiene que seguir
en verde con cada modelo nuevo. Son tareas de esquema, sin pantalla: el hito 1 no cambia de
contenido por tenerlas.

Aquí se mueve **solo el esquema**. Las reglas que gobiernan estas tablas se quedan en sus
historias, porque son el motor del hito 2 y adelantarlas cargaría la semana más frágil (RG-04):
`ApprovalFlow` (T058, T059) en la Phase 8 y `ActivityStatus` (T166, T167) en la Phase 12. T056, el
detalle del recálculo del avance, también se queda en US09: T055 trae su propia verificación.

- [x] T053 · Migración y modelo `annual_plans`
      Archivos: `database/migrations/<ts>_create_annual_plans_table.php` · `app/Models/AnnualPlan.php` · `database/factories/AnnualPlanFactory.php`
      `year` único por sede; `approved_by_user_id` y `approved_at` nulos = plan sin aprobar.
      Verifica: `php artisan test --filter=AnnualPlanTest` — un plan por año y por sede, no dos.

- [x] T054 · Migración y modelo `activities`
      Archivos: `database/migrations/<ts>_create_activities_table.php` · `app/Models/Activity.php` · `database/factories/ActivityFactory.php`
      Con `BelongsToSite` y `BelongsToCommitteeScope`. `status`: `pending` | `approved` | `in_progress` | `completed` | `rejected` (RF-026). `progress` 0..100 calculado. `rejection_reason` obligatorio si `status` = `rejected`.
      Verifica: `php artisan test --filter=ScopeCoverageTest` sigue en verde con el modelo nuevo · `php artisan test --filter=ActivityModelTest`

- [x] T055 · Migración, modelo y recálculo de `activity_tasks`
      Archivos: `database/migrations/<ts>_create_activity_tasks_table.php` · `app/Models/ActivityTask.php` · `app/Models/Activity.php`
      `progress = redondear(tareas cumplidas / tareas totales × 100)`, y **0 cuando no hay tareas**. Nadie lo escribe a mano (RF-027).
      Verifica: `php artisan test --filter=ActivityProgressTest`

- [x] T057 · Migración y modelo `approvals` polimórfica
      Archivos: `database/migrations/<ts>_create_approvals_table.php` · `app/Models/Approval.php` · `database/factories/ApprovalFactory.php`
      Una sola bandeja para los cinco tipos: `activity`, `expense`, `task`, `date_change`, `new_member` (RF-015, RF-019). `level` (`president` | `pastor`), `status` (`pending` | `approved` | `rejected`), `amount` obligatorio si `type` = `expense`, `reason` **obligatorio si `status` = `rejected`**, `transferred_from_user_id` y `transferred_at` para el traspaso. La combinación `type` = `expense` con `level` = `pastor` **no existe**: `ApprovalFlow` (T058) es el único que crea filas y el modelo la rechaza al guardar (data-model.md §5, RF-018). **La regla no baja a la migración**: es política de aprobación, no esquema, y es la más propensa a cambiar.
      Verifica: `php artisan test --filter=ApprovalModelTest` — guardar el par `expense` + `pastor` lanza excepción.

**Checkpoint**: cimiento listo. El filtro de alcance existe, está probado y tiene red de
seguridad, y el esquema compartido ya está en pie. A partir de aquí **ninguna historia espera una
tabla que todavía no exista**: lo único que sigue ejecutándose fuera de su fase son T099 y T151
(US04) y T152 (US20), y cada una lo dice en su propio texto.

---

## Phase 3: User Story 1 - US01 · Entrar al sistema por primera vez (Priority: P1) 🎯 MVP

**Goal**: que las diecisiete personas con cargo entren con un usuario sencillo y cambien su clave la
primera vez, sin depender de tener correo electrónico. **Hito 1.**

**Independent Test**: crear una cuenta con clave temporal e intentar entrar. Entrega valor porque
habilita a las diecisiete personas con una clave propia.

- [x] T029 [US01] · Entrada con usuario asignado en vez de correo
      Archivos: `app/Http/Controllers/Auth/AuthenticatedSessionController.php` · `app/Http/Requests/Auth/LoginRequest.php` · `resources/js/pages/Auth/Login.tsx` · `routes/auth.php` (ruta `/login`)
      Verifica: `php artisan test --filter=LoginTest` · revisada a 360 px, los campos y el botón por encima del teclado.

- [x] T030 [P] [US01] · Retirar el registro público y la recuperación por correo
      Archivos: `routes/auth.php` · eliminar `resources/js/pages/Auth/Register.tsx`, `ForgotPassword.tsx`, `ResetPassword.tsx`, `VerifyEmail.tsx`
      El sistema no envía nada hacia afuera (S-08): un flujo de recuperación por correo sería una puerta que no funciona.
      Verifica: `php artisan route:list | grep -iE "regist|password.request"` no devuelve nada · `curl -I .../register` responde 404.

- [x] T031 [US01] · Cambio obligatorio de clave en el primer ingreso
      Archivos: `app/Http/Middleware/EnsurePasswordChanged.php` · `app/Http/Controllers/Auth/ChangePasswordController.php` · `resources/js/pages/Auth/ChangePassword.tsx` · ruta `/password/change`
      Verifica: `php artisan test --filter=FirstLoginTest` — con `must_change_password` en verdadero, **ninguna** otra ruta responde con información · revisada a 360 px.

- [x] T032 [US01] · Bloqueo de quince minutos tras cinco intentos
      Archivos: `app/Http/Requests/Auth/LoginRequest.php` · `lang/es/auth.php`
      Por usuario y por dirección de origen. El mensaje indica pedirle al pastor el restablecimiento (RF-004).
      Verifica: `php artisan test --filter=LoginLockoutTest` — el sexto intento responde con el bloqueo y el texto nombra al pastor.

- [x] T033 [US01] · Prueba: los cuatro escenarios de HU-01
      Archivos: `tests/Feature/Auth/LoginTest.php` · `tests/Feature/Auth/FirstLoginTest.php` · `tests/Feature/Auth/LoginLockoutTest.php`
      Verifica: `php artisan test tests/Feature/Auth` — primer ingreso, ingreso posterior, bloqueo y clave restablecida.

**Checkpoint**: US01 funciona por separado. Las diecisiete personas pueden entrar.

---

## Phase 4: User Story 2 - US02 · Administrar las cuentas y los cargos (Priority: P1)

**Goal**: que el pastor cree, modifique y retire cuentas, para que nadie conserve acceso a
información que ya no le corresponde. **Hito 1.**

**Independent Test**: crear un cargo, asignarlo, retirarlo, y comprobar que la persona retirada ya
no entra mientras su nombre permanece en el historial.

- [x] T034 [US02] · Policy del pastor y compuerta `is-pastor`
      Archivos: `app/Policies/UserPolicy.php` · `app/Providers/AppServiceProvider.php`
      Verifica: `php artisan test --filter=UserPolicyTest` — un presidente no crea, no modifica y no retira cuentas.

- [x] T035 [US02] · Pantalla y controlador de usuarios: crear con clave temporal, modificar, retirar
      Archivos: `app/Http/Controllers/UserController.php` · `app/Http/Requests/StoreUserRequest.php`, `UpdateUserRequest.php` · `resources/js/pages/Users/Index.tsx`, `Users/Form.tsx` · ruta `/users`
      Verifica: `php artisan test --filter=UserManagementTest` · revisada a 360 px en tarjetas.

- [ ] T170 [US02] · Ficha mínima de miembro al habilitar una cuenta
      Archivos: `database/migrations/<ts>_add_profile_pending_to_members_table.php` · `app/Http/Controllers/UserController.php` · `app/Models/Member.php`
      Vacío V-03 (RF-003). Toda cuenta queda ligada a la única ficha de esa persona por `users.member_id`; si todavía no está registrada, el sistema le crea la ficha **con nombre y teléfono** y la marca con `profile_pending` en verdadero. Una persona tiene una sola ficha, tenga cargo o no.
      Verifica: `php artisan test --filter=MinimalProfileTest`

- [ ] T171 [US02] · Prueba: la ficha mínima, su marca y el conteo de pendientes
      Archivos: `tests/Feature/Users/MinimalProfileTest.php`
      CL-16: la ficha pendiente aparece en listas y reportes señalada como incompleta, y el sistema indica cuántas faltan por completar.
      Verifica: `php artisan test --filter=MinimalProfileTest` — crear una cuenta para alguien nuevo deja una ficha con `profile_pending`; completarla la quita del conteo.

- [x] T036 [US02] · Asignación de cargos con vigencia y relevo
      Archivos: `app/Http/Controllers/CommitteeUserController.php` · `resources/js/components/domain/RoleAssigner.tsx`
      El relevo cierra `ended_at` y abre una fila nueva; nunca borra (RN-09).
      Verifica: `php artisan test --filter=RoleHandoverTest` — tras el relevo quedan dos filas y la historia conserva el nombre anterior.

- [x] T037 [P] [US02] · Restablecer clave, volviendo a exigir el cambio
      Archivos: `app/Http/Controllers/UserController.php` · `resources/js/pages/Users/Index.tsx`
      Verifica: `php artisan test --filter=PasswordResetByPastorTest` — solo el pastor; la persona vuelve a `/password/change` al entrar (RF-005).

- [x] T038 [US02] · Retiro inmediato del acceso, conservando el nombre en la historia
      Archivos: `app/Models/User.php` · `app/Http/Middleware/EnsureActiveUser.php`
      Nunca se borra una cuenta: `active` en falso (RF-006, RN-09).
      Verifica: `php artisan test --filter=RevokeAccessTest` — la sesión vigente se corta en la petición siguiente y el nombre sigue apareciendo en lo que aprobó.

- [x] T061 [US02] · Traspaso de pendientes al reemplazo
      Archivos: `app/Support/Approvals/TransferPending.php` · `app/Http/Controllers/CommitteeUserController.php`
      La tabla `approvals` (T057) ya existe: se construye en la Phase 2, bloque 2.3 (RF-023, CL-06).
      Verifica: `php artisan test --filter=PendingTransferTest` — al relevar, los pendientes quedan a nombre del nuevo responsable y `transferred_from_user_id` guarda de quién venían, con la fecha.

- [ ] T178 [US02] · Policies de secretario y tesorero
      Archivos: `app/Policies/MinutePolicy.php` · `app/Policies/BudgetMovementPolicy.php` · `app/Providers/AppServiceProvider.php`
      Los cargos nuevos de la ampliación del 2026-10-24. **No amplían el ámbito**: ven exactamente lo que ve su presidente —su comité y todo su sub-árbol— y se diferencian en lo que pueden hacer (D-20). El secretario redacta actas; el tesorero registra movimientos y sube soportes.
      Verifica: `php artisan test --filter=RolePermissionsTest`

- [ ] T186 [US02] · Prueba: secretario y tesorero, mismo ámbito y distinto permiso
      Archivos: `tests/Feature/Users/RolePermissionsTest.php`
      Verifica: `php artisan test --filter=RolePermissionsTest` — el secretario de un comité ve lo mismo que su presidente y **no** puede cerrar un acta ni asignar presupuesto; el tesorero registra movimientos y **no** puede redactar actas; ninguno de los dos ve nada de otra rama.

- [ ] T039 [US02] · Prueba: los cuatro escenarios de HU-02
      Archivos: `tests/Feature/Users/UserManagementTest.php` · `tests/Feature/Users/RoleHandoverTest.php` · `tests/Feature/Users/RevokeAccessTest.php`
      Incluye asignar dos cargos a la misma persona: **la persona escoge con cuál actúa y ve un ámbito a la vez**, con el cargo activo indicado en la pantalla (corrección C-01).
      Verifica: `php artisan test tests/Feature/Users`

**Checkpoint**: US02 funciona por separado. El pastor administra el acceso de toda la congregación.

---

## Phase 5: User Story 3 - US03 · Ver la estructura de comités (Priority: P1)

**Goal**: que cada persona con cargo entienda de quién depende cada cosa y a quién acudir.
**Hito 1.**

**Independent Test**: cargar los seis comités y sus diez sub-comités y abrir la sección con cada uno
de los tres cargos.

- [x] T040 [US03] · Policy de comités
      Archivos: `app/Policies/CommitteePolicy.php`
      Verifica: `php artisan test --filter=CommitteePolicyTest` — `view` cierto para el propio y sus hijos, falso para el ajeno.

- [x] T041 [US03] · Árbol de comités
      Archivos: `app/Http/Controllers/CommitteeController.php` · `resources/js/pages/Committees/Index.tsx` · `resources/js/components/domain/CommitteeTree.tsx` · ruta `/committees`
      Los ajenos aparecen como no disponibles, sin un solo dato suyo.
      **Revisada a ancho de celular por el autor y medida el 2026-10-26: sin desplazamiento horizontal.** Los comités ajenos no se listan uno por uno: la pantalla dice cuántos hay, con `scopeHidden`, como fija el contrato. A un líder le dice quién revisa lo que propone (HU-03 esc. 4), también si es líder de un equipo. El componente quedó en `components/committees/committee-tree.tsx`, con la convención de nombres del proyecto.
      Verifica: `php artisan test --filter=CommitteeIndexTest` · a 360 px el árbol se recorre sin desplazamiento horizontal.

- [ ] T042 [US03] · Ficha de comité con hijos, responsable y miembros
      Archivos: `app/Http/Controllers/CommitteeController.php` · `resources/js/pages/Committees/Show.tsx` · ruta `/committees/{committee}`
      Si no tiene sub-comités lo dice expresamente y aclara que el presidente coordina directamente (CL-01, RF-012). **No muestra una sección vacía.**
      **Hecha salvo los miembros**, que esperan la tabla de miembros, **y la revisión a 360 px.** Muestra presidente, secretario y tesorero (RF-013). Para caber en cinco consultas, el comité y sus hijos salen en una sola consulta en vez de buscarlo por la ruta; pasa igual por el filtro del cargo.
      Verifica: `php artisan test --filter=CommitteeShowTest` · a 360 px.

- [x] T043 [US03] · Prueba: los cuatro escenarios de HU-03 por los tres cargos, más CL-01
      Archivos: `tests/Feature/Committees/CommitteeVisibilityTest.php`
      Verifica: `php artisan test --filter=CommitteeVisibilityTest` — el líder ve su sub-comité y el nombre de su presidente, y nada más.
      El conteo de miembros por nodo (escenario 1) está escrito como pendiente (`todo`) hasta que exista la tabla de miembros.

**Checkpoint**: US03 funciona por separado.

---

## Phase 6: User Story 4 - US04 · Cada comité ve únicamente lo suyo (Priority: P1)

**Goal**: la certeza de que la información de un comité no la ve otro. Es la promesa que la
congregación aprobó en reunión y el principio no negociable I. **Hito 1.**

**Independent Test**: intentar, con cada cargo, consultar información de otro comité por todas las
vías disponibles, y comprobar que ninguna devuelve datos ajenos.

> El filtro en sí vive en la Phase 2 (T018–T025, T163–T165) porque bloquea todo lo demás. Esta fase
> construye la **prueba** de la promesa y su cara visible. T151 cierra la historia en la Phase 23,
> cuando exista la última ruta con Inertia.

- [x] T026 [US04] · Respuesta estándar de «fuera de su alcance», sin revelar un solo dato
      Archivos: `bootstrap/app.php` · `resources/js/pages/Errors/OutOfScope.tsx` · `app/Models/User.php`. No hizo falta una excepción propia: una sola regla en `bootstrap/app.php` convierte todo 403 y 404 en la misma página. Al hacerla apareció el hueco que corrige D-21.
      El texto explica la situación en una frase y dice a quién solicitar la información (RF-008).
      Verifica: `php artisan test --filter=OutOfScopeResponseTest` — la respuesta no contiene nombres, montos ni nombres de comité ajenos · revisada a 360 px.

- [ ] T027 [US04] · Prueba: abrir por identificador directo lo ajeno responde el mensaje y no filtra nada
      Archivos: `tests/Feature/Scope/DirectAccessTest.php`
      **Hecha para comité, actividad y aprobación**, con rutas propias de la prueba. Miembro, reunión y presupuesto están escritas como pendientes (`todo`) hasta que existan sus tablas.
      Verifica: `php artisan test --filter=DirectAccessTest` — comité, miembro, actividad, aprobación, reunión y presupuesto ajenos, los seis.

- [ ] T099 [US04] · Aviso de cuánta información queda fuera del alcance del cargo
      Archivos: `resources/js/components/domain/ScopeBanner.tsx` · `app/Http/Middleware/HandleInertiaRequests.php`
      RF-009: «Hay N hermanos de la congregación que no aparecen aquí porque pertenecen a otros comités.» **N lo calcula el servidor**: miembros de la sede menos miembros del ámbito del cargo activo, nunca un número escrito en la pantalla (con la semilla de T123, a un líder con 32 miembros le salen 108). **El número, nunca los nombres.** `scopeHidden` viaja como **propiedad diferida** (T028): la cinta la pide en su propia petición parcial, no en toda respuesta. *Se ejecuta cuando exista la lista de miembros (T096).*
      Verifica: `php artisan test --filter=ScopeBannerTest` — el número coincide con esa resta, calculada en la propia prueba, y la respuesta no trae un solo nombre ajeno · a 360 px.

- [ ] T151 [US04] · Prueba de aislamiento por pantalla: una por cada ruta, sin excepción
      Archivos: `tests/Feature/Scope/ScreenIsolationTest.php`
      Puerta de calidad 3. Es la prueba que el cliente pediría ver. **La lista la arma la prueba recorriendo `Route::getRoutes()`**: se queda con las rutas `GET` que responden con Inertia y falla si alguna no tiene caso de aislamiento. La fuente de verdad es la tabla de rutas, un hecho del servidor, no `resources/js/pages/`, que además contiene formularios sin ruta propia; y **ningún número escrito a mano**. Lista blanca justificada dentro de la prueba, con el motivo al lado: `/login` y `/password/change`, que no reciben nada del dominio. *Se ejecuta en la Phase 23, cuando exista la última ruta.*
      Verifica: `php artisan test --filter=ScreenIsolationTest` — un cargo ajeno no obtiene nada de ninguna ruta ajena, ni por lista ni por identificador directo · se agrega a propósito una ruta nueva sin caso y la prueba se pone roja.

**Checkpoint**: US04 demostrable. La promesa de la congregación tiene prueba.

---

## Phase 7: User Story 20 - US20 · Usar el sistema desde el celular (Priority: P1)

**Goal**: que todo funcione bien en una pantalla pequeña, para no depender de un computador.
**Hito 1.** Estas tareas fijan el molde de todas las pantallas que vienen después.

**Independent Test**: recorrer las pantallas ya construidas a 360 px y comprobar instalación en la
pantalla de inicio del teléfono y tiempo de apertura.

- [x] T044 [US20] · Diseño base: barra, menú con contador, cinta de alcance
      Archivos: `resources/js/layouts/AppLayout.tsx` · `resources/js/components/ui/` · `resources/css/app.css`
      Mínimos del principio II, constitución 2.1.0: zona pulsable de **48x48 px**, texto de contenido de **18 px**, contraste **AA**, **cero desplazamiento horizontal**. El público son adultos mayores con poca experiencia en aplicaciones.
      **Medida el 2026-10-26 a 375 px: sin desplazamiento horizontal.** La fila más ancha de todas las pantallas, la barra de arriba, necesita 321 px: cabe a 360, y por debajo de 360 el nombre de la aplicación se esconde para que quepa también a 320. Sigue el prototipo: el menú va abajo, siempre a la vista, y trae solo las secciones que ya existen; Cronograma y Dinero se suman con sus módulos. El contador de RF-055 va sobre «Inicio» y cuenta solo lo que espera la decisión del cargo activo. El molde quedó en `layouts/main-layout.tsx`, para no confundirlo con el `app-layout.tsx` del kit, y sus piezas en `components/layout/`. La raíz y «Salir» llevan a la pantalla de entrada; se retiró la página de bienvenida del kit.
      Verifica: a 360 px sin barra horizontal en ninguna de las pantallas existentes · `npm run build` sin advertencias.

- [ ] T045 [P] [US20] · Componente tabla-tarjeta
      Archivos: `resources/js/components/ui/CardTable.tsx`
      Verifica: a 360 px una tabla de seis columnas se presenta en tarjetas legibles; a 1280 px como tabla. Ninguna tabla se corta.
      **Se construye con la primera tabla de verdad**, la de miembros (T096): hoy ninguna pantalla tiene una, y un componente sin quien lo use es una capa «por si acaso» (CLAUDE.md). Las cuentas y los comités ya se muestran en tarjetas.

- [ ] T046 [P] [US20] · Aplicación instalable en el celular
      Archivos: `vite.config.ts` · `public/manifest.webmanifest` · `public/icons/`
      Verifica: Chrome en el celular ofrece «Instalar» · Lighthouse marca la aplicación como instalable.
      **Hecha sin dependencias nuevas; falta verificarla en el servidor.** El navegador solo instala con https, y el computador de desarrollo usa http, así que «Instalar» se comprueba después de T007. Manifiesto, iconos con el degradado del encabezado, y un trabajador de fondo que solo muestra una página amable sin señal: no guarda pantallas ni datos de la congregación. `InstallableAppTest` vigila el manifiesto, los iconos, las copias de la paleta y esa promesa. No hizo falta tocar `vite.config.ts`.

- [x] T047 [US20] · Aviso de versión nueva dentro de la aplicación
      Archivos: `resources/js/lib/update-check.ts` · `resources/js/layouts/AppLayout.tsx`
      Mitiga RG-06: la PWA sirviendo una versión vieja después de un despliegue.
      **Verificada el 2026-10-26 simulando un despliegue**: con la pantalla abierta se cambió el archivo de compilación, apareció «Hay una versión nueva» y al pulsar «Actualizar» recargó y el aviso no volvió. Compara la huella con que se sirvió la pantalla con la que publica `/version`; `UpdateCheckTest` vigila que sean la misma.
      Verifica: tras un despliegue, una pestaña ya abierta muestra «hay una versión nueva» y recarga al confirmar.

- [ ] T048 [US20] · Medición de apertura en red lenta
      Archivos: `docs/pruebas-rendimiento.md`
      Verifica: Lighthouse móvil con red 3G simulada, primera pintura con contenido **por debajo de 3 segundos**. El resultado queda anotado con fecha en el documento.
      **Se mide en el servidor (después de T007)**: en el computador de desarrollo los archivos vienen del servidor de Vite sin comprimir, y una medición ahí no dice nada del celular de un hermano.

- [ ] T152 [US20] · Afinar la aplicación instalada
      Archivos: `vite.config.ts` · `public/manifest.webmanifest` · `resources/js/lib/update-check.ts`
      Iconos en todos los tamaños, pantalla de arranque, y lectura de lo ya cargado sin conexión. *Se ejecuta en la Phase 23.*
      Verifica: instalada en un celular real, abre desde el icono, muestra la pantalla de arranque de la iglesia, y sin señal informa en lenguaje claro en vez de mostrar una página de error del navegador.

**Checkpoint**: US20 demostrable. El hito 1 puede cerrarse (T049–T052, Phase 23).

---

## Phase 8: User Story 6 - US06 · Revisar lo que proponen mis sub-comités (Priority: P1)

**Goal**: que el presidente revise y resuelva lo que proponen sus líderes, para responder por lo que
hace su comité. **Hito 2.**

**Independent Test**: enviar los cuatro tipos de solicitud desde un líder y resolverlos como
presidente, comprobando qué queda cerrado y qué escala al pastor.

> Esta fase va **antes** que US05 porque construye `ApprovalFlow` (T058), el escalamiento que
> proponer una actividad consume. La tabla `approvals` (T057) ya existe: se levanta en la Phase 2,
> bloque 2.3. La prueba independiente arma las solicitudes con sus factories, así que no espera la
> pantalla de propuesta.

- [ ] T058 [US06] · `ApprovalFlow`: la cadena de escalamiento en un solo lugar
      Archivos: `app/Support/Approvals/ApprovalFlow.php`
      Las reglas de `data-model.md` §5 (RF-016 a RF-018, RN-02, RN-03, RN-04), **incluida la corrección V-04**: el gasto del propio comité del presidente **no crea solicitud** — lo autoriza él y se registra el movimiento con su nombre y la fecha. Es el **único** que crea filas en `approvals`, y nunca el par `type` = `expense` con `level` = `pastor` (T057).
      Verifica: `php artisan test --filter=ApprovalFlowTest`

- [ ] T059 [US06] · Prueba: las reglas de escalamiento, incluida la que no cierra
      Archivos: `tests/Unit/Approvals/ApprovalFlowTest.php`
      Verifica: `php artisan test --filter=ApprovalFlowTest` — el presidente aprueba una actividad y la solicitud **sigue pendiente**, ahora en el pastor. Un gasto de un líder, de cualquier monto, lo cierra el presidente (RN-04) y **cierra en ese paso**, sin quedar pendiente del pastor. Un gasto del presidente mismo no genera solicitud (RF-018). Crear una solicitud con `type` = `expense` y `level` = `pastor` falla al guardar, rechazada por el modelo `Approval` (data-model.md §5).

- [x] T060 [US19] · Inmutabilidad de lo resuelto
      Archivos: `app/Models/Approval.php`
      El evento `updating` rechaza el cambio si la solicitud ya está resuelta (RF-021, RF-057, RN-07). Se hace cumplir **en el modelo, no en la interfaz**.
      Verifica: `php artisan test --filter=ApprovalImmutabilityTest` — editar una aprobación resuelta lanza excepción.

- [ ] T067 [US06] · Policy de aprobaciones por nivel
      Archivos: `app/Policies/ApprovalPolicy.php`
      Verifica: `php artisan test --filter=ApprovalPolicyTest` — un presidente no resuelve lo de nivel pastor ni lo de otro comité.

- [ ] T068 [US06] · Bandeja de aprobaciones: por resolver, escaladas e historia
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/pages/Approvals/Index.tsx` · `resources/js/components/domain/ApprovalCard.tsx` · ruta `/approvals`
      Cada tarjeta dice de qué comité viene, quién la pidió, por quién ya pasó, qué monto implica, qué efecto tiene en el cronograma y **desde cuándo espera**.
      Verifica: `php artisan test --filter=ApprovalInboxTest` · a 360 px la tarjeta se lee completa sin abrir nada.

- [ ] T069 [US06] · Aprobar: aplica `ApprovalFlow` y el efecto sobre el objeto apuntado
      Archivos: `app/Http/Controllers/ApprovalController.php` · ruta `POST /approvals/{approval}/approve`
      Verifica: `php artisan test --filter=ApproveTest` — aprobar una actividad la deja aprobada en el cronograma con nombre y fecha.

- [ ] T070 [US06] · Rechazar con motivo obligatorio y visible
      Archivos: `app/Http/Requests/RejectApprovalRequest.php` · `app/Http/Controllers/ApprovalController.php` · ruta `POST /approvals/{approval}/reject`
      Verifica: `php artisan test --filter=RejectTest` — sin motivo no pasa (RF-020); el motivo le aparece a quien propuso.

- [ ] T071 [US06] · Prueba: los cuatro escenarios de HU-06
      Archivos: `tests/Feature/Approvals/PresidentReviewTest.php`
      Verifica: `php artisan test --filter=PresidentReviewTest` — incluye la aprobación que no cierra y escala al pastor.

**Checkpoint**: US06 funciona por separado. El primer nivel de la aprobación en dos niveles.

---

## Phase 9: User Story 5 - US05 · Proponer una actividad nueva (Priority: P1)

**Goal**: proponer una actividad que no estaba en el plan del año, para que quede evaluada y
aprobada antes de comprometer a la iglesia. **Hito 2.**

**Independent Test**: proponer una actividad como líder y como presidente, verificando a quién le
llegó cada una, las validaciones y el aviso de cruce.

> Depende de `activities` (T054), que está en la Phase 2, bloque 2.3, y de `ApprovalFlow` (T058),
> que construye la Phase 8 (US06), inmediatamente anterior. Al llegar aquí las dos ya existen:
> esta fase no espera nada.

- [ ] T062 [US05] · Policy de actividades
      Archivos: `app/Policies/ActivityPolicy.php`
      Verifica: `php artisan test --filter=ActivityPolicyTest` — `view` cierto para cualquier cargo (RF-010), `update` solo para el comité dueño.

- [ ] T063 [US05] · Validación del formulario de propuesta
      Archivos: `app/Http/Requests/StoreActivityRequest.php` · `lang/es/validation.php`
      Título obligatorio; `scheduled_at` **dentro del año en curso y no pasada** (CL-07); mensajes que señalan exactamente qué campo corregir.
      Verifica: `php artisan test --filter=ActivityValidationTest` — título vacío y fecha de otro año se rechazan con el campo nombrado.

- [ ] T064 [US05] · Detector de cruce de fecha, hora y lugar
      Archivos: `app/Support/Activities/ScheduleConflict.php`
      Usa `Activity::forSharedCalendar()`; devuelve qué actividad es y de qué comité (RF-028, CL-08, CE-04). Es un aviso, no un bloqueo.
      Verifica: `php artisan test --filter=ScheduleConflictTest` — advierte el cruce con un comité ajeno **sin revelar su presupuesto**.

- [ ] T065 [US05] · Formulario de propuesta y aviso de a quién le llegó
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Form.tsx` · ruta `POST /activities`
      El aviso nombra a la persona: «Su propuesta le llegó al hermano…» (RF-016, RF-017).
      Verifica: `php artisan test --filter=ProposeActivityTest` · a 360 px el formulario se llena con una mano.

- [ ] T066 [US05] · Prueba: los cinco escenarios de HU-05, con líder y con presidente
      Archivos: `tests/Feature/Activities/ProposeActivityTest.php` · `tests/Feature/Activities/ScheduleConflictTest.php`
      Verifica: `php artisan test --filter="ProposeActivityTest|ScheduleConflictTest"`

**Checkpoint**: US05 funciona por separado.

---

## Phase 10: User Story 7 - US07 · Aprobar lo que sube al pastor (Priority: P1)

**Goal**: que el pastor apruebe o rechace lo nuevo y lo que se sale del plan, sin tener que estar en
cada reunión de comité. **Hito 2.**

**Independent Test**: dejar solicitudes escaladas en la bandeja del pastor y resolverlas una a una y
en bloque, verificando el efecto en el cronograma.

- [ ] T072 [US07] · Resolución en lote del mismo tipo, con una sola confirmación
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/pages/Approvals/Index.tsx` · ruta `POST /approvals/bulk-approve`
      Verifica: `php artisan test --filter=BulkApprovalTest` — tres gastos se resuelven en una acción y quedan tres registros separados en la historia (RF-022).

- [ ] T073 [US07] · Prueba: los cuatro escenarios de HU-07 desde el pastor
      Archivos: `tests/Feature/Approvals/PastorInboxTest.php`
      Verifica: `php artisan test --filter=PastorInboxTest` — la bandeja del pastor trae comité, solicitante, paso anterior, monto y efecto.

**Checkpoint**: US07 funciona por separado. La cadena de aprobación está cerrada.

---

## Phase 11: User Story 8 - US08 · Aprobar el plan del año (Priority: P1)

**Goal**: aprobar de una sola vez el cronograma completo del año, para no autorizar cada actividad
por separado. **Hito 2.**

**Independent Test**: cargar las actividades del año entrante, aprobar el plan en un acto, y
verificar que queda sellado con nombre y fecha.

> La tabla `annual_plans` (T053) ya existe: se levanta en la Phase 2, bloque 2.3, porque
> `activities` lleva `annual_plan_id`. Esta fase construye la aprobación, no el esquema.

- [ ] T074 [US08] · Aprobación del plan anual en un solo acto
      Archivos: `app/Http/Controllers/AnnualPlanController.php` · ruta `POST /annual-plan/{year}/approve`
      Solo el pastor. Deja `approved_by_user_id` y `approved_at`, y marca aprobadas todas sus actividades (RF-025, CE-02).
      Verifica: `php artisan test --filter=AnnualPlanApprovalTest` — **todas** las actividades del plan quedan aprobadas con un solo acto y una sola fila de aprobación; la prueba cuenta las que creó su propio escenario, no un total fijo (con la semilla de T087 son 38).

- [ ] T075 [US08] · Distinguir el plan original de la propuesta posterior
      Archivos: `app/Models/Activity.php` · `resources/js/components/domain/ActivityBadge.tsx`
      `annual_plan_id` nulo = propuesta durante el año (RF-024, RN-03).
      Verifica: `php artisan test --filter=AnnualPlanApprovalTest` · la distinción es visible a 360 px sin pasar el dedo por encima.

- [ ] T076 [US08] · Cambio de año calendario
      Archivos: `app/Http/Controllers/AnnualPlanController.php` · `resources/js/pages/Activities/Index.tsx`
      El plan del año anterior queda consultable; el nuevo empieza sin aprobar (CL-13).
      Verifica: `php artisan test --filter=YearRolloverTest`

- [ ] T077 [US08] · Prueba: los tres escenarios de HU-08 más CL-13
      Archivos: `tests/Feature/Activities/AnnualPlanApprovalTest.php` · `tests/Feature/Activities/YearRolloverTest.php`
      Verifica: `php artisan test --filter="AnnualPlanApprovalTest|YearRolloverTest"`

**Checkpoint**: US08 funciona por separado.

---

## Phase 12: User Story 9 - US09 · Consultar el cronograma del año (Priority: P1)

**Goal**: ver el año completo y cómo va el cumplimiento, para no cruzarse con otros comités.
**Hito 2.** Aquí vive **la única excepción que enumera la constitución** al filtro de alcance
(RF-010, D-15): se construye antes de la pantalla, no después.

**Independent Test**: con un año cargado, revisar la vista anual, los filtros, el recálculo del
cumplimiento y lo que ve un cargo ajeno al comité responsable.

> El esquema del cronograma —`activities` (T054) y `activity_tasks` (T055)— ya está en pie: se
> levanta en la Phase 2, bloque 2.3. Esta fase construye el detalle del recálculo, las
> transiciones de estado, la excepción al filtro y la pantalla.

- [x] T056 [US09] · Prueba: el avance con cero tareas, la mitad, todas, y el redondeo
      Archivos: `tests/Unit/Activities/ProgressCalculationTest.php`
      Verifica: `php artisan test --filter=ProgressCalculationTest` — cero tareas da 0 y no divide por cero.

- [ ] T166 [US09] · Transiciones de estado de una actividad
      Archivos: `app/Support/Activities/ActivityStatus.php` · `app/Models/Activity.php`
      Vacío V-05, regla definida en `research.md` R-04. `pending`→`approved` al aprobar el pastor o el plan anual · `pending`→`rejected` con motivo · `approved`→`in_progress` al cumplirse la primera tarea **o al llegar `scheduled_at`, lo que ocurra primero** · `in_progress`→`completed` al llegar `progress` a 100 · sin tareas, el responsable la marca cumplida. **Sin transiciones hacia atrás.**
      Verifica: `php artisan test --filter=ActivityStatusTest`

- [ ] T167 [US09] · Prueba: las seis transiciones y las que no se permiten
      Archivos: `tests/Unit/Activities/ActivityStatusTest.php`
      Verifica: `php artisan test --filter=ActivityStatusTest` — una actividad cumplida no vuelve a «en curso»; una aprobada cuya fecha llegó aparece «en curso» aunque no tenga tareas cumplidas.

- [ ] T078 [US09] · `Activity::forSharedCalendar()`
      Archivos: `app/Models/Activity.php`
      Retira el filtro de **comité** —el de sede no se retira nunca— y **selecciona a mano** los siete datos que enumera RF-010: título, descripción, fecha, hora, lugar, comité responsable y estado (`title`, `description`, `scheduled_at`, `location`, `status` y el nombre del comité). **Nunca `budget_amount`, nunca tareas.** **El método solo lee**: no se usa como base de un `update`, de un `delete` ni de una descarga, y **no concede autorización** — la policy de `Activity` (T062) sigue negando toda escritura sobre una actividad ajena. Es la única salida del filtro en todo el proyecto, y está **amparada por la constitución v2.0.0**: la enmienda del principio I del 2026-10-18 la enumera con sus datos, su método con nombre propio y su prueba obligatoria (R-11, D-15).
      Verifica: `php artisan test --filter=SharedCalendarTest`

- [ ] T079 [US09] · Prueba obligatoria de la excepción
      Archivos: `tests/Feature/Scope/SharedCalendarTest.php`
      Mitiga RG-03. Es **lista blanca, no lista negra**: comprueba que la carga de una actividad ajena trae **exactamente** los siete datos enumerados y ningún otro, comparando el juego de llaves ordenado de la propiedad, no buscando llaves prohibidas. Una columna nueva en `activities` deja la prueba roja hasta que alguien decida expresamente si se comparte. Incluye el caso de **escritura**: un presidente que ve una actividad ajena en el cronograma intenta modificarla y marcarla cumplida.
      Verifica: `php artisan test --filter=SharedCalendarTest` — un presidente consulta el cronograma, ve la actividad de otro comité y el conjunto de llaves es exactamente el de los siete datos, en la lista y en la ficha (`assertSame` sobre las llaves ordenadas, nunca `assertJsonMissing`) · las dos escrituras sobre la actividad ajena responden «no disponible» (RF-008).

- [ ] T080 [US09] · Pantalla del cronograma: doce meses, lista, resumen y barra de cumplimiento
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Index.tsx` · `resources/js/components/domain/MonthGrid.tsx` · ruta `/activities`
      Distingue cumplidas, en camino, aprobadas sin empezar y esperando aprobación.
      Verifica: `php artisan test --filter=ScheduleIndexTest` · a 360 px los doce meses se recorren en vertical, sin barra horizontal.

- [ ] T081 [US09] · Filtros de comité, sub-comité, mes y estado, con recálculo de los totales
      Archivos: `app/Http/Controllers/ActivityController.php` · `resources/js/pages/Activities/Index.tsx`
      Los filtros viajan en la dirección, de modo que una pantalla filtrada se puede compartir (RF-029, RF-030).
      Verifica: `php artisan test --filter=ScheduleFilterTest` — al filtrar por un comité, el resumen y el porcentaje de cumplimiento cambian con él.

- [ ] T082 [US09] · Ficha de actividad: tareas, rastro de decisiones y marcar cumplida
      Archivos: `app/Http/Controllers/ActivityController.php` · `app/Http/Controllers/ActivityTaskController.php` · `resources/js/pages/Activities/Show.tsx` · ruta `/activities/{activity}`
      La ajena muestra solo lo compartido; `canEdit` lo resuelve el servidor, nunca la pantalla.
      Verifica: `php artisan test --filter=ActivityShowTest` — marcar una tarea recalcula el avance sin que nadie lo escriba · a 360 px.

- [ ] T083 [US09] · Prueba: los cuatro escenarios de HU-09
      Archivos: `tests/Feature/Activities/ScheduleIndexTest.php` · `tests/Feature/Activities/ActivityShowTest.php` · `tests/Feature/Activities/ScheduleFilterTest.php`
      Verifica: `php artisan test tests/Feature/Activities`

**Checkpoint**: US09 funciona por separado. La excepción al filtro está probada.

---

## Phase 13: User Story 17 - US17 · Saber qué tengo pendiente (Priority: P2)

**Goal**: darse cuenta apenas entra de lo que le están esperando. **Hito 2.** Como no hay avisos
externos (S-01), es el único mecanismo por el que alguien se entera de lo que debe resolver.

**Independent Test**: dejar solicitudes pendientes para cada cargo y comprobar qué aparece primero
en la pantalla de inicio y en el contador del menú.

- [ ] T084 [US17] · Pantalla de inicio: primero lo que espera su decisión, y desde cuándo
      Archivos: `app/Http/Controllers/DashboardController.php` · `resources/js/pages/Dashboard/Index.tsx` · ruta `/`
      `kpis` y `pendingApprovals[]` en la primera carga; `myRequests[]`, `committeeProgress[]`, `upcoming[]` y `pendingProfiles` como **propiedades diferidas**, cada una en su petición parcial con su propio presupuesto (RF-054, S-01). Es una de las **dos excepciones declaradas** del presupuesto de cinco consultas (T150), y el reparto se declara aquí, no se improvisa.
      Verifica: `php artisan test --filter=DashboardTest` · a 360 px lo pendiente queda por encima del pliegue, antes de cualquier otra cosa.

- [ ] T085 [US17] · Contador del menú y estado de lo que yo envié
      Archivos: `app/Http/Middleware/HandleInertiaRequests.php` · `resources/js/layouts/AppLayout.tsx` · `resources/js/pages/Dashboard/Index.tsx`
      El líder ve en qué va su solicitud, quién debe resolverla y, si ya se resolvió, el resultado con su motivo (RF-055). `pendingCount` es **propiedad diferida** (T028): el menú la pide aparte, no viaja en toda respuesta.
      Verifica: `php artisan test --filter=PendingCountTest` — **el contador es del cargo activo**, no de la congregación ni de la suma de los cargos · la propiedad diferida se mide con su propio presupuesto.

- [ ] T086 [US17] · Prueba: los tres escenarios de HU-17 por los tres cargos
      Archivos: `tests/Feature/Dashboard/DashboardTest.php`
      Verifica: `php artisan test --filter=DashboardTest`

**Checkpoint**: US17 funciona por separado. El hito 2 puede cerrarse (T087–T089, Phase 23).

---

## Phase 14: User Story 10 - US10 · Registrar y consultar miembros (Priority: P2)

**Goal**: mantener al día la lista de los miembros del ámbito, para que los reportes sean ciertos.
**Hito 3.**

**Independent Test**: registrar, buscar y filtrar miembros con cada cargo, incluidas las
validaciones de edad y de bautismo.

- [ ] T090 [US10] · Migración y modelo `members` con fecha de nacimiento o edad declarada
      Archivos: `database/migrations/<ts>_create_members_table.php` · `app/Models/Member.php` · `database/factories/MemberFactory.php`
      D-09: se guarda `birth_date` (admite nulo) y la edad se calcula. **Se admiten las dos entradas**, porque lo que la congregación tiene escrito en el cuaderno es la edad: `age_reported` (entero, nulo) junto con `age_reported_at` (fecha, nulo), la fecha de la declaración, que es lo que impide que una edad suelta envejezca. `age_reported` exige `age_reported_at`, y al revés. **La edad se calcula de `birth_date` cuando existe y del par `age_reported` + `age_reported_at` cuando no**, y los reportes por rango de edad usan las dos fuentes. Cuando falten las dos, la mayoría de edad confirmada se guarda en `adult_confirmed` (bool) con `adult_confirmed_at` (fecha, nulo, obligatoria si la marca es verdadera), según RF-032. `gender` (`f` | `m`); `marital_status` (`single` | `married` | `widowed` | `separated` | `common_law`); `baptized_at` nulo si no está bautizado. `committee_id` nulo = sin comité (CL-03); si el nodo tiene padre, la persona está en un sub-comité (CL-02, RF-014).
      Verifica: `php artisan test --filter=MemberModelTest` · `ScopeCoverageTest` sigue en verde.

- [ ] T091 [US10] · Prueba: la edad se calcula, de la fecha o de la edad declarada
      Archivos: `tests/Unit/Members/AgeCalculationTest.php`
      Verifica: `php artisan test --filter=AgeCalculationTest` — un miembro nacido ayer hace 18 años tiene 18; el que los cumple mañana tiene 17; el que tiene 40 declarados hace dos años tiene 42, y el que no tiene ninguna de las dos cosas no reporta edad, en vez de reportar cero.

- [ ] T092 [US10] · Policy y validaciones del registro de miembros
      Archivos: `app/Policies/MemberPolicy.php` · `app/Http/Requests/StoreMemberRequest.php`, `UpdateMemberRequest.php`
      RF-032: **menor de dieciocho años no puede ser `married`, `widowed`, `separated` ni `common_law`**. Cuando la edad se conoce —por `birth_date` o por el par de edad declarada— la regla se aplica tal cual; cuando no se conoce ninguna de las dos y se marca un estado civil distinto de `single`, el sistema pide **confirmar que la persona es mayor de dieciocho años**, no la fecha, y la confirmación queda anotada en `adult_confirmed` con su fecha: **nunca se bloquea el registro por un dato que la congregación no tiene** (principio IV). RF-033: `baptized_at` obligatoria cuando `baptized` es verdadero, no anterior a `birth_date` ni posterior a hoy.
      Verifica: `php artisan test --filter=MemberValidationTest`

- [ ] T093 [US10] · Prueba: las reglas de validación, con su explicación
      Archivos: `tests/Feature/Members/MemberValidationTest.php`
      Verifica: `php artisan test --filter=MemberValidationTest` — un menor marcado casado o viudo se rechaza y el mensaje explica por qué, en una frase · un hermano casado sin fecha de nacimiento y sin edad declarada se registra confirmando que es mayor de dieciocho años, y el registro **no** se bloquea.

- [ ] T094 [US10] · Búsqueda que ignora mayúsculas y tildes
      Archivos: `app/Models/Member.php` · `database/migrations/<ts>_set_members_collation.php`
      Verifica: `php artisan test --filter=MemberSearchTest` — «hernandez» encuentra «Hernández», y «NIÑO» encuentra «Niño».

- [ ] T095 [US10] · Filtros combinables con conteo sobre el total
      Archivos: `app/Http/Controllers/MemberController.php` · `resources/js/components/domain/FilterBar.tsx`
      Comité, sub-comité, género, estado civil, bautizado y rango de edad —con las dos fuentes de edad (T090)—; quitar todos de una vez (RF-035).
      Verifica: `php artisan test --filter=MemberFilterTest` — informa «32 de 140» y el conteo respeta el ámbito del cargo activo.

- [ ] T096 [US10] · Lista y formulario de miembros
      Archivos: `app/Http/Controllers/MemberController.php` · `resources/js/pages/Members/Index.tsx`, `Members/Form.tsx` · ruta `/members`
      `committees[]` solo trae los del ámbito, **también para llenar desplegables**. El formulario pide la fecha de nacimiento y, cuando no se sabe, ofrece **«No la sé — anote la edad»**, que guarda la edad declarada con la fecha de la declaración (T090).
      Verifica: `php artisan test --filter=MemberIndexTest` · a 360 px la lista se presenta en tarjetas, no en tabla cortada · la opción de anotar la edad guarda el par completo.

- [ ] T172 [US10] [US13] · Campos de autorización de tratamiento de datos
      Archivos: `database/migrations/<ts>_add_data_consent_to_members_table.php` · `database/migrations/<ts>_add_data_consent_to_sympathizers_table.php` · `app/Models/Member.php` · `app/Models/Sympathizer.php`
      Vacío V-02 (RF-061). `data_consent` (bool), `data_consent_at` (fecha, **obligatoria si `data_consent`**), `data_consent_means` (`signature` | `verbal` | `message`, **obligatorio si `data_consent`**).
      Verifica: `php artisan test --filter=DataConsentTest` · `ScopeCoverageTest` sigue en verde.

- [ ] T173 [US10] [US13] · Aviso de privacidad en los formularios de personas
      Archivos: `resources/js/components/domain/PrivacyNotice.tsx` · `resources/js/pages/Members/Form.tsx` · `resources/js/pages/Sympathizers/Form.tsx`
      RF-062: qué datos se guardan, para qué se usan y a quién dirigirse para consultarlos o corregirlos. El texto lo entrega la congregación (DEP-04).
      Verifica: visible en los dos formularios sin desplazarse · a 360 px · el texto no contiene jerga técnica.

- [ ] T174 [US10] · Conteo de fichas sin autorización y de fichas por completar
      Archivos: `app/Http/Controllers/MemberController.php` · `app/Http/Controllers/SympathizerController.php` · `app/Support/Reports/MemberStats.php`
      `missingConsent` y `pendingProfiles` en los listados y en el reporte de la congregación (CE-12, CL-16). **Conteos del ámbito del cargo activo, nunca nombres ajenos.**
      Verifica: `php artisan test --filter=ConsentCountTest`

- [ ] T175 [US10] · Prueba: autorización, aviso y conteos, en miembros y en simpatizantes
      Archivos: `tests/Feature/Members/DataConsentTest.php` · `tests/Feature/Members/ConsentCountTest.php` · `tests/Feature/Sympathizers/SympathizerConsentTest.php`
      RF-061 cubre las dos fichas, así que la autorización se prueba sobre las dos tablas.
      Verifica: `php artisan test --filter="DataConsentTest|ConsentCountTest|SympathizerConsentTest"` — en miembros y en simpatizantes, marcar la autorización sin fecha o sin medio se rechaza; el conteo de fichas sin autorización es correcto y respeta el ámbito del cargo activo.

- [ ] T097 [US10] · Miembro sin comité y miembro sin sub-comité
      Archivos: `app/Models/Member.php` · `resources/js/pages/Members/Index.tsx`
      El que no pertenece a ningún comité **cuenta en los reportes de la congregación** y se señala como oportunidad, no como error (CL-02, CL-03, CE-06).
      Verifica: `php artisan test --filter=MemberEdgeCasesTest`

- [ ] T098 [US10] · Prueba: todos los escenarios de HU-10, más CL-02, CL-03 y CL-16
      Archivos: `tests/Feature/Members/MemberIndexTest.php` · `tests/Feature/Members/MemberEdgeCasesTest.php` · `tests/Feature/Members/MemberFilterTest.php`
      La cuenta de escenarios sale de HU-10 en el spec, no de un número escrito aquí: si la historia gana un escenario, la prueba gana un caso.
      Verifica: `php artisan test tests/Feature/Members` — un caso por cada escenario de HU-10, sin faltar ninguno, incluido el registro con edad declarada en vez de fecha de nacimiento.

**Checkpoint**: US10 funciona por separado. T099 (Phase 6) ya se puede ejecutar.

---

## Phase 15: User Story 11 - US11 · Consultar reportes y estadísticas (Priority: P2)

**Goal**: obtener en el momento las cifras de la congregación, para responder en las reuniones sin
contar a mano. **Hito 3.**

**Independent Test**: con el padrón cargado, abrir los reportes con cada cargo y comparar la vista
de gráficos con la de tabla.

- [ ] T100 [US11] · Cálculos de las estadísticas de la congregación
      Archivos: `app/Support/Reports/MemberStats.php`
      Total, bautizados con porcentaje, estado civil, género, rangos de edad, distribución por comité, bautismos por año, y cuántos no pertenecen a ningún comité (RF-036). Los rangos de edad usan **las dos fuentes de edad** (T090). **Una consulta de agregación por eje**, no conteo de filas en PHP (R-08).
      Verifica: `php artisan test --filter=MemberStatsTest`

- [ ] T101 [US11] · Prueba: las estadísticas, incluido el grupo vacío
      Archivos: `tests/Unit/Reports/MemberStatsTest.php`
      Verifica: `php artisan test --filter=MemberStatsTest` — con cero miembros devuelve ceros y no una pantalla rota (CL-12); el promedio de edad de un grupo vacío no divide por cero; los miembros con edad declarada entran en los rangos de edad, con precisión de ±1 año, y solo los que no tienen ni fecha ni edad declarada se cuentan aparte y quedan fuera de esos rangos (RG-05).

- [ ] T102 [US11] · Pantalla de reportes con gráficos
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Index.tsx` · `resources/js/components/domain/BarChart.tsx`, `DonutChart.tsx` · ruta `/reports`
      Gráficos en SVG propio: una librería de gráficos exigiría justificación escrita y no la necesita. `filters` y `scopeNotice` viajan en la primera carga; **las cifras de los ocho ejes de RF-036 van en propiedades diferidas**, con su propio presupuesto de cinco consultas por respuesta, y si los ocho no caben se separan en dos propiedades diferidas. Es la segunda de las **dos excepciones declaradas** del presupuesto (T150), porque dos ejes —distribución por comité y bautismos por año— exigen `GROUP BY` propio.
      Verifica: `php artisan test --filter=ReportIndexTest` · a 360 px los gráficos se leen sin girar el teléfono · cada propiedad diferida se mide aparte y ninguna pasa de cinco consultas.

- [ ] T103 [US11] · Vista de tabla equivalente a cada gráfico
      Archivos: `resources/js/pages/Reports/Index.tsx` · `resources/js/components/ui/ChartTableToggle.tsx`
      RF-037: quien no distingue bien los colores o prefiere los números obtiene **exactamente** la misma información.
      Verifica: a 360 px el conmutador funciona y la tabla trae los mismos números que el gráfico, uno por uno.

- [ ] T104 [US11] · Reportes por ámbito, con la aclaración
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Index.tsx`
      El presidente y el líder obtienen los mismos reportes con su gente, y `scopeNotice` aclara que los totales de la congregación los ve únicamente el pastor.
      Verifica: `php artisan test --filter=ReportScopeTest` — los números de un presidente suman su comité, no la congregación.

- [ ] T105 [US11] · Prueba: los cuatro escenarios de HU-11 por los tres cargos
      Archivos: `tests/Feature/Reports/ReportIndexTest.php` · `tests/Feature/Reports/ReportScopeTest.php`
      Verifica: `php artisan test tests/Feature/Reports`

**Checkpoint**: US11 funciona por separado.

---

## Phase 16: User Story 12 - US12 · Descargar e imprimir los reportes (Priority: P2)

**Goal**: llevarse las cifras en un archivo o en papel, para presentarlas en reuniones de distrito y
convenciones. **Hito 3.**

**Independent Test**: aplicar filtros a un reporte, descargarlo e imprimirlo, y verificar que el
archivo respeta los filtros y el ámbito del cargo.

- [ ] T106 [US12] · Descarga en hoja de cálculo, respetando filtros y ámbito
      Archivos: `app/Exports/MemberReportExport.php` · `app/Exports/StatsReportExport.php` · `app/Http/Controllers/ReportController.php` · ruta `/reports/download`
      RF-038: el archivo trae lo que la persona está viendo, no todo.
      Verifica: `php artisan test --filter=ReportDownloadTest` — con un filtro de 32 personas, el archivo trae 32 filas, no 140.

- [ ] T107 [US12] · Vista de impresión en blanco y negro
      Archivos: `app/Http/Controllers/ReportController.php` · `resources/js/pages/Reports/Print.tsx` · `resources/css/print.css` · ruta `/reports/print`
      Con el nombre de la iglesia, la fecha y los filtros aplicados en el encabezado.
      Verifica: Ctrl+P produce una hoja legible en blanco y negro, sin fondos de color y sin menús · cabe en una página por sección.

- [ ] T108 [US12] · Prueba: los tres escenarios de HU-12
      Archivos: `tests/Feature/Reports/ReportDownloadTest.php`
      Verifica: `php artisan test --filter=ReportDownloadTest` — el archivo descargado por un líder contiene únicamente su ámbito.

**Checkpoint**: US12 funciona por separado.

---

## Phase 17: User Story 13 - US13 · Registrar un simpatizante (Priority: P2)

**Goal**: dejar anotado a alguien que visitó la iglesia, para que no se pierda de vista. **Hito 3.**

**Independent Test**: registrar un simpatizante con un cargo cualquiera y comprobar que el
seguimiento **no** le queda visible a quien lo registró.

> La autorización de tratamiento de datos de la ficha de simpatizante la cubren T172–T175
> (Phase 14); T172 y T173 llevan las dos etiquetas, `[US10] [US13]`, porque RF-061 y RF-062 valen
> para las dos fichas. La prueba independiente de esta historia incluye el formulario con su aviso
> y su autorización.

- [ ] T109 [US13] · Migraciones y modelos de simpatizantes
      Archivos: `database/migrations/<ts>_create_sympathizers_table.php`, `<ts>_create_sympathizer_stages_table.php`, `<ts>_create_sympathizer_visits_table.php` · `app/Models/Sympathizer.php`, `SympathizerStage.php`, `SympathizerVisit.php` · factories
      `committee_id` apunta al **Comité de Evangelismo (comité padre)**, no a un sub-comité. **No existe `assigned_to_user_id`**: repartir simpatizantes entre líderes fue descartado en la aclaración (R-06). `arrival_source` (`invitation` | `refam` | `campaign` | `family` | `neighbor` | `media`); `stage` 1..5; `visited_at` no futura; `type` de visita (`visit` | `call` | `study` | `service`). El nivel de compromiso **no se guarda** (D-10).
      Verifica: `php artisan test --filter=SympathizerModelTest` · `ScopeCoverageTest` en verde.

- [ ] T110 [US13] · Registro por cualquier cargo
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Http/Requests/StoreSympathizerRequest.php` · `resources/js/pages/Sympathizers/Form.tsx` · ruta `POST /sympathizers`
      Nombre, edad, género, teléfono, barrio, cómo llegó, quién lo invitó y la fecha del primer contacto (RF-041). Quien registra conserva la constancia en `invited_by_user_id` (S-07).
      Verifica: `php artisan test --filter=RegisterSympathizerTest` — el seguimiento queda a cargo del presidente del Comité de Evangelismo · a 360 px.

- [ ] T111 [US13] · Aviso de duplicado, con continuar o cancelar
      Archivos: `app/Support/Sympathizers/DuplicateCheck.php` · `resources/js/pages/Sympathizers/Form.tsx`
      CL-09. **Advierte, no bloquea**: dos personas pueden llamarse igual.
      Verifica: `php artisan test --filter=SympathizerDuplicateTest` — advierte por nombre y teléfono, y deja decidir sin perder lo escrito.

- [ ] T112 [US13] · El seguimiento es solo del presidente del Comité de Evangelismo
      Archivos: `app/Policies/SympathizerPolicy.php` · `resources/js/pages/Sympathizers/Index.tsx`
      **La policy más estrecha del proyecto** (RF-040, R-06): pastor, o presidente del Comité de Evangelismo. Ningún otro cargo, **ni los líderes de los sub-comités de Evangelismo**, ni quien registró a la persona.
      Verifica: `php artisan test --filter=SympathizerScopeTest` — quien lo registró no ve su ficha de seguimiento y recibe una explicación que no parece un error.

- [ ] T176 [US13] · Prueba: ni el líder de Evangelismo ni quien registró abren la ficha
      Archivos: `tests/Feature/Sympathizers/SympathizerScopeTest.php`
      Corrección R-06, por identificador directo además de por lista.
      Verifica: `php artisan test --filter=SympathizerScopeTest` — un líder de sub-comité de Evangelismo recibe «no disponible» al abrir la ficha por su identificador; quien la registró, también.

- [ ] T113 [US13] · Prueba: los cuatro escenarios de HU-13
      Archivos: `tests/Feature/Sympathizers/RegisterSympathizerTest.php` · `tests/Feature/Sympathizers/SympathizerScopeTest.php`
      Verifica: `php artisan test --filter="RegisterSympathizerTest|SympathizerScopeTest"`

**Checkpoint**: US13 funciona por separado.

---

## Phase 18: User Story 14 - US14 · Acompañar a los simpatizantes (Priority: P2)

**Goal**: que el presidente de Evangelismo sepa cómo va cada persona que visita la iglesia, para
darse cuenta a tiempo de quién se está alejando. **Hito 3.**

**Independent Test**: con simpatizantes cargados, recorrer sus etapas, registrar visitas y
comprobar que el nivel de compromiso se recalcula sin intervención manual.

- [ ] T114 [US14] · Cálculo del nivel de compromiso
      Archivos: `app/Support/Sympathizers/CommitmentLevel.php`
      Umbrales de `research.md` R-07, **declarados como constantes de `CommitmentLevel` y en ningún otro lugar**: visitas recientes = contactos de los últimos **60 días**; creciendo = 3 o más visitas recientes y menos de **15 días** sin contacto; estable = al menos 1 visita reciente y menos de **45 días** sin contacto; alejándose = 45 días o más sin contacto, o ninguna visita reciente. Nadie lo escribe a mano (RF-043). *Valores de partida defendibles pero **sin confirmar por el cliente** (A-01): ajustarlos es editar esas constantes y nada más, porque la pantalla y la prueba las leen de ahí.*
      Verifica: `php artisan test --filter=CommitmentTest` — la prueba arma sus casos a partir de las constantes, así que cambiar un umbral no obliga a reescribirla.

- [ ] T115 [US14] · Prueba: creciendo, estable, alejándose, y el que vuelve meses después
      Archivos: `tests/Unit/Sympathizers/CommitmentTest.php`
      CL-10: su historia no se reinicia.
      Verifica: `php artisan test --filter=CommitmentTest` — el que vuelve conserva sus etapas y sus fechas anteriores.

- [ ] T116 [US14] · Pantalla de simpatizantes: embudo de cinco etapas y semáforo
      Archivos: `app/Http/Controllers/SympathizerController.php` · `resources/js/pages/Sympathizers/Index.tsx` · `resources/js/components/domain/StepPath.tsx` · ruta `/sympathizers`
      Cuántos acompañamos, cuántos creciendo, cuántos estables, cuántos alejándose.
      Verifica: `php artisan test --filter=SympathizerIndexTest` · a 360 px el embudo se presenta en vertical, legible.

- [ ] T117 [US14] · Ficha con etapas fechadas y bitácora de visitas
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Http/Controllers/SympathizerVisitController.php` · `resources/js/pages/Sympathizers/Show.tsx` · ruta `/sympathizers/{sympathizer}`
      Las cinco etapas en orden y con el vocabulario de la IPUC: visitó el culto; asiste con frecuencia; recibe estudio bíblico; **se bautizó en el nombre de Jesucristo**; recibió el Espíritu Santo (RF-042, RF-044). Una sola fila por etapa; una etapa no se alcanza sin las anteriores.
      Verifica: `php artisan test --filter=SympathizerShowTest` — registrar una visita recalcula el compromiso · a 360 px.

- [ ] T118 [US14] · Paso a miembro, aprobado por el pastor
      Archivos: `app/Http/Controllers/SympathizerController.php` · `app/Support/Approvals/ApprovalFlow.php` · `app/Support/Sympathizers/ConvertToMember.php` · ruta `POST /sympathizers/{sympathizer}/promote`
      RF-019, RF-045, RN-06. Conserva la fecha de bautismo y la del primer contacto; llena `converted_member_id` y `converted_at`. **La fila del simpatizante no se borra** (CL-10).
      Verifica: `php artisan test --filter=SympathizerToMemberTest` — sin la aprobación del pastor la persona no entra a la lista de miembros, **por el servidor**.

- [ ] T119 [US14] · Prueba: los cinco escenarios de HU-14
      Archivos: `tests/Feature/Sympathizers/SympathizerIndexTest.php` · `tests/Feature/Sympathizers/SympathizerShowTest.php` · `tests/Feature/Sympathizers/SympathizerToMemberTest.php`
      Verifica: `php artisan test tests/Feature/Sympathizers`

**Checkpoint**: US14 funciona por separado. El hito 3 puede cerrarse (T123–T125, Phase 23).

---

## Phase 19: User Story 15 - US15 · Registrar asistencia (Priority: P2)

**Goal**: anotar quién asistió a cada ensayo o reunión, para saber a quién hay que buscar.
**Hito 4.**

**Independent Test**: registrar una reunión, marcar asistentes, corregir una marca y verificar los
porcentajes por persona.

- [ ] T126 [US15] · Migraciones y modelos de asistencia
      Archivos: `database/migrations/<ts>_create_meetings_table.php`, `<ts>_create_meeting_attendances_table.php` · `app/Models/Meeting.php`, `MeetingAttendance.php` · factories
      `held_at` no futura; `type` (`rehearsal` | `meeting` | `service`). **Una fila por convocado**, con `present` verdadero o falso: de ahí sale el denominador. Una sola fila por reunión y miembro.
      Verifica: `php artisan test --filter=MeetingModelTest` · `ScopeCoverageTest` en verde.

- [ ] T127 [US15] · Registro de reunión con convocados y asistentes
      Archivos: `app/Http/Controllers/MeetingController.php` · `app/Http/Requests/StoreMeetingRequest.php` · `resources/js/pages/Meetings/Index.tsx`, `Meetings/Form.tsx` · ruta `/attendance`
      Fecha, tipo, tema, convocados y asistentes. **Los convocados solo pueden ser miembros de ese comité o sub-comité** (RF-046, RF-047).
      Verifica: `php artisan test --filter=MeetingTest` — convocar a alguien de otro comité se rechaza en el servidor · a 360 px se marca la asistencia con el pulgar, una fila por persona.

- [ ] T128 [US15] · Porcentaje por persona sobre las reuniones a las que fue convocada
      Archivos: `app/Support/Meetings/AttendanceRate.php`
      RF-048: el denominador son las reuniones a las que **fue convocada**, no todas las del comité.
      Verifica: `php artisan test --filter=AttendanceRateTest`

- [ ] T129 [US15] · Prueba: el porcentaje, incluida la reunión sin ningún asistente
      Archivos: `tests/Unit/Meetings/AttendanceRateTest.php`
      CL-11: una reunión sin asistentes se registra, porque es información.
      Verifica: `php artisan test --filter=AttendanceRateTest` — quien fue convocado a cero reuniones no da error ni 0 %, da «sin convocatorias».

- [ ] T130 [US15] · Señal de acompañamiento por debajo del sesenta por ciento
      Archivos: `resources/js/pages/Meetings/Index.tsx` · `resources/js/components/domain/CarePill.tsx`
      RF-049: en tono de cuidado, **nunca de reproche**.
      Verifica: el texto dice «conviene buscarlo» o equivalente, y en ninguna parte aparece «incumplido», «falló» ni «ausente injustificado» · a 360 px.

- [ ] T131 [US15] · Corrección de asistencia conservando lo anterior
      Archivos: `app/Http/Controllers/MeetingController.php` · `app/Models/MeetingAttendance.php` · ruta `PUT /attendance/{meeting}`
      Verifica: `php artisan test --filter=AttendanceCorrectionTest` — tras corregir, la bitácora conserva qué se había registrado antes, quién lo cambió y cuándo.

- [ ] T132 [US15] · Prueba: los tres escenarios de HU-15
      Archivos: `tests/Feature/Meetings/MeetingTest.php` · `tests/Feature/Meetings/AttendanceCorrectionTest.php`
      Verifica: `php artisan test tests/Feature/Meetings`

**Checkpoint**: US15 funciona por separado.

---

## Phase 20: User Story 16 - US16 · Manejar el presupuesto del comité (Priority: P2)

**Goal**: ver cuánto le asignaron, cuánto lleva gastado y cuánto le queda, para no comprometer plata
que no tiene. **Hito 4.**

**Independent Test**: asignar presupuesto, repartirlo entre sub-comités, aprobar gastos hasta pasar
el saldo y revisar la vista del pastor.

- [ ] T133 [US16] · Migraciones y modelos de presupuesto
      Archivos: `database/migrations/<ts>_create_budgets_table.php`, `<ts>_create_budget_movements_table.php` · `app/Models/Budget.php`, `BudgetMovement.php` · factories
      `budgets`: `year` único junto con `committee_id`, `assigned_amount` mayor o igual a cero. `budget_movements`: `amount` mayor que cero, `type` (`expense` | `allocation` | `income`), `receipt` nulo, `authorized_by_user_id` obligatorio. Aplica a comités y a sub-comités.
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
      Archivos: `app/Http/Controllers/BudgetController.php` · `app/Http/Requests/AssignBudgetRequest.php` · `resources/js/pages/Budget/Index.tsx` · rutas `/budget`, `POST /budget/assign`, `POST /budget/distribute`
      RF-050. El presidente ve asignado, gastado, disponible, porcentaje usado y el reparto entre sus sub-comités.
      Verifica: `php artisan test --filter=BudgetAssignmentTest` — un presidente no asigna fuera de su comité · a 360 px.

- [ ] T168 [US16] · Gasto del propio presidente, sin pasar por el pastor
      Archivos: `app/Support/Approvals/ApprovalFlow.php` · `app/Http/Controllers/BudgetMovementController.php`
      Vacío V-04 (RF-018, RN-04). Un gasto del propio comité del presidente **no crea solicitud**: se registra el movimiento con su nombre y la fecha, cualquiera sea el monto, y el pastor lo ve en el presupuesto, no en su bandeja.
      Verifica: `php artisan test --filter=PresidentOwnExpenseTest`

- [ ] T169 [US16] · Prueba: el gasto del presidente no llega a la bandeja del pastor
      Archivos: `tests/Feature/Budget/PresidentOwnExpenseTest.php`
      Verifica: `php artisan test --filter=PresidentOwnExpenseTest` — tras registrarlo, la bandeja del pastor sigue vacía y el movimiento queda con el nombre del presidente; el gasto **de un líder** sí llega a su presidente.

- [ ] T137 [US16] · Advertencia por exceder el saldo, que permite continuar
      Archivos: `app/Http/Controllers/ApprovalController.php` · `resources/js/components/domain/OverBudgetNotice.tsx`
      RF-051, RN-05: el presupuesto es guía, no tope rígido. **Advierte, no bloquea**, y queda registrado.
      Verifica: `php artisan test --filter=OverBudgetTest` — aprobar un gasto que deja el saldo en negativo exige confirmación y deja el registro del exceso.

- [ ] T138 [US16] · Movimientos con fecha, concepto, monto, soporte y quién autorizó
      Archivos: `app/Http/Controllers/BudgetMovementController.php` · `resources/js/pages/Budget/Index.tsx` · ruta `POST /budget/movements`
      RF-052. Un movimiento registrado **no se borra** (RN-07): una corrección es un movimiento nuevo.
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

**Checkpoint**: US16 funciona por separado.

---

## Phase 21: User Story 18 - US18 · Cargar la lista inicial de miembros (Priority: P3)

**Goal**: subir de una vez la lista de los miembros, para no digitar ciento cuarenta fichas a mano.
**Hito 3.**

**Independent Test**: subir una plantilla con filas correctas y erróneas mezcladas y verificar la
vista previa antes de guardar.

> **Antes de empezar esta fase, confirmar con el pastor.** El supuesto S-02 del spec no está listado
> en la Cláusula Segunda del contrato (riesgo RG-08). Si el cliente no lo pide, la fase se registra
> como propuesta para cotizar y las 140 fichas se cargan con un comando de semilla.

- [ ] T120 [US18] · Plantilla de hoja de cálculo para la carga inicial
      Archivos: `app/Exports/MemberTemplateExport.php` · `public/templates/members.xlsx`
      `birth_date` es **opcional** (mitiga RG-05) y la plantilla trae además las columnas de la **edad declarada**, `age_reported` y `age_reported_at` (T090): si no hay fecha pero sí edad, el miembro entra completo a los reportes por edad; si no hay ninguna de las dos, queda con `profile_pending` y fuera de esos rangos. Las columnas exactas se fijan aquí (A-07).
      Verifica: `php artisan test --filter=MemberTemplateTest` — la plantilla descargada trae los encabezados exactos que lee la importación, las dos columnas de edad declarada entre ellos.

- [ ] T121 [US18] · Importación con validación previa, fila por fila
      Archivos: `app/Http/Controllers/MemberImportController.php` · `app/Imports/MemberImport.php` · `resources/js/pages/Members/Import.tsx` · ruta `/members/import`
      Muestra cuántas filas están correctas y cuáles tienen error **antes de guardar nada**; permite cargar solo las correctas; advierte duplicados y deja actualizar u omitir (RF-039, CL-14).
      Verifica: `php artisan test --filter=MemberImportTest` · a 360 px la vista previa se recorre en tarjetas.

- [ ] T122 [US18] · Prueba: los tres escenarios de HU-18 con un archivo mixto
      Archivos: `tests/Feature/Members/MemberImportTest.php` · `tests/fixtures/members-mixed.xlsx`
      Verifica: `php artisan test --filter=MemberImportTest` — diez filas, tres con error: nada se guarda hasta confirmar, y al confirmar entran siete.

**Checkpoint**: US18 funciona por separado.

---

## Phase 22: User Story 19 - US19 · Corregir un dato equivocado (Priority: P3)

**Goal**: corregir lo que se registró mal, para que la información sea confiable. **Hito 4.**

**Independent Test**: corregir un monto y una fecha, consultar el historial del registro y
comprobar que una aprobación ya resuelta no se puede editar.

- [ ] T142 [US19] · Bitácora activada en los siete modelos del plan
      Archivos: `app/Models/Member.php`, `Activity.php`, `Approval.php`, `BudgetMovement.php`, `MeetingAttendance.php`, `Sympathizer.php`, `CommitteeUser.php` · `config/activitylog.php`
      Principio III, RF-056: qué decía antes, quién lo cambió y cuándo.
      Verifica: `php artisan test --filter=ActivityLogCoverageTest` — cambiar un monto deja el valor anterior, el autor y la fecha, en los siete modelos.

- [ ] T177 [US19] · El historial se lee por la relación del modelo, nunca de `activity_log`
      Archivos: `app/Http/Controllers/HistoryController.php` · `tests/Architecture/ScopeCoverageTest.php`
      Vacío V-01 (R-05). La tabla del paquete no lleva `site_id`, así que queda fuera del filtro automático: el historial se obtiene **siempre** pidiéndoselo al modelo apuntado, que sí carga el ámbito. `ScopeCoverageTest` mantiene esa única entrada en su lista blanca.
      Verifica: `php artisan test --filter=HistoryScopeTest` — un presidente no obtiene el historial de un registro de otro comité, ni por la pantalla ni por identificador directo · `grep -rn "activity_log" app/Http` no devuelve consultas directas.

- [ ] T143 [US19] · Pantalla de historial de un registro
      Archivos: `app/Http/Controllers/HistoryController.php` · `resources/js/components/domain/ChangeTrail.tsx` · ruta `/history/{type}/{id}`
      En lenguaje de la congregación: «El hermano Rigoberto cambió el monto de $100.000 a $150.000 el 12/03/2027». **Nunca nombres de columna.**
      Verifica: `php artisan test --filter=HistoryTest` — la secuencia completa, en orden · a 360 px.

- [ ] T144 [US19] · La aprobación resuelta no se edita
      Archivos: `app/Policies/ApprovalPolicy.php` · `resources/js/pages/Approvals/Index.tsx`
      RF-057, RN-07: se registra una decisión nueva. **El botón no existe y la ruta lo rechaza.**
      Verifica: `php artisan test --filter=ApprovalImmutabilityTest` — la ruta responde que no se puede, aunque se llame directamente.

- [ ] T145 [US19] · Prueba: los tres escenarios de HU-19
      Archivos: `tests/Feature/History/HistoryTest.php`
      Verifica: `php artisan test --filter=HistoryTest`

- [ ] T146 [US19] · Pérdida de conexión sin perder lo escrito
      Archivos: `resources/js/lib/draft-storage.ts` · `resources/js/components/ui/Form.tsx`
      CL-15, S-06. Guarda el borrador en el dispositivo y lo repone al volver; avisa en una frase clara. **No se busca un modo de trabajo sin conexión.**
      Verifica: con la red apagada en el navegador, el formulario avisa, y al reconectar conserva lo que la persona escribió.

**Checkpoint**: todas las historias funcionan por separado.

---

## Phase 23: User Story 21 - US21 · Administrar la estructura de comités (Priority: P1)

**Goal**: que el pastor cree, renombre, reordene e inactive comités, sub-comités y equipos, para que
el organigrama del sistema sea el de la congregación de hoy. **Hito 1.**

**Independent Test**: crear un comité con un sub-comité y un equipo, renombrarlos, inactivar uno y
comprobar que su historia sigue consultable.

> **P1, y su orden de ejecución es justo después de la Phase 2**, no aquí. Aparece al final para no
> renumerar las veinte fases anteriores. Sin estructura configurable no hay nada que configurar: la
> semilla de demostración (T049) depende de esta fase, y la investigación del 2026-10-24 confirmó
> que no existe una lista canónica de comités que sembrar (DEP-05).

- [ ] T181 [US21] · Policy de la estructura: solo el pastor
      Archivos: `app/Policies/CommitteeStructurePolicy.php` · `app/Providers/AppServiceProvider.php`
      RF-063. Ningún otro cargo crea, renombra, reordena ni inactiva nodos.
      Verifica: `php artisan test --filter=CommitteeStructurePolicyTest` — un presidente recibe «no disponible» en las seis acciones, por el servidor y no por la pantalla.

- [ ] T182 [US21] · Crear, renombrar y reordenar nodos en los tres niveles
      Archivos: `app/Http/Controllers/CommitteeStructureController.php` · `app/Http/Requests/StoreCommitteeRequest.php`, `UpdateCommitteeRequest.php` · `resources/js/pages/Structure/Index.tsx` · `resources/js/components/domain/StructureTree.tsx` · rutas `/structure`
      Valida la profundidad: un `parent_id` que apunte a un nodo de `depth` 3 se rechaza con una frase que explica que la estructura llega hasta tres niveles (CL-17). `name` único por sede y por padre.
      Verifica: `php artisan test --filter=CommitteeStructureTest` · a 360 px el árbol se administra con el pulgar, sin desplazamiento horizontal.

- [ ] T183 [US21] · Inactivar un nodo conservando toda su historia
      Archivos: `app/Http/Controllers/CommitteeStructureController.php` · `app/Models/Committee.php`
      RF-064, CL-18: `active` en falso, **nunca borrado**. Sale de los desplegables para proponer cosas nuevas; su historia de actividades, actas, asistencia, presupuesto y decisiones sigue consultable; sus miembros quedan con `committee_id` nulo y señalados para reasignar.
      Verifica: `php artisan test --filter=DeactivateCommitteeTest` — tras inactivar, sus actividades y actas siguen consultables, no aparece para proponer, y sus miembros quedan señalados.

- [ ] T184 [US21] · El nombre de la época se conserva en la historia
      Archivos: `app/Models/Committee.php` · `app/Models/Minute.php`
      RF-065, CL-19: renombrar no reescribe la historia. Las actas guardan `committee_name_at_close` al cerrarse, y la bitácora conserva el cambio de nombre con su fecha.
      Verifica: `php artisan test --filter=RenameCommitteeTest` — un acta cerrada antes del cambio sigue mostrando el nombre anterior; el cronograma y los reportes nuevos usan el vigente.

- [ ] T185 [US21] · Prueba: los seis escenarios de HU-21
      Archivos: `tests/Feature/Structure/CommitteeStructureTest.php` · `tests/Feature/Structure/DeactivateCommitteeTest.php` · `tests/Feature/Structure/RenameCommitteeTest.php`
      Verifica: `php artisan test tests/Feature/Structure`

**Checkpoint**: US21 funciona por separado. El pastor administra su organigrama sin ayuda técnica
(CE-16).

---

## Phase 24: User Story 22 - US22 · Levantar el acta de una reunión (Priority: P2)

**Goal**: que el secretario escriba el acta de cada reunión y se pueda imprimir, para que el trabajo
del comité quede digitalizado y no en un cuaderno que se pierde. **Hito 4.**

**Independent Test**: registrar una reunión, escribir su acta, cerrarla e imprimirla.

- [ ] T187 [US22] · Migraciones y modelos `minutes` y `minute_commitments`
      Archivos: `database/migrations/<ts>_create_minutes_table.php`, `<ts>_create_minute_commitments_table.php` · `app/Models/Minute.php`, `MinuteCommitment.php` · factories
      `meeting_id` **único**: una reunión tiene a lo sumo una acta (RF-067). El acta **no duplica** fecha, lugar ni asistentes: los lee de `meetings` y `meeting_attendances` (D-18). `status` (`draft` | `closed`); `number` y `committee_name_at_close` nulos hasta el cierre.
      Verifica: `php artisan test --filter=MinuteModelTest` · `ScopeCoverageTest` en verde con los dos modelos nuevos.

- [ ] T188 [US22] · `MinuteNumber`: correlativo por comité y por año
      Archivos: `app/Support/Minutes/MinuteNumber.php`
      RF-070. Se asigna **al cerrar**, no al crear, para que un borrador abandonado no consuma número.
      Verifica: `php artisan test --filter=MinuteNumberTest`

- [ ] T189 [US22] · Prueba: el correlativo, incluido el cambio de año y dos comités a la vez
      Archivos: `tests/Unit/Minutes/MinuteNumberTest.php`
      Verifica: `php artisan test --filter=MinuteNumberTest` — dos comités llevan numeraciones independientes; el primero del año nuevo vuelve a 1; un borrador abandonado no deja huecos.

- [ ] T190 [US22] · Redacción del acta por el secretario, con borradores
      Archivos: `app/Http/Controllers/MinuteController.php` · `app/Http/Requests/StoreMinuteRequest.php` · `resources/js/pages/Minutes/Form.tsx` · rutas `/minutes`
      RF-068, RF-069: campos fijos —orden del día, desarrollo, compromisos—, no texto libre. El formulario trae la fecha, el lugar y los asistentes de la reunión. Si el comité no tiene secretario, lo redacta el presidente y el sistema lo indica (CL-20).
      Verifica: `php artisan test --filter=DraftMinuteTest` · a 360 px el acta se escribe desde el celular, con el teclado abierto.

- [ ] T191 [US22] · Cierre del acta por el presidente, inmutable desde ese momento
      Archivos: `app/Http/Controllers/MinuteController.php` · `app/Models/Minute.php` · `app/Policies/MinutePolicy.php`
      RF-070, RF-071: al cerrar se asigna el número, se copia el nombre del comité y se sellan quién la cerró y cuándo. Con `status` = `closed` el evento `updating` **rechaza el cambio en el modelo**, no en la pantalla, igual que `approvals` (T060).
      Verifica: `php artisan test --filter=MinuteImmutabilityTest`

- [ ] T192 [US22] · Prueba: la inmutabilidad y la corrección con acta nueva
      Archivos: `tests/Feature/Minutes/MinuteImmutabilityTest.php`
      CL-22: una acta cerrada no se edita ni por la ruta directa; el sistema ofrece levantar una acta nueva que haga constar la corrección, y las dos quedan en la historia.
      Verifica: `php artisan test --filter=MinuteImmutabilityTest`

- [ ] T193 [US22] · Compromisos del acta, y su paso opcional a tarea
      Archivos: `app/Http/Controllers/MinuteCommitmentController.php` · `resources/js/components/domain/CommitmentList.tsx`
      RF-073, D-19: el compromiso es **texto**; convertirlo es opcional. Al convertir se crea la fila en `activity_tasks` con responsable y fecha, se guarda su identificador, y **no se reescribe el texto del compromiso**: el acta conserva lo acordado palabra por palabra.
      Verifica: `php artisan test --filter=CommitmentToTaskTest` — convertido, aparece en los pendientes de esa persona y cuenta para el avance de la actividad (RF-027).

- [ ] T194 [US22] · Vista de impresión del acta
      Archivos: `app/Http/Controllers/MinuteController.php` · `resources/js/pages/Minutes/Print.tsx` · `resources/css/print.css` · ruta `/minutes/{minute}/print`
      RF-072: blanco y negro, con el nombre de la iglesia, el comité, el número, la fecha, los asistentes, el desarrollo, los compromisos y el espacio de las firmas. **Pendiente DEP-06**: si el distrito tiene formato oficial, hay que verlo antes de cerrar esta tarea; no está publicado en ninguna parte.
      Verifica: Ctrl+P produce una hoja legible en blanco y negro, sin fondos de color ni menús.

- [ ] T195 [US22] · Prueba: los siete escenarios de HU-22
      Archivos: `tests/Feature/Minutes/DraftMinuteTest.php` · `tests/Feature/Minutes/CloseMinuteTest.php` · `tests/Feature/Minutes/CommitmentToTaskTest.php`
      Verifica: `php artisan test tests/Feature/Minutes` — incluye que un cargo de otro comité no ve ninguna acta ajena (RF-074).

**Checkpoint**: US22 funciona por separado. El libro de actas queda digitalizado (CE-13).

---

## Phase 25: User Story 23 - US23 · Respaldar el cumplimiento con evidencias (Priority: P2)

**Goal**: adjuntar fotos y documentos a una actividad, para demostrar que se cumplió sin tener que
explicarlo de palabra. **Hito 4.** Esta fase construye la infraestructura de archivos que US24
también usa.

**Independent Test**: subir una foto y un documento a una actividad del comité y comprobar que un
cargo ajeno no los ve ni por la pantalla ni por la ruta directa.

- [ ] T196 [US23] · Migración y modelo `attachments`
      Archivos: `database/migrations/<ts>_create_attachments_table.php` · `app/Models/Attachment.php` · `database/factories/AttachmentFactory.php`
      Polimórfica sobre `Activity` y `BudgetMovement`; `kind` (`evidence` | `receipt`); `committee_id` copiado del objeto apuntado para filtrar sin unir; `replaced_by_id` nulo.
      Verifica: `php artisan test --filter=AttachmentModelTest` · `ScopeCoverageTest` en verde.

- [ ] T197 [US23] · `StoreAttachment`: valida tipo y peso, comprime y guarda
      Archivos: `app/Support/Attachments/StoreAttachment.php` · `config/filesystems.php`
      RF-076, RF-077: imagen (JPEG, PNG, WebP) o PDF, nada ejecutable; **10 MB como máximo** después de comprimir; las imágenes se reducen al recibirlas sin que la persona haga nada. Se guarda en el disco del servidor **fuera de la carpeta pública** (D-17, RF-078).
      Verifica: `php artisan test --filter=StoreAttachmentTest`

- [ ] T198 [US23] · Prueba: límite, compresión y tipos rechazados
      Archivos: `tests/Unit/Attachments/StoreAttachmentTest.php`
      CL-23: un archivo de 12 MB se rechaza con una frase que dice cuánto pesa y cuál es el límite, **sin perder lo que la persona escribió** en el formulario. Un `.php` o un `.exe` se rechaza. Una foto de 6 MB queda guardada pesando menos.
      Verifica: `php artisan test --filter=StoreAttachmentTest`

- [ ] T199 [US23] · `ServeAttachment`: entrega el archivo pasando por la policy
      Archivos: `app/Support/Attachments/ServeAttachment.php` · `app/Http/Controllers/AttachmentController.php` · `app/Policies/AttachmentPolicy.php` · ruta `/attachments/{attachment}`
      RF-080, D-17: **nunca por URL pública**. Si los archivos estuvieran en `public/`, cualquiera con el enlace vería una evidencia ajena y el principio I se caería por la puerta de atrás sin que ninguna prueba lo note.
      Verifica: `php artisan test --filter=ServeAttachmentTest`

- [ ] T200 [US23] · Prueba: un cargo ajeno no obtiene el archivo por ninguna vía
      Archivos: `tests/Feature/Scope/AttachmentIsolationTest.php`
      Verifica: `php artisan test --filter=AttachmentIsolationTest` — ni por la pantalla, ni por `/attachments/{id}` con el identificador en la mano, ni en la carga del cronograma compartido (RF-080). Y no existe ningún archivo del proyecto servido desde `public/`.

- [ ] T201 [US23] · Evidencias en la ficha de la actividad
      Archivos: `app/Http/Controllers/AttachmentController.php` · `resources/js/pages/Activities/Show.tsx` · `resources/js/components/domain/FileUpload.tsx`, `AttachmentGallery.tsx`
      RF-075. Una actividad sin evidencias lo indica con naturalidad, no como error (CL-26).
      Verifica: `php artisan test --filter=ActivityEvidenceTest` · a 360 px se sube una foto desde la cámara del teléfono en dos toques.

- [ ] T202 [US23] · Reemplazo sin borrado
      Archivos: `app/Http/Controllers/AttachmentController.php` · `app/Models/Attachment.php`
      RF-079, CL-25: un archivo adjunto a algo ya cumplido o ya autorizado **no se borra**; se sube el correcto y el anterior queda con `replaced_by_id`, conservado en la historia.
      Verifica: `php artisan test --filter=AttachmentReplaceTest` — intentar borrar responde que no se puede; reemplazar deja los dos en la historia.

- [ ] T203 [US23] · Prueba: los cinco escenarios de HU-23
      Archivos: `tests/Feature/Attachments/ActivityEvidenceTest.php` · `tests/Feature/Attachments/AttachmentReplaceTest.php`
      Verifica: `php artisan test tests/Feature/Attachments`

**Checkpoint**: US23 funciona por separado. El cumplimiento queda respaldado (CE-14).

---

## Phase 26: User Story 24 - US24 · Subir el soporte de un gasto (Priority: P2)

**Goal**: que el tesorero adjunte la factura o el recibo a cada gasto, para que quede claro en qué
se usó el dinero y quién lo autorizó. **Hito 4.** Reutiliza la infraestructura de la Phase 25.

**Independent Test**: registrar un gasto con su factura en PDF y comprobar que el ámbito del comité
se respeta.

- [ ] T204 [US24] · Soporte adjunto al movimiento de presupuesto
      Archivos: `app/Http/Controllers/BudgetMovementController.php` · `resources/js/pages/Budget/Index.tsx` · `resources/js/components/domain/FileUpload.tsx`
      RF-075 con `kind` = `receipt`. Lo sube el tesorero o el presidente. El pastor abre el soporte de cualquier movimiento de su congregación; un cargo ajeno no ve ni el movimiento ni el soporte.
      Verifica: `php artisan test --filter=BudgetReceiptTest` · a 360 px se adjunta un PDF desde el teléfono.

- [ ] T205 [US24] · Prueba: los cuatro escenarios de HU-24
      Archivos: `tests/Feature/Attachments/BudgetReceiptTest.php`
      Verifica: `php artisan test --filter=BudgetReceiptTest` — quitarle el soporte a un movimiento ya registrado responde que no se puede: se registra un movimiento nuevo que corrija (RN-07).

**Checkpoint**: US24 funciona por separado. Cada peso tiene su soporte (CE-15).

---

## Phase 27: Polish & Cross-Cutting Concerns

**Purpose**: semillas, cierres de hito, barridos sobre el sistema completo y entrega. Las tareas de
cierre de cada hito se ejecutan al terminar las fases de ese hito, no todas al final.

### 23.1 · Semillas y cierres de hito

- [x] T049 · Semilla de la organización: 6 comités, 10 sub-comités, 17 cargos
      Archivos: `database/seeders/CommitteeSeeder.php` · `database/seeders/UserSeeder.php` · `database/seeders/DatabaseSeeder.php`
      Los datos de la presentación que el pastor ya vio (D-14). **Ficticios**, como exige la constitución. Incluye la cuenta de doble cargo para probar el selector (T163).
      Verifica: `php artisan migrate:fresh --seed` deja en `committees` y en `users` tantas filas como declara el sembrador, y la comprobación las cuenta contra los datos de `CommitteeSeeder` y `UserSeeder`, nunca contra un número escrito en esta tarea (hoy, 6 comités más 10 sub-comités y 17 cargos) · entrar con cada uno de los tres cargos muestra su ámbito y nada más.

- [ ] T050 · Prueba de humo del hito 1
      Archivos: `tests/Feature/Scope/MilestoneOneSmokeTest.php`
      Verifica: `php artisan test --filter=MilestoneOneSmokeTest` — los tres cargos entran, cambian clave, ven lo suyo, y el ajeno les responde el mensaje de alcance.

- [ ] T051 · Presupuesto de consultas de las pantallas del hito 1
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: inicio, comités y ficha de comité por debajo de cinco consultas cada una.

- [ ] T052 · Puerta del hito 1
      Archivos: `docs/hitos/hito-1.md`
      Verifica: las ocho puertas de calidad pasan · la URL de producción sirve el hito · revisión en un celular real anotada en el documento.

- [ ] T087 · Semilla del cronograma: 38 actividades, plan del año y solicitudes de ejemplo
      Archivos: `database/seeders/ActivitySeeder.php` · `database/seeders/ApprovalSeeder.php`
      Verifica: `php artisan migrate:fresh --seed` · el cronograma muestra los doce meses con datos y la bandeja de cada cargo trae solicitudes.

- [ ] T088 · Presupuesto de consultas de las pantallas del hito 2
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: cronograma, ficha de actividad, bandeja e inicio por debajo de cinco consultas cada una.

- [ ] T089 · Puerta del hito 2
      Archivos: `docs/hitos/hito-2.md`
      Verifica: las ocho puertas pasan · producción sirve el hito · revisión en celular real anotada.

- [ ] T123 · Semilla de la gente: 140 miembros y 37 simpatizantes
      Archivos: `database/seeders/MemberSeeder.php` · `database/seeders/SympathizerSeeder.php`
      Los datos de la presentación (D-14). **Ficticios.** Incluye los casos límite: sin comité, sin sub-comité, menor de edad, no bautizado, ficha pendiente de completar, sin autorización de datos, y el simpatizante que se alejó.
      Verifica: `php artisan migrate:fresh --seed` · los reportes dan los mismos números que el pastor vio en la presentación.

- [ ] T124 · Presupuesto de consultas de las pantallas del hito 3
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Verifica: miembros, reportes, simpatizantes y la ficha de simpatizante por debajo de cinco consultas cada una.

- [ ] T125 · Puerta del hito 3
      Archivos: `docs/hitos/hito-3.md`
      Verifica: las ocho puertas pasan · producción sirve el hito · revisión en celular real anotada.

### 23.2 · Barridos de calidad de todo el sistema

- [ ] T147 [P] · Barrido de jerga técnica en la interfaz
      Archivos: `resources/js/` · `lang/es/`
      Principio IV, RN-08.
      Verifica: `grep -rniE "endpoint|query|dashboard|tenant|login|token|logout|submit" resources/js lang` no devuelve nada **en texto visible al usuario**.

- [ ] T148 [P] · Barrido de vocabulario IPUC
      Archivos: `resources/js/` · `lang/es/` · `database/seeders/`
      Verifica: `grep -rniE "misa|sacerdote|p[áa]rroco|trinidad|padre, hijo y esp[íi]ritu" resources database lang` no devuelve nada · aparecen culto, bautismo en el nombre de Jesucristo, Escuela Dominical, REFAM, Damas Dorcas, vigilia, convención y Santa Cena.

- [ ] T149 [P] · Revisión manual de las consultas con nombre de tabla dinámico
      Archivos: `app/` · `docs/convenciones.md`
      Puerta de calidad 6, **solo la parte que `NoRawQueriesTest` (T165) no puede ver**: la tabla nombrada en una variable, concatenada, recibida por parámetro o resuelta en tiempo de ejecución, donde el análisis estático no sabe si esa tabla lleva ámbito. Lo literal ya lo prohíbe la prueba automatizada y aquí no se repite.
      Verifica: `grep -rnE "DB::|->from\(|->table\(|whereRaw|selectRaw" app/ | grep '\$'` — cada resultado queda corregido, o anotado en `docs/convenciones.md` con el motivo por el que esa tabla no lleva ámbito · `php artisan test --filter=NoRawQueriesTest` sigue en verde.

- [ ] T150 · Presupuesto de consultas de todas las pantallas
      Archivos: `tests/Architecture/QueryBudgetTest.php`
      Puerta de calidad 5, y base del CE-07. **La lista la arma la prueba recorriendo `Route::getRoutes()`**: se queda con las rutas `GET` que responden con Inertia y falla si alguna no tiene caso de medición, en vez de quedar fuera por un número escrito a mano. La fuente de verdad es la tabla de rutas, no `resources/js/pages/`: lo que se mide son respuestas. Lista blanca justificada para `/login` y `/password/change`. Cuentan las consultas propias de la pantalla, sin las tres fijas de Laravel: 3 consultas como máximo para el bloque compartido y 2 para la pantalla (T025, T028). **Dos excepciones declaradas**, y ninguna más: `/reports` (T102) y `/` (T084), cuyas cifras viajan en propiedades diferidas con su propio presupuesto de cinco por respuesta. La de reportes es la de mayor riesgo (R-08).
      Verifica: `php artisan test --filter=QueryBudgetTest` — todas las rutas con Inertia por debajo de cinco consultas propias, como el pastor, un presidente y un líder, y sin crecer con los datos · una ruta nueva sin caso de medición pone la prueba roja · las propiedades diferidas de las dos excepciones se miden una por una.

- [ ] T153 · Puerta del hito 4
      Archivos: `docs/hitos/hito-4.md`
      Verifica: las ocho puertas pasan sobre el sistema completo · producción sirve el hito.

### 23.3 · Entrega

- [ ] T154 · Migraciones sobre base de datos en limpio
      Archivos: — (verificación sobre el repositorio)
      Puerta de calidad 2.
      Verifica: `php artisan migrate:fresh --seed` en una base vacía, sin un solo error ni advertencia.

- [ ] T155 · Suite completa en verde
      Archivos: — (verificación sobre el repositorio)
      Puerta de calidad 1.
      Verifica: `php artisan test` · ninguna prueba omitida, saltada ni marcada como pendiente · `ScopeCoverageTest`, `NoRawQueriesTest` y `QueryBudgetTest` incluidas.

- [ ] T156 · Revisión a 360 px de todas las pantallas
      Archivos: `docs/revision-360.md`
      Puerta de calidad 4. La lista sale de `Route::getRoutes()` —las rutas `GET` que responden con Inertia—, no de `resources/js/pages/` y no de un número fijo: una ruta sin captura deja la puerta sin pasar. Los formularios quedan cubiertos por la ruta que los sirve. Lista blanca justificada para `/login` y `/password/change`, que igual se revisan por ser las primeras pantallas que alguien ve.
      Verifica: una captura de cada ruta con Inertia a 360 px anotada en el documento, sin faltar ninguna · cero desplazamiento horizontal · ninguna zona pulsable por debajo de **48x48 px** y ningún texto de contenido por debajo de **18 px** (constitución 2.1.0).

- [ ] T206 · Revisar las pantallas ya construidas contra los mínimos nuevos
      Archivos: `resources/css/app.css` · `resources/js/components/ui/` · `resources/js/layouts/AppLayout.tsx`
      La enmienda 2.1.0 subió los mínimos del principio II después de que varias pantallas ya estaban hechas: zona pulsable de 48x48 px, texto de 18 px y contraste AA. Esta tarea las pone al día, y se ejecuta **antes** de cerrar el hito en curso, no al final.
      Verifica: `npm run build` sin advertencias · ninguna pantalla por debajo de los mínimos · el contraste de texto sobre fondo cumple AA medido con la herramienta del navegador.

- [ ] T157 · Medición final en producción
      Archivos: `docs/pruebas-rendimiento.md`
      Verifica: Lighthouse móvil sobre la URL de producción: instalable, y apertura de la pantalla principal por debajo de 3 segundos en 3G simulada.

- [ ] T158 · Acompañar al pastor en la carga de la información de la congregación
      Archivos: `docs/puesta-en-marcha.md`
      La información es propiedad del cliente: se carga en su servidor, **no entra al repositorio** y no sale de allí (constitución, datos y seguridad). Base del CE-05 y del CE-06. Incluye recoger las autorizaciones de tratamiento de datos (CE-12, DEP-04).
      Verifica: los 140 miembros quedan cargados en producción · `php artisan db:backup` corre sobre los datos reales · **ningún dato de la semilla queda en producción**.

- [ ] T159 · Manual de uso en lenguaje de la congregación
      Archivos: `docs/manual/`
      Una página por sección, con capturas del celular.
      Verifica: una hermana que no participó en el desarrollo entra y registra un miembro sin que nadie le explique nada. Es la prueba de aceptación del principio IV; **si no pasa, la pantalla se corrige, no el manual.**

- [ ] T160 · Capacitación de las diecisiete personas con cargo
      Archivos: `docs/capacitacion.md`
      Incluye decirle al pastor, con esas palabras, que los avisos viven dentro del sistema y no llegan por WhatsApp ni por correo (S-01), para que no lo espere.
      Verifica: cada una entra, cambia su clave, y resuelve o envía una solicitud de prueba. Base del CE-01.

- [ ] T161 · Acta de entrega
      Archivos: `docs/acta-entrega.md`
      Cláusula Tercera del contrato: desde la firma corren los cinco días hábiles para observaciones.
      Verifica: firmada por el pastor, con la lista de los módulos entregados y la fecha.

- [ ] T162 · Línea base de los doce criterios de éxito
      Archivos: `docs/criterios-exito.md`
      CE-01 a CE-12 con su forma de medición y el valor del día uno, para poder medirlos a los seis meses.
      Verifica: los doce criterios con un número de partida y una consulta o pantalla que los mide.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: sin dependencias, empieza de inmediato.
- **Foundational (Phase 2)**: depende del Setup. **BLOQUEA todas las historias.** El filtro de
  alcance es el principio no negociable I: ninguna pantalla con datos de comité se construye antes
  de T024.
- **Historias (Phases 3–22)**: todas dependen de la Phase 2. Después pueden ir en paralelo si
  hubiera equipo, o en orden de prioridad (P1 → P2 → P3), que es lo que corresponde aquí: una sola
  persona construyendo.
- **Polish (Phase 23)**: las tareas de cierre de cada hito se ejecutan al terminar las fases de ese
  hito. Los barridos y la entrega, al final.

### Dependencias entre historias

Con el esquema compartido en la Phase 2 (bloque 2.3: T053, T054, T055 y T057), las dependencias de
tabla quedan resueltas antes de la primera historia. Las siete que quedan son de regla, de datos o
de pantalla:

| Historia | Depende de | Por qué |
|---|---|---|
| US05 | US06 · T058 | Proponer escala por `ApprovalFlow`, que construye la fase anterior |
| US04 · T099 | US10 · T096 | La cinta de alcance necesita la lista de miembros para contar |
| US04 · T151 | Todas | El aislamiento por pantalla necesita que exista cada ruta con Inertia |
| US11, US12 | US10 · T090 | Los reportes necesitan el padrón |
| US14 | US13 · T109 | El acompañamiento necesita el registro |
| US16 · T168 | US06 · T058 | El gasto propio del presidente se decide en `ApprovalFlow` |
| US05 · T064 | US09 · T078 | El detector de cruce consulta el cronograma compartido |

Todas esas filas apuntan **hacia atrás** en el documento, y por eso el orden de ejecución sigue
siendo el orden del documento: `ApprovalFlow` (T058) se construye en la Phase 8 y se consume en las
Phases 9, 18 y 20; el esquema vive en el cimiento; el padrón y el registro de simpatizantes van
antes que lo que los usa. Las excepciones son tres tareas, anotadas cada una en su propio texto:
T099 (US04) se ejecuta al terminar la Phase 14, T151 (US04) en la Phase 23, y T152 (US20) también
en la Phase 23, esta por afinamiento y no por dependencia. Hay una cuarta, de regla y no de tabla:
**T064** (US05, Phase 9) consulta `Activity::forSharedCalendar()`, que construye T078 en la Phase
12, así que T064 se ejecuta al llegar la Phase 12 y US05 cierra ahí su quinto escenario, el del
aviso de cruce. Fuera de esas cuatro, **ninguna tarea espera una tabla o una regla que no exista**.

### Dentro de cada historia

- Esquema (migración, modelo, factory) → cálculo puro → policy → controlador y pantalla → prueba
- La tarea `Prueba:` va **inmediatamente después** de lo que verifica (principio V)
- La historia no se cierra sin su prueba en verde y su revisión a 360 px

### Parallel Opportunities

- **Phase 1**: T003, T004, T005, T006 y T011 son `[P]`. T007–T009 dependen del servidor, en serie.
- **Phase 2**: ninguna. El bloque 2.1 bloquea al 2.2, el 2.2 bloquea al 2.3, y el 2.3 bloquea
  todo lo demás. Es deliberado: el filtro de alcance no se construye en paralelo con nada, y el
  esquema compartido se levanta sobre él.
- **Phases 3–22**: dentro de cada historia, lo marcado `[P]` (T030, T037, T045, T046).
- **Phase 23**: los dos barridos T147 y T148 y la revisión T149 son independientes entre sí.
- **Con equipo**: tras la Phase 2, las historias P1 de los hitos 1 y 2 se pueden repartir. Las
  pruebas de aislamiento, no: las escribe quien construye la pantalla.

---

## Parallel Example: Phase 1 (Setup)

```bash
# Las cinco tareas [P] del arranque, en paralelo:
Task: "T003 Instalar y fijar las tres dependencias en composer.json y package.json"
Task: "T004 Escribir la guía de ejecución en CLAUDE.md"
Task: "T005 Configurar Pest y la base de pruebas en tests/Pest.php y .env.testing"
Task: "T006 Repositorio, rama y convención de commits en .gitignore y docs/convenciones.md"
Task: "T011 Prueba del respaldo en tests/Feature/BackupCommandTest.php"
```

## Parallel Example: Phase 7 (US20 · Primero el celular)

```bash
# Dos tareas [P] tras el diseño base (T044):
Task: "T045 Componente tabla-tarjeta en resources/js/components/ui/CardTable.tsx"
Task: "T046 Aplicación instalable en vite.config.ts y public/manifest.webmanifest"
```

---

## Implementation Strategy

### MVP First (US01)

1. Phase 1 · Setup, con el servidor levantado desde el día 2.
2. Phase 2 · Foundational — **CRÍTICO, bloquea todo**. El filtro de alcance con sus tres capas y
   sus tres pruebas de arquitectura, y el esquema compartido del bloque 2.3.
3. Phase 3 · US01: entrar y cambiar la clave.
4. **PARAR Y VALIDAR**: las diecisiete personas entran, cambian su clave, y ninguna ve lo ajeno.
5. Desplegar y mostrar.

### Entrega incremental, por los cuatro hitos del contrato

| Hito | Fases | Qué se puede mostrar al pastor |
|---|---|---|
| **1 · Cimiento** | 1, 2, 3, 4, 5, 6, 7 + cierres T049–T052 | Cada cargo entra y ve exactamente lo suyo, desde el celular |
| **2 · Decidir** | 8 (US06), 9 (US05), 10, 11, 12, 13 + cierres T087–T089 | Un líder propone, el presidente revisa, el pastor aprueba, y entra al cronograma |
| **3 · La gente** | 14, 15, 16, 17, 18, 21 + cierres T123–T125 | El padrón con sus reportes descargables, y el acompañamiento de simpatizantes |
| **4 · Cerrar** | 19, 20, 22 + 23.2 y 23.3 | Asistencia, presupuesto, historial de cambios y la entrega formal |

Cada hito termina **funcionando de punta a punta en el celular**, desplegado y probado — no a
medias.

### Si el tiempo aprieta

El riesgo RG-04 (cuatro semanas para veinte historias) es el principal del proyecto, y la política
de alcance de este documento no deja recortarlo: **se mueve la fecha, con acuerdo escrito previo**.
El indicador temprano es el cierre del hito 1. Lo que **nunca** se negocia son los dos principios
no negociables: el filtro de alcance y las pruebas.

---

## Cobertura del spec

### Requisitos funcionales — los 80

| RF | Tareas | RF | Tareas |
|---|---|---|---|
| RF-001 | T029 | RF-032 | T092, T093 |
| RF-002 | T031, T033 | RF-033 | T092, T093 |
| RF-003 | T034, T035, T036, **T170, T171** | RF-034 | T094 |
| RF-004 | T032 | RF-035 | T095 |
| RF-005 | T037 | RF-036 | T100, T102 |
| RF-006 | T038 | RF-037 | T103 |
| RF-007 | T018–T022, T024, **T163, T164** | RF-038 | T106, T107, T108 |
| RF-008 | T026, T027 | RF-039 | T121, T122 |
| RF-009 | T028, T099 | RF-040 | T110, T112, **T176** |
| RF-010 | T078, T079 | RF-041 | T110 |
| RF-011 | T015 | RF-042 | T117 |
| RF-012 | T042, T043 | RF-043 | T114, T115 |
| RF-013 | T016, T017 | RF-044 | T117 |
| RF-014 | T090, T097 | RF-045 | T118 |
| RF-015 | T057, T058 | RF-046 | T127 |
| RF-016 | T058, T065 | RF-047 | T127 |
| RF-017 | T058, T065, **T168** | RF-048 | T128, T129 |
| RF-018 | T058, T059, **T168, T169** | RF-049 | T130 |
| RF-019 | T118 | RF-050 | T136 |
| RF-020 | T070 | RF-051 | T137 |
| RF-021 | T060, T142 | RF-052 | T138 |
| RF-022 | T072 | RF-053 | T140 |
| RF-023 | T061 | RF-054 | T084 |
| RF-024 | T075 | RF-055 | T028, T085 |
| RF-025 | T074 | RF-056 | T142, T143 |
| RF-026 | T054, **T166, T167** | RF-057 | T060, T144 |
| RF-027 | T055, T056 | RF-058 | T044, T045, T156 |
| RF-028 | T064 | RF-059 | T046, T048, T157 |
| RF-029 | T081 | RF-060 | T026, T143, T147, T148 |
| RF-030 | T081 | RF-061 | **T172, T174, T175** (miembros y simpatizantes) |
| RF-031 | T090 *(D-09: fecha o edad declarada)*, **T170** | RF-062 | **T173** (los dos formularios) |

Los dieciocho de la ampliación del 2026-10-24:

| RF | Tareas | RF | Tareas |
|---|---|---|---|
| RF-063 | T181, T182 | RF-072 | T194 |
| RF-064 | T183 | RF-073 | T193 |
| RF-065 | T184 | RF-074 | T187, T195 |
| RF-066 | T182, T049 | RF-075 | T196, T201, T204 |
| RF-067 | T187, T190 | RF-076 | T197, T198 |
| RF-068 | T190 | RF-077 | T197, T198 |
| RF-069 | T178, T190, T191 | RF-078 | T197, T010 |
| RF-070 | T188, T191 | RF-079 | T202 |
| RF-071 | T191, T192 | RF-080 | T199, T200 |

### Casos límite — los 26

| CL | Tareas | CL | Tareas |
|---|---|---|---|
| CL-01 | T042, T043 | CL-09 | T111 |
| CL-02 | T097, T098 | CL-10 | T115, T118 |
| CL-03 | T097, T098, T100 | CL-11 | T129 |
| CL-04 | T135 | CL-12 | T101 |
| CL-05 | T017, T019, T020, **T163, T164** | CL-13 | T076, T077 |
| CL-06 | T061 | CL-14 | T121, T122 |
| CL-07 | T063 | CL-15 | T146 |
| CL-08 | T064 | CL-16 | **T170, T171, T174** |
| CL-17 | T015, T182 | CL-22 | T192 |
| CL-18 | T183 | CL-23 | T198 |
| CL-19 | T184 | CL-24 | T197, T010 |
| CL-20 | T186, T190 | CL-25 | T202 |
| CL-21 | T195 | CL-26 | T201, T204 |

### Reglas de negocio — las 9

| RN | Tareas | RN | Tareas |
|---|---|---|---|
| RN-01 | T019, T020 | RN-06 | T118 |
| RN-02 | T019, T058 | RN-07 | T060, T138, T142, T144 |
| RN-03 | T058, T075 | RN-08 | T147, T148 |
| RN-04 | T058, T059, **T168** | RN-09 | T017, T038 |
| RN-05 | T137 | | |

### Criterios de éxito — los 16

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
| CE-09 | T151 aislamiento por pantalla · T164 cargo activo | T162 |
| CE-10 | T044–T048, T152, T156 | T162 |
| CE-11 | T147, T148, T159 | T162 |
| CE-12 | **T172, T174, T175** · T158 recolección | T162 |
| CE-13 | T187–T195 actas cerradas | T162 |
| CE-14 | T196–T203 evidencias | T162 |
| CE-15 | T204, T205 soportes de gasto | T162 |
| CE-16 | T181–T185 estructura administrable | T162 |

### Principios de la constitución

| Principio | Tareas que lo garantizan |
|---|---|
| I · Privacidad entre comités (NO NEGOCIABLE) | T018–T028, **T163–T165**, T040, T062, T067, T078, T079, T092, T099, T112, **T176, T177**, T149, T151 |
| II · Primero el celular | T044–T048, T152, T156, T157 · y la revisión a 360 px de cada tarea de pantalla |
| III · Toda decisión deja rastro | T060, T061, T138, T142, **T177**, T143, T144, **T166** |
| IV · El lenguaje de la congregación | T026, T130, T140, T143, T147, T148, **T173**, T159 |
| V · Ninguna tarea sin pruebas (NO NEGOCIABLE) | Las 50 tareas `Prueba:` · T012, T024, T025, **T165**, T050, T079, T150, T151, T155 |

### Historias de usuario — las 24

| HU | Tareas | HU | Tareas |
|---|---|---|---|
| US01 | T014, T029–T033 | US11 | T100–T105 |
| US02 | T016, T017, T034–T039, T061, **T170, T171** | US12 | T106–T108 |
| US03 | T015, T040–T043 | US13 | T109–T113, **T172, T173, T176** |
| US04 | T019–T028, **T163, T164**, T099, T151 | US14 | T114–T119 |
| US05 | T062–T066 | US15 | T126–T132 |
| US06 | T057–T060, T067–T071 | US16 | T133–T141, **T168, T169** |
| US07 | T072, T073 | US17 | T084–T086 |
| US08 | T053, T074–T077 | US18 | T120–T122 |
| US09 | T054–T056, **T166, T167**, T078–T083 | US19 | T060, T142–T146, **T177** |
| US10 | T090–T098, **T172–T175** | US20 | T044–T048, T152, T206 |
| US21 | T015, T179–T185 | US23 | T196–T203 |
| US22 | T187–T195 | US24 | T204, T205 |

---

## Notes

- `[P]` = archivos distintos, sin dependencias pendientes.
- La etiqueta `[Story]` mapea cada tarea a su historia para poder rastrearla; las del cimiento que
  sirven a varias no la llevan y se rastrean por la tabla de cobertura por historia.
- Cada historia se completa y se prueba por separado.
- Las tareas en **negrita** en las tablas de cobertura (T163–T177) son las que cerraron los vacíos
  detectados en `/speckit-plan`: cargo activo, prueba de SQL crudo, transiciones de estado, gasto
  del propio presidente, ficha mínima, autorización de tratamiento de datos, policy estrecha de
  simpatizantes e historial con ámbito.
- Commit después de cada tarea o grupo lógico, en español e imperativo.
- Parar en cualquier *checkpoint* para validar la historia por separado.
- Evitar: tareas vagas, conflictos en el mismo archivo, y dependencias cruzadas que rompan la
  independencia de las historias.

### Lo que este documento deja pendiente de confirmar

1. **S-02, importación de miembros (Phase 21).** No está listada en la Cláusula Segunda del
   contrato. Confirmar antes de la semana 3; si no se confirma, se registra como propuesta para
   cotizar y las fichas se cargan con un comando de semilla.
2. **S-01, avisos solo dentro del sistema (T084, T085).** El spec lo asume y el contrato excluye
   WhatsApp, correo y mensajes de texto. Decírselo al pastor con esas palabras en la capacitación
   (T160), para que no lo espere.
3. **RG-05, fechas de nacimiento (T090, T120).** Riesgo, no pendiente de decisión: si el cliente no
   las tiene todas, `birth_date` queda opcional y en su lugar se anota la edad declarada, así que
   esos miembros **sí** entran en los reportes por edad, con precisión de ±1 año. Solo quedan fuera
   de los rangos los que no tienen ninguna de las dos cosas. **Decisión cerrada el 2026-10-18**: se
   guarda la fecha de nacimiento y la edad se calcula, se admite la edad declarada como segunda
   entrada, y RF-031 quedó corregido en el spec. Ya no hay visto bueno pendiente.
4. **A-01, umbrales del nivel de compromiso (T114).** Puestos con valores de partida defendibles
   (60, 15 y 45 días), **sin confirmar**, y declarados como constantes de `CommitmentLevel` en un
   solo lugar para poder ajustarlos sin tocar la suite. Confirmar con el Comité de Evangelismo
   durante el hito 3.
5. **DEP-04, aviso de privacidad (T173) y medio de recolección de autorizaciones (T158).** Los
   entrega la congregación. La decisión jurídica sobre el tratamiento de datos es del cliente.
6. **A-08, versiones exactas de las tres dependencias (T003).** Confirmar la compatibilidad con
   Laravel 12 contra Packagist al instalar, y anotar el número resuelto en `research.md`.
