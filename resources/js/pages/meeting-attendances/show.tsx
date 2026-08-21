import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';

import { useAuthorization } from '@/hooks/use-authorization';

import type {
    Meeting,
    Session,
    Tontine
} from '@/types';

import { UsersIcon } from 'lucide-react';
import { AttendanceRow } from './attendace-row';


type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function MeetingAttendances({
    tontine,
    session,
    meeting,
}: Props) {
    const { can } = useAuthorization();

    const attendances =
        meeting.attendances ?? [];

    if (meeting.status === 'scheduled') {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>
                        Présences
                    </CardTitle>
                </CardHeader>

                <CardContent>
                    <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                        <UsersIcon className="size-8 text-muted-foreground" />

                        <div className="space-y-1">
                            <p className="font-medium">
                                La réunion n’est pas encore ouverte
                            </p>

                            <p className="max-w-md text-sm text-muted-foreground">
                                Les présences seront initialisées automatiquement lors de l’ouverture de la réunion.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    Présences
                </CardTitle>
            </CardHeader>

            <CardContent className="px-0">
                <Table>
                    <TableHeader>
                        <TableRow className="[&>th:first-child]:pl-6 [&>th:last-child]:pr-6">
                            <TableHead>
                                Membre
                            </TableHead>

                            <TableHead>
                                N° membre
                            </TableHead>

                            <TableHead>
                                Statut
                            </TableHead>

                            <TableHead>
                                Arrivée
                            </TableHead>

                            <TableHead>
                                Note
                            </TableHead>

                            <TableHead className="text-end" />
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {attendances.map(
                            (attendance) => (
                                <AttendanceRow
                                    key={
                                        attendance.id
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
                                    attendance={
                                        attendance
                                    }
                                    canUpdate={
                                        can(
                                            'meeting-attendances.update',
                                        )
                                    }
                                />
                            ),
                        )}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}