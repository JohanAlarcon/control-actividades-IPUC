# Especificación — Sistema de Comités IPUC

## 1. Problema y propósito

Hoy la congregación coordina el trabajo de sus comités con cuadernos personales y grupos de
WhatsApp. En consecuencia: los acuerdos se pierden entre mensajes, dos comités programan
actividades el mismo día, la lista de miembros está desactualizada, nadie sabe con certeza quién
autorizó un gasto, y las personas que empiezan a visitar la iglesia se pierden de vista.

El sistema debe convertir ese trabajo en información consultable y verificable, sin cambiar la
forma en que la congregación ya trabaja: los comités proponen, el presidente de cada comité
responde por sus sub-comités, y el pastor aprueba lo nuevo y lo que se sale del plan del año.

**Por qué importa:** la congregación aprobó esta propuesta en reunión. Lo que se prometió allí es
lo que debe cumplirse: que cada comité vea solo lo suyo, que toda aprobación quede registrada, y
que el pastor pueda responder por toda la congregación sin pedirle cuentas a nadie por WhatsApp.

---

## 2. Usuarios

Solo las personas con un cargo entran al sistema. Los demás miembros están registrados, pero no
tienen acceso.

| Usuario | Cuántos | Qué hace |
|---|---|---|
| **Pastor** | 1 | Ve toda la congregación. Crea y quita cuentas. Aprueba el plan anual, las actividades nuevas y el paso de simpatizantes a miembros. Asigna el presupuesto de cada comité. |
| **Presidente de comité** | 6 | Ve su comité y sus sub-comités. Revisa y resuelve lo que le proponen sus líderes. Reparte el presupuesto entre sus sub-comités. |
| **Líder de sub-comité** | 10 | Ve únicamente su sub-comité. Propone actividades y gastos, registra avance, toma asistencia. |

**Caso particular:** el presidente del Comité de Evangelismo es, además, el único que hace
seguimiento a los simpatizantes.

---

## 3. Alcance

### Dentro del alcance

1. Comités y sub-comités, con su jerarquía, responsables y miembros.
2. Cuentas de acceso y control de lo que ve cada cargo.
3. Aprobaciones en dos niveles.
4. Cronograma anual: plan aprobado, propuestas durante el año, seguimiento del avance.
5. Registro de miembros con reportes y estadísticas.
6. Simpatizantes: registro, seguimiento por etapas y paso a miembro.
7. Asistencia a reuniones y ensayos.
8. Presupuesto asignado y ejecutado por comité.

### Fuera del alcance

- Avisos por WhatsApp, correo electrónico o mensaje de texto.
- Acceso para los miembros que no tienen cargo.
- Manejo de dinero: el sistema registra en qué se gastó y quién lo autorizó; la tesorería de la
  iglesia sigue manejando los recursos.
- Integración con cualquier otro sistema.
- Más de una sede en operación (el sistema queda preparado, pero la licencia cubre una).

---

## 4. Supuestos pendientes de confirmar

Estos puntos se resolvieron con el valor más defendible, pero no fueron confirmados por el cliente.
Deben validarse antes de planear.

- **S-01.** Las personas se enteran de lo que tienen pendiente **únicamente al entrar al sistema**
  (un contador visible en el menú y un panel en la pantalla de inicio). No hay avisos externos.
- **S-02.** Los 140 miembros se cargan la primera vez mediante **importación de una plantilla de
  hoja de cálculo** que el sistema valida antes de guardar.

---

## 5. Historias de usuario

Prioridad **P1** = sin esto no hay sistema. **P2** = el sistema sirve pero queda incompleto.
**P3** = mejora el uso diario.

---

### HU-01 · Entrar al sistema por primera vez — P1

**Como** persona con un cargo en la congregación
**quiero** entrar con un usuario sencillo y cambiar mi clave la primera vez
**para** no depender de tener correo electrónico ni de recordar datos complicados.

- **Dado** que el pastor me creó una cuenta con una clave temporal,
  **cuando** entro por primera vez con ese usuario y esa clave,
  **entonces** el sistema me pide cambiar la clave antes de dejarme ver cualquier información.
