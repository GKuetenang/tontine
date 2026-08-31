import { CollectionPagination } from '@/components/collection-pagination';
import type { SelectOption } from '@/components/select-with-items';
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
    Donation,
    PaginatedCollection,
    Session,
    Tontine,
} from '@/types';
import { Form, Head } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { CreateDonationForm } from './form';

type Props = {
    tontine: Tontine;
    session: Session;
    collection: PaginatedCollection<Donation>;
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
            { title: 'Dons', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, session, collection, statuses }) => {
        const { can } = useAuthorization();
        const routeParameters = {
            tontine: tontine.slug!,
            session: session.slug,
        };
        const statusLabels = Object.fromEntries(
            statuses.map((option) => [option.value, option.label]),
        );

        return (
            <>
                <Head title="Dons" />
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold">
                                Dons aux membres
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Aides sans remboursement accordées pendant la
                                session {session.name}.
                            </p>
                        </div>

                        {can('donations.create') && (
                            <CreateDonationForm
                                tontine={tontine}
                                session={session}
                                trigger={<Button><PlusIcon />Ajouter un don</Button>}
                            />
                        )}
                    </div>

                    <div className="grid gap-4">
                        {collection.data.map((donation) => (
                            <Card key={donation.id}>
                                <CardContent className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div className="flex items-center gap-3">
                                            <p className="font-semibold">
                                                {donation.member_name}
                                            </p>
                                            <span className="rounded-full border px-2 py-0.5 text-xs">
                                                {statusLabels[donation.status]}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {donation.reason}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            Créé le{' '}
                                            {formatDate(donation.created_at)}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2 sm:flex-col sm:items-end">
                                        <p className="text-lg font-semibold">
                                            {formatCurrency(donation.amount)}
                                        </p>
                                        {donation.status === 'pending' && (
                                            <div className="flex gap-2">
                                                {can('donations.cancel') && (
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
                                                {can('donations.pay') && (
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
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                        {collection.data.length === 0 && (
                            <Card>
                                <CardContent className="py-10 text-center text-sm text-muted-foreground">
                                    Aucun don enregistré.
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
