import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SortableTableHead } from '@/components/sortable-table-head';
import { TopActions } from '@/components/top-actions';
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
import { withAppLayout } from '@/layouts/app-layout';
import memberships from '@/routes/memberships';
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, PaginatedCollection, Tontine } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';
import { EditIcon, PlusIcon, TrashIcon, UsersIcon } from 'lucide-react';

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
    console.log(collection);
    return (
        <>
            <Head title='Toutes les tontines' />
            <Heading
                title='Toutes les tontines'
            />
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
                <Card className='bg-background pt-0'>
                    <CardHeader className='border-b py-4'>
                        <Button
                            asChild
                            variant="outline"
                            className="w-fit"
                        >
                            <Link href={tontines.create()}>
                                <PlusIcon />
                                Ajouter une tontine
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent className='px-0'>
                        <Table className='border-spacing-4'>
                            <TableHeader>
                                <TableRow className='[&>th:first-child]:pl-6 [&>th:last-child]:pr-6'>
                                    <SortableTableHead field="id">ID</SortableTableHead>
                                    <SortableTableHead field="name">Nom</SortableTableHead>
                                    <SortableTableHead field="member_number_prefix">Préfixe</SortableTableHead>
                                    <SortableTableHead field="slug">Slug</SortableTableHead>
                                    <TableHead>Members</TableHead>
                                    <TableHead className="text-end">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className='[&>td:first-child]:pl-6 [&>td:last-child]:pr-6'>
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
                                                    <div className="aspect-square size-14 bg-secondary rounded-lg"></div>
                                                )}
                                                <Link
                                                    className="hover:underline"
                                                    href={tontines.edit({
                                                        tontine: item.id!,
                                                    })}
                                                >
                                                    {item.name}
                                                </Link>
                                            </div>
                                        </TableCell>
                                        <TableCell>{item.member_number_prefix}</TableCell>
                                        <TableCell>{item.slug}</TableCell>
                                        <TableCell>
                                            <Button asChild variant='outline'>
                                                <Link href={memberships.index({ tontine: item.id! })}>
                                                    <UsersIcon size={16} />
                                                    {`${item.members_count} membre${item.members_count! > 1 ? 's' : ''}`}
                                                </Link>
                                            </Button>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center justify-end gap-2">
                                                <Button
                                                    asChild
                                                    size="icon"
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={tontines.edit({
                                                            tontine: item.id!,
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
                                                            tontine: item.id!,
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

                        <CollectionPagination className='px-6 pt-6' collection={collection} />
                    </CardContent>
                </Card>
            </div>
        </>
    );
});

// withAppLayout.layout = {

// }
