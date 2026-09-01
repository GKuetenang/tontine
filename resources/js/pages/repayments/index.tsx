import { Form, Head } from '@inertiajs/react';
import { SearchIcon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import type {
    BreadcrumbItem,
    PaginatedCollection,
    Repayment,
    Session,
    Tontine,
} from '@/types';

type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Repayment>;
    q: string | null;
};

export default withAppLayout<Props>(
    ({ tontine, session }) =>
        [
            { title: 'Tontines', href: tontines.index() },
            {
                title: tontine.name,
                href: tontines.show({ tontine: tontine.slug! }),
            },
            {
                title: session.name,
                href: sessions.show({
                    tontine: tontine.slug!,
                    session: session.slug,
                }),
            },
            { title: 'Remboursements', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, session, collection, q }) => (
        <>
            <Head title="Remboursements" />
            <Heading
                title="Remboursements"
                description="Historique des remboursements de prêts de la session."
            />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <div className="flex items-center justify-between">
                        <CardTitle>Mouvements enregistrés</CardTitle>
                        <Form
                            {...sessions.repayments.index.form({
                                tontine: tontine.slug!,
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
                                    field="paid_at"
                                    className="pl-6"
                                >
                                    Date
                                </SortableTableHead>
                                <TableHead>Membre</TableHead>
                                <SortableTableHead field="amount">
                                    Montant
                                </SortableTableHead>
                                <SortableTableHead field="interest_amount">
                                    Intérêt
                                </SortableTableHead>
                                <SortableTableHead
                                    field="principal_amount"
                                    className="pr-6 text-right"
                                >
                                    Capital
                                </SortableTableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody className="[&_td]:py-3">
                            {collection.data.map((item) => (
                                <TableRow key={item.id} className="h-14">
                                    <TableCell className="pl-6">
                                        {formatDate(item.paid_at)}
                                    </TableCell>
                                    <TableCell>{item.member_name}</TableCell>
                                    <TableCell>
                                        <Badge variant="success">
                                            + {formatCurrency(item.amount)}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(item.interest_amount)}
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {formatCurrency(item.principal_amount)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    {collection.data.length === 0 && (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            {q
                                ? `Aucun remboursement ne correspond à la recherche « ${q} ».`
                                : 'Aucun remboursement enregistré.'}
                        </p>
                    )}
                    <CollectionPagination
                        className="px-6 pt-6"
                        collection={collection}
                    />
                </CardContent>
            </Card>
        </>
    ),
);
