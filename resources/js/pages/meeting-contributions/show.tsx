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
import { useTranslation } from '@/hooks/use-translation';
import { formatCurrency } from '@/lib/utils';

import type { Meeting, Session, Group } from '@/types';

import { ContributionRow } from './contribution-row';
import { ContributionSummaryCard } from './contribution-summary-card';
import { ContributionPlaceholder } from './placeholder';

type Props = {
    group: Group;
    session: Session;
    meeting: Meeting;
};

export function MeetingContributions({ group, session, meeting }: Props) {
    const { t } = useTranslation();
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
                <CardTitle>{t('Cotisations')}</CardTitle>
            </CardHeader>

            <CardContent className="space-y-6 px-0">
                <div className="grid gap-4 px-6 md:grid-cols-2 xl:grid-cols-4">
                    <ContributionSummaryCard
                        title={t('Total attendu')}
                        value={formatCurrency(totalDue, group.currency)}
                        icon={CoinsIcon}
                    />

                    <ContributionSummaryCard
                        title={t('Encaissé')}
                        value={formatCurrency(totalPaid, group.currency)}
                        icon={BanknoteIcon}
                    />

                    <ContributionSummaryCard
                        title={t('Reste à payer')}
                        value={formatCurrency(totalRemaining, group.currency)}
                        icon={CircleDollarSignIcon}
                    />

                    <ContributionSummaryCard
                        title={t('Payées')}
                        value={`${paidCount} / ${contributions.length}`}
                        icon={CheckCircle2Icon}
                    />
                </div>

                <Table>
                    <TableHeader>
                        <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                            <TableHead>{t('Membre')}</TableHead>

                            <TableHead>{t('N° membre')}</TableHead>

                            <TableHead>{t('Montant dû')}</TableHead>

                            <TableHead>{t('Payé')}</TableHead>

                            <TableHead>{t('Reste')}</TableHead>

                            <TableHead>{t('Statut')}</TableHead>

                            <TableHead className="text-end" />
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {contributions.map((contribution) => (
                            <ContributionRow
                                key={contribution.id}
                                group={group}
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
