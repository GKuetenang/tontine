import { Form } from '@inertiajs/react';
import { format } from 'date-fns';
import { SaveIcon } from 'lucide-react';
import { useState } from 'react';

import { AppDateTimePicker } from '@/components/app-datetime-picker';
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
import payments from '@/routes/groups/sessions/meetings/contributions/payments';
import type { Contribution, Group, Meeting, Session } from '@/types';

import type { ReactElement } from 'react';

type Props = {
    trigger: ReactElement;
    group: Group;
    session: Session;
    meeting: Meeting;
    contribution: Contribution;
};

export function RecordContributionPaymentForm({
    trigger,
    group,
    session,
    meeting,
    contribution,
}: Props) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);

    const [occurredAt, setOccurredAt] = useState<Date | undefined>(
        () => new Date(),
    );

    const action = payments.store.form({
        group: group.slug!,
        session: session.slug,
        meeting: meeting.slug,
        contribution: contribution.id,
    });

    const handleOpenChange = (value: boolean) => {
        if (value) {
            setOccurredAt(new Date());
        }

        setOpen(value);
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>

            <DialogContent className="sm:max-w-md">
                <Form
                    {...action}
                    resetOnSuccess
                    options={{
                        preserveScroll: true,
                    }}
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {t('Enregistrer un paiement')}
                                </DialogTitle>

                                <DialogDescription>
                                    {t('Montant restant :')}{' '}
                                    {formatCurrency(
                                        contribution.remaining_amount,
                                        group.currency,
                                    )}
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                label={t('Montant payé')}
                                htmlFor="amount"
                                error={errors.amount}
                                required
                            >
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min={1}
                                    max={contribution.remaining_amount}
                                    defaultValue={contribution.remaining_amount}
                                />
                            </FormField>

                            <FormField
                                label={t('Date du paiement')}
                                htmlFor="occurred_at"
                                error={errors.occurred_at}
                                required
                            >
                                <input
                                    type="hidden"
                                    name="occurred_at"
                                    value={
                                        occurredAt
                                            ? format(
                                                  occurredAt,
                                                  'yyyy-MM-dd HH:mm:ss',
                                              )
                                            : ''
                                    }
                                />

                                <AppDateTimePicker
                                    granularity="minute"
                                    value={occurredAt}
                                    onChange={setOccurredAt}
                                />
                            </FormField>

                            <FormField
                                label={t('Description')}
                                htmlFor="description"
                                error={errors.description}
                                optional
                            >
                                <textarea
                                    id="description"
                                    name="description"
                                    placeholder={t('Ex. Paiement en espèces')}
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
