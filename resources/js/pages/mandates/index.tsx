import { Head, Link } from '@inertiajs/react';
import {
    BadgeCheckIcon,
    PencilIcon,
    PlusIcon,
    Settings2Icon,
} from 'lucide-react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
import groups from '@/routes/groups';
import mandates from '@/routes/groups/mandates';
import type { BreadcrumbItem, Group, PaginatedCollection } from '@/types';
import { MandateForm } from './form';

export type MandateItem = {
    id: number;
    name: string;
    starts_at: string;
    ends_at: string;
    status: string;
    status_label: string;
    editable: boolean;
    assignable: boolean;
};
type Props = {
    group: Group;
    collection: PaginatedCollection<MandateItem>;
};

export default withAppLayout<Props>(
    ({ group }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: groups.show({ group: group.slug! }) },
            { title: 'Mandats', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, collection }) => {
        const { can } = useAuthorization();

        return (
            <>
                <Head title="Mandats" />
                <Heading
                    title="Mandats"
                    description={`Historique de la gouvernance de ${group.name}.`}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between">
                            {can('mandates.create') && (
                                <MandateForm
                                    group={group}
                                    trigger={
                                        <Button className="w-fit">
                                            <PlusIcon /> Ajouter un mandat
                                        </Button>
                                    }
                                />
                            )}
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
                                        Mandat
                                    </SortableTableHead>
                                    <SortableTableHead field="starts_at">
                                        Période
                                    </SortableTableHead>
                                    <SortableTableHead field="status">
                                        Statut
                                    </SortableTableHead>
                                    <TableHead className="pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((mandate) => (
                                    <TableRow key={mandate.id} className="h-14">
                                        <TableCell className="pl-6 font-medium">
                                            {mandate.name}
                                        </TableCell>
                                        <TableCell>
                                            {mandate.starts_at} –{' '}
                                            {mandate.ends_at}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    mandate.status === 'active'
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {mandate.status_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="pr-6">
                                            <div className="flex justify-end gap-1">
                                                {can('mandates.update') &&
                                                    mandate.editable && (
                                                        <MandateForm
                                                            group={group}
                                                            mandate={mandate}
                                                            trigger={
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                >
                                                                    <PencilIcon />{' '}
                                                                    Modifier
                                                                </Button>
                                                            }
                                                        />
                                                    )}
                                                {can(
                                                    'mandates.roles.assign',
                                                ) && (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={mandates.assignments.index(
                                                                {
                                                                    group: group.slug!,
                                                                    mandate:
                                                                        mandate.id,
                                                                },
                                                            )}
                                                        >
                                                            <Settings2Icon />{' '}
                                                            Gérer les
                                                            responsabilités
                                                        </Link>
                                                    </Button>
                                                )}
                                                {can('mandates.activate') &&
                                                    mandate.editable && (
                                                        <Button
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                {...mandates.activate(
                                                                    {
                                                                        group: group.slug!,
                                                                        mandate:
                                                                            mandate.id,
                                                                    },
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                onBefore={() =>
                                                                    confirm(
                                                                        'Voulez-vous activer ce mandat ? Le mandat actuellement actif sera clôturé.',
                                                                    )
                                                                }
                                                            >
                                                                <BadgeCheckIcon />{' '}
                                                                Activer
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
                                Aucun mandat enregistré.
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
