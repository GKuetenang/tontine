import { FormField } from '@/components/form-field';
import { RichTextEditor } from '@/components/rich-text-editor';
import {
    SelectWithItems,
} from '@/components/select-with-items';
import { Button } from '@/components/ui/button';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

import { Spinner } from '@/components/ui/spinner';

import notes from '@/routes/tontines/sessions/meetings/notes';

import type {
    Meeting,
    MeetingNote,
    Session,
    Tontine,
} from '@/types';

import { Form } from '@inertiajs/react';

import { SaveIcon } from 'lucide-react';

import type { ReactElement } from 'react';
import { useState } from 'react';

type Props = {
    trigger: ReactElement;
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    note?: MeetingNote;
};

export function EditMeetingNoteForm({
    trigger,
    tontine,
    session,
    meeting,
    note,
}: Props) {
    const [open, setOpen] =
        useState(false);

    const [content, setContent] =
        useState(
            note?.content ?? '',
        );

    const isEditing =
        Boolean(note?.id);

    const agendaItems =
        meeting.agenda_items?.map(
            (item) => ({
                value: String(item.id),
                label: `${item.position}. ${item.title}`,
            }),
        ) ?? [];

    const handleOpenChange = (
        value: boolean,
    ) => {
        if (value) {
            setContent(
                note?.content ?? '',
            );
        }

        setOpen(value);
    };

    const action = note?.id
        ? notes.update.form({
            tontine: tontine.slug!,
            session: session.slug,
            meeting: meeting.slug,
            note: note.id,
        })
        : notes.store.form({
            tontine: tontine.slug!,
            session: session.slug,
            meeting: meeting.slug,
        });

    return (
        <Dialog
            open={open}
            onOpenChange={handleOpenChange}
        >
            <DialogTrigger asChild>
                {trigger}
            </DialogTrigger>

            <DialogContent
                className="sm:max-w-4xl"
                onInteractOutside={(event) =>
                    event.preventDefault()
                }
            >
                <Form
                    {...action}
                    resetOnSuccess
                    options={{
                        preserveScroll: true,
                    }}
                    onSuccess={() =>
                        setOpen(false)
                    }
                >
                    {({
                        errors,
                        processing,
                    }) => (
                        <div className="min-h-0 flex-1 space-y-4 overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Modifier la note'
                                        : 'Ajouter une note'}
                                </DialogTitle>

                                <DialogDescription>
                                    Consigner une information
                                    importante de la réunion.
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                label="Point de l’ordre du jour"
                                htmlFor="meeting_agenda_item_id"
                                error={
                                    errors[
                                    'meeting_agenda_item_id'
                                    ]
                                }
                                optional
                            >
                                <SelectWithItems
                                    id="meeting_agenda_item_id"
                                    name="meeting_agenda_item_id"
                                    items={[
                                        {
                                            value: '',
                                            label: 'Note générale',
                                        },
                                        ...agendaItems,
                                    ]}
                                    defaultValue={
                                        note?.agenda_item
                                            ?.id
                                            ? String(
                                                note
                                                    .agenda_item
                                                    .id,
                                            )
                                            : ''
                                    }
                                />
                            </FormField>

                            <FormField
                                label="Note"
                                htmlFor="content"
                                error={errors.content}
                                required
                            >
                                <input
                                    type="hidden"
                                    name="content"
                                    value={content}
                                />

                                <RichTextEditor
                                    value={content}
                                    onChange={setContent}
                                />
                            </FormField>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button
                                        type="button"
                                        variant="outline"
                                    >
                                        Annuler
                                    </Button>
                                </DialogClose>

                                <Button
                                    type="submit"
                                    disabled={
                                        processing
                                    }
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <SaveIcon />
                                    )}

                                    Enregistrer
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}