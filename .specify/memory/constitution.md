# Constitución de Control de Actividades — Sistema de Comités IPUC

Aplicación web para la gestión de comités, miembros y actividades de una congregación de la
Iglesia Pentecostal Unida de Colombia (IPUC). Reemplaza cuadernos y grupos de WhatsApp. La
usan personas de todas las edades, en su mayoría desde el celular, muchas con poca
experiencia usando aplicaciones. Esta constitución fija las reglas no negociables del
proyecto.

Idioma del proyecto:

- La documentación de Spec Kit —constitución, especificaciones, planes, tareas— se escribe
  en español.
- El código se escribe en inglés: tablas, columnas, modelos, clases, métodos, variables,
  rutas y componentes, siguiendo las convenciones de Laravel (`committees`, `members`,
  `activities`, `approvals`).
- Los textos que ve el usuario final van en español de Colombia, tratando de "usted".

## Principios Fundamentales

### I. La privacidad entre comités es ley (NO NEGOCIABLE)

Cada persona ve únicamente la información de su ámbito: el pastor ve toda la congregación;
el presidente de un comité ve su comité y sus sub-comités; el líder de un sub-comité ve
solo el suyo. Un miembro que pertenece a varios comités entra en el ámbito de cada uno de
ellos, y de ningún otro.

- La restricción DEBE aplicarse siempre en el servidor. Ocultar elementos en la interfaz NO
  cuenta como restricción.
- Toda consulta a comités, miembros, actividades, cronogramas, asistencia, presupuesto,
  simpatizantes y niños DEBE pasar por el filtro de alcance centralizado. Ninguna consulta
  puede construirse salteándolo, salvo las excepciones enumeradas más abajo en este principio.
- Cada ruta y cada acción DEBE exigir autorización explícita mediante policy o gate.
- Toda funcionalidad nueva que lea o escriba datos de un comité DEBE incluir una prueba que
  demuestre que un rol de otro comité no puede verla ni modificarla.

Excepciones enumeradas. Son cuatro, y ninguna más. Cada una vive en un único lugar con nombre
propio, solo lee, NO concede autorización para escribir, NUNCA retira el filtro de sede
(`site_id`) y tiene su propia prueba automatizada de lista blanca: compara el juego de llaves
de lo que devuelve con lo que aquí se enumera, de modo que un dato nuevo pone la prueba roja
hasta que alguien decida si se comparte.

**1. El cronograma general compartido.**

- El cronograma general del año, y solo él, se comparte con toda la congregación: así lo
  aprobó la congregación en reunión. Toda persona con un cargo activo escogido lo consulta
  completo en el calendario. Los cronogramas internos de cada comité NO se comparten.
- De una actividad ajena se comparten exactamente seis datos: título, fecha, hora, lugar,
  comité responsable y estado. La descripción, el presupuesto, las tareas internas y el
  soporte NUNCA salen del comité dueño: ni en el calendario, ni en la ficha, ni en un
  reporte, ni en una descarga. El detalle de una actividad lo ven su comité, sus ascendientes
  y el pastor.
- El nombre del comité responsable SÍ se comparte. Los nombres de los comités no son un dato
  reservado.
- Vive en `Activity::forSharedCalendar()`, que retira el filtro de comité y selecciona a mano
  esos seis datos. La prueba incluye el caso de escritura: un presidente que intenta modificar
  una actividad ajena recibe una negativa.

**2. BIS recibe a todos los simpatizantes.**

- El comité BIS da la bienvenida a quienes llegan por primera vez a la iglesia. Por eso su
  ámbito sobre los simpatizantes es toda la sede: los registra, los ve a todos, los acompaña
  y asigna a cada uno el único comité que lo acompaña también. Para asignarlo ve el nombre de
  todos los comités activos. El comité asignado ve únicamente a los suyos.
- BIS no ve por esto ningún otro dato de otros comités: ni sus miembros, ni sus actividades,
  ni su presupuesto.
- Vive en el filtro de alcance de los simpatizantes, no en una consulta suelta. La prueba
  comprueba que un cargo de BIS ve a todos los simpatizantes y nada más ajeno, y que un comité
  ve solo los que le asignaron.

