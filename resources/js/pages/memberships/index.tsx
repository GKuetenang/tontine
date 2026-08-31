import { Form, Head } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { MembershipStatusBadge } from '@/components/membership-status-badge';
import type { SelectOption } from '@/components/select-with-items';
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
import { useAuthorization } from '@/hooks/use-authorization';
import { withAppLayout } from '@/layouts/app-layout';
import tontines from '@/routes/tontines';
import memberships from '@/routes/tontines/memberships';
import type {
    BreadcrumbItem,
    Membership,
    PaginatedCollection,
    ResultTontine,
} from '@/types';
import { Actions } from './actions';
import { EditMembershipForm } from './form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
    {
        title: 'Membres',
        href: '#',
    },
];

type Props = {
    collection: PaginatedCollection<Membership>;
    q: string | null;
    tontine: ResultTontine;
    roles: SelectOption[];
    membership: Membership;
    statuses: SelectOption[];
};

export default withAppLayout(
    breadcrumbs,
    ({ collection, q, tontine, roles, membership, statuses }: Props) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Tous les membres" />
                <Heading title="Tous les membres" />
                <div className="space-y-4">
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between">
                                {can('memberships.create') && (
                                    <EditMembershipForm
                                        statuses={[]}
                                        membership={membership}
                                        roles={roles}
                                        tontine={tontine}
                                        trigger={
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="w-fit"
                                            >
                                                <PlusIcon />
                                                Ajouter un membre
                                            </Button>
                                        }
                                    />
                                )}
                                <Form
                                    {...memberships.index.form({
                                        tontine: tontine.slug,
                                    })}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher un membre"
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
                                        <SortableTableHead field="member_number">
                                            Numéro
                                        </SortableTableHead>
                                        <TableHead>Nom</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead>Statut</TableHead>
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
                                                {item.member_number}
                                            </TableCell>
                                            <TableCell>
                                                {item.user?.name}
                                            </TableCell>
                                            <TableCell>
                                                {item.user?.email}
                                            </TableCell>
                                            <TableCell>
                                                {item.role?.label}
                                            </TableCell>
                                            <TableCell>
                                                <MembershipStatusBadge
                                                    status={item.status}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <Actions
                                                    membership={item}
                                                    roles={roles}
                                                    tontine={tontine}
                                                    statuses={statuses}
                                                />
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
    },
);
