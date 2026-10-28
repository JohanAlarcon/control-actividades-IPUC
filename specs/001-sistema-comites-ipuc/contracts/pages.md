# Contrato — Pantallas, ruta por ruta

Rutas en inglés (R-03, corrección C-02). Toda respuesta incluye además el bloque compartido de
[shared-props.md](./shared-props.md), salvo `scopeHidden` y `pendingCount`, que son propiedades
diferidas y viajan en su propia petición parcial. La columna **No debe viajar** es la que la prueba
de aislamiento verifica sobre la carga de la respuesta.

El ámbito de un cargo es **su nodo y todo su sub-árbol**, en los dos niveles —comité y sub-comité—
desde el levantamiento del 2026-10-28. Los miembros entran en el ámbito por sus pertenencias (D-32),
y BIS ve a todos los simpatizantes (D-34). Las cuatro excepciones al filtro son las que enumera el
principio I de la constitución 3.0.0, y cada una tiene su sección aquí.

## Entrar al sistema

| Ruta | Método | Pantalla | Propiedades propias | Permiso | No debe viajar |
|---|---|---|---|---|---|
| `/login` | GET, POST | `Auth/Login` | — | público | nada del dominio |
| `/password/change` | GET, POST | `Auth/ChangePassword` | `forced` (bool) | autenticado | nada hasta que cambie la clave |
| `/logout` | POST | — | — | autenticado | — |

**Reglas**: con `must_change_password` verdadero, **ninguna** otra ruta responde con información:
redirige al cambio de clave (RF-002, HU-01 esc. 1). Cinco intentos fallidos bloquean quince minutos
e indican pedirle al pastor el restablecimiento (RF-004).

## Inicio

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/dashboard` | GET | `dashboard` | `kpis`, `pendingApprovals[]` · **diferidas**, cada una en su petición: `myRequests[]`, `committeeProgress[]`, `upcoming[]` · `pendingProfiles` cuando existan los miembros |

**Reglas**: lo primero de la pantalla es qué espera la decisión del cargo activo y **desde cuándo**
(RF-054, HU-17 esc. 1). `myRequests[]` muestra a un líder en qué va lo que envió, quién debe
resolverlo y, si ya se resolvió, el resultado con su motivo (HU-17 esc. 3). `pendingProfiles` es el
conteo de fichas por completar del ámbito (CL-16).

Esta pantalla es una de las **dos excepciones declaradas** al presupuesto de consultas. Cada
propiedad diferida viaja en su propia petición parcial, que se mide con su propio presupuesto de
cinco consultas por respuesta y con las dos del bloque compartido incluidas
([shared-props.md](./shared-props.md)). El techo de cinco no se sube: lo que no quepa en una
petición se reparte en más propiedades diferidas.

## Comités

| Ruta | Método | Pantalla | Propiedades propias | No debe viajar |
|---|---|---|---|---|
| `/committees` | GET | `Committees/Index` | `tree[]`, `reviewer` · `cards[]` cuando existan miembros y presupuesto | de un comité fuera del ámbito: sus miembros, su presupuesto y su avance |
| `/committees/{committee}` | GET | `Committees/Show` | `committee`, `responsible`, `secretary`, `treasurer`, `children[]`, `hasChildren` · `members[]`, `budget` y `progress` cuando existan esos módulos | nada si el comité está fuera del ámbito: «no disponible» (RF-008) |

**Reglas**: con `hasChildren` falso, la ficha lo dice expresamente y muestra que el presidente
coordina directamente a sus miembros (RF-012, CL-01). Un líder ve su sub-comité y **el nombre de su
presidente**, porque es quien revisa lo que propone (HU-03 esc. 4).

El **nombre** de un comité ajeno no es dato reservado: el cronograma compartido lo publica como
responsable de una actividad (RF-010). Esta pantalla, de todos modos, no lista los comités fuera del
ámbito; lo que nunca viaja de ellos son sus **datos**: sus miembros, su presupuesto y su avance. La
reserva de RF-009 se refiere a los conteos de `scopeHidden`, que nunca dicen cuáles son; no
convierte el nombre de un comité en dato reservado.

## Aprobaciones

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/approvals` | GET | `Approvals/Index` | `inbox[]`, `escalated[]`, `history[]`, `counts` |
| `/approvals` | POST | — | crea la solicitud; aplica `ApprovalFlow` |
| `/approvals/{approval}/approve` | POST | — | resuelve o escala, según `ApprovalFlow` |
| `/approvals/{approval}/reject` | POST | — | **exige `reason`** (RF-020) |
| `/approvals/bulk-approve` | POST | — | `ids[]`: varias del mismo tipo, una sola confirmación (RF-022). Solo el pastor; todas o ninguna, cada una con su propio registro |

