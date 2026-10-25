# Especificación de Funcionalidad: Sistema de Comités IPUC

**Rama de funcionalidad**: `001-sistema-comites-ipuc`
(Nota: todavía no existe como rama de git. No hay hook `before_specify` configurado, así que la
rama debe crearse manualmente antes de implementar, conforme a la Gobernanza de la constitución.)

**Creada**: 2026-10-18

**Estado**: Borrador — alcance ampliado el 2026-10-24 (constitución 2.1.0). Sin puntos por aclarar;
quedan tres dependencias del cliente: DEP-05 (estructura inicial), DEP-06 (formato de acta del
distrito) y DEP-07 (ampliar el respaldo a los archivos).

**Entrada**: Descripción del usuario: `@spec-input.md` — documento «Especificación — Sistema de
Comités IPUC» entregado por el autor del proyecto. Se conserva en la raíz del repositorio como
`spec-input.md`.

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
  el presupuesto, no en su bandeja.
- Q: ¿La ficha de seguimiento de un simpatizante la ven todos los cargos del Comité de
  Evangelismo, o únicamente su presidente? (RF-040) → A: Únicamente el presidente del Comité de
  Evangelismo, además del pastor, que ve toda la congregación.
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
  equipo.
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

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Usuarios y alcance de cada cargo

Solo las personas con un cargo entran al sistema. Los demás miembros están registrados, pero no
tienen acceso.

| Usuario | Cuántos | Qué hace |
|---|---|---|
| **Pastor** | 1 | Ve toda la congregación. Administra la estructura de comités: crea, renombra e inactiva en los tres niveles. Crea y quita cuentas. Aprueba el plan anual, las actividades nuevas y el paso de simpatizantes a miembros. Asigna el presupuesto de cada comité. |
| **Presidente de comité** | uno por comité | Ve su comité y todos sus descendientes. Revisa y resuelve lo que le proponen. Reparte el presupuesto entre ellos. Cierra las actas de su comité. |
| **Secretario** | uno por comité | Mismo ámbito que su presidente. Redacta las actas de reunión y las guarda como borrador hasta que el presidente las cierra. |
| **Tesorero** | uno por comité | Mismo ámbito que su presidente. Registra los movimientos de presupuesto y sube sus soportes. |
| **Líder de sub-comité o de equipo** | uno por nodo | Ve únicamente su nodo y sus descendientes. Propone actividades y gastos, registra avance, sube evidencias, toma asistencia. |

La cantidad de personas con cargo **no es un número fijo**: depende de la estructura que configure
el pastor. La congregación arranca con la estructura que él cargue, y el sistema no supone ni
seis comités ni diez sub-comités (ver HU-21).

**Caso particular**: el presidente del Comité de Evangelismo es, además, el único que hace
seguimiento a los simpatizantes.

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

**Prueba independiente**: se puede probar sola cargando una estructura de los tres niveles y
abriendo la sección con cada cargo; entrega valor porque la congregación ve por primera vez su
organización completa en un solo lugar.

**Escenarios de Aceptación**:

1. **Dado** que soy el pastor, **cuando** abro la sección de comités, **entonces** veo la estructura
   completa de los tres niveles, con sus responsables y cuántos miembros tiene cada nodo.
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

**Excepción declarada**: el cronograma de actividades es información compartida de la congregación.
Ver HU-09 y RF-010.

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
   título, fecha, hora, lugar, descripción y presupuesto estimado, **entonces** el sistema la
   registra como propuesta y me indica con nombre propio a quién le llegó.
2. **Dado** que soy líder de sub-comité, **cuando** propongo una actividad, **entonces** la
   solicitud le llega primero al presidente de mi comité.
3. **Dado** que soy presidente de comité, **cuando** propongo una actividad, **entonces** la
   solicitud le llega directamente al pastor.
4. **Dado** que dejo el título vacío o pongo una fecha fuera del año en curso, **cuando** intento
   enviarla, **entonces** el sistema no la envía y me señala exactamente qué campo corregir.
