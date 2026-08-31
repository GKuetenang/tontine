import { Form, Head } from '@inertiajs/react';
import { CollectionPagination } from '@/components/collection-pagination';
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
    Session,
    Tontine,
    Transaction,
} from '@/types';

type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Transaction>;
    filters: { direction?: string; type?: string; from?: string; to?: string };
    summary: { credits: string; debits: string; balance: string };
};

const typeLabels: Record<string, string> = {
    contribution: 'Cotisation',
    payout: 'Versement',
    loan: 'Prêt',
    repayment: 'Remboursement',
    penalty: 'Pénalité',
    cash_fund: 'Fonds de caisse',
    donation: 'Don',
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
            { title: 'Transactions', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, session, collection, filters, summary }) => (
        <>
            <Head title="Journal des transactions" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Journal financier
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Mouvements auditables de la session {session.name}.
                    </p>
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                    {(
                        [
                            ['Crédits', summary.credits],
                            ['Débits', summary.debits],
                            ['Solde', summary.balance],
                        ] as const
                    ).map(([label, value]) => (
                        <Card key={label}>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    {label}
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {formatCurrency(value)}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Transactions</CardTitle>
                        <Form
                            {...sessions.transactions.index.form({
                                tontine: tontine.slug!,
                                session: session.slug,
                            })}
                            className="grid gap-2 sm:grid-cols-5"
                        >
                            <select
                                name="direction"
                                defaultValue={filters.direction ?? ''}
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">Toutes directions</option>
                                <option value="credit">Crédits</option>
                                <option value="debit">Débits</option>
                            </select>
                            <select
                                name="type"
                                defaultValue={filters.type ?? ''}
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">Tous types</option>
                                {Object.entries(typeLabels).map(
                                    ([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ),
                                )}
                            </select>
                            <Input
                                type="date"
                                name="from"
                                defaultValue={filters.from ?? ''}
                            />
                            <Input
                                type="date"
                                name="to"
                                defaultValue={filters.to ?? ''}
                            />
                            <Button>Filtrer</Button>
                        </Form>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="pl-6">Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Membre</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Direction</TableHead>
                                    <TableHead className="pr-6 text-right">
                                        Montant
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((transaction) => (
                                    <TableRow key={transaction.id}>
                                        <TableCell className="pl-6">
                                            {formatDate(
                                                transaction.occurred_at,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {typeLabels[transaction.type] ??
                                                transaction.type}
                                        </TableCell>
                                        <TableCell>
                                            {transaction.member_name ?? '—'}
                                        </TableCell>
                                        <TableCell className="max-w-xs whitespace-normal">
                                            {transaction.description ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {transaction.direction === 'credit'
                                                ? 'Crédit'
                                                : 'Débit'}
                                        </TableCell>
                                        <TableCell
                                            className={`pr-6 text-right font-medium ${transaction.direction === 'credit' ? 'text-emerald-600' : 'text-destructive'}`}
                                        >
                                            {transaction.direction === 'credit'
                                                ? '+'
                                                : '−'}{' '}
                                            {formatCurrency(transaction.amount)}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {collection.data.length === 0 && (
                            <p className="p-8 text-center text-sm text-muted-foreground">
                                Aucune transaction pour ces critères.
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
