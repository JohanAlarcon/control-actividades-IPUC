/** Las clases que se repiten. Todo control mide 48 px de alto y su texto 18 px (principio II). */
const field = 'mt-2 w-full rounded-xl border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none';

export const fieldClass = `${field} h-12`;

/** Para escribir más de una línea, como el motivo de un rechazo. */
export const textAreaClass = `${field} min-h-28 py-3`;

export const errorClass = 'mt-2 text-lg font-semibold text-alerta';

/** Una tarjeta blanca sobre el fondo azul suave: sin borde, con una sombra leve. */
export const cardClass = 'rounded-2xl bg-white shadow-tarjeta';

/** En las pantallas de entrada los campos son más altos y llevan su icono adentro, a la izquierda. */
export const iconFieldClass = 'h-14 w-full rounded-2xl border border-linea bg-white pl-12 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none';

/** El botón de las pantallas de entrada, de la altura de sus campos. */
export const bigButtonClass = 'flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-ipuc px-4 text-lg font-semibold text-white shadow-tarjeta active:bg-ipuc-oscuro disabled:opacity-60';

const button = 'flex h-12 w-full items-center justify-center rounded-xl px-4 text-center text-lg font-semibold disabled:opacity-60';

export const buttonClass = {
    primary: `${button} bg-ipuc text-white shadow-tarjeta active:bg-ipuc-oscuro`,
    secondary: `${button} border-2 border-ipuc bg-white text-ipuc`,
    danger: `${button} bg-alerta text-white`,
};
