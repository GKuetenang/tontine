import { forwardRef } from 'react';

import type {
    DateTimePickerProps,
    DateTimePickerRef,
} from '@/components/ui/datetime-picker';
import { DateTimePicker } from '@/components/ui/datetime-picker';
import { useTranslation } from '@/hooks/use-translation';
import { getDateFnsLocale } from '@/lib/locale';

export type AppDateTimePickerProps = Omit<
    DateTimePickerProps,
    'locale' | 'weekStartsOn'
>;

const AppDateTimePicker = forwardRef<
    Partial<DateTimePickerRef>,
    AppDateTimePickerProps
>((props, ref) => {
    const { locale } = useTranslation();

    return (
        <DateTimePicker
            {...props}
            ref={ref}
            weekStartsOn={1}
            locale={getDateFnsLocale(locale)}
        />
    );
});

AppDateTimePicker.displayName = 'AppDateTimePicker';

export { AppDateTimePicker };
export default AppDateTimePicker;