- **Dado** que ya cambié mi clave,
  **cuando** vuelvo a entrar,
  **entonces** el sistema me lleva directamente a mi pantalla de inicio.
- **Dado** que escribo mal mi clave cinco veces seguidas,
  **cuando** intento una sexta,
  **entonces** el sistema bloquea el acceso durante quince minutos y me indica que le pida al
  pastor que me la restablezca.
- **Dado** que olvidé mi clave,
  **cuando** se lo informo al pastor,
  **entonces** él puede generarme una clave temporal nueva y el sistema vuelve a exigirme
  cambiarla al entrar.

---

### HU-02 · Administrar las cuentas y los cargos — P1

**Como** pastor
**quiero** crear, modificar y retirar las cuentas de quienes tienen cargo
**para** que nadie conserve acceso a información que ya no le corresponde.

- **Dado** que un hermano asume la presidencia de un comité,
  **cuando** le creo la cuenta y le asigno ese cargo y ese comité,
  **entonces** el sistema genera su usuario y una clave temporal que puedo entregarle.
- **Dado** que un líder deja su sub-comité,
  **cuando** retiro su acceso,
  **entonces** esa persona deja de entrar inmediatamente, y su nombre se conserva en el historial
  de todo lo que aprobó o propuso.
- **Dado** que una persona tenía solicitudes pendientes al dejar el cargo,
  **cuando** asigno el cargo a su reemplazo,
  **entonces** las solicitudes pendientes quedan a nombre del nuevo responsable y el sistema
  registra quién las recibió y en qué fecha.
- **Dado** que intento asignar dos cargos a la misma persona,
  **cuando** guardo,
  **entonces** el sistema lo permite y la persona ve la información de ambos ámbitos, claramente
  identificada.

---

### HU-03 · Ver la estructura de comités — P1

**Como** persona con cargo
**quiero** ver cómo está organizada la congregación en comités y sub-comités
**para** entender de quién depende cada cosa y a quién acudir.

- **Dado** que soy el pastor,
  **cuando** abro la sección de comités,
  **entonces** veo los seis comités, sus diez sub-comités, sus responsables y cuántos miembros
  tiene cada uno.
- **Dado** que soy presidente de un comité,
  **cuando** abro la misma sección,
  **entonces** veo únicamente mi comité y mis sub-comités; los demás aparecen como no disponibles,
  sin mostrar ningún dato suyo.
- **Dado** que un comité no tiene sub-comités,
  **cuando** abro su ficha,
  **entonces** el sistema lo indica expresamente y muestra que el presidente coordina
  directamente a sus miembros.
- **Dado** que soy líder de un sub-comité,
  **cuando** abro la sección,
  **entonces** veo mi sub-comité y el nombre de mi presidente, porque es la persona que revisa lo
  que propongo.

---

### HU-04 · Cada comité ve únicamente lo suyo — P1

**Como** miembro de la congregación
**quiero** tener la certeza de que mi información no la ve cualquiera
**para** confiar en que el sistema no expone a los hermanos.

- **Dado** que soy presidente de un comité,
  **cuando** consulto miembros, aprobaciones, asistencia o presupuesto,
  **entonces** solo obtengo lo de mi comité y mis sub-comités, sin excepción.
- **Dado** que intento abrir directamente la información de otro comité,
  **cuando** el sistema la recibe,
  **entonces** me responde que esa información no está disponible para mi cargo e indica a quién
  debo solicitarla, sin revelar ningún dato.
- **Dado** que soy líder de un sub-comité,
  **cuando** consulto la lista de miembros,
  **entonces** veo únicamente a los de mi sub-comité, y el sistema me indica cuántas personas de
  la congregación no aparecen allí y por qué.
- **Dado** que soy el pastor,
  **cuando** consulto cualquier información,
  **entonces** la veo completa, porque respondo por toda la congregación.

**Excepción declarada:** el cronograma de actividades es información compartida de la
congregación. Ver HU-09.

---

### HU-05 · Proponer una actividad nueva — P1

**Como** líder de sub-comité o presidente de comité
**quiero** proponer una actividad que no estaba en el plan del año
**para** que quede evaluada y aprobada antes de comprometer a la iglesia.

