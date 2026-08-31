import { CollectionPagination } from '@/components/collection-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

const labels = {
    pending: 'En attente',
    active: 'Actif',
    repaid: 'Remboursé',
    cancelled: 'Annulé',
} as const;
type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Loan>;
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
    ({ tontine, session, collection }) => {
        const { can } = useAuthorization();
        const params = { tontine: tontine.slug!, session: session.slug };

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
                                trigger={<Button> <PlusIcon /> Créer un prêt</Button>}
                            />
                        )}
                    </div>
                    <div className="grid gap-4">
                        {collection.data.map((loan) => (
                            <Card key={loan.id}>
                                <CardContent className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div className="flex gap-3">
                                            <p className="font-semibold">
                                                {loan.member_name}
                                            </p>
                                            <span className="rounded-full border px-2 py-0.5 text-xs">
                                                {labels[loan.status]}
                                            </span>
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            Échéance : {formatDate(loan.due_at)}{' '}
                                            · Taux : {loan.interest_rate} %
                                        </p>
                                        {loan.reason && (
                                            <p className="text-sm text-muted-foreground">
                                                {loan.reason}
                                            </p>
                                        )}
                                    </div>
                                    <div className="sm:text-right">
                                        <p className="font-semibold">
                                            Total :{' '}
                                            {formatCurrency(loan.total_due)}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Capital{' '}
                                            {formatCurrency(
                                                loan.principal_amount,
                                            )}{' '}
                                            + intérêt{' '}
                                            {formatCurrency(
                                                loan.interest_amount,
                                            )}
                                        </p>
                                        {loan.status === 'pending' &&
                                            can('loans.approve') && (
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
                                                        className="mt-2"
                                                        size="sm"
                                                    >
                                                        Approuver et décaisser
                                                    </Button>
                                                </Form>
                                            )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                        {collection.data.length === 0 && (
                            <Card>
                                <CardContent className="py-10 text-center text-sm text-muted-foreground">
                                    Aucun prêt enregistré.
                                </CardContent>
                            </Card>
                        )}
                    </div>
                    <CollectionPagination collection={collection} />
                </div>
            </>
        );
    },
);
