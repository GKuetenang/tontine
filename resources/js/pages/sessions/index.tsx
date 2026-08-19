import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SelectOption } from '@/components/select-with-items';
import { SessionStatusBadge } from '@/components/session-status-badge';
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
import sessions from '@/routes/tontines/sessions';
import draws from '@/routes/tontines/sessions/draw';
import participants from '@/routes/tontines/sessions/participants';
import type { BreadcrumbItem, PaginatedCollection, Session } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';
import { format, isValid, parseISO } from 'date-fns';
import { frCA } from 'date-fns/locale';
import { PlusIcon, ShuffleIcon, UsersIcon } from 'lucide-react';
import { Actions } from './actions';
import { EditSessionForm } from './form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
    {
        title: 'Sessions',
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

export type ResultTontine = {
    id: number;
    name: string;
    slug: string;
};

type Props = {
    collection: PaginatedCollection<Session>;
    q: string | null;
    tontine: ResultTontine;
    session: Session;
    draw_allocation_modes: SelectOption[];
};

export default withAppLayout(
    breadcrumbs,
    ({ collection, q, tontine, session, draw_allocation_modes }: Props) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Tous les sessions" />
                <Heading title="Tous les sessions" />
                <div className="space-y-4">
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex justify-between items-center">
                                {can('sessions.create') && (
                                    <EditSessionForm
                                        draw_allocation_modes={draw_allocation_modes}
                                        session={session}
                                        tontine={tontine}
                                        trigger={
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="w-fit"
                                            >
                                                <PlusIcon />
                                                Ajouter une session
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...sessions.index.form({
                                        tontine: tontine.slug,
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
                                            Montant par defaut
                                        </SortableTableHead>
                                        <SortableTableHead field='draw_allocation_mode'>
                                            Mode d’attribution des tours
                                        </SortableTableHead>
                                        <SortableTableHead field="start_at">
                                            Date de début
                                        </SortableTableHead>
                                        <SortableTableHead field="end_at">
                                            Date de fin
                                        </SortableTableHead>
                                        <TableHead>Statut</TableHead>
                                        <TableHead>Participants</TableHead>
                                        <TableHead>Tirage</TableHead>
                                        <TableHead className="text-end"></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {collection.data.map((item) => (
                                        <TableRow
                                            key={item.id}
                                            className="[&>td:first-child]:pl-6 [&>td:last-child]:pr-6"
                                        >
                                            <TableCell>{item.name}</TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    item.default_contribution_amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {item.draw_allocation_mode_label}
                                            </TableCell>
                                            <TableCell>
                                                {formatSessionDate(
                                                    item.start_at,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {formatSessionDate(item.end_at)}
                                            </TableCell>
                                            <TableCell>
                                                <SessionStatusBadge
                                                    session={item}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={participants.index(
                                                            {
                                                                tontine:
                                                                    tontine.slug!,
                                                                session:
                                                                    item.slug,
                                                            },
                                                        )}
                                                    >
                                                        <UsersIcon size={16} />
                                                        {`${item.participants_count} participant${item.participants_count! > 1 ? 's' : ''}`}
                                                    </Link>
                                                </Button>
                                                {/* <span>{`${item.participants_count} participant${item.participants_count! > 1 ? 's' : ''}`}</span> */}
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={draws.show(
                                                            {
                                                                tontine:
                                                                    tontine.slug!,
                                                                session:
                                                                    item.slug,
                                                            },
                                                        )}
                                                    >
                                                        <ShuffleIcon size={16} />
                                                        Tirage
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                            <TableCell>
                                                <Actions
                                                    draw_allocation_modes={draw_allocation_modes}
                                                    tontine={tontine}
                                                    session={item}
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
