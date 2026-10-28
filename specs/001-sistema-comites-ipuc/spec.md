# Especificación de Funcionalidad: Sistema de Comités IPUC

**Rama de funcionalidad**: `001-sistema-comites-ipuc`
(Nota: todavía no existe como rama de git. No hay hook `before_specify` configurado, así que la
rama debe crearse manualmente antes de implementar, conforme a la Gobernanza de la constitución.)

**Creada**: 2026-10-18

**Estado**: Borrador — reorientado el 2026-10-28 con el levantamiento de la sede La Esmeralda
(constitución 3.0.0). Quedan nueve puntos por confirmar (P-01 a P-09) y las
dependencias del cliente; ver «Supuestos».

**Entrada**:

- Descripción del usuario: `@spec-input.md` — documento «Especificación — Sistema de Comités
  IPUC» entregado por el autor del proyecto. Se conserva en la raíz del repositorio como
  `spec-input.md`.
- Levantamiento de información de la sede La Esmeralda, del 2026-10-26, con sus anexos:
  `levantamiento-la-esmeralda.md`, en esta misma carpeta. Donde las dos entradas difieren, manda
  el levantamiento.

Hoy la congregación coordina el trabajo de sus comités con cuadernos personales y grupos de
WhatsApp. En consecuencia: los acuerdos se pierden entre mensajes, dos comités programan
actividades el mismo día, la lista de miembros está desactualizada, nadie sabe con certeza quién
autorizó un gasto, y las personas que empiezan a visitar la iglesia se pierden de vista.

El sistema debe convertir ese trabajo en información consultable y verificable, sin cambiar la
forma en que la congregación ya trabaja: los comités proponen, el presidente de cada comité
responde por sus sub-comités, y el pastor aprueba lo nuevo y lo que se sale del plan del año. La
congregación aprobó esta propuesta en reunión; lo que se prometió allí es lo que debe cumplirse:
que cada comité vea solo lo suyo, que toda aprobación quede registrada, y que el pastor pueda
responder por toda la congregación sin pedirle cuentas a nadie por WhatsApp.

## Clarifications

### Session 2026-10-18

- Q: Cuando una misma persona tiene dos cargos al mismo tiempo, ¿el sistema le muestra la
  información de los dos ámbitos junta, o le pide escoger con qué cargo está actuando y le muestra
  solo ese? (RF-007) → A: Cargo activo. Un selector visible deja escoger con qué cargo actúa y el
  sistema aplica solo ese ámbito; para ver el otro, cambia de cargo.
- Q: ¿La persona que tiene un cargo es la misma ficha que su registro de miembro de la
  congregación, o son dos listas aparte? (RF-003, RF-031) → A: Una sola ficha. Si la persona
  todavía no está en la lista de miembros, el sistema le crea la ficha con nombre y teléfono y la
  deja señalada como pendiente de completar.
- Q: Cuando el presidente de un comité quiere hacer un gasto de su propio comité, ¿necesita que el
  pastor lo apruebe, o lo autoriza él mismo dejando el registro? (RF-018) → A: Lo autoriza él
  mismo, cualquiera sea el monto. Queda registrado con su nombre y la fecha, y el pastor lo ve en
  el presupuesto, no en su bandeja. *Reemplazada el 2026-10-26: también lo aprueba el pastor (ver
  la sesión de ese día y D-24).*
- Q: ¿La ficha de seguimiento de un simpatizante la ven todos los cargos del Comité de
  Evangelismo, o únicamente su presidente? (RF-040) → A: Únicamente el presidente del Comité de
  Evangelismo, además del pastor, que ve toda la congregación. *Reemplazada el 2026-10-28: BIS ve
  a todos y cada comité a los que le asignan (ver la sesión de ese día).*
- Q: ¿El sistema debe guardar la autorización de cada persona para manejar sus datos, o la
  congregación responde por eso fuera del sistema? (RF-031, RF-041) → A: El sistema la guarda. Por
  cada miembro y cada simpatizante registra si la persona autorizó, en qué fecha y cómo la dio, y
  muestra el aviso de privacidad donde se registran personas.

### Session 2026-10-24

Doce decisiones del autor del proyecto que amplían el alcance contratado. La constitución se
enmendó a 2.1.0 para incorporarlas.

- Q: ¿Cómo entran los cuatro módulos nuevos —actas, evidencias, soportes de factura y comités
  administrables—, que no estaban en el alcance contratado? → A: **Completos, sin mover la fecha.**
  Decisión tomada contra la recomendación, que era cotizarlos como fase 2. Consecuencia aceptada:
  los hitos semanales dejarán de cerrar de punta a punta y el cierre real se corre al final. Los
  dos principios NO NEGOCIABLES no se tocan.
- Q: ¿Dónde se guardan los archivos? → A: En el disco del servidor del contrato. El respaldo diario
  debe ampliarse para incluirlos; hoy solo cubre la base de datos.
- Q: ¿Cuántos niveles tiene la estructura de comités? → A: **Tres fijos**: comité, sub-comité y
  equipo. *Reemplazada el 2026-10-28: son dos niveles (ver la sesión de ese día).*
- Q: ¿Qué son los compromisos que salen de una reunión? → A: Texto dentro del acta, con la opción
  de convertir cualquiera en tarea con responsable.
- Q: ¿Qué cargos existen en un comité? → A: Presidente, **secretario**, **tesorero** y líder.
- Q: ¿Quién ve una evidencia de cumplimiento? → A: Solo el comité dueño y el pastor. **No** viajan
  por la excepción del cronograma compartido.
- Q: ¿Cómo se estructura un acta? → A: Enganchada a la reunión que ya se registra, con campos
  fijos, numerada por año y comité, e inmutable al cerrarse.
- Q: ¿Qué es el plan de trabajo anual de un comité? → A: El cronograma anual que ya existe. No hay
  módulo nuevo.
- Q: ¿Se suben los mínimos del principio II para adultos mayores? → A: Sí: texto de 18 px, zonas
  pulsables de 48x48 px y contraste AA.
- Q: ¿Quién administra la estructura y qué pasa al inactivar un comité? → A: Solo el pastor. La
  historia se conserva consultable y sus miembros quedan sin comité, señalados para reasignar.
  Renombrar conserva el nombre de la época en actas y reportes.
- Q: ¿Límites y borrado de archivos? → A: 10 MB por archivo, las fotos se comprimen al subirlas, y
  un archivo de algo ya cumplido o autorizado no se borra: se marca como reemplazado.
- Q: ¿Quién cierra un acta? → A: El secretario la redacta, el presidente la cierra. Desde el cierre
  no se edita: se corrige con un acta nueva.

### Session 2026-10-26

- Q: ¿Qué puede dejar resuelto el presidente sin que pase por el pastor? → A: Nada. **Solo el
  pastor aprueba.** El presidente revisa: le da su visto bueno y la solicitud sube al pastor, o la
  devuelve a quien la propuso con el motivo, y entonces no llega al pastor. Vale para los cuatro
  tipos —actividad nueva, gasto, tarea y cambio de fecha— y también para el gasto del propio comité
  del presidente: reemplaza la respuesta del 2026-10-18 sobre ese gasto. Cambian RF-017, RF-018,
  RN-04 y los escenarios 1 y 3 de HU-06 (D-24).
- Q: Las clases de actividad —culto, ensayo, reunión…—, ¿son una lista fija o se pueden agregar?
  → A: Se pueden agregar. Las administra el pastor desde una pantalla: agrega, cambia el nombre y
  desactiva. Una clase en uso no se borra, se desactiva. Cada congregación empieza con las ocho de
  siempre (D-25).

### Session 2026-10-28

El autor del proyecto trajo el levantamiento de información que hizo con la sede La Esmeralda el
2026-10-26 (`levantamiento-la-esmeralda.md`). La constitución se enmendó a 3.0.0 para
incorporarlo.

- Q: ¿Cómo entra lo que el levantamiento agrega al alcance contratado? → A: **Todo en el proyecto
  actual.** Se enmienda la constitución con el levantamiento completo como alcance contratado, y
  las fechas de entrega se corren.
- Q: ¿Cuántos niveles tiene la estructura? → A: **Dos**: comité y sub-comité. Reemplaza la
  respuesta del 2026-10-24: el equipo desaparece.
- Q: ¿Cuáles son los comités? → A: Los quince del levantamiento, con tres sub-comités en Damas
  Dorcas y tres en Conquistadores Pentecostales.
- Q: Con BIS registrando a la entrada y asignando un comité a cada simpatizante, ¿quién ve y
  acompaña a cada persona? → A: **BIS a todos, cada comité a los suyos.**
  - BIS ve a todos y hace la bienvenida, la integración y el seguimiento.
  - El comité asignado ve y acompaña solo a los suyos.
  - El pastor ve todo.
  - Se conservan las cinco etapas y el paso a miembro con aprobación del pastor.
  - Reemplaza la respuesta del 2026-10-18 sobre el Comité de Evangelismo.
- Q: En el calendario general, ¿qué ve un comité de las actividades de otro? → A: **Título, fecha,
  hora, lugar, comité responsable y estado.** Sin descripción, presupuesto, tareas ni soporte: el
  detalle solo lo ven el comité dueño y el pastor.
- Q: Un miembro puede estar en varios comités y un comité no modifica a sus miembros. ¿Quién
  registra y corrige la ficha? → A: **El pastor; los comités escogen.** El pastor crea y corrige
  las fichas. Cada comité agrega o quita de su lista a personas ya registradas, sin tocar sus
  datos, y sí registra a sus amigos (simpatizantes).
- Q: ¿Qué más trae el levantamiento? → A:
  - el nombre «IPUC La Esmeralda - Ibagué» y el lema del año;
  - el logo, el lema y los objetivos de cada comité;
  - los cronogramas internos, varios por comité y privados;
  - que las actividades generales no se crucen;
  - un soporte por actividad;
  - la asistencia a todas las actividades;
  - el orden de culto;
  - los cumpleaños;
  - imprimir y compartir por WhatsApp;
  - los módulos de Obra Social, Alabanza y Escuela Dominical;
  - y ninguna dirección en la ficha de los miembros.

  Ver HU-25 a HU-37 y RF-081 a RF-114.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Usuarios y alcance de cada cargo

Solo las personas con un cargo entran al sistema. Los demás miembros están registrados, pero no
tienen acceso.

| Usuario | Cuántos | Qué hace |
|---|---|---|
| **Pastor** | 1 | Ve toda la congregación. Administra la estructura de comités: crea, renombra e inactiva en los dos niveles. Lleva el padrón de miembros: crea y corrige las fichas. Crea y quita cuentas. Registra el lema del año. Aprueba el plan anual, las actividades nuevas y el paso de simpatizantes a miembros. Asigna el presupuesto de cada comité. |
| **Presidente de comité** | uno por comité | Ve su comité y sus sub-comités. Revisa lo que le proponen. Escoge del padrón a los integrantes de su comité. Lleva los cronogramas internos y la identidad del comité. Reparte el presupuesto. Cierra las actas de su comité. |
| **Secretario** | uno por comité | Mismo ámbito que su presidente. Redacta las actas, toma asistencia y prepara el orden de culto. |
| **Tesorero** | uno por comité | Mismo ámbito que su presidente. Registra los movimientos de presupuesto y sube sus soportes; en Obra Social, también el fondo propio. |
| **Líder de sub-comité** | uno por sub-comité | Ve únicamente su sub-comité. Propone actividades y gastos, lleva sus cronogramas internos, registra avance, sube soportes, toma asistencia. |

La cantidad de personas con cargo **no es un número fijo**: depende de la estructura que configure
el pastor. La sede La Esmeralda arranca con quince comités y seis sub-comités (levantamiento), pero
el sistema no los supone: los carga el pastor y los puede cambiar (ver HU-21).

**Casos particulares**:

- BIS recibe a todos los simpatizantes y asigna a cada uno el comité que lo acompaña (HU-13).
- La Escuela Dominical, Obra Social y Alabanza tienen además sus módulos propios (HU-31 a HU-37).
- La directiva de cada comité incluye a sus vocales, que no tienen cuenta por serlo (RF-084).

Las historias se presentan ordenadas por prioridad. **P1** = sin esto no hay sistema.
**P2** = el sistema sirve pero queda incompleto. **P3** = mejora el uso diario. Se conserva el
identificador original (HU-xx) del documento de entrada para poder rastrear cada historia.

---

### Historia de Usuario 1 — HU-01 · Entrar al sistema por primera vez (Prioridad: P1)

Como persona con un cargo en la congregación quiero entrar con un usuario sencillo y cambiar mi
clave la primera vez, para no depender de tener correo electrónico ni de recordar datos
complicados.

**Por qué esta prioridad**: sin una forma de entrar no hay sistema. Es el cimiento de todas las
demás historias y la única puerta por la que se aplica el alcance de cada cargo.

**Prueba independiente**: se puede probar sola creando una cuenta con clave temporal e intentando
entrar; entrega valor porque las personas con cargo quedan habilitadas con una clave
propia, sin usar correo electrónico.

**Escenarios de Aceptación**:

1. **Dado** que el pastor me creó una cuenta con una clave temporal, **cuando** entro por primera
   vez con ese usuario y esa clave, **entonces** el sistema me pide cambiar la clave antes de
   dejarme ver cualquier información.
2. **Dado** que ya cambié mi clave, **cuando** vuelvo a entrar, **entonces** el sistema me lleva
   directamente a mi pantalla de inicio.
3. **Dado** que escribo mal mi clave cinco veces seguidas, **cuando** intento una sexta,
   **entonces** el sistema bloquea el acceso durante quince minutos y me indica que le pida al
   pastor que me la restablezca.
4. **Dado** que olvidé mi clave, **cuando** se lo informo al pastor, **entonces** él puede
   generarme una clave temporal nueva y el sistema vuelve a exigirme cambiarla al entrar.
5. **Dado** que abro el sistema, **cuando** aparece la pantalla de entrada, **entonces** veo el
   nombre «IPUC La Esmeralda - Ibagué» y el lema del año (HU-25).

---

### Historia de Usuario 2 — HU-02 · Administrar las cuentas y los cargos (Prioridad: P1)

Como pastor quiero crear, modificar y retirar las cuentas de quienes tienen cargo, para que nadie
conserve acceso a información que ya no le corresponde.

**Por qué esta prioridad**: es la única fuente de las cuentas y de los cargos. Sin ella no hay a
quién aplicar el alcance, y un cargo que cambia de persona dejaría acceso abierto.

**Prueba independiente**: se puede probar sola creando un cargo, asignándolo, retirándolo y
comprobando que la persona retirada ya no entra mientras su nombre permanece en el historial.

**Escenarios de Aceptación**:

1. **Dado** que un hermano asume la presidencia de un comité, **cuando** le creo la cuenta y le
   asigno ese cargo y ese comité, **entonces** el sistema genera su usuario y una clave temporal
   que puedo entregarle y, si ese hermano todavía no está en la lista de miembros, le queda creada
   su ficha con nombre y teléfono, señalada como pendiente de completar.
2. **Dado** que un líder deja su sub-comité, **cuando** retiro su acceso, **entonces** esa persona
   deja de entrar inmediatamente, y su nombre se conserva en el historial de todo lo que aprobó o
   propuso.
3. **Dado** que una persona tenía solicitudes pendientes al dejar el cargo, **cuando** asigno el
   cargo a su reemplazo, **entonces** las solicitudes pendientes quedan a nombre del nuevo
   responsable y el sistema registra quién las recibió y en qué fecha.
4. **Dado** que intento asignar dos cargos a la misma persona, **cuando** guardo, **entonces** el
   sistema lo permite, y esa persona escoge con cuál de sus dos cargos está actuando: ve la
   información de uno a la vez, con el cargo activo siempre indicado en la pantalla.

---

### Historia de Usuario 3 — HU-03 · Ver la estructura de comités (Prioridad: P1)

