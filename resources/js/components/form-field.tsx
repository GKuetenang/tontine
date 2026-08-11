import type { PropsWithChildren } from 'react';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
    FieldLegend,
    FieldSet,
} from '@/components/ui/field';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Props = PropsWithChildren<{
    htmlFor?: string;
    label?: string;
    help?: string;
    error?: string;
    className?: string;
    required?: boolean
    optional?: boolean
}>;

export function FormField({
    children,
    htmlFor,
    label,
    help,
    error,
    className,
    required,
    optional,
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
                <Label asChild className={cn(required && 'required', optional && 'optional')}>
                    <FieldLegend className="mb-0!">{label}</FieldLegend>
                </Label>
                {content}
            </FieldSet>
        );
    }

    return (
        <Field className={className} data-invalid={!!error}>
            {label && <FieldLabel className={cn(required && 'required', optional && 'optional')} htmlFor={htmlFor}>{label}</FieldLabel>}
            {content}
        </Field>
    );
}