5. **Dado** que la fecha que escogí coincide con otra actividad de la congregación, **cuando** la
   propongo, **entonces** el sistema me advierte qué actividad es y de qué comité, para que lo
   coordinemos antes de insistir.

---

### Historia de Usuario 6 — HU-06 · Revisar lo que proponen mis sub-comités (Prioridad: P1)

Como presidente de comité quiero revisar y resolver lo que proponen mis líderes, para responder por
lo que hace mi comité.

**Por qué esta prioridad**: es el primer nivel de la aprobación en dos niveles. Sin él, el pastor
recibiría todo directamente y se perdería la responsabilidad del presidente.

**Prueba independiente**: se puede probar sola enviando los cuatro tipos de solicitud desde un
líder y resolviéndolas como presidente, comprobando qué queda cerrado y qué escala al pastor.

**Escenarios de Aceptación**:

1. **Dado** que un líder me envió una solicitud de gasto, tarea o cambio de fecha, **cuando** la
   apruebo, **entonces** queda resuelta en ese momento y el líder ve el resultado con mi nombre y
   la fecha.
2. **Dado** que un líder me envió una propuesta de actividad nueva, **cuando** la apruebo,
   **entonces** no queda cerrada: pasa al pastor, y el sistema se lo informa a quien la propuso.
3. **Dado** que decido no aprobar una solicitud, **cuando** la rechazo, **entonces** el sistema me
   exige escribir el motivo, y ese motivo queda visible para quien la propuso.
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
   cronograma y, cuando es una propuesta de actividad, su presupuesto estimado. Ningún gasto llega
   a esta bandeja: el gasto del propio comité lo autoriza su presidente y el del líder lo resuelve
   su presidente (RF-018); yo los veo en el presupuesto del comité, no aquí.
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
   título, descripción, fecha, hora, lugar, comité responsable y estado, pero **no** su presupuesto
   ni sus tareas internas.
4. **Dado** que una actividad tiene tareas pendientes, **cuando** marco una como cumplida,
   **entonces** el avance de esa actividad se recalcula solo, sin que nadie lo escriba a mano.

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

Como persona con cargo quiero mantener al día la lista de los miembros de mi ámbito, para que los
reportes de la congregación sean ciertos.

**Por qué esta prioridad**: el sistema de comités funciona sin el padrón completo, pero los
reportes y las estadísticas no sirven hasta que los miembros estén cargados y al día.

**Prueba independiente**: se puede probar sola registrando, buscando y filtrando miembros con cada
cargo, incluidas las validaciones de edad y de bautismo.

**Escenarios de Aceptación**:

1. **Dado** que quiero registrar a un hermano, **cuando** ingreso nombre, fecha de nacimiento,
   género, estado civil, si está bautizado, comité, sub-comité, teléfono y barrio, **entonces**
   queda registrado, el sistema me muestra los años que tiene, y aparece de inmediato en las listas
   y en los números.
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

Como persona con cargo quiero dejar anotado a alguien que visitó la iglesia, para que no se pierda
de vista mientras aviso al comité encargado.

**Por qué esta prioridad**: resuelve uno de los problemas declarados —las personas que empiezan a
visitar la iglesia se pierden de vista— y es la puerta de entrada del acompañamiento.

**Prueba independiente**: se puede probar sola registrando un simpatizante con un cargo cualquiera
y comprobando que el seguimiento no le queda visible a quien lo registró.

**Escenarios de Aceptación**:

1. **Dado** que invité a alguien y vino al culto, **cuando** registro su nombre, su fecha de
   nacimiento si la sé —o los años que me diga, cuando es lo único que sé—, género, teléfono,
   barrio y cómo llegó, **entonces** queda registrado, mi nombre queda como quien lo invitó, y el
   seguimiento pasa al presidente del Comité de Evangelismo.
2. **Dado** que registré a una persona, **cuando** vuelvo a consultar, **entonces** no veo su ficha
   de seguimiento, porque esa información la lleva el presidente del Comité de Evangelismo; el
   sistema me lo explica sin que parezca un error.
