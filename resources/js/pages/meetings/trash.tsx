import { Form, Head, Link } from '@inertiajs/react';
import { RotateCcwIcon, SearchIcon, Trash2Icon } from 'lucide-react';
import { toast } from 'sonner';

import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { MeetingStatusBadge } from '@/components/meeting-status-badge';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import meetings from '@/routes/groups/sessions/meetings';
import type { BreadcrumbItem, Meeting, PaginatedCollection } from '@/types';

type Parent = { id: number; name: string; slug: string };
type Props = {
    group: Parent;
    session: Parent;
    collection: PaginatedCollection<Meeting>;
    q: string | null;
};

export default withAppLayout<Props>(
    ({ group, session }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: groups.show({ group: group.slug }) },
            { title: 'Sessions', href: sessions.index({ group: group.slug }) },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug,
                    session: session.slug,
                }),
            },
            {
                title: 'Assises',
                href: meetings.index({
                    group: group.slug,
                    session: session.slug,
                }),
            },
            { title: 'Corbeille', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, session, collection, q }) => {
        const { t } = useTranslation();
        const { can } = useAuthorization();
        const params = { group: group.slug, session: session.slug };

        return (
            <>
                <Head title={t('Corbeille des assises')} />
                <Heading
                    title={t('Corbeille des assises')}
                    description={`Assises supprimées de la session ${session.name}.`}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between gap-3">
                            <CardTitle>{t('Assises supprimées')}</CardTitle>
                            <Form
                                {...meetings.trash.form(params)}
                                className="flex items-center gap-1"
                            >
                                <Input
                                    defaultValue={q ?? ''}
                                    name="q"
                                    placeholder={t('Rechercher une assise')}
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
                                    <SortableTableHead
                                        field="number"
                                        className="pl-6"
                                    >
                                        {t('N°')}
                                    </SortableTableHead>
                                    <SortableTableHead field="title">
                                        {t('Assise')}
                                    </SortableTableHead>
                                    <TableHead>{t('Statut')}</TableHead>
                                    <SortableTableHead field="deleted_at">
                                        {t('Supprimée le')}
                                    </SortableTableHead>
                                    <TableHead className="pr-6 text-right">
                                        {t('Actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className="h-14">
                                        <TableCell className="pl-6">
                                            #{item.number}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {item.title}
                                        </TableCell>
                                        <TableCell>
                                            <MeetingStatusBadge
                                                meeting={item}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(item.deleted_at)}
                                        </TableCell>
                                        <TableCell className="pr-6">
                                            <div className="flex justify-end gap-2">
                                                {can('meetings.restore') && (
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        <Link
                                                            href={meetings.restore(
                                                                {
                                                                    ...params,
                                                                    meeting:
                                                                        item.slug,
                                                                },
                                                            )}
                                                            method="patch"
                                                            as="button"
                                                        >
                                                            <RotateCcwIcon />{' '}
                                                            {t('Restaurer')}
                                                        </Link>
                                                    </Button>
                                                )}
                                                {can(
                                                    'meetings.force-delete',
                                                ) && (
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="destructive"
                                                    >
                                                        <Link
                                                            href={meetings.forceDelete(
                                                                {
                                                                    ...params,
                                                                    meeting:
                                                                        item.slug,
                                                                },
                                                            )}
                                                            method="delete"
                                                            as="button"
                                                            onBefore={() =>
                                                                confirm(
                                                                    `Supprimer définitivement « ${item.title} » ?`,
                                                                )
                                                            }
                                                            onError={(errors) =>
                                                                toast.error(
                                                                    Object.values(
                                                                        errors,
                                                                    )[0],
                                                                )
                                                            }
                                                        >
                                                            <Trash2Icon />{' '}
                                                            {t(
                                                                'Supprimer définitivement',
                                                            )}
                                                        </Link>
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {collection.data.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="h-32 text-center text-muted-foreground"
                                        >
                                            {q
                                                ? `Aucune assise supprimée ne correspond à « ${q} ».`
                                                : 'La corbeille des assises est vide.'}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
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
