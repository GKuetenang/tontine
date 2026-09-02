import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import type { SelectOption } from '@/components/select-with-items';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Badge } from '@/components/ui/badge';
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
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type {
    BreadcrumbItem,
    Donation,
    PaginatedCollection,
    Session,
    Group,
} from '@/types';
import { CreateDonationForm } from './form';

type Props = {
    group: Group;
    session: Session;
    collection: PaginatedCollection<Donation>;
    statuses: SelectOption[];
    q: string | null;
};

export default withAppLayout<Props>(
    ({ group, session }) =>
        [
            { title: 'Réunions', href: groups.index() },
            {
                title: group.name,
                href: groups.show({ group: group.slug! }),
            },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug!,
                    session: session.slug,
                }),
            },
            { title: 'Dons', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, session, collection, statuses, q }) => {
        const { can } = useAuthorization();
        const routeParameters = {
            group: group.slug!,
            session: session.slug,
        };
        const statusLabels = Object.fromEntries(
            statuses.map((option) => [option.value, option.label]),
        );

        return (
            <>
                <Head title="Dons" />
                <div className="space-y-6">
                    <Heading
                        title="Dons aux membres"
                        description={`Aides sans remboursement accordées pendant la session ${session.name}.`}
                    />
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between">
                                {can('donations.create') && (
                                    <CreateDonationForm
                                        group={group}
                                        session={session}
                                        trigger={
                                            <Button className="w-fit">
                                                <PlusIcon /> Ajouter un don
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...sessions.donations.index.form(
                                        routeParameters,
                                    )}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher un membre"
                                        name="q"
                                    />
                                    <Button variant="outline">
                                        <SearchIcon /> Rechercher
                                    </Button>
                                </Form>
                            </div>
                        </CardHeader>
                        <CardContent className="px-0">
                            <Table className="border-spacing-4">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="pl-6">
                                            Membre
                                        </TableHead>
                                        <SortableTableHead field="created_at">
                                            Date de création
                                        </SortableTableHead>
                                        <SortableTableHead field="amount">
                                            Montant
                                        </SortableTableHead>
                                        <SortableTableHead field="status">
                                            Statut
                                        </SortableTableHead>
                                        <TableHead className="pr-6 text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody className="[&_td]:py-3">
                                    {collection.data.map((donation) => (
                                        <TableRow
                                            key={donation.id}
                                            className="h-14"
                                        >
                                            <TableCell className="pl-6 font-medium">
                                                {donation.member_name}
                                            </TableCell>
                                            <TableCell>
                                                {formatDate(
                                                    donation.created_at,
                                                )}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatCurrency(
                                                    donation.amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {
                                                        statusLabels[
                                                            donation.status
                                                        ]
                                                    }
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="pr-6 text-right">
                                                {donation.status ===
                                                    'pending' && (
                                                    <div className="flex justify-end gap-2">
                                                        {can(
                                                            'donations.cancel',
                                                        ) && (
                                                            <Form
                                                                {...sessions.donations.cancel.form(
                                                                    {
                                                                        ...routeParameters,
                                                                        donation:
                                                                            donation.id,
                                                                    },
                                                                )}
                                                                onBefore={() =>
                                                                    confirm(
                                                                        `Voulez-vous vraiment annuler le don de ${formatCurrency(donation.amount)} destiné à ${donation.member_name} ?`,
                                                                    )
                                                                }
                                                            >
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                >
                                                                    Annuler
                                                                </Button>
                                                            </Form>
                                                        )}
                                                        {can(
                                                            'donations.pay',
                                                        ) && (
                                                            <Form
                                                                {...sessions.donations.pay.form(
                                                                    {
                                                                        ...routeParameters,
                                                                        donation:
                                                                            donation.id,
                                                                    },
                                                                )}
                                                                onBefore={() =>
                                                                    confirm(
                                                                        `Voulez-vous vraiment effectuer le don de ${formatCurrency(donation.amount)} à ${donation.member_name} ? Cette opération créera un débit financier.`,
                                                                    )
                                                                }
                                                            >
                                                                <Button size="sm">
                                                                    Effectuer
                                                                </Button>
                                                            </Form>
                                                        )}
                                                    </div>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            {collection.data.length === 0 && (
                                <div className="py-10 text-center text-sm text-muted-foreground">
                                    {q
                                        ? `Aucun don ne correspond à la recherche « ${q} ».`
                                        : 'Aucun don enregistré.'}
                                </div>
                            )}
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
