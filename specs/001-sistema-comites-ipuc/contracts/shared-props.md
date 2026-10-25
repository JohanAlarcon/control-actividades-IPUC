# Contrato — Propiedades compartidas

El bloque compartido viaja en **toda** respuesta de Inertia, desde el middleware, con dos
excepciones: `scope.hidden` y `pendingCount` son **propiedades diferidas** y viajan en su propia
petición parcial, no en la primera carga. Unas y otras reflejan la corrección C-01: el ámbito es el
de **un solo cargo activo**, nunca la unión de dos.

## Forma

```
auth.user          { id, name, username, isPastor }
auth.roles         [ { id, committeeId, committeeName, role, isSubcommittee } ]
                   todos los cargos VIGENTES de la persona; alimenta el selector

scope.activeRole   { id, committeeId, committeeName, role } | null
                   null solo cuando es pastor (su ámbito es toda la congregación)
scope.label        "Toda la congregación" | "Comité de Alabanza" | "Sub-comité de Sonido"
scope.mustChoose   true cuando tiene dos o más cargos y no hay uno activo en la sesión
scope.canSwitch    true cuando tiene dos o más cargos vigentes
flash              { type: "success" | "warning" | "error", message }
```

Propiedades **diferidas**, fuera del bloque que viaja siempre:

```
scope.hidden       { committees, subcommittees, members, activities }
                   SOLO CONTEOS, nunca nombres ni identificadores (RF-009)
                   se pide aparte; no viaja en la primera carga de ninguna pantalla

pendingCount       número de solicitudes esperando la decisión del cargo activo (RF-055)
                   se pide aparte; no viaja en la primera carga de ninguna pantalla
```

## Reglas

| # | Regla | Requisito |
|---|---|---|
| 1 | `scope.activeRole` determina el ámbito de **toda** consulta de la petición. Un cargo distinto es otra petición. | RF-007 |
| 2 | Si `scope.mustChoose` es verdadero, el sistema **no muestra información** hasta que la persona escoja cargo. | RF-007, R-01 |
| 3 | `scope.hidden` lleva **solo cantidades**. Decir «hay 3 comités que usted no ve» es lo que pide RF-009; decir cuáles, en estos conteos, sería filtrar datos. La reserva de RF-009 se refiere a estos conteos: el nombre del comité responsable de una actividad sí se comparte, por la excepción del cronograma. | RF-009, HU-04 esc. 3 |
| 10 | `scope.activeRole` incluye el **nivel** del nodo (comité, sub-comité o equipo) y su ámbito es el nodo con todos sus descendientes. Secretario y tesorero traen el mismo ámbito que su presidente, y se diferencian en los `can*`. | RF-011, RF-013, D-20 |
| 4 | `auth.roles` solo trae cargos con `ended_at` nulo. Un cargo cerrado desaparece del selector el mismo día. | RN-09, RF-006 |
| 5 | `pendingCount` se cuenta con el ámbito del cargo activo, no con la suma de los cargos. | RF-007, RF-055 |
| 6 | Toda pantalla muestra `scope.label` de forma visible: la persona siempre sabe con qué cargo actúa. | RF-007, CL-05 |
| 7 | `flash.message` va en español, explica en una frase qué pasó y qué hacer. | RF-060 |
| 8 | El bloque compartido se arma con **dos consultas como máximo**: la persona, y sus cargos vigentes con su comité. A la pantalla le quedan tres. | Constitución, plan.md |
| 9 | `scope.hidden` y `pendingCount` son **propiedades diferidas**: se piden aparte y esa petición paga también las dos consultas del bloque compartido. | RF-009, RF-055 |

## Presupuesto de consultas

`Architecture/QueryBudgetTest` cuenta **las consultas propias de la pantalla**, sin las tres fijas
de Laravel en cada petición —leer la sesión, cargar a la persona y guardar la sesión—, que se miden
aparte. La primera carga y cada petición de propiedad diferida se miden por separado, y ninguna pasa
de cinco consultas. El reparto normativo es este:

| Parte de la respuesta | Consultas |
|---|---|
| bloque compartido: los cargos vigentes, sus comités, y el sub-árbol del cargo activo | **3** como máximo |
| la pantalla, con sus propiedades propias | 2 |
| `scope.hidden` y `pendingCount` | propiedad diferida, petición aparte |

> **Corregido el 2026-10-24, al medirlo.** Este documento decía 2 para el bloque compartido y 3
> para la pantalla. Al implementarlo, la medición real dio **6**: los cargos se consultaban dos
> veces, el comité del cargo activo otra, y el conteo para saber si se puede cambiar de cargo una
> más. Reutilizando lo que ya se cargó bajó a **3**, que es el mínimo real: los cargos, sus comités
> por carga anticipada, y el sub-árbol. A la pantalla le quedan **2**. El pastor cuesta menos,
> porque no tiene cargos que consultar.
>
> Si una pantalla no cabe en 2, reparte sus cifras en propiedades diferidas, como ya hacen `/` y
> `/reports`. El techo de cinco por petición lo fija la constitución y **no se sube**.

`scope.hidden` y `pendingCount` son **dos** propiedades diferidas distintas: cada una viaja en su
propia petición parcial, y esa petición paga también las dos consultas del bloque compartido. Los
cuatro conteos de `scope.hidden` se resuelven en **una sola agregación** armada desde los propios
modelos, nunca con un conteo por llave (`plan.md`).

El techo de cinco lo fija la constitución y **no se sube**: la pantalla que no quepa reparte sus
cifras en propiedades diferidas. Dos lo hacen y están declaradas como excepción en
[pages.md](./pages.md): `/reports` —RF-036 pide ocho grupos y dos de ellos exigen `GROUP BY`— y `/`.

## Cambio de cargo activo

```
POST /role/switch   { roleId }
```

Valida que el cargo pertenezca a la persona y esté vigente, lo guarda en la sesión y regresa a la
pantalla anterior. Un `roleId` ajeno o cerrado responde «no disponible» sin revelar ningún dato
(RF-008).

## Qué hay que probar

- Persona con dos cargos, actuando con el cargo A: ninguna pantalla, ningún acceso directo por
  identificador y ninguna descarga de reporte devuelve algo del ámbito B (CL-05).
- `scope.hidden` nunca contiene nombres ni identificadores, en ninguna respuesta.
- El bloque compartido no pasa de **tres consultas** en ninguna respuesta, ni en la primera carga ni
  en una petición parcial.
- Ninguna respuesta pasa de cinco consultas propias: se mide por separado la primera carga de cada
  pantalla y cada petición de propiedad diferida.
- Ni `scope.hidden` ni `pendingCount` aparecen en la primera carga de ninguna pantalla: se piden
  aparte, y la pantalla funciona mientras no han llegado.
- Al cerrar un cargo, desaparece de `auth.roles` en la siguiente petición; si era el activo, el
  sistema pide escoger de nuevo.
- `POST /role/switch` con un `roleId` de otra persona: «no disponible», sin datos.