3. **Dado** que registro a alguien que ya existe, **cuando** guardo, **entonces** el sistema me
   advierte que hay una persona con ese nombre y teléfono y me permite continuar o cancelar.
4. **Dado** que estoy registrando a un simpatizante, **cuando** abro el formulario, **entonces**
   veo el aviso de privacidad y el sistema me pide anotar si la persona autorizó el manejo de sus
   datos, con su fecha y su medio.

---

### Historia de Usuario 15 — HU-14 · Acompañar a los simpatizantes (Prioridad: P2)

Como presidente del Comité de Evangelismo quiero saber cómo va cada persona que está visitando la
iglesia, para darme cuenta a tiempo de quién se está alejando.

**Por qué esta prioridad**: convierte el registro de simpatizantes en acompañamiento real, pero
depende de que el registro exista (HU-13).

**Prueba independiente**: se puede probar sola con simpatizantes cargados, recorriendo sus etapas,
registrando visitas y comprobando que el nivel de compromiso se recalcula sin intervención manual.

**Escenarios de Aceptación**:

1. **Dado** que abro la sección de simpatizantes, **cuando** la pantalla carga, **entonces** veo
   cuántas personas acompañamos, cuántas están creciendo, cuántas estables y cuántas se están
   alejando.
2. **Dado** que quiero ver el camino de una persona, **cuando** abro su ficha, **entonces** veo en
   qué etapa va, con la fecha de cada una: visitó el culto, asiste con frecuencia, recibe estudio
   bíblico, se bautizó en el nombre de Jesucristo, recibió el Espíritu Santo.
3. **Dado** que el sistema califica el compromiso de una persona, **cuando** consulto cómo lo
   determinó, **entonces** el sistema lo calcula con su asistencia a los cultos recientes y los
   días transcurridos desde la última visita, y nadie lo escribe a mano.
4. **Dado** que visitamos a una persona, **cuando** registro la visita con su fecha, tipo y una
   nota, **entonces** esa visita queda en su historia y el indicador de compromiso se recalcula.
5. **Dado** que una persona recorrió todas las etapas, **cuando** propongo su paso a miembro,
   **entonces** la solicitud le llega al pastor, y solo cuando él la apruebe la persona entra a la
   lista de miembros.

---

### Historia de Usuario 16 — HU-15 · Registrar asistencia (Prioridad: P2)

Como líder de sub-comité o presidente de comité quiero anotar quién asistió a cada ensayo o
reunión, para saber a quién hay que buscar.

**Por qué esta prioridad**: da seguimiento al compromiso de los miembros, pero la coordinación de
comités funciona sin ella.

**Prueba independiente**: se puede probar sola registrando una reunión, marcando asistentes,
corrigiendo una marca y verificando los porcentajes por persona.

**Escenarios de Aceptación**:

1. **Dado** que tuvimos un ensayo, **cuando** registro la fecha, el tipo, el tema y marco quiénes
   asistieron, **entonces** queda registrado y el porcentaje de asistencia de cada persona se
   actualiza.
2. **Dado** que me equivoqué al marcar, **cuando** abro la reunión y corrijo, **entonces** el
   sistema guarda la corrección y conserva qué se había registrado antes.
3. **Dado** que reviso la asistencia por persona, **cuando** abro la lista, **entonces** veo a
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

Como pastor quiero crear, renombrar, reordenar e inactivar comités, sub-comités y equipos, para que
el organigrama del sistema sea el de la congregación de hoy y no el del día que se instaló.

**Por qué esta prioridad**: la estructura es el esqueleto del que dependen el filtro de alcance, las
aprobaciones, el presupuesto y la asistencia. Y no se puede cablear: ni el distrito ni la sede
publican su organigrama, y cambia entre vigencias.

**Prueba independiente**: se puede probar sola creando un comité con un sub-comité y un equipo,
renombrándolos, inactivando uno y comprobando que su historia sigue consultable.

**Escenarios de Aceptación**:

1. **Dado** que la congregación organiza un comité nuevo, **cuando** lo creo con su nombre y su
   responsable, **entonces** queda disponible de inmediato para proponer actividades, recibir
   presupuesto y registrar asistencia.
2. **Dado** que un comité necesita dividirse, **cuando** le creo un sub-comité y a ese sub-comité un
   equipo, **entonces** el sistema lo acepta y el ámbito de cada responsable incluye a sus
   descendientes.
3. **Dado** que intento colgar un nodo de un equipo, **cuando** guardo, **entonces** el sistema no
   lo permite y explica que la estructura llega hasta tres niveles.
4. **Dado** que un comité cambia de nombre, **cuando** lo renombro, **entonces** las actas y los
   reportes ya registrados siguen mostrando el nombre que tenía en esa fecha.
5. **Dado** que un comité deja de funcionar, **cuando** lo inactivo, **entonces** no aparece más
   para proponer cosas nuevas, su historia completa sigue consultable, y sus miembros quedan sin
   nodo asignado y señalados para que yo los reasigne.
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

1. **Dado** que mi comité tuvo una reunión ya registrada, **cuando** levanto su acta, **entonces**
   el sistema trae la fecha, el lugar y los asistentes de esa reunión, y me deja escribir el orden
   del día, el desarrollo y los compromisos.
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

Como líder o presidente quiero adjuntar fotos y documentos a una actividad, para demostrar que se
cumplió sin tener que explicarlo de palabra.

**Por qué esta prioridad**: da respaldo a lo que el cronograma afirma, pero el avance ya se calcula
sin ella.

**Prueba independiente**: se puede probar sola subiendo una foto y un documento a una actividad de
mi comité y comprobando que un cargo ajeno no los ve.

**Escenarios de Aceptación**:

1. **Dado** que mi comité cumplió una actividad, **cuando** subo una foto o un documento como
   evidencia, **entonces** queda adjunta a la actividad con mi nombre y la fecha.
2. **Dado** que subo una foto tomada con el celular, **cuando** el sistema la recibe, **entonces**
   la reduce para que pese menos sin que yo tenga que hacer nada, y me avisa si pasa de 10 MB.
3. **Dado** que soy de otro comité, **cuando** consulto esa actividad en el cronograma compartido,
   **entonces** veo su título, fecha y comité responsable, pero **ninguna de sus evidencias**.
4. **Dado** que subí la foto equivocada a una actividad ya cumplida, **cuando** intento borrarla,
   **entonces** el sistema no la borra: la marca como reemplazada, la conserva en la historia y deja
   que suba la correcta.
5. **Dado** que una actividad no tiene evidencias, **cuando** la abro, **entonces** el sistema lo
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
- **CL-08.** Dos actividades el mismo día, hora y lugar: debe advertirse antes de proponer.
- **CL-09.** Un simpatizante registrado dos veces por personas distintas: debe advertirse la
  duplicidad.
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
- **CL-17.** Un equipo —tercer nivel— intenta tener un hijo: el sistema debe impedirlo y explicar
  que la estructura llega hasta tres niveles.
- **CL-18.** Un comité inactivado: su historia completa sigue consultable, no aparece para proponer
  nada nuevo, y sus miembros quedan sin nodo asignado y señalados para reasignar.
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

## Requisitos *(obligatorio)*

Se conserva la numeración del documento de entrada (RF-001 a RF-057). RF-058 a RF-060 se agregan
para que las promesas del celular y del lenguaje queden verificables, conforme a los principios II
y IV de la constitución. RF-061 y RF-062 provienen de la sesión de aclaraciones del 2026-10-18.
RF-063 a RF-080 provienen de la ampliación de alcance del 2026-10-24: estructura administrable en
tres niveles (RF-063 a RF-066), actas de reunión (RF-067 a RF-074) y archivos adjuntos (RF-075 a
RF-080).

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
  su alcance, sin detallarla.