**Qué trae cada lista**: `inbox[]`, lo que el cargo activo resuelve; `escalated[]`, lo pendiente
que está en manos de otra persona —el pastor, o la presidencia del comité para quien no decide—;
`history[]`, las 20 decisiones más recientes de su ámbito, con quién, cuándo y el motivo si no se
aprobó; `counts` = `{ inbox, escalated }`. La pantalla hace dos consultas propias, tenga las
solicitudes que tenga: una para lo pendiente y otra para lo resuelto.

**Reglas**: cada elemento de `inbox[]` lleva de qué comité viene, quién la pidió, por quién ya pasó,
qué monto implica y qué efecto tiene en el cronograma (HU-07 esc. 1), más `waitingSince`. **Solo
el pastor aprueba** (D-24): en manos del presidente, `/approve` es su visto bueno y nunca cierra
—la solicitud pasa al pastor y se avisa a quien la propuso (HU-06 esc. 1 y 2)—, y `/reject` la
devuelve con el motivo sin que llegue al pastor (HU-06 esc. 3). El gasto del propio comité del
presidente también va al pastor (RF-018). Una solicitud resuelta no se edita: `/approve` y
`/reject` sobre una ya resuelta responden error explicado (RF-057).

## Cronograma

| Ruta | Método | Pantalla | Propiedades propias | No debe viajar |
|---|---|---|---|---|
| `/activities` | GET | `Activities/Index` | `filters`, `years`, `summary`, `months[]`, `canPropose` · **diferidas**: `annualPlan`, `committees[]` | de actividades ajenas: todo lo que no sean los seis datos compartidos (y su `id`); nada de los cronogramas internos ajenos |
| `/activities/print` | GET | `Activities/Print` | `filters` (los mismos del cronograma; `month` nulo es el año completo), `months[]` con las mismas tarjetas | lo mismo que `/activities`: cada quien imprime lo que ve (RF-117) |
| `/activities/{activity}` | GET | `Activities/Show` | `activity`, `mine`, `tasks[]`, `canEdit`, `canCheckTasks` · **diferida**: `trail[]` | si es ajena: responde como fuera del ámbito; nunca descripción, presupuesto, tareas, soporte, orden de culto ni rastro |
| `/activities/create` | GET | `Activities/Form` | `recipient`: el nombre de quien va a recibir la propuesta, o nulo si ese cargo está vacante; `types[]`: las clases activas, `{ id, name }` | — |
| `/activity-types` | GET | `ActivityTypes/Index` | `types[]`: `{ id, name, active, uses }`. Solo el pastor (D-25) | — |
| `/activity-types` | POST | — | agrega una clase; el nombre no se repite en la sede | — |
| `/activity-types/{activityType}` | PATCH | — | cambia el nombre, o desactiva y activa. Nunca borra | — |
| `/activities` | POST | — | propone; **impide** el cruce con otra actividad del cronograma general y dice cuál y de qué comité (RF-028, D-37) | — |
| `/activities/{activity}/tasks/{task}/toggle` | POST | — | recalcula `progress` y el estado (R-04) | — |
| `/activities/{activity}/tasks` | POST | — | agrega una tarea (`description`, `assigned_to`); solo el comité dueño | — |
| `/annual-plan/{year}/activities` | POST | — | carga una actividad al plan de este año o del siguiente, mientras no esté aprobado (D-27) | — |
| `/activities/{activity}/complete` | POST | — | solo para actividades sin tareas (R-04) | — |
| `/annual-plan/{year}/approve` | POST | — | solo pastor (RF-025) | — |

