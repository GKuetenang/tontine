import { Form, Head } from '@inertiajs/react';
import { ListFilterIcon } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import finances from '@/routes/groups/finances';
import type { BreadcrumbItem, Group } from '@/types';

type FinancialSummary = {
    credits: string;
    debits: string;
    balance: string;
    outstanding_loans: string;
};

type Breakdown = {
    type: string;
    label: string;
    credits: string;
    debits: string;
};

type RecentTransaction = {
    id: number;
    type_label: string;
    direction: 'credit' | 'debit';
    direction_label: string;
    amount: string;
    member_name: string | null;
    session_name: string;
    occurred_at: string;
};

type Props = {
    group: Group;
    dashboard: {
        summary: FinancialSummary;
        breakdown: Breakdown[];
        recent_transactions: RecentTransaction[];
    };
    filters: {
        session_id?: number;
        meeting_id?: number;
        from?: string;
        to?: string;
    };
    sessions: {
        value: string;
        label: string;
        meetings: { value: string; label: string }[];
    }[];
};

export default withAppLayout<Props>(
    ({ group }) =>
        [
            { title: 'Réunions', href: groups.index() },
            {
                title: group.name,
                href: groups.show({ group: group.slug! }),
            },
            { title: 'Finances', href: '#' },
        ] as BreadcrumbItem[],
    ({ group, dashboard, filters, sessions }) => {
        const currency = group.currency ?? 'XAF';
        const [sessionId, setSessionId] = useState(
            filters.session_id ? String(filters.session_id) : 'all',
        );
        const [meetingId, setMeetingId] = useState(
            filters.meeting_id ? String(filters.meeting_id) : 'all',
        );
        const meetings =
            sessions.find((session) => session.value === sessionId)?.meetings ??
            [];

        return (
            <>
                <Head title="Finances" />
                <Heading
                    title="État financier"
                    description={`Situation financière consolidée de ${group.name}.`}
                />
                <div className="space-y-6">
                    <Card className="overflow-hidden bg-background py-0">
                        <CardHeader className="border-b pt-4">
                            <Form
                                {...finances.index.form({
                                    group: group.slug!,
                                })}
                                className="grid items-end gap-3 md:grid-cols-5"
                            >
                                <div className="space-y-2">
                                    <Label htmlFor="finance-session">
                                        Session
                                    </Label>
                                    <input
                                        type="hidden"
                                        name="session_id"
                                        value={
                                            sessionId === 'all' ? '' : sessionId
                                        }
                                    />
                                    <Select
                                        value={sessionId}
                                        onValueChange={(value) => {
                                            setSessionId(value);
                                            setMeetingId('all');
                                        }}
                                    >
                                        <SelectTrigger
                                            id="finance-session"
                                            className="mb-0 w-full"
                                        >
                                            <SelectValue placeholder="Toutes les sessions" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                Toutes les sessions
                                            </SelectItem>
                                            {sessions.map((session) => (
                                                <SelectItem
                                                    key={session.value}
                                                    value={session.value}
                                                >
                                                    {session.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="finance-meeting">
                                        Assise
                                    </Label>
                                    <input
                                        type="hidden"
                                        name="meeting_id"
                                        value={
                                            meetingId === 'all' ? '' : meetingId
                                        }
                                    />
                                    <Select
                                        value={meetingId}
                                        onValueChange={setMeetingId}
                                        disabled={sessionId === 'all'}
                                    >
                                        <SelectTrigger
                                            id="finance-meeting"
                                            className="mb-0 w-full"
                                        >
                                            <SelectValue placeholder="Toutes les assises" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                Toutes les assises
                                            </SelectItem>
                                            {meetings.map((meeting) => (
                                                <SelectItem
                                                    key={meeting.value}
                                                    value={meeting.value}
                                                >
                                                    {meeting.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="finance-from">Du</Label>
                                    <Input
                                        id="finance-from"
                                        type="date"
                                        name="from"
                                        defaultValue={filters.from ?? ''}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="finance-to">Au</Label>
                                    <Input
                                        id="finance-to"
                                        type="date"
                                        name="to"
                                        defaultValue={filters.to ?? ''}
                                    />
                                </div>
                                <Button variant="outline" className="w-fit">
                                    <ListFilterIcon /> Filtrer
                                </Button>
                            </Form>
                        </CardHeader>
                    </Card>

                    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {[
                            ['Solde disponible', dashboard.summary.balance],
                            ['Total des entrées', dashboard.summary.credits],
                            ['Total des sorties', dashboard.summary.debits],
                            [
                                'Prêts à recouvrer',
                                dashboard.summary.outstanding_loans,
                            ],
                        ].map(([label, value]) => (
                            <Card key={label}>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="mt-1 text-2xl font-semibold">
                                        {formatCurrency(value, currency)}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </section>

                    <div className="grid gap-6 xl:grid-cols-2">
                        <Card className="bg-background pt-0">
                            <CardHeader className="border-b py-4">
                                <CardTitle>Ventilation par type</CardTitle>
                            </CardHeader>
                            <CardContent className="px-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="pl-6">
                                                Type
                                            </TableHead>
                                            <TableHead>Entrées</TableHead>
                                            <TableHead className="pr-6 text-right">
                                                Sorties
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {dashboard.breakdown.map((item) => (
                                            <TableRow
                                                key={item.type}
                                                className="h-14"
                                            >
                                                <TableCell className="pl-6 font-medium">
                                                    {item.label}
                                                </TableCell>
                                                <TableCell className="text-emerald-600">
                                                    {formatCurrency(
                                                        item.credits,
                                                        currency,
                                                    )}
                                                </TableCell>
                                                <TableCell className="pr-6 text-right text-destructive">
                                                    {formatCurrency(
                                                        item.debits,
                                                        currency,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>

                        <Card className="bg-background pt-0">
                            <CardHeader className="border-b py-4">
                                <CardTitle>Dernières transactions</CardTitle>
                            </CardHeader>
                            <CardContent className="px-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="pl-6">
                                                Date
                                            </TableHead>
                                            <TableHead>Type</TableHead>
                                            <TableHead>Session</TableHead>
                                            <TableHead className="pr-6 text-right">
                                                Montant
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {dashboard.recent_transactions.map(
                                            (transaction) => (
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
                                                            {
                                                                transaction.type_label
                                                            }
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        {
                                                            transaction.session_name
                                                        }
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
                                                            currency,
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )}
                                    </TableBody>
                                </Table>
                                {dashboard.recent_transactions.length === 0 && (
                                    <p className="p-8 text-center text-sm text-muted-foreground">
                                        Aucune transaction pour cette période.
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </>
        );
    },
);
