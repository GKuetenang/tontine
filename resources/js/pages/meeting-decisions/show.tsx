import {
    Button,
} from '@/components/ui/button';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import {
    useAuthorization,
} from '@/hooks/use-authorization';

import type {
    Meeting,
    Session,
    Tontine,
} from '@/types';

import {
    CheckCircle2Icon,
    PlusIcon,
} from 'lucide-react';

import {
    MeetingDecisionItem,
} from './decision-item';

import {
    EditMeetingDecisionForm,
} from './form';

import {
    MeetingDecisionsPlaceholder,
} from './placeholder';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function MeetingDecisions({
    tontine,
    session,
    meeting,
}: Props) {
    const { can } =
        useAuthorization();

    if (
        meeting.status ===
        'scheduled'
    ) {
        return (
            <MeetingDecisionsPlaceholder />
        );
    }

    const decisions =
        meeting.decisions ?? [];

    const canCreate =
        meeting.status ===
        'in_progress'
        && can(
            'meeting-decisions.create',
        );

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <div>
                    <CardTitle>
                        Décisions
                    </CardTitle>

                    <p className="mt-1 text-sm text-muted-foreground">
                        {decisions.length}{' '}
                        décision
                        {decisions.length > 1
                            ? 's'
                            : ''}
                    </p>
                </div>

                {canCreate && (
                    <EditMeetingDecisionForm
                        tontine={tontine}
                        session={session}
                        meeting={meeting}
                        trigger={
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                            >
                                <PlusIcon className="size-4" />

                                Ajouter une décision
                            </Button>
                        }
                    />
                )}
            </CardHeader>

            <CardContent>
                {decisions.length === 0 ? (
                    <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                        <div className="rounded-full bg-muted p-3">
                            <CheckCircle2Icon className="size-6 text-muted-foreground" />
                        </div>

                        <div className="space-y-1">
                            <p className="font-medium">
                                Aucune décision
                            </p>

                            <p className="text-sm text-muted-foreground">
                                Les décisions prises
                                pendant la réunion
                                apparaîtront ici.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {decisions.map(
                            (decision) => (
                                <MeetingDecisionItem
                                    key={
                                        decision.id
                                    }
                                    tontine={
                                        tontine
                                    }
                                    session={
                                        session
                                    }
                                    meeting={
                                        meeting
                                    }
                                    decision={
                                        decision
                                    }
                                />
                            ),
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}