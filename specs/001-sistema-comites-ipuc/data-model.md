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
| `name` | string | obligatorio |
| `city` | string | obligatorio |
| `active` | bool | por omisión verdadero |

**Ámbito**: ninguno (es la raíz). Una sola fila en operación; existe desde el día 1 por mandato de
la constitución, para que agregar una segunda sede no sea una migración de datos.

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

### `committees` — los tres niveles en una sola tabla

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `parent_id` | fk → `committees`, nulo | nulo = comité; con padre sin abuelo = sub-comité; con abuelo = equipo |
| `depth` | tinyint | 1, 2 o 3, derivado de `parent_id` y guardado para poder validar y consultar sin recorrer el árbol |
| `name` | string | obligatorio, único por sede y por padre |
| `slug` | string | único por sede |
| `mission` | text, nulo | |
| `color`, `icon` | string, nulo | presentación |
| `position` | int | orden de despliegue |
| `active` | bool | por omisión verdadero |

**Ámbito**: sede+comité. Los tres niveles viven en una sola tabla: un solo filtro, una sola policy,
un solo juego de pantallas (D-02). Un nodo sin hijos resuelve CL-01 sin código especial: la ficha
consulta si tiene hijos y, si no, lo dice expresamente (RF-012).

**Regla de profundidad**: **tres niveles** (RF-011, decisión del 2026-10-24). `depth` 1 es comité,
2 es sub-comité, 3 es equipo. Un `parent_id` que apunte a un nodo de `depth` 3 es inválido: se
valida en el formulario, en el modelo y en una prueba (CL-17). `depth` se calcula al guardar, nunca
se escribe a mano.

**Administración** (RF-063 a RF-066, HU-21): solo el pastor crea, renombra, reordena e inactiva
nodos. Un nodo nunca se borra: `active` en falso. Inactivarlo conserva toda su historia
consultable, lo saca de los desplegables para proponer cosas nuevas, y deja a sus miembros con
`committee_id` nulo, señalados para reasignar (RF-064, CL-18). El nombre vigente vive en esta
tabla; la historia conserva el nombre de su época por la bitácora y por el nombre que las actas
guardan al cerrarse (RF-065, CL-19).

> **Nota de alcance.** El sistema no supone ninguna cantidad ni ningún nombre de comité. La
> estructura inicial la carga el pastor (DEP-05). La investigación del 2026-10-24 confirmó que ni el
> Distrito 28 ni la sede de Ibagué publican su organigrama, así que no hay lista canónica que
> sembrar: la semilla trae datos ficticios de demostración, no la estructura real.

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
- `role` = `president` solo si `depth` es 1; `leader` solo si `depth` es 2 o 3. `secretary` y
  `treasurer` solo en `depth` 1: un sub-comité o un equipo no lleva libro de actas propio ni maneja
  presupuesto aparte del de su comité.
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
| `committee_id` | fk → `committees`, nulo | nulo = sin comité (CL-03) |
| `name` | string | obligatorio |
| `birth_date` | date, nulo | fecha de nacimiento; nulo cuando la congregación no la tiene (RF-031) |
| `age_reported` | int, nulo | edad declarada, solo cuando no se sabe la fecha (RF-031) |
| `age_reported_at` | date, nulo | fecha de la declaración; **obligatoria si `age_reported`** |
| `gender` | enum | `f` \| `m` |
| `marital_status` | enum | `single` \| `married` \| `widowed` \| `separated` \| `common_law` |
| `baptized` | bool | por omisión falso |
| `baptized_at` | date, nulo | **obligatorio si `baptized`** (RF-033) |
| `joined_at` | date, nulo | fecha de ingreso |
| `phone` | string, nulo | |
| `neighborhood` | string, nulo | |
| `adult_confirmed` | bool | verdadero cuando se confirmó que es mayor de dieciocho años sin fecha ni edad anotada (RF-032) |
| `adult_confirmed_at` | date, nulo | obligatoria si `adult_confirmed` |
| `profile_pending` | bool | verdadero en la ficha mínima creada al habilitar una cuenta (V-03) |
| `data_consent` | bool | autorizó el manejo de sus datos (RF-061) |
| `data_consent_at` | date, nulo | obligatorio si `data_consent` |
| `data_consent_means` | enum, nulo | `signature` \| `verbal` \| `message`; obligatorio si `data_consent` |
| `active` | bool | por omisión verdadero |

**Ámbito**: sede+comité.

**Reglas de validación**:

- `committee_id` apunta a **cualquiera de los tres niveles**: comité, sub-comité o equipo (RF-014).
  Un solo campo cubre los tres casos, y `depth` del nodo dice en cuál está la persona. Nulo = sin
  nodo asignado (CL-02, CL-03), que es también el estado en que quedan los miembros de un nodo
  inactivado (RF-064, CL-18).
- **La edad se calcula, nunca se guarda suelta.** Sale de `birth_date` cuando existe; cuando no,
  del par `age_reported` + `age_reported_at`, que no envejece porque lleva su fecha de referencia.
  El formulario pide fecha de nacimiento y, cuando no se sabe, ofrece «No la sé — anote la edad»,
  que guarda la edad declarada con la fecha en que se declaró. Las dos entradas son válidas y
  **nunca se piden las dos a la vez**.
- Los reportes por rango de edad usan **las dos fuentes**, así que el padrón entra completo en los
  cortes, e informan que una parte tiene precisión de ±1 año (RF-036).
- Menor de dieciocho años no puede ser `married`, `widowed`, `separated` ni `common_law` (RF-032).
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

---

## 3. Simpatizantes