**3. Lo que se pregunta sin traer datos.**

- Dos preguntas miran fuera del ámbito y responden solo con un número o con un sí o un no:
  cuántas personas de la congregación no aparecen en la lista de un cargo, y si la persona que
  se va a registrar ya existe. Nunca devuelven un nombre ni ningún otro dato.
- Cada una vive en una clase con nombre propio en `app/Support/`, nunca en un controlador. La
  prueba comprueba que la respuesta es un número o un sí o un no.

**4. El buscador de miembros ya registrados.**

- El padrón de miembros lo lleva el pastor. Para que un comité agregue a su lista a una
  persona ya registrada, el buscador le muestra de cada miembro de la sede únicamente su
  nombre y su edad. No abre la ficha ni deja modificarla.
- Vive en `Member::forPicker()`. La prueba comprueba que cada resultado trae exactamente el
  nombre y la edad.

Agregar otra excepción, o ampliar lo que una de estas deja ver, exige enmendar esta
constitución: no se autoriza en una especificación, en un plan ni en una tarea. Cualquier otra
consulta que salga del filtro de alcance es un defecto, no una excepción.

Razón: es la promesa que la congregación aprobó en la reunión; si falla una sola vez, se
pierde la confianza del cliente.

### II. Primero el celular

Toda pantalla DEBE diseñarse y probarse primero a 360 px de ancho, y solo después ampliarse.
Ninguna funcionalidad se considera terminada si solo funciona bien en escritorio.

Objetivos verificables:

- Cero desplazamiento horizontal a 360 px de ancho.
- Toda zona pulsable mide 48x48 px como mínimo.
- El texto de contenido mide 18 px como mínimo.
- El contraste entre texto y fondo cumple el nivel AA de las pautas de accesibilidad.
- Los colores salen de la paleta de la IPUC definida en `resources/css/app.css` y se usan por su
  nombre, nunca escritos a mano. La excepción es lo que no pasa por ese CSS: la barra de carga en
  `resources/js/app.tsx`, el manifiesto de la aplicación instalable y la página sin señal, que
  funciona sin conexión. Esos tres llevan una copia de los valores y una nota que remite a
  `app.css`. El azul claro y el dorado no llevan texto encima, porque no alcanzan el contraste AA.
- La aplicación se instala como PWA y abre su pantalla principal en menos de 3 segundos
  sobre una red 3G simulada.
- Las tablas anchas se convierten en tarjetas en pantallas angostas; no se desbordan.

### III. Toda decisión deja rastro

Toda aprobación, rechazo, cambio de estado o movimiento de presupuesto DEBE quedar
registrado con quién, qué, cuándo y por qué.

- Ese rastro es inmutable: NO se edita ni se borra. Una corrección se hace agregando un
  asiento nuevo que explique la corrección.
- Nada que alguien haya aprobado puede desaparecer de la historia.
- La cadena de aprobación —el sub-comité propone, el presidente revisa, el pastor aprueba lo
  nuevo o lo que se sale del plan del año— DEBE vivir en los datos, no solamente en la
  interfaz.

### IV. El lenguaje es el de la congregación

En la interfaz NO aparece jerga técnica: nada de endpoint, query, dashboard, tenant, login,
token, ni "registro" en el sentido de base de datos. Se dice culto, comité, hermano,
hermana, bautismo, simpatizante, cronograma, bandeja de aprobaciones, entrar con su clave.

- Los mensajes de error explican en una frase qué pasó y qué hacer.
- Se trata a la persona de "usted".
- El vocabulario de la IPUC es obligatorio: bautismo en el nombre de Jesucristo, Escuela
  Dominical, REFAM, Damas Dorcas, vigilia, convención, Santa Cena.
- Queda prohibido el vocabulario católico (misa, sacerdote, párroco) y la fórmula bautismal
  trinitaria.

Prueba de aceptación: si una hermana de 70 años no entiende la pantalla sin que nadie se la
explique, la pantalla está mal hecha.

### V. Ninguna tarea se cierra sin pruebas (NO NEGOCIABLE)

No se exige TDD estricto: las pruebas pueden escribirse después de implementar. Pero ninguna
tarea se marca como terminada sin pruebas automatizadas que la cubran y que pasen.

