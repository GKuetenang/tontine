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
import loans from '@/routes/tontines/sessions/loans';
import type { MemberUser, Session, Tontine } from '@/types';

export function CreateLoanForm({
    trigger,
    tontine,
    session,
}: {
    trigger: ReactElement;
    tontine: Tontine;
    session: Session;
}) {
    const [open, setOpen] = useState(false);
    const [borrower, setBorrower] = useState<MemberUser | null>(null);

    return (
        <Dialog
            open={open}
            onOpenChange={(value) => {
                setOpen(value);

                if (value) {
                    setBorrower(null);
                }
            }}
        >
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...loans.store.form({
                        tontine: tontine.slug!,
                        session: session.slug,
                    })}
                    resetOnSuccess
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Créer un prêt</DialogTitle>
                                <DialogDescription>
                                    Taux de {tontine.default_loan_interest_rate}{' '}
                                    % sur le capital initial, échéance à{' '}
                                    {tontine.default_loan_term_months} mois.
                                </DialogDescription>
                            </DialogHeader>
                            {errors.loan && (
                                <div
                                    role="alert"
                                    className="rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                                >
                                    {errors.loan}
                                </div>
                            )}
                            <FormField
                                error={errors.membership_id}
                                label="Emprunteur"
                                htmlFor="membership_id"
                                required
                            >
                                <input
                                    type="hidden"
                                    id="membership_id"
                                    name="membership_id"
                                    value={borrower?.id ?? ''}
                                />
                                <UserCombobox onSelect={setBorrower} />
                                {borrower && (
                                    <div
                                        className={cn(
                                            'rounded-sm border bg-accent px-2 py-1.5',
                                            errors.membership_id &&
                                                'border-destructive',
                                        )}
                                    >
                                        <p className="text-sm">
                                            {borrower.name}
                                        </p>
                                        <p className="text-xs">
                                            {borrower.email}
                                        </p>
                                    </div>
                                )}
                            </FormField>
                            <FormField
                                error={errors.principal_amount}
                                label="Capital"
                                htmlFor="principal_amount"
                                required
                            >
                                <Input
                                    id="principal_amount"
                                    name="principal_amount"
                                    inputMode="decimal"
                                />
                            </FormField>
                            <FormField
                                error={errors.reason}
                                label="Motif"
                                htmlFor="reason"
                            >
                                <Textarea id="reason" name="reason" />
                            </FormField>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">Annuler</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    disabled={processing || !borrower}
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
