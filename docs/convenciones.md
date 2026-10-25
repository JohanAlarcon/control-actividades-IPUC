# Convenciones del proyecto

Las reglas completas están en [`CLAUDE.md`](../CLAUDE.md) y mandan sobre este archivo. Aquí queda
lo operativo del día a día: cómo se nombran las cosas y cómo se trabaja con git.

## Idioma

| Qué | Idioma |
|---|---|
| Tablas, columnas, modelos, clases, métodos, variables, **rutas**, componentes | Inglés, convenciones de Laravel |
| Texto que ve el usuario final | Español de Colombia, tratando de «usted» |
| Documentación (`specs/`, `docs/`) y commits | Español |

## Nombres

- **Tablas**: plural en inglés, en minúscula con guion bajo — `committee_user`, `budget_movements`.
- **Modelos**: singular en inglés, en PascalCase — `Committee`, `BudgetMovement`.
- **Controladores**: terminan en `Controller` — `MinuteController`.
- **Rutas**: en inglés y en plural — `/committees`, `/approvals`, `/attendance`.
- **Pantallas de React**: `resources/js/pages/<Sección>/<Pantalla>.tsx` — `Minutes/Form.tsx`.
- **Clases de apoyo**: `app/Support/<Tema>/<LoQueHace>.php` — `Scope/CurrentScope.php`.
- **Pruebas**: el nombre del caso dice qué se espera, no qué se ejecuta.

## Commits

- En español, en **imperativo**, describiendo el cambio funcional.
- **Breves**: una línea de asunto. Cuerpo solo cuando el *por qué* no quepa en el asunto.
- Ejemplos: `Agregar el filtro de alcance por sub-árbol` · `Corregir el cálculo de asistencia`.
- Nunca mezclar dos cambios distintos en un commit.

## Ramas

- Una rama por funcionalidad. La actual: `001-sistema-comites-ipuc`.
- Nada llega a `main` sin revisión y aprobación explícita del autor del proyecto.
- Antes de mezclar, pasan las nueve puertas de calidad de [`CLAUDE.md`](../CLAUDE.md).

## El entorno local

Laragon, con **PHP 8.3.30** y **MySQL 8.4.3** activos. Dos cosas que hay que saber:

1. **La terminal de Windows puede traer otro PHP.** Si `php -v` no dice 8.3, use la terminal que
   abre el propio Laragon, o anteponga la ruta:
   `export PATH="/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH"`
2. **La extensión `zip` de PHP debe estar activa.** Sin ella, Composer no instala la librería de
   hojas de cálculo y —sin avisar— baja una versión de hace diez años. Verifíquelo con
   `php -m | grep zip`. El mismo cuidado vale para el servidor de producción.

Bases de datos: `control_actividades` para trabajar y `control_actividades_test` para las pruebas.
Las pruebas corren sobre **MySQL**, no sobre SQLite, porque la búsqueda que ignora tildes (RF-034)
depende del *collation* y en SQLite no se comportaría igual.