Como persona con cargo quiero ver cómo está organizada la congregación en comités y sub-comités,
para entender de quién depende cada cosa y a quién acudir.

**Por qué esta prioridad**: la jerarquía de comités es el esqueleto del que dependen las
aprobaciones, el presupuesto, la asistencia y el alcance de la información.

**Prueba independiente**: se puede probar sola cargando una estructura de los dos niveles y
abriendo la sección con cada cargo; entrega valor porque la congregación ve por primera vez su
organización completa en un solo lugar.

**Escenarios de Aceptación**:

1. **Dado** que soy el pastor, **cuando** abro la sección de comités, **entonces** veo la estructura
   completa de los dos niveles, con sus responsables y cuántos miembros tiene cada nodo.
2. **Dado** que soy presidente de un comité, **cuando** abro la misma sección, **entonces** veo
   únicamente mi comité y mis sub-comités; los demás aparecen como no disponibles, sin mostrar
   ningún dato suyo.
3. **Dado** que un comité no tiene sub-comités, **cuando** abro su ficha, **entonces** el sistema
   lo indica expresamente y muestra que el presidente coordina directamente a sus miembros.
4. **Dado** que soy líder de un sub-comité, **cuando** abro la sección, **entonces** veo mi
   sub-comité y el nombre de mi presidente, porque es la persona que revisa lo que propongo.

---

### Historia de Usuario 4 — HU-04 · Cada comité ve únicamente lo suyo (Prioridad: P1)

Como miembro de la congregación quiero tener la certeza de que mi información no la ve cualquiera,
para confiar en que el sistema no expone a los hermanos.

**Por qué esta prioridad**: es la promesa que la congregación aprobó en reunión y el primer
principio no negociable de la constitución. Si falla una sola vez, se pierde la confianza del
cliente.

**Prueba independiente**: se puede probar sola intentando, con cada cargo, consultar información
de otro comité por todas las vías disponibles y comprobando que ninguna devuelve datos ajenos.

**Escenarios de Aceptación**:

1. **Dado** que soy presidente de un comité, **cuando** consulto miembros, aprobaciones,
   asistencia o presupuesto, **entonces** solo obtengo lo de mi comité y mis sub-comités, sin
   excepción.
2. **Dado** que intento abrir directamente la información de otro comité, **cuando** el sistema la
   recibe, **entonces** me responde que esa información no está disponible para mi cargo e indica
   a quién debo solicitarla, sin revelar ningún dato.
3. **Dado** que soy líder de un sub-comité, **cuando** consulto la lista de miembros, **entonces**
   veo únicamente a los de mi sub-comité, y el sistema me indica cuántas personas de la
   congregación no aparecen allí y por qué.
4. **Dado** que soy el pastor, **cuando** consulto cualquier información, **entonces** la veo
   completa, porque respondo por toda la congregación.

**Excepciones declaradas**: son las cuatro que enumera el principio I de la constitución 3.0.0.

1. El cronograma general es información compartida de la congregación (HU-09, RF-010).
2. BIS ve a todos los simpatizantes (HU-13, RF-040).
3. El sistema dice cuántas personas quedan fuera del alcance y si una persona ya existe, sin
   mostrarla (RF-009, RF-096).
4. El buscador del padrón muestra solo el nombre y la edad, para escoger integrantes (RF-085).

---

### Historia de Usuario 5 — HU-05 · Proponer una actividad nueva (Prioridad: P1)

Como líder de sub-comité o presidente de comité quiero proponer una actividad que no estaba en el
plan del año, para que quede evaluada y aprobada antes de comprometer a la iglesia.

**Por qué esta prioridad**: es el punto de entrada de la cadena de aprobación, el flujo que
reemplaza los acuerdos perdidos en WhatsApp.

**Prueba independiente**: se puede probar sola proponiendo una actividad como líder y como
presidente y verificando a quién le llegó cada una, incluidas las validaciones y el aviso de
cruce de fechas.

**Escenarios de Aceptación**:

1. **Dado** que quiero hacer una actividad que no está en el plan, **cuando** la propongo indicando
   título, fecha, hora de inicio y de fin, lugar, descripción y presupuesto estimado, **entonces**
   el sistema la registra como propuesta y me indica con nombre propio a quién le llegó.
2. **Dado** que soy líder de sub-comité, **cuando** propongo una actividad, **entonces** la
   solicitud le llega primero al presidente de mi comité.
3. **Dado** que soy presidente de comité, **cuando** propongo una actividad, **entonces** la
   solicitud le llega directamente al pastor.
4. **Dado** que dejo el título vacío o pongo una fecha fuera del año en curso, **cuando** intento
   enviarla, **entonces** el sistema no la envía y me señala exactamente qué campo corregir.
5. **Dado** que la fecha y el horario que escogí se cruzan con otra actividad del cronograma
   general, **cuando** la propongo, **entonces** el sistema no la deja enviar, me dice qué
   actividad ocupa ese momento y de qué comité, y me pide escoger otro (RF-028).

---

### Historia de Usuario 6 — HU-06 · Revisar lo que proponen mis sub-comités (Prioridad: P1)

Como presidente de comité quiero revisar lo que proponen mis líderes —darle mi visto bueno o
devolverlo con el motivo—, para responder por lo que hace mi comité. Quien aprueba es el pastor.

**Por qué esta prioridad**: es el primer nivel de la aprobación en dos niveles. Sin él, el pastor
recibiría todo directamente y se perdería la responsabilidad del presidente.

**Prueba independiente**: se puede probar sola enviando los cuatro tipos de solicitud desde un
líder y revisándolas como presidente, comprobando que todo lo que recibe su visto bueno escala al
pastor y que lo devuelto vuelve al líder con su motivo.

**Escenarios de Aceptación**:

1. **Dado** que un líder me envió una solicitud de gasto, tarea o cambio de fecha, **cuando** le
   doy mi visto bueno, **entonces** no queda resuelta: pasa al pastor, que es quien aprueba, y el
   líder ve que ya pasó por mí, con mi nombre y la fecha.
2. **Dado** que un líder me envió una propuesta de actividad nueva, **cuando** le doy mi visto
   bueno, **entonces** no queda cerrada: pasa al pastor, y el sistema se lo informa a quien la
   propuso.
3. **Dado** que no estoy de acuerdo con una solicitud, **cuando** la devuelvo, **entonces** el
   sistema me exige escribir el motivo, la solicitud no llega al pastor, y ese motivo queda visible
   para quien la propuso, que puede corregirla y volver a proponerla.
4. **Dado** que tengo varias solicitudes esperando, **cuando** abro mi pantalla de inicio,
   **entonces** veo cuántas son y desde cuándo esperan, antes que cualquier otra cosa.

---

### Historia de Usuario 7 — HU-07 · Aprobar lo que sube al pastor (Prioridad: P1)

Como pastor quiero aprobar o rechazar lo nuevo y lo que se sale del plan, para mantener el orden
del año sin tener que estar en cada reunión de comité.

**Por qué esta prioridad**: cierra la cadena de aprobación. Sin este paso, ninguna actividad nueva
puede quedar aprobada.

**Prueba independiente**: se puede probar sola dejando solicitudes escaladas en la bandeja del
pastor y resolviéndolas una a una y en bloque, verificando el efecto en el cronograma.

**Escenarios de Aceptación**:

1. **Dado** que tengo solicitudes esperando mi decisión, **cuando** abro la bandeja, **entonces**
   veo de qué comité viene cada una, quién la pidió, por quién ya pasó, qué efecto tiene en el
   cronograma y, cuando es una propuesta de actividad, su presupuesto estimado. Los gastos también
   llegan a esta bandeja, con su monto: solo yo apruebo (RF-018).
2. **Dado** que apruebo una propuesta de actividad, **cuando** confirmo, **entonces** la actividad
   entra al cronograma del año como aprobada, con mi nombre y la fecha.
3. **Dado** que no apruebo una propuesta, **cuando** escribo el motivo y confirmo, **entonces** la
   actividad queda como no aprobada, con el motivo visible para el comité, y no aparece en el
   cronograma vigente.
4. **Dado** que tengo varias solicitudes del mismo tipo, **cuando** decido resolverlas todas,
   **entonces** el sistema me permite hacerlo en una sola acción, pidiéndome confirmación una vez.

---

### Historia de Usuario 8 — HU-08 · Aprobar el plan del año (Prioridad: P1)

Como pastor quiero aprobar de una sola vez el cronograma completo del año, para no tener que
autorizar cada actividad por separado.

**Por qué esta prioridad**: es la regla que distingue lo planeado de lo nuevo (RN-03). Sin plan
anual aprobado, toda actividad tendría que aprobarse una por una.

**Prueba independiente**: se puede probar sola cargando las actividades del año entrante,
aprobando el plan en un acto y verificando que queda sellado con nombre y fecha.

**Escenarios de Aceptación**:

1. **Dado** que los comités cargaron sus actividades para el año entrante, **cuando** reviso y
   apruebo el plan, **entonces** todas esas actividades quedan aprobadas con mi nombre y la fecha
   de aprobación.
2. **Dado** que el plan del año ya fue aprobado, **cuando** cualquiera consulta el cronograma,
   **entonces** el sistema muestra quién lo aprobó y cuándo.
3. **Dado** que una actividad fue cargada después de aprobado el plan, **cuando** se consulta,
   **entonces** el sistema la distingue claramente de las que venían en el plan original.

---

### Historia de Usuario 9 — HU-09 · Consultar el cronograma del año (Prioridad: P1)

Como persona con cargo quiero ver el año completo y cómo va el cumplimiento, para no cruzarme con
otros comités y saber qué falta.

**Por qué esta prioridad**: es la razón por la que dos comités dejan de programar el mismo día, uno
de los problemas que motivó el proyecto.

**Prueba independiente**: se puede probar sola con un año cargado, revisando la vista anual, los
filtros, el recálculo del cumplimiento y lo que ve un cargo ajeno al comité responsable.

**Escenarios de Aceptación**:

1. **Dado** que abro el cronograma, **cuando** elijo ver el año completo, **entonces** veo los doce
   meses con sus actividades, distinguiendo las cumplidas, las que van en camino, las aprobadas que
   aún no empiezan y las que esperan aprobación.
2. **Dado** que quiero ver un comité en particular, **cuando** lo escojo, **entonces** tanto la
   lista como los números del resumen y la barra de cumplimiento se recalculan solo con ese comité.
3. **Dado** que consulto una actividad de otro comité, **cuando** la abro, **entonces** veo su
   título, fecha, hora, lugar, comité responsable y estado, pero **no** su descripción, su
   presupuesto, sus tareas internas, su soporte ni su orden de culto.
4. **Dado** que una actividad tiene tareas pendientes, **cuando** marco una como cumplida,
   **entonces** el avance de esa actividad se recalcula solo, sin que nadie lo escriba a mano.
5. **Dado** que quiero ver el mes, **cuando** abro el calendario, **entonces** lo veo con el
   formato del cronograma de la sede (RF-115) y, por día, las actividades del cronograma general de
   todos los comités, con su hora y su comité; al tocar una de mi comité veo su detalle.
6. **Dado** que busco un cronograma interno de otro comité, **cuando** recorro el calendario,
   **entonces** no lo encuentro: los cronogramas internos no se comparten (HU-27).
7. **Dado** que el pastor va a repartir el cronograma, **cuando** lo imprime, **entonces** sale
   una hoja horizontal por mes, con el mismo formato y los mismos colores del de la sede, o solo
   el mes que escoja (RF-117).
8. **Dado** que una actividad es un día especial, es del distrito o trae su tema, **cuando** la
   veo en el calendario, **entonces** sale en rojo, en naranja o con el tema en azul, y en la
   pantalla además lo dice con palabras: el color nunca es la única señal (RF-116).

---

### Historia de Usuario 10 — HU-20 · Usar el sistema desde el celular (Prioridad: P1)

Como hermano que atiende su comité desde el teléfono quiero que todo funcione bien en una pantalla
pequeña, para no depender de un computador.

**Por qué esta prioridad**: la mayoría de las personas con cargo usará el sistema desde
el celular. Es el segundo principio de la constitución: una funcionalidad que solo sirve en
escritorio no está terminada.

**Prueba independiente**: se puede probar sola recorriendo las pantallas ya construidas a 360 px de
ancho y comprobando instalación en la pantalla de inicio del teléfono y tiempo de apertura.

**Escenarios de Aceptación**:

1. **Dado** que entro desde un celular, **cuando** abro cualquier pantalla, **entonces** la uso
   completa sin necesidad de desplazarme hacia los lados.
2. **Dado** que una pantalla muestra una tabla con muchas columnas, **cuando** la abro en el
   celular, **entonces** la información se presenta en tarjetas legibles, no en una tabla cortada.
3. **Dado** que uso el sistema con frecuencia, **cuando** lo deseo, **entonces** puedo dejarlo
   instalado en la pantalla de inicio de mi teléfono y abrirlo como una aplicación.

---

### Historia de Usuario 11 — HU-10 · Registrar y consultar miembros (Prioridad: P2)

Como pastor quiero llevar al día el padrón de los miembros de la congregación, y como presidente o
líder quiero escoger del padrón a los integrantes de mi comité, para que las listas y los reportes
sean ciertos sin que cada comité tenga su propia versión de cada persona.

**Por qué esta prioridad**: el sistema de comités funciona sin el padrón completo, pero los
reportes y las estadísticas no sirven hasta que los miembros estén cargados y al día.

**Prueba independiente**: se puede probar sola registrando, buscando y filtrando miembros como
pastor, incluidas las validaciones de edad y de bautismo, y agregando y quitando integrantes como
presidente.

**Escenarios de Aceptación**:

1. **Dado** que soy el pastor y quiero registrar a un hermano, **cuando** ingreso nombre, fecha de
   nacimiento, género, estado civil, si está bautizado, teléfono, barrio y los comités a los que
   pertenece, **entonces** queda registrado, el sistema me muestra los años que tiene, y aparece de
   inmediato en las listas de esos comités y en los números. El formulario no pide la dirección.
2. **Dado** que registro a una persona menor de dieciocho años, **cuando** intento marcarla como
   casada o viuda, **entonces** el sistema no lo permite y explica por qué.
3. **Dado** que marco a alguien como bautizado, **cuando** guardo sin la fecha de bautismo,
   **entonces** el sistema me la exige.
4. **Dado** que busco un hermano por nombre o por barrio, **cuando** escribo parte del texto,
   **entonces** la lista se filtra sin distinguir mayúsculas ni tildes.
5. **Dado** que necesito un grupo específico, **cuando** combino filtros de comité, género, estado
   civil, bautizado y rango de edad, **entonces** el sistema muestra cuántas personas cumplen y me
   deja quitar los filtros de una vez.
6. **Dado** que estoy registrando a un hermano, **cuando** abro el formulario, **entonces** veo el
   aviso de privacidad de la congregación y el sistema me pide anotar si la persona autorizó el
   manejo de sus datos, en qué fecha y cómo lo hizo.
7. **Dado** que no sé la fecha de nacimiento de un hermano, **cuando** escojo «No la sé — anote la
   edad» y anoto los años que tiene, **entonces** el sistema guarda esa edad junto con el día en
   que la anoté, me deja guardar la ficha, y ese hermano sigue contando en los reportes por edad.
8. **Dado** que de un hermano no tengo ni la fecha de nacimiento ni la edad, **cuando** le marco un
   estado civil distinto de soltero, **entonces** el sistema me pide confirmar que es mayor de
   dieciocho años, deja la confirmación anotada y guarda la ficha, señalada como pendiente de
   completar; nunca me exige la fecha para poder guardar.
9. **Dado** que soy presidente y un hermano ya registrado se une a mi comité, **cuando** lo busco
   por su nombre, **entonces** el buscador me muestra solo el nombre y la edad de los miembros de
   la sede, y al escogerlo queda en la lista de mi comité sin que yo vea ni cambie su ficha.
