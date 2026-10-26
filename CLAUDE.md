# Guía de desarrollo — Control de Actividades (Sistema de Comités IPUC)

Esta es la guía en tiempo de ejecución que designa la Gobernanza de la constitución. Lo que está
aquí **no sustituye** a `.specify/memory/constitution.md`: la resume para tenerla a mano y agrega
las reglas de trabajo del día a día. Si algo de aquí contradice la constitución, manda la
constitución.

Constitución vigente: **2.3.1** (ratificada 2026-10-14; enmendada 2026-10-18, 2026-10-24).

---

## Los cinco principios, en una línea cada uno

1. **La privacidad entre comités es ley (NO NEGOCIABLE).** Cada cargo ve solo su nodo y sus
   descendientes; el filtro se aplica siempre en el servidor y ninguna consulta lo saltea.
2. **Primero el celular.** Toda pantalla se diseña y se prueba primero a 360 px de ancho.
3. **Toda decisión deja rastro.** Aprobaciones, rechazos, cambios de estado y movimientos de
   presupuesto quedan registrados y son inmutables.
4. **El lenguaje es el de la congregación.** Sin jerga técnica en la interfaz; vocabulario IPUC.
5. **Ninguna tarea se cierra sin pruebas (NO NEGOCIABLE).** Las pruebas pueden ir después de
   implementar, pero la tarea no se marca hecha sin ellas en verde.

Los principios **I** y **V** no admiten excepción: ni por plazo, ni por alcance, ni por petición
del cliente.

---

## Las puertas antes de mezclar a `main`

Todas deben pasar:

1. `php artisan test` en verde, incluidas `ScopeCoverageTest`, `NoRawQueriesTest` y
   `QueryBudgetTest`.
2. `php artisan migrate:fresh --seed` corre sobre base de datos en limpio, sin errores.
3. Cada pantalla nueva tiene al menos una prueba de aislamiento: un cargo ajeno **no** la ve.
4. La pantalla se verificó a **360 px** de ancho.
5. Ninguna pantalla hace más de **5 consultas propias**, y el número no crece con los datos. Las 3
   fijas de Laravel —leer y guardar la sesión, cargar a la persona— se miden aparte.
6. No hay `DB::` ni SQL crudo sobre tablas con ámbito.
7. Ningún texto de interfaz contiene jerga técnica.
8. **Ningún componente de React pasa de 300 líneas.**
9. **Los colores se usan por su nombre**, no escritos a mano. Ver «La paleta» más abajo.
10. Revisión y aprobación explícita del autor del proyecto: la IA propone, la persona aprueba.

---

## La paleta

Es la de la IPUC. Está definida una sola vez, en `resources/css/app.css`, y se usa por el nombre
de la clase de Tailwind. **Ninguna pantalla escribe un `#código` a mano.**

| Clase | Color | Para qué | Contraste |
|---|---|---|---|
| `bg-ipuc` `text-ipuc` | `#00338d` azul | botones, títulos | 11:1 |
| `bg-ipuc-oscuro` | `#001a4d` | el botón presionado | — |
| `border-ipuc-claro` | `#009fda` | bordes, iconos, foco | 3:1 |
| `bg-ipuc-dorado` | `#f0ab00` dorado | adorno | 2:1 |
| `text-tinta` | `#1a1a18` | texto normal | 18:1 |
| `text-tinta-suave` | `#5a5852` | texto de apoyo | 7:1 |
| `border-linea` | `#6e6a62` | borde de los campos | 5:1 |
| `text-alerta` | `#8a2b12` | errores | 9:1 |
| `bg-fondo` | `#f2f5fa` | el fondo de las pantallas, detrás de las tarjetas | — |
| `shadow-tarjeta` `shadow-barra` | el azul, apenas | tarjetas y menú de abajo, sin bordes duros | — |

El azul claro y el dorado **no llevan texto encima**: 3:1 y 2:1 están por debajo del 4,5:1 que
exige el principio II. Sirven para bordes, iconos y adorno, o con texto oscuro encima.

---

## El logo

| Archivo | Para qué |
|---|---|
| `public/img/logo-ipuc.webp` | El logo en las pantallas. Mide 360 px, se muestra a 180 |
| `public/img/icono-ipuc.png` | El icono al agregar la aplicación a la pantalla de inicio |
| `public/favicon.ico` | El icono de la pestaña. Es el oficial que publica la iglesia |