- Cobertura obligatoria en: el filtro de alcance por rol, el flujo completo de aprobaciones,
  los cálculos de estadísticas y de presupuesto, y las reglas de validación de los
  formularios.
- Se usa Pest con factories sobre la base de datos de pruebas.
- Las pruebas corren antes de cada mezcla a `main`.

## Restricciones Técnicas

### Stack obligatorio

Versiones exactas, que NO se actualizan sin decisión explícita del autor del proyecto:

PHP 8.4 · Laravel 12 · MySQL 8 · Inertia.js 2 · React 19 · Tailwind CSS 4 · Vite · Pest.

Entorno local Laragon; el proyecto vive en `C:\laragon\www\control-actividades`.

### Arquitectura

- Laravel + Inertia + React. Blade solo para el documento raíz. En este proyecto NO se usa
  Livewire ni Filament.
- Multi-sede desde el primer día: toda tabla con datos de la congregación lleva su columna
  de sede (`site_id`) con llave foránea, y un global scope la aplica automáticamente.
  Ninguna consulta puede omitirlo. La licencia vigente cubre una sola sede, pero el modelo
  nace preparado para varias.
- La autorización vive en policies y gates de Laravel. El frontend nunca decide permisos:
  solo refleja los que el servidor ya resolvió.
- Antes de agregar cualquier dependencia se evalúa si Laravel o React ya lo resuelven.
  Agregar un paquete exige justificación escrita.
- Las migraciones son la única fuente de verdad del esquema. La base de datos NO se modifica
  a mano.
- Zona horaria America/Bogota, moneda COP, fechas en formato colombiano en la interfaz.

### Código legible por un desarrollador junior

La regla de oro: **la solución simple gana**. Un desarrollador junior debe poder entender y
mantener cualquier archivo de este proyecto sin que nadie se lo explique.

- Sin patrones de diseño que el problema no pida. Nada de sobreingeniería: ni capas de
  abstracción «por si acaso», ni lógica difícil de seguir.
- **Pocos comentarios, y solo donde hagan falta.** El código se explica por sus nombres. Un
  comentario se justifica cuando el *por qué* no es evidente, nunca para narrar el *qué*.
- Límites de tamaño en los componentes de React, medidos en `resources/js`:
  - **250 líneas por archivo de componente.** Al pasarlas, se divide. **300 es el tope
    infranqueable**: ningún componente nuevo lo supera.
  - **70 líneas por función.** Una función más larga casi siempre hace dos cosas.
  - **Un archivo, un componente exportado.** Los auxiliares que solo usa ese archivo pueden
    vivir junto a él mientras no se pase del límite.
  - El estado vive en el **nivel más bajo posible**: si solo lo usa un componente, no sube a
    la página.
  - Al dividir: cada sección visual a su propio componente en `resources/js/components/`; el
    estado y las llamadas al servidor a un hook en `resources/js/hooks/`; y el archivo de
    `resources/js/pages/` se queda solo componiendo.
  - **Un archivo que se toca y está por encima del límite, se divide.** No hay que arreglar
    todo el proyecto de una vez, pero lo que se modifica no se deja igual.
- El diseño de la interfaz es minimalista: la claridad para quien la usa está por encima de
  cualquier otra consideración (principios II y IV).

### Datos y seguridad

- La información de miembros, simpatizantes y niños es propiedad del cliente. NO se usa para
  otros fines, NO se copia fuera del servidor acordado y NO aparece en registros de depuración.
- Los datos de los niños de la Escuela Dominical son los más protegidos del sistema: sus
  datos de salud solo los ven la Escuela Dominical y el pastor, y ninguna foto de un niño se
  sube sin la autorización de imagen firmada por su padre, madre o acudiente.
- La dirección de residencia de los miembros NO se guarda.
- Contraseñas con el hash de Laravel, sesiones seguras y protección CSRF activa.
- Respaldo diario de la base de datos.
- Las semillas y los datos de prueba son siempre ficticios. Nunca datos reales de personas
  en el repositorio.

### Alcance y simplicidad

