import { formatDate } from "@/lib";
import { Meeting, Session, Tontine } from "@/types";
import { MeetingStatusBadge } from "../../../components/meeting-status-badge";
import { Actions } from "../actions";

export function MeetingHeader({
    meeting,
    session,
    tontine,
}: {
    meeting: Meeting;
    session: Session,
    tontine: Tontine
}) {
    return (
        <div className="flex justify-between">
            <div className="flex flex-col gap-2">
                <div className="flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl font-semibold">
                        Réunion #{meeting.number} —{' '}
                        {meeting.title}
                    </h1>

                    <MeetingStatusBadge
                        meeting={meeting}
                    />
                </div>

                <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <span>
                        {formatDate(
                            meeting.scheduled_at,
                        )}
                    </span>

                    {meeting.location && (
                        <>
                            <span>•</span>
                            <span>
                                {meeting.location}
                            </span>
                        </>
                    )}
                </div>
            </div>
            <Actions
                tontine={
                    tontine
                }
                session={
                    session
                }
                meeting={
                    meeting
                }
            />
        </div>
    );
}