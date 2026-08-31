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
import { Textarea } from '@/components/ui/textarea';
import { UserCombobox } from '@/components/user-combobox';
import { cn } from '@/lib/utils';
import donations from '@/routes/tontines/sessions/donations';
import type { MemberUser, Session, Tontine } from '@/types';

type Props = { trigger: ReactElement; tontine: Tontine; session: Session };

export function CreateDonationForm({ trigger, tontine, session }: Props) {
    const [open, setOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState<MemberUser | null>(null);

    return (
        <Dialog
            open={open}
            onOpenChange={(value) => {
                setOpen(value);

                if (value) {
                    setSelectedUser(null);
                }
            }}
        >
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...donations.store.form({
                        tontine: tontine.slug!,
                        session: session.slug,
                    })}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false);
                        setSelectedUser(null);
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Ajouter un don</DialogTitle>
                                <DialogDescription>
                                    Sélectionnez un participant actif de la
                                    session.
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                error={errors.membership_id}
                                label="Bénéficiaire"
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
                                            'rounded-sm border bg-accent px-2 py-1.5',
                                            errors.membership_id &&
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
                            <FormField
                                error={errors.amount}
                                label="Montant"
                                htmlFor="amount"
                                required
                            >
                                <Input
                                    id="amount"
                                    name="amount"
                                    inputMode="decimal"
                                    required
                                />
                            </FormField>
                            <FormField
                                error={errors.reason}
                                label="Motif"
                                htmlFor="reason"
                                required
                            >
                                <Textarea id="reason" name="reason" required />
                            </FormField>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">Annuler</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    disabled={processing || !selectedUser}
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
