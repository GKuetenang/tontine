import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRightIcon,
    CalendarDaysIcon,
    LandmarkIcon,
    MapPinIcon,
    PlusIcon,
    ReceiptTextIcon,
    UsersIcon,
} from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { dashboard } from '@/routes';
import groups from '@/routes/groups';
import meetings from '@/routes/groups/sessions/meetings';
import type { BreadcrumbItem } from '@/types';

type Money = { currency: string; amount: string };
type MeetingItem = {
    id: number;
    title: string;
    scheduled_at: string;
    location: string | null;
    group_name: string;
    group_slug: string;
    session_slug: string;
    meeting_slug: string;
};
type TransactionItem = {
    id: number;
    type_label: string;
    direction: 'credit' | 'debit';
    amount: string;
    occurred_at: string;
    group_name: string;
    currency: string;
};
type GroupItem = {
    id: number;
    name: string;
    slug: string;
    currency: string;
    active_members_count: number;
    active_session: { name: string; slug: string } | null;
};
type Props = {
    has_groups: boolean;
    summary: {
        groups_count: number;
        upcoming_meetings_count: number;
        contributions_due: Money[];
        active_loans_count: number;
        loans_due: Money[];
    };
    next_meetings: MeetingItem[];
    recent_transactions: TransactionItem[];
    groups: GroupItem[];
};

const moneySummary = (items: Money[]) => {
    if (items.length === 0) {
        return 'Aucun montant dû';
    }

    if (items.length === 1) {
        return formatCurrency(items[0].amount, items[0].currency);
    }

    return `${items.length} devises`;
};

