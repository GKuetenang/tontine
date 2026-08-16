import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SessionParticipantStatusBadge } from '@/components/session-participant-status-badge';
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
import { useAuthorization } from '@/hooks/use-authorization';
import { withAppLayout } from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';
import sessionParticipants from '@/routes/tontines/sessions/participants';
import type { BreadcrumbItem, PaginatedCollection, ResultTontine, Session, SessionParticipant } from '@/types';
import { Form, Head } from '@inertiajs/react';
import { format, isValid, parseISO } from 'date-fns';
import { frCA } from 'date-fns/locale';
import { PlusIcon } from 'lucide-react';
import { Actions } from './actions';
import { EditSessionParticipantForm } from './form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
    {
        title: 'Sessions',
        href: sessionParticipants.index({
            tontine: '',
            session: ''
        }).url,
    },
    {
        title: 'Participants',
        href: '#',
    },
];

function formatSessionDate(value?: string | null): string {
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

type Props = {
    collection: PaginatedCollection<SessionParticipant>;
    q: string | null;
    tontine: ResultTontine;
    sessionParticipant: SessionParticipant;
    session: Session;
};

export default withAppLayout(
    breadcrumbs,
    ({ collection, q, tontine, sessionParticipant, session }: Props) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Tous les participants" />
                <Heading title="Tous les participants" />
                <div className="space-y-4">
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex justify-between items-center">
                                {can('session-participants.create') && (
                                    <EditSessionParticipantForm
                                        participant={sessionParticipant}
                                        tontine={tontine}
                                        session={session}
                                        trigger={
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="w-fit"
                                            >
                                                <PlusIcon />
                                                Ajouter un participant
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...sessionParticipants.index.form({
                                        tontine: tontine.slug,
                                        session: ''
                                    })}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher un membre"
                                        name="q"
                                    />
                                    <Button>Rechercher</Button>
                                </Form>
                            </div>
                        </CardHeader>
                        <CardContent className="px-0">
                            <Table className="border-spacing-4">
                                <TableHeader>
                                    <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                                        <SortableTableHead field="name">
                                            Nom
                                        </SortableTableHead>
                                        <SortableTableHead field="default_contibution_amount">
                                            Montant de tontine
                                        </SortableTableHead>
                                        <SortableTableHead field="draw_entries_count">
                                            Parts
                                        </SortableTableHead>
                                        <SortableTableHead field="joined_at">
                                            A rejoint le
                                        </SortableTableHead>
                                        <TableHead>Statut</TableHead>
                                        <TableHead className="text-end"></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {collection.data.map((item) => (
                                        <TableRow
                                            key={item.id}
                                            className="[&>td:first-child]:pl-6 [&>td:last-child]:pr-6"
                                        >
                                            <TableCell>{item.membership?.user?.name}</TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    item.contribution_amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {item.draw_entries_count}
                                            </TableCell>
                                            <TableCell>
                                                {formatSessionDate(item.joined_at)}
                                            </TableCell>
                                            <TableCell>
                                                <SessionParticipantStatusBadge
                                                    isActive={item.is_active}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <Actions
                                                    tontine={tontine}
                                                    session={session}
                                                    participant={item}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}
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
