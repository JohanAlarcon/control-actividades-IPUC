# Fase 1 — Modelo de datos

**Funcionalidad**: Sistema de Comités IPUC · **Fecha**: 2026-10-18 · **Plan**: [plan.md](./plan.md)

Nombres de tablas y columnas en inglés, convenciones de Laravel (constitución, sección de idioma).
Toda tabla con información de la congregación lleva `site_id` con llave foránea y el global scope
del rasgo `BelongsToSite`. Las migraciones son la única fuente de verdad: lo que está aquí se
expresa en migraciones, y la base no se modifica a mano.

`created_at` y `updated_at` son estándar de Laravel en todas las tablas y no se repiten abajo.

Leyenda de la columna **Ámbito**: `sede` = solo `BelongsToSite`; `sede+comité` = además
`BelongsToCommitteeScope`, el filtro del cargo activo.

---

## 1. Identidad y organización

### `sites` — la congregación

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `name` | string | obligatorio; es el nombre del sistema: «IPUC La Esmeralda - Ibagué» (RF-081, D-43) |
| `city` | string | obligatorio |
| `active` | bool | por omisión verdadero |

**Ámbito**: ninguno (es la raíz). Una sola fila en operación; existe desde el día 1 por mandato de
la constitución, para que agregar una segunda sede no sea una migración de datos.

### `site_mottos` — el lema de cada año

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `year` | smallint | obligatorio, único por sede |
| `text` | string | obligatorio |
| `citation` | string, nulo | la cita bíblica |
| `recorded_by_user_id` | fk → `users` | `restrict` (D-22) |

**Ámbito**: sede. Lo registra el pastor; la entrada y la barra muestran el del año en curso, y si no
hay, solo el nombre de la sede (RF-082, CL-40).

### `month_notes` — la nota general de un mes

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `year` | smallint | obligatorio |
| `month` | tinyint | 1..12 |
| `text` | string(160) | obligatorio |
| `highlight` | enum, nulo | como en `activities`: rojo lo de la sede, naranja lo del distrito o nacional |
| `recorded_by_user_id` | fk → `users` | `restrict` (D-22) |

**Ámbito**: sede. Las registra el pastor y se ven junto al nombre del mes, en el calendario y en la
impresión (RF-118).

### `users` — quien tiene cargo y entra al sistema

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `member_id` | fk → `members`, nulo | la ficha única de esa persona (RF-003) |
| `username` | string | **único por sede**, obligatorio, sin espacios |
| `name` | string | obligatorio |
| `password` | string | hash de Laravel |
| `must_change_password` | bool | verdadero al crear y al restablecer (RF-002) |
| `is_pastor` | bool | por omisión falso |
| `active` | bool | falso = no entra más, de inmediato (RF-006) |
| `last_login_at` | datetime, nulo | |

**Ámbito**: sede. Sin columna de correo: se entra con usuario asignado (HU-01, R-09). El pastor se
marca con `is_pastor` y no cuelga de ningún comité. `member_id` es nulo solo de forma transitoria
durante la creación; al terminar, toda cuenta apunta a su ficha (V-03).

**Regla de borrado**: una cuenta nunca se borra. Se pone `active` en falso, y su nombre permanece
en todo registro histórico (RF-006, RN-09).

### `committees` — los dos niveles en una sola tabla

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `parent_id` | fk → `committees`, nulo | nulo = comité; con padre = sub-comité |
| `depth` | tinyint | 1 o 2, derivado de `parent_id` y guardado para poder validar y consultar sin recorrer el árbol |
| `name` | string | obligatorio, único por sede y por padre |
| `slug` | string | único por sede |
| `mission` | text, nulo | |
| `color`, `icon` | string, nulo | presentación |
| `position` | int | orden de despliegue |
| `active` | bool | por omisión verdadero |
| `receives_sympathizers` | bool | por omisión falso; marca a BIS, que recibe a todos los simpatizantes; uno por sede (D-34) |
| `logo_path` | string, nulo | el logo del comité, guardado fuera de `public/` como los demás archivos (RF-083) |
| `motto` | string, nulo | el lema del comité |
| `objectives` | text, nulo | los objetivos del comité |

**Ámbito**: sede+comité. Los dos niveles viven en una sola tabla: un solo filtro, una sola policy,
un solo juego de pantallas (D-02). Un nodo sin hijos resuelve CL-01 sin código especial: la ficha
consulta si tiene hijos y, si no, lo dice expresamente (RF-012).

**Regla de profundidad**: **dos niveles** (RF-011, levantamiento del 2026-10-28, D-31). `depth` 1
es comité y 2 es sub-comité. Un `parent_id` que apunte a un nodo de `depth` 2 es inválido: se valida
en el formulario, en el modelo y en una prueba (CL-17). `depth` se calcula al guardar, nunca se
escribe a mano. Del 2026-10-24 al 2026-10-28 la estructura tuvo tres niveles; la migración de D-31
comprueba que no quede ningún equipo antes de restringir.

**Administración** (RF-063 a RF-066, HU-21): solo el pastor crea, renombra, reordena e inactiva
nodos. Un nodo nunca se borra: `active` en falso. Inactivarlo conserva toda su historia
consultable y lo saca de los desplegables para proponer cosas nuevas. Cierra las pertenencias de
sus integrantes; quien no pertenece a otro nodo queda señalado para reasignar, y sus simpatizantes
vuelven a BIS (RF-064, CL-18, CL-29). El nombre vigente vive en esta
tabla; la historia conserva el nombre de su época por la bitácora y por el nombre que las actas
guardan al cerrarse (RF-065, CL-19).

> **Nota de alcance.** El sistema no supone ninguna cantidad ni ningún nombre de comité: las
> pantallas y los reportes se arman con la estructura vigente. La estructura inicial de la sede es la
> del levantamiento del 2026-10-28 —quince comités y seis sub-comités— y la semilla de demostración
> la usa con personas ficticias (S-11). Los responsables reales los entrega el pastor (DEP-05).

### `committee_user` — los cargos, con vigencia

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `user_id` | fk → `users` | obligatorio |
| `committee_id` | fk → `committees` | obligatorio |
| `role` | enum | `president` \| `secretary` \| `treasurer` \| `leader` |
| `started_at` | date | obligatorio |
| `ended_at` | date, nulo | nulo = cargo vigente |

**Ámbito**: sede.

**Reglas**:

- Cada nodo tiene **un único titular vigente por cargo** (RF-013): no puede haber dos filas con el
  mismo `committee_id`, el mismo `role` y `ended_at` nulo. Un comité puede tener a la vez su
  presidente, su secretario y su tesorero vigentes.
- `role` = `president` solo si `depth` es 1; `leader` solo si `depth` es 2. `secretary` y
  `treasurer` solo en `depth` 1: un sub-comité no lleva libro de actas propio ni maneja presupuesto
  aparte del de su comité.
