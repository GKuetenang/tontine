import { AcceptedLocales } from "@/types";
import { enUS, frCA, Locale } from "date-fns/locale";

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