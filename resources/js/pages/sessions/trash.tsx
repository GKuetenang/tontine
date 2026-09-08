import { Form, Head, Link } from '@inertiajs/react';
import { RotateCcwIcon, SearchIcon, Trash2Icon } from 'lucide-react';
import { toast } from 'sonner';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SessionStatusBadge } from '@/components/session-status-badge';
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
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type { BreadcrumbItem, PaginatedCollection, Session } from '@/types';

type Props = {
    group: { id: number; name: string; slug: string };
    collection: PaginatedCollection<Session>;
    q: string | null;
};

export default withAppLayout<Props>(
    ({ group }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: groups.show({ group: group.slug }) },
            { title: 'Sessions', href: sessions.index({ group: group.slug }) },
            { title: 'Corbeille', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, collection, q }) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Corbeille des sessions" />
                <Heading
                    title="Corbeille des sessions"
                    description={`Sessions supprimées de ${group.name}.`}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between gap-3">
                            <CardTitle>Sessions supprimées</CardTitle>
                            <Form
                                {...sessions.trash.form({ group: group.slug })}
                                className="flex items-center gap-1"
                            >
                                <Input
                                    defaultValue={q ?? ''}
                                    name="q"
                                    placeholder="Rechercher une session"
                                />
                                <Button variant="outline">
                                    <SearchIcon /> Rechercher
                                </Button>
                            </Form>
                        </div>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <SortableTableHead
                                        field="name"
                                        className="pl-6"
                                    >
                                        Session
                                    </SortableTableHead>
                                    <TableHead>Statut</TableHead>
                                    <SortableTableHead field="deleted_at">
                                        Supprimée le
                                    </SortableTableHead>
                                    <TableHead className="pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className="h-14">
                                        <TableCell className="pl-6 font-medium">
                                            {item.name}
                                        </TableCell>
                                        <TableCell>
                                            <SessionStatusBadge
                                                session={item}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {item.deleted_at
                                                ? formatDate(item.deleted_at)
                                                : '—'}
                                        </TableCell>
                                        <TableCell className="pr-6">
                                            <div className="flex justify-end gap-2">
                                                {can('sessions.restore') && (
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        <Link
                                                            href={sessions.restore(
                                                                {
                                                                    group: group.slug,
                                                                    session:
                                                                        item.slug,
                                                                },
                                                            )}
                                                            method="patch"
                                                            as="button"
                                                        >
                                                            <RotateCcwIcon />{' '}
                                                            Restaurer
                                                        </Link>
                                                    </Button>
                                                )}
                                                {can(
                                                    'sessions.force-delete',
                                                ) && (
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="destructive"
                                                    >
                                                        <Link
                                                            href={sessions.forceDelete(
                                                                {
                                                                    group: group.slug,
                                                                    session:
                                                                        item.slug,
                                                                },
                                                            )}
                                                            method="delete"
                                                            as="button"
                                                            onBefore={() =>
                                                                confirm(
                                                                    `Supprimer définitivement « ${item.name} » ? Cette action est irréversible.`,
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
                                                            Supprimer
                                                            définitivement
                                                        </Link>
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {collection.data.length === 0 && (
                            <p className="p-8 text-center text-sm text-muted-foreground">
                                {q
                                    ? `Aucune session supprimée ne correspond à la recherche « ${q} ».`
                                    : 'La corbeille est vide.'}
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