- **El ámbito lo da el nodo, no el cargo**: secretario y tesorero ven exactamente lo que ve su
  presidente —su comité y todos sus descendientes—, y se diferencian en lo que pueden *hacer*, que
  lo resuelven las policies. El secretario redacta actas (RF-069); el tesorero registra movimientos
  y sube soportes (HU-24).
- Un nodo sin secretario funciona: el presidente redacta y cierra el acta, y el sistema lo indica en
  lugar de bloquear el registro (CL-20).
- Una persona puede tener dos filas vigentes (CL-05). Eso **no** significa que vea los dos ámbitos
  a la vez: ver `scope` en [contracts/shared-props.md](./contracts/shared-props.md) y R-01.
- El relevo no borra: cierra la fila con `ended_at` y abre otra (HU-02, RN-09). Las solicitudes
  pendientes del cargo pasan al nuevo responsable y queda registrado el traspaso
  (`approvals.transferred_from_user_id`, RF-023, CL-06).

---

## 2. Miembros

### `members` — la ficha única de cada persona de la congregación

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `code` | string | lo asigna el sistema en orden de registro; único por sede; no cambia (RF-031, S-12) |
| `name` | string | obligatorio |
| `birth_date` | date, nulo | fecha de nacimiento; nulo cuando la congregación no la tiene (RF-031) |
| `age_reported` | int, nulo | edad declarada, solo cuando no se sabe la fecha (RF-031) |
| `age_reported_at` | date, nulo | fecha de la declaración; **obligatoria si `age_reported`, y al revés**: el modelo no guarda una sin la otra |
| `gender` | enum | `f` \| `m` |
| `marital_status` | enum | `single` \| `married` \| `common_law` \| `separated` \| `divorced` \| `widowed` |
| `baptized` | bool | por omisión falso |
| `baptized_at` | date, nulo | **obligatorio si `baptized`** (RF-033) |
| `joined_at` | date, nulo | fecha de ingreso |
| `phone` | string, nulo | |
| `neighborhood` | string, nulo | |
| `notes` | text, nulo | observaciones |
| `adult_confirmed` | bool | verdadero cuando se confirmó que es mayor de dieciocho años sin fecha ni edad anotada (RF-032) |
| `adult_confirmed_at` | date, nulo | obligatoria si `adult_confirmed` |
| `profile_pending` | bool | verdadero en la ficha mínima creada al habilitar una cuenta (V-03) |
| `data_consent` | bool | autorizó el manejo de sus datos (RF-061) |
| `data_consent_at` | date, nulo | obligatorio si `data_consent` |
| `data_consent_means` | enum, nulo | `signature` \| `verbal` \| `message`; obligatorio si `data_consent` |
| `active` | bool | por omisión verdadero |

**Ámbito**: sede+comité, **por sus pertenencias** (D-32): un miembro entra en el ámbito de cada
comité o sub-comité al que pertenece (`committee_member`), y en el del pastor. Sin pertenencias,
solo lo ve el pastor (CL-03).

**No hay columna de dirección** (RF-031, constitución 3.0.0).

**Reglas de validación**:

- **Solo el pastor crea y corrige fichas** (RF-085, D-33). Los cargos agregan o quitan de su lista a
  personas ya registradas, y para encontrarlas usan `Member::forPicker()`, que trae únicamente el
  id, el nombre y la edad calculada (constitución, principio I, excepción 4).
- **La edad se calcula, nunca se guarda suelta.** Sale de `birth_date` cuando existe; cuando no,
  del par `age_reported` + `age_reported_at`, que no envejece porque lleva su fecha de referencia.
  El formulario pide fecha de nacimiento y, cuando no se sabe, ofrece «No la sé — anote la edad»,
  que guarda la edad declarada con la fecha en que se declaró. Las dos entradas son válidas y
  **nunca se piden las dos a la vez**.
- Los reportes por rango de edad usan **las dos fuentes**, así que el padrón entra completo en los
  cortes, e informan que una parte tiene precisión de ±1 año (RF-036).
- Menor de dieciocho años no puede ser `married`, `common_law`, `separated`, `divorced` ni
  `widowed` (RF-032).
  La edad se calcula con `birth_date` cuando existe y con el par `age_reported` +
  `age_reported_at` cuando no; con cualquiera de las dos, la regla se aplica tal cual. **Solo
  cuando la ficha no tiene ninguno de los dos datos**, marcar un estado civil distinto de
  `single` pide **confirmar que la persona es mayor de dieciocho años**, no la fecha, y esa
  confirmación queda anotada: el registro nunca se bloquea por un dato que la congregación no
  tiene (principio IV).
- `baptized_at` obligatoria cuando `baptized` es verdadero (RF-033).
- `baptized_at` no puede ser anterior a `birth_date` —cuando hay fecha de nacimiento— ni posterior
  a hoy.
- La búsqueda por nombre y barrio ignora mayúsculas y tildes (RF-034): la comparación se hace sobre
  una forma normalizada del texto.
- Una ficha con `profile_pending` aparece en listas y reportes señalada como incompleta, y el
  sistema informa cuántas faltan por completar (CL-16).
- Una ficha nunca se borra: `active` en falso. Las decisiones y la asistencia conservan su nombre.

> **Decisión cerrada sobre la edad (RF-031).** El requisito pedía «edad»; se guarda `birth_date` y
> la edad se **calcula**, porque una edad suelta se vuelve falsa al año siguiente y rompería los
> reportes (CE-07); además el Comité de Serenatas necesita cumpleaños. El autor del proyecto dio
> su visto bueno y RF-031 quedó corregido en la especificación el 2026-10-18, admitiendo **las dos
> entradas**: la fecha de nacimiento, y la edad declarada con la fecha de su declaración cuando la
> fecha de nacimiento no se sabe. Lo que la congregación tiene escrito en el cuaderno es muchas
> veces la edad, no la fecha: con solo fecha, los reportes del pastor nacerían vacíos y un hermano
> casado de cuarenta no se podría registrar. Queda abierto el riesgo RG-05 —de cuántos miembros
> hay fecha y de cuántos solo edad—, que es un asunto de datos y se resuelve en la carga inicial:
> las dos columnas son opcionales en la importación, y la ficha a la que le falten las dos queda
> con `profile_pending`. El punto se sigue en A-02 y A-10 de [research.md](./research.md).

### `committee_member` — la pertenencia de cada miembro a uno o varios comités

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `member_id` | fk → `members` | obligatorio; `restrict` (D-22) |
| `committee_id` | fk → `committees` | obligatorio; comité o sub-comité |
| `role` | enum | `board` \| `teacher` \| `assistant` \| `member` (RF-084, RF-106) |
| `board_position` | enum, nulo | `president` \| `secretary` \| `treasurer` \| `vocal`; solo si `role` = `board` |
| `since` | date | obligatorio |
| `ended_at` | date, nulo | nulo = pertenencia vigente |

