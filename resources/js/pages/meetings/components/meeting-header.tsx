import { Link } from '@inertiajs/react';
import { FileTextIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAuthorization } from '@/hooks/use-authorization';
import { formatDate } from '@/lib';
import meetings from '@/routes/tontines/sessions/meetings';
import type { Meeting, Session, Tontine } from '@/types';
import { MeetingStatusBadge } from '../../../components/meeting-status-badge';
import { Actions } from '../actions';

export function MeetingHeader({
    meeting,
    session,
    tontine,
}: {
    meeting: Meeting;
    session: Session;
    tontine: Tontine;
}) {
    const { can } = useAuthorization();

    return (
        <div className="flex flex-wrap justify-between gap-4">
            <div className="flex flex-col gap-2">
                <div className="flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl font-semibold">
                        Réunion #{meeting.number} — {meeting.title}
                    </h1>

                    <MeetingStatusBadge meeting={meeting} />
                </div>

                <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <span>{formatDate(meeting.scheduled_at)}</span>

                    {meeting.location && (
                        <>
                            <span>•</span>
                            <span>{meeting.location}</span>
                        </>
                    )}
                </div>
            </div>
            <div className="flex items-center gap-2">
                {can('reports.view') && (
                    <Button variant="outline" asChild>
                        <Link
                            href={meetings.report.show({
                                tontine: tontine.slug!,
                                session: session.slug,
                                meeting: meeting.slug,
                            })}
                        >
                            <FileTextIcon className="size-4" />
                            Rapport
                        </Link>
                    </Button>
                )}

                <Actions
                    tontine={tontine}
                    session={session}
                    meeting={meeting}
                />
            </div>
        </div>
    );
}