- Simple antes que completo. Lo que no está en el alcance contratado NO se construye: se
  registra como propuesta para cotizar.
- El alcance contratado es el levantamiento de información de la sede La Esmeralda del
  2026-10-26, completo:
  - la estructura de comités en **dos niveles** —comité y sub-comité—, administrable por el
    pastor, con la identidad de cada comité: logo, lema y objetivos;
  - el nombre de la sede y su lema del año, en la entrada y en toda la plataforma;
  - aprobaciones en dos niveles;
  - el cronograma general del año, con propuesta, aprobación, rechazo y seguimiento, sin
    actividades que se crucen, compartido en un calendario; y los cronogramas internos de
    cada comité, varios por comité y privados;
  - el orden de culto de cada comité;
  - el padrón de miembros que lleva el pastor, con la pertenencia de cada persona a uno o
    varios comités, sus cumpleaños, reportes y estadísticas;
  - el registro y el seguimiento de los simpatizantes por etapas, con BIS en la entrada;
  - la asistencia a las actividades internas y a las del cronograma general;
  - actas de reunión con sus compromisos, numeradas e imprimibles;
  - un soporte por actividad y los soportes de los movimientos de presupuesto;
  - presupuesto asignado y ejecutado por comité;
  - imprimir las actividades y compartirlas por WhatsApp;
  - los módulos propios de la Escuela Dominical —grupos de niños, inscripción, autorización
    firmada de los padres, cronograma de clases, visitas y estadísticas—, de Obra Social
    —ayudas entregadas y fondo propio— y de Alabanza —quién toca y quién canta—.
- Ninguna pantalla hace más de 5 consultas propias a la base de datos, y su número no crece
  con la cantidad de datos: se previenen las consultas N+1. No cuentan las tres fijas de
  Laravel en cada petición —leer la sesión, cargar a la persona y guardar la sesión—, que
  ninguna pantalla puede agregar ni quitar y se miden aparte.

## Flujo de Trabajo y Puertas de Calidad

### Flujo Spec Kit

- Toda funcionalidad empieza por `/speckit-specify`. NO se escribe código sin especificación
  aprobada.
- Secuencia obligatoria: specify → clarify → plan → tasks → implement. Si una especificación
  tiene ambigüedades, se resuelven en clarify antes de planear.
- La constitución manda sobre cualquier especificación o plan. Si un plan la contradice, se
  corrige el plan, no la constitución.

### Ramas y aprobación

- Trabajo por rama de funcionalidad. La IA propone y la persona aprueba: ningún cambio llega
  a `main` sin revisión y aprobación explícita del autor del proyecto.
- Antes de mezclar a `main`: las pruebas pasan, las migraciones corren sobre base de datos
  en limpio, la pantalla se verificó a 360 px de ancho, y **ningún componente de React pasa
  de 300 líneas**. Se comprueba con:
  `find resources/js -name "*.tsx" | xargs wc -l | sort -rn | head`
- Commits en español, en imperativo, describiendo el cambio funcional. **Breves**: una línea
  de asunto que diga qué cambió, y cuerpo solo cuando el *por qué* no quepa en el asunto.
- Toda explicación de un cambio se da **en lenguaje claro**, sin tecnicismos innecesarios, y
  cuando haga falta un término técnico se explica en la misma frase. La entiende alguien sin
  conocimientos avanzados.

### Entrega

- El desarrollo se entrega en siete hitos: (1) comités en dos niveles, identidad de la sede y
  de cada comité, usuarios y control de acceso; (2) cronograma general, aprobaciones y
  cronogramas internos; (3) miembros, cumpleaños, simpatizantes con BIS y reportes;
  (4) asistencia, orden de culto, actas, soportes, presupuesto, imprimir y compartir;
  (5) Escuela Dominical; (6) Obra Social y Alabanza; (7) pruebas, carga inicial y puesta en
  funcionamiento. Las fechas de cada hito las acuerda el autor del proyecto con la
  congregación: la ampliación del 2026-10-28 corrió las que había.
- Cada hito termina funcionando de punta a punta en el celular, no a medias.

## Gobernanza

- Esta constitución está por encima de cualquier otra práctica, especificación o plan del
  proyecto.
