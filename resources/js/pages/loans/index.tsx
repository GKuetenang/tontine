import { Form, Head } from '@inertiajs/react';
import { CheckIcon, PlusIcon, SearchIcon } from 'lucide-react';
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
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import type {
    BreadcrumbItem,
    Loan,
    PaginatedCollection,
    Session,
    Tontine,
} from '@/types';
import { CreateLoanForm } from './form';
import { CreateRepaymentForm } from './repayment-form';

type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Loan>;
    statuses: SelectOption[];
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
            { title: 'Prêts', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, session, collection, statuses, q }) => {
        const { can } = useAuthorization();
        const params = { tontine: tontine.slug!, session: session.slug };
        const statusLabels = Object.fromEntries(
            statuses.map((option) => [option.value, option.label]),
        );

        return (
            <>
                <Head title="Prêts" />
                <div className="space-y-6">
                    <Heading
                        title="Prêts"
                        description="Prêts et échéances de la session."
                    />
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between">
                                {can('loans.create') && (
                                    <CreateLoanForm
                                        tontine={tontine}
                                        session={session}
                                        trigger={
                                            <Button className="w-fit">
                                                <PlusIcon /> Ajouter un prêt
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...sessions.loans.index.form(params)}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher un emprunteur"
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
                                            Emprunteur
                                        </TableHead>
                                        <SortableTableHead field="due_at">
                                            Échéance
                                        </SortableTableHead>
                                        <SortableTableHead field="principal_amount">
                                            Capital
                                        </SortableTableHead>
                                        <SortableTableHead field="interest_amount">
                                            Intérêt
                                        </SortableTableHead>
                                        <SortableTableHead field="total_due">
                                            Total dû
                                        </SortableTableHead>
                                        <TableHead>Solde</TableHead>
                                        <SortableTableHead field="status">
                                            Statut
                                        </SortableTableHead>
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
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                >
                                                                    <CheckIcon />
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
                                    {q
                                        ? `Aucun prêt ne correspond à la recherche « ${q} ».`
                                        : 'Aucun prêt enregistré.'}
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
