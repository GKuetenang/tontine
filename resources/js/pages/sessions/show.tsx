import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { InformationRow } from '@/components/tontines/information-row';
import { OverviewCard } from '@/components/tontines/overview-card';

import { withAppLayout } from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';

import tontines from '@/routes/tontines';

import type {
    BreadcrumbItem,
    Session,
    Tontine,
} from '@/types';

import type { InertiaLinkProps } from '@inertiajs/react';
import { Head, Link } from '@inertiajs/react';
import {
    CalendarDays,
    Coins,
    Shuffle,
    Users,
} from 'lucide-react';

type Props = {
    tontine: Tontine;
    session: Session;
};

export default withAppLayout<Props>(
    ({ tontine, session }) =>
        [
            {
                title: 'Tontines',
                href: tontines.index(),
            },
            {
                title: tontine.name,
                href: tontines.show(
                    tontine.slug!,
                ),
            },
            {
                title: 'Sessions',
                href: tontines.sessions.index(
                    tontine.slug!,
                ),
            },
            {
                title: session.name,
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({ tontine, session }: Props) => {
        return (
            <>
                <Head title={session.name} />

                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
                    <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <OverviewCard
                            title="Participants"
                            value={
                                session.participants_count ??
                                0
                            }
                            description="Participants de la session"
                            icon={Users}
                        />

                        <OverviewCard
                            title="Réunions"
                            value={
                                session.meetings_count ??
                                0
                            }
                            description="Réunions de la session"
                            icon={CalendarDays}
                        />

                        <OverviewCard
                            title="Cotisation"
                            value={formatCurrency(
                                session.default_contribution_amount,
                                tontine.currency,
                            )}
                            description="Montant par défaut"
                            icon={Coins}
                        />

                        <OverviewCard
                            title="Tirage"
                            value={
                                session.draw_allocation_mode_label ??
                                '—'
                            }
                            description="Mode d’attribution des tours"
                            icon={Shuffle}
                        />
                    </section>

                    <section className="grid gap-6 xl:grid-cols-3">
                        <Card className="xl:col-span-2">
                            <CardHeader>
                                <CardTitle>
                                    Accès rapides
                                </CardTitle>
                            </CardHeader>

                            <CardContent className="grid gap-4 md:grid-cols-3">
                                <SessionModuleCard
                                    title="Participants"
                                    description="Voir et gérer les participants de la session."
                                    icon={Users}
                                    href={
                                        tontines.sessions.participants.index(
                                            {
                                                tontine:
                                                    tontine.slug!,
                                                session:
                                                    session.slug,
                                            },
                                        )
                                    }
                                />

                                <SessionModuleCard
                                    title="Réunions"
                                    description="Créer et gérer les réunions de la session."
                                    icon={CalendarDays}
                                    href={
                                        tontines.sessions.meetings.index(
                                            {
                                                tontine:
                                                    tontine.slug!,
                                                session:
                                                    session.slug,
                                            },
                                        )
                                    }
                                />

                                <SessionModuleCard
                                    title="Tirage"
                                    description="Configurer et gérer le tirage de la session."
                                    icon={Shuffle}
                                    href={
                                        tontines.sessions.draw.show(
                                            {
                                                tontine:
                                                    tontine.slug!,
                                                session:
                                                    session.slug,
                                            },
                                        )
                                    }
                                />
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
                                    label="Nom"
                                    value={
                                        session.name
                                    }
                                />

                                <InformationRow
                                    label="Cotisation"
                                    value={formatCurrency(
                                        session.default_contribution_amount,
                                        tontine.currency,
                                    )}
                                />

                                <InformationRow
                                    label="Mode d’attribution"
                                    value={
                                        session.draw_allocation_mode_label ??
                                        '—'
                                    }
                                />

                                <InformationRow
                                    label="Date de début"
                                    value={formatDate(
                                        session.start_at,
                                    )}
                                />

                                <InformationRow
                                    label="Date de fin"
                                    value={formatDate(
                                        session.end_at,
                                    )}
                                />

                                <InformationRow
                                    label="Statut"
                                    value={getStatusLabel(
                                        session.status,
                                    )}
                                />
                            </CardContent>
                        </Card>
                    </section>

                    {session.description && (
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Description
                                </CardTitle>
                            </CardHeader>

                            <CardContent>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {session.description}
                                </p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </>
        );
    },
);

function SessionModuleCard({
    title,
    description,
    href,
    icon: Icon,
}: {
    title: string;
    description: string;
    href: InertiaLinkProps['href'];
    icon: React.ElementType;
}) {
    return (
        <Button
            asChild
            variant="outline"
            className="h-auto justify-start p-4"
        >
            <Link
                href={href}
                prefetch
                className="flex items-start gap-3"
            >
                <div className="rounded-md bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </div>

                <div className="min-w-0 text-left">
                    <p className="font-medium">
                        {title}
                    </p>

                    <p className="mt-1 whitespace-normal text-sm font-normal text-muted-foreground">
                        {description}
                    </p>
                </div>
            </Link>
        </Button>
    );
}

function formatDate(
    date?: string | null,
): string {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        'fr-CA',
        {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        },
    ).format(
        new Date(date),
    );
}

function getStatusLabel(
    status: Session['status'],
): string {
    switch (status) {
        case 'draft':
            return 'Préparation';

        case 'active':
            return 'Active';

        case 'closed':
            return 'Fermée';

        default:
            return '—';
    }
}