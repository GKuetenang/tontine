import { Link } from '@inertiajs/react';
import { CheckIcon, XIcon } from 'lucide-react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import { useAuthorization } from '@/hooks/use-authorization';

import { formatCurrency } from '@/lib/utils';
import payouts from '@/routes/tontines/sessions/meetings/payouts';

import type { Meeting, Payout, Session, Tontine } from '@/types';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    payout: Payout;
};

export function PayoutItem({ tontine, session, meeting, payout }: Props) {
    const { can } = useAuthorization();

    const beneficiary =
        payout.draw_entry?.session_participant?.membership?.user?.name ?? '—';

    const handleError = (errors: Record<string, string>) => {
        const firstError = Object.values(errors)[0];

        if (firstError) {
            toast.error(firstError);
        }
    };

    return (
        <div className="flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="space-y-1">
                <div className="flex flex-wrap items-center gap-2">
                    <p className="font-medium">{beneficiary}</p>

                    <Badge
                        variant={
                            payout.status === 'paid'
                                ? 'success'
                                : payout.status === 'cancelled'
                                  ? 'destructive'
                                  : 'secondary'
                        }
                        className="rounded-full"
                    >
                        {payout.status === 'paid'
                            ? 'Payé'
                            : payout.status === 'cancelled'
                              ? 'Annulé'
                              : 'En attente'}
                    </Badge>
                </div>

                <p className="text-lg font-semibold">
                    {formatCurrency(payout.amount)}
                </p>

                {payout.draw_entry && (
                    <p className="text-xs text-muted-foreground">
                        Position {payout.draw_entry.position}
                        {' • '}
                        Part {payout.draw_entry.entry_number}
                    </p>
                )}
            </div>

            {payout.status === 'pending' && (
                <div className="flex flex-wrap gap-2">
                    {can('payouts.cancel') && (
                        <Button variant="outline" asChild>
                            <Link
                                href={payouts.cancel({
                                    tontine: tontine.slug!,

                                    session: session.slug,

                                    meeting: meeting.slug,

                                    payout: payout.id,
                                })}
                                method="patch"
                                as="button"
                                onBefore={() =>
                                    confirm(
                                        'Voulez-vous annuler ce versement ?',
                                    )
                                }
                                onError={handleError}
                            >
                                <XIcon className="size-4" />
                                Annuler
                            </Link>
                        </Button>
                    )}

                    {can('payouts.pay') && (
                        <Button asChild>
                            <Link
                                href={payouts.pay({
                                    tontine: tontine.slug!,

                                    session: session.slug,

                                    meeting: meeting.slug,

                                    payout: payout.id,
                                })}
                                method="patch"
                                as="button"
                                onBefore={() =>
                                    confirm(
                                        `Confirmer le versement de ${formatCurrency(
                                            payout.amount,
                                        )} à ${beneficiary} ?`,
                                    )
                                }
                                onError={handleError}
                            >
                                <CheckIcon className="size-4" />
                                Marquer comme payé
                            </Link>
                        </Button>
                    )}
                </div>
            )}
        </div>
    );
}
