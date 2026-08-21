import { AttendanceStatusBadge } from "@/components/attendance-status-badge";
import { Button } from "@/components/ui/button";
import { TableCell, TableRow } from "@/components/ui/table";
import { formatDate } from "@/lib";
import { Meeting, MeetingAttendance, Session, Tontine } from "@/types";
import { EditAttendanceForm } from "./form";

export function AttendanceRow({
    tontine,
    session,
    meeting,
    attendance,
    canUpdate,
}: {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    attendance: MeetingAttendance;
    canUpdate: boolean;
}) {
    const participant =
        attendance.session_participant;

    const membership =
        participant?.membership;

    const user =
        membership?.user;

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
                <AttendanceStatusBadge
                    status={
                        attendance.status
                    }
                />
            </TableCell>

            <TableCell>
                {formatDate(
                    attendance.checked_in_at,
                )}
            </TableCell>

            <TableCell className="max-w-xs">
                <span className="line-clamp-2 text-sm text-muted-foreground">
                    {attendance.note ??
                        '—'}
                </span>
            </TableCell>

            <TableCell className="text-end">
                {canUpdate &&
                    meeting.status ===
                    'in_progress' && (
                        <EditAttendanceForm
                            tontine={
                                tontine
                            }
                            session={
                                session
                            }
                            meeting={
                                meeting
                            }
                            attendance={
                                attendance
                            }
                            trigger={
                                <Button
                                    variant="outline"
                                    size="sm"
                                >
                                    Modifier
                                </Button>
                            }
                        />
                    )}
            </TableCell>
        </TableRow>
    );
}