- **Dado** que quiero hacer una actividad que no está en el plan,
  **cuando** la propongo indicando título, fecha, hora, lugar, descripción y presupuesto estimado,
  **entonces** el sistema la registra como propuesta y me indica con nombre propio a quién le llegó.
- **Dado** que soy líder de sub-comité,
  **cuando** propongo una actividad,
  **entonces** la solicitud le llega primero al presidente de mi comité.
- **Dado** que soy presidente de comité,
  **cuando** propongo una actividad,
  **entonces** la solicitud le llega directamente al pastor.
- **Dado** que dejo el título vacío o pongo una fecha fuera del año en curso,
  **cuando** intento enviarla,
  **entonces** el sistema no la envía y me señala exactamente qué campo corregir.
- **Dado** que la fecha que escogí coincide con otra actividad de la congregación,
  **cuando** la propongo,
  **entonces** el sistema me advierte qué actividad es y de qué comité, para que lo coordinemos
  antes de insistir.

---

### HU-06 · Revisar lo que proponen mis sub-comités — P1

**Como** presidente de comité
**quiero** revisar y resolver lo que proponen mis líderes
**para** responder por lo que hace mi comité.

- **Dado** que un líder me envió una solicitud de gasto, tarea o cambio de fecha,
  **cuando** la apruebo,
  **entonces** queda resuelta en ese momento y el líder ve el resultado con mi nombre y la fecha.
- **Dado** que un líder me envió una propuesta de actividad nueva,
  **cuando** la apruebo,
  **entonces** no queda cerrada: pasa al pastor, y el sistema se lo informa a quien la propuso.
- **Dado** que decido no aprobar una solicitud,
  **cuando** la rechazo,
  **entonces** el sistema me exige escribir el motivo, y ese motivo queda visible para quien la
  propuso.
- **Dado** que tengo varias solicitudes esperando,
  **cuando** abro mi pantalla de inicio,
  **entonces** veo cuántas son y desde cuándo esperan, antes que cualquier otra cosa.

---

### HU-07 · Aprobar lo que sube al pastor — P1

**Como** pastor
**quiero** aprobar o rechazar lo nuevo y lo que se sale del plan
**para** mantener el orden del año sin tener que estar en cada reunión de comité.

- **Dado** que tengo solicitudes esperando mi decisión,
  **cuando** abro la bandeja,
  **entonces** veo de qué comité viene cada una, quién la pidió, por quién ya pasó, qué monto
  implica y qué efecto tiene en el cronograma.
- **Dado** que apruebo una propuesta de actividad,
  **cuando** confirmo,
  **entonces** la actividad entra al cronograma del año como aprobada, con mi nombre y la fecha.
- **Dado** que no apruebo una propuesta,
  **cuando** escribo el motivo y confirmo,
  **entonces** la actividad queda como no aprobada, con el motivo visible para el comité, y no
  aparece en el cronograma vigente.
- **Dado** que tengo varias solicitudes del mismo tipo,
  **cuando** decido resolverlas todas,
  **entonces** el sistema me permite hacerlo en una sola acción, pidiéndome confirmación una vez.

---

### HU-08 · Aprobar el plan del año — P1

**Como** pastor
**quiero** aprobar de una sola vez el cronograma completo del año
**para** no tener que autorizar cada actividad por separado.

- **Dado** que los comités cargaron sus actividades para el año entrante,
  **cuando** reviso y apruebo el plan,
  **entonces** todas esas actividades quedan aprobadas con mi nombre y la fecha de aprobación.
- **Dado** que el plan del año ya fue aprobado,
  **cuando** cualquiera consulta el cronograma,
  **entonces** el sistema muestra quién lo aprobó y cuándo.
- **Dado** que una actividad fue cargada después de aprobado el plan,
  **cuando** se consulta,
  **entonces** el sistema la distingue claramente de las que venían en el plan original.

---

### HU-09 · Consultar el cronograma del año — P1

**Como** persona con cargo
**quiero** ver el año completo y cómo va el cumplimiento
**para** no cruzarme con otros comités y saber qué falta.

- **Dado** que abro el cronograma,
  **cuando** elijo ver el año completo,
  **entonces** veo los doce meses con sus actividades, distinguiendo las cumplidas, las que van en
  camino, las aprobadas que aún no empiezan y las que esperan aprobación.
