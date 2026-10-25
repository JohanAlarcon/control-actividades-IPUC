Proyecto: Control de Actividades — Sistema de Comités IPUC

CONTEXTO
Aplicación web para la gestión de comités, miembros y actividades de una congregación de la
Iglesia Pentecostal Unida de Colombia. Reemplaza cuadernos y grupos de WhatsApp. La usan
personas de todas las edades, en su mayoría desde el celular, muchas con poca experiencia
usando aplicaciones.

IDIOMA DEL PROYECTO
- La documentación de Spec Kit (constitución, especificaciones, planes, tareas) se escribe en español.
- El código se escribe en inglés: tablas, columnas, modelos, clases, métodos, variables, rutas y
  componentes, siguiendo las convenciones de Laravel (committees, members, activities, approvals).
- Los textos que ve el usuario final van en español de Colombia, tratando de "usted".

PRINCIPIOS (cinco, en este orden)

I. La privacidad entre comités es ley (NO NEGOCIABLE)
Cada persona ve únicamente la información de su ámbito: el pastor ve toda la congregación; el
presidente de un comité ve su comité y sus sub-comités; el líder de un sub-comité ve solo el suyo.
La restricción se aplica SIEMPRE en el servidor, nunca ocultando elementos en la interfaz. Toda
consulta a comités, miembros, actividades, asistencia, presupuesto y simpatizantes pasa por un
filtro de alcance centralizado, y ninguna consulta puede construirse salteándolo. Cada ruta y cada
acción exige autorización explícita mediante policy o gate. Toda funcionalidad nueva que lea o
escriba datos de un comité debe incluir una prueba que demuestre que un rol de otro comité no
puede verla ni modificarla.
Razón: es la promesa que la congregación aprobó en la reunión; si falla una sola vez, se pierde
la confianza del cliente.

II. Primero el celular
Toda pantalla se diseña y se prueba primero a 360 px de ancho y luego se amplía. Ninguna
funcionalidad se considera terminada si solo funciona bien en escritorio. Objetivos verificables:
cero desplazamiento horizontal; toda zona pulsable de 44x44 px como mínimo; texto de contenido
de 16 px como mínimo; la aplicación se instala como PWA y abre su pantalla principal en menos de
3 segundos sobre una red 3G simulada. Las tablas anchas se convierten en tarjetas en pantallas
angostas, no se desbordan.

III. Toda decisión deja rastro
Toda aprobación, rechazo, cambio de estado o movimiento de presupuesto queda registrado con
quién, qué, cuándo y por qué. Ese registro es inmutable: no se edita ni se borra, se corrige con un
registro nuevo. Nada que alguien haya aprobado puede desaparecer de la historia. La cadena de
aprobación (el sub-comité propone, el presidente revisa, el pastor aprueba lo nuevo o lo que se
sale del plan del año) vive en los datos, no solamente en la interfaz.

IV. El lenguaje es el de la congregación
En la interfaz no aparece jerga técnica: nada de endpoint, query, dashboard, tenant, login, token
ni "registro" en sentido de base de datos. Se dice culto, comité, hermano, hermana, bautismo,
simpatizante, cronograma, bandeja de aprobaciones, entrar con su clave. Los mensajes de error
explican en una frase qué pasó y qué hacer. Se trata a la persona de "usted". El vocabulario de la
IPUC es obligatorio: bautismo en el nombre de Jesucristo, Escuela Dominical, REFAM, Damas
Dorcas, vigilia, convención, Santa Cena. Queda prohibido el vocabulario católico (misa, sacerdote,
párroco) y la fórmula bautismal trinitaria.
Prueba de aceptación: si una hermana de 70 años no entiende la pantalla sin que nadie se la
explique, la pantalla está mal hecha.

V. Ninguna tarea se cierra sin pruebas (NO NEGOCIABLE)
No se exige TDD estricto: las pruebas pueden escribirse después de implementar, pero ninguna
tarea se marca como terminada sin pruebas automatizadas que la cubran y que pasen. Cobertura
obligatoria en: el filtro de alcance por rol, el flujo completo de aprobaciones, los cálculos de
estadísticas y de presupuesto, y las reglas de validación de los formularios. Se usa Pest con
factories sobre la base de datos de pruebas. Las pruebas corren antes de cada mezcla a main.

RESTRICCIONES TÉCNICAS

