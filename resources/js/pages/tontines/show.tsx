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
import { EmptySessions } from '@/components/tontines/empty-session';
import { InformationRow } from '@/components/tontines/information-row';
import { SessionRow } from '@/components/tontines/session-row';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { withAppLayout } from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, Session, Tontine } from '@/types';

type Props = { tontine: Tontine; sessions: Session[] };
const initials = (name: string) =>
    name
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();

export default withAppLayout<Props>(
    ({ tontine }) =>
        [
            { title: 'Tontines', href: tontines.index() },
            { title: tontine.name, href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, sessions }) => (
        <>
            <Head title={tontine.name} />
            <div className="space-y-6">
                <section className="relative overflow-hidden rounded-2xl border bg-linear-to-br from-primary/12 via-background to-background p-6 shadow-sm md:p-8">
                    <div className="absolute -top-24 -right-20 size-64 rounded-full bg-primary/10 blur-3xl" />
                    <div className="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-4">
                            {tontine.image ? (
                                <img
                                    src={tontine.image}
                                    alt={tontine.name}
                                    className="size-20 rounded-2xl border bg-background object-cover shadow-sm md:size-24"
                                />
                            ) : (
                                <Avatar className="border-primary bg-primary/10">
                                    <AvatarFallback>
                                        {initials(tontine.name)}
                                    </AvatarFallback>
                                </Avatar>
                            )}
                            <div className="space-y-2">
                                <div className="flex flex-wrap gap-2">
                                    <Badge
                                        variant={
                                            tontine.is_active
                                                ? 'success'
                                                : 'secondary'
                                        }
                                    >
                                        {tontine.is_active
                                            ? 'Active'
                                            : 'Inactive'}
                                    </Badge>
                                    <Badge variant="outline">
                                        {tontine.is_public ? (
                                            <Eye />
                                        ) : (
                                            <EyeOff />
                                        )}
                                        {tontine.is_public
                                            ? 'Publique'
                                            : 'Privée'}
                                    </Badge>
                                </div>
                                <div>
                                    <h1 className="text-2xl font-bold tracking-tight md:text-3xl">
                                        {tontine.name}
                                    </h1>
                                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                                        {tontine.description ||
                                            'Gérez les membres, les sessions et les opérations financières de cette tontine.'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        {tontine.can?.update && (
                            <div className="flex flex-wrap gap-2">
                                <Button
                                    asChild
                                    variant="outline"
                                    className="bg-background/80"
                                >
                                    <Link
                                        href={tontines.edit({
                                            tontine: tontine.slug!,
                                        })}
                                    >
                                        <Pencil />
                                        Modifier
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
                            value: tontine.members_count ?? 0,
                            detail: 'membres enregistrés',
                            icon: Users,
                        },
                        {
                            label: 'Sessions',
                            value: tontine.sessions_count ?? 0,
                            detail: 'sessions créées',
                            icon: CalendarDays,
                        },
                        {
                            label: 'Cotisation',
                            value: formatCurrency(
                                tontine.default_contribution_amount,
                                tontine.currency,
                            ),
                            detail: 'montant par défaut',
                            icon: Coins,
                        },
                        {
                            label: 'Configuration',
                            value: `${tontine.default_loan_interest_rate} %`,
                            detail: `prêts sur ${tontine.default_loan_term_months} mois`,
                            icon: Landmark,
                        },
                    ].map(({ label, value, detail, icon: Icon }) => (
                        <Card
                            key={label}
                            className="group transition-shadow hover:shadow-md"
                        >
                            <CardContent className="flex items-center gap-4">
                                <div className="rounded-xl bg-primary/10 p-3 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                                    <Icon className="size-5" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        {label}
                                    </p>
                                    <p className="truncate text-xl font-bold">
                                        {value}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {detail}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.8fr)]">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between border-b">
                            <div>
                                <CardTitle>Sessions récentes</CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Les dernières activités de la tontine
                                </p>
                            </div>
                            <Button
                                asChild
                                className="w-fit"
                                variant="link"
                                size="sm"
                            >
                                <Link
                                    href={tontines.sessions.index(
                                        tontine.slug!,
                                    )}
                                >
                                    Toutes les sessions
                                    <ArrowRight />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {sessions.length === 0 ? (
                                <EmptySessions tontine={tontine} />
                            ) : (
                                <div className="divide-y">
                                    {sessions.map((session) => (
                                        <SessionRow
                                            key={session.id}
                                            tontine={tontine}
                                            session={session}
                                        />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2">
                                <ShieldCheck className="size-4 text-primary" />
                                Informations
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <InformationRow
                                label="Préfixe membre"
                                value={tontine.member_number_prefix}
                            />
                            <InformationRow
                                label="Devise"
                                value={tontine.currency ?? '—'}
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
    ),
);
