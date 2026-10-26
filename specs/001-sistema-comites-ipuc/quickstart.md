# Fase 1 — Guía de puesta en marcha y validación

**Funcionalidad**: Sistema de Comités IPUC · **Fecha**: 2026-10-18 · **Plan**: [plan.md](./plan.md)

Cómo levantar el proyecto y cómo comprobar que de verdad funciona. No lleva código de
implementación: eso vive en las tareas. Los detalles de columnas están en
[data-model.md](./data-model.md) y la forma de las respuestas en [contracts/](./contracts/).

---

## 1. Requisitos previos

| Pieza | Versión exigida por la constitución |
|---|---|
| PHP | 8.4 |
| MySQL | 8 |
| Node | la que traiga Laragon al día; Vite la requiere para compilar |
| Composer | 2.x |
| Entorno | Laragon, con el proyecto en `C:\laragon\www\control-actividades` |

Comprobación rápida, desde la raíz del proyecto:

```bash
php -v          # debe decir 8.4
mysql --version # debe decir 8
composer -V
node -v
```

---

## 2. Levantar el proyecto por primera vez

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

En `.env`, lo que este proyecto exige:

```
APP_TIMEZONE=America/Bogota
APP_LOCALE=es
DB_CONNECTION=mysql
DB_DATABASE=control_actividades
```

Base de datos y datos de la presentación:

```bash
php artisan migrate:fresh --seed
```

La semilla —`DatabaseSeeder`, que encadena `SiteSeeder`, `CommitteeSeeder`, `UserSeeder`,
`ActivitySeeder`, `ApprovalSeeder`, `MemberSeeder` y `SympathizerSeeder`— crea la sede, los seis
comités con sus diez sub-comités, las diecisiete cuentas con sus cargos y un juego de miembros,
actividades, simpatizantes, asistencia y presupuesto. **Todo ficticio**, como exige la constitución:
nunca datos reales de personas en el repositorio.

Levantar servidor e interfaz:

```bash
php artisan serve        # en una terminal
npm run dev              # en otra
```

---

## 3. Las cinco cuentas con las que se valida

La semilla deja estas cuentas con clave temporal, para poder recorrer los cinco puntos de vista que
hay que validar. La clave exacta se imprime al final de la siembra.

| Usuario | Cargo | Para comprobar |
|---|---|---|
| `pastor` | Pastor | Ve toda la congregación |
| `presidente.alabanza` | Presidente de un comité con sub-comités | Ve su comité y sus hijos |
| `lider.sonido` | Líder de un sub-comité | Ve solo su sub-comité |
| `presidente.evangelismo` | Presidente de Evangelismo | El único que acompaña simpatizantes |
| `doble.cargo` | Presidente de un comité **y** líder de un sub-comité de otro | El selector de cargo activo |

---

## 4. Comprobaciones a mano, en orden

Cada una corresponde a una historia y se hace **primero en una ventana de 360 px de ancho**, que es
la medida que exige el principio II.

### 4.1 Entrar y cambiar la clave — HU-01

1. Entre con `lider.sonido` y la clave temporal.
2. **Esperado**: el sistema exige cambiar la clave antes de mostrar cualquier información.
3. Cambie la clave y vuelva a entrar: debe llevarlo directo a su pantalla de inicio.
4. Escriba mal la clave cinco veces y pruebe una sexta.
5. **Esperado**: bloqueo de quince minutos, con el mensaje de pedirle al pastor que la restablezca.

### 4.2 El filtro de alcance — HU-04, principio I

1. Con `presidente.alabanza`, abra comités, miembros, aprobaciones, asistencia y presupuesto.
2. **Esperado**: solo su comité y sus sub-comités. Los demás aparecen como no disponibles, **sin
   ningún dato**.
3. Anote el identificador de un comité ajeno (véalo con `pastor`) y ábralo directo por su URL.
4. **Esperado**: «esa información no está disponible para su cargo», con indicación de a quién
   solicitarla, y **ningún dato del comité** en la respuesta.
5. Con `lider.sonido`, abra la lista de miembros.
6. **Esperado**: solo los de su sub-comité, y el sistema le dice **cuántas** personas de la
   congregación no aparecen y por qué — nunca quiénes.

### 4.3 El cargo activo — CL-05, corrección C-01

1. Entre con `doble.cargo`.
2. **Esperado**: el sistema le pide escoger con qué cargo va a actuar antes de mostrarle nada.
3. Escoja el primero. **Esperado**: toda pantalla muestra con qué cargo está actuando.
4. Abra la lista de miembros y verifique que **solo** aparecen los del ámbito escogido, no la suma
   de los dos.
