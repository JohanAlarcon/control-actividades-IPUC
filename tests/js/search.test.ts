import { matchesEveryWord, normalize } from '@/lib/search';
import { describe, expect, it } from 'vitest';

describe('normalize: para comparar sin importar cómo se escribió', () => {
    it('quita las tildes y las mayúsculas', () => {
        expect(normalize('Comité de Damas Dorcas')).toBe('comite de damas dorcas');
    });

    it('la ñ queda como n, para quien no la encuentra en el teclado', () => {
        expect(normalize('Ñoño Muñoz')).toBe('nono munoz');
    });
});

describe('matchesEveryWord: el buscador de cuentas', () => {
    const cuenta = 'Hermana Luz Mary Quintero secretaria.alabanza Secretario Comité de Alabanza';

    it('sin nada escrito, todas las cuentas aparecen', () => {
        expect(matchesEveryWord(cuenta, '')).toBe(true);
    });

    it('encuentra sin tildes ni mayúsculas', () => {
        expect(matchesEveryWord(cuenta, 'COMITE')).toBe(true);
    });

    it('cada palabra escrita tiene que estar, en cualquier orden', () => {
        expect(matchesEveryWord(cuenta, 'alabanza luz')).toBe(true);
        expect(matchesEveryWord(cuenta, 'luz damas')).toBe(false);
    });

    it('los espacios de más no estorban', () => {
        expect(matchesEveryWord(cuenta, '  luz   mary  ')).toBe(true);
    });

    it('encuentra con un pedazo de la palabra', () => {
        expect(matchesEveryWord(cuenta, 'quin')).toBe(true);
    });
});
