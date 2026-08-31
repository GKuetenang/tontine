import { HandCoinsIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { useAuthorization } from '@/hooks/use-authorization';

import type { Meeting, MeetingPayoutContext, Session, Tontine } from '@/types';

import { CreatePayoutForm } from './form';

import { PayoutItem } from './payout-item';
import { MeetingPayoutsPlaceholder } from './placeholder';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    context: MeetingPayoutContext;
};

export function MeetingPayouts({ tontine, session, meeting, context }: Props) {
    const { can } = useAuthorization();

    const payouts = meeting.payouts ?? [];

    const canCreate =
        meeting.status === 'in_progress' &&
        context.available.length > 0 &&
        can('payouts.create');

    if (meeting.status === 'scheduled') {
        return (
            <MeetingPayoutsPlaceholder message="Les versements pourront être préparés lorsque la réunion sera en cours." />
        );
    }

    return (
        <Card>
            <CardHeader className="flex flex-row items-start justify-between gap-4">
                <div className="space-y-1">
                    <CardTitle>Versements</CardTitle>

                    <p className="text-sm text-muted-foreground">
                        Gérez les versements effectués aux bénéficiaires pendant
                        cette réunion.
                    </p>
                </div>

                {canCreate && (
                    <CreatePayoutForm
                        tontine={tontine}
                        session={session}
                        meeting={meeting}
                        context={context}
                        defaultCandidate={context.expected[0]}
                        trigger={
                            <Button type="button" size="sm">
                                <HandCoinsIcon className="size-4" />
                                Nouveau versement
                            </Button>
                        }
                    />
                )}
            </CardHeader>

            <CardContent className="space-y-6">
                {context.expected.length > 0 && (
                    <div className="space-y-3">
                        <div>
                            <p className="font-medium">
                                Bénéficiaire
                                {context.expected.length > 1 ? 's' : ''} prévu
                                {context.expected.length > 1 ? 's' : ''}
                            </p>

                            <p className="text-sm text-muted-foreground">
                                Selon le tirage confirmé.
                            </p>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {context.expected.map((candidate) => (
                                <div
                                    key={candidate.draw_entry_id}
                                    className="rounded-lg border bg-muted/20 p-3"
                                >
                                    <p className="font-medium">
                                        {candidate.member_name}
                                    </p>

                                    <p className="text-sm text-muted-foreground">
                                        Position {candidate.position}
                                        {candidate.entry_number > 1
                                            ? ` • Part ${candidate.entry_number}`
                                            : ''}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="space-y-3">
                    <div>
                        <p className="font-medium">
                            Versements de cette réunion
                        </p>

                        <p className="text-sm text-muted-foreground">
                            {payouts.length} versement
                            {payouts.length > 1 ? 's' : ''}
                        </p>
                    </div>

                    {payouts.length === 0 ? (
                        <div className="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Aucun versement enregistré pour cette réunion.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {payouts.map((payout) => (
                                <PayoutItem
                                    key={payout.id}
                                    tontine={tontine}
                                    session={session}
                                    meeting={meeting}
                                    payout={payout}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
