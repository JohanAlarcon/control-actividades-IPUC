# Fase 0 — Investigación y decisiones

**Funcionalidad**: Sistema de Comités IPUC · **Fecha**: 2026-10-18 · **Plan**: [plan.md](./plan.md)

El stack está fijado por la constitución, así que esta fase no compara tecnologías. Resuelve los
puntos que el plan de entrada dejaba abiertos o en contradicción con la especificación aclarada, y
deja fijadas las reglas que después hay que probar.

Cada punto sigue el formato: **Decisión · Razón · Alternativas consideradas**.

**Convenio de identificadores.** En los artefactos de esta funcionalidad hay cuatro familias, y
cada una lleva su propio prefijo: `R-xx` son las decisiones de investigación de este documento,
`RG-xx` son los riesgos del registro de [plan.md](./plan.md), `D-xx` son sus decisiones de
arquitectura y `DEP-xx` son las dependencias del cliente de [spec.md](./spec.md). Cuando aquí se
cita un riesgo del plan, se cita como `RG-xx`.

---

## R-01 · El ámbito de una persona con dos cargos

**Decisión.** Una petición tiene **un solo cargo activo**. `CurrentScope` lo resuelve así:

```
1. Si el usuario es pastor            → ámbito = todos los comités de la sede.
2. Si tiene un solo cargo vigente     → ese cargo es el activo, sin preguntar.
3. Si tiene dos o más cargos vigentes → el activo es el guardado en la sesión;
                                        si no hay ninguno guardado, el sistema le pide escoger
                                        antes de mostrarle información.
4. El ámbito del cargo activo es:
     presidente de X → X y sus hijos
     líder de Y      → solo Y
```

El cargo activo se cambia por `POST /role/switch`, que valida que el cargo pertenezca al usuario y
esté vigente, lo guarda en la sesión y regresa a la pantalla anterior. Toda pantalla muestra con
qué cargo se está actuando (`scope.activeRole` en las propiedades compartidas).

**Razón.** La aclaración del 2026-10-18 (RF-007) lo fijó así. Técnicamente es lo que más protege el
principio I: cada consulta tiene un único ámbito posible, de modo que la prueba de aislamiento se
escribe una sola vez por pantalla y no hay que razonar sobre uniones. Una pantalla nueva mal hecha
falla de forma evidente, no de forma sutil.

**Alternativas consideradas.** La unión de ámbitos, que era lo que traía el plan de entrada: se
descarta porque obliga a etiquetar el origen de cada dato en cada pantalla y multiplica los casos
de prueba del principio no negociable. El esquema mixto (unión en el inicio, cargo activo al
consultar) se descarta por tener dos reglas que mantener.

**Qué hay que probar.** Una persona con dos cargos, actuando con el cargo A, no obtiene nada del
ámbito B por ninguna vía: ni listado, ni acceso directo por identificador, ni descarga de reporte
(CL-05).

---

## R-02 · Versiones exactas de las tres dependencias añadidas

**Decisión.** Se confirma que las dos dependencias de Composer funcionan con Laravel 12 y se fijan
a versión exacta —no a rango— en el momento de instalar, tomando el número de `composer show`.
La tarea de instalación debe dejar escrito el número en este documento.

### Versiones resueltas al instalar — 2026-10-24 (T003 hecha)

| Dependencia | Versión fijada | Nota |
|---|---|---|
| `spatie/laravel-activitylog` | **4.12.3** | La línea 5.x exige PHP 8.4 y la constitución fija 8.3, así que queda en la 4.x |
| `maatwebsite/excel` | **4.0.6** | Arrastra `phpoffice/phpspreadsheet` 5.10.0 |
| `vite-plugin-pwa` | **2.0.0** | Fijada con `--save-exact` |

