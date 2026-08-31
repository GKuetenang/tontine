import { MeetingStatusBadge } from '@/components//meeting-status-badge';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Form, Head, Link } from '@inertiajs/react';
import { MapPinIcon, PlusIcon, SearchIcon } from 'lucide-react';

import { useAuthorization } from '@/hooks/use-authorization';
import { withAppLayout } from '@/layouts/app-layout';

import { formatDate } from '@/lib';
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import meetings from '@/routes/tontines/sessions/meetings';

import type {
    BreadcrumbItem,
    Meeting,
    PaginatedCollection,
    Session,
    Tontine,
} from '@/types';

import { Actions } from './actions';
import { EditMeetingForm } from './form';

type Props = {
    collection: PaginatedCollection<Meeting>;
    q: string | null;
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export default withAppLayout<Props>(
    ({ tontine, session }) =>
        [
            {
                title: 'Tontines',
                href: tontines.index(),
            },
            {
                title: tontine.name,
                href: tontines.show({
                    tontine: tontine.slug!,
                }),
            },
            {
                title: 'Sessions',
                href: sessions.index({
                    tontine: tontine.slug!,
                }),
            },
            {
                title: session.name,
                href: sessions.show({
                    tontine: tontine.slug!,
                    session: session.slug,
                }),
            },
            {
                title: 'Réunions',
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({ collection, q, tontine, session, meeting }: Props) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Réunions" />

                <Heading
                    title="Réunions"
                    description={`Réunions de la session ${session.name}`}
                />

                <div className="space-y-4">
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    {can('meetings.create') && (
                                        <EditMeetingForm
                                            meeting={meeting}
                                            tontine={tontine}
                                            session={session}
                                            trigger={
                                                <Button
                                                    type="button"
                                                    className="w-fit"
                                                >
                                                    <PlusIcon />
                                                    Ajouter une réunion
                                                </Button>
                                            }
                                        />
                                    )}
                                </div>

                                <Form
                                    {...meetings.index.form({
                                        tontine: tontine.slug!,
                                        session: session.slug,
                                    })}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher une réunion"
                                        name="q"
                                    />

                                    <Button variant="outline"><SearchIcon />Rechercher</Button>
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
                                            Réunion
                                        </SortableTableHead>

                                        <SortableTableHead field="scheduled_at">
                                            Date prévue
                                        </SortableTableHead>

                                        <SortableTableHead field="location">
                                            Lieu
                                        </SortableTableHead>

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
                                                            tontine:
                                                                tontine.slug!,
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
                                                    tontine={tontine}
                                                    session={session}
                                                    meeting={item}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}

                                    {collection.data.length === 0 && (
                                        <TableRow>
                                            <TableCell
                                                colSpan={8}
                                                className="h-32 text-center text-muted-foreground"
                                            >
                                                Aucune réunion trouvée.
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