- Toda enmienda se documenta: qué cambia, por qué, y qué impacto tiene sobre las
  especificaciones ya escritas.
- Versionado semántico de la constitución: MAJOR si se elimina o redefine un principio;
  MINOR si se agrega un principio o una sección; PATCH para aclaraciones de redacción.
- En cada revisión y antes de cada mezcla a `main` se verifica el cumplimiento de los cinco
  principios. Toda complejidad añadida debe justificarse por escrito.
- Los dos principios marcados NO NEGOCIABLE —I y V— no admiten excepción: ni por plazo, ni
  por alcance, ni por petición del cliente. Las únicas excepciones que existen son las cuatro
  enumeradas dentro del principio I —el cronograma general compartido, BIS sobre los
  simpatizantes, lo que se pregunta sin traer datos y el buscador de miembros—, que entraron
  por enmienda, nombran lo que dejan ver y exigen su propia prueba. Ninguna otra se concede, y
  ningún plan ni especificación puede diluir ni reinterpretar estos principios en silencio.
- Guía de desarrollo en tiempo de ejecución: `CLAUDE.md` en la raíz del proyecto.

### Registro de enmiendas

**3.0.0 · 2026-10-28 · El levantamiento de la sede La Esmeralda**

- Qué cambia:
  - **Alcance:** «Alcance y simplicidad» reescribe el alcance contratado con el levantamiento
    de información del 2026-10-26, completo.
  - **Estructura:** baja de tres niveles a dos; el equipo desaparece.
  - **Principio I, ámbito:** un miembro que pertenece a varios comités entra en el ámbito de
    cada uno, y los niños entran en la lista de lo que el filtro protege.
  - **Principio I, excepciones:** pasa de una a cuatro. El cronograma compartido deja de
    compartir la descripción: de siete datos baja a seis. Se agregan BIS sobre los
    simpatizantes, lo que se pregunta sin traer datos y el buscador de miembros.
  - **Datos y seguridad:** protege los datos de los niños y prohíbe guardar la dirección de
    los miembros.
  - **Entrega:** pasa de cuatro hitos semanales a siete, con fechas por acordar.
- Por qué: el autor del proyecto hizo con la congregación un levantamiento de información
  detallado —el nombre de la sede, sus quince comités, cómo trabaja cada uno y lo que
  necesitan la Escuela Dominical, Obra Social, Alabanza y BIS— y decidió el 2026-10-28 que
  todo entra en el proyecto actual, aunque las fechas se corran. La congregación pidió que un
  comité vea el calendario de los demás pero no el detalle, que BIS reciba a los
  simpatizantes en la entrada y los reparta, y que el padrón lo lleve el pastor mientras cada
  comité escoge a los suyos. Las cuatro excepciones no se pueden construir sin salir del
  filtro, y esta Gobernanza prohíbe hacerlo en silencio. Dos de ellas ya existían en el
  código sin estar autorizadas (D-29 del plan): esta enmienda las sanciona o las corrige.
- Salto de versión: MAJOR. Redefine el principio I —el ámbito y sus excepciones— y la garantía
  de la Gobernanza que las enumera, y reescribe el alcance contratado.
- Impacto sobre las especificaciones ya escritas:
  - **spec.md:** se reorienta. Cambian la estructura (RF-011, HU-03, HU-21), el cronograma
    compartido (RF-010, HU-09), los miembros (RF-014, RF-031, HU-10), los simpatizantes
    (RF-040, HU-13, HU-14), la asistencia, las actas y los soportes. Entran trece historias
    nuevas, de HU-25 a HU-37.
  - **Código ya construido:** se rehace el filtro de los miembros para la pertenencia a
    varios comités, `forSharedCalendar()` y su prueba de lista blanca para seis datos, el
    seguimiento de simpatizantes para BIS, y las semillas para la estructura real de dos
    niveles.
  - **plan.md, data-model.md, contracts/ y tasks.md:** recogen todo lo anterior.
- Lo que NO cambia: los principios I y V siguen sin admitir excepción por plazo, por alcance
  ni por petición del cliente. Ninguna de las cuatro excepciones deja ver presupuestos, actas,
  asistencia ni datos personales de otro comité.

