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
    amount_due: string;
    amount_paid: string;
    status_label: string;
    meeting_title: string;
    scheduled_at: string;
    tontine: { name: string; currency: string };
};
type Props = { collection: PaginatedCollection<Item> };
export default withAppLayout<Props>(
    [
        { title: 'Mon espace', href: account.index() },
        { title: 'Mes cotisations', href: account.contributions.index() },
    ] as BreadcrumbItem[],
    ({ collection }) => (
        <AccountLayout>
            <Head title="Mes cotisations" />
            <Heading
                title="Mes cotisations"
                description="Suivez les montants attendus et les paiements enregistrés à votre nom."
            />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <CardTitle>Historique des cotisations</CardTitle>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Réunion</TableHead>
                                <TableHead>Tontine</TableHead>
                                <TableHead>Statut</TableHead>
                                <TableHead>Attendu</TableHead>
                                <TableHead className="pr-6 text-right">
                                    Payé
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {collection.data.map((item) => (
                                <TableRow key={item.id} className="h-14">
                                    <TableCell className="pl-6">
                                        <p className="font-medium">
                                            {item.meeting_title}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {formatDate(item.scheduled_at)}
                                        </p>
                                    </TableCell>
                                    <TableCell>{item.tontine.name}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {item.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(
                                            item.amount_due,
                                            item.tontine.currency,
                                        )}
                                    </TableCell>
                                    <TableCell className="pr-6 text-right font-medium">
                                        {formatCurrency(
                                            item.amount_paid,
                                            item.tontine.currency,
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    {collection.data.length === 0 && (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            Aucune cotisation enregistrée à votre nom.
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