**Lo que la advertencia de esta sección anticipó, y pasó.** La investigación previa decía que la
línea compatible era la **3.1.x** de `maatwebsite/excel`. Es falso: con Laravel 12 y PHP 8.3 la que
resuelve es la **4.0.6**. La verificación contra buscadores no servía, y confirmarlo con `composer`
al instalar —como esta sección exigía— fue lo que lo detectó.

**Y hubo un segundo hallazgo, peor.** El primer intento instaló `maatwebsite/excel` **v1.1.5**, de
hace diez años y para Laravel 4, arrastrando el abandonado `phpoffice/phpexcel`. La causa real:
**faltaba la extensión `zip` de PHP**, que `phpspreadsheet` exige; sin ella Composer descartó toda
la línea moderna y cayó silenciosamente a la más vieja que no la pedía. Se activó
`extension=zip` en `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini` (respaldo en
`php.ini.backup-antes-de-zip`) y la instalación resolvió bien.

**Lección para el despliegue (RG-07).** El servidor de producción necesita la extensión `zip`
habilitada. Si falta, el mismo problema aparece allá, y Composer no falla: instala algo que no
sirve. Hay que verificarlo en T007 con `php -m | grep zip`.

| Dependencia | Compatibilidad | Cómo se fija |
|---|---|---|
| `spatie/laravel-activitylog` | Laravel 12 soportado; la línea mayor vigente es la 5.x | `composer require` y luego fijar el número exacto en `composer.json` |
| `maatwebsite/excel` | La línea 3.1.x cubre Laravel 12 | Igual. El problema conocido de `phpspreadsheet` con PHP 8.5 **no aplica**: la constitución fija PHP 8.4 |
| `vite-plugin-pwa` | Complemento de Vite, independiente del framework | Fijar el número exacto en `package.json`, sin `^` |

**Razón.** La constitución exige versiones exactas que no se actualizan sin decisión explícita. Un
rango (`^5.0`) deja que una actualización entre sin que nadie la decida, lo que la constitución
prohíbe.

**Alternativas consideradas.** Escribir la bitácora y el generador de hojas de cálculo a mano: ya
se descartó en el plan de entrada con su justificación (D-07, D-08), y la constitución la acepta
como justificación escrita.

**Advertencia honesta.** La compatibilidad se verificó con buscadores sobre directorios de
terceros, no sobre las notas de publicación oficiales. Antes de cerrar la tarea de instalación hay
que confirmarlo contra Packagist y que `composer require` resuelva sin conflicto en PHP 8.4.