10. **Dado** que un hermano está en dos comités, **cuando** consulto la lista de cada uno,
    **entonces** aparece en las dos, y en los totales de la congregación cuenta una sola vez.
11. **Dado** que soy presidente o líder, **cuando** intento corregir la ficha de un integrante de
    mi comité, **entonces** el sistema no me lo permite y me explica que las fichas las corrige el
    pastor; sí puedo quitarlo de mi lista.
12. **Dado** que reviso la lista de mi comité, **cuando** la abro, **entonces** distingo a la
    directiva —presidente, secretario, tesorero y vocales— de los demás integrantes.

---

### Historia de Usuario 12 — HU-11 · Consultar reportes y estadísticas (Prioridad: P2)

Como pastor quiero obtener en el momento las cifras de la congregación, para responder en las
reuniones sin tener que contar a mano.

**Por qué esta prioridad**: es el beneficio visible para el pastor, pero depende de que los
miembros ya estén registrados (HU-10).

**Prueba independiente**: se puede probar sola con el padrón cargado, abriendo los reportes con
cada cargo y comparando la vista de gráficos con la de tabla.

**Escenarios de Aceptación**:

1. **Dado** que abro los reportes, **cuando** la pantalla carga, **entonces** veo total de
   miembros, bautizados con su porcentaje, casados, solteros, edad promedio y cuántos no pertenecen
   a ningún comité.
2. **Dado** que quiero el detalle, **cuando** reviso los gráficos, **entonces** encuentro la
   distribución por estado civil, por comité, por rango de edad, por género, bautizados y bautismos
   por año.
3. **Dado** que alguien no distingue bien los colores o prefiere los números, **cuando** cambia a
   la vista de tabla, **entonces** obtiene exactamente la misma información en forma de tabla.
4. **Dado** que soy presidente o líder, **cuando** abro los reportes, **entonces** obtengo los
   mismos reportes pero solo con mi gente, y el sistema aclara que los totales de la congregación
   los ve únicamente el pastor.
5. **Dado** que un hermano pertenece a varios comités, **cuando** el pastor abre los reportes de la
   congregación, **entonces** ese hermano cuenta una sola vez en los totales y aparece en la
   distribución de cada comité al que pertenece.

---

### Historia de Usuario 13 — HU-12 · Descargar e imprimir los reportes (Prioridad: P2)

Como pastor quiero llevarme las cifras en un archivo o en papel, para presentarlas en reuniones de
distrito y convenciones.

**Por qué esta prioridad**: extiende el valor de los reportes fuera del sistema, pero no es
indispensable para operar.

**Prueba independiente**: se puede probar sola aplicando filtros a un reporte, descargándolo e
imprimiéndolo, y verificando que el archivo respeta los filtros y el ámbito del cargo.

**Escenarios de Aceptación**:

1. **Dado** que estoy viendo un reporte con filtros aplicados, **cuando** lo descargo, **entonces**
   obtengo un archivo de hoja de cálculo con exactamente la información que estoy viendo, no con
   todo.
2. **Dado** que necesito el reporte en papel, **cuando** elijo imprimir, **entonces** obtengo una
   versión legible en blanco y negro, con el nombre de la iglesia, la fecha y los filtros
   aplicados.
3. **Dado** que soy presidente o líder, **cuando** descargo o imprimo, **entonces** el archivo
   contiene únicamente la información de mi ámbito.

---

### Historia de Usuario 14 — HU-13 · Registrar un simpatizante (Prioridad: P2)

Como integrante de BIS quiero registrar en la entrada a quien llega por primera vez y asignarle el
comité que lo va a acompañar, y como cualquier comité quiero registrar a los amigos que invitamos,
para que nadie que visita la iglesia se pierda de vista.

**Por qué esta prioridad**: resuelve uno de los problemas declarados —las personas que empiezan a
visitar la iglesia se pierden de vista— y es la puerta de entrada del acompañamiento.

**Prueba independiente**: se puede probar sola registrando un simpatizante como BIS y como otro
comité, y comprobando a qué comité queda asignado, quién lo ve y cómo se avisa un duplicado.

**Escenarios de Aceptación**:

1. **Dado** que una persona llega por primera vez, **cuando** BIS registra su nombre, su fecha de
   nacimiento si la sabe —o los años que diga—, género, teléfono, barrio, cómo llegó y quién la
   invitó, y escoge el comité que la acompañará según la persona, **entonces** queda registrada,
   asignada a ese único comité, y la ven BIS, ese comité y el pastor.
2. **Dado** que estoy escribiendo el nombre, **cuando** coincide con personas ya registradas,
   **entonces** el sistema me las sugiere en una lista mientras escribo —a BIS, entre todos los
   simpatizantes; a un comité, entre los suyos—, para no registrar dos veces a la misma.
3. **Dado** que soy de un comité y registro a un amigo que invitamos, **cuando** guardo,
   **entonces** queda asignado a mi comité, BIS lo ve también, y mi nombre queda como quien lo
   invitó.
4. **Dado** que la persona que registro ya existe en otro comité, **cuando** guardo, **entonces** el
   sistema me advierte que ya está registrada, sin decirme dónde ni mostrarme su ficha, y me deja
   continuar o cancelar.
5. **Dado** que estoy registrando a un simpatizante, **cuando** abro el formulario, **entonces**
   veo el aviso de privacidad y el sistema me pide anotar si la persona autorizó el manejo de sus
   datos, con su fecha y su medio.

---

### Historia de Usuario 15 — HU-14 · Acompañar a los simpatizantes (Prioridad: P2)

Como integrante de BIS o del comité asignado quiero saber cómo va cada persona que está visitando
la iglesia, para darme cuenta a tiempo de quién se está alejando.

**Por qué esta prioridad**: convierte el registro de simpatizantes en acompañamiento real, pero
depende de que el registro exista (HU-13).

**Prueba independiente**: se puede probar sola con simpatizantes cargados, recorriendo sus etapas,
registrando visitas y comprobando que el nivel de compromiso se recalcula sin intervención manual.

**Escenarios de Aceptación**:

1. **Dado** que abro la sección de simpatizantes, **cuando** la pantalla carga, **entonces** veo
   cuántas personas acompañamos, cuántas están creciendo, cuántas estables y cuántas se están
   alejando: en BIS, de toda la sede y por comité asignado; en un comité, solo las suyas.
2. **Dado** que quiero ver el camino de una persona, **cuando** abro su ficha, **entonces** veo en
   qué etapa va, con la fecha de cada una: visitó el culto, asiste con frecuencia, recibe estudio
   bíblico, se bautizó en el nombre de Jesucristo, recibió el Espíritu Santo.
3. **Dado** que el sistema califica el compromiso de una persona, **cuando** consulto cómo lo
   determinó, **entonces** el sistema lo calcula con su asistencia a las actividades y cultos
   recientes y los días transcurridos desde el último contacto, y nadie lo escribe a mano.
4. **Dado** que visitamos a una persona, **cuando** registro la visita con su fecha, tipo y una
   nota, **entonces** esa visita queda en su historia y el indicador de compromiso se recalcula.
5. **Dado** que una persona necesita otro comité, **cuando** BIS la reasigna, **entonces** deja de
   verla el comité anterior, la ve el nuevo con toda su historia, y queda constancia del cambio.
6. **Dado** que una persona recorrió todas las etapas, **cuando** BIS o el presidente del comité
   asignado proponen su paso a miembro, **entonces** la solicitud le llega al pastor, y solo cuando
   él la apruebe la persona entra al padrón.

---

### Historia de Usuario 16 — HU-15 · Registrar asistencia (Prioridad: P2)

Como presidente, secretario o líder quiero anotar quién asistió a cada actividad de mi comité —un
ensayo, una reunión, un culto o un programa—, para saber a quién hay que buscar.

**Por qué esta prioridad**: todos los comités la pidieron en el levantamiento, pero la
coordinación funciona sin ella.

**Prueba independiente**: se puede probar sola tomando asistencia en una actividad interna y en una
del cronograma general, con integrantes y simpatizantes, corrigiendo una marca y verificando los
porcentajes por persona.

**Escenarios de Aceptación**:

1. **Dado** que tuvimos un ensayo o una reunión, **cuando** abro la actividad y marco quiénes
   asistieron, **entonces** queda registrado y el porcentaje de asistencia de cada persona se
   actualiza.
2. **Dado** que alguien no pudo asistir y avisó, **cuando** marco su inasistencia con la excusa,
   **entonces** queda como inasistencia excusada y se distingue de la de quien no avisó.
3. **Dado** que a la actividad vinieron simpatizantes, **cuando** los marco, **entonces** su
   asistencia cuenta para su nivel de compromiso (RF-043).
4. **Dado** que mi comité tiene un culto en el cronograma general, **cuando** tomo asistencia,
   **entonces** se registra igual que en una actividad interna.
5. **Dado** que me equivoqué al marcar, **cuando** abro la actividad y corrijo, **entonces** el
   sistema guarda la corrección y conserva qué se había registrado antes.
6. **Dado** que reviso la asistencia por persona, **cuando** abro la lista, **entonces** veo a
   quién fue convocado, cuántas veces asistió y su porcentaje, con una señal clara —y en tono de
   cuidado, no de reproche— en quienes están por debajo del sesenta por ciento.

---

### Historia de Usuario 17 — HU-16 · Manejar el presupuesto del comité (Prioridad: P2)

Como presidente de comité quiero ver cuánto me asignaron, cuánto llevo gastado y cuánto me queda,
para no comprometer plata que no tengo.

**Por qué esta prioridad**: responde a «nadie sabe quién autorizó un gasto», pero requiere que la
cadena de aprobación ya funcione (HU-06, HU-07).

**Prueba independiente**: se puede probar sola asignando presupuesto, repartiéndolo entre
sub-comités, aprobando gastos hasta pasar el saldo y revisando la vista del pastor.

**Escenarios de Aceptación**:

1. **Dado** que el pastor asignó el presupuesto anual de mi comité, **cuando** abro la sección,
   **entonces** veo asignado, gastado, disponible y el porcentaje usado, y el reparto entre mis
   sub-comités.
2. **Dado** que un gasto deja mi saldo en negativo, **cuando** lo apruebo, **entonces** el sistema
   me advierte expresamente que me estoy pasando del presupuesto, y me permite continuar si lo
   confirmo.
3. **Dado** que soy líder de sub-comité, **cuando** necesito autorización para un gasto,
   **entonces** la solicito indicando concepto y monto, y le llega a mi presidente.
4. **Dado** que soy el pastor, **cuando** abro la sección, **entonces** veo todos los comités
   activos con su asignado, gastado y disponible, y el total de la congregación, destacando el que
   ya superó el noventa por ciento de lo asignado.
5. **Dado** que cualquiera abre esta sección, **cuando** la pantalla carga, **entonces** encuentra
   visible la aclaración de que la tesorería sigue manejando los recursos y que aquí solo se
   registra en qué se usaron y quién los autorizó.

---

### Historia de Usuario 18 — HU-17 · Saber qué tengo pendiente (Prioridad: P2)

Como persona con cargo quiero darme cuenta apenas entro de lo que me están esperando, para no
demorar las decisiones de mi comité.

**Por qué esta prioridad**: es el reemplazo de los avisos de WhatsApp. Como no habrá avisos
externos (S-01), es el único mecanismo por el que alguien se entera de lo que debe resolver.

**Prueba independiente**: se puede probar sola dejando solicitudes pendientes para cada cargo y
comprobando qué aparece primero en la pantalla de inicio y en el contador del menú.

**Escenarios de Aceptación**:

1. **Dado** que entro al sistema, **cuando** carga mi pantalla de inicio, **entonces** lo primero
   que veo es qué está esperando mi decisión y desde cuándo.
2. **Dado** que tengo solicitudes sin resolver, **cuando** miro el menú, **entonces** veo un
   contador con cuántas son.
3. **Dado** que soy líder y envié una solicitud, **cuando** entro, **entonces** veo en qué va,
   quién debe resolverla y, si ya fue resuelta, el resultado con su motivo.

---

### Historia de Usuario 19 — HU-18 · Cargar la lista inicial de miembros (Prioridad: P3)

Como pastor quiero subir de una vez la lista de los miembros de la congregación, para no digitar
ciento cuarenta fichas a mano.

**Por qué esta prioridad**: ahorra trabajo en la puesta en funcionamiento, pero los miembros
pueden registrarse uno por uno con HU-10 si esto no existe.

**Prueba independiente**: se puede probar sola subiendo una plantilla con filas correctas y
erróneas mezcladas y verificando la vista previa antes de guardar.

**Escenarios de Aceptación**:

1. **Dado** que tengo la lista en una plantilla, **cuando** la subo, **entonces** el sistema me
   muestra cuántas filas están correctas y cuáles tienen error, antes de guardar nada.
2. **Dado** que algunas filas tienen error, **cuando** reviso, **entonces** el sistema me indica la
   fila y qué está mal, y me permite cargar solo las correctas.
3. **Dado** que una persona de la lista ya está registrada, **cuando** la importo, **entonces** el
   sistema me advierte y me deja decidir si la actualizo o la omito.

---

### Historia de Usuario 20 — HU-19 · Corregir un dato equivocado (Prioridad: P3)

Como persona con cargo quiero corregir lo que registré mal, para que la información sea confiable.

**Por qué esta prioridad**: mejora la confianza en el día a día. El historial inmutable que exige
el tercer principio de la constitución se construye desde las primeras historias; esta historia
expone ese historial a la persona y habilita la corrección.

**Prueba independiente**: se puede probar sola corrigiendo un monto y una fecha, consultando el
historial del registro y comprobando que una aprobación ya resuelta no se puede editar.

**Escenarios de Aceptación**:

1. **Dado** que registré mal un monto o una fecha, **cuando** lo corrijo, **entonces** el sistema
   guarda el cambio y conserva qué decía antes, quién lo cambió y cuándo.
2. **Dado** que consulto el historial de un registro, **cuando** lo abro, **entonces** veo la
   secuencia completa de cambios.
3. **Dado** que se trata de una aprobación ya resuelta, **cuando** intento modificarla,
   **entonces** el sistema no la deja editar: debe registrarse una nueva decisión, porque las
   aprobaciones no se borran.

---

---

Las cuatro historias que siguen entran por la ampliación de alcance del 2026-10-24. Se numeran a
continuación para no renumerar las veinte anteriores, pero **HU-21 es P1 y se construye en el
cimiento**, junto con HU-03: sin estructura configurable no hay nada que configurar. Las otras tres
son P2.

### Historia de Usuario 21 — HU-21 · Administrar la estructura de comités (Prioridad: P1)

Como pastor quiero crear, renombrar, reordenar e inactivar comités y sub-comités, para que el
organigrama del sistema sea el de la congregación de hoy y no el del día que se instaló.

**Por qué esta prioridad**: la estructura es el esqueleto del que dependen el filtro de alcance, las
aprobaciones, el presupuesto y la asistencia. Y no se puede cablear: cambia entre vigencias.

**Prueba independiente**: se puede probar sola creando un comité con dos sub-comités,
renombrándolos, inactivando uno y comprobando que su historia sigue consultable.

**Escenarios de Aceptación**:

1. **Dado** que la congregación organiza un comité nuevo, **cuando** lo creo con su nombre y su
   responsable, **entonces** queda disponible de inmediato para proponer actividades, recibir
   presupuesto y registrar asistencia.
2. **Dado** que un comité necesita dividirse, **cuando** le creo los sub-comités que necesite,
   **entonces** el sistema los acepta y el ámbito de su presidente incluye a todos ellos.
3. **Dado** que intento crear un grupo dentro de un sub-comité, **cuando** guardo, **entonces** el
   sistema no lo permite y explica que la estructura llega hasta dos niveles.
