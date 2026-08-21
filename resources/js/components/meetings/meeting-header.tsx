import { formatDate } from "@/lib";
import { Meeting } from "@/types";
import { MeetingStatusBadge } from "../meeting-status-badge";

export function MeetingHeader({
    meeting,
}: {
    meeting: Meeting;
}) {
    return (
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
    );
}