import { Form } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarRangeIcon, SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useMemo, useState } from 'react';
import { rrulestr } from 'rrule';

import { FormField } from '@/components/form-field';
import type { SelectOption } from '@/components/select-with-items';
import { SelectWithItems } from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
import { DateTimePicker } from '@/components/ui/datetime-picker';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { parseDate } from '@/lib';
import meetingSchedule from '@/routes/groups/sessions/meeting-schedule';

import type { MeetingSchedule, Session, Group } from '@/types';

type Props = {
    trigger: ReactElement;
    group: Group;
    session: Session;
    recurrences: SelectOption[];
    timezones: SelectOption[];
    monthlyPatterns: SelectOption[];
    schedule?: MeetingSchedule | null;
};

function sessionDate(value: unknown): Date | undefined {
    return parseDate(typeof value === 'string' ? value : null);
}

function intervalFromRRule(rrule?: string): number {
    const value = Number(rrule?.match(/INTERVAL=([0-9]+)/)?.[1] ?? 1);

    return Number.isInteger(value) && value > 0 ? value : 1;
}

function asFloatingUtc(date: Date): Date {
    return new Date(
        Date.UTC(
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
            date.getHours(),
            date.getMinutes(),
            date.getSeconds(),
        ),
    );
}

function buildRRule(
    first: Date,
    recurrence: string,
    monthlyPattern: string,
    interval: number,
): string {
    if (recurrence === 'weekly') {
        return `FREQ=WEEKLY;INTERVAL=${interval}`;
    }

    if (monthlyPattern === 'day_of_month') {
        const lastDayOfMonth = new Date(
            first.getFullYear(),
            first.getMonth() + 1,
            0,
        ).getDate();

        if (first.getDate() === lastDayOfMonth) {
            return `FREQ=MONTHLY;INTERVAL=${interval};BYMONTHDAY=-1`;
        }

        return `FREQ=MONTHLY;INTERVAL=${interval}`;
    }

    const ordinal = Math.floor((first.getDate() - 1) / 7) + 1;
    const weekday = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][first.getDay()];

    return `FREQ=MONTHLY;INTERVAL=${interval};BYDAY=${ordinal}${weekday}`;
}

