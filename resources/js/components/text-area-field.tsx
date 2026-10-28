import { errorClass, textAreaClass } from '@/lib/styles';
import type { TextareaHTMLAttributes } from 'react';

interface Props extends Omit<TextareaHTMLAttributes<HTMLTextAreaElement>, 'className'> {
    id: string;
    label: string;
    error?: string;
}

export default function TextAreaField({ id, label, error, ...props }: Props) {
    return (
        <div>
            <label htmlFor={id} className="block text-lg font-semibold">
                {label}
            </label>
            <textarea id={id} rows={4} aria-invalid={error ? true : undefined} className={textAreaClass} {...props} />
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
