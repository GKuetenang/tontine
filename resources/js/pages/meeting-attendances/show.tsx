import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useAuthorization } from '@/hooks/use-authorization';
import { useTranslation } from '@/hooks/use-translation';
import type { Meeting, Session, Group } from '@/types';

import { AttendanceRow } from './attendace-row';
import { AttendancePlaceholder } from './placeholder';

type Props = {
    group: Group;
    session: Session;
    meeting: Meeting;
};

export function MeetingAttendances({ group, session, meeting }: Props) {
    const { t } = useTranslation();
    const { can } = useAuthorization();

    const attendances = meeting.attendances ?? [];

    if (meeting.status === 'scheduled') {
        return <AttendancePlaceholder />;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Présences')}</CardTitle>
            </CardHeader>

            <CardContent className="px-0">
                <Table>
                    <TableHeader>
                        <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                            <TableHead>{t('Membre')}</TableHead>

                            <TableHead>{t('N° membre')}</TableHead>

                            <TableHead>{t('Statut')}</TableHead>

                            <TableHead>{t('Arrivée')}</TableHead>

                            <TableHead>{t('Note')}</TableHead>

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
