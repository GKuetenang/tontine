import { Head, Link } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    HandCoinsIcon,
    LandmarkIcon,
    UsersIcon,
} from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { AccountLayout } from '@/layouts/account-layout';
import { withAppLayout } from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import account from '@/routes/account';
import groups from '@/routes/groups';
import type { BreadcrumbItem, PaginatedCollection } from '@/types';

type Item = {
    id: number;
    member_number: string;
    joined_at: string | null;
    insurance_total: string;
    group: { name: string; slug: string; currency: string };
    active_session: { name: string; slug: string } | null;
};
type Props = {
    collection: PaginatedCollection<Item>;
    summary: {
        groups_count: number;
        insurance_payments_count: number;
        contributions_due_count: number;
        active_loans_count: number;
    };
};

export default withAppLayout<Props>(
    [{ title: 'Mon espace', href: account.index() }] as BreadcrumbItem[],
    ({ collection, summary }) => (
        <AccountLayout>
            <Head title="Mon espace" />
            <Heading
                title="Mon espace"
                description="Retrouvez vos réunions, vos versements et vos obligations personnelles."
            />
            <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {(
                    [
                        [UsersIcon, 'Mes réunions', summary.groups_count],
                        [
                            HandCoinsIcon,
                            'Versements d’assurance',
                            summary.insurance_payments_count,
                        ],
                        [
                            CalendarDaysIcon,
                            'Cotisations non payées',
                            summary.contributions_due_count,
                        ],
                        [
                            LandmarkIcon,
                            'Prêts actifs',
                            summary.active_loans_count,
                        ],
                    ] as const
                ).map(([Icon, label, value]) => (
                    <Card key={String(label)}>
                        <CardContent className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    {String(label)}
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {String(value)}
                                </p>
                            </div>
                            <Icon className="size-5 text-primary" />
                        </CardContent>
                    </Card>
                ))}
            </section>
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <CardTitle>Mes réunions</CardTitle>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Réunion</TableHead>
                                <TableHead>N° membre</TableHead>
                                <TableHead>Session active</TableHead>
                                <TableHead>Assurance accumulée</TableHead>
                                <TableHead className="pr-6 text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {collection.data.map((item) => (
                                <TableRow key={item.id} className="h-14">
                                    <TableCell className="pl-6 font-medium">
                                        {item.group.name}
                                    </TableCell>
                                    <TableCell>{item.member_number}</TableCell>
                                    <TableCell>
                                        {item.active_session?.name ?? 'Aucune'}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {formatCurrency(
                                            item.insurance_total,
                                            item.group.currency,
                                        )}
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                size="sm"
                                                variant="outline"
                                            >
                                                <Link
                                                    href={account.insurance.index(
                                                        {
                                                            group: item.group
                                                                .slug,
                                                        },
                                                    )}
                                                >
                                                    Assurance
                                                </Link>
                                            </Button>
                                            <Button asChild size="sm">
                                                <Link
                                                    href={groups.show({
                                                        group: item.group.slug,
                                                    })}
                                                >
                                                    Consulter
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    {collection.data.length === 0 && (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            Vous n’appartenez encore à aucune réunion.
                        </p>
                    )}
                    <CollectionPagination
                        collection={collection}
                        className="px-6 pt-6"
                    />
                </CardContent>
            </Card>
        </AccountLayout>
    ),
);