**Ámbito**: sede+comité. Lo crea y lo cierra el presidente o el líder del nodo, o el pastor.

**Reglas**:

- Una sola pertenencia vigente por miembro y nodo. Un miembro tiene cero, una o varias.
- La función en la lista no da cuenta ni permisos: el cargo con cuenta vive en `committee_user`
  (P-08).
- Quitar a alguien de una lista cierra la fila con `ended_at`; no la borra. Así la asistencia y las
  actas anteriores siguen sabiendo quién estaba.
- Los totales de la congregación cuentan **miembros distintos**, no filas de esta tabla (RF-014,
  RF-036, CL-27).

---

## 3. Simpatizantes

### `sympathizers`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | **el comité asignado**, que lo acompaña; uno solo a la vez (RF-097, D-34) |
| `name` | string | obligatorio |
| `birth_date` | date, nulo | |
| `age_reported`, `age_reported_at` | int y date, nulos | la edad que dijo y el día en que la dijo, como en `members` |
| `gender` | enum | `f` \| `m` |
| `phone` | string, nulo | |
| `neighborhood` | string, nulo | |
| `arrival_source` | enum | `invitation` \| `refam` \| `campaign` \| `family` \| `neighbor` \| `media` |
| `invited_by_user_id` | fk → `users`, nulo | quien lo registró conserva la constancia (S-07) |
| `invited_by_name` | string, nulo | cuando quien invitó no tiene cuenta |
| `first_contact_at` | date | obligatorio |
| `stage` | tinyint | 1..5, la etapa alcanzada más alta |
| `converted_member_id` | fk → `members`, nulo | se llena al aprobarse el paso a miembro |
| `converted_at` | date, nulo | |

**Ámbito**: sede+comité, con la excepción 2 del principio I: un cargo de **BIS** ve a todos los
simpatizantes de la sede; cualquier otro cargo ve solo los asignados a su ámbito; el pastor ve todo
(RF-040, D-34). La excepción vive en el filtro de alcance de `Sympathizer`, no en una consulta
suelta. Para asignar, BIS ve el id y el nombre de los comités activos, y nada más de ellos.

**No existe `assigned_to_user_id`.** Repartir simpatizantes entre líderes de sub-comité fue
descartado en la aclaración; dejar el campo invitaría a construirlo (R-06).

**Reglas**:

- Al registrar, mientras se escribe el nombre, el sistema sugiere a los ya registrados **dentro
  del ámbito**: a BIS, todos; a un comité, los suyos (RF-096). Si al guardar hay alguien con el
  mismo nombre y teléfono **fuera** del ámbito, el sistema **advierte** sin mostrarlo y permite
  continuar o cancelar (CL-09). La comprobación mira toda la sede, pero solo responde sí o no
  (constitución, principio I, excepción 3).
- Lo que registra un comité queda asignado a ese comité; lo que registra BIS, al comité que BIS
  escoge.
- La autorización de tratamiento de datos se guarda igual que en `members`: `data_consent`,
  `data_consent_at`, `data_consent_means`, con las mismas reglas (RF-061).
- Al aprobarse el paso a miembro (RF-045), se crea la fila en `members` conservando la fecha de
  bautismo y la de primer contacto, y se llenan `converted_member_id` y `converted_at`. La fila del
  simpatizante **no se borra**: su historia se conserva (CL-10).

### `sympathizer_assignments` — por qué comités pasó

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `sympathizer_id` | fk → `sympathizers` | obligatorio |
| `committee_id` | fk → `committees` | el comité al que quedó asignado |
| `assigned_by_user_id` | fk → `users` | `restrict` (D-22) |
| `assigned_at` | datetime | obligatorio |

**Ámbito**: sede. Solo se agregan filas: cada asignación y cada reasignación (RF-097). Si el comité
asignado se inactiva, el simpatizante vuelve a BIS y queda la fila (CL-29).

### `sympathizer_stages` — el camino, con fecha por etapa

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `sympathizer_id` | fk → `sympathizers` | obligatorio |
| `stage` | tinyint | 1..5 |
| `reached_at` | date | obligatorio |
| `recorded_by_user_id` | fk → `users` | obligatorio |

**Ámbito**: sede. Solo se agregan filas; la etapa alcanzada conserva su fecha (RF-042). Las cinco
etapas, en orden: 1 visitó el culto · 2 asiste con frecuencia · 3 recibe estudio bíblico ·
4 se bautizó en el nombre de Jesucristo · 5 recibió el Espíritu Santo.

**Reglas**: una sola fila por etapa y simpatizante. Una etapa no se alcanza sin las anteriores. Si
la persona se aleja y vuelve, su historia no se reinicia: las filas siguen ahí (CL-10).

### `sympathizer_visits` — visitas, llamadas y estudios

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `sympathizer_id` | fk → `sympathizers` | obligatorio |
| `visited_at` | date | obligatorio, no futura |
| `type` | enum | `visit` \| `call` \| `study` \| `service` |
| `by_user_id` | fk → `users` | el responsable (RF-044) |
| `note` | text, nulo | |

**Ámbito**: sede.

**El nivel de compromiso no se guarda**: se calcula en
`Support/Sympathizers/CommitmentLevel.php` con las visitas de los últimos 60 días y los días desde
el último contacto, con los umbrales de R-07. Nadie lo escribe a mano (RF-043, D-10).

---

## 4. Cronograma

### `annual_plans` — el plan del año y su acto único de aprobación

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `year` | smallint | único por sede |
| `approved_by_user_id` | fk → `users`, nulo | nulo = plan sin aprobar |
| `approved_at` | datetime, nulo | |

**Ámbito**: sede. Un año nuevo empieza sin aprobar y el anterior queda consultable (CL-13). Solo el
pastor aprueba, en un acto único, y queda identificado con quién y cuándo (RF-025, HU-08).