4. **Dado** que un comité cambia de nombre, **cuando** lo renombro, **entonces** las actas y los
   reportes ya registrados siguen mostrando el nombre que tenía en esa fecha.
5. **Dado** que un comité deja de funcionar, **cuando** lo inactivo, **entonces** no aparece más
   para proponer cosas nuevas y su historia completa sigue consultable. Sus integrantes salen de su
   lista, y quien no pertenece a ningún otro comité queda señalado para que yo lo reasigne. Sus
   simpatizantes vuelven a BIS.
6. **Dado** que soy presidente o líder, **cuando** entro a la sección de estructura, **entonces** la
   veo pero no puedo modificarla: solo el pastor la administra.

---

### Historia de Usuario 22 — HU-22 · Levantar el acta de una reunión (Prioridad: P2)

Como secretario de un comité quiero escribir el acta de cada reunión y poder imprimirla, para que el
trabajo del comité quede digitalizado y no en un cuaderno que se pierde.

**Por qué esta prioridad**: es el reemplazo del libro de actas, pero el comité funciona sin ella
mientras registre su asistencia y sus aprobaciones.

**Prueba independiente**: se puede probar sola registrando una reunión, escribiendo su acta,
cerrándola e imprimiéndola.

**Escenarios de Aceptación**:

1. **Dado** que mi comité tuvo una reunión —prevista en su cronograma o imprevista— o cumplió una
   actividad programada, **cuando** levanto su acta, **entonces** el sistema trae la fecha, el
   lugar y los asistentes, y me deja escribir el orden del día, el desarrollo y los compromisos.
2. **Dado** que estoy escribiendo un acta, **cuando** la guardo sin terminar, **entonces** queda
   como borrador, visible para mi comité, y puedo seguir después.
3. **Dado** que el acta está lista, **cuando** el presidente la cierra, **entonces** el sistema le
   asigna su número del año, la deja con los dos nombres y la fecha, y **nadie la puede editar más**.
4. **Dado** que una acta cerrada tiene un error, **cuando** intento corregirla, **entonces** el
   sistema no lo permite y me explica que se corrige levantando un acta nueva que haga constar la
   corrección.
5. **Dado** que necesito el acta en papel, **cuando** la imprimo, **entonces** obtengo una hoja
   legible en blanco y negro con el nombre de la iglesia, el comité, el número del acta, la fecha,
   los asistentes, el desarrollo, los compromisos y el espacio de las firmas.
6. **Dado** que un compromiso del acta necesita seguimiento, **cuando** lo convierto en tarea con su
   responsable y su fecha, **entonces** aparece en los pendientes de esa persona y cuenta para el
   avance de la actividad.
7. **Dado** que soy de otro comité, **cuando** consulto las actas, **entonces** no veo ninguna de un
   comité que no es el mío.

---

### Historia de Usuario 23 — HU-23 · Respaldar el cumplimiento con evidencias (Prioridad: P2)

Como líder o presidente quiero adjuntar el soporte de una actividad —una foto, una imagen o un
documento—, para demostrar que se cumplió sin tener que explicarlo de palabra.

**Por qué esta prioridad**: da respaldo a lo que el cronograma afirma, pero el avance ya se calcula
sin ella.

**Prueba independiente**: se puede probar sola subiendo el soporte de una actividad de mi comité,
reemplazándolo y comprobando que un cargo ajeno no lo ve.

**Escenarios de Aceptación**:

1. **Dado** que mi comité cumplió una actividad, **cuando** subo su soporte, **entonces** queda
   adjunto a la actividad con mi nombre y la fecha. Es uno solo por actividad, y es opcional.
2. **Dado** que subo una foto tomada con el celular, **cuando** el sistema la recibe, **entonces**
   la reduce para que pese menos sin que yo tenga que hacer nada, y me avisa si pasa de 10 MB.
3. **Dado** que soy de otro comité, **cuando** consulto esa actividad en el calendario general,
   **entonces** veo sus seis datos (RF-010), pero **no su soporte**.
4. **Dado** que subí la foto equivocada a una actividad ya cumplida, **cuando** subo la correcta,
   **entonces** el sistema no borra la anterior: la marca como reemplazada y la conserva en la
   historia, y la nueva queda como el soporte vigente.
5. **Dado** que una actividad no tiene soporte, **cuando** la abro, **entonces** el sistema lo
   indica con naturalidad y no como un error: no toda actividad necesita respaldo.

---

### Historia de Usuario 24 — HU-24 · Subir el soporte de un gasto (Prioridad: P2)

Como tesorero de un comité quiero adjuntar la factura o el recibo a cada gasto, para que quede claro
en qué se usó el dinero y quién lo autorizó.

**Por qué esta prioridad**: cierra el registro del presupuesto, que hoy guarda el soporte como un
texto. La tesorería de la iglesia sigue manejando los recursos.

**Prueba independiente**: se puede probar sola registrando un gasto con su factura en PDF y
comprobando que el ámbito del comité se respeta.

**Escenarios de Aceptación**:

1. **Dado** que registro un gasto de mi comité, **cuando** adjunto la factura en PDF o una foto del
   recibo, **entonces** queda unida al movimiento con mi nombre y la fecha.
2. **Dado** que un movimiento ya quedó registrado, **cuando** intento quitarle el soporte,
   **entonces** el sistema no lo permite: se registra un movimiento nuevo que corrija, porque lo
   registrado no se borra.
3. **Dado** que soy el pastor, **cuando** reviso el presupuesto de un comité, **entonces** puedo
   abrir el soporte de cualquiera de sus movimientos.
4. **Dado** que soy de otro comité, **cuando** consulto el presupuesto, **entonces** no veo ni los
   movimientos ni los soportes ajenos.

---

---

Las trece historias que siguen entran con el levantamiento de la sede La Esmeralda del
2026-10-28. HU-25 y HU-27 son P1: el nombre y el lema se ven desde la entrada, y los cronogramas
internos son el trabajo de cada semana de todos los comités. Las demás son P2.

### Historia de Usuario 25 — HU-25 · Ver el nombre y el lema de la sede (Prioridad: P1)

Como persona con cargo quiero ver el nombre de mi sede y el lema del año al entrar y mientras
trabajo, para sentir que el sistema es de mi congregación y tener presente hacia dónde va este año.

**Por qué esta prioridad**: es lo primero que ve cada persona, y el lema acompaña el cronograma del
año: la congregación pidió verlo en la entrada y en toda la plataforma.

**Prueba independiente**: se puede probar sola registrando el lema de un año y comprobando la
entrada y la barra de la plataforma, antes y después del cambio de año.

**Escenarios de Aceptación**:

1. **Dado** que abro el sistema sin haber entrado, **cuando** carga la pantalla de entrada,
   **entonces** veo «IPUC La Esmeralda - Ibagué» y el lema del año en curso con su cita bíblica.
2. **Dado** que ya entré, **cuando** recorro cualquier pantalla, **entonces** el nombre de la sede y
   el lema del año siguen a la vista, sin quitarle espacio al contenido en el celular.
3. **Dado** que soy el pastor y viene un año nuevo, **cuando** registro el lema de ese año con su
   cita, **entonces** desde el primer día del año la entrada y la plataforma muestran el nuevo, y
   el anterior queda en la historia.
4. **Dado** que el año empezó y todavía no hay lema registrado, **cuando** alguien entra,
   **entonces** el sistema muestra solo el nombre de la sede, sin un espacio vacío ni el lema
   viejo.

---

### Historia de Usuario 26 — HU-26 · Dar identidad a mi comité (Prioridad: P2)

Como presidente de comité quiero registrar el logo, el lema y los objetivos de mi comité, para que
se vean en su ficha y en lo que imprimimos.

**Por qué esta prioridad**: cada comité trabaja con su identidad propia —los planes que entregaron
empiezan por ella—, pero el sistema funciona sin ella.

**Prueba independiente**: se puede probar sola subiendo el logo de un comité, escribiendo su lema y
sus objetivos, imprimiendo algo del comité y comprobando lo que ve otro comité.

**Escenarios de Aceptación**:

1. **Dado** que soy presidente, **cuando** subo el logo y escribo el lema y los objetivos,
   **entonces** quedan en la ficha del comité, con mi nombre y la fecha del cambio.
2. **Dado** que mi comité imprime sus actividades, un orden de culto o un acta, **cuando** sale la
   hoja, **entonces** lleva el logo y el lema del comité junto al nombre de la sede.
3. **Dado** que un comité no tiene logo, **cuando** se muestra, **entonces** aparece con sus
   iniciales en un círculo, no con un espacio vacío.
4. **Dado** que soy de otro comité, **cuando** veo sus actividades en el calendario general,
   **entonces** veo el nombre del comité responsable y nada más de su identidad.
5. **Dado** que soy líder de un sub-comité, **cuando** abro la ficha de mi comité, **entonces** veo
   su identidad, pero no la puedo cambiar.

---

### Historia de Usuario 27 — HU-27 · Llevar los cronogramas internos de mi comité (Prioridad: P1)

Como presidente o líder quiero llevar los cronogramas internos de mi comité —uno por semestre, por
módulo o por grupo—, para organizar los ensayos, las clases, las reuniones y los programas que no
van en el cronograma general.

**Por qué esta prioridad**: todos los comités los piden en el levantamiento. Es su trabajo de cada
semana, y hoy está en cuadernos.

**Prueba independiente**: se puede probar sola creando dos cronogramas internos y una actividad que
se repite, comprobando que se cruzan sin problema y que un cargo ajeno no los ve.

**Escenarios de Aceptación**:

1. **Dado** que soy presidente, **cuando** creo un cronograma interno con su nombre y sus fechas
   —por ejemplo «Módulo 1, enero a marzo»—, **entonces** queda solo para mi comité, mis
   sub-comités y el pastor.
2. **Dado** que tengo un cronograma interno, **cuando** agrego una actividad con título, tema,
   fecha, hora de inicio y de fin, lugar y responsable, **entonces** queda registrada sin pasar por
   aprobación (P-03).
3. **Dado** que un programa se hace cada semana, cada quince días o cada mes, **cuando** lo agrego
   como repetido entre dos fechas, **entonces** el sistema crea cada fecha, y cada una se puede
   mover o cancelar sola.
4. **Dado** que una actividad interna coincide con otra —interna o del cronograma general—,
   **cuando** la guardo, **entonces** el sistema la acepta: las internas sí se pueden cruzar.
5. **Dado** que soy de otro comité, **cuando** busco por cualquier vía un cronograma interno ajeno,
   **entonces** no lo veo ni en el calendario ni en la lista, y el sistema no revela que existe.
6. **Dado** que una actividad interna necesita plata, **cuando** pido el gasto, **entonces** sigue
   la cadena de aprobación de siempre (RF-015).

---

### Historia de Usuario 28 — HU-28 · Preparar el orden de culto (Prioridad: P2)

Como presidente o secretario de un comité quiero dejar listo el orden de cada culto que nos toca
—quién abre, el himno, la oración, la ofrenda, quién predica—, para que todos sepan con tiempo
quién hace qué.

**Por qué esta prioridad**: cada comité tiene cultos programados y hoy el orden se arma a última
hora, pero el cronograma funciona sin él.

**Prueba independiente**: se puede probar sola con un culto de un comité en el cronograma general,
llenando su orden, dejando otro incompleto y comprobando los avisos de los quince y de los ocho
días.

**Escenarios de Aceptación**:

1. **Dado** que mi comité tiene un culto en el cronograma general, **cuando** abro su orden de
   culto, **entonces** veo las partes de siempre —apertura, himno, oración y peticiones, ofrenda y
   predicación— y puedo agregar otras.
2. **Dado** que asigno cada parte, **cuando** escojo a la persona entre los integrantes de mi
   comité o escribo el nombre de alguien de afuera, **entonces** queda en el orden.
3. **Dado** que faltan quince días para el culto y el orden no está completo, **cuando** la
   directiva entra, **entonces** lo ve en su pantalla de inicio como pendiente.
4. **Dado** que faltan ocho días o menos y el orden sigue incompleto, **cuando** el presidente o el
   pastor lo consultan, **entonces** el sistema lo señala como atrasado, en tono de cuidado (P-04).
5. **Dado** que el orden está listo, **cuando** lo imprimo o lo comparto por WhatsApp, **entonces**
   sale con la fecha, la hora, el comité y cada parte con su responsable.
6. **Dado** que soy de otro comité, **cuando** veo ese culto en el calendario, **entonces** veo sus
   seis datos, pero no su orden.

---

### Historia de Usuario 29 — HU-29 · Saludar a quien cumple años (Prioridad: P2)

Como persona con cargo quiero que el sistema me recuerde los cumpleaños de los hermanos de mi
comité, para saludarlos y celebrarlos a tiempo.

**Por qué esta prioridad**: todos los comités llevan los cumpleaños en un cuaderno y la congregación
pidió verlos al entrar, pero no detienen ningún trabajo.

**Prueba independiente**: se puede probar sola con miembros, simpatizantes y niños que cumplen hoy,
esta semana y otro mes, entrando con cargos de comités distintos.

**Escenarios de Aceptación**:

1. **Dado** que hoy cumple años alguien de mi ámbito, **cuando** entro, **entonces** veo una alerta
   clara y amigable con su nombre y los años que cumple, que puedo cerrar.
2. **Dado** que en los próximos siete días cumplen años otros hermanos, **cuando** abro la alerta,
   **entonces** los veo con su fecha.
3. **Dado** que quiero planear la celebración del mes, **cuando** abro la lista de cumpleaños,
   **entonces** veo por mes los miembros, los simpatizantes y, en la Escuela Dominical, los niños de
   mi ámbito, con su fecha y su código.
4. **Dado** que una persona no tiene fecha de nacimiento —solo la edad anotada—, **cuando** se arma
   la lista, **entonces** no aparece, y el sistema indica cuántas fichas no tienen fecha.
5. **Dado** que soy de otro comité, **cuando** abro mis cumpleaños, **entonces** no aparece nadie
   que no esté en mi ámbito.

---

### Historia de Usuario 30 — HU-30 · Imprimir y compartir las actividades (Prioridad: P2)

Como presidente de comité quiero imprimir las actividades que les tocan a mis sub-comités y
compartírselas por WhatsApp, para que cada líder tenga su programación sin que yo la copie a mano.

**Por qué esta prioridad**: hoy la programación se reparte copiándola en el grupo de WhatsApp, pero
cada líder puede consultarla en el sistema sin esto.

**Prueba independiente**: se puede probar sola imprimiendo y compartiendo las actividades de un
sub-comité en un periodo, como presidente y como líder.

**Escenarios de Aceptación**:

1. **Dado** que escojo un sub-comité y un periodo, **cuando** pido imprimir, **entonces** obtengo
   una hoja legible en blanco y negro con el nombre de la sede, el comité, el periodo y sus
   actividades —del cronograma general y de los internos— con fecha, hora, lugar y responsable.
2. **Dado** que quiero mandarla por WhatsApp, **cuando** toco «Compartir por WhatsApp», **entonces**
   el celular abre WhatsApp con el resumen escrito, listo para escoger a quién enviarlo.
3. **Dado** que soy líder de sub-comité, **cuando** imprimo o comparto, **entonces** solo salen las
   actividades de mi sub-comité.
4. **Dado** que nadie toca el botón, **cuando** pasa el tiempo, **entonces** el sistema no manda
   nada por su cuenta: compartir es siempre un acto de la persona (S-01).

---

### Historia de Usuario 31 — HU-31 · Registrar las ayudas de Obra Social (Prioridad: P2)

Como presidente o tesorero de Obra Social quiero registrar mes a mes los mercados, el dinero y los
elementos que entregamos, para saber a quién hemos ayudado y mostrarlo con cifras.

**Por qué esta prioridad**: es el trabajo central de Obra Social, pero solo de ese comité.

