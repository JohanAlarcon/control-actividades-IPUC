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

### `committees` — comités y sub-comités en una sola tabla

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `parent_id` | fk → `committees`, nulo | nulo = comité principal; con valor = sub-comité |
| `name` | string | obligatorio, único por sede y por padre |
| `slug` | string | único por sede |
| `mission` | text, nulo | |
| `color`, `icon` | string, nulo | presentación |
| `position` | int | orden de despliegue |
| `active` | bool | por omisión verdadero |

**Ámbito**: sede+comité. Un sub-comité es un comité con padre: un solo filtro, una sola policy, un
solo juego de pantallas (D-02). Un comité sin hijos resuelve CL-01 sin código especial: la ficha
consulta si tiene hijos y, si no, lo dice expresamente (RF-012).

**Regla de profundidad**: dos niveles. `parent_id` de un comité que ya tiene padre es inválido;
se valida en el formulario y en una prueba.

### `committee_user` — los cargos, con vigencia

| Columna | Tipo | Reglas |
|---|---|---|
| `id` | id | |
| `site_id` | fk → `sites` | obligatorio |
| `user_id` | fk → `users` | obligatorio |
| `committee_id` | fk → `committees` | obligatorio |
| `role` | enum | `president` \| `leader` |
| `started_at` | date | obligatorio |
| `ended_at` | date, nulo | nulo = cargo vigente |

**Ámbito**: sede.

**Reglas**:

- Un comité o sub-comité tiene **un único responsable vigente** (RF-013): no puede haber dos filas
  con el mismo `committee_id` y `ended_at` nulo.
- `role` = `president` solo si el comité no tiene padre; `leader` solo si lo tiene.
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

- `committee_id` apunta al comité **o** al sub-comité. Si el nodo tiene padre, la persona está en
  un sub-comité; si no, está en un comité sin sub-comité (CL-02). Un solo campo cubre los dos
  casos.
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
| `type` | enum | `service` \| `rehearsal` \| `event` \| `outing` \| `campaign` \| `convention` \| `meeting` \| `visit` |
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
reserva de RF-009 se refiere a los conteos de `scope.hidden`, que nunca listan cuáles son los
comités ajenos; no convierte el nombre de un comité en dato reservado.

**Qué hay que probar**: una prueba de **lista blanca**. Se compara el juego completo de llaves que
trae una actividad ajena contra esos siete datos y falla si aparece cualquier otro —no busca llaves
prohibidas—, así que una columna nueva en `activities` deja la prueba roja hasta que alguien decida
si se comparte. Y un caso de **escritura**: un presidente que intenta modificar una actividad ajena
recibe «no disponible».

**Aviso de cruce**: al proponer, el sistema advierte si ya existe otra actividad el mismo día
indicando cuál y de qué comité (RF-028), y señala el choque exacto de día, hora y lugar (CL-08). Es
un aviso, no un bloqueo: la decisión de insistir es de las personas.

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
| `level` | enum | `president` \| `pastor` — quién debe resolver **ahora**; nunca `pastor` si `type` = `expense` |
| `status` | enum | `pending` \| `approved` \| `rejected` |
| `title` | string | obligatorio |
| `detail` | text, nulo | |
| `amount` | decimal(14,2), nulo | obligatorio si `type` = `expense` |
| `requested_by_user_id` | fk → `users` | |
| `requested_at` | datetime | de aquí sale «desde cuándo espera» (RF-054) |
| `resolved_by_user_id` | fk → `users`, nulo | |
| `resolved_at` | datetime, nulo | |
| `reason` | text, nulo | **obligatorio si `status` = `rejected`** (RF-020) |
| `transferred_from_user_id` | fk → `users`, nulo | traspaso por cargo vacante (RF-023, CL-06) |
| `transferred_at` | datetime, nulo | |

**Ámbito**: sede+comité.

**Regla de inmutabilidad**: una fila con `status` distinto de `pending` **no se edita ni se borra**
(RF-021, RF-057, RN-07). Corregir significa registrar una decisión nueva. Se hace cumplir en el
modelo, no solo en la interfaz, y tiene prueba propia.

**Combinación inválida**: `type` = `expense` con `level` = `pastor` **no existe**. Tras la
aclaración de RF-018 ningún gasto llega al pastor: el del líder lo cierra su presidente, y el del
presidente lo autoriza él mismo sin solicitud. Se hace cumplir en dos sitios:

1. `ApprovalFlow` es el único lugar que crea filas, y nunca asigna ese par.
2. El modelo `Approval` lo rechaza al guardar, igual que la regla de inmutabilidad.

No lleva restricción `check` en la migración: el escalamiento es la regla más volátil del proyecto
—ya se movió una vez con V-04— y llevarla a la base la congela donde es más caro cambiarla.

**Qué hay que probar**: crear una solicitud con `type` = `expense` y `level` = `pastor` falla en el
modelo `Approval`. Y un gasto propuesto por un líder y aprobado por su presidente **cierra** en ese
paso: no queda `pending` esperando al pastor (RF-018).

### El escalamiento — `Support/Approvals/ApprovalFlow.php`

Un solo lugar donde vive la cadena. Incluye RF-018, que el plan de entrada no contemplaba (V-04).

```
Al crear la solicitud:
  la propone un líder                → level = president
  la propone un presidente:
      type = expense de su comité    → NO se crea solicitud: él autoriza y se registra
                                       el movimiento con su nombre y la fecha (RF-018)
      type = activity                → level = pastor
      type = task | date_change      → level = pastor
      type = new_member              → level = pastor (solo el presidente de Evangelismo)

Al resolver:
  presidente aprueba type = activity → NO cierra: level pasa a pastor, status sigue pending,
                                       y se avisa a quien la propuso (HU-06 escenario 2)
  presidente aprueba expense | task | date_change → cierra
  presidente rechaza                 → cierra con motivo obligatorio
  pastor aprueba                     → cierra y aplica el efecto sobre el objeto apuntado
  pastor rechaza                     → cierra con motivo obligatorio
```

El pastor puede resolver varias solicitudes del mismo tipo en una sola acción, confirmando una vez
(RF-022, HU-07 escenario 4).

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
| `activity_tasks` | sí | — | se consulta desde la actividad |
| `approvals` | sí | **sí** | |
| `meetings` | sí | **sí** | |
| `meeting_attendances` | sí | — | se consulta desde la reunión |
| `budgets` | sí | **sí** | |
| `budget_movements` | sí | **sí** | |
| `activity_log` | **no** | **no** | del paquete; reglas especiales de la sección 8 |

`Architecture/ScopeCoverageTest` recorre los modelos del proyecto y falla si uno con `site_id` no
usa `BelongsToSite`, o si uno marcado como de ámbito de comité no usa `BelongsToCommitteeScope`. Un
modelo nuevo mal hecho rompe la construcción el mismo día.
