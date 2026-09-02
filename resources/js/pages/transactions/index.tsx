import { Form, Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon, ListFilterIcon } from 'lucide-react';
import { useState } from 'react';
import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import type { SelectOption } from '@/components/select-with-items';
import { SortableTableHead } from '@/components/sortable-table-head';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type {
    BreadcrumbItem,
    PaginatedCollection,
    Session,
    Group,
    Transaction,
} from '@/types';

type Props = {
    group: Group;
    session: Session;
    collection: PaginatedCollection<Transaction>;
    filters: { direction?: string; type?: string; from?: string; to?: string };
    summary: { credits: string; debits: string; balance: string };
    transaction_types: SelectOption[];
    transaction_directions: SelectOption[];
};

function DateFilter({
    value,
    onChange,
    placeholder,
}: {
    value?: Date;
    onChange: (date?: Date) => void;
    placeholder: string;
}) {
    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    className="w-full justify-start border border-input font-normal"
                >
                    <CalendarIcon />
                    {value ? (
                        format(value, 'dd/MM/yyyy')
                    ) : (
                        <span className="text-muted-foreground">
                            {placeholder}
                        </span>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
                <Calendar mode="single" selected={value} onSelect={onChange} />
            </PopoverContent>
        </Popover>
    );
}

export default withAppLayout<Props>(
    ({ group, session }) =>
        [
            { title: 'Réunions', href: groups.index() },
            {
                title: group.name,
                href: groups.show({ group: group.slug! }),
            },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug!,
                    session: session.slug,
                }),
            },
            { title: 'Transactions', href: '#' },
        ] as BreadcrumbItem[],
    ({
        group,
        session,
        collection,
        filters,
        summary,
        transaction_types,
        transaction_directions,
    }) => {
        const [direction, setDirection] = useState(filters.direction || 'all');
        const [type, setType] = useState(filters.type || 'all');
        const [from, setFrom] = useState<Date | undefined>(
            filters.from ? new Date(`${filters.from}T00:00:00`) : undefined,
        );
        const [to, setTo] = useState<Date | undefined>(
            filters.to ? new Date(`${filters.to}T00:00:00`) : undefined,
        );
        const typeLabels = Object.fromEntries(
            transaction_types.map((option) => [option.value, option.label]),
        );
        const directionLabels = Object.fromEntries(
            transaction_directions.map((option) => [
                option.value,
                option.label,
            ]),
        );

        const hasSelectedFilters =
            direction !== 'all' || type !== 'all' || !!from || !!to;
        const hasAppliedFilters = Object.values(filters).some(Boolean);
        const canFilter = hasSelectedFilters || hasAppliedFilters;

        return (
            <>
                <Head title="Journal des transactions" />
                <div className="space-y-6">
                    <Heading
                        title="Journal financier"
                        description={`Mouvements auditables de la session ${session.name}.`}
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        {(
                            [
                                ['Crédits', summary.credits],
                                ['Débits', summary.debits],
                                ['Solde', summary.balance],
                            ] as const
                        ).map(([label, value]) => (
                            <Card key={label}>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="mt-1 text-2xl font-semibold">
                                        {formatCurrency(value)}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                    <Card className="bg-background pt-0">
                        <CardHeader className="border-b py-4">
                            <div className="flex items-center justify-between gap-4">
                                <CardTitle>Transactions</CardTitle>
                                <Form
                                    {...sessions.transactions.index.form({
                                        group: group.slug!,
                                        session: session.slug,
                                    })}
                                    className="grid flex-1 gap-2 sm:grid-cols-5"
                                >
                                    <input
                                        type="hidden"
                                        name="direction"
                                        value={
                                            direction === 'all' ? '' : direction
                                        }
                                    />
                                    <Select
                                        value={direction}
                                        onValueChange={setDirection}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue placeholder="Toutes directions" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                Toutes directions
                                            </SelectItem>
                                            {transaction_directions.map(
                                                (option) => (
                                                    <SelectItem
                                                        key={option.value}
                                                        value={option.value}
                                                    >
                                                        {option.label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="type"
                                        value={type === 'all' ? '' : type}
                                    />
                                    <Select
                                        value={type}
                                        onValueChange={setType}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue placeholder="Tous types" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                Tous types
                                            </SelectItem>
                                            {transaction_types.map((option) => (
                                                <SelectItem
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="from"
                                        value={
                                            from
                                                ? format(from, 'yyyy-MM-dd')
                                                : ''
                                        }
                                    />
                                    <DateFilter
                                        value={from}
                                        onChange={setFrom}
                                        placeholder="Date de début"
                                    />
                                    <input
                                        type="hidden"
                                        name="to"
                                        value={
                                            to ? format(to, 'yyyy-MM-dd') : ''
                                        }
                                    />
                                    <DateFilter
                                        value={to}
                                        onChange={setTo}
                                        placeholder="Date de fin"
                                    />
                                    <Button
                                        variant="outline"
                                        className="w-fit"
                                        disabled={!canFilter}
                                    >
                                        <ListFilterIcon /> Filtrer
                                    </Button>
                                </Form>
                            </div>
                        </CardHeader>
                        <CardContent className="px-0">
                            <Table className="border-spacing-4">
                                <TableHeader>
                                    <TableRow>
                                        <SortableTableHead
                                            field="occurred_at"
                                            className="pl-6"
                                        >
                                            Date
                                        </SortableTableHead>
                                        <SortableTableHead field="type">
                                            Type
                                        </SortableTableHead>
                                        <TableHead>Membre</TableHead>
                                        <SortableTableHead field="direction">
                                            Direction
                                        </SortableTableHead>
                                        <SortableTableHead
                                            field="amount"
                                            className="pr-6 text-right"
                                        >
                                            Montant
                                        </SortableTableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody className="[&_td]:py-3">
                                    {collection.data.map((transaction) => (
                                        <TableRow
                                            key={transaction.id}
                                            className="h-14"
                                        >
                                            <TableCell className="pl-6">
                                                {formatDate(
                                                    transaction.occurred_at,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {typeLabels[
                                                        transaction.type
                                                    ] ?? transaction.type}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {transaction.member_name ?? '—'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        transaction.direction ===
                                                        'credit'
                                                            ? 'success'
                                                            : 'secondary'
                                                    }
                                                >
                                                    {
                                                        directionLabels[
                                                            transaction
                                                                .direction
                                                        ]
                                                    }
                                                </Badge>
                                            </TableCell>
                                            <TableCell
                                                className={`pr-6 text-right font-medium ${transaction.direction === 'credit' ? 'text-emerald-600' : 'text-destructive'}`}
                                            >
                                                {transaction.direction ===
                                                'credit'
                                                    ? '+'
                                                    : '−'}{' '}
                                                {formatCurrency(
                                                    transaction.amount,
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            {collection.data.length === 0 && (
                                <p className="p-8 text-center text-sm text-muted-foreground">
                                    Aucune transaction pour ces critères.
                                </p>
                            )}
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
