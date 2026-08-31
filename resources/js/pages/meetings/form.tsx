import { Form } from '@inertiajs/react';
import { format } from 'date-fns';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import { DateTimePicker } from '@/components/ui/datetime-picker';
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
import { parseDate } from '@/lib';

import meetings from '@/routes/tontines/sessions/meetings';

import type { Meeting, Session, Tontine } from '@/types';

type Props = {
    trigger: ReactElement;
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function EditMeetingForm({ trigger, tontine, session, meeting }: Props) {
    const [open, setOpen] = useState(false);

    const [scheduledAt, setScheduledAt] = useState<Date | undefined>(() =>
        parseDate(meeting?.scheduled_at),
    );

    const handleOpenChange = (value: boolean) => {
        if (value) {
            setScheduledAt(parseDate(meeting?.scheduled_at));
        }

        setOpen(value);
    };

    const action = meeting.id
        ? meetings.update.form({
              tontine: tontine.slug!,
              session: session.slug,
              meeting: meeting.slug,
          })
        : meetings.store.form({
              tontine: tontine.slug!,
              session: session.slug,
          });

    const isEditing = Boolean(meeting.id);

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(event) => event.preventDefault()}
                onEscapeKeyDown={(event) => event.preventDefault()}
            >
                <Form
                    {...action}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false);
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Modifier la réunion'
                                        : 'Ajouter une réunion'}
                                </DialogTitle>

                                <DialogDescription>
                                    {isEditing
                                        ? 'Modifier les informations de la réunion.'
                                        : 'Planifier une nouvelle réunion pour cette session.'}
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                error={errors['title']}
                                label="Titre"
                                htmlFor="title"
                                required
                            >
                                <Input
                                    id="title"
                                    name="title"
                                    defaultValue={meeting?.title}
                                    placeholder="Ex. Réunion mensuelle"
                                    aria-invalid={!!errors['title']}
                                />
                            </FormField>

                            <FormField
                                error={errors['scheduled_at']}
                                label="Date et heure"
                                htmlFor="scheduled_at"
                                required
                            >
                                <input
                                    type="hidden"
                                    name="scheduled_at"
                                    value={
                                        scheduledAt
                                            ? format(
                                                  scheduledAt,
                                                  'yyyy-MM-dd HH:mm:ss',
                                              )
                                            : ''
                                    }
                                />

                                <DateTimePicker
                                    granularity="minute"
                                    className="text-foreground"
                                    placeholder="Choisir une date et une heure"
                                    value={scheduledAt}
                                    onChange={setScheduledAt}
                                />
                            </FormField>

                            <FormField
                                error={errors['location']}
                                label="Lieu"
                                htmlFor="location"
                                optional
                            >
                                <Input
                                    id="location"
                                    name="location"
                                    defaultValue={meeting?.location ?? ''}
                                    placeholder="Ex. Domicile du président"
                                    aria-invalid={!!errors['location']}
                                />
                            </FormField>

                            <FormField
                                error={errors['description']}
                                label="Description"
                                htmlFor="description"
                                optional
                            >
                                <textarea
                                    id="description"
                                    name="description"
                                    defaultValue={meeting?.description ?? ''}
                                    placeholder="Ajouter une description..."
                                    aria-invalid={!!errors['description']}
                                    className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40"
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

                                    {isEditing ? 'Modifier' : 'Enregistrer'}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