- **Dado** que quiero ver un comité en particular,
  **cuando** lo escojo,
  **entonces** tanto la lista como los números del resumen y la barra de cumplimiento se
  recalculan solo con ese comité.
- **Dado** que consulto una actividad de otro comité,
  **cuando** la abro,
  **entonces** veo su título, descripción, fecha, hora, lugar y comité responsable, pero **no** su
  presupuesto ni sus tareas internas.
- **Dado** que una actividad tiene tareas pendientes,
  **cuando** marco una como cumplida,
  **entonces** el avance de esa actividad se recalcula solo, sin que nadie lo escriba a mano.

---

### HU-10 · Registrar y consultar miembros — P2

**Como** persona con cargo
**quiero** mantener al día la lista de los miembros de mi ámbito
**para** que los reportes de la congregación sean ciertos.

- **Dado** que quiero registrar a un hermano,
  **cuando** ingreso nombre, edad, género, estado civil, si está bautizado, comité, sub-comité,
  teléfono y barrio,
  **entonces** queda registrado y aparece de inmediato en las listas y en los números.
- **Dado** que registro a una persona menor de dieciocho años,
  **cuando** intento marcarla como casada o viuda,
  **entonces** el sistema no lo permite y explica por qué.
- **Dado** que marco a alguien como bautizado,
  **cuando** guardo sin la fecha de bautismo,
  **entonces** el sistema me la exige.
- **Dado** que busco un hermano por nombre o por barrio,
  **cuando** escribo parte del texto,
  **entonces** la lista se filtra sin distinguir mayúsculas ni tildes.
- **Dado** que necesito un grupo específico,
  **cuando** combino filtros de comité, género, estado civil, bautizado y rango de edad,
  **entonces** el sistema muestra cuántas personas cumplen y me deja quitar los filtros de una vez.

---

### HU-11 · Consultar reportes y estadísticas — P2

**Como** pastor
**quiero** obtener en el momento las cifras de la congregación
**para** responder en las reuniones sin tener que contar a mano.

- **Dado** que abro los reportes,
  **cuando** la pantalla carga,
  **entonces** veo total de miembros, bautizados con su porcentaje, casados, solteros, edad
  promedio y cuántos no pertenecen a ningún comité.
- **Dado** que quiero el detalle,
  **cuando** reviso los gráficos,
  **entonces** encuentro la distribución por estado civil, por comité, por rango de edad, por
  género, bautizados y bautismos por año.
- **Dado** que alguien no distingue bien los colores o prefiere los números,
  **cuando** cambia a la vista de tabla,
  **entonces** obtiene exactamente la misma información en forma de tabla.
- **Dado** que soy presidente o líder,
  **cuando** abro los reportes,
  **entonces** obtengo los mismos reportes pero solo con mi gente, y el sistema aclara que los
  totales de la congregación los ve únicamente el pastor.

---

### HU-12 · Descargar e imprimir los reportes — P2

**Como** pastor
**quiero** llevarme las cifras en un archivo o en papel
**para** presentarlas en reuniones de distrito y convenciones.

- **Dado** que estoy viendo un reporte con filtros aplicados,
  **cuando** lo descargo,
  **entonces** obtengo un archivo de hoja de cálculo con exactamente la información que estoy
  viendo, no con todo.
- **Dado** que necesito el reporte en papel,
  **cuando** elijo imprimir,
  **entonces** obtengo una versión legible en blanco y negro, con el nombre de la iglesia, la
  fecha y los filtros aplicados.
- **Dado** que soy presidente o líder,
  **cuando** descargo o imprimo,
  **entonces** el archivo contiene únicamente la información de mi ámbito.

---

### HU-13 · Registrar un simpatizante — P2

**Como** persona con cargo
**quiero** dejar anotado a alguien que visitó la iglesia
**para** que no se pierda de vista mientras aviso al comité encargado.

- **Dado** que invité a alguien y vino al culto,
  **cuando** registro su nombre, edad, género, teléfono, barrio y cómo llegó,
  **entonces** queda registrado, mi nombre queda como quien lo invitó, y el seguimiento pasa al
  Comité de Evangelismo.