Fuentes consultadas: [laraplugins.io — spatie/laravel-activitylog](https://laraplugins.io/plugins/spatie/laravel-activitylog?page=5),
[laraplugins.io — maatwebsite/excel](https://laraplugins.io/plugins/maatwebsite/excel?page=5),
[Laravel-Excel · incidencia sobre PHP 8.5](https://github.com/SpartnerNL/Laravel-Excel/issues/4345).

---

## R-03 · El idioma de las rutas

**Decisión.** Las rutas van en **inglés**: `/committees`, `/approvals`, `/activities`,
`/annual-plan`, `/members`, `/sympathizers`, `/attendance`, `/budget`, `/reports`, `/users`,
`/login`, `/password/change`. Los nombres de las pantallas de React y todo texto visible siguen en
español de Colombia.

**Razón.** La sección de idioma de la constitución lista expresamente **rutas** entre lo que se
escribe en inglés, junto con tablas, columnas, modelos, clases, métodos, variables y componentes.
La gobernanza dice que si un plan contradice la constitución se corrige el plan.

**Alternativas consideradas.** Rutas en español, que es lo que traía el plan de entrada con el
argumento de que la URL es visible para el usuario. Es un argumento razonable, pero la constitución
ya decidió. Si el cliente prefiere URLs en español, el camino correcto es una enmienda PATCH a la
sección de idioma, no una excepción en el plan.

---

## R-04 · Cuándo una actividad pasa de un estado al siguiente

**Decisión.** `progress` se calcula, nunca se escribe a mano:

```
progress = redondear(tareas cumplidas / tareas totales × 100)
progress = 0 cuando la actividad no tiene tareas
```

Y los estados se mueven así:

| De | A | Cuándo |
|---|---|---|
| — | `pending` | Al proponerse una actividad fuera del plan anual |
| `pending` | `approved` | Al aprobarla el pastor, o al aprobarse el plan anual completo |
| `pending` | `rejected` | Al rechazarla, con motivo escrito obligatorio |
| `approved` | `in_progress` | Al cumplirse la primera tarea, o al llegar la fecha programada: lo que ocurra primero |
| `in_progress` | `completed` | Al llegar `progress` a 100 |
| `approved` / `in_progress` | `completed` | Si la actividad no tiene tareas, el responsable la marca cumplida; queda en la bitácora con su nombre |

Sin transiciones hacia atrás: una actividad cumplida no vuelve a «en curso». Si hubo error, se
corrige registrando el cambio, que la bitácora conserva (principio III).

**Razón.** Era el único punto que la sesión de aclaraciones dejó como *Outstanding*, y es un
cálculo: el principio V exige prueba automatizada para los cálculos. Dejarlo implícito garantizaba
que cada pantalla lo interpretara distinto. La regla de «la fecha programada también arranca la
actividad» evita que una actividad sin tareas cumplidas se vea como «aprobada, aún no empieza»
cuando ya pasó (HU-09 distingue los cuatro estados en la vista anual).

**Alternativas consideradas.** Que el responsable moviera el estado a mano siempre: se descarta
porque RF-027 obliga a que el avance se recalcule solo y HU-09 escenario 4 lo dice expresamente.
Derivar el estado únicamente de la fecha: se descarta porque entonces una actividad con todas sus
tareas cumplidas seguiría apareciendo en curso.

---

## R-05 · El historial queda fuera del filtro automático

**Decisión.** Tres reglas:

1. Ninguna pantalla consulta la tabla del paquete de bitácora directamente.
2. El historial se lee **siempre** a través del modelo apuntado —que sí carga `site_id` y el
   filtro de ámbito—, pidiéndole sus registros de bitácora por la relación.
3. `ScopeCoverageTest` mantiene una lista blanca documentada con esa única tabla, y falla si
   alguien agrega otra sin justificarla en este documento.

**Razón.** La tabla del paquete no lleva `site_id` ni columna de comité, así que los rasgos de
ámbito no la cubren. Si una pantalla la consultara directamente, el filtro del principio I se
saltaría sin que nadie se enterara, que es exactamente el riesgo RG-01 del plan.

**Alternativas consideradas.** Agregarle `site_id` a la tabla del paquete con una migración
propia: se descarta porque habría que interceptar cómo el paquete escribe cada registro, lo que
anula la razón de usarlo. Escribir la bitácora a mano: ya descartado en D-07.

---

## R-06 · Quién ve el acompañamiento de los simpatizantes

**Decisión.** La ficha de seguimiento, las etapas y las visitas las ve y las actualiza
**únicamente el presidente del Comité de Evangelismo**, más el pastor. En consecuencia:

- `sympathizers.committee_id` apunta al **Comité de Evangelismo** (el comité padre), no a uno de
  sus sub-comités.
- **No existe** `assigned_to_user_id`. Repartir simpatizantes entre los líderes de sub-comité era
  la opción C de la aclaración y fue descartada; dejar el campo invitaría a construirla.
- Quien registró a la persona conserva la constancia en `invited_by_user_id` y puede ver que la
  registró, pero no abre su ficha de seguimiento (HU-13 escenario 2, S-07).
- La policy de `Sympathizer` es la más estrecha del proyecto: pastor, o presidente del Comité de
  Evangelismo. Ningún otro cargo, ni los líderes de los sub-comités de Evangelismo.

**Razón.** Lo fijó la aclaración del 2026-10-18 (RF-040). Es la información más delicada del
sistema —personas que apenas están visitando la iglesia— y el principio I pide el ámbito más
estrecho que cumpla el requisito.

**Alternativas consideradas.** Todos los cargos del Comité de Evangelismo (opción B) y el reparto
por sub-comité (opción C): descartadas por el cliente en la sesión de aclaraciones.

**Qué hay que probar.** Un líder de sub-comité de Evangelismo recibe «no disponible» al abrir la
ficha de un simpatizante por su identificador directo. Quien lo registró, también.

---

## R-07 · El nivel de compromiso de un simpatizante

**Decisión.** Se calcula, no se guarda, en `Support/Sympathizers/CommitmentLevel.php`, con dos
señales y umbrales en un solo lugar:

```
visitas recientes = contactos registrados en los últimos 60 días
días sin contacto = días desde el último contacto registrado

creciendo  → 3 o más visitas recientes y menos de 15 días sin contacto
estable    → al menos 1 visita reciente y menos de 45 días sin contacto
alejándose → 45 días o más sin contacto, o ninguna visita reciente
```

Los tres umbrales (60, 15, 45) y los conteos viven como constantes en esa clase, para poder
ajustarlos sin tocar pantallas.

**Razón.** RF-043 obliga a calcularlo con la asistencia reciente y los días desde la última
visita, y prohíbe que alguien lo escriba a mano. El supuesto S-03 de la especificación anticipó que
los umbrales se fijarían en el plan y quedarían en un solo lugar. Son números defendibles pero
**no confirmados por el cliente**: están puestos para poder construir y probar, y se ajustan con
una línea cuando el Comité de Evangelismo diga qué considera «alejándose».

**Alternativas consideradas.** Guardar el nivel en una columna: descartado en D-10, porque es un
dato que envejece sin que nadie lo note. Pedirle al presidente que lo clasifique a mano: lo
prohíbe RF-043.

---

## R-08 · Mantener cinco consultas por pantalla

**Decisión.** Tres medidas, en este orden:

1. `Architecture/QueryBudgetTest` recorre las pantallas de lectura, cuenta las consultas con el
   registro de consultas de Laravel y falla por encima de cinco. Cuentan **las consultas propias
   de la pantalla**: las del filtro de alcance, las propiedades compartidas, las policies y el
   controlador. No cuentan las tres fijas de Laravel en cada petición —leer la sesión, cargar a
   la persona y guardar la sesión—, que se miden aparte con su propia aserción. El reparto
   normativo lo fija el Constitution Check de [plan.md](./plan.md), y es el único válido: **tres**
   consultas como máximo para el bloque compartido —los cargos vigentes, sus comités y el
   sub-árbol del cargo activo— y **dos** para la pantalla. `scope.hidden` y `pendingCount` dejan de viajar en toda respuesta:
   pasan a ser **propiedades diferidas** de Inertia, se resuelven solo donde se muestran y se miden
   en su propia petición (A-09).
2. Las relaciones se cargan por adelantado siempre (`with`), nunca dentro de un bucle.
3. Los reportes y los resúmenes se resuelven con **consultas de agregación**, una por grupo de
   cifras, no contando filas en PHP. La pantalla de reportes es la de mayor riesgo: ocho grupos de
   cifras (RF-036), de los cuales dos exigen `GROUP BY`. Las cifras viajan en **propiedades
   diferidas**, con su propio presupuesto de cinco consultas por respuesta, y se reutilizan para la
   vista de gráficos y la de tabla (RF-037). Si los ocho ejes no caben en cinco, se separan en dos
   propiedades diferidas antes de tocar el límite.

**Dos excepciones declaradas.** La puerta del Constitution Check de [plan.md](./plan.md) para esta
restricción no dice «Pasa» a secas: dice «Pasa, con dos excepciones declaradas», y las nombra.

- `/reports`, por los ocho grupos de cifras de RF-036: las cifras van en propiedades diferidas con
  su propio presupuesto de cinco por respuesta.
- `/` (la pantalla principal): el reparto entre la primera carga y las propiedades diferidas se
  declara igual, expresamente.

En las dos, cada respuesta se mide aparte y ninguna pasa de cinco consultas. El techo de cinco
**no se sube**: lo fija la constitución y no se enmienda por esto. Si al medir con
`QueryBudgetTest` una pantalla no cabe, lo que se mueve a propiedad diferida son sus cifras, no el
límite.

> **Corregido el 2026-10-24, al medirlo.** Este criterio decía «la petición completa» y un reparto
> de 2 + 3. La prueba medía sin sesiones en la base y con la persona ya cargada, así que no veía
> las tres consultas fijas de Laravel; medido como en el servidor, el inicio de un presidente daba
> 6 sin mostrar nada. El autor del proyecto decidió contar las consultas propias de la pantalla y
> medir aparte las fijas (constitución 2.3.1).

**Razón.** La constitución fija el máximo y exige prevenir N+1. Con ~140 miembros el costo real es
bajo hoy, pero el límite protege del hábito, no del volumen. El criterio de medición vive en
plan.md, que es el documento normativo; aquí se remite a él para que no haya dos cifras que
mantener.

**Alternativas consideradas.** Caché de los resúmenes: se descarta por ahora. Agrega una fuente de
datos viejos —contra el espíritu de D-10— y con este volumen no hace falta. Si CE-07 (reporte en
menos de un minuto) llegara a fallar, se reconsidera con medición de por medio.

---

## R-09 · Entrar sin correo electrónico

**Decisión.** El kit inicial oficial se adapta así: `users.username` único por sede reemplaza al
correo como identificador de entrada; se retira el registro público; se retira el restablecimiento
de clave por correo. `users.must_change_password` obliga al cambio en el primer ingreso y después
de cada restablecimiento hecho por el pastor. El bloqueo tras cinco intentos fallidos usa el
limitador de intentos del framework, por usuario y por dirección de origen, con quince minutos de
espera (RF-004).

**Razón.** HU-01 pide no depender de tener correo. RF-005 deja el restablecimiento solo en manos
del pastor, y el supuesto S-08 confirma que el sistema no envía nada hacia afuera: no hay correo
saliente, así que un flujo de recuperación por correo sería una puerta que no funciona.

**Alternativas consideradas.** Dejar el correo como opcional pero presente: se descarta porque
invita a un flujo de recuperación que no existe. Autenticación a mano: descartada en D-05.

---

## R-10 · Qué se hace con la autorización de tratamiento de datos

**Decisión.** `members` y `sympathizers` llevan `data_consent` (bool),
`data_consent_at` (fecha) y `data_consent_means` (`signature` | `verbal` | `message`). Las
pantallas de registro y edición de personas muestran el aviso de privacidad, cuyo texto lo entrega
la congregación (DEP-04). Los listados de miembros y simpatizantes informan cuántas fichas del
ámbito están sin autorización registrada, y el reporte de la congregación incluye ese conteo
(CE-12).

El sistema **no** implementa borrado de datos a pedido de la persona: fue la opción C de la
aclaración y no se escogió. Si el cliente lo pide después, entra como propuesta para cotizar, y hay
que conciliarlo con RN-07, que prohíbe borrar lo registrado.

**Razón.** Lo fijó la aclaración del 2026-10-18 (RF-061, RF-062). Es la constancia mínima que
protege a la congregación sin salirse del alcance contratado.

**Alternativas consideradas.** No guardar nada y dejarlo en papel (opción A): deja a la iglesia sin
constancia. El juego completo de derechos del titular (opción C): excede lo contratado.

---

## R-11 · La única excepción que enumera la constitución al filtro de alcance

**Decisión.** `Activity::forSharedCalendar()`: un método con nombre propio que retira el filtro de
comité y **selecciona a mano** solo las columnas compartidas que RF-010 permite —título,
descripción, fecha, hora, lugar, comité responsable y estado—. Nunca devuelve `budget_amount` ni
carga tareas. Es la **única** salida del filtro en todo el proyecto —la única que la constitución
enumera— y cualquier otra se considera defecto.

Tres condiciones hacen parte de la excepción y no se pueden leer por separado:

- **Solo lee.** El método no se usa como base de un `update`, de un `delete` ni de una descarga.
- **No concede autorización.** La policy de `Activity` sigue negando toda escritura sobre una
  actividad ajena: ver el cronograma de otro comité no habilita tocarlo.
- **El filtro de sede no se retira nunca.** `BelongsToSite` sigue puesto; lo único que se retira es
  el filtro del cargo activo.

El **nombre del comité responsable sí se comparte** por esta excepción: es uno de los siete datos
enumerados. La reserva de RF-009 se refiere a los conteos de `scope.hidden`, que informan cuánto
queda fuera del ámbito sin listar nunca cuáles son; no convierte el nombre de un comité en dato
reservado.

**Razón.** RF-010 obliga a que cualquier cargo vea el cronograma de toda la congregación, lo que el
filtro general no permite. Eso no es una excepción que el plan se tome por su cuenta: la enmienda
del principio I del 2026-10-18 (constitución v2.0.0) la incorpora como **única excepción
enumerada** del principio, limitada a los datos compartidos que RF-010 autoriza y solo para
lectura. El plan solo decide la forma —una puerta con nombre, vigilada y probada, en vez de filtros
inconsistentes repartidos por los controladores (D-15)—, no el permiso. Con la enmienda escrita, la
excepción está amparada por la constitución y `forSharedCalendar()` se puede construir.

**Alternativas consideradas.** Quitarle el filtro al modelo `Activity` y filtrar en cada consulta:
se descarta porque invierte la carga —habría que acordarse de filtrar en lugar de acordarse de
abrir— justo en el principio no negociable.

**Qué hay que probar.** La prueba es de **lista blanca**, no de lista negra: compara el juego
completo de llaves que trae una actividad ajena contra los siete datos enumerados y falla si
aparece cualquier otro. No busca llaves prohibidas. Una columna nueva en `activities` deja la
prueba roja hasta que alguien decida si se comparte. Dos casos obligatorios:

1. **Lectura.** Un presidente consulta el cronograma, ve la actividad de otro comité, y la carga de
   la respuesta trae **exactamente** esos siete datos: ni presupuesto, ni tareas, ni nada más. La
   prueba revisa la carga de la respuesta, no solo lo que se ve en pantalla.
2. **Escritura.** El mismo presidente intenta modificar una actividad ajena y recibe «no
   disponible». La excepción no abre ninguna puerta de escritura.

---

## R-12 · Tres niveles sin consultas recursivas

**Decisión.** La estructura pasa a tres niveles —comité, sub-comité y equipo— y el sub-árbol de
cualquier nodo se resuelve en **una sola consulta**, sin recursión ni CTE:

```
depth se calcula al guardar y se guarda: 1 comité, 2 sub-comité, 3 equipo
parent_id que apunte a un nodo de depth 3 es inválido (CL-17)

sub-árbol de X = X, los hijos de X, y los hijos de los hijos de X
              → una consulta sobre parent_id y el parent_id del padre
```

**Razón.** La profundidad está acotada a tres por RF-011, así que no hace falta recursión. Eso es lo
que mantiene el techo de cinco consultas por pantalla sin enmendar la constitución: con un árbol de
profundidad libre habría que subirlo, y la constitución lo fija. Vive en
`Support/Committees/SubtreeResolver.php`, un solo lugar, con su prueba de conteo de consultas (T179,
T180).

**Alternativas consideradas.** Árbol de profundidad libre con CTE recursivas: se descarta porque
rompe el presupuesto de consultas y obliga a SQL crudo, que este proyecto prohíbe sobre tablas con
ámbito. Tabla de cierre transitivo: resuelve el rendimiento pero agrega una tabla que hay que
mantener sincronizada en cada movimiento del árbol, y con tres niveles no se gana nada.

**Qué hay que probar.** Lo más delicado de toda la ampliación (RG-14): un líder de equipo **no** ve
a su sub-comité hermano; un presidente **sí** ve a sus nietos; y el sub-árbol se resuelve en una
consulta, verificado con el registro de consultas.

---

## R-13 · Dónde viven los archivos y cómo se sirven

**Decisión.** Los adjuntos van al **disco del servidor del contrato**, fuera de la carpeta pública,
y se entregan por `/attachments/{attachment}`, una ruta que resuelve la policy antes de leer el
disco. Límite de 10 MB después de comprimir; las imágenes se reducen al recibirlas; tipos admitidos
JPEG, PNG, WebP y PDF.

**Razón.** Dos razones, y la segunda es la que importa. La constitución prohíbe que los datos del
cliente salgan del servidor acordado, lo que descarta cualquier servicio externo. Y si los archivos
se sirvieran desde `public/`, cualquiera con el enlace vería una evidencia ajena: el principio I se
caería por la puerta de atrás **sin que ninguna prueba de aislamiento lo notara**, porque las
pruebas revisan la carga de la respuesta de Inertia, no el servidor de archivos estáticos.

**Alternativas consideradas.** Servicio externo tipo S3 o R2: más barato de escalar y resuelve el
respaldo, pero contradice la cláusula de datos de la constitución. Servir desde `public/` con un
nombre difícil de adivinar: seguridad por oscuridad, y el enlace se comparte por WhatsApp en un
minuto.

**Lo que esto obliga.** El respaldo diario debe incluir el directorio de archivos (DEP-07, RG-13), y
el módulo **no se despliega** hasta que eso esté hecho: una falla de disco perdería las evidencias y
las facturas aunque la base de datos se recupere.

---

## R-14 · Lo que la investigación de la IPUC sí y no encontró

**Decisión.** El sistema **no cablea ninguna lista de comités**. La estructura inicial la carga el
pastor (DEP-05), y la semilla trae datos ficticios de demostración, no la estructura real.

**Razón.** El 2026-10-24 se buscó la estructura de comités de la IPUC, en especial de Ibagué, para
parametrizarla por adelantado. **No está documentada públicamente.** Lo que sí se confirmó, del
sitio de la IPUC Central Ibagué: existen **Damas Dorcas**, **Jóvenes** y **Misiones** como
«Directivas», y en el cuerpo de las páginas aparecen **Obra Social**, **Red de Familia** y
**Alabanza**. La página de Obra Social tiene un encabezado «Equipos de trabajo y comités / Para la
vigencia 2026» **sin lista debajo**, y la página «Comités y Oficios» del Distrito 28 es una
plantilla con marcadores sin diligenciar. De paso quedó confirmado que **REFAM es «Red de
Familia»**, el vocabulario que ya usaba la especificación.

Que ni el distrito ni la sede publiquen su organigrama es el argumento a favor de la estructura
administrable: varía entre sedes y entre vigencias. Lo que sí se puede estandarizar son los
**flujos** —plan de trabajo, actas, evidencias, presupuesto—, que son iguales para cualquier comité,
y eso es lo que hace el sistema.

**Fuentes consultadas**: [IPUC Central Ibagué](https://www.ipuccentralibague.org/) ·
[Obra Social](https://www.ipuccentralibague.org/obra-social) ·
[Distrito 28 · Comités y Oficios](https://distrito28.ipuc.org.co/comites-y-oficios/) ·
[Reseña histórica de la IPUC](https://sozai.app/transcript/rese-a-historica-ipuc-aniversario-88/).

**Advertencia honesta.** Tres búsquedas y tres páginas fue lo que valía la pena gastar. El dato lo
tiene la congregación, y la conversación con el pastor lo resuelve en diez minutos.

---

## Puntos que siguen abiertos

Ninguno bloquea el hito 1. Uno bloquea algo concreto: A-04 bloquea la carga inicial (US18) hasta
que el pastor confirme el supuesto S-02.

| # | Punto | Estado |
|---|---|---|
| A-01 | Umbrales del nivel de compromiso (R-07) | Puestos con valores defendibles. Confirmar con el Comité de Evangelismo durante el hito 3 |
| A-02 | Edad de los miembros: fecha de nacimiento o edad declarada (RG-05 del plan) | **Decidido**: se admiten las dos entradas —`birth_date` cuando se sabe, y `age_reported` con `age_reported_at` cuando no— y RF-031 quedó corregido en la especificación. Queda por confirmar con el pastor, al cargar los datos en el hito 3, de cuántos miembros hay fecha y de cuántos solo edad declarada |
| A-03 | Texto del aviso de privacidad y medio de recolección de autorizaciones | Lo entrega la congregación (DEP-04) |
| A-04 | Supuestos S-01 (sin avisos externos) y S-02 (carga inicial por plantilla) | Pendientes de confirmación del pastor (DEP-03). **Bloquea la carga inicial (US18)** hasta que el pastor confirme S-02 |
| A-05 | Confiabilidad y disponibilidad | Solo respaldo diario, por constitución. Sin meta de tiempo en servicio ni de recuperación; bajo impacto con 17 usuarios |
| A-06 | Edición simultánea de la misma fila | Sin control de concurrencia. Con 17 usuarios la probabilidad es baja; si aparece, se trata como defecto |
| A-07 | Columnas exactas de la plantilla de importación | Se fijan al construir HU-18, en el hito 3 |
| A-08 | Versiones exactas de las tres dependencias (R-02) | Se escriben aquí al instalar, en el hito 1. Falta además confirmar contra Packagist la compatibilidad de las dos de Composer con Laravel 12: la advertencia de R-02 dice que se verificó contra directorios de terceros |
| A-09 | Medición real del presupuesto de cinco consultas (R-08) | El criterio ya está fijado: cuentan las consultas propias de la pantalla, tres para el bloque compartido y dos para la pantalla, y las tres fijas de Laravel se miden aparte (constitución 2.3.1). La medición real quedó hecha en el hito 1; y el reparto definitivo de las propiedades diferidas de `/reports` y de `/`, que son las dos excepciones declaradas |
| A-10 | Precisión de los reportes por rango de edad (RF-036, A-02) | Quienes solo tengan `age_reported` entran en los rangos con precisión de ±1 año, y el reporte lo informa. Confirmar con el pastor que esa precisión le sirve para las reuniones de distrito |
| A-11 | Estructura inicial de comités (R-14, DEP-05) | **No es investigable**: ni el distrito ni la sede publican su organigrama. La entrega el pastor antes de habilitar las cuentas. La semilla trae datos ficticios, no la estructura real |
| A-12 | Formato oficial de acta del distrito (DEP-06) | No encontré ninguno publicado. Si el distrito exige uno, hay que ver una copia de un acta real **antes** de cerrar T194, la vista de impresión. Mientras no llegue, rige el formato de RF-072 |
| A-13 | Espacio en disco para archivos (RG-12) | 2 TB con límite de 10 MB por archivo y compresión de imágenes. Falta medir el consumo real al cerrar cada hito, y definir el umbral en que el sistema avisa al pastor (CL-24) |
| A-14 | Ampliación del respaldo a los archivos (DEP-07, RG-13) | **Bloquea el despliegue del módulo de archivos.** Hoy el respaldo solo cubre la base de datos; T010 lo amplía |
| A-15 | Secretario y tesorero solo en el primer nivel (D-20) | Criterio mío, no decisión del cliente: supuse que un sub-comité no lleva libro de actas propio ni maneja presupuesto aparte. Confirmar con el pastor cómo opera la congregación |