5. Cambie de cargo con el selector y repita.
6. **Esperado**: ahora ve el otro ámbito, y en ningún momento los dos mezclados.

### 4.4 El cronograma compartido — RF-010, la única excepción que enumera la constitución

1. Con `presidente.alabanza`, abra el cronograma del año completo.
2. **Esperado**: ve actividades de **todos** los comités, con título, descripción, fecha, hora,
   lugar, comité responsable y estado.
3. Abra una actividad de otro comité.
4. **Esperado**: la carga de la respuesta trae **exactamente** esos siete datos y ningún otro.
   Compare el juego de llaves de la actividad con los siete; no se trata de buscar llaves
   prohibidas. **No** aparecen su presupuesto ni sus tareas internas. Revise la carga de la
   respuesta, no solo la pantalla.
5. Con `presidente.alabanza`, intente modificar esa actividad ajena y luego completarla.
6. **Esperado**: las dos acciones responden «no disponible» (RF-008). El cronograma compartido
   **solo deja leer**: no concede autorización para escribir sobre una actividad de otro comité.

### 4.5 La cadena de aprobación — HU-05, HU-06, HU-07

1. Con `lider.sonido`, proponga una actividad nueva.
2. **Esperado**: queda como propuesta y el sistema dice **con nombre propio** a quién le llegó.
3. Intente proponer una con título vacío y con fecha de otro año.
4. **Esperado**: no se envía y se señala exactamente qué corregir.
5. Proponga una en una fecha que ya tenga actividad.
6. **Esperado**: advertencia con cuál es y de qué comité.
7. Con `presidente.alabanza`, apruebe esa propuesta de actividad.
8. **Esperado**: **no** queda cerrada: pasa al pastor, y se le informa a quien la propuso.
9. Con `pastor`, apruebe. **Esperado**: entra al cronograma como aprobada, con su nombre y la fecha.
10. Rechace otra. **Esperado**: exige motivo escrito, y el motivo queda visible para quien propuso.

### 4.6 El gasto — RF-018, D-24: solo el pastor aprueba

1. Con `presidente.alabanza`, pida un gasto de su propio comité.
2. **Esperado**: le llega al pastor, que es quien aprueba. El presidente **no** lo deja aprobado.
3. Con `lider.sonido`, solicite autorización de un gasto.
4. **Esperado**: le llega a su presidente, que solo puede darle el visto bueno —y entonces pasa al
   pastor— o devolverlo con el motivo.
5. Registre un gasto que deje el saldo en negativo.
6. **Esperado**: advierte expresamente que se está pasando del presupuesto y **permite continuar**
   si se confirma.

### 4.7 Los simpatizantes — HU-13, HU-14, RF-040

1. Con `lider.sonido`, registre un simpatizante.
2. **Esperado**: queda registrado y su nombre queda como quien lo invitó.
3. Intente abrir la ficha de seguimiento de ese simpatizante.
4. **Esperado**: no la ve, y el sistema le explica por qué sin que parezca un error.
5. Con `presidente.evangelismo`, abra la ficha.
6. **Esperado**: ve las etapas con su fecha y el nivel de compromiso **calculado**, con las señales
   que lo determinaron.
7. Registre una visita. **Esperado**: el compromiso se recalcula solo.
8. Proponga el paso a miembro. **Esperado**: va al pastor; solo al aprobarse entra a la lista de
   miembros, conservando su fecha de bautismo y la de primer contacto.

### 4.8 Reportes y descarga — HU-11, HU-12

1. Con `pastor`, abra reportes.
2. **Esperado**: total, bautizados con su porcentaje, casados, solteros, edad promedio y cuántos no
   pertenecen a ningún comité.
3. Cambie a vista de tabla. **Esperado**: exactamente la misma información que los gráficos.
4. Aplique filtros y descargue.
5. **Esperado**: el archivo contiene **lo que está viendo**, no todo.
6. Repita con `presidente.alabanza`. **Esperado**: el archivo contiene únicamente su ámbito.

### 4.9 Primero el celular — HU-20, principio II

1. Ponga la ventana en 360 px de ancho y recorra todas las pantallas.
2. **Esperado**: ni una exige desplazamiento horizontal.
3. Mida una zona pulsable y un texto de contenido con las herramientas del navegador.
4. **Esperado**: la zona pulsable mide **48x48 px** o más y el texto **18 px** o más, y el contraste
   cumple **AA** (constitución 2.1.0, para el público de adultos mayores).
