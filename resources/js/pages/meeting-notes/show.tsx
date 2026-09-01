import { Button } from '@/components/ui/button';
import { NotebookPenIcon, PlusIcon } from 'lucide-react';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { useAuthorization } from '@/hooks/use-authorization';

import type { Meeting, Session, Tontine } from '@/types';

import { EditMeetingNoteForm } from './form';
import { MeetingNoteItem } from './note-item';
import { MeetingNotesPlaceholder } from './placeholder';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function MeetingNotes({ tontine, session, meeting }: Props) {
    const { can } = useAuthorization();

    if (meeting.status === 'scheduled') {
        return <MeetingNotesPlaceholder />;
    }

    const notes = meeting.notes ?? [];

    const canCreate =
        meeting.status === 'in_progress' && can('meeting-notes.create');

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <div>
                    <CardTitle>Notes</CardTitle>

                    <p className="mt-1 text-sm text-muted-foreground">
                        {notes.length} note
                        {notes.length > 1 ? 's' : ''}
                    </p>
                </div>

                {canCreate && (
                    <EditMeetingNoteForm
                        tontine={tontine}
                        session={session}
                        meeting={meeting}
                        trigger={
                            <Button size="sm">
                                <PlusIcon className="size-4" />
                                Ajouter une note
                            </Button>
                        }
                    />
                )}
            </CardHeader>

            <CardContent>
                {notes.length === 0 ? (
                    <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                        <div className="rounded-full bg-muted p-3">
                            <NotebookPenIcon className="size-6 text-muted-foreground" />
                        </div>

                        <div className="space-y-1">
                            <p className="font-medium">Aucune note</p>

                            <p className="text-sm text-muted-foreground">
                                Les notes prises pendant la réunion apparaîtront
                                ici.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {notes.map((note) => (
                            <MeetingNoteItem
                                key={note.id}
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                                note={note}
                            />
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