- **RF-010**: El cronograma es la única excepción: toda persona con cargo consulta título,
  descripción, fecha, hora, lugar, comité responsable y estado de cualquier actividad. El
  presupuesto y las tareas internas permanecen reservados a su comité.
- **RF-061**: Toda ficha de persona —miembro o simpatizante— guarda si esa persona autorizó el
  manejo de sus datos, la fecha de la autorización y cómo la dio: firma, de palabra en el culto o
  mensaje. El sistema informa a quien corresponda cuántas fichas de su ámbito están sin
  autorización registrada.
- **RF-062**: Toda pantalla donde se registra o se edita a una persona muestra el aviso de
  privacidad de la congregación: qué datos se guardan, para qué se usan y a quién dirigirse para
  consultarlos o corregirlos.

#### Comités

- **RF-011**: El sistema representa la estructura en **tres niveles**: comité, sub-comité y equipo.
  Cada nodo tiene cero o más hijos, y ningún nodo cuelga de un equipo: el tercer nivel es el
  último.
- **RF-012**: Un nodo sin hijos se identifica expresamente como tal, y la ficha aclara que su
  responsable coordina directamente a sus miembros.
- **RF-013**: Cada nodo tiene un único responsable vigente, y hasta un secretario y un tesorero
  vigentes. El responsable de un comité es su presidente; el de un sub-comité o de un equipo, su
  líder.
- **RF-014**: Un miembro pertenece a un solo nodo, del nivel que sea, o a ninguno.
- **RF-063**: Solo el pastor crea, renombra, reordena e inactiva nodos de la estructura, en los
  tres niveles. Ningún otro cargo la modifica.
- **RF-064**: Un nodo inactivado conserva toda su historia consultable —actividades, actas,
  asistencia, presupuesto y decisiones—, deja de aparecer para proponer cosas nuevas, y sus
  miembros quedan sin nodo asignado y señalados para reasignar.
- **RF-065**: Renombrar un nodo no reescribe la historia: las actas, los reportes y las decisiones
  ya registradas conservan el nombre que el nodo tenía en esa fecha.
- **RF-066**: El sistema no supone una cantidad ni unos nombres de comités. La estructura inicial
  la carga el pastor, y toda pantalla y todo reporte se arman con la estructura vigente.

#### Aprobaciones

- **RF-015**: Son objeto de aprobación: actividad nueva, gasto, tarea y cambio de fecha.
- **RF-016**: Lo propuesto por un líder llega al presidente de su comité.
- **RF-017**: Lo propuesto por un presidente llega al pastor, salvo el gasto de su propio comité,
  que él mismo autoriza (RF-018).
- **RF-018**: Una actividad nueva aprobada por el presidente escala al pastor; el gasto, la tarea y
  el cambio de fecha propuestos por un líder quedan resueltos por el presidente. El gasto del
  propio comité lo autoriza el presidente sin pasar por el pastor, cualquiera sea su monto: queda
  registrado con su nombre y la fecha, y el pastor lo ve en el presupuesto, no en su bandeja.
- **RF-019**: El paso de un simpatizante a miembro lo propone el presidente del Comité de
  Evangelismo y lo aprueba el pastor.
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
- **RF-028**: Al proponer una actividad, el sistema advierte si ya existe otra en esa fecha,
  indicando cuál y de qué comité.
- **RF-029**: El cronograma se consulta por año completo o como lista, y se filtra por comité,
  sub-comité, mes y estado.
- **RF-030**: Los totales y el porcentaje de cumplimiento se recalculan según el filtro aplicado.

#### Miembros y reportes

- **RF-031**: El registro de un miembro incluye nombre, fecha de nacimiento, género, estado civil,
  si está bautizado, fecha de bautismo, fecha de ingreso, comité, sub-comité, teléfono y barrio. El
  formulario pide la fecha de nacimiento y, cuando la congregación no la tiene, ofrece «No la sé —
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
  el reporte informa cuántos son.
- **RF-037**: Todo reporte gráfico tiene su equivalente en tabla.
- **RF-038**: Todo reporte se descarga como hoja de cálculo y se imprime, respetando los filtros
  aplicados y el ámbito del cargo.
