import { Form, Link } from '@inertiajs/react';
import { EditIcon, PlusIcon, TrashIcon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import { SortableTableHead } from '@/components/sortable-table-head';
import { TopActions } from '@/components/top-actions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { withAppLayout } from '@/layouts/app-layout';
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, PaginatedCollection, Tontine } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
];

type Props = {
    collection: PaginatedCollection<Tontine>;
    q: string | null;
};

export default withAppLayout(breadcrumbs, ({ collection, q }: Props) => {
    return (
        <div className="space-y-4">
            <TopActions>
                <Form
                    {...tontines.index.form()}
                    className="flex items-center gap-1"
                >
                    <Input
                        autoFocus
                        defaultValue={q ?? ''}
                        placeholder="Rechercher une tontine"
                        name="q"
                    />
                    <Button>Rechercher</Button>
                </Form>
            </TopActions>
            <Table>
                <TableHeader>
                    <TableRow>
                        <SortableTableHead field="id">ID</SortableTableHead>
                        <SortableTableHead field="name">Nom</SortableTableHead>
                        <SortableTableHead field="slug">Slug</SortableTableHead>
                        <TableHead className="text-end">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow>
                        <TableCell colSpan={5}>
                            <Button
                                asChild
                                variant="outline"
                                className="w-full"
                            >
                                <Link href={tontines.create()}>
                                    <PlusIcon />
                                    Ajouter une tontine
                                </Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                    {collection.data.map((item) => (
                        <TableRow key={item.id}>
                            <TableCell>{item.id}</TableCell>
                            <TableCell>
                                <div className="flex items-center gap-3">
                                    {item.image ? (
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            className="aspect-square w-14 rounded-lg object-cover"
                                        />
                                    ) : (
                                        <div className="aspect-square size-14 bg-background"></div>
                                    )}
                                    <Link
                                        className="hover:underline"
                                        href={tontines.edit({
                                            tontine: item.id,
                                        })}
                                    >
                                        {item.name}
                                    </Link>
                                </div>
                            </TableCell>
                            <TableCell>{item.slug}</TableCell>
                            <TableCell>
                                <div className="flex items-center justify-end gap-2">
                                    <Button
                                        asChild
                                        size="icon"
                                        variant="outline"
                                    >
                                        <Link
                                            href={tontines.edit({
                                                tontine: item.id,
                                            })}
                                        >
                                            <EditIcon size={16} />
                                        </Link>
                                    </Button>
                                    <Button
                                        asChild
                                        size="icon"
                                        variant="destructive-outline"
                                    >
                                        <Link
                                            href={tontines.destroy({
                                                tontine: item.id,
                                            })}
                                            onBefore={() =>
                                                confirm(
                                                    'Voulez-vous vraiment supprimer cet tontine?',
                                                )
                                            }
                                        >
                                            <TrashIcon size={16} />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
            <CollectionPagination collection={collection} />
        </div>
    );
});
