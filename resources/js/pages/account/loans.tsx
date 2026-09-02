import { Head } from '@inertiajs/react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
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
import { AccountLayout } from '@/layouts/account-layout';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import account from '@/routes/account';
import type { BreadcrumbItem, PaginatedCollection } from '@/types';

type Item = {
    id: number;
    principal_amount: string;
    interest_amount: string;
    total_due: string;
    repaid_amount: string;
    due_at: string;
    status_label: string;
    tontine: { name: string; currency: string };
};
type Props = { collection: PaginatedCollection<Item> };
export default withAppLayout<Props>(
    [
        { title: 'Mon espace', href: account.index() },
        { title: 'Mes prêts', href: account.loans.index() },
    ] as BreadcrumbItem[],
    ({ collection }) => (
        <AccountLayout>
            <Head title="Mes prêts" />
            <Heading
                title="Mes prêts"
                description="Consultez vos emprunts, leurs intérêts, remboursements et échéances."
            />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <CardTitle>Historique des prêts</CardTitle>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Tontine</TableHead>
                                <TableHead>Statut</TableHead>
                                <TableHead>Capital</TableHead>
                                <TableHead>Intérêt</TableHead>
                                <TableHead>Remboursé</TableHead>
                                <TableHead className="pr-6 text-right">
                                    Échéance
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {collection.data.map((item) => (
                                <TableRow key={item.id} className="h-14">
                                    <TableCell className="pl-6 font-medium">
                                        {item.tontine.name}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {item.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(
                                            item.principal_amount,
                                            item.tontine.currency,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(
                                            item.interest_amount,
                                            item.tontine.currency,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(
                                            item.repaid_amount,
                                            item.tontine.currency,
                                        )}
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {formatDate(item.due_at)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    {collection.data.length === 0 && (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            Aucun prêt enregistré à votre nom.
                        </p>
                    )}
                    <CollectionPagination
                        collection={collection}
                        className="px-6 pt-6"
                    />
                </CardContent>
            </Card>
        </AccountLayout>
    ),
);
