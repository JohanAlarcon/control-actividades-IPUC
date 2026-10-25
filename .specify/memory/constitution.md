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
solo el suyo.

- La restricción DEBE aplicarse siempre en el servidor. Ocultar elementos en la interfaz NO
  cuenta como restricción.
- Toda consulta a comités, miembros, actividades, asistencia, presupuesto y simpatizantes
  DEBE pasar por el filtro de alcance centralizado. Ninguna consulta puede construirse
  salteándolo, salvo la única excepción enumerada más abajo en este principio.
- Cada ruta y cada acción DEBE exigir autorización explícita mediante policy o gate.
- Toda funcionalidad nueva que lea o escriba datos de un comité DEBE incluir una prueba que
  demuestre que un rol de otro comité no puede verla ni modificarla.

Única excepción enumerada —el cronograma compartido—:

- El cronograma de actividades, y solo él, se comparte con toda la congregación: así lo
  aprobó la congregación en reunión. Que un dato interese a toda la congregación no lo
  convierte en compartido; lo compartido es lo que esta excepción enumera. Toda persona con
  un cargo activo escogido consulta cualquier actividad del año, sea de su comité o no.
- De una actividad ajena se comparten exactamente siete datos: título, descripción, fecha,
  hora, lugar, comité responsable y estado. El presupuesto y las tareas internas NUNCA salen
  del comité dueño: ni en el listado, ni en la ficha, ni en un reporte, ni en una descarga.
- El nombre del comité responsable SÍ se comparte por esta excepción. La reserva de RF-009 se
  refiere a los conteos de `scope.hidden`, que nunca listan cuáles son; no convierte el
  nombre de un comité en dato reservado.
- La excepción DEBE vivir en un único método con nombre propio —`forSharedCalendar()` en el
  modelo `Activity`—, que retira el filtro de comité y selecciona a mano esos siete datos. El
  filtro de sede (`site_id`) NO se retira nunca. Cualquier otra consulta que salga del filtro
  de alcance es un defecto, no una excepción.
- El método SOLO lee. NO se usa como base de un `update`, de un `delete` ni de una descarga,
  y NO concede autorización: la policy de `Activity` sigue negando toda escritura sobre una
  actividad ajena.
- La excepción DEBE tener una prueba automatizada de lista blanca: comprueba que la carga de
  una actividad ajena trae EXACTAMENTE los siete datos enumerados y ningún otro, comparando
  el juego de llaves y no buscando llaves prohibidas. Así, una columna nueva en `activities`
  pone la prueba roja hasta que alguien decida si se comparte. La prueba revisa la carga de
  la respuesta, no lo que se ve en pantalla, e incluye el caso de escritura: un presidente
  que intenta modificar una actividad ajena recibe una negativa.
- Esta es la ÚNICA excepción al filtro de alcance en todo el proyecto. Agregar otra exige
  enmendar esta constitución: no se autoriza en una especificación, en un plan ni en una
  tarea.

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

### Datos y seguridad

- La información de miembros y simpatizantes es propiedad del cliente. NO se usa para otros
  fines, NO se copia fuera del servidor acordado y NO aparece en registros de depuración.
- Contraseñas con el hash de Laravel, sesiones seguras y protección CSRF activa.
- Respaldo diario de la base de datos.
- Las semillas y los datos de prueba son siempre ficticios. Nunca datos reales de personas
  en el repositorio.

### Alcance y simplicidad

- Simple antes que completo. Lo que no está en el alcance contratado NO se construye: se
  registra como propuesta para cotizar.
- El alcance contratado es: la estructura de comités en tres niveles —comité, sub-comité y
  equipo—, administrable por el pastor; aprobaciones en dos niveles; cronograma anual con
  propuesta, aprobación, rechazo y seguimiento, que es también el plan de trabajo de cada
  comité; registro de miembros con reportes y estadísticas; seguimiento de simpatizantes por
  etapas; asistencia a reuniones y ensayos; actas de reunión con sus compromisos, numeradas
  e imprimibles; archivos que respaldan el cumplimiento de una actividad y los soportes de
  los movimientos de presupuesto; y presupuesto asignado y ejecutado por comité.
- Ninguna pantalla hace más de 5 consultas a la base de datos. Se previenen las consultas
  N+1.

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
  en limpio, y la pantalla se verificó a 360 px de ancho.
- Commits en español, en imperativo, describiendo el cambio funcional.

### Entrega

- El desarrollo se entrega en cuatro hitos semanales: (1) comités, sub-comités, usuarios y
  control de acceso; (2) cronograma anual y aprobaciones; (3) miembros, simpatizantes y
  reportes; (4) asistencia, presupuesto, pruebas y puesta en funcionamiento.
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
  por alcance, ni por petición del cliente. La única excepción que existe es la enumerada
  dentro del principio I —el cronograma compartido—, que entró por enmienda, nombra sus siete
  datos y exige su propia prueba. Ninguna otra se concede, y ningún plan ni especificación
  puede diluir ni reinterpretar estos principios en silencio.
- Guía de desarrollo en tiempo de ejecución: `CLAUDE.md` en la raíz del proyecto.

### Registro de enmiendas

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

**Versión**: 2.1.0 | **Ratificada**: 2026-10-14 | **Última enmienda**: 2026-10-24
