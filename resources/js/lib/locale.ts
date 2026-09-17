import { enUS, frCA } from 'date-fns/locale';

import type { AcceptedLocales } from '@/types';

import type { Locale } from 'date-fns/locale';

export function getDateFnsLocale(locale: AcceptedLocales): Locale {
    switch (locale) {
        case 'fr':
            return frCA;

            break;
        case 'en':
            return enUS;

            break;
        default:
            return enUS;

            break;
    }

    return enUS;
}