**Prueba independiente**: se puede probar sola registrando ayudas de varios tipos en dos meses,
abriendo el informe mensual y comprobando que otro comité no ve nada.

**Escenarios de Aceptación**:

1. **Dado** que entregamos una ayuda, **cuando** registro la fecha, a quién, el tipo —mercado,
   dinero, elementos, visita con aporte u otra—, el valor o lo que se entregó, y una foto si la
   hay, **entonces** queda en la historia de esa persona y en el mes.
2. **Dado** que la persona no está en nuestra lista, **cuando** la agrego, **entonces** queda como
   beneficiaria con nombre, teléfono, barrio y su condición —madre cabeza de familia, adulto mayor
   u otra—, sea hermano de la congregación o amigo.
3. **Dado** que abro el informe, **cuando** la pantalla carga, **entonces** veo cuántas ayudas, de
   qué tipo y por cuánto, con un gráfico mes a mes y la misma información en tabla.
4. **Dado** que reparto los mercados del mes, **cuando** abro la lista mensual, **entonces** veo
   quiénes recibieron y quiénes de la lista todavía no, para no repetir ni olvidar a nadie.
5. **Dado** que soy de otro comité, **cuando** busco por cualquier vía, **entonces** no veo ni las
   ayudas ni la lista de beneficiarios.

---

### Historia de Usuario 32 — HU-32 · Llevar el fondo propio de Obra Social (Prioridad: P2)

Como tesorero de Obra Social quiero registrar los aportes que nos dan y lo que sale del fondo, para
saber cuánto tenemos, aparte del presupuesto general del año.

**Por qué esta prioridad**: el levantamiento lo pide expresamente como un dinero independiente del
presupuesto general, pero solo para este comité.

**Prueba independiente**: se puede probar sola registrando aportes y salidas, comprobando el saldo
y que el presupuesto general del comité no cambia.

**Escenarios de Aceptación**:

1. **Dado** que un hermano aporta voluntariamente, **cuando** registro quién, cuándo y qué —dinero
   o elementos—, **entonces** entra al fondo propio del comité, y no al presupuesto general
   (RF-050).
2. **Dado** que los integrantes del comité aportan cada mes, **cuando** registro el aporte de cada
   uno, **entonces** veo quién aportó en cada mes.
3. **Dado** que entregamos una ayuda en dinero, pagamos un taller o compramos lo de una visita,
   **cuando** lo registro, **entonces** sale del fondo y el saldo se recalcula.
4. **Dado** que abro el fondo, **cuando** carga, **entonces** veo lo recibido, lo entregado y el
   saldo, con la aclaración de que la tesorería de la iglesia sigue manejando los recursos.
5. **Dado** que registré mal un aporte, **cuando** lo corrijo, **entonces** el sistema guarda el
   cambio y conserva lo que decía antes (RF-056).

---

### Historia de Usuario 33 — HU-33 · Organizar el servicio de Alabanza (Prioridad: P2)

Como presidente de Alabanza quiero dejar anotado, para cada culto, ensayo o presentación, quién toca
y quién canta, para que cada integrante sepa a tiempo lo que le toca.

**Por qué esta prioridad**: es el formato que pidió Alabanza, pero solo de ese comité.

**Prueba independiente**: se puede probar sola armando el formato de un culto, agregando una función
nueva y compartiéndolo.

**Escenarios de Aceptación**:

1. **Dado** que tenemos un culto o una presentación, **cuando** abro su formato de Alabanza,
   **entonces** asigno entre nuestros integrantes el pianista, el baterista, las voces principales
   y las voces de apoyo.
2. **Dado** que necesitamos otra función —guitarra, bajo, director—, **cuando** la agrego a la
   lista, **entonces** queda disponible para todas las actividades.
3. **Dado** que una persona ya tiene una función en esa actividad, **cuando** le doy otra,
   **entonces** el sistema me lo advierte sin impedirlo.
4. **Dado** que tuvimos un ensayo del cronograma interno, **cuando** tomo asistencia, **entonces**
   queda por persona y se calcula su porcentaje de asistencia a las prácticas (RF-048).
5. **Dado** que el formato está listo, **cuando** lo comparto por WhatsApp, **entonces** sale con la
   fecha, la actividad y cada función con su responsable.

---

### Historia de Usuario 34 — HU-34 · Organizar los grupos de la Escuela Dominical (Prioridad: P2)

Como presidente de la Escuela Dominical quiero tener los grupos de niños por edad, con su maestro y
su auxiliar, para saber quién atiende a quién.

**Por qué esta prioridad**: es la base del módulo de la Escuela Dominical, que solo usa ese comité.

**Prueba independiente**: se puede probar sola con los cinco grupos iniciales, cambiando un rango,
creando un grupo y asignando maestros.

**Escenarios de Aceptación**:

1. **Dado** que el comité empieza, **cuando** abre la Escuela Dominical, **entonces** encuentra los
   cinco grupos: de 0 a 2 años, Semillitas de Amor (3 a 5), Huellitas de Fe (6 a 8), Corazones
   Valientes (9 a 11) y Generación Elegida (12 a 17).
2. **Dado** que necesitamos cambiar un grupo, **cuando** lo creo, lo renombro, le cambio el rango de
   edad o lo desactivo, **entonces** el sistema me advierte antes de guardar si quedan rangos que se
   pisan o edades sin grupo.
3. **Dado** que asigno el maestro y el auxiliar de cada grupo, **cuando** los escojo entre los
   integrantes del comité, **entonces** quedan en la ficha del grupo.
4. **Dado** que reviso la lista del comité, **cuando** la abro, **entonces** distingo la directiva,
   los maestros y los auxiliares.
5. **Dado** que soy de otro comité, **cuando** busco, **entonces** no veo los grupos ni a los niños.

---

### Historia de Usuario 35 — HU-35 · Inscribir a un niño en la Escuela Dominical (Prioridad: P2)

Como integrante de la directiva de la Escuela Dominical quiero inscribir a cada niño con el
formulario de la Escuela Dominical, para tener sus datos a la mano cuando lo atendemos.

**Por qué esta prioridad**: sin la inscripción no hay a quién tomar asistencia ni de quién llevar
estadísticas.

**Prueba independiente**: se puede probar sola inscribiendo niños de varias edades, haciendo que uno
cumpla años y comprobando quién ve sus datos de salud.

**Escenarios de Aceptación**:

1. **Dado** que inscribo a un niño, **cuando** diligencio sus datos —identidad, familia, estudios,
   salud e iglesia— y su fecha de nacimiento, **entonces** queda inscrito y el sistema lo ubica
   solo en el grupo de su edad.
2. **Dado** que un niño cumple la edad del grupo siguiente, **cuando** llega ese día, **entonces**
   aparece en el nuevo grupo sin que nadie lo mueva, y su historia en el anterior se conserva.
3. **Dado** que un niño cumple dieciocho años, **cuando** llega ese día, **entonces** sale de los
   grupos y queda señalado, para que la directiva decida con su familia.
4. **Dado** que la ficha trae datos de salud, **cuando** la abre alguien que no es de la Escuela
   Dominical ni el pastor, **entonces** no los ve.
5. **Dado** que el niño no tiene firmada la autorización de imagen, **cuando** intento subir su
   foto, **entonces** el sistema no la deja subir y explica por qué (RF-110).
6. **Dado** que inscribo a un niño que ya está inscrito, **cuando** guardo, **entonces** el sistema
   me advierte y me deja continuar o cancelar.

---

### Historia de Usuario 36 — HU-36 · Firmar la autorización de los padres (Prioridad: P2)

Como integrante de la Escuela Dominical quiero que el padre, la madre o el acudiente firme en mi
celular las autorizaciones del niño, para tenerlas guardadas e imprimirlas cuando haga falta, sin
papeles que se pierdan.

**Por qué esta prioridad**: protege a los niños y a la iglesia, y el levantamiento la pide con firma
y para imprimir; pero el registro de los niños funciona sin ella.

**Prueba independiente**: se puede probar sola haciendo firmar las dos autorizaciones de un niño,
imprimiéndolas e intentando cambiar una ya firmada.

**Escenarios de Aceptación**:

1. **Dado** que el acudiente está conmigo, **cuando** abro la autorización de asistencia y
   participación, **entonces** el sistema trae el texto oficial con los datos del niño y del
   acudiente ya puestos, y le pide firmar con el dedo en la pantalla.
2. **Dado** que firma la autorización de uso de imagen, **cuando** marca lo que comprende —fotos,
   audios, videos, otros datos—, **entonces** queda guardado exactamente lo que autorizó, con la
   fecha.
3. **Dado** que una autorización ya está firmada, **cuando** alguien intenta cambiarla,
   **entonces** el sistema no lo permite: si algo cambia, se firma una nueva, y las dos quedan en la
   historia.
4. **Dado** que necesito el papel, **cuando** imprimo la autorización, **entonces** sale igual al
   formato oficial, con la firma, la fecha y los números de documento.
5. **Dado** que el acudiente no puede firmar en la pantalla, **cuando** firma el papel, **entonces**
   puedo subir la foto del formato firmado como soporte.
6. **Dado** que abro las estadísticas, **cuando** cargan, **entonces** veo cuántos niños no tienen
   sus autorizaciones firmadas.

---

### Historia de Usuario 37 — HU-37 · Llevar las clases y las visitas de la Escuela Dominical (Prioridad: P2)

Como presidente de la Escuela Dominical quiero llevar el cronograma de clases de cada grupo, la
asistencia y las visitas, para saber qué se enseña, quién lo enseña y cómo van los niños y los
maestros.

**Por qué esta prioridad**: reemplaza las carpetas por maestro y las planillas de visitas, pero
depende de los grupos y de la inscripción (HU-34, HU-35).

**Prueba independiente**: se puede probar sola armando el semestre de un grupo con un día sin clase,
tomando asistencia, registrando una visita y filtrando.

**Escenarios de Aceptación**:

1. **Dado** que armo el semestre de un grupo, **cuando** registro cada clase con la fecha del
   cuaderno, la fecha de la clase, el tema, el maestro, el auxiliar y el aseo, **entonces** queda en
   el cronograma del grupo.
2. **Dado** que un domingo no hay clase —por el ayuno de los maestros, por ejemplo—, **cuando** lo
   registro con el motivo, **entonces** aparece así en el cronograma y no cuenta para la
   asistencia.
3. **Dado** que quiero ver lo que viene, **cuando** filtro por grupo, mes o maestro, **entonces** la
   lista y el calendario muestran solo eso.
4. **Dado** que tuvimos clase, **cuando** tomo asistencia del grupo, **entonces** se registra por
   niño, y el informe del mes muestra la asistencia de cada uno.
5. **Dado** que visitamos a un maestro, **cuando** registro la fecha, el grupo, el maestro visitado,
   la asistencia, la necesidad que encontramos y la novedad, **entonces** queda en su historia; y
   la visita a un niño, con los maestros que fueron y una foto si la hay.
6. **Dado** que abro las estadísticas de los niños, **cuando** cargan, **entonces** veo cuántos hay
   por grupo, por género y por edad, con su tabla equivalente.

---

### Casos Límite

- **CL-01.** Un comité sin sub-comités: la ficha debe explicarlo, no mostrar una sección vacía.
- **CL-02.** Un miembro que pertenece al comité pero a ningún sub-comité.
- **CL-03.** Un miembro que no pertenece a ningún comité: debe seguir contando en los reportes de
  la congregación y señalarse como oportunidad.
- **CL-04.** Un comité sin presupuesto asignado: las pantallas de presupuesto deben funcionar en
  cero, no fallar.
- **CL-05.** Una persona con dos cargos: debe poder actuar con cualquiera de los dos escogiendo
  cuál es el activo, y nunca ver los dos ámbitos mezclados en una misma consulta.
- **CL-06.** Un cargo vacante: sus pendientes no pueden quedar sin responsable.
- **CL-07.** Una actividad propuesta para una fecha ya pasada: el sistema debe impedirlo.
- **CL-08.** Dos actividades del cronograma general que se cruzan en fecha y horario: el sistema no
  deja proponer ni aprobar la segunda (RF-028). Las actividades internas sí se cruzan.
- **CL-09.** Un simpatizante registrado dos veces por personas distintas. Dentro del ámbito propio,
  la lista que se completa mientras se escribe lo evita. Fuera de él, el sistema advierte que ya
  existe sin mostrarlo (RF-096).
- **CL-10.** Un simpatizante que se aleja y vuelve meses después: su historia no se reinicia.
- **CL-11.** Una reunión sin ningún asistente: debe poder registrarse, porque es información.
- **CL-12.** Un reporte sobre un grupo vacío: debe mostrar cero, no una pantalla rota.
- **CL-13.** El año calendario cambia: el plan del año anterior se conserva consultable y el nuevo
  empieza sin aprobar.
- **CL-14.** Una importación con filas correctas y erróneas mezcladas: deben poder cargarse solo
  las correctas.
- **CL-15.** Pérdida de conexión durante el registro: el sistema debe informarlo sin perder lo que
  la persona escribió.
- **CL-16.** Ficha de miembro pendiente de completar, sea porque nació al habilitar una cuenta
  (RF-003) o porque no tiene ni fecha de nacimiento ni edad anotada (RF-031): aparece en las listas
  y en los reportes señalada como incompleta, y el sistema indica cuántas faltan por completar.
- **CL-17.** Un sub-comité —segundo nivel— intenta tener un hijo: el sistema debe impedirlo y
  explicar que la estructura llega hasta dos niveles.
- **CL-18.** Un comité inactivado:
  - su historia completa sigue consultable y no aparece para proponer nada nuevo;
  - sus integrantes que no pertenecen a otro comité quedan señalados para reasignar;
  - sus simpatizantes vuelven a BIS.
- **CL-19.** Un comité renombrado: las actas y los reportes anteriores conservan el nombre de su
  fecha; el nombre nuevo rige desde el cambio.
- **CL-20.** Un comité sin secretario: el presidente redacta y cierra el acta, y el sistema lo
  indica en lugar de bloquear el registro.
- **CL-21.** Una reunión sin acta: se puede consultar la asistencia sin más; el sistema no supone
  que toda reunión tenga acta.
- **CL-22.** Un acta cerrada con un error: no se edita. El sistema ofrece levantar una acta nueva
  que haga constar la corrección, y las dos quedan en la historia.
- **CL-23.** Un archivo que pasa de 10 MB: el sistema lo rechaza con una frase que dice cuánto pesa
  y cuál es el límite, sin perder lo que la persona ya había escrito en el formulario.
- **CL-24.** El disco del servidor se queda sin espacio: el sistema avisa a quien sube y al pastor,
  en lenguaje claro, en vez de fallar sin explicación.
- **CL-25.** Un archivo adjunto a algo ya cumplido que hay que reemplazar: se marca como
  reemplazado y se conserva; nunca desaparece de la historia.
- **CL-26.** Una actividad o un movimiento sin archivos: se indica con naturalidad, no como un
  error. No todo necesita respaldo.
- **CL-27.** Un miembro que pertenece a varios comités: aparece en la lista de cada uno y cuenta
  una sola vez en los totales de la congregación.
- **CL-28.** Un comité que intenta corregir la ficha de un integrante: el sistema lo impide y
  explica que las fichas las corrige el pastor.
- **CL-29.** Un simpatizante asignado a un comité que se inactiva: vuelve a BIS, con toda su
  historia.
- **CL-30.** Una fecha de una actividad repetida que se cancela o se mueve: cambia solo esa fecha, y
  las demás siguen.
- **CL-31.** Un culto cuyo orden no está completo a ocho días: se señala como atrasado, en tono de
  cuidado.
- **CL-32.** Alguien que cumple años el 29 de febrero: en los años que no son bisiestos se le
  recuerda el 28 de febrero.
- **CL-33.** Un niño que cumple la edad del grupo siguiente a mitad de año: pasa solo al grupo
  nuevo ese día, y su asistencia anterior queda en el grupo anterior.
