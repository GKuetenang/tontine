import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Coins,
    Eye,
    EyeOff,
    Landmark,
    Pencil,
    ShieldCheck,
    Users,
} from 'lucide-react';

import DashboardCard from '@/components/dashboard-card';
import { EmptySessions } from '@/components/groups/empty-session';
import { InformationRow } from '@/components/groups/information-row';
import { SessionRow } from '@/components/groups/session-row';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import { withAppLayout } from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import type { BreadcrumbItem, Group, Session } from '@/types';

type Props = { group: Group; sessions: Session[] };
// const initials = (name: string) =>
//     name
//         .split(/\s+/)
//         .slice(0, 2)
//         .map((word) => word[0])
//         .join('')
//         .toUpperCase();

export default withAppLayout<Props>(
    ({ group }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: '#' },
        ] as BreadcrumbItem[],
    ({ group, sessions }) => {
        const { t } = useTranslation();

        return (
            <>
                <Head title={group.name} />
                <div className="space-y-6">
                    <section className="bg-card-gradient relative overflow-hidden rounded-2xl border bg-card p-6 shadow-sm md:p-8">
                        <div className="absolute -top-24 -right-20 size-64 rounded-full bg-primary/10 blur-3xl" />
                        <div className="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div className="flex items-center gap-4">
                                <div className="space-y-2">
                                    <div className="flex flex-wrap gap-2">
                                        <Badge
                                            variant={
                                                group.is_active
                                                    ? 'success'
                                                    : 'secondary'
                                            }
                                        >
                                            {group.is_active
                                                ? 'Active'
                                                : 'Inactive'}
                                        </Badge>
                                        <Badge variant="outline">
                                            {group.is_public ? (
                                                <Eye />
                                            ) : (
                                                <EyeOff />
                                            )}
                                            {group.is_public
                                                ? 'Publique'
                                                : 'Privée'}
                                        </Badge>
                                    </div>
                                    <div>
                                        <h1 className="text-2xl font-bold tracking-tight md:text-3xl">
                                            {group.name}
                                        </h1>
                                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                                            {group.description ||
                                                'Gérez les membres, les sessions et les opérations financières de cette réunion.'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {group.can?.update && (
                                <div className="flex flex-wrap gap-2">
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="bg-background/80"
                                    >
                                        <Link
                                            href={groups.edit({
                                                group: group.slug!,
                                            })}
                                        >
                                            <Pencil />
                                            {t('Modifier')}
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </div>
                    </section>

                    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {[
                            {
                                label: 'Membres',
                                value: group.members_count ?? 0,
                                detail: 'membres enregistrés',
                                icon: Users,
                            },
                            {
                                label: 'Sessions',
                                value: group.sessions_count ?? 0,
                                detail: 'sessions créées',
                                icon: CalendarDays,
                            },
                            {
                                label: 'Cotisation',
                                value: formatCurrency(
                                    group.default_contribution_amount,
                                    group.currency,
                                ),
                                detail: 'montant par défaut',
                                icon: Coins,
                            },
                            {
                                label: 'Configuration',
                                value: `${group.default_loan_interest_rate} %`,
                                detail: `prêts sur ${group.default_loan_term_months} mois`,
                                icon: Landmark,
                            },
                        ].map(({ label, value, detail, icon: Icon }) => (
                            <DashboardCard
                                key={label}
                                title={label}
                                value={value}
                                detail={detail}
                                icon={Icon}
                            />
                        ))}
                    </section>

                    <section className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.8fr)]">
                        <Card className="bg-bacground">
                            <CardHeader className="flex-row items-center justify-between border-b">
                                <div>
                                    <CardTitle>
                                        {t('Sessions récentes')}
                                    </CardTitle>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {t(
                                            'Les dernières activités de la réunion',
                                        )}
                                    </p>
                                </div>
                                <Button
                                    asChild
                                    className="w-fit"
                                    variant="link"
                                    size="sm"
                                >
                                    <Link
                                        href={groups.sessions.index(
                                            group.slug!,
                                        )}
                                    >
                                        {t('Toutes les sessions')}
                                        <ArrowRight />
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent>
                                {sessions.length === 0 ? (
                                    <EmptySessions group={group} />
                                ) : (
                                    <div className="divide-y">
                                        {sessions.map((session) => (
                                            <SessionRow
                                                key={session.id}
                                                group={group}
                                                session={session}
                                            />
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                        <Card className="bg-background">
                            <CardHeader className="border-b">
                                <CardTitle className="flex items-center gap-2">
                                    <ShieldCheck className="size-4 text-primary" />
                                    {t('Informations')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <InformationRow
                                    label={t('Préfixe membre')}
                                    value={group.member_number_prefix}
                                />
                                <InformationRow
                                    label={t('Devise')}
                                    value={group.currency ?? '—'}
                                />
                                <InformationRow
                                    label={t('Vérification')}
                                    value={
                                        group.is_verified
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
