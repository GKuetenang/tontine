import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import { useState } from 'react';

import { FormField } from '@/components/form-field';
import { SelectWithItems } from '@/components/select-with-items';
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
import { useTranslation } from '@/hooks/use-translation';
import attendances from '@/routes/groups/sessions/meetings/attendances';
import type { Meeting, MeetingAttendance, Session, Group } from '@/types';

import type { ReactElement } from 'react';

type Props = {
    trigger: ReactElement;
    group: Group;
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
    group,
    session,
    meeting,
    attendance,
}: Props) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);

    const action = attendances.update.form({
        group: group.slug!,
        session: session.slug,
        meeting: meeting.slug,
        attendance: attendance.id,
    });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent className="sm:max-w-md">
                <Form {...action} onSuccess={() => setOpen(false)}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {t('Modifier la présence')}
                                </DialogTitle>
                            </DialogHeader>

                            <FormField
                                label={t('Statut')}
                                htmlFor="status"
                                error={errors.status}
                                required
                            >
                                <SelectWithItems
                                    id="status"
                                    name="status"
                                    items={attendanceStatuses}
                                    defaultValue={attendance.status}
                                />
                            </FormField>

                            <FormField
                                label={t('Note')}
                                htmlFor="note"
                                error={errors.note}
                                optional
                            >
                                <textarea
                                    id="note"
                                    name="note"
                                    defaultValue={attendance.note ?? ''}
                                    className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                />
                            </FormField>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        {t('Annuler')}
                                    </Button>
                                </DialogClose>

                                <Button type="submit" disabled={processing}>
                                    {processing ? <Spinner /> : <SaveIcon />}
                                    {t('Enregistrer')}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