- **Dado** que registré a una persona,
  **cuando** vuelvo a consultar,
  **entonces** no veo su ficha de seguimiento, porque esa información es del Comité de
  Evangelismo; el sistema me lo explica sin que parezca un error.
- **Dado** que registro a alguien que ya existe,
  **cuando** guardo,
  **entonces** el sistema me advierte que hay una persona con ese nombre y teléfono y me permite
  continuar o cancelar.

---

### HU-14 · Acompañar a los simpatizantes — P2

**Como** presidente del Comité de Evangelismo
**quiero** saber cómo va cada persona que está visitando la iglesia
**para** darme cuenta a tiempo de quién se está alejando.

- **Dado** que abro la sección de simpatizantes,
  **cuando** la pantalla carga,
  **entonces** veo cuántas personas acompañamos, cuántas están creciendo, cuántas estables y
  cuántas se están alejando.
- **Dado** que quiero ver el camino de una persona,
  **cuando** abro su ficha,
  **entonces** veo en qué etapa va, con la fecha de cada una: visitó el culto, asiste con
  frecuencia, recibe estudio bíblico, se bautizó en el nombre de Jesucristo, recibió el Espíritu
  Santo.
- **Dado** que el sistema califica el compromiso de una persona,
  **cuando** consulto cómo lo determinó,
  **entonces** el sistema lo calcula con su asistencia a los cultos recientes y los días
  transcurridos desde la última visita, y nadie lo escribe a mano.
- **Dado** que visitamos a una persona,
  **cuando** registro la visita con su fecha, tipo y una nota,
  **entonces** esa visita queda en su historia y el indicador de compromiso se recalcula.
- **Dado** que una persona recorrió todas las etapas,
  **cuando** propongo su paso a miembro,
  **entonces** la solicitud le llega al pastor, y solo cuando él la apruebe la persona entra a la
  lista de miembros.

---

### HU-15 · Registrar asistencia — P2

**Como** líder de sub-comité o presidente de comité
**quiero** anotar quién asistió a cada ensayo o reunión
**para** saber a quién hay que buscar.

- **Dado** que tuvimos un ensayo,
  **cuando** registro la fecha, el tipo, el tema y marco quiénes asistieron,
  **entonces** queda registrado y el porcentaje de asistencia de cada persona se actualiza.
- **Dado** que me equivoqué al marcar,
  **cuando** abro la reunión y corrijo,
  **entonces** el sistema guarda la corrección y conserva qué se había registrado antes.
- **Dado** que reviso la asistencia por persona,
  **cuando** abro la lista,
  **entonces** veo a quién fue convocado, cuántas veces asistió y su porcentaje, con una señal
  clara —y en tono de cuidado, no de reproche— en quienes están por debajo del sesenta por ciento.

---

### HU-16 · Manejar el presupuesto del comité — P2

**Como** presidente de comité
**quiero** ver cuánto me asignaron, cuánto llevo gastado y cuánto me queda
**para** no comprometer plata que no tengo.

- **Dado** que el pastor asignó el presupuesto anual de mi comité,
  **cuando** abro la sección,
  **entonces** veo asignado, gastado, disponible y el porcentaje usado, y el reparto entre mis
  sub-comités.
- **Dado** que un gasto deja mi saldo en negativo,
  **cuando** lo apruebo,
  **entonces** el sistema me advierte expresamente que me estoy pasando del presupuesto, y me
  permite continuar si lo confirmo.
- **Dado** que soy líder de sub-comité,
  **cuando** necesito autorización para un gasto,
  **entonces** la solicito indicando concepto y monto, y le llega a mi presidente.
- **Dado** que soy el pastor,
  **cuando** abro la sección,
  **entonces** veo los seis comités con su asignado, gastado y disponible, y el total de la
  congregación, destacando el comité que ya superó el noventa por ciento de lo asignado.
- **Dado** que cualquiera abre esta sección,
  **cuando** la pantalla carga,
  **entonces** encuentra visible la aclaración de que la tesorería sigue manejando los recursos y
  que aquí solo se registra en qué se usaron y quién los autorizó.

---

### HU-17 · Saber qué tengo pendiente — P2

