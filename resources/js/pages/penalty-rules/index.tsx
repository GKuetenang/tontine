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
import { withAppLayout } from '@/layouts/app-layout';
import tontines from '@/routes/tontines';
import type {
    BreadcrumbItem,
    PaginatedCollection,
    PenaltyRule,
    Tontine,
} from '@/types';
import { PenaltyRuleForm } from './form';

type Props = {
    tontine: Tontine;
    collection: PaginatedCollection<PenaltyRule>;
    triggers: SelectOption[];
    calculation_types: SelectOption[];
    grace_units: SelectOption[];
    q: string | null;
};

export default withAppLayout<Props>(
    ({ tontine }) =>
        [
            { title: 'Tontines', href: tontines.index() },
            {
                title: tontine.name,
                href: tontines.show({ tontine: tontine.slug! }),
            },
            { title: 'Règles de pénalité', href: '#' },
        ] as BreadcrumbItem[],
    ({
        tontine,
        collection,
        triggers,
        calculation_types: calculationTypes,
        grace_units: graceUnits,
        q,
    }) =>
        (() => {
            return (
                <>
                    <Head title="Règles de pénalité" />
                    <Heading
                        title="Règles de pénalité"
                        description={
                            'Configuration des pénalités de ' + tontine.name
                        }
                    />
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between">
                                <PenaltyRuleForm
                                    tontine={tontine}
                                    triggers={triggers}
                                    calculationTypes={calculationTypes}
                                    graceUnits={graceUnits}
                                    trigger={
                                        <Button className="w-fit">
                                            <PlusIcon />
                                            Ajouter une règle
                                        </Button>
                                    }
                                />
                                <Form
                                    {...tontines.penaltyRules.index.form({
                                        tontine: tontine.slug!,
                                    })}
                                    className="flex items-center gap-1"
                                >
                                    <Input
                                        autoFocus
                                        defaultValue={q ?? ''}
                                        placeholder="Rechercher une règle"
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
                                            Règle
                                        </SortableTableHead>
                                        <SortableTableHead field="trigger">
                                            Déclencheur
                                        </SortableTableHead>
                                        <SortableTableHead field="calculation_type">
                                            Calcul
                                        </SortableTableHead>
                                        <SortableTableHead field="value">
                                            Valeur
                                        </SortableTableHead>
                                        <SortableTableHead field="grace_period">
                                            Tolérance
                                        </SortableTableHead>
                                        <SortableTableHead field="is_automatic">
                                            Application
                                        </SortableTableHead>
                                        <SortableTableHead field="is_active">
                                            Statut
                                        </SortableTableHead>
                                        <TableHead className="pr-6 text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody className="[&_td]:py-3">
                                    {collection.data.map((rule) => (
                                        <TableRow
                                            key={rule.id}
                                            className="h-14"
                                        >
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
                                                <PenaltyRuleForm
                                                    tontine={tontine}
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
                                                            Configurer
                                                        </Button>
                                                    }
                                                />
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
        })(),
);