**Reglas**: esta es la **excepción 1** que enumera la constitución al filtro de alcance.
`forSharedCalendar()` entrega de las actividades ajenas del **cronograma general** exactamente seis
datos —título, fecha, hora de inicio y de fin, lugar, comité responsable y estado— y ningún otro
(RF-010, R-11, D-36). La descripción deja de viajar desde el 2026-10-28, y lo de los cronogramas
internos no viaja nunca. La vista de mes del calendario usa esta misma excepción (RF-029). La
prueba de aislamiento compara el **juego de llaves** de la carga con esos seis: una columna nueva
en `activities` la pone roja hasta que alguien decida si se comparte. El método **solo lee**: no se
usa como base de un `update`, un `delete` ni una descarga, y no concede autorización — la policy de
`Activity` sigue negando toda escritura sobre una actividad ajena, aunque se vea en el cronograma.
`summary` y la barra de cumplimiento se recalculan con el filtro aplicado
(RF-030, HU-09 esc. 2). El plan anual aprobado muestra quién lo aprobó y cuándo, y distingue lo que
venía en el plan de lo propuesto después (RF-024, HU-08).

La excepción está amparada por el principio I, excepción 1, de la constitución 3.0.0.

## Miembros

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/members` | GET | `Members/Index` | `members[]`, `filters`, `totalCount`, `missingConsent`, `pendingProfiles`, `canCreate`, `committees[]` (diferida) |
| `/members/create` | GET | `Members/Form` | `committees[]`, `canLeaveWithoutCommittee` |
| `/members` | POST | — | registra; avisa los años que tiene, o que su edad queda por completar |
| `/members/{member}/edit` | GET | `Members/Form` | `member`, `committees[]`, `canLeaveWithoutCommittee` |
| `/members/{member}` | PUT | — | corrige; conserva el valor anterior (RF-056) |
| `/members/{member}/history` | GET | `Members/History` | `trail[]` leído por la relación del modelo, nunca de `activity_log` (V-01) |
| `/members/picker` | GET | — (respuesta para el buscador) | `results[]`: `{ id, name, age }` y **nada más** — excepción 4 (`Member::forPicker()`, D-33) |
| `/committees/{committee}/members` | POST | — | agrega a la lista del nodo a un miembro ya registrado, con su función (RF-084, RF-085) |
| `/committees/{committee}/members/{member}` | DELETE | — | lo quita de la lista: cierra la pertenencia, no la borra |
| `/members/import` | GET, POST | `Members/Import` | `preview[]`, `errors[]`, `validCount`, `invalidCount`, `duplicates[]` |

**Reglas**: **registrar y corregir fichas es solo del pastor** desde el 2026-10-28 (RF-085, D-33):
`/members/create`, `POST /members`, `/members/{member}/edit` y `PUT` le responden a cualquier otro
cargo como fuera de su permiso. Los cargos manejan su lista con las rutas de pertenencia. El
formulario no pide la dirección (RF-031). `committees[]` solo trae los del ámbito, también para
llenar desplegables. La búsqueda
ignora mayúsculas y tildes (RF-034) y corre en el teléfono sobre `members[]`, que ya llega filtrada
por el ámbito (D-30). Cuántos cumplen —el largo de `members[]`, o lo que deja la búsqueda— se
informa sobre `totalCount` (RF-035). Solo el pastor puede dejar a alguien sin comité
(`canLeaveWithoutCommittee`, CL-03). Un líder ve solo los de su sub-comité y el sistema le indica **cuántas**
personas de la congregación no aparecen y por qué (RF-009, HU-04 esc. 3). La importación muestra
filas correctas y erróneas **antes de guardar nada**, permite cargar solo las correctas y advierte
duplicados dejando decidir (HU-18, CL-14). El formulario muestra el aviso de privacidad y pide la
autorización con su fecha y medio (RF-061, RF-062).

## Simpatizantes

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/sympathizers` | GET | `Sympathizers/Index` | `isReception` (BIS); `stats`, `funnel[]`, `list[]` con el comité asignado de cada uno, `missingConsent` |
| `/sympathizers/create` | GET | `Sympathizers/Form` | a BIS: `committees[]` activos `{ id, name }` para asignar; a los demás: nada |
| `/sympathizers/suggest` | GET | — (respuesta para la lista que se completa sola) | `results[]` solo del ámbito: `{ id, name, phone }` (RF-096) |
| `/sympathizers/{sympathizer}/assign` | POST | — | solo BIS: reasigna a otro comité y deja el asiento (RF-097) |
| `/sympathizers/{sympathizer}` | GET | `Sympathizers/Show` | `sympathizer` (con `commitment`), `stages[]`, `thresholds`, `canPromote`, `visits[]` (diferida) |
| `/sympathizers` | POST | — | registra; advierte duplicado (CL-09) |
| `/sympathizers/{sympathizer}/stages` | POST | — | alcanza una etapa con su fecha (RF-042) |
| `/sympathizers/{sympathizer}/visits` | POST | — | registra visita; recalcula el compromiso |
| `/sympathizers/{sympathizer}/promote` | POST | — | propone el paso a miembro: va al pastor (RF-019) |

