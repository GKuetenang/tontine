import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import { RichTextEditor } from '@/components/rich-text-editor';
import { SelectWithItems } from '@/components/select-with-items';

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

import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

import decisions from '@/routes/groups/sessions/meetings/decisions';

import type { Meeting, MeetingDecision, Session, Group } from '@/types';

type Props = {
    trigger: ReactElement;

    group: Group;
    session: Session;
    meeting: Meeting;

    decision?: MeetingDecision;
};

export function EditMeetingDecisionForm({
    trigger,
    group,
    session,
    meeting,
    decision,
}: Props) {
    const [open, setOpen] = useState(false);

    const [description, setDescription] = useState(decision?.description ?? '');

    const isEditing = Boolean(decision?.id);

    const agendaItems =
        meeting.agenda_items?.map((item) => ({
            value: String(item.id),

            label: `${item.position}. ${item.title}`,
        })) ?? [];

    const action = decision?.id
        ? decisions.update.form({
              group: group.slug!,

              session: session.slug,

              meeting: meeting.slug,

              decision: decision.id,
          })
        : decisions.store.form({
              group: group.slug!,

              session: session.slug,

              meeting: meeting.slug,
          });

    const handleOpenChange = (value: boolean) => {
        if (value) {
            setDescription(decision?.description ?? '');
        }

        setOpen(value);
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent className="flex max-h-[90vh] flex-col overflow-hidden sm:max-w-5xl">
                <DialogHeader className="shrink-0">
                    <DialogTitle>
                        {isEditing
                            ? 'Modifier la décision'
                            : 'Ajouter une décision'}
                    </DialogTitle>

                    <DialogDescription>
                        Enregistrer une décision prise pendant l’assise.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...action}
                    resetOnSuccess
                    options={{
                        preserveScroll: true,
                    }}
                    className="flex min-h-0 flex-1 flex-col"
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="min-h-0 flex-1 space-y-4 overflow-y-auto pr-2">
                                <FormField
                                    label="Point de l’ordre du jour"
                                    htmlFor="meeting_agenda_item_id"
                                    error={errors['meeting_agenda_item_id']}
                                    optional
                                >
                                    <SelectWithItems
                                        id="meeting_agenda_item_id"
                                        name="meeting_agenda_item_id"
                                        items={[
                                            {
                                                value: '',

                                                label: 'Décision générale',
                                            },

                                            ...agendaItems,
                                        ]}
                                        defaultValue={
                                            decision?.agenda_item?.id
                                                ? String(
                                                      decision.agenda_item.id,
                                                  )
                                                : ''
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Titre"
                                    htmlFor="title"
                                    error={errors.title}
                                    required
                                >
                                    <Input
                                        id="title"
                                        name="title"
                                        defaultValue={decision?.title ?? ''}
                                        placeholder="Ex. Nouvelle cotisation mensuelle"
                                    />
                                </FormField>

                                <FormField
                                    label="Description"
                                    htmlFor="description"
                                    error={errors.description}
                                    optional
                                >
                                    <input
                                        type="hidden"
                                        name="description"
                                        value={description}
                                    />

                                    <RichTextEditor
                                        value={description}
                                        onChange={setDescription}
                                        placeholder="Décrire la décision..."
                                    />
                                </FormField>
                            </div>

                            <DialogFooter className="shrink-0 border-t pt-4">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Annuler
                                    </Button>
                                </DialogClose>

                                <Button type="submit" disabled={processing}>
                                    {processing ? <Spinner /> : <SaveIcon />}
                                    Enregistrer
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
