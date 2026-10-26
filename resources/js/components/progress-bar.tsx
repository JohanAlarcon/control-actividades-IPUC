/** Una barra de avance o de cumplimiento, de 0 a 100, que también se lee con lector de pantalla. */
export default function ProgressBar({ value, label, size = 'md' }: { value: number; label: string; size?: 'sm' | 'md' }) {
    return (
        <div
            role="progressbar"
            aria-valuenow={value}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-label={label}
            className={`overflow-hidden rounded-full bg-ipuc/10 ${size === 'sm' ? 'h-2' : 'h-3'}`}
        >
            <div className="h-full rounded-full bg-ipuc" style={{ width: `${Math.min(100, Math.max(0, value))}%` }} />
        </div>
    );
}
