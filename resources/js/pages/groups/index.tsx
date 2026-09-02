import { Form, Head, Link } from '@inertiajs/react';
import { PlusIcon, SearchIcon } from 'lucide-react';
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
import groups from '@/routes/groups';
import type { BreadcrumbItem, PaginatedCollection, Group } from '@/types';
import { Actions } from './actions';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Réunions',
        href: groups.index().url,
    },
];

type Props = {
    collection: PaginatedCollection<Group>;
    q: string | null;
};

export default withAppLayout(breadcrumbs, ({ collection, q }: Props) => {
    // const { can } = useAuthorization();

    // const { auth } = usePage().props;
    // console.log(collection);

    return (
        <>
            <Head title="Toutes les réunions" />
            <Heading title="Toutes les réunions" />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <div className="flex items-center justify-between">
                        <Button asChild className="w-fit">
                            <Link href={groups.create()}>
                                <PlusIcon />
                                Ajouter une réunion
                            </Link>
                        </Button>
                        <Form
                            {...groups.index.form()}
                            className="flex items-center gap-1"
                        >
                            <Input
                                autoFocus
                                defaultValue={q ?? ''}
                                placeholder="Rechercher une réunion"
                                name="q"
                            />
                            <Button variant="outline">
                                <SearchIcon />
                                Rechercher
                            </Button>
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
                                <SortableTableHead field="default_contribution_amount">
                                    Cotisation par défaut
                                </SortableTableHead>
                                <SortableTableHead field="default_loan_interest_rate">
                                    Taux des prêts
                                </SortableTableHead>
                                <SortableTableHead field="default_loan_term_months">
                                    Échéance
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
                                                    href={groups.show({
                                                        group: item.slug!,
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
                                            item.currency,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {item.default_loan_interest_rate} %
                                    </TableCell>
                                    <TableCell>
                                        {item.default_loan_term_months} mois
                                    </TableCell>
                                    <TableCell>
                                        <Actions group={item} />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>

                    {collection.data.length === 0 && (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            {q
                                ? `Aucune réunion ne correspond à la recherche « ${q} ».`
                                : 'Aucune réunion enregistrée.'}
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
});

// withAppLayout.layout = {

// }