Se muestra con `resources/js/components/ipuc-logo.tsx`. **El logo no se modifica**: no se recorta,
no se le cambia el color y no se le quita el lema. La versión que publica la iglesia es blanca, así
que necesita fondo oscuro detrás; por eso el componente trae su propia banda azul.

Pendiente con el pastor: **DEP-08**, conseguir la versión a color —hace falta para imprimir actas
sobre papel blanco— y confirmar que la IPUC nacional autoriza usar el logo en el sistema.

---

## Prohibiciones

- **Nada de `DB::` ni SQL crudo** (`whereRaw`, `selectRaw`) sobre tablas con ámbito. Saltearía el
  filtro de alcance sin que nadie se entere. `NoRawQueriesTest` lo verifica.
- **Nada de Livewire ni Filament.** El stack es Laravel + Inertia + React.
- **La base de datos no se modifica a mano.** Las migraciones son la única fuente de verdad.
- **Ninguna salida del filtro de alcance** salvo la única que enumera la constitución: el
  cronograma compartido, por `Activity::forSharedCalendar()`, de solo lectura y con los siete datos
  permitidos. Cualquier otra es un defecto.
- **No se cambia el orden del filtro.** `ResolveScope` corre antes de que Laravel busque el registro
  de la ruta (D-21). Si ese orden se pierde, abrir `/committees/5` trae un comité ajeno.
  `DirectAccessTest` lo vigila.
- **Los archivos adjuntos no se sirven desde `public/`.** Pasan por una ruta con policy.
- **Nunca datos reales de personas en el repositorio.** Las semillas son ficticias.

---

## Idioma

| Qué | Idioma |
|---|---|
| Documentación de Spec Kit (constitución, spec, plan, tareas) | Español |
| Código: tablas, columnas, modelos, clases, métodos, variables, **rutas**, componentes | Inglés, convenciones de Laravel |
| Texto que ve el usuario final | Español de Colombia, tratando de «usted» |
| Archivos de `docs/` | Español |
| Commits | Español, en imperativo |

Vocabulario obligatorio de la IPUC: culto, comité, hermano, hermana, bautismo en el nombre de
Jesucristo, simpatizante, Escuela Dominical, REFAM, Damas Dorcas, vigilia, convención, Santa Cena.
**Prohibido** el vocabulario católico (misa, sacerdote, párroco) y la fórmula bautismal trinitaria.

---

## Cómo se escribe el código

La regla de oro: **la solución simple gana.** Esto lo va a mantener alguien que no participó en
construirlo.

- Sin patrones de diseño que el problema no pida. Sin capas de abstracción «por si acaso».
- **Pocos comentarios.** El código se explica por sus nombres. Un comentario se justifica cuando el
  *por qué* no es evidente, nunca para narrar el *qué*.
- La lógica vive en los controladores; lo que se repite o se puede equivocar se extrae a
  `app/Support/` con nombre propio, para tener **un solo lugar** donde puede fallar y un solo lugar
  donde probarlo.
- Una `FormRequest` por formulario, con sus reglas y sus mensajes en español.
- Antes de agregar una dependencia: ¿lo resuelve ya Laravel o React? Agregar un paquete exige
  justificación escrita en `plan.md`.

### El molde de las pantallas

Toda pantalla interna va dentro de `resources/js/layouts/main-layout.tsx` y empieza con
`PageHeader`. El molde pone la barra de arriba, la cinta con el cargo activo, los avisos y el menú
de abajo, como en el prototipo. Una sección nueva se suma al menú en
`components/layout/bottom-nav.tsx` el día que existe su pantalla, nunca antes: un botón que lleva
a la nada confunde más que uno que falta.

### Límites de tamaño en React

Medidos en `resources/js`. Este proyecto usa **TypeScript** (`.tsx`), no `.jsx`.

- **250 líneas por archivo de componente.** Al pasarlas, se divide. **300 es el tope
  infranqueable**: ningún componente nuevo lo supera.
- **70 líneas por función.** Más largo que eso, casi siempre hace dos cosas.
- **Un archivo, un componente exportado.** Los auxiliares que solo usa ese archivo pueden vivir
  junto a él mientras no se pase del límite.