**Como** persona con cargo
**quiero** darme cuenta apenas entro de lo que me están esperando
**para** no demorar las decisiones de mi comité.

- **Dado** que entro al sistema,
  **cuando** carga mi pantalla de inicio,
  **entonces** lo primero que veo es qué está esperando mi decisión y desde cuándo.
- **Dado** que tengo solicitudes sin resolver,
  **cuando** miro el menú,
  **entonces** veo un contador con cuántas son.
- **Dado** que soy líder y envié una solicitud,
  **cuando** entro,
  **entonces** veo en qué va, quién debe resolverla y, si ya fue resuelta, el resultado con su
  motivo.

---

### HU-18 · Cargar la lista inicial de miembros — P3

**Como** pastor
**quiero** subir de una vez la lista de los miembros de la congregación
**para** no digitar ciento cuarenta fichas a mano.

- **Dado** que tengo la lista en una plantilla,
  **cuando** la subo,
  **entonces** el sistema me muestra cuántas filas están correctas y cuáles tienen error, antes de
  guardar nada.
- **Dado** que algunas filas tienen error,
  **cuando** reviso,
  **entonces** el sistema me indica la fila y qué está mal, y me permite cargar solo las correctas.
- **Dado** que una persona de la lista ya está registrada,
  **cuando** la importo,
  **entonces** el sistema me advierte y me deja decidir si la actualizo o la omito.

---

### HU-19 · Corregir un dato equivocado — P3

**Como** persona con cargo
**quiero** corregir lo que registré mal
**para** que la información sea confiable.

- **Dado** que registré mal un monto o una fecha,
  **cuando** lo corrijo,
  **entonces** el sistema guarda el cambio y conserva qué decía antes, quién lo cambió y cuándo.
- **Dado** que consulto el historial de un registro,
  **cuando** lo abro,
  **entonces** veo la secuencia completa de cambios.
- **Dado** que se trata de una aprobación ya resuelta,
  **cuando** intento modificarla,
  **entonces** el sistema no la deja editar: debe registrarse una nueva decisión, porque las
  aprobaciones no se borran.

---

### HU-20 · Usar el sistema desde el celular — P1

**Como** hermano que atiende su comité desde el teléfono
**quiero** que todo funcione bien en una pantalla pequeña
**para** no depender de un computador.

- **Dado** que entro desde un celular,
  **cuando** abro cualquier pantalla,
  **entonces** la uso completa sin necesidad de desplazarme hacia los lados.
- **Dado** que una pantalla muestra una tabla con muchas columnas,
  **cuando** la abro en el celular,
  **entonces** la información se presenta en tarjetas legibles, no en una tabla cortada.
- **Dado** que uso el sistema con frecuencia,
  **cuando** lo deseo,
  **entonces** puedo dejarlo instalado en la pantalla de inicio de mi teléfono y abrirlo como una
  aplicación.

---

## 6. Requisitos funcionales

### Acceso y cuentas
- **RF-001.** El sistema permite entrar con un usuario asignado y una clave.
- **RF-002.** El sistema obliga a cambiar la clave en el primer ingreso.
- **RF-003.** Solo el pastor crea, modifica y retira cuentas, y asigna cargo y comité.
- **RF-004.** El sistema bloquea el acceso por quince minutos tras cinco intentos fallidos.
- **RF-005.** Solo el pastor restablece claves.
- **RF-006.** Al retirar un acceso, la persona deja de entrar de inmediato y su nombre se conserva
  en todo registro histórico.

### Alcance de la información
- **RF-007.** Toda consulta devuelve únicamente la información del ámbito del cargo activo.
- **RF-008.** El intento de acceder a información fuera del ámbito produce una respuesta que
  explica la situación sin revelar ningún dato.
- **RF-009.** El sistema informa a cada cargo cuánta información de la congregación queda fuera de
  su alcance, sin detallarla.
- **RF-010.** El cronograma es la única excepción: toda persona con cargo consulta título,
  descripción, fecha, hora, lugar y comité responsable de cualquier actividad. El presupuesto y
  las tareas internas permanecen reservados a su comité.

