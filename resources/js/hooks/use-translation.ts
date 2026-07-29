import { usePage } from '@inertiajs/react';

type TranslationValues = Record<string, string | number>;

interface SharedProps {
    translations: Record<string, unknown>;
}

function resolveTranslation(
    translations: Record<string, unknown>,
    key: string,
): unknown {
    return key.split('.').reduce<unknown>((value, segment) => {
        if (
            typeof value !== 'object' ||
            value === null ||
            !(segment in value)
        ) {
            return undefined;
        }

        return (value as Record<string, unknown>)[segment];
    }, translations);
}

export function useTranslation() {
    const { translations } = usePage<SharedProps>().props;

    console.log('Translations:', translations); // Debugging line to check the translations object

    function t(
        key: string,
        replacements: TranslationValues = {},
    ): string {
        const translation = resolveTranslation(translations, key);

        if (typeof translation !== 'string') {
            return key;
        }

        return Object.entries(replacements).reduce(
            (message, [name, value]) =>
                message
                    .replaceAll(`:${name}`, String(value))
                    .replaceAll(
                        `:${name.charAt(0).toUpperCase()}${name.slice(1)}`,
                        String(value),
                    ),
            translation,
        );
    }

    return { t };
}