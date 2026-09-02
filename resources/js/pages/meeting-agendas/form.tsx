import { Form, router } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
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

import agenda from '@/routes/groups/sessions/meetings/agenda';

import type { Meeting, MeetingAgendaItem, Session, Group } from '@/types';

type Props = {
    trigger: ReactElement;
    group: Group;
    session: Session;
    meeting: Meeting;
    agendaItem?: MeetingAgendaItem;
};

export function EditAgendaItemForm({
    trigger,
    group,
    session,
    meeting,
    agendaItem,
}: Props) {
    const [open, setOpen] = useState(false);

    const isEditing = Boolean(agendaItem?.id);

    const action = agendaItem?.id
        ? agenda.update.form({
              group: group.slug!,
              session: session.slug,
              meeting: meeting.slug,
              agendaItem: agendaItem.id,
          })
        : agenda.store.form({
              group: group.slug!,
              session: session.slug,
              meeting: meeting.slug,
          });

    const handleSuccess = () => {
        setOpen(false);

        router.reload({
            only: ['meeting'],
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...action}
                    resetOnSuccess
                    onSuccess={() => handleSuccess()}
                    options={{
                        preserveScroll: true,
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Modifier le point'
                                        : 'Ajouter un point'}
                                </DialogTitle>

                                <DialogDescription>
                                    Préparer l’ordre du jour de cette assise.
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                label="Titre"
                                htmlFor="title"
                                error={errors.title}
                                required
                            >
                                <Input
                                    id="title"
                                    name="title"
                                    defaultValue={agendaItem?.title ?? ''}
                                />
                            </FormField>

                            <FormField
                                label="Description"
                                htmlFor="description"
                                error={errors.description}
                                optional
                            >
                                <textarea
                                    id="description"
                                    name="description"
                                    defaultValue={agendaItem?.description ?? ''}
                                    className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                />
                            </FormField>

                            <DialogFooter>
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
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
