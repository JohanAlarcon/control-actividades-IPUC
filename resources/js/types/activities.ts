/** Una clase de actividad, como la ofrece el formulario de propuesta. */
export interface ActivityTypeOption {
    id: number;
    name: string;
}

/** Una clase, como la ve el pastor al administrarlas (D-25). */
export interface ManagedActivityType extends ActivityTypeOption {
    active: boolean;
    uses: number;
}