5. Abra una pantalla con tabla de muchas columnas.
6. **Esperado**: se presenta en tarjetas legibles, no en una tabla cortada.
7. Instale la aplicación en la pantalla de inicio del teléfono y ábrala.
8. **Esperado**: abre como aplicación, y la pantalla principal aparece en menos de 3 segundos con la
   red limitada a 3G en las herramientas del navegador.

### 4.10 Los tres niveles y la estructura administrable — HU-21

1. Con `pastor`, abra la estructura y cree un comité, luego un sub-comité dentro de él, y luego un
   equipo dentro del sub-comité.
2. **Esperado**: los tres se crean, y el sistema muestra el nivel de cada uno.
3. Intente crear un nodo colgado del equipo.
4. **Esperado**: lo rechaza y explica que la estructura llega hasta tres niveles.
5. Entre como el líder del equipo y abra miembros, actividades y presupuesto.
6. **Esperado**: ve solo lo de su equipo. **No ve nada de su sub-comité hermano.**
7. Entre como el presidente del comité.
8. **Esperado**: ve lo del comité, lo del sub-comité **y lo del equipo** — su sub-árbol completo.
9. Con `pastor`, renombre el comité y luego inactívelo.
10. **Esperado**: las actas y los reportes anteriores siguen mostrando el nombre viejo; tras
    inactivarlo, su historia sigue consultable, no aparece para proponer nada, y sus miembros quedan
    señalados para reasignar.
11. Entre como presidente y abra la estructura.
12. **Esperado**: la ve y no puede modificarla.

### 4.11 El acta de una reunión — HU-22

1. Con el secretario de un comité, registre una reunión y levante su acta.
2. **Esperado**: el formulario trae la fecha, el lugar y los asistentes de la reunión, y pide orden
   del día, desarrollo y compromisos.
3. Guarde sin terminar. **Esperado**: queda como borrador y puede seguir después.
4. Con el presidente del mismo comité, ciérrela.
5. **Esperado**: recibe su número del año, queda con los dos nombres y la fecha.
6. Intente editarla, incluso llamando la ruta directamente.
7. **Esperado**: no lo permite y ofrece levantar una acta nueva que haga constar la corrección.
8. Convierta un compromiso en tarea con su responsable.
9. **Esperado**: aparece en los pendientes de esa persona y cuenta para el avance de la actividad; el
   texto del compromiso en el acta **no cambió**.
10. Imprímala. **Esperado**: hoja legible en blanco y negro con el nombre de la iglesia, el comité,
    el número, la fecha, los asistentes, el desarrollo, los compromisos y el espacio de las firmas.
11. Con un cargo de otro comité, busque esa acta. **Esperado**: no la ve.

### 4.12 Evidencias y soportes — HU-23, HU-24

1. Con un líder, suba una foto como evidencia de una actividad cumplida de su comité.
2. **Esperado**: queda adjunta con su nombre y la fecha, y el sistema la redujo de peso solo.
3. Intente subir un archivo de más de 10 MB y un archivo `.exe`.
4. **Esperado**: los rechaza con una frase que dice cuánto pesa y cuál es el límite, **sin perder lo
   que ya había escrito** en el formulario.
5. Copie la dirección del archivo y ábrala con la sesión de un cargo de otro comité.
6. **Esperado**: «no disponible». El archivo **no** se sirve desde una dirección pública: pasa por la
   policy. Esto es lo más importante de esta comprobación.
7. Consulte esa actividad desde otro comité, en el cronograma compartido.
8. **Esperado**: ve título, fecha y comité responsable, y **ninguna evidencia**.
9. Intente borrar la evidencia de una actividad ya cumplida.
10. **Esperado**: no la borra; la marca como reemplazada y deja subir la correcta.
11. Con el tesorero, registre un gasto y adjunte una factura en PDF.
12. **Esperado**: queda unida al movimiento. Con `pastor`, ábrala; con un cargo ajeno, no aparece.

---

## 5. Las pruebas automatizadas

```bash
php artisan test                          # todo
php artisan test --filter=Scope           # aislamiento entre comités
php artisan test tests/Architecture       # cobertura del filtro y conteo de consultas
php artisan test --filter=Approval        # la cadena de aprobación
```

Las tres de arquitectura son la red de seguridad del proyecto:

| Prueba | Qué garantiza |
|---|---|
| `ScopeCoverageTest` | Ningún modelo con `site_id` se queda sin los rasgos de ámbito. Un modelo nuevo mal hecho rompe la construcción el mismo día. La lista blanca tiene **una sola** tabla, la de la bitácora, justificada en [research.md](./research.md) |
| `NoRawQueriesTest` | No hay `DB::` ni SQL crudo sobre tablas con ámbito: saltarían el filtro sin que nadie se entere |
| `QueryBudgetTest` | Ninguna pantalla pasa de cinco **consultas propias**, para el pastor, un presidente y un líder, y el número no crece con los datos. Las tres fijas de Laravel se miden aparte ([contracts/shared-props.md](./contracts/shared-props.md)) |

---

## 6. Las puertas antes de fusionar a `main`

Todas deben pasar. Son las de la constitución más las que agregó este plan.

1. Las pruebas de Pest pasan, incluidas `ScopeCoverageTest`, `NoRawQueriesTest` y
   `QueryBudgetTest`.
2. Las migraciones corren sobre base de datos en limpio: `php artisan migrate:fresh --seed`.
3. Cada pantalla nueva tiene al menos una prueba de aislamiento: un cargo ajeno **no** la ve.
4. La pantalla se verificó a 360 px de ancho.
5. Ninguna pantalla supera cinco consultas propias, y el número no crece con los datos. Las dos excepciones declaradas —`/` y
   `/reports`— reparten sus cifras en propiedades diferidas, cada una con su propio presupuesto de
   cinco por respuesta.
6. No hay `DB::` ni SQL crudo sobre tablas con ámbito.
7. Ningún texto de interfaz contiene jerga técnica (principio IV).
8. **Ningún componente de React pasa de 300 líneas** (constitución 2.2.0). Se comprueba con
   `npm run lint` y a ojo con:
   `find resources/js -name "*.tsx" | xargs wc -l | sort -rn | head`
9. Revisión y aprobación explícita del autor del proyecto: la IA propone, la persona aprueba.

---

## 7. Orden de construcción

Los cuatro hitos del contrato. Cada uno termina **funcionando de punta a punta en el celular**,
desplegado y probado — no a medias.

| Hito | Contenido | Historias |
|---|---|---|
| **1 · Cimiento** | Sede, usuarios, los cuatro cargos con vigencia, estructura de **tres niveles** administrable por el pastor, el filtro de alcance por sub-árbol con sus tres capas, el selector de cargo activo, las policies, las tres pruebas de arquitectura, entrada con cambio de clave, semilla y despliegue | HU-01 a HU-04, **HU-21**, HU-20 |
| **2 · Decidir** | Aprobaciones con su cadena completa, cronograma anual, propuestas, aviso de cruce, tareas y avance, transiciones de estado, bandeja y contadores | HU-05 a HU-09, HU-17 |
| **3 · La gente** | Miembros con filtros, importación, reportes con gráficos y tabla, descarga e impresión, simpatizantes con etapas y visitas, paso a miembro, autorización de datos | HU-10 a HU-14, HU-18 |
| **4 · Cerrar** | Asistencia, **actas con sus compromisos**, presupuesto con **soportes**, **evidencias de cumplimiento**, correcciones con bitácora, PWA afinada, conteo de consultas, capacitación y acta de entrega | HU-15, HU-16, HU-19, **HU-22 a HU-24** |

Si el hito 1 no queda sólido, nada de lo demás se sostiene: ahí vive el principio no negociable I, y
desde la ampliación del 2026-10-24 también la reescritura del filtro a tres niveles (RG-14).

**Sobre el plazo, con los nombres claros.** La ampliación del 2026-10-24 metió cuatro módulos al
alcance **sin mover la fecha**, por decisión del autor y contra la recomendación de cotizarlos como
fase 2 (RG-11). Los dos principios NO NEGOCIABLES se sostienen y la calidad no se negocia, así que
lo que cede es la promesa de que cada hito cierre de punta a punta: los hitos entregan funcionalidad
parcial y el cierre real se corre al final. Eso hay que decírselo al pastor **por escrito antes del
vencimiento**, no después. P1 completo antes de cualquier P2.

**Dos cosas que no se despliegan hasta que su dependencia esté lista:** el módulo de archivos, hasta
que el respaldo diario los incluya (DEP-07, RG-13), y la vista de impresión del acta, hasta que se
sepa si el distrito exige un formato oficial (DEP-06).