### `sympathizers`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | **el Comité de Evangelismo** (comité padre), no un sub-comité |
| `name` | string | obligatorio |
| `birth_date` | date, nulo | |
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

**Ámbito**: sede+comité, con la policy más estrecha del proyecto: la ficha de seguimiento la ve y la
actualiza **solo el presidente del Comité de Evangelismo**, más el pastor (RF-040, R-06). Ni los
líderes de los sub-comités de Evangelismo, ni quien registró a la persona.

**No existe `assigned_to_user_id`.** Repartir simpatizantes entre líderes de sub-comité fue
descartado en la aclaración; dejar el campo invitaría a construirlo (R-06).

**Reglas**:

- Al registrar, si ya existe alguien con el mismo nombre y teléfono, el sistema **advierte** y
  permite continuar o cancelar (CL-09, HU-13 escenario 3). No bloquea: dos personas pueden
  llamarse igual.
- La autorización de tratamiento de datos se guarda igual que en `members`: `data_consent`,
  `data_consent_at`, `data_consent_means`, con las mismas reglas (RF-061).
- Al aprobarse el paso a miembro (RF-045), se crea la fila en `members` conservando la fecha de
  bautismo y la de primer contacto, y se llenan `converted_member_id` y `converted_at`. La fila del
  simpatizante **no se borra**: su historia se conserva (CL-10).

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
| `title` | string | obligatorio |
| `description` | text, nulo | |
| `scheduled_at` | datetime | obligatorio, dentro del año en curso, no pasada (CL-07) |
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

**Ámbito**: sede+comité, **con la única excepción que enumera la constitución** —la enmienda del
principio I del 2026-10-18, constitución v2.0.0—: `Activity::forSharedCalendar()` retira el filtro
de comité y selecciona a mano solo los siete datos que RF-010 permite compartir —título,
descripción, fecha, hora, lugar, comité responsable y estado; es decir `title`, `description`,
`scheduled_at`, `location`, `status` y el nombre del comité responsable—. Nunca devuelve
`budget_amount` ni carga tareas (R-11, D-15).

Tres condiciones hacen parte de la excepción, y quien escriba la migración, el modelo o el
controlador las lee aquí:

- **El método solo lee.** No se usa como base de un `update`, de un `delete` ni de una descarga.
- **No concede autorización.** La policy de `Activity` sigue negando toda escritura sobre una
  actividad ajena.
- **El filtro de sede no se retira nunca.** `BelongsToSite` sigue puesto; lo único que se retira es
  el filtro del cargo activo.

El **nombre del comité responsable sí se comparte**: es uno de los siete datos enumerados. La
reserva de RF-009 se refiere a los conteos de `scopeHidden`, que nunca listan cuáles son los
comités ajenos; no convierte el nombre de un comité en dato reservado.

**Qué hay que probar**: una prueba de **lista blanca**. Se compara el juego completo de llaves que
trae una actividad ajena contra esos siete datos y falla si aparece cualquier otro —no busca llaves
prohibidas—, así que una columna nueva en `activities` deja la prueba roja hasta que alguien decida
si se comparte. Y un caso de **escritura**: un presidente que intenta modificar una actividad ajena
recibe «no disponible».

**Aviso de cruce**: al proponer, el sistema advierte si ya existe otra actividad el mismo día
indicando cuál y de qué comité (RF-028), y señala el choque exacto de día, hora y lugar (CL-08). Es
un aviso, no un bloqueo: la decisión de insistir es de las personas.

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

### `meetings`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `committee_id` | fk → `committees` | obligatorio |
| `held_at` | datetime | obligatorio, no futura |
| `type` | enum | `rehearsal` \| `meeting` \| `service` |
| `topic` | string, nulo | |
| `recorded_by_user_id` | fk → `users` | |

**Ámbito**: sede+comité. Una reunión sin ningún asistente se puede registrar, porque es información
(CL-11).

### `meeting_attendances`

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `meeting_id` | fk → `meetings` | obligatorio |
| `member_id` | fk → `members` | obligatorio |
| `present` | bool | |

**Ámbito**: sede. Una fila por **convocado**, presente o no: de ahí sale el denominador.

**Reglas**:

- Un convocado solo puede ser miembro de ese comité o sub-comité (RF-047): se valida en el
  formulario y en una prueba.
- Una sola fila por reunión y miembro.
- El porcentaje por persona se calcula sobre las reuniones a las que **fue convocada** (RF-048), no
  sobre todas las del comité.
- Corregir una marca conserva lo que se había registrado antes, por la bitácora (HU-15 escenario 2,
  RF-056).
- Por debajo del sesenta por ciento, la interfaz lo señala con texto de acompañamiento, nunca de
  reproche (RF-049).

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
| `users` | sí | — | el pastor administra; la policy restringe |
| `committees` | sí | **sí** | |
| `committee_user` | sí | — | se consulta siempre desde el usuario |
| `members` | sí | **sí** | |
| `sympathizers` | sí | **sí** | policy más estrecha: solo presidente de Evangelismo y pastor |
| `sympathizer_stages` | sí | — | se consulta desde el simpatizante |
| `sympathizer_visits` | sí | — | se consulta desde el simpatizante |
| `annual_plans` | sí | — | información de toda la sede |
| `activities` | sí | **sí** | con la única excepción `forSharedCalendar()` |
| `activity_types` | sí | — | las clases son de toda la congregación |
| `activity_tasks` | sí | — | se consulta desde la actividad |
| `approvals` | sí | **sí** | |
| `meetings` | sí | **sí** | |
| `meeting_attendances` | sí | — | se consulta desde la reunión |
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
