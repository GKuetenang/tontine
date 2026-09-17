import { Form, Head } from '@inertiajs/react';
import { PlusIcon, SearchIcon, Settings2Icon } from 'lucide-react';

import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import type { SelectOption } from '@/components/select-with-items';
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
import { useAuthorization } from '@/hooks/use-authorization';
import { useTranslation } from '@/hooks/use-translation';
import { withAppLayout } from '@/layouts/app-layout';
import groups from '@/routes/groups';
import type {
    BreadcrumbItem,
    Group,
    PaginatedCollection,
    PenaltyRule,
} from '@/types';

import { PenaltyRuleForm } from './form';

type Props = {
    group: Group;
    collection: PaginatedCollection<PenaltyRule>;
    triggers: SelectOption[];
    calculation_types: SelectOption[];
    grace_units: SelectOption[];
    q: string | null;
};

export default withAppLayout<Props>(
    ({ group }) =>
        [
            { title: 'Réunions', href: groups.index() },
            {
                title: group.name,
                href: groups.show({ group: group.slug! }),
            },
            { title: 'Règles de pénalité', href: '#' },
        ] as BreadcrumbItem[],
    ({
        group,
        collection,
        triggers,
        calculation_types: calculationTypes,
        grace_units: graceUnits,
        q,
    }) => {
        const { t } = useTranslation();
        const { can } = useAuthorization();

        return (
            <>
                <Head title={t('Règles de pénalité')} />
                <Heading
                    title={t('Règles de pénalité')}
                    description={'Configuration des pénalités de ' + group.name}
                />
                <Card className="bg-background pt-0">
                    <CardHeader className="border-b py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                {can('penalties.create') && (
                                    <PenaltyRuleForm
                                        group={group}
                                        triggers={triggers}
                                        calculationTypes={calculationTypes}
                                        graceUnits={graceUnits}
                                        trigger={
                                            <Button className="w-fit">
                                                <PlusIcon />
                                                {t('Ajouter une règle')}
                                            </Button>
                                        }
                                    />
                                )}
                            </div>
                            <Form
                                {...groups.penaltyRules.index.form({
                                    group: group.slug!,
                                })}
                                className="flex items-center gap-1"
                            >
                                <Input
                                    autoFocus
                                    defaultValue={q ?? ''}
                                    placeholder={t('Rechercher une règle')}
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
                                        {t('Règle')}
                                    </SortableTableHead>
                                    <SortableTableHead field="trigger">
                                        {t('Déclencheur')}
                                    </SortableTableHead>
                                    <SortableTableHead field="calculation_type">
                                        {t('Calcul')}
                                    </SortableTableHead>
                                    <SortableTableHead field="value">
                                        {t('Valeur')}
                                    </SortableTableHead>
                                    <SortableTableHead field="grace_period">
                                        {t('Tolérance')}
                                    </SortableTableHead>
                                    <SortableTableHead field="is_automatic">
                                        {t('Application')}
                                    </SortableTableHead>
                                    <SortableTableHead field="is_active">
                                        {t('Statut')}
                                    </SortableTableHead>
                                    <TableHead className="pr-6 text-right">
                                        {t('Actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody className="[&_td]:py-3">
                                {collection.data.map((rule) => (
                                    <TableRow key={rule.id} className="h-14">
                                        <TableCell className="pl-6">
                                            <p className="font-medium">
                                                {rule.name}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            {rule.trigger_label}
                                        </TableCell>
                                        <TableCell>
                                            {rule.calculation_type_label}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {rule.value_label}
                                        </TableCell>
                                        <TableCell>
                                            {rule.grace_period_label}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {rule.application_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    rule.is_active
                                                        ? 'success'
                                                        : 'secondary'
                                                }
                                            >
                                                {rule.status_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="pr-6 text-right">
                                            {can('penalties.update') && (
                                                <PenaltyRuleForm
                                                    group={group}
                                                    rule={rule}
                                                    triggers={triggers}
                                                    calculationTypes={
                                                        calculationTypes
                                                    }
                                                    graceUnits={graceUnits}
                                                    trigger={
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                        >
                                                            <Settings2Icon />
                                                            {t('Configurer')}
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
                            <div className="py-10 text-center text-sm text-muted-foreground">
                                {q
                                    ? `Aucune règle de pénalité ne correspond à la recherche « ${q} ».`
                                    : 'Aucune règle de pénalité configurée.'}
                            </div>
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
