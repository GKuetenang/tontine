import { FormField } from '@/components/form-field';
import {
    SelectWithItems,
} from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';

import attendances from '@/routes/tontines/sessions/meetings/attendances';

import type {
    Meeting,
    MeetingAttendance,
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
    attendance: MeetingAttendance;
};

const attendanceStatuses = [
    {
        value: 'pending',
        label: 'En attente',
    },
    {
        value: 'present',
        label: 'Présent',
    },
    {
        value: 'late',
        label: 'En retard',
    },
    {
        value: 'absent',
        label: 'Absent',
    },
    {
        value: 'excused',
        label: 'Absent justifié',
    },
];

export function EditAttendanceForm({
    trigger,
    tontine,
    session,
    meeting,
    attendance,
}: Props) {
    const [open, setOpen] =
        useState(false);

    const action =
        attendances.update.form({
            tontine: tontine.slug!,
            session: session.slug,
            meeting: meeting.slug,
            attendance:
                attendance.id,
        });

    return (
        <Dialog
            open={open}
            onOpenChange={setOpen}
        >
            <DialogTrigger asChild>
                {trigger}
            </DialogTrigger>

            <DialogContent className="sm:max-w-md">
                <Form
                    {...action}
                    onSuccess={() =>
                        setOpen(false)
                    }
                >
                    {({
                        errors,
                        processing,
                    }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    Modifier la présence
                                </DialogTitle>
                            </DialogHeader>

                            <FormField
                                label="Statut"
                                htmlFor="status"
                                error={
                                    errors.status
                                }
                                required
                            >
                                <SelectWithItems
                                    id="status"
                                    name="status"
                                    items={
                                        attendanceStatuses
                                    }
                                    defaultValue={
                                        attendance.status
                                    }
                                />
                            </FormField>

                            <FormField
                                label="Note"
                                htmlFor="note"
                                error={
                                    errors.note
                                }
                                optional
                            >
                                <textarea
                                    id="note"
                                    name="note"
                                    defaultValue={
                                        attendance.note ??
                                        ''
                                    }
                                    className="border-input min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
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