### `activities`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | el comité responsable |
| `annual_plan_id` | fk → `annual_plans`, nulo | nulo = propuesta durante el año (RF-024) |
| `schedule_id` | fk → `schedules` | obligatorio: el cronograma general de su año o uno interno (D-35) |
| `series_id` | fk → `activity_series`, nulo | cuando es una fecha de una actividad que se repite (D-38) |
| `title` | string | obligatorio |
| `description` | text, nulo | reservada al comité responsable (RF-010) |
| `theme` | string(120), nulo | el tema o lema del culto; sale en azul en el cronograma (RF-116) |
| `highlight` | enum, nulo | `special_day` \| `district` \| `national`: rojo o naranja en el cronograma (RF-116) |
| `scheduled_at` | datetime | obligatorio, dentro del año en curso, no pasada (CL-07) |
| `ends_at` | time | obligatoria, posterior a la hora de inicio (RF-028, D-37) |
| `location` | string, nulo | |
| `activity_type_id` | fk → `activity_types` | obligatorio; la clase debe estar activa al proponer (D-25) |
| `status` | enum | `pending` \| `approved` \| `in_progress` \| `completed` \| `rejected` (RF-026) |
| `progress` | tinyint | 0..100, **calculado** desde las tareas (RF-027) |
| `budget_amount` | decimal(14,2), nulo | reservado al comité responsable (RF-010) |
| `proposed_by_user_id` | fk → `users` | |
| `proposed_at` | datetime | |
| `approved_by_user_id` | fk → `users`, nulo | |
| `approved_at` | datetime, nulo | |
| `rejection_reason` | text, nulo | obligatorio si `status` = `rejected` (RF-020) |
| `completed_at` | datetime, nulo | |
| `completed_by_user_id` | fk → `users`, nulo | quien la marcó cumplida a mano, si no tenía tareas (R-04); `restrict` (D-22) |

**Ámbito**: sede+comité, **con la excepción 1 del principio I** —constitución 3.0.0—:
`Activity::forSharedCalendar()` retira el filtro de comité, toma solo las actividades del
cronograma **general** y selecciona a mano los seis datos que RF-010 permite compartir —título,
fecha, hora, lugar, comité responsable y estado; es decir `title`, `scheduled_at`, `ends_at`,
`location`, `status` y el nombre del comité responsable—. Nunca devuelve `description`,
`budget_amount`, el soporte ni el orden de culto, ni carga tareas (R-11, D-15, D-36). Tampoco
`theme` ni `highlight`, mientras el autor no decida otra cosa (P-09). Las actividades de los
cronogramas internos nunca salen de su comité.

Tres condiciones hacen parte de la excepción, y quien escriba la migración, el modelo o el
controlador las lee aquí:

- **El método solo lee.** No se usa como base de un `update`, de un `delete` ni de una descarga.
- **No concede autorización.** La policy de `Activity` sigue negando toda escritura sobre una
  actividad ajena.
- **El filtro de sede no se retira nunca.** `BelongsToSite` sigue puesto; lo único que se retira es
  el filtro del cargo activo.

El **nombre del comité responsable sí se comparte**: es uno de los seis datos enumerados. La
reserva de RF-009 se refiere a los conteos de `scopeHidden`, que nunca listan cuáles son los
comités ajenos; no convierte el nombre de un comité en dato reservado.

**Qué hay que probar**: una prueba de **lista blanca**. Se compara el juego completo de llaves que
trae una actividad ajena contra esos seis datos y falla si aparece cualquier otro —no busca llaves
prohibidas—, así que una columna nueva en `activities` deja la prueba roja hasta que alguien decida
si se comparte. Y un caso de **escritura**: un presidente que intenta modificar una actividad ajena
recibe «no disponible».

**Cruce**: las actividades del cronograma general no se cruzan (RF-028, D-37). Al proponer, al
cargar al plan y al aprobar, el sistema **impide** guardar una actividad general cuyo horario se
sobreponga el mismo día con otra general vigente, y dice cuál es y de qué comité (CL-08, P-02). Las
de los cronogramas internos sí se cruzan, entre sí y con las generales. Hasta el 2026-10-28 era un
aviso.

**Soporte**: a lo sumo un archivo vigente en `attachments` por actividad (RF-075, D-39).

