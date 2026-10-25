/** Las clases que se repiten. Todo control mide 48 px de alto y su texto 18 px (principio II). */
export const fieldClass =
    'mt-2 h-12 w-full rounded-lg border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none';

export const errorClass = 'mt-2 text-lg font-semibold text-alerta';

const button = 'flex h-12 w-full items-center justify-center rounded-lg px-4 text-center text-lg font-semibold disabled:opacity-60';

export const buttonClass = {
    primary: `${button} bg-ipuc text-white active:bg-ipuc-oscuro`,
    secondary: `${button} border-2 border-ipuc bg-white text-ipuc`,
    danger: `${button} bg-alerta text-white`,
};
