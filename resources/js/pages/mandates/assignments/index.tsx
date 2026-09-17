import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon } from 'lucide-react';

import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import type { SelectOption } from '@/components/select-with-items';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useAuthorization } from '@/hooks/use-authorization';
import { useTranslation } from '@/hooks/use-translation';
import { withAppLayout } from '@/layouts/app-layout';
import { EditMembershipForm } from '@/pages/memberships/form';
import groups from '@/routes/groups';
import mandates from '@/routes/groups/mandates';
import assignments from '@/routes/groups/mandates/assignments';
import type {
    BreadcrumbItem,
    Membership,
    PaginatedCollection,
    ResultGroup,
} from '@/types';

import AssignmentRow from './assignment-row';

import type { MandateMember } from './assignment-row';

type Props = {
    group: ResultGroup;
    mandate: { id: number; name: string; status: string };
    collection: PaginatedCollection<MandateMember>;
    roles: SelectOption[];
    membership: Membership;
    q: string | null;
};

export default withAppLayout<Props>(
    ({ group, mandate }) =>
        [
            { title: 'Réunions', href: groups.index() },
            { title: group.name, href: groups.show({ group: group.slug! }) },
            { title: 'Mandats', href: mandates.index({ group: group.slug! }) },
            { title: mandate.name, href: '#' },
        ] as BreadcrumbItem[],
    ({ group, mandate, collection, roles, q, membership }) => {
        const { t } = useTranslation();
        const { can } = useAuthorization();
        const canUpdate =
            mandate.status !== 'closed' && can('mandates.roles.assign');

        return (
            <>
                <Head title={`Responsabilités — ${mandate.name}`} />
                <Heading
                    title={t('Gérer les responsabilités')}
                    description={`Attribuez les rôles du mandat « ${mandate.name} » aux membres de la réunion.`}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between">
                            {can('memberships.create') && (
                                <EditMembershipForm
                                    statuses={[]}
                                    membership={membership}
                                    group={group}
                                    trigger={
                                        <Button type="button" className="w-fit">
                                            <PlusIcon />
                                            {t('Ajouter un membre')}
                                        </Button>
                                    }
                                />
                            )}
                            <Form
                                {...assignments.index.form({
                                    group: group.slug!,
                                    mandate: mandate.id,
                                })}
                                className="flex items-center gap-1"
                            >
                                <Input
                                    autoFocus
                                    defaultValue={q ?? ''}
                                    placeholder={t('Rechercher un membre')}
                                    name="q"
                                />
                                <Button variant="outline">
                                    <SearchIcon /> {t('Rechercher')}
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
                                        {t('Membre')}
                                    </SortableTableHead>
                                    <TableHead>
                                        {t('Rôle dans le mandat')}
                                    </TableHead>
                                    <TableHead className="pr-6 text-right">
                                        {t('Actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((member) => (
                                    <AssignmentRow
                                        key={member.id}
                                        group={group}
                                        mandateId={mandate.id}
                                        member={member}
                                        roles={roles}
                                        canUpdate={canUpdate}
                                    />
                                ))}
                            </TableBody>
                        </Table>
                        {collection.data.length === 0 && (
                            <p className="p-8 text-center text-sm text-muted-foreground">
                                {q
                                    ? `Aucun membre ne correspond à la recherche « ${q} ».`
                                    : 'Aucun membre actif.'}
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