### `schedules` — el cronograma general de cada año y los internos de cada comité

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees`, nulo | nulo en el general; el comité o sub-comité dueño en los internos |
| `kind` | enum | `general` \| `internal` |
| `year` | smallint | obligatorio |
| `name` | string | obligatorio en los internos —«Módulo 1», «Clases de Semillitas de Amor»— |
| `starts_on`, `ends_on` | date | el periodo |
| `active` | bool | por omisión verdadero |

**Ámbito**: sede+comité para los internos (RF-086): los ven el comité dueño, sus ascendientes y el
pastor. El general es uno por sede y año, y es el que ve todo cargo por la excepción 1. Un comité
tiene cero, uno o varios internos. El plan anual (`annual_plans`) es el acto de aprobación del
cronograma general de su año (D-35).

### `activity_series` — las actividades que se repiten

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `schedule_id` | fk → `schedules` | obligatorio; un cronograma interno |
| `frequency` | enum | `weekly` \| `biweekly` \| `monthly` |
| `starts_on`, `ends_on` | date | obligatorios; sesenta fechas como máximo (D-38) |

**Ámbito**: sede+comité, por su cronograma. Al crearla se generan sus fechas como actividades con
`series_id`, y cada una se mueve o se cancela sola (RF-088, CL-30).

### `activity_types` — las clases de actividad, que administra el pastor

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `name` | string(60) | obligatorio, único dentro de la sede |
| `active` | bool | solo las activas se ofrecen al proponer |
| `position` | smallint | el orden en que se ofrecen |

**Ámbito**: sede. No lleva filtro de comité: las clases son de toda la congregación.

**Nunca se borra** una clase: se desactiva, y las actividades que ya la tienen no cambian (D-25).
Cada sede nueva empieza con las ocho de siempre —Culto, Ensayo, Reunión, Evento, Salida, Visita,
Campaña y Convención—, que el modelo `Site` crea al nacer. La migración
`create_activity_types_table` pasó a esta tabla las actividades que ya existían, que antes
guardaban la clase en una columna `type` con esa misma lista fija.

**Qué hay que probar**: una clase desactivada no se ofrece ni se acepta al proponer; una clase de
otra sede no se ve ni se toca; nadie más que el pastor las administra (`ActivityTypesTest`).

### `activity_tasks`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `activity_id` | fk → `activities` | obligatorio |
| `description` | string | obligatorio |
| `done` | bool | por omisión falso |
| `assigned_to_name` | string, nulo | |
| `done_at` | datetime, nulo | se llena al marcar `done` |

**Ámbito**: sede. Reservadas al comité responsable: no viajan en el cronograma compartido (RF-010).

### Transiciones de estado de una actividad

Regla única del proyecto, definida en la Fase 0 (R-04) porque era el último punto implícito. Vive
en un solo lugar y tiene prueba propia, por exigencia del principio V.

```
progress = redondear(tareas cumplidas / tareas totales × 100)
progress = 0 cuando no hay tareas
```

| De | A | Cuándo |
|---|---|---|
| — | `pending` | Al proponerse fuera del plan anual |
| `pending` | `approved` | Al aprobarla el pastor, o al aprobarse el plan anual completo |
| `pending` | `rejected` | Al rechazarla, con motivo escrito obligatorio |
| `approved` | `in_progress` | Al cumplirse la primera tarea, o al llegar `scheduled_at`: lo que ocurra primero |
| `in_progress` | `completed` | Al llegar `progress` a 100 |
| `approved` / `in_progress` | `completed` | Sin tareas: el responsable la marca cumplida; queda en la bitácora con su nombre |

Sin transiciones hacia atrás: una actividad cumplida no vuelve a «en curso». Un error se corrige
registrando el cambio, que la bitácora conserva (principio III).

---

## 5. Aprobaciones

### `approvals` — una sola bandeja para los cinco tipos

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | el comité de origen |
| `approvable_type`, `approvable_id` | relación polimórfica | el objeto que la solicitud afecta |
| `type` | enum | `activity` \| `expense` \| `task` \| `date_change` \| `new_member` (RF-015, RF-019) |
| `level` | enum | `president` \| `pastor` — quién la tiene **ahora**: el presidente la revisa, el pastor la aprueba. Solo queda `approved` en `pastor` (D-24) |
| `escalated_by_user_id` | fk → `users`, nulo | el presidente que la aprobó y la pasó al pastor: la tarjeta dice «por quién ya pasó» (T068). `restrict`, como toda llave hacia una persona (D-22) |
| `escalated_at` | datetime, nulo | cuándo subió al pastor |
| `status` | enum | `pending` \| `approved` \| `rejected` |
| `title` | string | obligatorio |
| `detail` | text, nulo | |
| `amount` | decimal(14,2), nulo | obligatorio si `type` = `expense` |
| `requested_by_user_id` | fk → `users` | |
| `requested_at` | datetime | de aquí sale «desde cuándo espera» (RF-054); si ya subió al pastor, sale de `escalated_at` |
| `resolved_by_user_id` | fk → `users`, nulo | |
| `resolved_at` | datetime, nulo | |
| `reason` | text, nulo | **obligatorio si `status` = `rejected`** (RF-020) |
| `transferred_from_user_id` | fk → `users`, nulo | traspaso por cargo vacante (RF-023, CL-06) |
| `transferred_at` | datetime, nulo | |

**Ámbito**: sede+comité.

**Regla de inmutabilidad**: una fila con `status` distinto de `pending` **no se edita ni se borra**
(RF-021, RF-057, RN-07). Corregir significa registrar una decisión nueva. Se hace cumplir en el
modelo, no solo en la interfaz, y tiene prueba propia.

**Solo el pastor aprueba (D-24)**: una fila con `status` = `approved` y `level` = `president` **no
existe**. El presidente revisa: su visto bueno sube la solicitud al pastor, sea del tipo que sea, y
si la devuelve queda `rejected` en su propio nivel, con motivo, sin llegar al pastor. Se hace
cumplir en dos sitios:

1. `ApprovalFlow` es el único lugar que crea y resuelve filas, y nunca deja una solicitud aprobada
   en manos del presidente.
2. El modelo `Approval` lo rechaza al guardar, igual que la regla de inmutabilidad.

No lleva restricción `check` en la migración: el escalamiento es la regla más volátil del proyecto
—ya se movió dos veces, con V-04 y con D-24— y llevarla a la base la congela donde es más caro
cambiarla.

**Qué hay que probar**: guardar una solicitud aprobada en nivel `president` falla en el modelo
`Approval`. Y un gasto propuesto por un líder, con el visto bueno de su presidente, **no cierra**:
queda `pending` en el pastor (RF-018).

### El escalamiento — `Support/Approvals/ApprovalFlow.php`

Un solo lugar donde vive la cadena. Solo el pastor aprueba; el presidente revisa (RF-018, D-24).

```
Al crear la solicitud:
  la propone un líder, un secretario o un tesorero → level = president: la revisa su presidente
  la propone un presidente                         → level = pastor, de cualquier tipo, también
                                                     el gasto de su propio comité (D-24)
      type = new_member                            → solo el presidente de Evangelismo (RF-019)

Al resolver:
  el presidente da su visto bueno → NO cierra, sea del tipo que sea: level pasa a pastor, status
                                    sigue pending, quedan escalated_by_user_id y escalated_at, y
                                    quien la propuso lo ve en su bandeja (HU-06 escenarios 1 y 2)
  el presidente la devuelve       → cierra como rejected en su nivel, con motivo obligatorio, y
                                    no llega al pastor (HU-06 escenario 3)
  el pastor aprueba               → cierra y aplica el efecto sobre el objeto apuntado
  el pastor no la aprueba         → cierra como rejected, con motivo obligatorio
