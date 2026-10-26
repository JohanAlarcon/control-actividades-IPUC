/**
 * El logo oficial de la IPUC es blanco, asi que no se ve sobre fondo claro.
 * Por eso este componente trae su propia banda azul detras.
 */
export default function IpucLogo() {
    return (
        <div className="flex justify-center bg-linear-to-br from-ipuc to-ipuc-oscuro px-4 py-6">
            <img
                src="/img/logo-ipuc.webp"
                alt="Iglesia Pentecostal Unida de Colombia"
                width={180}
                height={125}
                className="w-[180px]"
            />
        </div>
    );
}
