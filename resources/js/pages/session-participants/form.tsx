import { Form } from '@inertiajs/react';
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
import { UserCombobox } from '@/components/user-combobox';
import { cn } from '@/lib/utils';
import sessionParticipants from '@/routes/tontines/sessions/participants';
import type {
    MemberUser,
    ResultTontine,
    Session,
    SessionParticipant,
} from '@/types';

type Props = {
    trigger: ReactElement;
    tontine: ResultTontine;
    session: Session;
    participant?: SessionParticipant;
};

export function EditSessionParticipantForm({
    trigger,
    tontine,
    session,
    participant,
}: Props) {
    const [open, setOpen] = useState(false);

    const [selectedUser, setSelectedUser] = useState<MemberUser | null>(
        participant?.membership?.user ?? null,
    );

    const isEditing = !!participant?.id;

    const handleOpenChange = (value: boolean) => {
        if (value) {
            setSelectedUser(participant?.membership?.user ?? null);
        }

        setOpen(value);
    };

    const action = isEditing
        ? sessionParticipants.update.form({
              tontine: tontine.slug,
              session: session.slug,
              participant: participant.id,
          })
        : sessionParticipants.store.form({
              tontine: tontine.slug,
              session: session.slug,
          });

    const defaultContributionAmount = isEditing
        ? participant.contribution_amount
        : (participant?.contribution_amount ??
          session.default_contribution_amount ??
          undefined);

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

                        if (!isEditing) {
                            setSelectedUser(null);
                        }
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Modifier le participant'
                                        : 'Ajouter un participant'}
                                </DialogTitle>

                                <DialogDescription>
                                    {isEditing
                                        ? 'Modifier les informations du participant.'
                                        : `Ajouter un participant à la session ${session.name}.`}
                                </DialogDescription>
                            </DialogHeader>

                            {!isEditing && (
                                <FormField
                                    error={errors['membership_id']}
                                    label="Membre"
                                    htmlFor="membership_id"
                                    required
                                >
                                    <input
                                        type="hidden"
                                        id="membership_id"
                                        name="membership_id"
                                        value={selectedUser?.id ?? ''}
                                    />

                                    <UserCombobox onSelect={setSelectedUser} />

                                    {selectedUser && (
                                        <div
                                            className={cn(
                                                'relative cursor-default flex-col items-center gap-2 rounded-sm border bg-accent px-2 py-1.5 text-sm text-shadow-accent-foreground',
                                                errors['user_id'] &&
                                                    'border-destructive bg-destructive/20',
                                            )}
                                        >
                                            <p className="text-sm">
                                                {selectedUser.name}
                                            </p>
                                            <p className="text-xs">
                                                {selectedUser.email}
                                            </p>
                                        </div>
                                    )}
                                </FormField>
                            )}

                            <FormField
                                error={errors['contribution_amount']}
                                label="Montant de cotisation"
                                htmlFor="contribution_amount"
                                required
                            >
                                <Input
                                    id="contribution_amount"
                                    name="contribution_amount"
                                    type="number"
                                    min={0}
                                    defaultValue={defaultContributionAmount}
                                    aria-invalid={
                                        !!errors['contribution_amount']
                                    }
                                />
                            </FormField>

                            {session.draw_allocation_mode === 'custom' && (
                                <FormField
                                    error={errors['draw_entries_count']}
                                    label="Nombre de tours"
                                    htmlFor="draw_entries_count"
                                    required
                                >
                                    <Input
                                        id="draw_entries_count"
                                        name="draw_entries_count"
                                        type="number"
                                        min={1}
                                        defaultValue={
                                            participant?.draw_entries_count ?? 1
                                        }
                                        aria-invalid={
                                            !!errors['draw_entries_count']
                                        }
                                    />
                                </FormField>
                            )}

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">Annuler</Button>
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