**Reglas — BIS y el comité asignado** (RF-040, D-34, constitución 3.0.0, excepción 2):
- BIS ve a todos los simpatizantes y los asigna.
- Cualquier otro cargo ve solo los asignados a su ámbito, y registra a sus amigos, que quedan
  asignados a su comité.
- La lista de comités para asignar solo le llega a BIS, con id y nombre.

`commitment` llega
**calculado** por el servidor, con las señales que lo determinaron para poder explicarlo (RF-043,
HU-14 esc. 3), y `thresholds` trae los umbrales con que se calculó. El aviso de duplicado fuera
del ámbito responde sí o no: nunca trae la ficha ajena (excepción 3). Cada tarjeta dice si ya espera al pastor para pasar a
miembro. El formulario muestra el aviso de privacidad y pide la autorización (RF-061).

## Asistencia

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/attendance` | GET | `Attendance/Index` | `perPerson[]` con su porcentaje, `kpis` |
| `/activities/{activity}/attendance` | GET | `Attendance/Edit` | `activity`, `convoked[]`, `sympathizers[]` del ámbito, `marks[]`, `canEdit` |
| `/activities/{activity}/attendance` | PUT | — | guarda o corrige las marcas —asistió, no asistió, excusado con su excusa—; conserva lo registrado antes (RF-056) |

**Reglas**: la asistencia es por actividad, interna o general (RF-046, D-40). Los convocados son los
integrantes vigentes del nodo dueño (RF-047). Una actividad sin ningún asistente se puede registrar
(CL-11). El porcentaje por persona se calcula sobre las actividades a las que **fue convocada**
(RF-048). Por debajo del sesenta por ciento, el texto es de acompañamiento, nunca de reproche
(RF-049). La asistencia de un simpatizante cuenta para su compromiso (RF-091).

## Actas de reunión

| Ruta | Método | Pantalla | Propiedades propias | No debe viajar |
|---|---|---|---|---|
| `/minutes` | GET | `Minutes/Index` | `minutes[]`, `canDraft`, `canClose` | ninguna acta de un comité fuera del sub-árbol |
| `/minutes/create` | GET | `Minutes/Form` | `meeting`, `attendees[]`, `agenda`, `development`, `commitments[]` | — |
| `/minutes` | POST, PUT | — | guarda el borrador | — |
| `/minutes/{minute}` | GET | `Minutes/Show` | `minute`, `commitments[]`, `canClose`, `canConvert` | — |
| `/minutes/{minute}/close` | POST | — | asigna número y sella; solo el presidente | — |
| `/minutes/{minute}/print` | GET | `Minutes/Print` | vista de impresión (RF-072) | — |
| `/minutes/{minute}/commitments/{commitment}/to-task` | POST | — | convierte el compromiso en tarea (RF-073) | — |

**Reglas**: el acta **hereda** fecha, lugar y asistentes de la reunión; no los duplica (D-18). La
redacta el secretario, o el presidente si no hay secretario (CL-20); la cierra el presidente
(RF-069). Cerrada, `PUT` y los demás cambios responden error explicado: se corrige con un acta nueva
(RF-071, CL-22). El número se asigna **al cerrar**, por comité y por año (RF-070). Convertir un
compromiso **no reescribe su texto**: el acta conserva lo acordado palabra por palabra (D-19). Las
actas siguen el filtro completo y **no viajan** por la excepción del cronograma (RF-074).

## Archivos adjuntos

| Ruta | Método | Pantalla | Propiedades propias | No debe viajar |
|---|---|---|---|---|
| `/attachments` | POST | — | sube una evidencia o un soporte; devuelve `warning` si pasa de 10 MB | — |
| `/attachments/{attachment}` | GET | — | entrega el archivo **pasando por la policy** | ningún archivo de un comité fuera del sub-árbol |
| `/attachments/{attachment}/replace` | POST | — | sube el correcto y marca el anterior como reemplazado (RF-079) | — |

**Reglas**: los archivos **no se sirven desde `public/`** (D-17). Se guardan fuera de la carpeta
pública y se entregan por esta ruta, que resuelve la policy antes de leer el disco: con una URL
pública, cualquiera con el enlace vería una evidencia ajena y el principio I se caería por la puerta
de atrás sin que ninguna prueba de aislamiento lo note. Tipos admitidos: JPEG, PNG, WebP y PDF;
nada ejecutable. 10 MB como máximo después de comprimir, y las imágenes se reducen al recibirlas
(RF-076, RF-077). Un archivo adjunto a algo ya cumplido o autorizado **no se borra**: se reemplaza
(RF-079, CL-25). **No viajan** por la excepción del cronograma ni en ninguna descarga de reporte
fuera del ámbito (RF-080).

## Estructura de comités

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/structure` | GET | `Structure/Index` | `tree[]`, `canManage` |
| `/structure` | POST | — | crea un nodo en el nivel que corresponda |
| `/structure/{committee}` | PUT | — | renombra, reordena o cambia de padre |
| `/structure/{committee}/deactivate` | POST | — | inactiva conservando la historia (RF-064) |