**2.3.2 · 2026-10-26 · Dos excepciones más a los colores escritos a mano**

- Qué cambia: la regla de la paleta nombraba una sola excepción, la barra de carga. Se suman el
  manifiesto de la aplicación instalable, que por su formato lleva los colores escritos, y la
  página sin señal, que se muestra sin conexión y no recibe el CSS compilado.
- Por qué: la aplicación instalable (T046). Sin esas copias la página sin señal saldría sin los
  colores de la iglesia, y el manifiesto no admite otra forma de escribirlos.
- Salto de versión: PATCH. Precisa el alcance de una regla existente; la regla no cambia.

**2.3.1 · 2026-10-24 · Qué cuenta en el presupuesto de consultas**

- Qué cambia: se precisa la restricción de las cinco consultas. Cuentan las de la pantalla —lo
  que ejecutan el filtro de alcance, las propiedades compartidas, las policies y el
  controlador—; no cuentan las tres fijas de Laravel en cada petición, que se miden aparte. Y se
  hace explícito que el número no crece con los datos.
- Por qué: la prueba que vigilaba esta restricción medía sin sesiones en la base y con la
  persona ya cargada, así que no veía esas tres consultas. Medido como en el servidor, el inicio
  de un presidente daba 6 sin mostrar todavía nada: leída como «la petición completa», la regla
  no la podía cumplir ninguna pantalla de un presidente. Lo que protege es que una pantalla no se
  vuelva más lenta a medida que la congregación crece, y esas tres consultas no crecen nunca. Lo
  decidió el autor del proyecto.
- Salto de versión: PATCH. Precisa cómo se mide una restricción existente; no agrega ni quita
  ninguna.

**2.3.0 · 2026-10-24 · La paleta de colores es la de la IPUC**

- Qué cambia: el principio II agrega un objetivo verificable. Los colores de la interfaz son los
  de la IPUC —azul `#00338d`, azul claro `#009fda`, dorado `#f0ab00`— definidos una sola vez en
  `resources/css/app.css` y usados por su nombre. El azul claro y el dorado no pueden llevar
  texto encima.
- Por qué: la paleta anterior la escogió quien construía, no la iglesia, y no estaba anotada en
  ninguna parte, así que cada pantalla podía inventar la suya. Lo segundo era el problema real.
  El límite del azul claro y el dorado no es estético: medidos contra fondo blanco dan 3:1 y 2:1,
  por debajo del 4,5:1 que ya exigía este principio, y la congregación es mayor.
- Salto de versión: MINOR. Se agrega un objetivo verificable; no se elimina ni se redefine
  ninguno de los existentes.

**2.2.0 · 2026-10-24 · Código legible por un desarrollador junior**

- Qué cambia: se agrega a «Restricciones Técnicas» la sub-sección «Código legible por un
  desarrollador junior», con los límites de tamaño de los componentes de React (250 líneas por
  archivo, 300 como tope infranqueable, 70 por función, un componente exportado por archivo),
  la regla de pocos comentarios, el estado en el nivel más bajo posible y cómo dividir un
  archivo que se pasa. Y en «Ramas y aprobación» se agrega el tamaño de los componentes a las
  puertas previas a mezclar, los commits pasan a ser breves, y toda explicación de un cambio
  se da en lenguaje claro.
- Por qué: lo pidió el autor del proyecto. El sistema lo va a mantener alguien que no
  participó en construirlo, y un archivo de mil líneas o un patrón innecesario lo vuelven
  inmantenible. Es el mismo criterio del principio IV —claridad por encima de todo—, aplicado
  al código en vez de a la interfaz.
- Salto de versión: MINOR. Se agrega una sub-sección normativa; no se elimina ni se redefine
  ningún principio.
- Impacto sobre las especificaciones ya escritas: ninguna cambia de contenido. Se agrega una
  puerta de calidad, que `plan.md` y `tasks.md` recogen, y `CLAUDE.md` pasa a existir como la
  guía en tiempo de ejecución que esta Gobernanza ya designaba.
