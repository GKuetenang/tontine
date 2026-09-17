import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon, ShieldOffIcon } from 'lucide-react';

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
import { useTranslation } from '@/hooks/use-translation';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import penalties from '@/routes/groups/sessions/penalties';
import type {
    BreadcrumbItem,
    PaginatedCollection,
    Penalty,
    Session,
} from '@/types';

import { PenaltyForm } from './form';
import { WaivePenaltyForm } from './waive-form';

type Props = {
    group: { id: number; name: string; slug: string; currency: string };
    session: Session;
    collection: PaginatedCollection<Penalty>;
    q: string | null;
    meetings: SelectOption[];
    rules: SelectOption[];
};

export default withAppLayout<Props>(
    ({ group, session }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: groups.show({ group: group.slug }) },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug,
                    session: session.slug,
                }),
            },
            { title: 'Pénalités', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, session, collection, q, meetings, rules }) => {
        const { t } = useTranslation();
        const { can } = useAuthorization();

        return (
            <>
                <Head title={t('Pénalités')} />
                <Heading
                    title={t('Pénalités')}
                    description={`Pénalités appliquées pendant la session ${session.name}.`}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between">
                            {can('penalties.create') && (
                                <PenaltyForm
                                    group={group}
                                    session={session}
                                    meetings={meetings}
                                    rules={rules}
                                    trigger={
                                        <Button className="w-fit">
                                            <PlusIcon />{' '}
                                            {t('Ajouter une pénalité')}
                                        </Button>
                                    }
                                />
                            )}
                            <Form
                                {...penalties.index.form({
                                    group: group.slug,
                                    session: session.slug,
                                })}
                                className="flex items-center gap-1"
                            >
                                <Input
                                    name="q"
                                    defaultValue={q ?? ''}
                                    placeholder={t('Rechercher un membre')}
                                />
                                <Button variant="outline">
                                    <SearchIcon /> {t('Rechercher')}
                                </Button>
                            </Form>
                        </div>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="pl-6">
                                        {t('Membre')}
                                    </TableHead>
                                    <TableHead>{t('Règle')}</TableHead>
                                    <TableHead>{t('Assise')}</TableHead>
                                    <SortableTableHead field="source">
                                        {t('Origine')}
                                    </SortableTableHead>
                                    <SortableTableHead field="assessed_at">
                                        {t('Date')}
                                    </SortableTableHead>
                                    <SortableTableHead field="amount">
                                        {t('Montant')}
                                    </SortableTableHead>
                                    <SortableTableHead field="status">
                                        {t('Statut')}
                                    </SortableTableHead>
                                    <TableHead className="pr-6 text-right">
                                        {t('Actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className="h-14">
                                        <TableCell className="pl-6 font-medium">
                                            {item.member_name}
                                        </TableCell>
                                        <TableCell>{item.rule_name}</TableCell>
                                        <TableCell>
                                            {item.meeting_name}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {item.source_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(item.assessed_at)}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {formatCurrency(
                                                item.amount,
                                                group.currency,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    item.status === 'waived'
                                                        ? 'secondary'
                                                        : 'destructive'
                                                }
                                            >
                                                {item.status_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="pr-6 text-right">
                                            {item.status === 'pending' &&
                                                can('penalties.update') && (
                                                    <WaivePenaltyForm
                                                        group={group.slug}
                                                        session={session.slug}
                                                        penalty={item.id}
                                                        trigger={
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                            >
                                                                <ShieldOffIcon />{' '}
                                                                {t('Exempter')}
                                                            </Button>
                                                        }
                                                    />
                                                )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {collection.data.length === 0 && (
                            <p className="p-8 text-center text-sm text-muted-foreground">
                                {q
                                    ? `Aucune pénalité ne correspond à la recherche « ${q} ».`
                                    : 'Aucune pénalité enregistrée.'}
                            </p>
                        )}
                        <CollectionPagination
                            className="px-6 pt-6"
                            collection={collection}
                        />
                    </CardContent>
                </Card>
            </>
        );
    },
);
