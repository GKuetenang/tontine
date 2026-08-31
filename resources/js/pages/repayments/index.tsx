import { Head } from '@inertiajs/react';
import { CollectionPagination } from '@/components/collection-pagination';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    ({ collection }) => (
        <>
            <Head title="Remboursements" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Remboursements</h1>
                    <p className="text-sm text-muted-foreground">
                        Historique des remboursements de prêts de la session.
                    </p>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Mouvements enregistrés</CardTitle>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="pl-6">Date</TableHead>
                                    <TableHead>Membre</TableHead>
                                    <TableHead>Montant</TableHead>
                                    <TableHead>Intérêt</TableHead>
                                    <TableHead className="pr-6 text-right">
                                        Capital
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody className="[&_td]:py-3">
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className="h-14">
                                        <TableCell className="pl-6">
                                            {formatDate(item.paid_at)}
                                        </TableCell>
                                        <TableCell>
                                            {item.member_name}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="success">
                                                + {formatCurrency(item.amount)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(
                                                item.interest_amount,
                                            )}
                                        </TableCell>
                                        <TableCell className="pr-6 text-right">
                                            {formatCurrency(
                                                item.principal_amount,
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {collection.data.length === 0 && (
                            <p className="p-8 text-center text-sm text-muted-foreground">
                                Aucun remboursement enregistré.
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
    ),
);
