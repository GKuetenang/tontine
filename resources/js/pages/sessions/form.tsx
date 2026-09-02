import { Form } from '@inertiajs/react';
import { format } from 'date-fns';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import type { SelectOption } from '@/components/select-with-items';
import { SelectWithItems } from '@/components/select-with-items';
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
import { Textarea } from '@/components/ui/textarea';
import { parseDate } from '@/lib';
import sessions from '@/routes/groups/sessions';
import type { Session } from '@/types';
import type { ResultGroup } from '.';

type Props = {
    trigger: ReactElement;
    group: ResultGroup;
    session: Session;
    draw_allocation_modes: SelectOption[];
};

export function EditSessionForm({
    trigger,
    group,
    session,
    draw_allocation_modes,
}: Props) {
    const [open, setOpen] = useState(false);
    const configurationLocked = session.status === 'active';

    const [startDate, setStartDate] = useState<Date | undefined>(() =>
        parseDate(session?.start_at),
    );

    const [endDate, setEndDate] = useState<Date | undefined>(() =>
        parseDate(session?.end_at),
    );

    const handleOpenChange = (value: boolean) => {
        if (value) {
            setStartDate(parseDate(session?.start_at));

            setEndDate(parseDate(session?.end_at));
        }

        setOpen(value);
    };

    const action = session.id
        ? sessions.update.form({ group: group.slug, session: session.slug })
        : sessions.store.form({ group: group.slug });

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(e) => e.preventDefault()}
                onEscapeKeyDown={(e) => e.preventDefault()}
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
                                    {session.id
                                        ? 'Modifier la session'
                                        : 'Ajouter une session'}
                                </DialogTitle>
                                <DialogDescription>
                                    {session.id
                                        ? 'Mettre à jour les informations de la session.'
                                        : 'Ajouter une session à la réunion.'}
                                </DialogDescription>
                            </DialogHeader>

                            {errors.session && (
                                <div
                                    role="alert"
                                    className="rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                                >
                                    {errors.session}
                                </div>
                            )}

                            <FormField
                                error={errors['name']}
                                label="Nom"
                                htmlFor="name"
                                required
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={session?.name}
                                    aria-invalid={!!errors['name']}
                                />
                            </FormField>

                            <FormField
                                error={errors.description}
                                label="Description"
                                htmlFor="description"
                                optional
                            >
                                <Textarea
                                    id="description"
                                    name="description"
                                    defaultValue={session.description ?? ''}
                                />
                            </FormField>

                            <FormField
                                error={errors['default_contribution_amount']}
                                label="Montant par defaut"
                                htmlFor="default_contribution_amount"
                                optional
                            >
                                <Input
                                    id="default_contribution_amount"
                                    name="default_contribution_amount"
                                    defaultValue={
                                        session.default_contribution_amount ??
                                        undefined
                                    }
                                    aria-invalid={
                                        !!errors['default_contribution_amount']
                                    }
                                    readOnly={configurationLocked}
                                />
                            </FormField>
                            <FormField
                                error={errors['beneficiaries_per_meeting']}
                                label="Bénéficiaires par assise"
                                htmlFor="beneficiaries_per_meeting"
                                optional
                            >
                                <Input
                                    id="beneficiaries_per_meeting"
                                    name="beneficiaries_per_meeting"
                                    defaultValue={
                                        session.beneficiaries_per_meeting ?? 1
                                    }
                                    aria-invalid={
                                        !!errors['beneficiaries_per_meeting']
                                    }
                                    readOnly={configurationLocked}
                                />
                            </FormField>

                            <FormField
                                error={errors['draw_allocation_mode']}
                                label="Mode"
                                htmlFor="draw_allocation_mode"
                            >
                                <SelectWithItems
                                    items={draw_allocation_modes}
                                    id="draw_allocation_mode"
                                    name="draw_allocation_mode"
                                    defaultValue={
                                        session.draw_allocation_mode ??
                                        'one_per_member'
                                    }
                                    aria-invalid={
                                        !!errors['draw_allocation_mode']
                                    }
                                    disabled={configurationLocked}
                                />
                                {configurationLocked && (
                                    <input
                                        type="hidden"
                                        name="draw_allocation_mode"
                                        value={session.draw_allocation_mode}
                                    />
                                )}
                            </FormField>

                            <FormField
                                error={errors['base_contribution_amount']}
                                label="Montant de base pour l’attribution des tours"
                                htmlFor="base_contribution_amount"
                                optional
                            >
                                <Input
                                    id="base_contribution_amount"
                                    name="base_contribution_amount"
                                    inputMode="decimal"
                                    defaultValue={
                                        session.base_contribution_amount ??
                                        undefined
                                    }
                                    aria-invalid={
                                        !!errors['base_contribution_amount']
                                    }
                                    readOnly={configurationLocked}
                                />
                            </FormField>

                            <FormField
                                error={errors['start_at']}
                                label="Date de début"
                                htmlFor="start_at"
                            >
                                <input
                                    type="hidden"
                                    name="start_at"
                                    value={
                                        startDate
                                            ? format(
                                                  startDate,
                                                  'yyyy-MM-dd HH:mm:ss',
                                              )
                                            : ''
                                    }
                                />
                                <DateTimePicker
                                    granularity="minute"
                                    className="text-foreground"
                                    placeholder="Choisir une date"
                                    value={startDate}
                                    onChange={setStartDate}
                                />
                            </FormField>

                            <FormField
                                error={errors['end_at']}
                                label="Date de fin"
                                htmlFor="end_at"
                            >
                                <DateTimePicker
                                    granularity="minute"
                                    className="text-foreground"
                                    placeholder="Choisir une date"
                                    value={endDate}
                                    onChange={setEndDate}
                                />
                                <input
                                    type="hidden"
                                    name="end_at"
                                    value={
                                        endDate
                                            ? format(
                                                  endDate,
                                                  'yyyy-MM-dd HH:mm:ss',
                                              )
                                            : ''
                                    }
                                />
                            </FormField>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Annuler
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="save-session-button"
                                >
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