```

El pastor puede resolver varias solicitudes del mismo tipo en una sola acción, confirmando una vez
(RF-022, HU-07 escenario 4).

**Al resolver, `ApprovalFlow` vuelve a leer la fila bloqueada** y confirma que sigue como la vio la
persona: pendiente y en el mismo nivel. Un doble toque en «Aprobar» no produce dos decisiones, y un
presidente no cierra lo que ya había subido al pastor (RF-057). Lo ya resuelto responde con una
explicación —quién lo resolvió—, no con un error.

La solicitud se resuelve por **nivel y comité**, no por persona: la de nivel presidente la resuelve
quien actúa como presidente del comité de origen o de uno que lo contiene; la de nivel pastor, el
pastor (`ApprovalPolicy`). Nadie decide a nombre de otro: el pastor no resuelve lo que le toca a un
presidente.

---

## 6. Asistencia

### `attendances` — la asistencia por actividad

Reemplaza a `meetings` y `meeting_attendances` del plan original, que nunca se construyeron: una
reunión es una actividad de un cronograma interno (D-40).

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `activity_id` | fk → `activities` | obligatorio; interna o general (RF-046) |
| `member_id` | fk → `members`, nulo | `restrict` |
| `sympathizer_id` | fk → `sympathizers`, nulo | `restrict` |
| `child_id` | fk → `children`, nulo | `restrict`; la asistencia a las clases (RF-112) |
| `status` | enum | `present` \| `absent` \| `excused` |
| `excuse` | string, nulo | obligatoria si `status` = `excused` |
| `recorded_by_user_id` | fk → `users` | `restrict` |

**Ámbito**: sede, y se consulta siempre desde la actividad, que lleva el filtro de comité.

**Reglas**:

- Exactamente una de las tres llaves de persona va llena.
- Una fila por **convocado**, presente o no: de ahí sale el denominador. Los convocados son los
  integrantes vigentes del nodo dueño de la actividad (RF-047); los simpatizantes se agregan solo
  si vinieron.
- Una sola fila por actividad y persona.
- El porcentaje por persona se calcula sobre las actividades a las que **fue convocada** (RF-048).
  Las clases sin clase no cuentan (RF-112).
- La asistencia de un simpatizante cuenta para su nivel de compromiso (RF-091).
- Corregir una marca conserva lo que se había registrado antes, por la bitácora (RF-056).
- Por debajo del sesenta por ciento, la interfaz lo señala con texto de acompañamiento, nunca de
  reproche (RF-049).
- Una actividad sin ningún asistente se puede registrar, porque es información (CL-11).

---

## 6 bis. Actas de reunión

Entran por la ampliación del 2026-10-24 (RF-067 a RF-074, HU-22).

### `minutes` — el acta de una reunión

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `meeting_id` | fk → `meetings` | obligatorio y **único**: una reunión tiene a lo sumo una acta |
| `committee_id` | fk → `committees` | el comité dueño, copiado de la reunión para poder filtrar sin unir |
| `committee_name_at_close` | string, nulo | el nombre que el comité tenía al cerrarse (RF-065, CL-19) |
| `number` | int, nulo | correlativo **por comité y por año**; se asigna al cerrar, no al crear |
| `year` | smallint | el año de la reunión |
| `agenda` | text, nulo | orden del día |
| `development` | text | desarrollo de la reunión; obligatorio para cerrar |
| `status` | enum | `draft` \| `closed` |
| `drafted_by_user_id` | fk → `users` | quien la redactó (RF-069) |
| `closed_by_user_id` | fk → `users`, nulo | quien la cerró; nulo mientras es borrador |
| `closed_at` | datetime, nulo | |

**Ámbito**: sede+comité. Las actas **no** viajan por la excepción del cronograma compartido
(RF-074): las ve el comité dueño, sus ascendientes y el pastor.

**Reglas**:

- No existen actas sueltas: toda acta pertenece a una reunión ya registrada y hereda su fecha, su
  lugar y sus asistentes (RF-067). Fecha, lugar y asistentes **no se duplican** aquí: se leen de
  `meetings` y `meeting_attendances`.
- La redacta el secretario del comité, o el presidente si no hay secretario (CL-20). El presidente
  la cierra (RF-069, decisión del 2026-10-24).
- Al cerrar se asigna `number` —el siguiente del comité para ese año—, se copia
  `committee_name_at_close` y se sellan `closed_by_user_id` y `closed_at` (RF-070).
- Con `status` = `closed` la fila **no se edita ni se borra** (RF-071). Se hace cumplir en el
  modelo, no en la interfaz, igual que `approvals`, y tiene prueba propia. Una corrección es un
  acta nueva que la haga constar (CL-22).
- Una reunión sin acta es normal: el sistema no supone que toda reunión tenga una (CL-21).

### `minute_commitments` — los compromisos que salen de la reunión

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `minute_id` | fk → `minutes` | obligatorio |
| `text` | text | el compromiso, tal como se acordó |
| `position` | int | orden dentro del acta |
| `activity_task_id` | fk → `activity_tasks`, nulo | se llena al convertirlo en tarea |

**Ámbito**: sede. Se consulta siempre desde el acta.

**Reglas**:

- Un compromiso es **texto**; convertirlo en tarea es opcional (RF-073, decisión del 2026-10-24).
- Al convertirlo, se crea la fila en `activity_tasks` con su responsable y su fecha, y se guarda
  aquí su identificador. Desde ese momento cuenta para el avance de la actividad (RF-027) y aparece
  en los pendientes de esa persona.
- Convertir no reescribe el texto del compromiso: el acta conserva lo que se acordó, palabra por
  palabra.

---

## 6 ter. Archivos adjuntos

Entran por la ampliación del 2026-10-24 (RF-075 a RF-080, HU-23 y HU-24).

### `attachments` — evidencias de actividad y soportes de gasto

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | el comité dueño, copiado del objeto apuntado para poder filtrar sin unir |
| `attachable_type`, `attachable_id` | relación polimórfica | `Activity` (evidencia) o `BudgetMovement` (soporte) |
| `kind` | enum | `evidence` \| `receipt` |
| `original_name` | string | el nombre con que la persona lo subió |
| `path` | string | ruta en el disco del servidor del contrato |
| `mime` | string | tipo del archivo |
| `bytes` | int | peso después de comprimir |
| `uploaded_by_user_id` | fk → `users` | quién lo subió (RF-076) |
| `replaced_by_id` | fk → `attachments`, nulo | cuando se reemplazó, apunta al que lo sustituye |

**Ámbito**: sede+comité. Los archivos **no** viajan por la excepción del cronograma compartido
(RF-080): los ve el comité dueño, sus ascendientes y el pastor. Tampoco salen en ninguna descarga
de reporte fuera del ámbito.

**Reglas**:

- Tipos admitidos: imagen (JPEG, PNG, WebP) y PDF. Nada ejecutable.
- **10 MB como máximo** después de comprimir. Por encima, el sistema lo rechaza con una frase que
  dice cuánto pesa y cuál es el límite, **sin perder lo que la persona ya escribió** en el
  formulario (RF-076, CL-23).
- Las imágenes se reducen al recibirlas, sin que la persona haga nada, para no gastarle los datos
  del celular (RF-077).
- Se guardan en el **disco del servidor del contrato**, fuera de la carpeta pública: se sirven por
  una ruta que pasa por la policy, nunca por URL directa. No se copian a ningún servicio externo
  (RF-078, constitución: los datos del cliente no salen del servidor acordado).
- Un archivo adjunto a algo ya cumplido o ya autorizado **no se borra**: se sube el correcto y el
  anterior queda con `replaced_by_id`, conservado en la historia (RF-079, CL-25).
- Una actividad o un movimiento sin archivos es normal, no un error (CL-26).
- El **respaldo diario debe incluir estos archivos**, no solo la base de datos (RF-078, DEP-07).
  Sin eso, una falla de disco pierde las evidencias aunque la base se recupere.

---

## 7. Presupuesto

### `budgets` — lo asignado por año

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | comité o sub-comité |
| `year` | smallint | único junto con `committee_id` |
| `assigned_amount` | decimal(14,2) | mayor o igual a cero |
| `assigned_by_user_id` | fk → `users` | |

**Ámbito**: sede+comité. El pastor asigna a los comités; el presidente reparte creando filas para
sus hijos (RF-050). Un comité sin presupuesto asignado funciona en cero, no falla (CL-04).

### `budget_movements`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | obligatorio |
| `activity_id` | fk → `activities`, nulo | |
| `moved_at` | date | obligatorio |
| `concept` | string | obligatorio |
| `amount` | decimal(14,2) | mayor que cero |
| `type` | enum | `expense` \| `allocation` \| `income` |
| `receipt` | string, nulo | soporte (RF-052) |
| `authorized_by_user_id` | fk → `users` | quién lo autorizó (RF-052) |

**Ámbito**: sede+comité.

**Reglas**:

- El **saldo se calcula**, no se guarda: asignado − gastado (D-10).
- Excederlo **advierte pero no bloquea**, y permite continuar si se confirma (RF-051, RN-05).
- Un movimiento registrado no se borra (RN-07). Una corrección es un movimiento nuevo.
- Toda pantalla de presupuesto muestra la aclaración de que la tesorería sigue manejando los
  recursos (RF-053, HU-16 escenario 5).

---

## 8. Bitácora

`activity_log`, la tabla del paquete. Registra el modelo apuntado, el cambio, los valores anterior
y nuevo, el autor y la fecha. Se activa en: `members`, `activities`, `approvals`,
`budget_movements`, `meeting_attendances`, `sympathizers`, `committee_user`.

**Ámbito: ninguno, y por eso tiene reglas especiales** (V-01, R-05):

1. Ninguna pantalla consulta `activity_log` directamente.
2. El historial se lee **siempre** a través del modelo apuntado, que sí carga el ámbito.
3. `ScopeCoverageTest` mantiene una lista blanca documentada con esa única tabla, y falla si
   alguien agrega otra sin justificarla en [research.md](./research.md).

---

## 9. Resumen de cobertura del filtro de alcance

| Tabla | `site_id` | Filtro del cargo activo | Nota |
|---|---|---|---|
| `sites` | — | — | raíz |
| `site_mottos` | sí | — | el lema es de toda la sede |
| `users` | sí | — | el pastor administra; la policy restringe |
| `committees` | sí | **sí** | |
| `committee_user` | sí | — | se consulta siempre desde el usuario |
| `members` | sí | **sí** | por sus pertenencias (D-32); el buscador `forPicker()` es la excepción 4 |
| `committee_member` | sí | **sí** | |
| `sympathizers` | sí | **sí** | BIS ve a todos (excepción 2); los demás, los asignados a su ámbito |
| `sympathizer_assignments` | sí | — | se consulta desde el simpatizante |
| `sympathizer_stages` | sí | — | se consulta desde el simpatizante |
| `sympathizer_visits` | sí | — | se consulta desde el simpatizante |
| `annual_plans` | sí | — | información de toda la sede |
| `schedules` | sí | **sí** | el general lo ve todo cargo por la excepción 1; los internos, nunca fuera de su comité |
| `activity_series` | sí | **sí** | por su cronograma |
| `activities` | sí | **sí** | con la excepción 1, `forSharedCalendar()`, solo para el cronograma general |
| `activity_types` | sí | — | las clases son de toda la congregación |
| `activity_tasks` | sí | — | se consulta desde la actividad |
| `approvals` | sí | **sí** | |
| `attendances` | sí | — | se consulta desde la actividad |
| `service_orders` | sí | **sí** | detalle reservado de la actividad (RF-094) |
| `service_order_parts` | sí | — | se consulta desde el orden |
| `worship_roles` | sí | **sí** | |
| `worship_assignments` | sí | — | se consulta desde la actividad |
| `beneficiaries` | sí | **sí** | solo Obra Social y el pastor |
| `aid_deliveries` | sí | **sí** | solo Obra Social y el pastor |
| `fund_movements` | sí | **sí** | solo Obra Social y el pastor |
| `class_groups` | sí | **sí** | solo Escuela Dominical y el pastor |
| `children` | sí | **sí** | solo Escuela Dominical y el pastor; los datos de salud, por policy |
| `child_consents` | sí | **sí** | inmutables al firmarse |
| `class_sessions` | sí | **sí** | |
| `sunday_school_visits` | sí | **sí** | |
| `minutes` | sí | **sí** | no viajan por la excepción del cronograma (RF-074) |
| `minute_commitments` | sí | — | se consulta desde el acta |
| `attachments` | sí | **sí** | no viajan por la excepción del cronograma (RF-080); se sirven por ruta con policy, nunca por URL pública |
| `budgets` | sí | **sí** | |
| `budget_movements` | sí | **sí** | |
| `activity_log` | **no** | **no** | del paquete; reglas especiales de la sección 8 |

`Architecture/ScopeCoverageTest` recorre los modelos del proyecto y falla si uno con `site_id` no
usa `BelongsToSite`, o si uno marcado como de ámbito de comité no usa `BelongsToCommitteeScope`. Un
modelo nuevo mal hecho rompe la construcción el mismo día.

### Lo que cambia al pasar a tres niveles

El filtro de alcance del principio I deja de resolverse con «el nodo y sus hijos» y pasa a «el nodo
y todos sus descendientes». Es la pieza más delicada de la ampliación del 2026-10-24, y toca:

| Qué | Cómo cambia |
|---|---|
| `Support/Scope/CurrentScope.php` | De «X y sus hijos» a «X y todo su sub-árbol». Con `depth` acotado a 3, el sub-árbol se resuelve en **una sola consulta** sin recursión: los hijos y los nietos del nodo activo |
| `Models/Concerns/BelongsToCommitteeScope.php` | El global scope compara contra el conjunto de descendientes, no contra `parent_id` |
| Las cuatro policies con ámbito | «es mi comité o mi hijo» pasa a «está en mi sub-árbol» |
| `tests/Feature/Scope/` | **Todas** las pruebas de aislamiento se rehacen con tres niveles: un líder de equipo no ve a su sub-comité hermano, y un presidente sí ve a sus nietos |
| `ScopeCoverageTest` | Sin cambio de forma; cubre las tres tablas nuevas automáticamente |

No hay consultas recursivas ni CTE: la profundidad está acotada a tres por RF-011, así que el
sub-árbol de cualquier nodo se obtiene con una comparación sobre `parent_id` y el `parent_id` del
padre. Esto mantiene el presupuesto de cinco consultas por pantalla.

> El sub-árbol de dos niveles es un caso particular del de tres: lo de esta sección sigue valiendo
> después del 2026-10-28, con un nivel menos (D-31).

### Lo que cambia con el levantamiento del 2026-10-28

| Qué | Cómo cambia |
|---|---|
| `committees` | Dos niveles; BIS se marca con `receives_sympathizers`; logo, lema y objetivos |
| `members` | Sin `committee_id` ni dirección; gana `code` y `notes`; el ámbito sale de `committee_member` |
| `Models/Concerns/BelongsToCommitteeScope.php` | Para `Member`, «tiene una pertenencia dentro del ámbito», con una sola subconsulta (D-32) |
| `sympathizers` | `committee_id` es el comité asignado; el filtro deja pasar todo a BIS (D-34) |
| `activities` | Gana `schedule_id`, `series_id` y `ends_at`; el cruce del general se impide (D-37) |
| `forSharedCalendar()` | Seis datos, y solo del cronograma general (D-36) |
| Asistencia | Por actividad, en `attendances`; `meetings` no se construye (D-40) |

---

## 10. Orden de culto y Alabanza

### `service_orders` — el orden de un culto

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | el comité del culto |
| `activity_id` | fk → `activities` | obligatorio, único; una actividad general de la clase «Culto» (D-41) |
| `completed_at` | datetime, nulo | cuando todas sus partes tienen responsable |

### `service_order_parts`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `service_order_id` | fk → `service_orders` | obligatorio |
| `position` | tinyint | orden de la parte |
| `name` | string | apertura, himno, oración y peticiones, ofrenda, predicación, u otra |
| `member_id` | fk → `members`, nulo | un integrante del comité |
| `person_name` | string, nulo | alguien de afuera, escrito a mano |

**Reglas**: las cinco partes de siempre se crean con el orden (RF-092). El pendiente de los quince
días y el atraso de los ocho se calculan en `ServiceOrderDeadline` (RF-093, P-04).

### `worship_roles` y `worship_assignments` — quién toca y quién canta

`worship_roles`: `id`, `site_id`, `committee_id` (Alabanza), `name` —piano, batería, voz principal,
voz de apoyo, y las que se agreguen—, `active`. `worship_assignments`: `id`, `site_id`,
`activity_id`, `worship_role_id`, `member_id`. Una persona con dos funciones en la misma actividad
se advierte, sin impedirlo (RF-103, CL-41).

---

## 11. Obra Social

### `beneficiaries` — a quién ayuda Obra Social

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Obra Social |
| `name` | string | obligatorio |
| `phone` | string, nulo | |
| `neighborhood` | string, nulo | |
| `condition` | enum | `head_of_household_mother` \| `senior` \| `other` |
| `is_member` | bool | hermano de la congregación o amigo; no lee el padrón (RF-098) |
| `active` | bool | |

### `aid_deliveries` — lo que se entregó

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Obra Social |
| `beneficiary_id` | fk → `beneficiaries` | obligatorio, `restrict` |
| `delivered_on` | date | obligatorio |
| `type` | enum | `groceries` \| `money` \| `goods` \| `visit` \| `other` |
| `amount` | decimal(14,2), nulo | el valor en pesos, cuando lo hay |
| `description` | string, nulo | lo que se entregó |
| `justification` | string, nulo | |
| `recorded_by_user_id` | fk → `users` | `restrict` |

La foto opcional va en `attachments` (RF-099). Una ayuda en dinero genera su salida en
`fund_movements`.

### `fund_movements` — el fondo propio de Obra Social

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Obra Social |
| `direction` | enum | `in` \| `out` |
| `moved_on` | date | obligatorio |
| `amount` | decimal(14,2), nulo | en dinero |
| `goods` | string, nulo | en elementos |
| `contributor_name` | string, nulo | quién aportó, en las entradas |
| `member_id` | fk → `members`, nulo | cuando aportó un integrante del comité |
| `aid_delivery_id` | fk → `aid_deliveries`, nulo | la ayuda que originó la salida |
| `concept` | string | obligatorio |
| `recorded_by_user_id` | fk → `users` | `restrict` |

**Reglas**: independiente de `budgets` y `budget_movements` (RF-101, RN-14). Un saldo negativo se
advierte y se permite si se confirma (CL-39). Corregir conserva el valor anterior (RF-056).

---

## 12. Escuela Dominical

### `class_groups` — los grupos por edad

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Escuela Dominical |
| `name` | string | obligatorio |
| `min_age`, `max_age` | tinyint | 0..17; el sistema advierte rangos que se pisan o edades sin grupo (CL-35) |
| `teacher_member_id` | fk → `members`, nulo | un maestro del comité (`committee_member.role` = `teacher`) |
| `assistant_member_id` | fk → `members`, nulo | un auxiliar del comité |
| `active` | bool | |

La semilla crea los cinco grupos del levantamiento (RF-105, DEP-10).

### `children` — los niños inscritos

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Escuela Dominical |
| `first_names`, `last_names` | string | obligatorios |
| `document_number` | string, nulo | |
| `birth_date` | date | obligatoria: de ella sale el grupo (RF-108) |
| `birth_place`, `gender` | | |
| `father_name`, `father_phone`, `mother_name`, `mother_phone` | string, nulo | |
| `only_child`, `siblings_count`, `sibling_position` | | |
| `school`, `school_level`, `grade`, `school_shift`, `other_studies`, `favorite_sport`, `free_time` | string, nulo | estudios |
| `vaccination_card`, `weight`, `height`, `blood_type`, `medicine_allergies`, `forbidden_foods`, `special_condition`, `suggested_care` | nulos | **salud**: solo la Escuela Dominical y el pastor (RF-107) |
| `parents_ipuc` | enum, nulo | `both` \| `father` \| `mother` \| `none` |
| `likes` | json, nulo | cantar, predicar, dramas, dibujar, declamar, otro |
| `notes` | text, nulo | |
| `enrolled_on` | date | |
| `aged_out_at` | date, nulo | el día que cumplió dieciocho años (RF-108, CL-34) |
| `active` | bool | |

**No hay columna de grupo**: `ClassGroupFor` lo calcula (D-46). La dirección queda pendiente de P-01.
La foto va en `attachments`, y solo si hay autorización de imagen vigente (RF-110, CL-37).

### `child_consents` — las autorizaciones firmadas

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | Escuela Dominical |
| `child_id` | fk → `children` | obligatorio, `restrict` |
| `kind` | enum | `participation` \| `image` |
| `covers` | json, nulo | en la de imagen: fotos, audios, videos, otros datos |
| `guardian_name`, `guardian_document`, `relationship` | string | obligatorios |
| `signed_on` | date | obligatoria |
| `signature_path` | string, nulo | la firma dibujada (D-45), o nulo si se subió el papel firmado |
| `collected_by_user_id` | fk → `users` | `restrict` |

**Inmutable al guardarse**, como las actas: un cambio se hace firmando una nueva, y la vigente es la
más reciente de cada clase (RF-109).

### `class_sessions` — el cronograma de clases de un grupo

`id`, `site_id`, `committee_id`, `class_group_id`, `notebook_on` (fecha del cuaderno), `taught_on`
(fecha de la clase), `topic`, `teacher_member_id`, `assistant_member_id`, `cleaning`, `no_class`
(bool), `no_class_reason`. Una clase con `no_class` no cuenta para la asistencia (RF-111, RF-112).
La asistencia por niño va en `attendances` con `child_id`, sobre la actividad del cronograma
interno que la clase genera.

### `sunday_school_visits` — las visitas

`id`, `site_id`, `committee_id`, `class_group_id`, `visited_on`, `kind` (`teacher` \| `children`),
`visited_member_id` (el maestro visitado), `visited_children` (json de ids, en la visita a niños),
`visiting_members` (json de ids de los maestros que fueron), `attendance`, `need_found`, `novelty`.
La foto opcional va en `attachments` (RF-113).