- **CL-34.** Un niño que cumple dieciocho años: sale de los grupos y queda señalado para que la
  directiva decida con su familia.
- **CL-35.** Rangos de edad que se pisan, o edades sin grupo, al editar los grupos de la Escuela
  Dominical: el sistema lo advierte antes de guardar.
- **CL-36.** Un acudiente que no puede firmar en la pantalla: se sube la foto del formato firmado en
  papel.
- **CL-37.** Una foto de un niño sin autorización de imagen firmada: el sistema no la deja subir.
- **CL-38.** Una ayuda de Obra Social a alguien que no está en la lista de beneficiarios: se agrega
  en el mismo paso.
- **CL-39.** El fondo de Obra Social queda en negativo: se advierte y se permite continuar si se
  confirma, igual que el presupuesto (RF-051).
- **CL-40.** Un año sin lema registrado: la entrada muestra solo el nombre de la sede.
- **CL-41.** Una persona que ya tiene una función de Alabanza en la misma actividad: se advierte
  sin impedir.

## Requisitos *(obligatorio)*

Se conserva la numeración del documento de entrada (RF-001 a RF-057). RF-058 a RF-060 se agregan
para que las promesas del celular y del lenguaje queden verificables, conforme a los principios II
y IV de la constitución. RF-061 y RF-062 provienen de la sesión de aclaraciones del 2026-10-18.
RF-063 a RF-080 provienen de la ampliación de alcance del 2026-10-24: estructura administrable en
tres niveles (RF-063 a RF-066), actas de reunión (RF-067 a RF-074) y archivos adjuntos (RF-075 a
RF-080). RF-081 a RF-114 provienen del levantamiento de la sede La Esmeralda del 2026-10-28, que
además reescribió RF-009 a RF-011, RF-013, RF-014, RF-019, RF-028, RF-029, RF-031, RF-036, RF-040,
RF-043, RF-046, RF-047, RF-063, RF-064, RF-067 y RF-075. RF-115 a RF-118 salen del formato del
cronograma que trae el portafolio de la sede, revisado el mismo día.

### Requisitos Funcionales

#### Acceso y cuentas

- **RF-001**: El sistema permite entrar con un usuario asignado y una clave.
- **RF-002**: El sistema obliga a cambiar la clave en el primer ingreso.
- **RF-003**: Solo el pastor crea, modifica y retira cuentas, y asigna cargo y comité. Toda cuenta
  queda ligada a la única ficha de miembro de esa persona: si todavía no está registrada, el
  sistema le crea la ficha con nombre y teléfono y la señala como pendiente de completar. Una
  persona tiene una sola ficha, tenga cargo o no.
- **RF-004**: El sistema bloquea el acceso por quince minutos tras cinco intentos fallidos.
- **RF-005**: Solo el pastor restablece claves.
- **RF-006**: Al retirar un acceso, la persona deja de entrar de inmediato y su nombre se conserva
  en todo registro histórico.

#### Alcance de la información

- **RF-007**: Toda consulta devuelve únicamente la información del ámbito del cargo activo. Una
  persona con dos o más cargos tiene siempre un único cargo activo: un selector visible le permite
  cambiarlo en cualquier momento, cada pantalla indica con qué cargo está actuando, y el sistema
  nunca mezcla información de dos ámbitos en una misma consulta.
- **RF-008**: El intento de acceder a información fuera del ámbito produce una respuesta que
  explica la situación sin revelar ningún dato.
- **RF-009**: El sistema informa a cada cargo cuánta información de la congregación queda fuera de
  su alcance, sin detallarla. Es una de las preguntas que miran fuera del ámbito sin traer datos
  (constitución, principio I, excepción 3).
- **RF-010**: El cronograma general es compartido. Toda persona con cargo lo consulta completo en el
  calendario, y de una actividad de otro comité ve exactamente seis datos: título, fecha, hora,
  lugar, comité responsable y estado. La descripción, el presupuesto, las tareas internas, el
  soporte y el orden de culto quedan reservados a su comité, sus ascendientes y el pastor. Los
  cronogramas internos no se comparten. El tema y la marca de color (RF-116) tampoco viajan de una
  actividad ajena mientras el autor no decida otra cosa (P-09).
- **RF-061**: Toda ficha de persona —miembro o simpatizante— guarda si esa persona autorizó el
  manejo de sus datos, la fecha de la autorización y cómo la dio: firma, de palabra en el culto o
  mensaje. El sistema informa a quien corresponda cuántas fichas de su ámbito están sin
  autorización registrada.
- **RF-062**: Toda pantalla donde se registra o se edita a una persona muestra el aviso de
  privacidad de la congregación: qué datos se guardan, para qué se usan y a quién dirigirse para
  consultarlos o corregirlos.

#### Comités

- **RF-011**: El sistema representa la estructura en **dos niveles**: comité y sub-comité. Un
  comité tiene cero o más sub-comités, y ningún nodo cuelga de un sub-comité.
- **RF-012**: Un nodo sin hijos se identifica expresamente como tal, y la ficha aclara que su
  responsable coordina directamente a sus miembros.
- **RF-013**: Cada comité tiene un único presidente vigente, y hasta un secretario y un tesorero
  vigentes. Cada sub-comité tiene un único líder vigente.
- **RF-014**: Un miembro pertenece a cero, uno o varios comités y sub-comités. Aparece en la lista
  de cada uno, y cuenta una sola vez en los totales de la congregación.
- **RF-063**: Solo el pastor crea, renombra, reordena e inactiva nodos de la estructura, en los dos
  niveles. Ningún otro cargo la modifica.
- **RF-064**: Un nodo inactivado conserva toda su historia consultable —actividades, actas,
  asistencia, presupuesto y decisiones— y deja de aparecer para proponer cosas nuevas. Sus
  integrantes que no pertenecen a otro nodo quedan señalados para reasignar, y sus simpatizantes
  vuelven a BIS.
- **RF-065**: Renombrar un nodo no reescribe la historia: las actas, los reportes y las decisiones
  ya registradas conservan el nombre que el nodo tenía en esa fecha.
- **RF-066**: El sistema no supone una cantidad ni unos nombres de comités. La estructura inicial
  la carga el pastor, y toda pantalla y todo reporte se arman con la estructura vigente.

#### Aprobaciones

- **RF-015**: Son objeto de aprobación: actividad nueva, gasto, tarea y cambio de fecha.
- **RF-016**: Lo propuesto por un líder llega al presidente de su comité.
- **RF-017**: Lo propuesto por un presidente llega directamente al pastor.
- **RF-018**: Solo el pastor aprueba. El presidente revisa lo que proponen sus líderes: si le da
  su visto bueno, la solicitud escala al pastor, sea una actividad nueva, un gasto, una tarea o un
  cambio de fecha; si no está de acuerdo, la devuelve con el motivo (RF-020) y no llega al pastor.
  El gasto del propio comité del presidente también lo aprueba el pastor, cualquiera sea su monto
  (decisión del autor del 2026-10-26, D-24).
- **RF-019**: El paso de un simpatizante a miembro lo proponen BIS o el presidente del comité
  asignado, y lo aprueba el pastor.
- **RF-020**: Todo rechazo exige un motivo escrito, visible para quien propuso.
- **RF-021**: Toda decisión registra quién, qué, cuándo y por qué, y no se borra.
- **RF-022**: El pastor puede resolver varias solicitudes del mismo tipo en una sola acción.
- **RF-023**: Las solicitudes pendientes de un cargo vacante pasan a su reemplazo, con registro
  del traspaso.

#### Cronograma

- **RF-024**: El sistema distingue las actividades del plan anual de las propuestas durante el año.
- **RF-025**: El plan anual se aprueba en un solo acto y queda identificado con quién lo aprobó y
  cuándo.
- **RF-026**: Cada actividad tiene uno de estos estados: esperando aprobación, aprobada, en curso,
  cumplida, no aprobada.
- **RF-027**: El avance de una actividad se calcula a partir de sus tareas cumplidas.
- **RF-028**: Las actividades del cronograma general no se cruzan. Al proponer, cargar al plan o
  aprobar una, el sistema no lo permite si su fecha y su horario se sobreponen con otra del
  cronograma general, y dice cuál es y de qué comité (P-02). Toda actividad tiene hora de inicio y
  de fin. Las actividades de los cronogramas internos sí se cruzan.
- **RF-029**: El cronograma general se consulta como calendario del mes, con el formato de la sede
  (RF-115), o como lista del año, y se filtra por comité, sub-comité, mes y estado.
- **RF-030**: Los totales y el porcentaje de cumplimiento se recalculan según el filtro aplicado.

#### Miembros y reportes

- **RF-031**: El registro de un miembro incluye nombre, fecha de nacimiento, género, estado civil
  —soltero, casado, unión libre, separado, divorciado o viudo—, si está bautizado, fecha de
  bautismo, fecha de ingreso, teléfono, barrio, observaciones y los comités y sub-comités a los que
  pertenece. **No incluye la dirección.** El sistema le asigna a cada miembro un código que no
  cambia. El formulario pide la fecha de nacimiento y, cuando la congregación no la tiene, ofrece «No la sé —
  anote la edad», que guarda los años que la persona tiene y el día en que se anotaron. La edad que
  muestran la ficha, las listas y los reportes se calcula en el momento de la consulta: de la fecha
  de nacimiento cuando existe, y de la edad anotada con su fecha cuando no la hay. Así ningún
  reporte muestra la edad del año pasado. Las dos entradas son opcionales: cuando no se tiene
  ninguna, la ficha queda señalada como pendiente de completar y ese miembro no entra en los cortes
  por edad (RF-036), aunque sí en los demás totales. La ficha creada automáticamente al habilitar
  una cuenta (RF-003) es la excepción a la lista completa: nace con nombre y teléfono y queda
  señalada como pendiente de completar hasta que se diligencie el resto.
- **RF-032**: El sistema impide estados civiles imposibles para menores de dieciocho años. La edad
  se toma de la fecha de nacimiento y, cuando no la hay, de la edad anotada (RF-031). Cuando la
  ficha no tiene ninguno de los dos datos, marcar un estado civil distinto de soltero exige
  confirmar que la persona es mayor de dieciocho años, y esa confirmación queda anotada; el sistema
  nunca exige la fecha de nacimiento para poder guardar la ficha.
- **RF-033**: El sistema exige fecha de bautismo cuando se marca a alguien como bautizado.
- **RF-034**: La búsqueda de miembros ignora mayúsculas y tildes.
- **RF-035**: Los filtros de comité, sub-comité, género, estado civil, bautizado y rango de edad
  son combinables, y el sistema informa cuántos resultados hay sobre el total.
- **RF-036**: Los reportes presentan: total, bautizados, estado civil, género, rangos de edad,
  distribución por comité, bautismos por año y miembros sin comité. Los cortes por edad —rangos y
  promedio— se calculan con las dos fuentes de la edad que admite RF-031: la fecha de nacimiento y
  la edad anotada con su fecha. Quienes no tienen ninguna de las dos quedan fuera de esos cortes y
  el reporte informa cuántos son. Un miembro de varios comités aparece en la distribución de cada
  uno y cuenta una vez en el total.
- **RF-037**: Todo reporte gráfico tiene su equivalente en tabla.
- **RF-038**: Todo reporte se descarga como hoja de cálculo y se imprime, respetando los filtros
  aplicados y el ámbito del cargo.
- **RF-039**: El sistema permite importar miembros desde una plantilla, validando antes de guardar
  e informando fila por fila los errores.

#### Simpatizantes

- **RF-040**: BIS registra en la entrada a quien llega por primera vez y le asigna un solo comité,
  que lo acompaña. Cualquier comité registra también a los amigos que invita, que quedan asignados
  a él. La ficha de seguimiento la consultan y la actualizan BIS, que ve a todos los simpatizantes
  de la sede, y el comité asignado, que ve solo los suyos; el pastor también la ve. Ningún otro
  comité la abre (constitución, principio I, excepción 2).
- **RF-041**: El registro incluye nombre, fecha de nacimiento, género, teléfono, barrio, cómo
  llegó, quién lo invitó y la fecha del primer contacto. La fecha de nacimiento es opcional e,
  igual que en la ficha de miembro (RF-031), admite que se anote la edad con su fecha cuando no se
  sabe el día de nacimiento; de cualquiera de las dos el sistema calcula la edad.
- **RF-042**: El camino consta de cinco etapas en este orden: visitó el culto; asiste con
  frecuencia; recibe estudio bíblico; se bautizó en el nombre de Jesucristo; recibió el Espíritu
  Santo. Cada etapa alcanzada conserva su fecha.
- **RF-043**: El sistema calcula el nivel de compromiso a partir de la asistencia reciente a las
  actividades y cultos (RF-091) y los días transcurridos desde el último contacto. Nadie lo escribe
  a mano.
- **RF-044**: Cada visita, llamada o estudio se registra con fecha, tipo, responsable y una nota.
- **RF-045**: Al aprobarse el paso a miembro, la persona entra a la lista de miembros conservando
  su fecha de bautismo y su fecha de primer contacto.

#### Asistencia

- **RF-046**: La asistencia se toma por actividad, interna o del cronograma general. Registra los
  integrantes convocados, cuáles asistieron y cuáles no —una inasistencia puede llevar su excusa—,
  y los simpatizantes que vinieron.
- **RF-047**: Los convocados son integrantes del comité o sub-comité dueño de la actividad.
- **RF-048**: El porcentaje de asistencia por persona se calcula sobre las reuniones a las que fue
  convocada.
- **RF-049**: El sistema señala a quienes están por debajo del sesenta por ciento con un texto de
  acompañamiento, nunca de reproche.

#### Presupuesto

- **RF-050**: El pastor asigna el presupuesto anual de cada comité; el presidente lo reparte entre
  sus sub-comités.
- **RF-051**: El sistema advierte cuando un gasto supera el saldo disponible, y permite continuar
  si se confirma.
- **RF-052**: Cada movimiento registra fecha, concepto, monto, soporte y quién lo autorizó.
- **RF-053**: Toda pantalla de presupuesto muestra la aclaración de que la tesorería sigue
  manejando los recursos.

#### Pendientes y correcciones

- **RF-054**: La pantalla de inicio de cada cargo muestra primero lo que espera su decisión y
  desde cuándo.
- **RF-055**: El menú indica con un contador cuántas solicitudes están pendientes para ese cargo.
- **RF-056**: Los registros corregibles conservan su valor anterior, quién lo cambió y cuándo.
- **RF-057**: Las aprobaciones ya resueltas no se editan: se registra una nueva decisión.

#### Actas de reunión

- **RF-067**: Toda acta pertenece a una reunión —prevista en un cronograma o imprevista— o a una
  actividad programada, y hereda su fecha, su lugar y sus asistentes. No existen actas sueltas.
- **RF-068**: El acta tiene campos fijos: orden del día, desarrollo y compromisos, además de lo que
  hereda de la reunión. No es texto libre.
- **RF-069**: El secretario del comité redacta el acta y la guarda como borrador cuantas veces
  quiera. El presidente la cierra.
- **RF-070**: Al cerrarse, el sistema le asigna un número correlativo por comité y por año, y
  registra quién la redactó, quién la cerró y cuándo.
- **RF-071**: Una acta cerrada **no se edita ni se borra**. Una corrección se hace levantando un
  acta nueva que haga constar la corrección.
- **RF-072**: Toda acta se imprime en blanco y negro, con el nombre de la iglesia, el comité, el
  número, la fecha, los asistentes, el desarrollo, los compromisos y el espacio de las firmas.
- **RF-073**: Cada compromiso del acta es texto, y se puede convertir en tarea con responsable y
  fecha. Convertido, aparece en los pendientes de esa persona y cuenta para el avance de su
  actividad.
- **RF-074**: Las actas siguen el filtro de alcance: las ve el comité dueño y sus ascendientes, y el
  pastor. No viajan por la excepción del cronograma compartido.