**Reglas**: **solo el pastor** (RF-063). Un `parent_id` que apunte a un nodo de `depth` 3 se rechaza
con una frase que explica que la estructura llega hasta tres niveles (CL-17). Inactivar **nunca
borra**: `active` en falso, la historia sigue consultable, el nodo sale de los desplegables para
proponer cosas nuevas, y sus miembros quedan sin nodo y señalados para reasignar (RF-064, CL-18).
Renombrar no reescribe la historia (RF-065, CL-19). Los demás cargos ven la estructura y no la
modifican (HU-21 escenario 6).

## Presupuesto

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/budget` | GET | `Budget/Index` | `summary`, `byCommittee[]`, `movements[]`, `treasuryNotice`, `canAssign`, `canDistribute` |
| `/budget/assign` | POST | — | solo pastor: asigna el anual de un comité (RF-050) |
| `/budget/distribute` | POST | — | presidente: reparte entre sus sub-comités (RF-050) |
| `/budget/movements` | POST | — | registra el gasto; devuelve `overBudgetWarning` (RF-051) |

**Reglas**: el saldo llega **calculado** (D-10). Pasarse del presupuesto **advierte y permite
continuar si se confirma**, no bloquea (RN-05, RF-051, HU-16 esc. 2). El pastor ve los seis comités
y el total, destacando el que pasó del noventa por ciento (HU-16 esc. 4). `treasuryNotice` viaja
siempre: la aclaración de que la tesorería sigue manejando los recursos es obligatoria en toda
pantalla de presupuesto (RF-053). Un comité sin asignación funciona en cero (CL-04).

## Reportes

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/reports` | GET | `Reports/Index` | `stats`, `charts`, `filters`, `scopeNotice` · **diferida**: `tables` |
| `/reports/download` | GET | — | archivo .xlsx con los filtros aplicados (RF-038) |
| `/reports/print` | GET | `Reports/Print` | vista de impresión |

**Reglas**: todo gráfico tiene su equivalente exacto en tabla, con la misma información (RF-037,
HU-11 esc. 3). Un presidente o un líder obtiene los mismos reportes **solo con su gente**, y
`scopeNotice` aclara que los totales de la congregación los ve únicamente el pastor (HU-11 esc. 4).
La descarga y la impresión respetan filtros **y** ámbito (RF-038, HU-12 esc. 3). La impresión es
legible en blanco y negro, con el nombre de la iglesia, la fecha y los filtros aplicados (HU-12
esc. 2). Un grupo vacío muestra cero, no una pantalla rota (CL-12).

Esta es la pantalla de mayor riesgo para el límite de cinco consultas: se arma con agregaciones, una
por eje (R-08). Por eso es la otra **excepción declarada**. `stats` y `charts` caben en la primera
carga con una agregación condicional más las dos que exigen `GROUP BY`; `tables` viaja como
**propiedad diferida**, en su propia petición parcial, con su propio presupuesto de cinco consultas
por respuesta y con las dos del bloque compartido incluidas
([shared-props.md](./shared-props.md)): si los ocho grupos de RF-036 no caben
—la distribución por comité y los bautismos por año exigen `GROUP BY`—, se separan en dos
propiedades diferidas. El techo de cinco no se sube.

## Usuarios y cargos

