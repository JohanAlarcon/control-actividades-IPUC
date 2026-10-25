# Fase 1 — Contratos

**Funcionalidad**: Sistema de Comités IPUC · **Fecha**: 2026-10-18 · **Plan**: [../plan.md](../plan.md)

## Qué es un contrato en este proyecto

Este sistema **no expone API pública**. Con Inertia, el servidor entrega directamente a la pantalla
las propiedades que necesita, y el navegador nunca consulta una ruta JSON de propósito general. La
decisión es deliberada: sin API abierta hay una superficie menos que proteger, y el principio no
negociable I se juega en una sola frontera.

El contrato es, entonces, **la forma de las propiedades que cada pantalla recibe**. Vale como
contrato porque:

- Define qué columnas salen del servidor, lo que es exactamente donde se cumple o se incumple el
  filtro de alcance. La prueba de aislamiento revisa la carga de la respuesta, no lo que se ve en
  pantalla.
- Fija qué decide el servidor y qué solo refleja el frontend. Los permisos llegan resueltos
  (`can*`); el frontend nunca decide si alguien puede algo (constitución, sección de arquitectura).

## Archivos

| Archivo | Contenido |
|---|---|
| [shared-props.md](./shared-props.md) | El bloque que viaja en **toda** respuesta: usuario, cargo activo, ámbito, avisos; más los conteos diferidos de lo oculto y de lo pendiente |
| [pages.md](./pages.md) | Ruta por ruta: método, pantalla, propiedades propias, permisos y qué **no** debe viajar |

## Reglas que gobiernan todos los contratos

1. **Rutas en inglés** (R-03, corrección C-02). Los nombres de pantalla y todo texto visible van en
   español de Colombia, tratando de «usted».
2. **El servidor resuelve los permisos.** Cada propiedad `can*` es un booleano ya decidido por una
   policy. El frontend lo refleja; no lo calcula.
3. **Nada fuera del ámbito del cargo activo.** La única excepción es la que enumera la
   constitución —el cronograma compartido—, y solo con los siete datos que RF-010 permite: título,
   descripción, fecha, hora, lugar, comité responsable y estado (R-11). Ningún otro dato de una
   actividad ajena viaja, y la excepción **solo deja leer**.
4. **Ningún identificador ajeno viaja.** Una pantalla no recibe listas de comités que el cargo
   activo no puede ver, ni para llenar un desplegable. Se exceptúa el **nombre** del comité
   responsable de una actividad, que el cronograma compartido publica por la excepción del
   principio I (RF-010); lo que nunca viaja son sus miembros, su presupuesto, sus tareas, sus actas
   y sus archivos.
4 bis. **El ámbito es el sub-árbol.** Desde la ampliación del 2026-10-24 la estructura tiene tres
   niveles, y el ámbito de un cargo es su nodo y todos sus descendientes. Las **actas** (RF-074) y
   los **archivos adjuntos** (RF-080) siguen el filtro completo: no amplían la excepción del
   cronograma, y los archivos se sirven por una ruta con policy, nunca desde `public/`.
5. **Sin jerga técnica en los textos** que viajen listos para mostrar (RF-060).
6. **Montos en pesos colombianos y fechas en formato colombiano** al presentarse; el servidor
   entrega el valor crudo y la capa de formato de `resources/js/lib` lo presenta.
7. **Máximo cinco consultas por petición completa** (constitución), verificado por
   `Architecture/QueryBudgetTest`: se mide la petición entera, sea la primera carga de la pantalla
   o una petición parcial de propiedad diferida. El bloque compartido cuesta **tres** y a la
   pantalla le quedan **dos** ([shared-props.md](./shared-props.md)). Hay **dos excepciones
   declaradas**, `/` y `/reports`, que reparten sus cifras en propiedades diferidas con su propio
   presupuesto de cinco por respuesta ([pages.md](./pages.md)). El techo de cinco no se sube.