#### Archivos adjuntos

- **RF-075**: Una actividad admite **un** soporte opcional que respalde su cumplimiento —una
  imagen, una foto o un documento—, y un movimiento de presupuesto admite su soporte —factura o
  recibo, en PDF o imagen—. Reemplazar el soporte de una actividad conserva el anterior (RF-079).
- **RF-076**: Cada archivo registra quién lo subió y cuándo, y pesa 10 MB como máximo. El sistema
  avisa en una frase cuando se pasa del límite.
- **RF-077**: El sistema reduce las imágenes al recibirlas, sin que la persona tenga que hacer nada,
  para que subir una foto desde el celular no consuma sus datos.
- **RF-078**: Los archivos se guardan en el servidor del contrato. No se copian a ningún servicio
  externo, y el respaldo diario los incluye junto con la base de datos.
- **RF-079**: Un archivo adjunto a algo ya cumplido o ya autorizado **no se borra**: se marca como
  reemplazado, se conserva en la historia, y se puede subir el correcto.
- **RF-080**: Los archivos siguen el filtro de alcance: los ve el comité dueño y sus ascendientes, y
  el pastor. **No** viajan por la excepción del cronograma compartido ni aparecen en ninguna
  descarga de reporte fuera del ámbito.

#### Identidad de la sede y de los comités

- **RF-081**: El sistema se llama «IPUC La Esmeralda - Ibagué» y muestra ese nombre en la pantalla
  de entrada y en la barra de toda la plataforma. El nombre es un dato de la sede, no un texto
  escrito en las pantallas.
- **RF-082**: Cada año la sede tiene su lema, con su cita bíblica, que el pastor registra junto con
  el cronograma general de ese año. La entrada y la plataforma muestran el lema del año en curso;
  los de años anteriores se conservan. Si el año no tiene lema, se muestra solo el nombre.
- **RF-083**: Cada comité tiene su logo, su lema y sus objetivos, que registra su presidente. Se
  muestran en la ficha del comité y en lo que el comité imprime; fuera de su ámbito solo viaja su
  nombre.

#### Padrón y pertenencia

- **RF-084**: La lista de un comité distingue a su directiva —presidente, secretario, tesorero y
  vocales— de los demás integrantes. Los vocales no tienen cuenta ni permisos por serlo (P-08).
- **RF-085**: Solo el pastor registra y corrige las fichas de los miembros. Cada comité agrega a su
  lista a personas ya registradas, o las quita, sin modificar sus datos. Para encontrarlas, el
  buscador trae de cada miembro de la sede únicamente su nombre y su edad (constitución, principio
  I, excepción 4).

#### Cronogramas internos

- **RF-086**: Cada comité y cada sub-comité llevan uno o varios cronogramas internos, con nombre y
  periodo —por ejemplo, uno por semestre, por módulo o por grupo de clase—. Los ven únicamente el
  comité dueño, sus ascendientes y el pastor.
- **RF-087**: Las actividades de un cronograma interno no pasan por aprobación (P-03), salvo sus
  gastos, que siguen RF-015. Se pueden cruzar entre sí y con las del cronograma general.
- **RF-088**: Una actividad interna se puede repetir —cada semana, cada quince días o cada mes—
  entre dos fechas. Cada repetición queda como una actividad propia, que se puede mover o cancelar
  sola.
- **RF-089**: Cada comité imprime las actividades suyas y de sus sub-comités —del cronograma
  general y de sus cronogramas internos— por periodo, y las comparte por WhatsApp desde un botón
  que abre la aplicación del celular con el resumen escrito. El sistema nunca envía nada por su
  cuenta (S-01).
- **RF-090**: Toda actividad, interna o general, admite asistencia (RF-046), soporte (RF-075) y
  acta (RF-067).

#### Asistencia de los simpatizantes

- **RF-091**: La asistencia de un simpatizante a una actividad o a un culto queda en su historia y
  cuenta para su nivel de compromiso (RF-043).

#### Orden de culto

- **RF-092**: Cada culto de un comité en el cronograma general tiene su orden de culto: las partes
  —apertura, himno, oración y peticiones, ofrenda y predicación, más las que el comité agregue— y
  quién hace cada una, escogido entre los integrantes del comité o escrito a mano.
- **RF-093**: El orden de culto se completa a más tardar ocho días antes del culto. Desde quince días
  antes, la directiva del comité lo ve como pendiente en su pantalla de inicio; si a ocho días no
  está completo, el sistema lo señala como atrasado (P-04).
- **RF-094**: El orden de culto se imprime y se comparte como RF-089, y es parte del detalle
  reservado de la actividad (RF-010).

#### Cumpleaños

- **RF-095**: Al entrar, el sistema muestra en una alerta clara y amigable quiénes del ámbito del
  cargo activo cumplen años hoy y en los próximos siete días: miembros, simpatizantes y, en la
  Escuela Dominical, niños. Ofrece además la lista por mes, con fecha y código. Quien no tiene
  fecha de nacimiento no aparece, y el sistema dice cuántos son.

#### Simpatizantes con BIS

- **RF-096**: Al registrar a un simpatizante, el sistema sugiere mientras se escribe el nombre las
  personas ya registradas que coinciden: a BIS, entre todos los simpatizantes; a un comité, entre
  los suyos. Cuando la coincidencia está fuera de su ámbito, solo avisa que ya existe, sin
  mostrarla, y deja continuar o cancelar (constitución, principio I, excepción 3).
- **RF-097**: Cada simpatizante está asignado a un solo comité a la vez. BIS lo puede reasignar; la
  historia conserva por qué comités pasó y quién lo cambió.

#### Obra Social

- **RF-098**: Obra Social lleva su propia lista de beneficiarios —hermanos de la congregación o
  amigos—, con nombre, teléfono, barrio y su condición: madre cabeza de familia, adulto mayor u
  otra. Esa lista no lee el padrón de miembros.
- **RF-099**: Cada ayuda entregada registra la fecha, el beneficiario, el tipo —mercado, dinero,
  elementos, visita con aporte u otra—, el valor o la descripción de lo entregado, la justificación
  y una foto opcional.
- **RF-100**: El informe de Obra Social muestra mes a mes las ayudas entregadas, por tipo y por
  valor, con gráfico y su tabla equivalente (RF-037), y la lista mensual de quienes recibieron
  mercado y de quienes no.
- **RF-101**: Obra Social registra los aportes que recibe —de quién, cuándo y qué: dinero o
  elementos—, incluidos los aportes mensuales de sus integrantes, en un fondo propio,
  independiente del presupuesto general del año (RF-050). Las ayudas en dinero, los talleres y las
  visitas salen de ese fondo, y el sistema muestra lo recibido, lo entregado y el saldo, con la
  aclaración de RF-053.
- **RF-102**: Un taller de Obra Social es una actividad de su cronograma interno: registra la fecha,
  el taller, los asistentes, el aporte a quien lo dicta y la inversión, que sale del fondo.

#### Alabanza

- **RF-103**: Para cada actividad de Alabanza —culto, ensayo o presentación— el comité registra
  quién toca el piano, quién la batería, las voces principales y las voces de apoyo, escogidos
  entre sus integrantes. La lista de funciones se puede ampliar. Dar dos funciones a la misma
  persona en la misma actividad se advierte, sin impedirlo.
- **RF-104**: La asistencia a las prácticas sigue RF-046 sobre el cronograma interno de Alabanza, y
  el porcentaje por persona sigue RF-048.

#### Escuela Dominical

- **RF-105**: La Escuela Dominical organiza a los niños en grupos por edad. Al empezar son cinco:
  0 a 2 años, 3 a 5 «Semillitas de Amor», 6 a 8 «Huellitas de Fe», 9 a 11 «Corazones Valientes» y
  12 a 17 «Generación Elegida». El comité los crea, los renombra, les cambia el rango o los
  desactiva, y el sistema advierte rangos que se pisan o edades sin grupo. Cada grupo tiene su
  maestro y su auxiliar.
- **RF-106**: La lista de la Escuela Dominical distingue la directiva, los maestros y los auxiliares.
- **RF-107**: La ficha del niño sigue el formulario de inscripción de la Escuela Dominical:
  identidad, familia, estudios, salud e iglesia, con foto opcional. Los datos de salud solo los
  ven la Escuela Dominical y el pastor. El sistema advierte si el niño ya está inscrito.
- **RF-108**: El sistema ubica a cada niño en su grupo según su fecha de nacimiento, y lo pasa al
  grupo siguiente el día que cumple la edad, sin que nadie lo mueva. Al cumplir dieciocho años sale
  de los grupos y queda señalado.
- **RF-109**: El padre, la madre o el acudiente firma con el dedo en la pantalla las dos
  autorizaciones de la Escuela Dominical: asistencia y participación, y uso de imagen, con lo que
  comprende —fotos, audios, videos, otros datos—. El documento firmado queda guardado con la fecha,
  no se modifica —un cambio se hace firmando uno nuevo— y se imprime igual al formato oficial.
  Cuando no se puede firmar en la pantalla, se sube la foto del papel firmado.
- **RF-110**: Mientras un niño no tenga firmada la autorización de imagen, el sistema no deja subir
  fotos suyas.
- **RF-111**: Cada grupo tiene su cronograma de clases por año: fecha del cuaderno, fecha de la
  clase, tema, maestro, auxiliar y aseo, y los días sin clase con su motivo. Se filtra por grupo,
  mes y maestro.
- **RF-112**: La asistencia a las clases se toma por grupo y por niño, y se consulta por mes. Los
  días sin clase no cuentan.
- **RF-113**: Las visitas de la Escuela Dominical registran la fecha y el grupo, y, según a quién se
  visita:
  - al maestro visitado, con la asistencia, la necesidad encontrada y la novedad;
  - a los niños visitados, con los maestros que fueron.

  Las dos llevan una foto opcional.
- **RF-114**: El sistema presenta las estadísticas de los niños —cuántos hay por grupo, por género
  y por edad, y cuántos no tienen sus autorizaciones firmadas— con su tabla equivalente.

#### El formato del cronograma de la sede

- **RF-115**: El calendario del mes reproduce el formato del cronograma anual de la sede: el nombre
  del mes en azul oscuro y el año en dorado; una banda azul oscuro con los días, de lunes a domingo;
  columnas alternas en blanco y azul muy claro, el domingo en crema y los días de otro mes en gris;
  el número del día abajo a la derecha. En el celular cada casilla lleva el número y una marca por
  actividad, y el día escogido muestra sus actividades debajo; en una pantalla grande las
  actividades se escriben dentro de la casilla, como en el papel.
- **RF-116**: Cada actividad puede llevar, si se quiere, un **tema** —el lema del culto, que sale en
  azul debajo de su nombre— y una **marca**: día especial de la congregación (en rojo), actividad
  del distrito o actividad nacional (en naranja). En la pantalla la marca además se dice con
  palabras, y los colores se oscurecen lo justo para leerse bien (RF-058).
- **RF-117**: El cronograma se imprime con ese formato: el año completo, una hoja horizontal por
  mes, o un solo mes. Cada quien imprime lo que ve: el pastor, el cronograma completo de la sede.
- **RF-118**: El pastor anota, si quiere, una nota general para un mes —una campaña de todo el mes
  o un evento con la fecha pendiente—, que sale junto al nombre del mes, en rojo si es de la sede y
  en naranja si es del distrito o nacional.

#### Celular y lenguaje

- **RF-058**: Toda pantalla se usa completa en un ancho de 360 px, sin desplazamiento horizontal,
  con zonas pulsables de **48x48 px** como mínimo, texto de contenido de **18 px** como mínimo y
  contraste entre texto y fondo que cumple el nivel **AA** de las pautas de accesibilidad
  (constitución 2.1.0).
- **RF-059**: El sistema se instala en la pantalla de inicio del teléfono y abre su pantalla
  principal en menos de 3 segundos sobre una conexión móvil lenta. Las tablas de muchas columnas
  se presentan como tarjetas en pantallas angostas.
- **RF-060**: Todo texto que ve la persona está en español de Colombia, tratando de «usted», con el
  vocabulario de la IPUC y sin jerga técnica. Cada mensaje de error explica en una frase qué pasó y
  qué hacer.

### Reglas de Negocio

- **RN-01**: El pastor ve toda la congregación porque responde por ella.
- **RN-02**: Un presidente responde por lo que hacen sus sub-comités.
- **RN-03**: Lo que venía en el plan anual no requiere nueva aprobación; lo que se sale de él, sí.
- **RN-04**: El monto de un gasto no determina quién lo aprueba: todo gasto lo aprueba el pastor,
  cualquiera sea su valor, después de la revisión del presidente cuando lo pide un líder.
- **RN-05**: El presupuesto es una guía, no un tope rígido: se puede exceder, pero el sistema lo
  advierte y queda registrado.
- **RN-06**: Nadie pasa a ser miembro sin aprobación del pastor.
- **RN-07**: Ninguna aprobación, rechazo o movimiento registrado se elimina.
- **RN-08**: El lenguaje de todo el sistema es el de la congregación, no el de la informática.
- **RN-09**: Quien deja un cargo pierde el acceso el mismo día, y su nombre permanece en la
  historia de lo que decidió.
- **RN-10**: Las actividades del cronograma general no se cruzan; las de los cronogramas internos
  sí.
- **RN-11**: El padrón de miembros lo lleva el pastor; cada comité escoge de él a los suyos.
- **RN-12**: Un simpatizante está a cargo de un solo comité a la vez, y BIS los recibe a todos.
- **RN-13**: Ninguna foto de un niño sin la autorización de imagen firmada.
- **RN-14**: El fondo de Obra Social es independiente del presupuesto general del año.

### Fuera del Alcance

Lo siguiente no se construye. Si la congregación lo pide, se registra como propuesta para cotizar.

- Avisos por WhatsApp, correo electrónico o mensaje de texto. Compartir por WhatsApp desde un botón
  sí está en el alcance (HU-30): lo hace la persona, no el sistema.
- Acceso para los miembros que no tienen cargo, para los padres de los niños o para los
  beneficiarios de Obra Social.
- Firma digital certificada por una entidad de certificación. La firma en la pantalla deja
  constancia de la autorización de los padres (HU-36), y la congregación decide si le basta
  (DEP-12).
- Manejo de dinero: el sistema registra en qué se gastó y quién lo autorizó; la tesorería de la
  iglesia sigue manejando los recursos.
- Integración con cualquier otro sistema.
- Más de una sede en operación. El sistema queda preparado para varias, pero la licencia cubre una.

### Entidades Principales

- **Sede**: la congregación que opera el sistema, con su nombre —«IPUC La Esmeralda - Ibagué»—. Toda
  la información de comités, personas, actividades, asistencia y presupuesto pertenece a una sede.
  Hoy existe una sola.
- **Lema del año**: el lema de la sede para un año, con su cita bíblica y quién lo registró.
- **Comité**: unidad de trabajo de la congregación, con nombre, su logo, su lema, sus objetivos,
  presidente vigente, su secretario y su tesorero, su marca de activo o inactivo, el orden en que
  se muestra, y cero o más sub-comités. En la sede La Esmeralda son quince; uno de ellos es BIS,
  que recibe a los simpatizantes.
- **Sub-comité**: unidad dependiente de un comité, con su propio líder. Es el segundo y último
  nivel: un sub-comité no tiene hijos.
- **Pertenencia**: el vínculo de un miembro con un comité o sub-comité, con su función en la lista
  —directiva (presidente, secretario, tesorero, vocal), maestro, auxiliar o integrante— y desde
  cuándo. Un miembro tiene cero o más.
- **Cronograma**: un conjunto de actividades. El general es uno por año y se comparte; los internos
  son de un comité o sub-comité, con su nombre y su periodo, y no se comparten.