- Nota de adaptación: las reglas llegaron escritas para `Pages/`, `Components/` y archivos
  `.jsx`. Este proyecto usa `resources/js/pages/`, `resources/js/components/` y **TypeScript**
  (`.tsx`), así que se escribieron con las rutas y la extensión reales; el comando de
  verificación busca `*.tsx`.

**2.1.0 · 2026-10-24 · Mínimos para adultos mayores y ampliación del alcance contratado**

- Qué cambia: el principio II sube sus mínimos verificables —zona pulsable de 48x48 px y texto
  de contenido de 18 px, antes 44x44 y 16— y agrega la exigencia de contraste AA. La sección
  «Alcance y simplicidad» reescribe la lista del alcance contratado: la estructura de comités
  pasa a tres niveles administrables por el pastor, y se incorporan las actas de reunión con
  sus compromisos y los archivos que respaldan actividades y movimientos de presupuesto.
- Por qué: quienes usan el sistema son en su mayoría adultos mayores con poca experiencia en
  aplicaciones, y los mínimos anteriores, aunque cumplían las pautas, se quedaban cortos para
  ese público. La ampliación del alcance la decidió el autor del proyecto el 2026-10-24, con
  la congregación pidiendo actas digitalizadas, evidencias de cumplimiento y soportes de
  factura.
- Salto de versión: MINOR. Se amplía materialmente la guía de un principio y la lista de
  alcance; no se elimina ni se redefine ningún principio.
- Impacto sobre las especificaciones ya escritas: alcanza a los cinco artefactos. RF-058
  sube sus medidas; el alcance de spec.md incorpora los cuatro módulos nuevos; el filtro de
  alcance pasa de dos niveles a tres, lo que obliga a reescribir `CurrentScope`, el rasgo de
  comité, las policies y **todas** las pruebas de aislamiento del principio I; y los cargos
  pasan de dos a cuatro, con secretario y tesorero. Las pantallas ya construidas se revisan
  contra los mínimos nuevos.
- Lo que NO cambia: los dos principios NO NEGOCIABLES siguen sin admitir excepción, y la
  única excepción enumerada al filtro de alcance sigue siendo la del cronograma compartido.
  Las evidencias y los soportes **no** viajan por ella.

**2.0.0 · 2026-10-18 · Excepción enumerada del cronograma en el principio I**

- Qué cambia: el principio I prohibía en términos absolutos toda salida del filtro de
  alcance; ahora admite una única excepción enumerada —el cronograma de actividades— con sus
  siete datos compartidos, su método de solo lectura con nombre propio y su prueba
  obligatoria de lista blanca. La viñeta de Gobernanza sobre los principios NO NEGOCIABLE se
  ajusta para remitir a esa excepción. Nada más del principio I se debilita.
- Por qué: RF-010 obliga a que cualquier cargo consulte título, descripción, fecha, hora,
  lugar, comité responsable y estado de cualquier actividad, porque el cronograma es
  información compartida de la congregación y así lo aprobó en reunión. Sin esta enmienda,
  RF-010 no se puede construir sin contradecir un principio no negociable, y esta Gobernanza
  prohíbe diluir o reinterpretar un principio en silencio.
- Salto de versión: MAJOR. La enmienda redefine la regla del principio I —"ninguna consulta
  puede construirse salteándolo" pasa a admitir una excepción— y reescribe una garantía de la
  Gobernanza, y el versionado de este archivo reserva MAJOR para redefinir un principio.
- Impacto sobre las especificaciones ya escritas: tres cambian, y ninguno amplía la excepción.
  spec.md RF-010 agrega «estado» como séptimo dato compartido, para que el requisito pida lo
  mismo que esta constitución autoriza y no menos. tasks.md T079 pasa de lista negra a lista
  blanca y suma el caso de escritura. contracts/pages.md deja de reservar el nombre de un
  comité ajeno, que el cronograma ya publica, y reserva sus miembros, su presupuesto y su
  avance. research.md R-11 —con su decisión D-15— y tasks.md T078 quedan sancionados sin
  cambio de fondo. Toda la demás cobertura del principio I sigue igual, y ninguna otra
  salida del filtro queda autorizada.

**Versión**: 3.0.0 | **Ratificada**: 2026-10-14 | **Última enmienda**: 2026-10-28
