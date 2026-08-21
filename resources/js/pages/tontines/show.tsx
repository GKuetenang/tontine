import { withAppLayout } from '@/layouts/app-layout';

import type {
    BreadcrumbItem,
    Session,
    Tontine,
} from '@/types';

import { Head, Link } from '@inertiajs/react';

import {
    CalendarDays,
    Coins,
    Eye,
    EyeOff,
    Users,
} from 'lucide-react';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { Button } from '@/components/ui/button';

import { EmptySessions } from '@/components/tontines/empty-session';
import { InformationRow } from '@/components/tontines/information-row';
import { OverviewCard } from '@/components/tontines/overview-card';
import { SessionRow } from '@/components/tontines/session-row';
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';

type Props = {
    tontine: Tontine;
    sessions: Session[];
};

export default withAppLayout<Props>(
    ({ tontine }) => [
        {
            title: 'Tontines',
            href: tontines.index(),
        },
        {
            title: tontine.name,
            href: '#',
        },
    ] as BreadcrumbItem[],
    ({ tontine, sessions }: Props) => {

        return (
            <>

                <Head title={tontine.name} />

                {/* <div className="flex items-center gap-3">
                    <Heading
                        title={tontine.name}
                        description={tontine.description ?? ''}
                    />

                    {tontine.is_active && (
                        <Badge
                            variant="outline"
                            className="rounded-full border-green-200 bg-green-50 text-green-700"
                        >
                            Active
                        </Badge>
                    )}
                </div> */}

                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
                    <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <OverviewCard
                            title="Membres"
                            value={
                                tontine.members_count ?? 0
                            }
                            description="Membres de la tontine"
                            icon={Users}
                        />

                        <OverviewCard
                            title="Sessions"
                            value={
                                tontine.sessions_count ?? 0
                            }
                            description="Sessions créées"
                            icon={CalendarDays}
                        />

                        <OverviewCard
                            title="Cotisation par défaut"
                            value={formatCurrency(
                                tontine.default_contribution_amount,
                                tontine.currency,
                            )}
                            description="Montant proposé à la création"
                            icon={Coins}
                        />

                        <OverviewCard
                            title="Visibilité"
                            value={
                                tontine.is_public
                                    ? 'Publique'
                                    : 'Privée'
                            }
                            description={
                                tontine.is_public
                                    ? 'Visible publiquement'
                                    : 'Accès limité aux membres'
                            }
                            icon={
                                tontine.is_public
                                    ? Eye
                                    : EyeOff
                            }
                        />
                    </section>

                    <section className="grid gap-6 xl:grid-cols-3">
                        <Card className="xl:col-span-2">
                            <CardHeader className="flex flex-row items-center justify-between">
                                <CardTitle>
                                    Sessions récentes
                                </CardTitle>

                                <Button
                                    asChild
                                    variant="outline"
                                    size="sm"
                                >
                                    <Link
                                        href={
                                            tontines.sessions.index(
                                                tontine.slug!,
                                            )
                                        }
                                    >
                                        Voir toutes
                                    </Link>
                                </Button>
                            </CardHeader>

                            <CardContent>
                                {sessions.length === 0 ? (
                                    <EmptySessions
                                        tontine={tontine}
                                    />
                                ) : (
                                    <div className="divide-y">
                                        {sessions.map(
                                            (session) => (
                                                <SessionRow
                                                    key={
                                                        session.id
                                                    }
                                                    tontine={
                                                        tontine
                                                    }
                                                    session={
                                                        session
                                                    }
                                                />
                                            ),
                                        )}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Informations
                                </CardTitle>
                            </CardHeader>

                            <CardContent className="space-y-4">
                                <InformationRow
                                    label="Préfixe membre"
                                    value={
                                        tontine.member_number_prefix
                                    }
                                />

                                <InformationRow
                                    label="Devise"
                                    value={
                                        tontine.currency ??
                                        '—'
                                    }
                                />

                                <InformationRow
                                    label="Statut"
                                    value={
                                        tontine.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }
                                />

                                <InformationRow
                                    label="Visibilité"
                                    value={
                                        tontine.is_public
                                            ? 'Publique'
                                            : 'Privée'
                                    }
                                />

                                <InformationRow
                                    label="Vérification"
                                    value={
                                        tontine.is_verified
                                            ? 'Vérifiée'
                                            : 'Non vérifiée'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </section>
                </div>
            </>
        );
    },
);