- **Asistencia**: la marca de una persona —integrante o simpatizante— en una actividad: asistió, no
  asistió, o no asistió con excusa.
- **Orden de culto**: las partes de un culto del cronograma general y quién hace cada una.
- **Función de Alabanza**: piano, batería, voz principal, voz de apoyo u otra que se agregue, y la
  persona que la tiene en una actividad.
- **Beneficiario de Obra Social**: persona a la que Obra Social ayuda, hermano o amigo, con su
  condición.
- **Ayuda entregada**: lo que Obra Social le dio a un beneficiario, con fecha, tipo, valor,
  justificación y foto opcional.
- **Aporte al fondo de Obra Social**: lo que alguien le dio al comité, dinero o elementos, con su
  fecha.
- **Grupo de clase**: grupo de niños de la Escuela Dominical, con su rango de edad, su nombre, su
  maestro y su auxiliar.
- **Niño**: niño inscrito en la Escuela Dominical, con los datos de su formulario de inscripción.
  Su grupo se calcula de su fecha de nacimiento.
- **Autorización firmada**: cada documento que firma el acudiente de un niño —asistencia y
  participación, o uso de imagen con lo que comprende—, con la firma, la fecha y quién la recogió.
  No se modifica.
- **Clase programada**: una fecha del cronograma de clases de un grupo, con el tema, el maestro, el
  auxiliar y el aseo, o el motivo de que no haya clase.
- **Visita de la Escuela Dominical**: la visita a un maestro o a unos niños, con su fecha, lo que se
  encontró y una foto opcional.
- **Acta de reunión**: el registro de lo tratado en una reunión, con su número del año, el orden del
  día, el desarrollo, los compromisos, quién la redactó, quién la cerró y cuándo. Cerrada, no se
  edita ni se borra.
- **Compromiso de acta**: cada acuerdo que sale de una reunión, con su texto y, cuando se convierte,
  su responsable y su fecha como tarea.
- **Archivo adjunto**: foto o documento que respalda el cumplimiento de una actividad, o soporte de
  un movimiento de presupuesto, con quién lo subió, cuándo, su peso y su marca de reemplazado.
- **Cargo**: el papel que una persona ejerce —pastor, presidente de comité, secretario, tesorero, o
  líder de sub-comité— sobre un nodo determinado de la estructura, con fecha de inicio y, cuando
  aplica, de retiro. Determina el ámbito de información de su titular y lo que puede hacer dentro
  de él.
- **Cuenta de acceso**: el usuario y la clave de una persona con cargo, ligada a la única ficha de
  miembro de esa persona, con la marca de si debe cambiar la clave, los intentos fallidos recientes
  y si está vigente o retirada.
- **Miembro**: hermano o hermana de la congregación, con su código, nombre, fecha de nacimiento —o
  la edad anotada con el día en que se anotó, cuando la fecha no se sabe—, de la que el sistema
  calcula la edad, género, estado civil, condición de bautizado y su fecha, fecha de ingreso,
  teléfono, barrio, observaciones, sus pertenencias a comités y sub-comités, la marca de ficha
  pendiente de completar —cuando nació al habilitar una cuenta o cuando no tiene ni fecha de
  nacimiento ni edad anotada—, y la autorización para manejar sus datos con su fecha y su medio. No
  tiene dirección. Es la única ficha de la persona, tenga cargo o no, y la lleva el pastor.
- **Simpatizante**: persona que está visitando la iglesia, con quién la invitó, cómo llegó, la
  fecha del primer contacto, el comité que la acompaña, la etapa alcanzada de su camino, su nivel
  de compromiso calculado y la autorización para manejar sus datos con su fecha y su medio.
- **Etapa del camino**: cada uno de los cinco momentos del acompañamiento de un simpatizante, con
  la fecha en que se alcanzó.
- **Contacto de acompañamiento**: visita, llamada o estudio bíblico hecho a un simpatizante, con
  fecha, tipo, responsable y nota.
- **Plan anual**: el conjunto de actividades de un año, con la constancia de quién lo aprobó y
  cuándo, y la distinción entre lo que venía en el plan y lo que se propuso después.
- **Actividad**: lo que un comité o sub-comité va a hacer, en el cronograma general o en uno
  interno, con título, tema y marca de color opcionales, descripción, fecha, hora de inicio y de
  fin, lugar, comité
  responsable, presupuesto estimado, estado, avance calculado, su soporte opcional y, si se repite,
  la serie a la que pertenece.
- **Tarea**: unidad de trabajo de una actividad, con responsable y condición de cumplida. De ella
  se calcula el avance de la actividad.
- **Solicitud de aprobación**: petición que espera decisión —actividad nueva, gasto, tarea, cambio
  de fecha o paso de simpatizante a miembro—, con quién la propuso, por quién va pasando, desde
  cuándo espera y su estado.
- **Decisión**: la aprobación o el rechazo de una solicitud, con quién decidió, cuándo y el motivo
  cuando es rechazo. No se edita ni se borra.
- **Reunión**: ensayo o encuentro de un comité o sub-comité, con fecha, tipo, tema, convocados y
  asistentes.
- **Presupuesto de comité**: el monto anual asignado a un comité y su reparto entre sub-comités,
  con lo gastado y lo disponible.
- **Movimiento de presupuesto**: cada gasto registrado, con fecha, concepto, monto, soporte y quién
  lo autorizó.
- **Asiento de historia**: la constancia de cada cambio en un registro corregible: qué decía antes,
  quién lo cambió y cuándo. No se edita ni se borra.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

Medibles a los seis meses de la puesta en funcionamiento, salvo los que indiquen otro plazo.

**Uso y oportunidad**

- **CE-01**: Todas las personas con cargo entran al sistema al menos una vez por semana.
- **CE-02**: El plan del año siguiente queda cargado y aprobado por el pastor antes del 31 de
  enero.
- **CE-03**: El noventa por ciento de las solicitudes se resuelven en menos de siete días
  calendario desde que se envían.
- **CE-04**: Cero actividades cruzadas en el cronograma general del año, entendiendo por cruce dos
  actividades el mismo día con horarios que se sobreponen (P-02). El sistema las impide.

**Información completa y al día**

- **CE-05**: Los ciento cuarenta miembros quedan cargados durante el primer mes.
- **CE-06**: Ningún miembro queda sin comité asignado sin que el pastor lo tenga identificado.
- **CE-07**: El reporte de la congregación se obtiene en menos de un minuto desde que se solicita,
  en cualquier momento y sin ayuda técnica.
- **CE-08**: Se registra asistencia en al menos el ochenta por ciento de las reuniones y ensayos de
  comité.

**Confianza y uso desde el celular**

- **CE-09**: Cero incidentes en que una persona haya visto información de un comité que no le
  corresponde.
- **CE-10**: Todas las personas con cargo completan sus tareas habituales desde el celular;
  ninguna pantalla exige desplazamiento horizontal ni computador.
- **CE-11**: Una persona mayor de la congregación, sin que nadie se la explique, entiende qué le
  pide cada pantalla que usa habitualmente y qué hacer cuando aparece un mensaje de error.
- **CE-12**: El cien por ciento de las fichas de miembros y simpatizantes tiene registrada su
  autorización para el manejo de datos, con fecha y medio, al cumplirse los seis meses.
- **CE-13**: Toda reunión de comité del semestre tiene su acta cerrada en el sistema, y ningún
  comité sigue llevando su libro en papel.
- **CE-14**: El ochenta por ciento de las actividades cumplidas tiene al menos un archivo que
  respalda su cumplimiento.
- **CE-15**: El cien por ciento de los movimientos de presupuesto tiene su soporte adjunto, de modo
  que el pastor puede mostrar en qué se usó cada peso sin pedirle nada a nadie.
- **CE-16**: El pastor administra la estructura de comités sin ayuda técnica: crea, renombra e
  inactiva un nodo en menos de dos minutos desde el celular.

**Lo que trajo el levantamiento de La Esmeralda**

- **CE-17**: Al cumplirse el primer mes, cada comité lleva al menos un cronograma interno en el
  sistema.
- **CE-18**: Todo culto del cronograma general tiene su orden completo ocho días antes.
- **CE-19**: Todo simpatizante registrado tiene un comité asignado el mismo día.
- **CE-20**: Al cumplirse los tres meses, el cien por ciento de los niños de la Escuela Dominical
  tiene firmadas sus dos autorizaciones.
- **CE-21**: Obra Social obtiene el informe mensual de sus ayudas en menos de un minuto, sin contar
  a mano.

## Supuestos

**Pendientes de confirmar con el cliente** (vienen así del documento de entrada; se resolvieron con
el valor más defendible, pero deben validarse antes de planear):

- **S-01**: Las personas se enteran de lo que tienen pendiente únicamente al entrar al sistema: un
  contador visible en el menú y un panel en la pantalla de inicio. No hay avisos externos.
- **S-02**: Los 140 miembros se cargan la primera vez mediante importación de una plantilla de
  hoja de cálculo que el sistema valida antes de guardar.

**Supuestos tomados al redactar esta especificación**:

- **S-03**: El nivel de compromiso de un simpatizante (RF-043) se clasifica como «creciendo»,
  «estable» o «alejándose» a partir de su asistencia a los cultos recientes y de los días desde su
  última visita. Los umbrales exactos se definen en el plan y quedan en un solo lugar para poder
  ajustarlos sin rehacer pantallas.
- **S-04**: «El año en curso» de HU-05 y CL-07 significa el año calendario, con zona horaria
  America/Bogota. Al cambiar de año, el plan anterior queda consultable y el nuevo empieza sin
  aprobar (CL-13).
- **S-05**: Los montos se expresan en pesos colombianos y las fechas en formato colombiano.
- **S-06**: Ante pérdida de conexión (CL-15), el sistema conserva en el dispositivo lo que la
  persona escribió en el formulario abierto y se lo devuelve al reconectar. No se busca un modo
  de trabajo sin conexión.
- **S-07**: La persona que registra un simpatizante conserva la constancia de haberlo invitado,
  aunque no vea su ficha de seguimiento (HU-13 escenario 2).
- **S-08**: El sistema no envía ni recibe nada hacia afuera: no hay correo saliente, por lo que el
  restablecimiento de clave es siempre un acto del pastor (RF-005).
- **S-09**: Las semillas y los datos de prueba son ficticios. Los datos reales de los 140 miembros
  se cargan en la puesta en funcionamiento, nunca en el repositorio.
- **S-10**: La información histórica no se purga: las decisiones, los movimientos de presupuesto y
  los asientos de historia se conservan mientras el sistema esté en uso.
- **S-11**: Los quince comités del levantamiento son la estructura inicial de la sede. Las
  semillas de prueba usan sus nombres, que no son datos de personas, con personas ficticias.
- **S-12**: El código de cada miembro lo asigna el sistema en orden de registro y no cambia.
- **S-13**: Compartir por WhatsApp abre la aplicación del celular con el texto listo. El sistema no
  se conecta con WhatsApp ni envía nada por su cuenta.
- **S-14**: La firma en la pantalla se guarda como imagen dentro del documento firmado, con la
  fecha y quién la recogió, y el documento no se modifica después.

**Pendientes de confirmar con la congregación** (vienen del levantamiento del 2026-10-28; mientras
no se confirmen rige lo que dice cada uno):

- **P-01**: La dirección de las simpatizantes y de los niños. El levantamiento omite la de los
  miembros. Damas Dorcas pide la de sus simpatizantes para visitarlas, y el formulario oficial de
  la Escuela Dominical trae la del niño. Mientras no se confirme, no se guarda: solo el barrio.
- **P-02**: Qué es un cruce en el cronograma general. Se toma como dos actividades el mismo día con
  horarios que se sobreponen, sin importar el lugar.
- **P-03**: Las actividades de los cronogramas internos no pasan por aprobación; sus gastos sí.
- **P-04**: El orden de culto se completa a más tardar ocho días antes, y desde quince días antes
  se recuerda.
- **P-05**: La junta local, Rayos de Luz, Artística y Edad Dorada aparecen en el portafolio pero no
  en la lista de comités. Mientras no se confirme qué son, no se crean.
- **P-06**: Los maestros y los auxiliares de la Escuela Dominical no entran al sistema por serlo: la
  asistencia de los grupos la toma la directiva del comité.
- **P-07**: El sistema no registra el dinero de las ofrendas de los cultos —por ejemplo, las
  misioneras de Conquistadores—; la ofrenda es solo una parte del orden de culto.
- **P-08**: El vocal de una directiva es una función en la lista del comité, sin cuenta ni
  permisos propios.
- **P-09**: Si los demás comités ven el tema y la marca de color de una actividad ajena. En el papel
  de la sede todos los ven, pero la constitución 3.0.0 enumera seis datos compartidos. Mientras el
  autor no decida, no viajan: cada comité ve en color lo suyo, y el pastor —que ve todo— imprime el
  cronograma completo con sus colores.

**Dependencias del cliente**:

Son las entregas que el proyecto espera de la congregación. Se identifican con el prefijo
`DEP-xx` para que no se confundan con las decisiones de arquitectura `D-xx` del plan de
implementación, que son otra familia de identificadores.

- **DEP-01**: El pastor entrega la lista de los 140 miembros en la plantilla de hoja de cálculo
  antes del primer mes de operación (CE-05).
- **DEP-02**: Reemplazada por DEP-05 el 2026-10-24. La estructura dejó de ser un dato fijo que el
  pastor entrega una vez: ahora la administra él mismo desde el sistema (HU-21).
- **DEP-03**: El pastor confirma los supuestos S-01 y S-02 antes de `/speckit-plan`. Los tres
  puntos que estaban marcados como pendientes de aclaración quedaron resueltos en la sesión del
  2026-10-18; ver `## Clarifications`.
- **DEP-04**: La congregación entrega el texto del aviso de privacidad (RF-062) y define con qué
  medio recoge las autorizaciones de los miembros y simpatizantes ya registrados (RF-061, CE-12).
  La decisión jurídica sobre el tratamiento de datos es del cliente; el sistema solo deja la
  constancia.
- **DEP-05**: El pastor entrega los **responsables** de la estructura inicial —presidente,
  secretario y tesorero de cada uno de los quince comités, y el líder de cada uno de los seis
  sub-comités— antes de habilitar las cuentas. Los nombres de los comités ya los trajo el
  levantamiento del 2026-10-28; las personas solo las tiene la congregación (RF-066, HU-21).
- **DEP-06**: Si el distrito exige un **formato oficial de acta**, el pastor entrega una copia de un
  acta real antes de que se construya la vista de impresión. No encontré ninguno publicado. Mientras
  no llegue, rige el formato de RF-072: nombre de la iglesia, comité, número, fecha, asistentes,
  desarrollo, compromisos y firmas.
- **DEP-07**: El respaldo diario debe **ampliarse para incluir los archivos** del disco, no solo la
  base de datos (RF-078). Hoy solo cubre la base.

- **DEP-08**: El pastor consigue la **versión a color del logo de la IPUC** y confirma que la
  iglesia nacional autoriza usarlo en el sistema. En su sitio la iglesia solo publica la versión
  blanca, que sirve sobre fondo azul pero no para imprimir actas sobre papel blanco.
- **DEP-09**: La Escuela Dominical entrega el **texto vigente de sus formularios oficiales**
  —inscripción del niño, consentimiento de asistencia y participación, y autorización de uso de
  imagen— y confirma que la congregación puede usarlos en el sistema (HU-35, HU-36).
- **DEP-10**: La Escuela Dominical define el **nombre del grupo de 0 a 2 años**, que el
  levantamiento deja por definir.
- **DEP-11**: El pastor entrega el **lema del año** con su cita, y cada presidente el logo, el lema
  y los objetivos de su comité (HU-25, HU-26).
- **DEP-12**: La congregación confirma si la **firma dibujada en la pantalla** le basta como
  constancia de las autorizaciones de los padres. No es una firma digital certificada (Fuera del
  Alcance).
