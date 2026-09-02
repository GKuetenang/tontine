import { Form, Head, Link } from '@inertiajs/react';
import {
    CalendarRangeIcon,
    ClockIcon,
    MapPinIcon,
    PencilIcon,
    PlusIcon,
    RepeatIcon,
    SearchIcon,
} from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { MeetingStatusBadge } from '@/components/meeting-status-badge';
import type { SelectOption } from '@/components/select-with-items';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { useAuthorization } from '@/hooks/use-authorization';
import { withAppLayout } from '@/layouts/app-layout';

import { formatDate } from '@/lib';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import meetings from '@/routes/groups/sessions/meetings';

import type {
    BreadcrumbItem,
    Meeting,
    MeetingSchedule,
    PaginatedCollection,
    Session,
    Group,
} from '@/types';

import { Actions } from './actions';
import { EditMeetingForm } from './form';
import { MeetingScheduleForm } from './schedule-form';

type Props = {
    collection: PaginatedCollection<Meeting>;
    q: string | null;
    group: Group;
    session: Session;
    meeting: Meeting;
    meeting_schedule: MeetingSchedule | null;
    meeting_recurrences: SelectOption[];
    meeting_monthly_patterns: SelectOption[];
    timezones: SelectOption[];
};

export default withAppLayout<Props>(
    ({ group, session }) =>
        [
            {
                title: 'Réunions',
                href: groups.index(),
            },
            {
                title: group.name,
                href: groups.show({
                    group: group.slug!,
                }),
            },
            {
                title: 'Sessions',
                href: sessions.index({
                    group: group.slug!,
                }),
            },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug!,
                    session: session.slug,
                }),
            },
            {
                title: 'Assises',
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({
        collection,
        q,
        group,
        session,
        meeting,
        meeting_schedule: schedule,
        meeting_recurrences: recurrences,
        meeting_monthly_patterns: monthlyPatterns,
        timezones,
    }: Props) => {
        const { can } = useAuthorization();
        const recurrenceInterval = Number(
            schedule?.rrule.match(/INTERVAL=(\d+)/)?.[1] ?? 1,
        );
        const recurrenceDescription =
            schedule?.recurrence === 'monthly'
                ? recurrenceInterval === 1
                    ? 'Chaque mois'
                    : `Tous les ${recurrenceInterval} mois`
                : recurrenceInterval === 1
                  ? 'Chaque semaine'
                  : `Toutes les ${recurrenceInterval} semaines`;
        const monthlyPatternLabel = monthlyPatterns.find(
            (option) =>
                option.value ===
                (schedule?.rrule.includes('BYDAY=')
                    ? 'weekday_ordinal'
                    : 'day_of_month'),
        )?.label;

        return (
            <>
                <Head title="Assises" />

                <Heading
                    title="Assises"
                    description={`Assises de la session ${session.name}`}
                />

                <div className="space-y-4">
                    <Card>
                        <CardHeader className="flex items-start justify-between gap-4">
                            <div className="space-y-1.5">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <CalendarRangeIcon className="size-5" />
                                    Calendrier des assises
                                </CardTitle>
                                <CardDescription>
                                    {schedule
                                        ? 'Les assises récurrentes de cette session ont été générées.'
                                        : 'Définissez une récurrence pour générer les assises de toute la session.'}
                                </CardDescription>
                            </div>

                            {session.status === 'draft' &&
                                can('meetings.create') && (
                                    <MeetingScheduleForm
                                        group={group}
                                        session={session}
                                        recurrences={recurrences}
                                        monthlyPatterns={monthlyPatterns}
                                        timezones={timezones}
                                        schedule={schedule}
                                        trigger={
                                            <Button
                                                type="button"
                                                className="w-fit"
                                                variant="outline"
                                            >
                                                {schedule ? (
                                                    <PencilIcon />
                                                ) : (
                                                    <RepeatIcon />
                                                )}
                                                {schedule
                                                    ? 'Modifier'
                                                    : 'Configurer'}
                                            </Button>
                                        }
                                    />
                                )}
                        </CardHeader>

                        {schedule && (
                            <CardContent className="grid gap-4 border-t pt-5 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Récurrence
                                    </p>
                                    <Badge variant="secondary" className="mt-1">
                                        <RepeatIcon />
                                        {recurrenceDescription}
                                    </Badge>
                                    {schedule.recurrence === 'monthly' && (
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {monthlyPatternLabel}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Première assise
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {formatDate(schedule.starts_at)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Lieu par défaut
                                    </p>
                                    <p className="mt-1 flex items-center gap-1.5 font-medium">
                                        <MapPinIcon className="size-4 text-muted-foreground" />
                                        {schedule.default_location ??
                                            'Non défini'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Durée et fuseau
                                    </p>
                                    <p className="mt-1 flex items-center gap-1.5 font-medium">
                                        <ClockIcon className="size-4 text-muted-foreground" />
                                        {schedule.default_duration_minutes} min
                                        · {schedule.timezone}
                                    </p>
                                </div>
                            </CardContent>
                        )}
                    </Card>

                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    {can('meetings.create') && (
                                        <EditMeetingForm
                                            meeting={meeting}
                                            group={group}
                                            session={session}
                                            trigger={
                                                <Button
                                                    type="button"
                                                    className="w-fit"
                                                >
                                                    <PlusIcon />
                                                    Ajouter une assise
                                                </Button>
                                            }
                                        />
                                    )}
                                </div>

                                <Form
                                    {...meetings.index.form({
                                        group: group.slug!,
                                        session: session.slug,
                                    })}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher une assise"
                                        name="q"
                                    />

                                    <Button variant="outline">
                                        <SearchIcon />
                                        Rechercher
                                    </Button>
                                </Form>
                            </div>
                        </CardHeader>

                        <CardContent className="px-0">
                            <Table className="border-spacing-4">
                                <TableHeader>
                                    <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                                        <SortableTableHead field="number">
                                            N°
                                        </SortableTableHead>

                                        <SortableTableHead field="title">
                                            Assise
                                        </SortableTableHead>

                                        <SortableTableHead field="scheduled_at">
                                            Date prévue
                                        </SortableTableHead>

                                        <SortableTableHead field="location">
                                            Lieu
                                        </SortableTableHead>

                                        <TableHead>Durée</TableHead>

                                        <SortableTableHead field="status">
                                            Statut
                                        </SortableTableHead>

                                        <TableHead>Ouverture</TableHead>

                                        <TableHead>Clôture</TableHead>

                                        <TableHead className="text-end" />
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    {collection.data.map((item) => (
                                        <TableRow
                                            key={item.id}
                                            className="[&>td:first-child]:pl-6 [&>td:last-child]:pr-6"
                                        >
                                            <TableCell className="font-medium">
                                                #{item.number}
                                            </TableCell>

                                            <TableCell>
                                                <div className="flex flex-col gap-1">
                                                    <Link
                                                        href={meetings.show({
                                                            group: group.slug!,
                                                            session:
                                                                session.slug,
                                                            meeting: item.slug,
                                                        })}
                                                        className="font-medium hover:underline"
                                                    >
                                                        {item.title}
                                                    </Link>

                                                    {item.description && (
                                                        <span className="line-clamp-1 max-w-sm text-xs text-muted-foreground">
                                                            {item.description}
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                {formatDate(item.scheduled_at)}
                                            </TableCell>

                                            <TableCell>
                                                {item.location ? (
                                                    <div className="flex items-center gap-1.5">
                                                        <MapPinIcon className="size-4 text-muted-foreground" />

                                                        <span>
                                                            {item.location}
                                                        </span>
                                                    </div>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>

                                            <TableCell>
                                                {item.duration_minutes
                                                    ? `${item.duration_minutes} min`
                                                    : '—'}
                                            </TableCell>

                                            <TableCell>
                                                <MeetingStatusBadge
                                                    meeting={item}
                                                />
                                            </TableCell>

                                            <TableCell>
                                                {formatDate(item.opened_at)}
                                            </TableCell>

                                            <TableCell>
                                                {formatDate(item.closed_at)}
                                            </TableCell>

                                            <TableCell className="text-end">
                                                <Actions
                                                    group={group}
                                                    session={session}
                                                    meeting={item}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}

                                    {collection.data.length === 0 && (
                                        <TableRow>
                                            <TableCell
                                                colSpan={9}
                                                className="h-32 text-center text-muted-foreground"
                                            >
                                                {q
                                                    ? `Aucune assise ne correspond à la recherche « ${q} ».`
                                                    : 'Aucune assise enregistrée.'}
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>

                            <CollectionPagination
                                className="px-6 pt-6"
                                collection={collection}
                            />
                        </CardContent>
                    </Card>
                </div>
            </>
        );
    },
);