### Comités
- **RF-011.** El sistema representa comités con cero o más sub-comités.
- **RF-012.** Un comité sin sub-comités se identifica expresamente como tal.
- **RF-013.** Cada comité y sub-comité tiene un único responsable vigente.
- **RF-014.** Un miembro pertenece a un comité y, opcionalmente, a un sub-comité de ese comité.

### Aprobaciones
- **RF-015.** Son objeto de aprobación: actividad nueva, gasto, tarea y cambio de fecha.
- **RF-016.** Lo propuesto por un líder llega al presidente de su comité.
- **RF-017.** Lo propuesto por un presidente llega al pastor.
- **RF-018.** Una actividad nueva aprobada por el presidente escala al pastor; gasto, tarea y
  cambio de fecha quedan resueltos por el presidente.
- **RF-019.** El paso de un simpatizante a miembro lo propone el Comité de Evangelismo y lo
  aprueba el pastor.
- **RF-020.** Todo rechazo exige un motivo escrito, visible para quien propuso.
- **RF-021.** Toda decisión registra quién, qué, cuándo y por qué, y no se borra.
- **RF-022.** El pastor puede resolver varias solicitudes del mismo tipo en una sola acción.
- **RF-023.** Las solicitudes pendientes de un cargo vacante pasan a su reemplazo, con registro
  del traspaso.

### Cronograma
- **RF-024.** El sistema distingue las actividades del plan anual de las propuestas durante el año.
- **RF-025.** El plan anual se aprueba en un solo acto y queda identificado con quién lo aprobó y
  cuándo.
- **RF-026.** Cada actividad tiene uno de estos estados: esperando aprobación, aprobada, en curso,
  cumplida, no aprobada.
- **RF-027.** El avance de una actividad se calcula a partir de sus tareas cumplidas.
- **RF-028.** Al proponer una actividad, el sistema advierte si ya existe otra en esa fecha,
  indicando cuál y de qué comité.
- **RF-029.** El cronograma se consulta por año completo o como lista, y se filtra por comité,
  sub-comité, mes y estado.
- **RF-030.** Los totales y el porcentaje de cumplimiento se recalculan según el filtro aplicado.

### Miembros y reportes
- **RF-031.** El registro de un miembro incluye nombre, edad, género, estado civil, si está
  bautizado, fecha de bautismo, fecha de ingreso, comité, sub-comité, teléfono y barrio.
- **RF-032.** El sistema impide estados civiles imposibles para menores de dieciocho años.
- **RF-033.** El sistema exige fecha de bautismo cuando se marca a alguien como bautizado.
- **RF-034.** La búsqueda de miembros ignora mayúsculas y tildes.
- **RF-035.** Los filtros de comité, sub-comité, género, estado civil, bautizado y rango de edad
  son combinables, y el sistema informa cuántos resultados hay sobre el total.
- **RF-036.** Los reportes presentan: total, bautizados, estado civil, género, rangos de edad,
  distribución por comité, bautismos por año y miembros sin comité.
- **RF-037.** Todo reporte gráfico tiene su equivalente en tabla.
- **RF-038.** Todo reporte se descarga como hoja de cálculo y se imprime, respetando los filtros
  aplicados y el ámbito del cargo.
- **RF-039.** El sistema permite importar miembros desde una plantilla, validando antes de guardar
  e informando fila por fila los errores.

### Simpatizantes
- **RF-040.** Cualquier cargo registra un simpatizante; el seguimiento lo realiza únicamente el
  Comité de Evangelismo.
- **RF-041.** El registro incluye nombre, edad, género, teléfono, barrio, cómo llegó, quién lo
  invitó y la fecha del primer contacto.
- **RF-042.** El camino consta de cinco etapas en este orden: visitó el culto; asiste con
  frecuencia; recibe estudio bíblico; se bautizó en el nombre de Jesucristo; recibió el Espíritu
  Santo. Cada etapa alcanzada conserva su fecha.
- **RF-043.** El sistema calcula el nivel de compromiso a partir de la asistencia reciente y los
  días transcurridos desde la última visita. Nadie lo escribe a mano.
- **RF-044.** Cada visita, llamada o estudio se registra con fecha, tipo, responsable y una nota.
- **RF-045.** Al aprobarse el paso a miembro, la persona entra a la lista de miembros conservando
  su fecha de bautismo y su fecha de primer contacto.

