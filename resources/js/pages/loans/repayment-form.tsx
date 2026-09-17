import { Form } from '@inertiajs/react';
import { CircleDollarSign } from 'lucide-react';
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
import { useTranslation } from '@/hooks/use-translation';
import { formatCurrency } from '@/lib/utils';
import loans from '@/routes/groups/sessions/loans';
import type { Loan, Session, Group } from '@/types';

export function CreateRepaymentForm({
    trigger,
    group,
    session,
    loan,
}: {
    trigger: ReactElement;
    group: Group;
    session: Session;
    loan: Loan;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <Form
                    {...loans.repayments.store.form({
                        group: group.slug!,
                        session: session.slug,
                        loan: loan.id,
                    })}
                    resetOnSuccess
                    onSuccess={() => setOpen(false)}
                    onBefore={() =>
                        confirm(
                            `Confirmer le remboursement du prêt de ${loan.member_name} ? Cette opération créera un crédit financier.`,
                        )
                    }
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {t('Enregistrer un remboursement')}
                                </DialogTitle>
                                <DialogDescription>
                                    {t('Solde restant :')}{' '}
                                    {formatCurrency(loan.remaining_amount)}
                                    {t(
                                        '. L’intérêt restant sera imputé avant le capital.',
                                    )}
                                </DialogDescription>
                            </DialogHeader>
                            {errors.repayment && (
                                <div
                                    role="alert"
                                    className="rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                                >
                                    {errors.repayment}
                                </div>
                            )}
                            <FormField
                                error={errors.amount}
                                label={t('Montant remboursé')}
                                htmlFor="amount"
                                required
                            >
                                <Input
                                    id="amount"
                                    name="amount"
                                    inputMode="decimal"
                                    defaultValue={loan.remaining_amount}
                                />
                            </FormField>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">
                                        {t('Annuler')}
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <CircleDollarSign />
                                    )}
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
