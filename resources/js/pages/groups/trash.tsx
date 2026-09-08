import { Head, Link } from '@inertiajs/react';
import { RotateCcwIcon, Trash2Icon } from 'lucide-react';
import { toast } from 'sonner';
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
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import groups from '@/routes/groups';
import type { BreadcrumbItem, PaginatedCollection } from '@/types';

type Item = {
    id: number;
    name: string;
    slug: string;
    deleted_at: string;
    can: { restore: boolean; force_delete: boolean };
};

type Props = { collection: PaginatedCollection<Item> };

export default withAppLayout<Props>(
    [
        { title: 'Réunions', href: groups.index() },
        { title: 'Corbeille', href: '#' },
    ] as BreadcrumbItem[],
    ({ collection }) => (
        <>
            <Head title="Corbeille des réunions" />
            <Heading
                title="Corbeille des réunions"
                description="Restaurez une réunion ou supprimez-la définitivement."
            />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <CardTitle>Réunions supprimées</CardTitle>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Réunion</TableHead>
                                <TableHead>Suppression</TableHead>
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
                                        {formatDate(item.deleted_at)}
                                    </TableCell>
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-2">
                                            {item.can.restore && (
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={groups.restore({
                                                            group: item.slug,
                                                        })}
                                                        method="patch"
                                                        as="button"
                                                    >
                                                        <RotateCcwIcon />{' '}
                                                        Restaurer
                                                    </Link>
                                                </Button>
                                            )}
                                            {item.can.force_delete && (
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    variant="destructive"
                                                >
                                                    <Link
                                                        href={groups.forceDelete(
                                                            {
                                                                group: item.slug,
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
                                                        <Trash2Icon /> Supprimer
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
                            La corbeille est vide.
                        </p>
                    )}
                    <CollectionPagination
                        className="px-6 pt-6"
                        collection={collection}
                    />
                </CardContent>
            </Card>
        </>
    ),
);
