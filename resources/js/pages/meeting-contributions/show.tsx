import {
    BanknoteIcon,
    CheckCircle2Icon,
    CircleDollarSignIcon,
    CoinsIcon,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { useAuthorization } from '@/hooks/use-authorization';
import { formatCurrency } from '@/lib/utils';

import type { Meeting, Session, Tontine } from '@/types';

import { ContributionRow } from './contribution-row';
import { ContributionSummaryCard } from './contribution-summary-card';
import { ContributionPlaceholder } from './placeholder';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function MeetingContributions({ tontine, session, meeting }: Props) {
    const { can } = useAuthorization();

    if (meeting.status === 'scheduled') {
        return <ContributionPlaceholder />;
    }

    const contributions = meeting.contributions ?? [];

    const totalDue = contributions.reduce(
        (total, contribution) => total + contribution.amount_due,
        0,
    );

    const totalPaid = contributions.reduce(
        (total, contribution) => total + contribution.amount_paid,
        0,
    );

    const totalRemaining = totalDue - totalPaid;

    const paidCount = contributions.filter(
        (contribution) => contribution.status === 'paid',
    ).length;

    return (
        <Card>
            <CardHeader>
                <CardTitle>Cotisations</CardTitle>
            </CardHeader>

            <CardContent className="space-y-6 px-0">
                <div className="grid gap-4 px-6 md:grid-cols-2 xl:grid-cols-4">
                    <ContributionSummaryCard
                        title="Total attendu"
                        value={formatCurrency(totalDue, tontine.currency)}
                        icon={CoinsIcon}
                    />

                    <ContributionSummaryCard
                        title="Encaissé"
                        value={formatCurrency(totalPaid, tontine.currency)}
                        icon={BanknoteIcon}
                    />

                    <ContributionSummaryCard
                        title="Reste à payer"
                        value={formatCurrency(totalRemaining, tontine.currency)}
                        icon={CircleDollarSignIcon}
                    />

                    <ContributionSummaryCard
                        title="Payées"
                        value={`${paidCount} / ${contributions.length}`}
                        icon={CheckCircle2Icon}
                    />
                </div>

                <Table>
                    <TableHeader>
                        <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                            <TableHead>Membre</TableHead>

                            <TableHead>N° membre</TableHead>

                            <TableHead>Montant dû</TableHead>

                            <TableHead>Payé</TableHead>

                            <TableHead>Reste</TableHead>

                            <TableHead>Statut</TableHead>

                            <TableHead className="text-end" />
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {contributions.map((contribution) => (
                            <ContributionRow
                                key={contribution.id}
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                                contribution={contribution}
                                canPay={can('contributions.pay')}
                            />
                        ))}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}