| Ruta | Método | Pantalla | Propiedades propias |
|---|---|---|---|
| `/users` | GET | `Users/Index` | `users[]`, `committees[]`, `roles[]` |
| `/users` | POST | — | crea cuenta y cargo; genera clave temporal |
| `/users/{user}` | PUT | — | modifica |
| `/users/{user}/reset-password` | POST | — | clave temporal nueva (RF-005) |
| `/users/{user}/deactivate` | POST | — | retira el acceso de inmediato (RF-006) |
| `/users/{user}/roles` | POST, DELETE | — | asigna o cierra un cargo |

**Reglas**: **solo el pastor** (RF-003, HU-02). Al crear, si la persona no está en la lista de
miembros, se le crea la ficha con nombre y teléfono marcada como pendiente de completar (V-03,
RF-003). Retirar el acceso **no borra**: `active` en falso, y el nombre permanece en todo registro
histórico (RF-006, RN-09). Al asignar un cargo vacante, las solicitudes pendientes pasan al nuevo
responsable con registro del traspaso (RF-023, CL-06). Una persona puede tener dos cargos; eso
habilita el selector, no la unión de ámbitos (CL-05, C-01).

## Lo que trajo el levantamiento del 2026-10-28

Las rutas de las historias HU-25 a HU-37. Se detallan propiedad por propiedad al construir cada
fase de `tasks.md` (R1 a R5); lo que aquí queda fijo es quién entra y qué **no** debe viajar, que es
lo que verifica la prueba de aislamiento de cada pantalla.

| Ruta | Pantalla | Quién | No debe viajar |
|---|---|---|---|
| `/site-motto` GET, POST | `Site/Motto` | solo el pastor | — |
| `/committees/{committee}/identity` GET, PUT | `Committees/Identity` | ver: su ámbito; cambiar: su presidente | a otro comité: nada; en el calendario solo viaja el nombre (RF-083) |
| `/schedules` GET, POST | `Schedules/Index` | presidente y líder, sobre su nodo | los cronogramas internos de otro comité, ni que existen (RF-086) |
| `/schedules/{schedule}` GET | `Schedules/Show` | su ámbito | — |
| `/schedules/{schedule}/activities` POST | — | su ámbito; crea una o una serie (RF-088) | — |
| `/activities/{activity}/service-order` GET, PUT | `ServiceOrders/Edit` | la directiva del comité del culto | a otro comité: nada (RF-094) |
| `/birthdays` GET | `Birthdays/Index` | todo cargo, sobre su ámbito | nadie fuera del ámbito (HU-29 esc. 5) |
| `/print/activities` GET | `Print/Activities` | presidente y líder, sobre su nodo | lo de otro nodo |
| `/obra-social/beneficiaries` GET, POST | `SocialWork/Beneficiaries` | Obra Social y el pastor | todo, a cualquier otro comité |
| `/obra-social/aid` GET, POST | `SocialWork/Aid` | Obra Social y el pastor | todo, a cualquier otro comité |
| `/obra-social/fund` GET, POST | `SocialWork/Fund` | el tesorero y el presidente de Obra Social, y el pastor | todo, a cualquier otro comité |
| `/activities/{activity}/worship` GET, PUT | `Worship/Edit` | Alabanza y el pastor | a otro comité: nada |
| `/sunday-school/groups` GET, POST, PUT | `SundaySchool/Groups` | Escuela Dominical y el pastor | todo, a cualquier otro comité |
| `/sunday-school/children` GET, POST | `SundaySchool/Children` | Escuela Dominical y el pastor | todo; los datos de salud, también dentro del comité a quien la policy no autorice |
| `/sunday-school/children/{child}/consents` GET, POST | `SundaySchool/Consent` | Escuela Dominical y el pastor | todo, a cualquier otro comité; una firma guardada no se modifica |
| `/sunday-school/classes` GET, POST | `SundaySchool/Classes` | Escuela Dominical y el pastor | todo, a cualquier otro comité |
| `/sunday-school/visits` GET, POST | `SundaySchool/Visits` | Escuela Dominical y el pastor | todo, a cualquier otro comité |

**Reglas comunes**:

- Toda pantalla nueva tiene su prueba de aislamiento: un cargo ajeno no la ve (puerta 3).
- Toda pantalla nueva tiene su medición del presupuesto de cinco consultas (puerta 5).
- El nombre de la sede y el lema del año viajan en el bloque compartido
  ([shared-props.md](./shared-props.md)).
- La alerta de cumpleaños viaja como propiedad diferida de la pantalla de inicio (D-42).