- **RF-039**: El sistema permite importar miembros desde una plantilla, validando antes de guardar
  e informando fila por fila los errores.

#### Simpatizantes

- **RF-040**: Cualquier cargo registra un simpatizante. La ficha de seguimiento la consulta y la
  actualiza únicamente el presidente del Comité de Evangelismo; el pastor también la ve, porque
  responde por toda la congregación. Ningún otro cargo la abre, ni siquiera los líderes de los
  sub-comités de Evangelismo ni quien registró a la persona.
- **RF-041**: El registro incluye nombre, fecha de nacimiento, género, teléfono, barrio, cómo
  llegó, quién lo invitó y la fecha del primer contacto. La fecha de nacimiento es opcional e,
  igual que en la ficha de miembro (RF-031), admite que se anote la edad con su fecha cuando no se
  sabe el día de nacimiento; de cualquiera de las dos el sistema calcula la edad.
- **RF-042**: El camino consta de cinco etapas en este orden: visitó el culto; asiste con
  frecuencia; recibe estudio bíblico; se bautizó en el nombre de Jesucristo; recibió el Espíritu
  Santo. Cada etapa alcanzada conserva su fecha.
- **RF-043**: El sistema calcula el nivel de compromiso a partir de la asistencia reciente y los
  días transcurridos desde la última visita. Nadie lo escribe a mano.
- **RF-044**: Cada visita, llamada o estudio se registra con fecha, tipo, responsable y una nota.
- **RF-045**: Al aprobarse el paso a miembro, la persona entra a la lista de miembros conservando
  su fecha de bautismo y su fecha de primer contacto.

#### Asistencia

- **RF-046**: Cada reunión registra fecha, tipo, tema, convocados y asistentes.
- **RF-047**: Los convocados solo pueden ser miembros del comité o sub-comité correspondiente.
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

- **RF-067**: Toda acta pertenece a una reunión ya registrada y hereda su fecha, su lugar y sus
  asistentes. No existen actas sueltas.
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

- **RF-075**: Una actividad admite archivos que respalden su cumplimiento —fotos y documentos—, y un
  movimiento de presupuesto admite su soporte —factura o recibo, en PDF o imagen—.
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
- **RN-04**: El monto de un gasto no determina quién lo aprueba: el presidente autoriza los gastos
  de su comité, cualquiera sea su valor.
- **RN-05**: El presupuesto es una guía, no un tope rígido: se puede exceder, pero el sistema lo
  advierte y queda registrado.
- **RN-06**: Nadie pasa a ser miembro sin aprobación del pastor.
- **RN-07**: Ninguna aprobación, rechazo o movimiento registrado se elimina.
- **RN-08**: El lenguaje de todo el sistema es el de la congregación, no el de la informática.
- **RN-09**: Quien deja un cargo pierde el acceso el mismo día, y su nombre permanece en la
  historia de lo que decidió.

### Fuera del Alcance

Lo siguiente no se construye. Si la congregación lo pide, se registra como propuesta para cotizar.

- Avisos por WhatsApp, correo electrónico o mensaje de texto.
- Acceso para los miembros que no tienen cargo.
- Manejo de dinero: el sistema registra en qué se gastó y quién lo autorizó; la tesorería de la
  iglesia sigue manejando los recursos.
- Integración con cualquier otro sistema.
- Más de una sede en operación. El sistema queda preparado para varias, pero la licencia cubre una.

### Entidades Principales

- **Sede**: la congregación que opera el sistema. Toda la información de comités, personas,
  actividades, asistencia y presupuesto pertenece a una sede. Hoy existe una sola.
- **Comité**: unidad de trabajo de la congregación, con nombre, responsable vigente, su secretario y
  su tesorero, su marca de activo o inactivo, el orden en que se muestra, y cero o más
  sub-comités. Son seis.
- **Sub-comité**: unidad dependiente de un comité, con su propio responsable.
- **Equipo**: unidad dependiente de un sub-comité, con su propio responsable. Es el tercer y último
  nivel: un equipo no tiene hijos.