- **El estado vive en el nivel más bajo posible.** Si solo lo usa un componente —qué fila de una
  tabla está abierta, por ejemplo—, no sube a la página.
- **Un archivo que se toca y está por encima del límite, se divide.** No hay que arreglar todo el
  proyecto de una vez, pero lo que se modifica no se deja igual.

Cómo dividir cuando se pasa:

1. Cada sección visual a su propio componente en `resources/js/components/`.
2. El estado y las llamadas al servidor a un hook en `resources/js/hooks/`.
3. El archivo de `resources/js/pages/` se queda **solo componiendo**: qué se muestra en cada paso.

Antes de cerrar un desarrollo en React:

```bash
find resources/js -name "*.tsx" | xargs wc -l | sort -rn | head
```

---

## Pruebas

Pest con factories sobre la base de datos de pruebas. Tres familias:

| Familia | Qué prueba |
|---|---|
| `tests/Feature/` | La mayoría. Entran por ruta HTTP como un cargo concreto: ejercitan ruta, policy y filtro juntos |
| `tests/Unit/` | Cálculos puros: compromiso, avance, estadísticas, saldos, sub-árbol |
| `tests/Architecture/` | `ScopeCoverageTest`, `NoRawQueriesTest`, `QueryBudgetTest` |

Cobertura obligatoria: el filtro de alcance, el flujo completo de aprobaciones, los cálculos de
estadísticas y de presupuesto, y las reglas de validación de los formularios.

**Al terminar un desarrollo, se prueba a fondo**, en escenarios distintos y con los casos límite
que la especificación enumera (`spec.md` → Casos Límite), no solo el camino felíz.

```bash
php artisan test                    # todo
php artisan test --filter=Scope     # aislamiento entre comités
php artisan test tests/Architecture # las tres redes de seguridad
```

---

## Flujo de trabajo

1. Toda funcionalidad empieza por `/speckit-specify`. No se escribe código sin especificación
   aprobada.
2. Secuencia: **specify → clarify → plan → tasks → implement**.
3. Trabajo por rama de funcionalidad. Rama actual: `001-sistema-comites-ipuc`.
4. La constitución manda sobre cualquier especificación o plan. Si un plan la contradice, **se
   corrige el plan**.

### Commits

- En español, en imperativo, describiendo el cambio funcional.
- **Breves**: una línea de asunto que diga qué cambió. Cuerpo solo cuando el *por qué* no quepa en
  el asunto.
- Ejemplos: `Agregar el filtro de alcance por sub-árbol`, `Corregir el cálculo de asistencia`.

### Cómo explicar un cambio

En **lenguaje claro**, sin tecnicismos innecesarios. Si hace falta un término técnico, se explica en
la misma frase. Con ejemplos o analogías cuando ayuden. La explicación la entiende alguien sin
conocimientos avanzados, y el tono es cercano, no de manual.

---

## Dónde está cada cosa

| Archivo | Para qué |
|---|---|
| `.specify/memory/constitution.md` | La norma del proyecto. Manda sobre todo lo demás |
| `specs/001-sistema-comites-ipuc/spec.md` | Qué se construye: historias, requisitos, casos límite |
| `specs/001-sistema-comites-ipuc/plan.md` | Cómo: stack, estructura, decisiones (D-xx) y riesgos (RG-xx) |
| `specs/001-sistema-comites-ipuc/data-model.md` | Las tablas, sus reglas y la cobertura del filtro |
| `specs/001-sistema-comites-ipuc/tasks.md` | Las tareas, por fase y por historia |
| `specs/001-sistema-comites-ipuc/research.md` | Las decisiones de investigación (R-xx) y los puntos abiertos (A-xx) |
| `specs/001-sistema-comites-ipuc/contracts/` | Qué propiedades recibe cada pantalla y qué **no** debe viajar |
| `specs/001-sistema-comites-ipuc/quickstart.md` | Levantar el proyecto y las comprobaciones a mano |

Cuatro familias de identificadores, y no se mezclan: **R-xx** decisiones de investigación ·
**RG-xx** riesgos del plan · **D-xx** decisiones de arquitectura del plan · **DEP-xx** dependencias
del cliente del spec.
