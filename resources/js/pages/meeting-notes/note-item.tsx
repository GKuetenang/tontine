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
import notes from '@/routes/tontines/sessions/meetings/notes';

import type { Meeting, MeetingNote, Session, Tontine } from '@/types';

import { EditMeetingNoteForm } from './form';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    note: MeetingNote;
};

export function MeetingNoteItem({ tontine, session, meeting, note }: Props) {
    const { can, canAny } = useAuthorization();

    const canEdit = meeting.status === 'in_progress';

    const hasActions =
        canEdit && canAny('meeting-notes.update', 'meeting-notes.delete');

    return (
        <Card className="relative max-h-100 overflow-y-auto py-0">
            <CardHeader className="sticky top-0 bg-card py-4 backdrop-blur-md">
                {note.agenda_item ? (
                    <div>
                        <span className="inline-flex rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                            {note.agenda_item.position}.{' '}
                            {note.agenda_item.title}
                        </span>
                    </div>
                ) : (
                    <div>
                        <span className="inline-flex rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            Note générale
                        </span>
                    </div>
                )}

                {hasActions && (
                    <CardAction className="flex items-center gap-2">
                        {can('meeting-notes.update') && (
                            <EditMeetingNoteForm
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                                note={note}
                                trigger={
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        onSelect={(ev) => ev.preventDefault()}
                                    >
                                        <PencilIcon className="size-4" />
                                    </Button>
                                }
                            />
                        )}

                        {can('meeting-notes.delete') && (
                            <Button
                                asChild
                                size="icon"
                                variant="destructive-outline"
                            >
                                <Link
                                    href={notes.destroy({
                                        tontine: tontine.slug!,
                                        session: session.slug,
                                        meeting: meeting.slug,
                                        note: note.id,
                                    })}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment supprimer cette note ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(errors)[0];

                                        if (firstError) {
                                            toast.error(firstError);
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
            <CardContent className="">
                <RichTextContent content={note.content} />

                <div className="mt-3 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                    {note.creator && <span>{note.creator.name}</span>}

                    <span>•</span>

                    <span>
                        {formatDate(note.created_at)}
                        {/* {new Intl.DateTimeFormat(
                                'fr-CA',
                                {
                                    dateStyle:
                                        'medium',
                                    timeStyle:
                                        'short',
                                },
                            ).format(
                                new Date(
                                    note.created_at,
                                ),
                            )} */}
                    </span>
                </div>
            </CardContent>
        </Card>
    );
}