export function MeetingScheduleForm({
    trigger,
    group,
    session,
    recurrences,
    timezones,
    monthlyPatterns,
    schedule,
}: Props) {
    const [open, setOpen] = useState(false);
    const isEditing = Boolean(schedule);
    const [recurrence, setRecurrence] = useState(
        schedule?.recurrence ?? recurrences[0]?.value ?? 'monthly',
    );
    const [interval, setInterval] = useState(() =>
        intervalFromRRule(schedule?.rrule),
    );
    const [monthlyPattern, setMonthlyPattern] = useState(
        (schedule?.rrule.includes('FREQ=MONTHLY') &&
        schedule.rrule.includes('BYDAY=')
            ? 'weekday_ordinal'
            : undefined) ??
            monthlyPatterns[0]?.value ??
            'day_of_month',
    );
    const [startsAt, setStartsAt] = useState<Date | undefined>(() =>
        sessionDate(schedule?.starts_at ?? session.start_at),
    );
    const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const defaultTimezone =
        schedule?.timezone ??
        (timezones.some((option) => option.value === browserTimezone)
            ? browserTimezone
            : (timezones[0]?.value ?? 'UTC'));
    const [timezone, setTimezone] = useState(defaultTimezone);
    const handleOpenChange = (value: boolean) => {
        if (value) {
            setRecurrence(
                schedule?.recurrence ?? recurrences[0]?.value ?? 'monthly',
            );
            setInterval(intervalFromRRule(schedule?.rrule));
            setMonthlyPattern(
                schedule?.rrule.includes('FREQ=MONTHLY') &&
                    schedule.rrule.includes('BYDAY=')
                    ? 'weekday_ordinal'
                    : (monthlyPatterns[0]?.value ?? 'day_of_month'),
            );
            setStartsAt(sessionDate(schedule?.starts_at ?? session.start_at));
            setTimezone(schedule?.timezone ?? defaultTimezone);
        }

        setOpen(value);
    };
    const action = isEditing
        ? meetingSchedule.update.form({
              group: group.slug!,
              session: session.slug,
          })
        : meetingSchedule.store.form({
              group: group.slug!,
              session: session.slug,
          });

    const occurrences = useMemo(() => {
        const sessionEnd = sessionDate(session.end_at);

        if (!startsAt || !sessionEnd) {
            return [];
        }

        const dtstart = format(startsAt, "yyyyMMdd'T'HHmmss");
        const rrule = buildRRule(
            startsAt,
            recurrence,
            monthlyPattern,
            interval,
        );
        const rule = rrulestr(`DTSTART:${dtstart}Z\nRRULE:${rrule}`);

        return rule
            .between(asFloatingUtc(startsAt), asFloatingUtc(sessionEnd), true)
            .slice(0, 250);
    }, [interval, monthlyPattern, recurrence, session.end_at, startsAt]);

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent
                className="sm:max-w-2xl"
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...action}
                    resetOnSuccess
                    onBefore={() =>
                        confirm(
                            'Voulez-vous ' +
                                (isEditing ? 'mettre à jour ' : 'générer ') +
                                occurrences.length +
                                ' assise(s) pour cette session ?',
                        )
                    }
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-5">
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Modifier le calendrier'
                                        : 'Configurer les assises'}
                                </DialogTitle>
                                <DialogDescription>
                                    La configuration sera appliquée globalement
                                    aux assises générées entre le début et la
                                    fin de la session.
                                </DialogDescription>
                            </DialogHeader>

                            {errors['schedule'] && (
                                <p className="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                                    {errors['schedule']}
                                </p>
                            )}

                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField
                                    error={errors['recurrence']}
                                    label="Récurrence"
                                    htmlFor="recurrence"
                                    required
                                >
                                    <SelectWithItems
                                        id="recurrence"
                                        name="recurrence"
                                        items={recurrences}
                                        defaultValue={recurrence}
                                        onValueChange={setRecurrence}
                                        placeholder="Choisir une récurrence"
                                    />
                                </FormField>

                                <FormField
                                    error={errors['interval']}
                                    label="Répéter tous les"
                                    htmlFor="interval"
                                    required
                                >
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="interval"
                                            name="interval"
                                            type="number"
                                            min={1}
                                            max={60}
                                            value={interval}
                                            onChange={(event) =>
                                                setInterval(
                                                    Math.max(
                                                        1,
                                                        Number(
                                                            event.target.value,
                                                        ) || 1,
                                                    ),
                                                )
                                            }
                                        />
                                        <span className="min-w-24 text-sm text-muted-foreground">
                                            {recurrence === 'monthly'
                                                ? 'mois'
                                                : 'semaine(s)'}
                                        </span>
                                    </div>
                                </FormField>

                                {recurrence === 'monthly' && (
                                    <FormField
                                        error={errors['monthly_pattern']}
                                        label="Répétition mensuelle"
                                        htmlFor="monthly_pattern"
                                        required
                                    >
                                        <SelectWithItems
                                            id="monthly_pattern"
                                            name="monthly_pattern"
                                            items={monthlyPatterns}
                                            defaultValue={monthlyPattern}
                                            onValueChange={setMonthlyPattern}
                                            placeholder="Choisir la règle mensuelle"
                                        />
                                    </FormField>
                                )}

                                <FormField
                                    error={errors['timezone']}
                                    label="Fuseau horaire"
                                    htmlFor="timezone"
                                    required
                                >
                                    <SelectWithItems
                                        id="timezone"
                                        name="timezone"
                                        items={timezones}
                                        defaultValue={defaultTimezone}
                                        onValueChange={setTimezone}
                                        placeholder="Choisir un fuseau horaire"
                                    />
                                </FormField>

                                <FormField
                                    error={errors['starts_at']}
                                    label="Première assise"
                                    htmlFor="starts_at"
                                    required
                                >
                                    <input
                                        type="hidden"
                                        name="starts_at"
                                        value={
                                            startsAt
                                                ? format(
                                                      startsAt,
                                                      'yyyy-MM-dd HH:mm:ss',
                                                  )
                                                : ''
                                        }
                                    />
                                    <DateTimePicker
                                        granularity="minute"
                                        className="text-foreground"
                                        value={startsAt}
                                        onChange={setStartsAt}
                                        placeholder="Choisir la date et l’heure"
                                    />
                                </FormField>

                                <FormField
                                    error={errors['default_duration_minutes']}
                                    label="Durée (minutes)"
                                    htmlFor="default_duration_minutes"
                                    required
                                >
                                    <Input
                                        id="default_duration_minutes"
                                        name="default_duration_minutes"
                                        type="number"
                                        min={15}
                                        max={1440}
                                        defaultValue={
                                            schedule?.default_duration_minutes ??
                                            120
                                        }
                                    />
                                </FormField>

                                <FormField
                                    error={errors['default_title']}
                                    label="Titre par défaut"
                                    htmlFor="default_title"
                                    required
                                >
                                    <Input
                                        id="default_title"
                                        name="default_title"
                                        defaultValue={
                                            schedule?.default_title ?? 'Assise'
                                        }
                                    />
                                </FormField>

                                <FormField
                                    error={errors['default_location']}
                                    label="Lieu par défaut"
                                    htmlFor="default_location"
                                    optional
                                >
                                    <Input
                                        id="default_location"
                                        name="default_location"
                                        defaultValue={
                                            schedule?.default_location ?? ''
                                        }
                                        placeholder="Ex. Siège de l’association"
                                    />
                                </FormField>
                            </div>

                            <div className="rounded-lg border bg-muted/30 p-4">
                                <div className="flex items-center gap-2 font-medium">
                                    <CalendarRangeIcon className="size-4" />
                                    Aperçu : {occurrences.length} assise(s)
                                    <span className="text-xs font-normal text-muted-foreground">
                                        ({timezone})
                                    </span>
                                </div>
                                <div className="mt-3 grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
                                    {occurrences.slice(0, 6).map((date) => (
                                        <span key={date.toISOString()}>
                                            {new Intl.DateTimeFormat('fr-FR', {
                                                dateStyle: 'short',
                                                timeStyle: 'short',
                                                timeZone: 'UTC',
                                            }).format(date)}
                                        </span>
                                    ))}
                                </div>
                                {occurrences.length > 6 && (
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        Et {occurrences.length - 6} autre(s)
                                        assise(s)…
                                    </p>
                                )}
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Annuler
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    disabled={
                                        processing || occurrences.length === 0
                                    }
                                >
                                    {processing ? <Spinner /> : <SaveIcon />}
                                    {isEditing
                                        ? 'Mettre à jour le calendrier'
                                        : 'Générer les assises'}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
