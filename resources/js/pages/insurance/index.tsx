import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon } from 'lucide-react';
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
import { useAuthorization } from '@/hooks/use-authorization';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type {
    BreadcrumbItem,
    InsuranceContribution,
    PaginatedCollection,
    Session,
    Group,
} from '@/types';
import { CreateInsuranceContributionForm } from './form';

type Props = {
    group: Group;
    session: Session;
    collection: PaginatedCollection<InsuranceContribution>;
    summary: {
        total: string;
        contributions_count: number;
        contributors_count: number;
    };
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
            { title: 'Assurance', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, session, collection, summary, q }) => {
        const { can } = useAuthorization();
        const cards = [
            {
                label: 'Assurance constituée',
                value: formatCurrency(summary.total),
            },
            { label: 'Versements', value: summary.contributions_count },
            {
                label: 'Membres contributeurs',
                value: summary.contributors_count,
            },
        ];

        return (
            <>
                <Head title="Assurance" />
                <div className="space-y-6">
                    <Heading
                        title="Assurance"
                        description={`Versements des membres pendant la session ${session.name}.`}
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        {cards.map((card) => (
                            <Card key={card.label}>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        {card.label}
                                    </p>
                                    <p className="mt-1 text-2xl font-semibold">
                                        {card.value}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between">
                                {can('insurance.manage') && (
                                    <CreateInsuranceContributionForm
                                        group={group}
                                        session={session}
                                        trigger={
                                            <Button className="w-fit">
                                                <PlusIcon /> Nouveau versement
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...sessions.insurance.index.form({
                                        group: group.slug!,
                                        session: session.slug,
                                    })}
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
                                        <SortableTableHead
                                            field="occurred_at"
                                            className="pl-6"
                                        >
                                            Date
                                        </SortableTableHead>
                                        <TableHead>Membre</TableHead>
                                        <TableHead>Enregistré par</TableHead>
                                        <SortableTableHead
                                            field="amount"
                                            className="pr-6 text-right"
                                        >
                                            Montant versé
                                        </SortableTableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody className="[&_td]:py-3">
                                    {collection.data.map((entry) => (
                                        <TableRow
                                            key={entry.id}
                                            className="h-14"
                                        >
                                            <TableCell className="pl-6">
                                                {formatDate(entry.occurred_at)}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {entry.member_name}
                                            </TableCell>
                                            <TableCell>
                                                {entry.creator_name ?? '—'}
                                            </TableCell>
                                            <TableCell className="pr-6 text-right font-medium text-emerald-600">
                                                + {formatCurrency(entry.amount)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            {collection.data.length === 0 && (
                                <p className="p-8 text-center text-sm text-muted-foreground">
                                    {q
                                        ? `Aucun versement d’assurance ne correspond à la recherche « ${q} ».`
                                        : 'Aucun versement d’assurance enregistré.'}
                                </p>
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