function EmptyDashboard() {
    return (
        <Card>
            <CardContent className="grid min-h-[440px] place-items-center p-8 text-center">
                <div className="max-w-xl space-y-6">
                    <div className="mx-auto grid size-16 place-items-center rounded-2xl bg-primary/10 text-primary">
                        <UsersIcon className="size-8" />
                    </div>
                    <div className="space-y-2">
                        <h2 className="text-2xl font-semibold">
                            Commencez avec votre première réunion
                        </h2>
                        <p className="text-muted-foreground">
                            Créez une réunion pour organiser les membres, les
                            sessions, les assises et les opérations financières.
                        </p>
                    </div>
                    <Button asChild className="w-fit">
                        <Link href={groups.create()}>
                            <PlusIcon /> Créer une réunion
                        </Link>
                    </Button>
                    <div className="grid gap-3 pt-4 text-left sm:grid-cols-3">
                        {[
                            'Créez votre réunion',
                            'Ajoutez les membres',
                            'Planifiez les assises',
                        ].map((label, index) => (
                            <div key={label} className="rounded-lg border p-4">
                                <Badge variant="secondary">{index + 1}</Badge>
                                <p className="mt-3 text-sm font-medium">
                                    {label}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

export default withAppLayout<Props>(
    [{ title: 'Tableau de bord', href: dashboard() }] as BreadcrumbItem[],
    ({
        has_groups,
        summary,
        next_meetings,
        recent_transactions,
        groups: items,
    }) => {
        const { auth } = usePage().props;
        const firstName = auth.user.first_name || auth.user.name;
        const statistics = [
            {
                icon: UsersIcon,
                title: 'Mes réunions',
                value: String(summary.groups_count),
                detail: 'Réunions actives',
            },
            {
                icon: CalendarDaysIcon,
                title: 'Assises à venir',
                value: String(summary.upcoming_meetings_count),
                detail: 'Parmi les 5 prochaines',
            },
            {
                icon: ReceiptTextIcon,
                title: 'Mes cotisations dues',
                value: moneySummary(summary.contributions_due),
                detail: 'Toutes mes sessions',
            },
            {
                icon: LandmarkIcon,
                title: 'Mes prêts actifs',
                value: String(summary.active_loans_count),
                detail: moneySummary(summary.loans_due),
            },
        ];

        return (
            <>
                <Head title="Tableau de bord" />
                <div className="flex items-start justify-between gap-4">
                    <Heading
                        title={`Bonjour, ${firstName}`}
                        description="Voici l’essentiel de vos réunions et les prochaines actions à suivre."
                    />
                    <Button asChild className="w-fit">
                        <Link href={groups.create()}>
                            <PlusIcon /> Créer une réunion
                        </Link>
                    </Button>
                </div>
                {!has_groups ? (
                    <EmptyDashboard />
                ) : (
                    <div className="space-y-6">
                        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            {statistics.map(
                                ({ icon: Icon, title, value, detail }) => (
                                    <Card key={title}>
                                        <CardContent className="flex items-start justify-between gap-4">
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    {title}
                                                </p>
                                                <p className="mt-1 text-2xl font-semibold">
                                                    {value}
                                                </p>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {detail}
                                                </p>
                                            </div>
                                            <div className="rounded-lg bg-primary/10 p-2 text-primary">
                                                <Icon className="size-5" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                ),
                            )}
                        </section>

                        <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                            <Card className="bg-background pt-0">
                                <CardHeader className="border-b py-4">
                                    <CardTitle>Prochaines assises</CardTitle>
                                    <CardDescription>
                                        Les assises des sessions auxquelles vous
                                        participez.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="divide-y px-0">
                                    {next_meetings.map((meeting) => (
                                        <Link
                                            key={meeting.id}
                                            href={meetings.show({
                                                group: meeting.group_slug,
                                                session: meeting.session_slug,
                                                meeting: meeting.meeting_slug,
                                            })}
                                            className="flex min-h-14 items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-muted/50"
                                        >
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {meeting.title}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {meeting.group_name} ·{' '}
                                                    {formatDate(
                                                        meeting.scheduled_at,
                                                    )}
                                                </p>
                                                {meeting.location && (
                                                    <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                                        <MapPinIcon className="size-3" />{' '}
                                                        {meeting.location}
                                                    </p>
                                                )}
                                            </div>
                                            <ArrowRightIcon className="size-4 shrink-0" />
                                        </Link>
                                    ))}
                                    {next_meetings.length === 0 && (
                                        <p className="p-8 text-center text-sm text-muted-foreground">
                                            Aucune assise à venir.
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                            <Card className="bg-background pt-0">
                                <CardHeader className="border-b py-4">
                                    <CardTitle>Actions rapides</CardTitle>
                                    <CardDescription>
                                        Accédez rapidement aux espaces les plus
                                        utiles.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="grid gap-3">
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="justify-start"
                                    >
                                        <Link href={groups.index()}>
                                            <UsersIcon /> Voir mes réunions
                                        </Link>
                                    </Button>
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="justify-start"
                                    >
                                        <Link href={groups.create()}>
                                            <PlusIcon /> Créer une réunion
                                        </Link>
                                    </Button>
                                    {items[0] && (
                                        <Button
                                            asChild
                                            variant="outline"
                                            className="justify-start"
                                        >
                                            <Link
                                                href={groups.show({
                                                    group: items[0].slug,
                                                })}
                                            >
                                                <ArrowRightIcon /> Continuer
                                                avec {items[0].name}
                                            </Link>
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        <Card className="bg-background pt-0">
                            <CardHeader className="border-b py-4">
                                <CardTitle>Activité récente</CardTitle>
                                <CardDescription>
                                    Vos dernières opérations financières
                                    personnelles.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="px-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="pl-6">
                                                Date
                                            </TableHead>
                                            <TableHead>Réunion</TableHead>
                                            <TableHead>Opération</TableHead>
                                            <TableHead className="pr-6 text-right">
                                                Montant
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recent_transactions.map(
                                            (transaction) => (
                                                <TableRow
                                                    key={transaction.id}
                                                    className="h-14"
                                                >
                                                    <TableCell className="pl-6">
                                                        {formatDate(
                                                            transaction.occurred_at,
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {transaction.group_name}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="outline">
                                                            {
                                                                transaction.type_label
                                                            }
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell
                                                        className={`pr-6 text-right font-medium ${transaction.direction === 'credit' ? 'text-emerald-600' : 'text-destructive'}`}
                                                    >
                                                        {transaction.direction ===
                                                        'credit'
                                                            ? '+'
                                                            : '−'}{' '}
                                                        {formatCurrency(
                                                            transaction.amount,
                                                            transaction.currency,
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )}
                                    </TableBody>
                                </Table>
                                {recent_transactions.length === 0 && (
                                    <p className="p-8 text-center text-sm text-muted-foreground">
                                        Aucune opération personnelle récente.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="bg-background pt-0">
                            <CardHeader className="flex-row items-center justify-between border-b py-4">
                                <div>
                                    <CardTitle>Mes réunions</CardTitle>
                                    <CardDescription>
                                        Vos espaces actifs et leur session
                                        courante.
                                    </CardDescription>
                                </div>
                                <Button
                                    asChild
                                    variant="outline"
                                    className="w-fit"
                                >
                                    <Link href={groups.index()}>
                                        Tout afficher
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {items.slice(0, 6).map((group) => (
                                    <Link
                                        key={group.id}
                                        href={groups.show({
                                            group: group.slug,
                                        })}
                                        className="rounded-xl border p-4 transition-colors hover:bg-muted/50"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <p className="font-semibold">
                                                {group.name}
                                            </p>
                                            <Badge variant="secondary">
                                                {group.currency}
                                            </Badge>
                                        </div>
                                        <p className="mt-3 text-sm text-muted-foreground">
                                            {group.active_session?.name ??
                                                'Aucune session active'}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {group.active_members_count}{' '}
                                            membre(s) actif(s)
                                        </p>
                                    </Link>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                )}
            </>
        );
    },
);
