import { Button } from '@/components/ui/button';
import {
    TableCell,
    TableRow,
} from '@/components/ui/table';

import { ContributionStatusBadge } from '@/components/contribution-status-badge';

import { formatCurrency } from '@/lib/utils';

import type {
    Contribution,
    Meeting,
    Session,
    Tontine,
} from '@/types';

import { BanknoteIcon } from 'lucide-react';

import { RecordContributionPaymentForm } from './form';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    contribution: Contribution;
    canPay: boolean;
};

export function ContributionRow({
    tontine,
    session,
    meeting,
    contribution,
    canPay,
}: Props) {
    const membership =
        contribution
            .session_participant
            ?.membership;

    const user =
        membership?.user;

    const canRecordPayment =
        canPay
        && contribution.remaining_amount > 0
        && (
            meeting.status === 'in_progress'
            || meeting.status === 'completed'
        );

    return (
        <TableRow className="[&>td:first-child]:pl-6 [&>td:last-child]:pr-6">
            <TableCell>
                <div className="flex flex-col">
                    <span className="font-medium">
                        {user?.name ?? '—'}
                    </span>

                    {user?.email && (
                        <span className="text-xs text-muted-foreground">
                            {user.email}
                        </span>
                    )}
                </div>
            </TableCell>

            <TableCell>
                {membership?.member_number ??
                    '—'}
            </TableCell>

            <TableCell>
                {formatCurrency(
                    contribution.amount_due,
                    tontine.currency,
                )}
            </TableCell>

            <TableCell className="font-medium text-emerald-600">
                {formatCurrency(
                    contribution.amount_paid,
                    tontine.currency,
                )}
            </TableCell>

            <TableCell>
                {formatCurrency(
                    contribution.remaining_amount,
                    tontine.currency,
                )}
            </TableCell>

            <TableCell>
                <ContributionStatusBadge
                    status={
                        contribution.status
                    }
                />
            </TableCell>

            <TableCell className="text-end">
                {canRecordPayment && (
                    <RecordContributionPaymentForm
                        tontine={tontine}
                        session={session}
                        meeting={meeting}
                        contribution={
                            contribution
                        }
                        trigger={
                            <Button
                                variant="outline"
                                size="sm"
                                className="rounded-full"
                            >
                                <BanknoteIcon className="size-4" />

                                Paiement
                            </Button>
                        }
                    />
                )}
            </TableCell>
        </TableRow>
    );
}