import { Form } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon, SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
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
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { UserCombobox } from '@/components/user-combobox';
import { cn } from '@/lib/utils';
import insurance from '@/routes/tontines/sessions/insurance';
import type { MemberUser, Session, Tontine } from '@/types';

type Props = { trigger: ReactElement; tontine: Tontine; session: Session };

export function CreateInsuranceContributionForm({
    trigger,
    tontine,
    session,
}: Props) {
    const [open, setOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState<MemberUser | null>(null);
    const [occurredAt, setOccurredAt] = useState<Date | undefined>(new Date());

    const reset = () => {
        setSelectedUser(null);
        setOccurredAt(new Date());
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(value) => {
                setOpen(value);

                if (value) {
                    reset();
                }
            }}
        >
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...insurance.store.form({
                        tontine: tontine.slug!,
                        session: session.slug,
                    })}
                    resetOnSuccess
                    onBefore={() =>
                        confirm(
                            'Voulez-vous vraiment enregistrer ce versement d’assurance ? Il créera immédiatement un crédit financier.',
                        )
                    }
                    onSuccess={() => {
                        setOpen(false);
                        reset();
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Nouveau versement</DialogTitle>
                                <DialogDescription>
                                    Enregistrez la contribution d’un membre au
                                    fonds d’assurance.
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                error={errors.membership_id}
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
                                label="Montant versé"
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
                                error={errors.occurred_at}
                                label="Date du versement"
                                htmlFor="occurred_at"
                            >
                                <input
                                    type="hidden"
                                    name="occurred_at"
                                    value={
                                        occurredAt
                                            ? format(occurredAt, 'yyyy-MM-dd')
                                            : ''
                                    }
                                />
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            id="occurred_at"
                                            type="button"
                                            variant="outline"
                                            className="w-full justify-start font-normal"
                                        >
                                            <CalendarIcon />
                                            {occurredAt
                                                ? format(
                                                      occurredAt,
                                                      'dd/MM/yyyy',
                                                  )
                                                : 'Sélectionner une date'}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0">
                                        <Calendar
                                            mode="single"
                                            selected={occurredAt}
                                            onSelect={setOccurredAt}
                                        />
                                    </PopoverContent>
                                </Popover>
                            </FormField>
                            <FormField
                                error={errors.description}
                                label="Motif"
                                htmlFor="description"
                            >
                                <Textarea
                                    id="description"
                                    name="description"
                                    placeholder="Facultatif"
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
                                    disabled={processing || !selectedUser}
                                >
                                    {processing ? <Spinner /> : <SaveIcon />}{' '}
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