- **Acta de reunión**: el registro de lo tratado en una reunión, con su número del año, el orden del
  día, el desarrollo, los compromisos, quién la redactó, quién la cerró y cuándo. Cerrada, no se
  edita ni se borra.
- **Compromiso de acta**: cada acuerdo que sale de una reunión, con su texto y, cuando se convierte,
  su responsable y su fecha como tarea.
- **Archivo adjunto**: foto o documento que respalda el cumplimiento de una actividad, o soporte de
  un movimiento de presupuesto, con quién lo subió, cuándo, su peso y su marca de reemplazado.
- **Cargo**: el papel que una persona ejerce —pastor, presidente de comité, secretario, tesorero, o
  líder de sub-comité o de equipo— sobre un nodo determinado de la estructura, con fecha de inicio
  y, cuando aplica, de retiro. Determina el ámbito de información de su titular y lo que puede
  hacer dentro de él.
- **Cuenta de acceso**: el usuario y la clave de una persona con cargo, ligada a la única ficha de
  miembro de esa persona, con la marca de si debe cambiar la clave, los intentos fallidos recientes
  y si está vigente o retirada.
- **Miembro**: hermano o hermana de la congregación, con nombre, fecha de nacimiento —o la edad
  anotada con el día en que se anotó, cuando la fecha no se sabe—, de la que el sistema calcula la
  edad, género, estado civil, condición de bautizado y su fecha, fecha de ingreso, teléfono,
  barrio, su pertenencia a un comité y opcionalmente a un sub-comité, la marca de ficha pendiente
  de completar —cuando nació al habilitar una cuenta o cuando no tiene ni fecha de nacimiento ni
  edad anotada—, y la autorización para manejar sus datos con su fecha y su medio. Es la única
  ficha de la persona, tenga cargo o no.
- **Simpatizante**: persona que está visitando la iglesia, con quién la invitó, cómo llegó, la
  fecha del primer contacto, la etapa alcanzada de su camino, su nivel de compromiso calculado y la
  autorización para manejar sus datos con su fecha y su medio.
- **Etapa del camino**: cada uno de los cinco momentos del acompañamiento de un simpatizante, con
  la fecha en que se alcanzó.
- **Contacto de acompañamiento**: visita, llamada o estudio bíblico hecho a un simpatizante, con
  fecha, tipo, responsable y nota.
- **Plan anual**: el conjunto de actividades de un año, con la constancia de quién lo aprobó y
  cuándo, y la distinción entre lo que venía en el plan y lo que se propuso después.
- **Actividad**: lo que un comité o sub-comité va a hacer, con título, descripción, fecha, hora,
  lugar, comité responsable, presupuesto estimado, estado y avance calculado.
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
- **CE-04**: Cero actividades cruzadas en el año, entendiendo por cruce dos actividades en el mismo
  día, hora y lugar.

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

**Dependencias del cliente**:

Son las cuatro entregas que el proyecto espera de la congregación. Se identifican con el prefijo
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
- **DEP-05**: El pastor entrega la **estructura inicial de comités** —nombres de los tres niveles y
  sus responsables, secretarios y tesoreros— antes de habilitar las cuentas. El sistema no supone
  ninguna: ni el Distrito 28 ni la sede de Ibagué publican su organigrama, y la página «Comités y
  Oficios» del distrito está sin diligenciar, así que este dato solo lo tiene la congregación
  (RF-066, HU-21).
- **DEP-06**: Si el distrito exige un **formato oficial de acta**, el pastor entrega una copia de un
  acta real antes de que se construya la vista de impresión. No encontré ninguno publicado. Mientras
  no llegue, rige el formato de RF-072: nombre de la iglesia, comité, número, fecha, asistentes,
  desarrollo, compromisos y firmas.
- **DEP-07**: El respaldo diario debe **ampliarse para incluir los archivos** del disco, no solo la
  base de datos (RF-078). Hoy solo cubre la base.
