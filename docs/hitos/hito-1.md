# Hito 1 · La base del sistema

Entrar, las cuentas y los cargos, la estructura de comités, la privacidad entre comités y el uso
desde el celular. Es el cimiento: todo lo que viene después (aprobaciones, cronograma, miembros,
actas, presupuesto) se apoya en esto.

**Estado al 2026-10-26: listo en el computador de desarrollo. Falta publicarlo y su aprobación.**

---

## Qué incluye

| Historia | Qué hace | Estado |
|---|---|---|
| US01 · Entrar al sistema | Usuario asignado, clave temporal que se cambia al entrar, bloqueo tras cinco intentos | Completa |
| US02 · Cuentas y cargos | El pastor crea, edita, retira y rehabilita cuentas, restablece claves y asigna cargos; el relevo se confirma aparte y los pendientes guardan de quién venían | Completa, salvo la ficha de miembro (espera la tabla de miembros) |
| US03 · Estructura de comités | Cada cargo ve su comité y lo que cuelga de él; el líder sabe quién revisa lo que propone | Completa, salvo los miembros de cada comité (misma espera) |
| US04 · Cada comité ve lo suyo | Lo ajeno, lo prohibido y lo que no existe responden la misma página, sin un solo dato | Completa para comités, actividades y aprobaciones; miembros, reuniones y presupuesto esperan sus tablas |
| US20 · Desde el celular | El molde de las pantallas, la cinta del cargo, el menú de abajo, la aplicación instalable y el aviso de versión nueva | Completa, salvo comprobar «Instalar», que exige https |

---

## Las puertas de calidad

| # | Puerta | Estado | Cómo se comprobó |
|---|---|---|---|
| 1 | Pruebas en verde | Pasa | 191 pruebas y 852 verificaciones, más 4 escritas como pendientes porque sus tablas aún no existen |
| 2 | `migrate:fresh --seed` sobre base vacía | Pasa | Corrido el 2026-10-26 sobre la base de pruebas: todas las migraciones y la semilla, sin errores |
| 3 | Cada pantalla con su prueba de aislamiento | Pasa | Cuentas (`UserPolicyTest`), comités (`CommitteeVisibilityTest`, `DirectAccessTest`), y la prueba de humo con los tres cargos (`MilestoneOneSmokeTest`) |
| 4 | Revisada a 360 px | Pasa en la medición; falta el celular real del diseño nuevo | Medido dentro de la página: cero desplazamiento horizontal. La fila más ancha cabe a 360 px y también a 320 |
| 5 | Cinco consultas propias, sin crecer con los datos | Pasa | `QueryBudgetTest` mide cada pantalla como pastor, presidente y líder, y vuelve a medir con diez veces más datos |
| 6 | Sin SQL crudo | Pasa | `NoRawQueriesTest` |
| 7 | Sin jerga técnica en las pantallas | Pasa | Revisión de los textos: español, trato de «usted», vocabulario de la congregación |
| 8 | Ningún componente de más de 300 líneas | Pasa | El más largo tiene 111. Se retiraron los restos del kit, entre ellos una barra lateral de 609 |
| 9 | Colores por su nombre | Pasa | Los tres archivos que no pasan por el CSS llevan una copia vigilada por `InstallableAppTest` |
| 10 | Aprobación del autor | **Pendiente** | — |

---

## Lo que falta para cerrarlo

1. **Publicarlo.** No hay servidor todavía (T007 a T012). Sin él no hay dirección de producción que
   revisar, ni https para que el celular ofrezca «Instalar» (T046), ni medición en red lenta (T048).
2. **Una revisión en un celular real del diseño nuevo**, anotada en la tabla de abajo.
3. **Su aprobación** (puerta 10).

## Lo que queda abierto, y por qué no bloquea

| Qué | Por qué espera |
|---|---|
| T027, 3 de 6 casos · T039 · T042 · T170 · T171 | Necesitan las tablas de miembros, reuniones y presupuesto, que llegan en sus fases |
| T045 · Tabla en tarjetas | Se construye con la primera tabla de verdad (miembros), para no hacer un componente sin uso |
| DEP-05 a DEP-08 | Dependen del pastor: la estructura real de comités, el formato de acta, el respaldo de archivos y el logo a color |

---

## Lo que se encontró y se corrigió en este hito

Vale la pena conocerlo, porque varios eran invisibles: las pruebas pasaban y la pantalla se veía
bien.

- **Lo ajeno se podía abrir por su número** (`/committees/5`): el sistema buscaba el registro antes
  de activar el filtro del cargo. Ninguna pantalla lo exponía todavía; todas las futuras lo
  habrían hecho. Decisión D-21.
- **Una cuenta se podía borrar a sí misma** desde una página de configuración que traía el kit, y la
  base de datos arrastraba en cascada sus cargos, sus actividades y sus solicitudes. Se retiró la
  página y ahora ni el sistema ni la base de datos dejan borrar a quien tiene historia. Decisión D-22.
- **Once pruebas de privacidad no podían fallar**: buscaban textos con tilde, y los datos viajan con
  las tildes escapadas. Ahora buscan en lo que la pantalla recibe (`PageTextTest` lo explica).
- **La regla de las cinco consultas se medía mal**: la prueba no veía las tres consultas fijas de
  Laravel. Constitución 2.3.1.
- **El contador de pendientes contaba de más** y **un dato compartido se borraba** al llegar el
  conteo diferido. Ambos corregidos antes de que una pantalla los mostrara.

---

## Revisión en un celular real

| Fecha | Celular | Quién revisó | Qué se vio |
|---|---|---|---|
| 2026-10-26 | El del autor | Autor del proyecto | Cuentas, comités y ficha de comité con el diseño anterior. Pidió un diseño más moderno |
| _pendiente_ | | | El diseño nuevo, con las tres cuentas de prueba: `pastor`, `doble.cargo` y `lider.montaje` |
