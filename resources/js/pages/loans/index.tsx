import { CollectionPagination } from '@/components/collection-pagination';
import type { SelectOption } from '@/components/select-with-items';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import type {
    BreadcrumbItem,
    Loan,
    PaginatedCollection,
    Session,
    Tontine,
} from '@/types';
import { Form, Head } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { CreateLoanForm } from './form';
import { CreateRepaymentForm } from './repayment-form';

type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Loan>;
    statuses: SelectOption[];
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
            { title: 'Prêts', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, session, collection, statuses }) => {
        const { can } = useAuthorization();
        const params = { tontine: tontine.slug!, session: session.slug };
        const statusLabels = Object.fromEntries(
            statuses.map((option) => [option.value, option.label]),
        );

        return (
            <>
                <Head title="Prêts" />
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold">Prêts</h1>
                            <p className="text-sm text-muted-foreground">
                                Prêts et échéances de la session.
                            </p>
                        </div>
                        {can('loans.create') && (
                            <CreateLoanForm
                                tontine={tontine}
                                session={session}
                                trigger={
                                    <Button>
                                        {' '}
                                        <PlusIcon /> Ajouter un prêt
                                    </Button>
                                }
                            />
                        )}
                    </div>
                    <Card>
                        <CardContent className="px-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="pl-6">
                                            Emprunteur
                                        </TableHead>
                                        <TableHead>Échéance</TableHead>
                                        <TableHead>Capital</TableHead>
                                        <TableHead>Intérêt</TableHead>
                                        <TableHead>Total dû</TableHead>
                                        <TableHead>Solde</TableHead>
                                        <TableHead>Statut</TableHead>
                                        <TableHead className="pr-6 text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody className="[&_td]:py-3">
                                    {collection.data.map((loan) => (
                                        <TableRow
                                            key={loan.id}
                                            className="h-14"
                                        >
                                            <TableCell className="pl-6">
                                                <p className="font-medium">
                                                    {loan.member_name}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                {formatDate(loan.due_at)}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    loan.principal_amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    loan.interest_amount,
                                                )}{' '}
                                                <span className="text-xs text-muted-foreground">
                                                    ({loan.interest_rate} %)
                                                </span>
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatCurrency(loan.total_due)}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    loan.remaining_amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        loan.status === 'active'
                                                            ? 'success'
                                                            : loan.status ===
                                                                'cancelled'
                                                                ? 'destructive'
                                                                : 'secondary'
                                                    }
                                                >
                                                    {statusLabels[loan.status]}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="pr-6 text-right">
                                                <div className="flex justify-end gap-2">
                                                    {loan.status ===
                                                        'pending' &&
                                                        can(
                                                            'loans.approve',
                                                        ) && (
                                                            <Form
                                                                {...sessions.loans.approve.form(
                                                                    {
                                                                        ...params,
                                                                        loan: loan.id,
                                                                    },
                                                                )}
                                                                onBefore={() =>
                                                                    confirm(
                                                                        `Voulez-vous vraiment approuver et décaisser le capital de ${formatCurrency(loan.principal_amount)} à ${loan.member_name} ? Cette opération créera un débit financier.`,
                                                                    )
                                                                }
                                                            >
                                                                <Button size="sm">
                                                                    Approuver et
                                                                    décaisser
                                                                </Button>
                                                            </Form>
                                                        )}
                                                    {loan.status === 'active' &&
                                                        can(
                                                            'repayments.create',
                                                        ) && (
                                                            <CreateRepaymentForm
                                                                tontine={
                                                                    tontine
                                                                }
                                                                session={
                                                                    session
                                                                }
                                                                loan={loan}
                                                                trigger={
                                                                    <Button
                                                                        size="sm"
                                                                        variant="outline"
                                                                    >
                                                                        Rembourser
                                                                    </Button>
                                                                }
                                                            />
                                                        )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            {collection.data.length === 0 && (
                                <div className="py-10 text-center text-sm text-muted-foreground">
                                    Aucun prêt enregistré.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    <CollectionPagination collection={collection} />
                </div>
            </>
        );
    },
);
