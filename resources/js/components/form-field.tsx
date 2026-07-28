import type { PropsWithChildren } from 'react';
import { Label } from '@/components/ui/label';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
    FieldLegend,
    FieldSet,
} from '@/components/ui/field';

type Props = PropsWithChildren<{
    htmlFor?: string;
    label?: string;
    help?: string;
    error?: string;
    className?: string;
}>;

export function FormField({
    children,
    htmlFor,
    label,
    help,
    error,
    className,
}: Props) {
    const content = (
        <>
            {children}
            {error && <FieldError>{error}</FieldError>}
            {help && <FieldDescription>{help}</FieldDescription>}
        </>
    );

    if (!htmlFor) {
        return (
            <FieldSet className={className} data-invalid={!!error}>
                <Label asChild>
                    <FieldLegend className="mb-0!">{label}</FieldLegend>
                </Label>
                {content}
            </FieldSet>
        );
    }

    return (
        <Field className={className} data-invalid={!!error}>
            {label && <FieldLabel htmlFor={htmlFor}>{label}</FieldLabel>}
            {content}
        </Field>
    );
}