### Asistencia
- **RF-046.** Cada reunión registra fecha, tipo, tema, convocados y asistentes.
- **RF-047.** Los convocados solo pueden ser miembros del comité o sub-comité correspondiente.
- **RF-048.** El porcentaje de asistencia por persona se calcula sobre las reuniones a las que fue
  convocada.
- **RF-049.** El sistema señala a quienes están por debajo del sesenta por ciento con un texto de
  acompañamiento, nunca de reproche.

### Presupuesto
- **RF-050.** El pastor asigna el presupuesto anual de cada comité; el presidente lo reparte entre
  sus sub-comités.
- **RF-051.** El sistema advierte cuando un gasto supera el saldo disponible, y permite continuar
  si se confirma.
- **RF-052.** Cada movimiento registra fecha, concepto, monto, soporte y quién lo autorizó.
- **RF-053.** Toda pantalla de presupuesto muestra la aclaración de que la tesorería sigue
  manejando los recursos.

### Pendientes y correcciones
- **RF-054.** La pantalla de inicio de cada cargo muestra primero lo que espera su decisión y
  desde cuándo.
- **RF-055.** El menú indica con un contador cuántas solicitudes están pendientes para ese cargo.
- **RF-056.** Los registros corregibles conservan su valor anterior, quién lo cambió y cuándo.
- **RF-057.** Las aprobaciones ya resueltas no se editan: se registra una nueva decisión.

---

## 7. Reglas de negocio

- **RN-01.** El pastor ve toda la congregación porque responde por ella.
- **RN-02.** Un presidente responde por lo que hacen sus sub-comités.
- **RN-03.** Lo que venía en el plan anual no requiere nueva aprobación; lo que se sale de él, sí.
- **RN-04.** El monto de un gasto no determina quién lo aprueba: el presidente autoriza los gastos
  de su comité, cualquiera sea su valor.
- **RN-05.** El presupuesto es una guía, no un tope rígido: se puede exceder, pero el sistema lo
  advierte y queda registrado.
- **RN-06.** Nadie pasa a ser miembro sin aprobación del pastor.
- **RN-07.** Ninguna aprobación, rechazo o movimiento registrado se elimina.
- **RN-08.** El lenguaje de todo el sistema es el de la congregación, no el de la informática.
- **RN-09.** Quien deja un cargo pierde el acceso el mismo día, y su nombre permanece en la
  historia de lo que decidió.

---

## 8. Casos límite

- **CL-01.** Un comité sin sub-comités: la ficha debe explicarlo, no mostrar una sección vacía.
- **CL-02.** Un miembro que pertenece al comité pero a ningún sub-comité.
- **CL-03.** Un miembro que no pertenece a ningún comité: debe seguir contando en los reportes de
  la congregación y señalarse como oportunidad.
- **CL-04.** Un comité sin presupuesto asignado: las pantallas de presupuesto deben funcionar en
  cero, no fallar.
- **CL-05.** Una persona con dos cargos: debe ver ambos ámbitos, claramente separados.
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

---

## 9. Criterios de éxito medibles

Medibles a los seis meses de la puesta en funcionamiento.

**Uso y oportunidad**
- **CE-01.** Las diecisiete personas con cargo entran al sistema al menos una vez por semana.
- **CE-02.** El plan del año siguiente queda cargado y aprobado por el pastor antes del 31 de enero.
- **CE-03.** El noventa por ciento de las solicitudes se resuelven en menos de siete días
  calendario desde que se envían.
- **CE-04.** Cero actividades cruzadas en el año, entendiendo por cruce dos actividades en el mismo
  día, hora y lugar.

**Información completa y al día**
- **CE-05.** Los ciento cuarenta miembros quedan cargados durante el primer mes.
- **CE-06.** Ningún miembro queda sin comité asignado sin que el pastor lo tenga identificado.
- **CE-07.** El reporte de la congregación se obtiene en menos de un minuto desde que se solicita,
  en cualquier momento y sin ayuda técnica.
- **CE-08.** Se registra asistencia en al menos el ochenta por ciento de las reuniones y ensayos de
  comité.
