import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { useAuthorization } from '@/hooks/use-authorization';

import type { Meeting, Session, Group } from '@/types';

import { AttendanceRow } from './attendace-row';
import { AttendancePlaceholder } from './placeholder';

type Props = {
    group: Group;
    session: Session;
    meeting: Meeting;
};

export function MeetingAttendances({ group, session, meeting }: Props) {
    const { can } = useAuthorization();

    const attendances = meeting.attendances ?? [];

    if (meeting.status === 'scheduled') {
        return <AttendancePlaceholder />;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Présences</CardTitle>
            </CardHeader>

            <CardContent className="px-0">
                <Table>
                    <TableHeader>
                        <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                            <TableHead>Membre</TableHead>

                            <TableHead>N° membre</TableHead>

                            <TableHead>Statut</TableHead>

                            <TableHead>Arrivée</TableHead>

                            <TableHead>Note</TableHead>

                            <TableHead className="text-end" />
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {attendances.map((attendance) => (
                            <AttendanceRow
                                key={attendance.id}
                                group={group}
                                session={session}
                                meeting={meeting}
                                attendance={attendance}
                                canUpdate={can('meeting-attendances.update')}
                            />
                        ))}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}
