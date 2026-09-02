import { Link } from '@inertiajs/react';
import { PencilIcon, TrashIcon } from 'lucide-react';
import { toast } from 'sonner';
import { RichTextContent } from '@/components/rich-text-content';

import { Button } from '@/components/ui/button';

import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
} from '@/components/ui/card';

import { useAuthorization } from '@/hooks/use-authorization';

import { formatDate } from '@/lib';

import decisions from '@/routes/groups/sessions/meetings/decisions';

import type { Meeting, MeetingDecision, Session, Group } from '@/types';

import { EditMeetingDecisionForm } from './form';

type Props = {
    group: Group;
    session: Session;
    meeting: Meeting;
    decision: MeetingDecision;
};

export function MeetingDecisionItem({
    group,
    session,
    meeting,
    decision,
}: Props) {
    const { can, canAny } = useAuthorization();

    const canEdit = meeting.status === 'in_progress';

    const hasActions =
        canEdit &&
        canAny('meeting-decisions.update', 'meeting-decisions.delete');

    return (
        <Card className="relative max-h-100 overflow-y-auto py-0">
            <CardHeader className="sticky top-0 bg-card py-4 backdrop-blur-md">
                {decision.agenda_item ? (
                    <div>
                        <span className="inline-flex rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                            {decision.agenda_item.position}.{' '}
                            {decision.agenda_item.title}
                        </span>
                    </div>
                ) : (
                    <div>
                        <span className="inline-flex rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            Décision générale
                        </span>
                    </div>
                )}

                {hasActions && (
                    <CardAction className="flex items-center gap-2">
                        {can('meeting-decisions.update') && (
                            <EditMeetingDecisionForm
                                group={group}
                                session={session}
                                meeting={meeting}
                                decision={decision}
                                trigger={
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        title="Modifier"
                                    >
                                        <PencilIcon className="size-4" />
                                    </Button>
                                }
                            />
                        )}

                        {can('meeting-decisions.delete') && (
                            <Button
                                asChild
                                size="icon"
                                variant="destructive-outline"
                                title="Supprimer"
                            >
                                <Link
                                    href={decisions.destroy({
                                        group: group.slug!,

                                        session: session.slug,

                                        meeting: meeting.slug,

                                        decision: decision.id,
                                    })}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment supprimer cette décision ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const error = Object.values(errors)[0];

                                        if (error) {
                                            toast.error(error);
                                        }
                                    }}
                                >
                                    <TrashIcon className="size-4" />
                                </Link>
                            </Button>
                        )}
                    </CardAction>
                )}
            </CardHeader>

            <CardContent>
                <h3 className="font-semibold">{decision.title}</h3>

                {decision.description && (
                    <div className="mt-2">
                        <RichTextContent content={decision.description} />
                    </div>
                )}

                <div className="mt-3 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                    {decision.creator && (
                        <>
                            <span>{decision.creator.name}</span>

                            <span>•</span>
                        </>
                    )}

                    <span>{formatDate(decision.created_at)}</span>
                </div>
            </CardContent>
        </Card>
    );
}
