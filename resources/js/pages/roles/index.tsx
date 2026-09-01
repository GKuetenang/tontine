import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon, Settings2Icon } from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
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
import { withAppLayout } from '@/layouts/app-layout';
import tontines from '@/routes/tontines';
import roles from '@/routes/tontines/roles';
import type { BreadcrumbItem, PaginatedCollection, Tontine } from '@/types';
import type { PermissionOption, RoleItem } from './form';
import { RoleForm } from './form';

type Props = {
    tontine: Tontine;
    collection: PaginatedCollection<RoleItem>;
    permissions: PermissionOption[];
    q: string | null;
    can: { create: boolean; update: boolean };
};

export default withAppLayout<Props>(
    ({ tontine }) =>
        [
            { title: 'Tontines', href: tontines.index() },
            {
                title: tontine.name,
                href: tontines.show({ tontine: tontine.slug! }),
            },
            { title: 'Rôles et permissions', href: '#' },
        ] as BreadcrumbItem[],
    ({ tontine, collection, permissions, q, can }) => (
        <>
            <Head title="Rôles et permissions" />
            <Heading
                title="Rôles et permissions"
                description={`Gérez les responsabilités au sein de ${tontine.name}.`}
            />
            <Card className="bg-background pt-0">
                <CardHeader className="border-b py-4">
                    <div className="flex items-center justify-between">
                        {can.create && (
                            <RoleForm
                                tontine={tontine}
                                permissions={permissions}
                                trigger={
                                    <Button className="w-fit">
                                        <PlusIcon /> Ajouter un rôle
                                    </Button>
                                }
                            />
                        )}
                        <Form
                            {...roles.index.form({ tontine: tontine.slug! })}
                            className="flex items-center gap-1"
                        >
                            <Input
                                autoFocus
                                defaultValue={q ?? ''}
                                placeholder="Rechercher un rôle"
                                name="q"
                            />
                            <Button variant="outline">
                                <SearchIcon /> Rechercher
                            </Button>
                        </Form>
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="border-spacing-4">
                        <TableHeader>
                            <TableRow>
                                <SortableTableHead
                                    field="name"
                                    className="pl-6"
                                >
                                    Rôle
                                </SortableTableHead>
                                <TableHead>Membres</TableHead>
                                <TableHead>Permissions</TableHead>
                                <TableHead className="pr-6 text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {collection.data.map((role) => (
                                <TableRow key={role.id} className="h-14">
                                    <TableCell className="pl-6 font-medium">
                                        {role.label}
                                    </TableCell>
                                    <TableCell>{role.users_count}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {role.permissions_count}{' '}
                                            permission(s)
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {can.update && role.editable && (
                                            <RoleForm
                                                tontine={tontine}
                                                permissions={permissions}
                                                role={role}
                                                trigger={
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        <Settings2Icon />{' '}
                                                        Configurer
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
                                ? `Aucun rôle ne correspond à la recherche « ${q} ».`
                                : 'Aucun rôle configuré.'}
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
