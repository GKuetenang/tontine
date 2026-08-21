import { format, isValid, parseISO } from "date-fns";
import { frCA } from "date-fns/locale";

export function parseDate(
    value?: string | null,
): Date | undefined {
    if (!value) {
        return undefined;
    }

    const date = parseISO(value);

    return isValid(date)
        ? date
        : undefined;
}

export function formatDate(value?: string | null): string {
    if (!value) {
        return '—';
    }

    const date = parseISO(value);

    if (!isValid(date)) {
        return '—';
    }

    return format(date, "d MMM yyyy 'à' HH:mm", {
        locale: frCA,
    });
}