Stack obligatorio con versiones exactas, que no se actualizan sin decisión explícita:
PHP 8.4 · Laravel 12 · MySQL 8 · Inertia.js 2 · React 19 · Tailwind CSS 4 · Vite · Pest.
Entorno local Laragon; el proyecto vive en C:\laragon\www\control-actividades.

Arquitectura:
- Laravel + Inertia + React. Blade solo para el documento raíz. En este proyecto no se usa
  Livewire ni Filament.
- Multi-sede desde el primer día: toda tabla con datos de la congregación lleva su columna de
  sede (site_id) con llave foránea, y un global scope la aplica automáticamente. Ninguna consulta
  puede omitirlo. La licencia vigente cubre una sola sede, pero el modelo nace preparado para varias.
- La autorización vive en policies y gates de Laravel. El frontend nunca decide permisos: solo
  refleja los que el servidor ya resolvió.
- Antes de agregar cualquier dependencia se evalúa si Laravel o React ya lo resuelven. Agregar un
  paquete exige justificación escrita.
- Las migraciones son la única fuente de verdad del esquema. La base de datos no se modifica a mano.
- Zona horaria America/Bogota, moneda COP, fechas en formato colombiano en la interfaz.

Datos y seguridad:
- La información de miembros y simpatizantes es propiedad del cliente. No se usa para otros fines,
  no se copia fuera del servidor acordado y no aparece en registros de depuración.
- Contraseñas con el hash de Laravel, sesiones seguras y protección CSRF activa.
- Respaldo diario de la base de datos.
- Las semillas y los datos de prueba son siempre ficticios. Nunca datos reales de personas en el
  repositorio.

Alcance y simplicidad:
- Simple antes que completo. Lo que no está en el alcance contratado no se construye: se registra
  como propuesta para cotizar.
- El alcance contratado es: comités y sub-comités; aprobaciones en dos niveles; cronograma anual
  con propuesta, aprobación, rechazo y seguimiento; registro de miembros con reportes y
  estadísticas; seguimiento de simpatizantes por etapas; asistencia a reuniones y ensayos; y
  presupuesto asignado y ejecutado por comité.
- Ninguna pantalla hace más de 5 consultas a la base de datos. Se previenen las consultas N+1.

FLUJO DE TRABAJO Y PUERTAS DE CALIDAD

Flujo Spec Kit:
- Toda funcionalidad empieza por /speckit-specify. No se escribe código sin especificación aprobada.
- Secuencia obligatoria: specify → clarify → plan → tasks → implement. Si una especificación tiene
  ambigüedades, se resuelven en clarify antes de planear.
- La constitución manda sobre cualquier especificación o plan. Si un plan la contradice, se corrige
  el plan, no la constitución.

Ramas y aprobación:
- Trabajo por rama de funcionalidad. La IA propone y la persona aprueba: ningún cambio llega a
  main sin revisión y aprobación explícita del autor del proyecto.
- Antes de mezclar a main: las pruebas pasan, las migraciones corren sobre base de datos en limpio,
  y la pantalla se verificó a 360 px de ancho.
- Commits en español, en imperativo, describiendo el cambio funcional.

Entrega:
- El desarrollo se entrega en cuatro hitos semanales: (1) comités, sub-comités, usuarios y control
  de acceso; (2) cronograma anual y aprobaciones; (3) miembros, simpatizantes y reportes;
  (4) asistencia, presupuesto, pruebas y puesta en funcionamiento.
- Cada hito termina funcionando de punta a punta en el celular, no a medias.

GOBERNANZA
- La constitución está por encima de cualquier otra práctica, especificación o plan del proyecto.
- Toda enmienda se documenta: qué cambia, por qué, y qué impacto tiene sobre las especificaciones
  ya escritas.
- Versionado semántico de la constitución: MAJOR si se elimina o redefine un principio; MINOR si se
  agrega un principio o una sección; PATCH para aclaraciones de redacción.
- En cada revisión y antes de cada mezcla a main se verifica el cumplimiento de los cinco
  principios. Toda complejidad añadida debe justificarse por escrito.
- Los dos principios marcados NO NEGOCIABLE no admiten excepción: ni por plazo, ni por alcance,
  ni por petición del cliente.
- Guía de desarrollo en tiempo de ejecución: CLAUDE.md en la raíz del proyecto.

Versión: 1.0.0 | Ratificada: 2026-10-14 | Última enmienda: 2026-10-14
