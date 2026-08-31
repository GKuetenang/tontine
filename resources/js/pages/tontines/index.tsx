import { Form, Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SortableTableHead } from '@/components/sortable-table-head';
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
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, PaginatedCollection, Tontine } from '@/types';
import { Actions } from './actions';

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
    // const { can } = useAuthorization();

    // const { auth } = usePage().props;
    // console.log(collection);

    return (
        <>
            <Head title="Toutes les tontines" />
            <Heading title="Toutes les tontines" />
            <div className="space-y-4">
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between">
                            <Button asChild variant="outline" className="w-fit">
                                <Link href={tontines.create()}>
                                    <PlusIcon />
                                    Ajouter une tontine
                                </Link>
                            </Button>
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
                        </div>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table className="border-spacing-4">
                            <TableHeader>
                                <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                                    <SortableTableHead field="name">
                                        Nom
                                    </SortableTableHead>
                                    <SortableTableHead field="member_number_prefix">
                                        Préfixe
                                    </SortableTableHead>
                                    <SortableTableHead field="default_contibution_amount">
                                        Montant par defaut
                                    </SortableTableHead>
                                    <TableHead className="text-end"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow
                                        key={item.id}
                                        className="[&>td:first-child]:pl-6 [&>td:last-child]:pr-6"
                                    >
                                        <TableCell>
                                            <div className="flex items-center gap-3">
                                                {item.image ? (
                                                    <img
                                                        src={item.image}
                                                        alt={item.name}
                                                        className="aspect-square w-14 rounded-lg object-cover"
                                                    />
                                                ) : (
                                                    <div className="aspect-square size-14 rounded-lg bg-secondary"></div>
                                                )}
                                                {item.can?.update && (
                                                    <Link
                                                        disabled={true}
                                                        className="hover:underline"
                                                        href={tontines.show({
                                                            tontine: item.slug!,
                                                        })}
                                                    >
                                                        {item.name}
                                                    </Link>
                                                )}
                                                {!item.can?.update && (
                                                    <span>{item.name}</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {item.member_number_prefix}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(
                                                item.default_contribution_amount,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Actions tontine={item} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        <CollectionPagination
                            className="px-6 pt-6"
                            collection={collection}
                        />
                    </CardContent>
                </Card>
            </div>
        </>
    );
});

// withAppLayout.layout = {

// }
