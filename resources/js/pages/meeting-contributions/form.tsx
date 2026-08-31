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

import { formatCurrency } from '@/lib/utils';
import payments from '@/routes/tontines/sessions/meetings/contributions/payments';

import type { Contribution, Meeting, Session, Tontine } from '@/types';

type Props = {
    trigger: ReactElement;
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    contribution: Contribution;
};

export function RecordContributionPaymentForm({
    trigger,
    tontine,
    session,
    meeting,
    contribution,
}: Props) {
    const [open, setOpen] = useState(false);

    const [occurredAt, setOccurredAt] = useState<Date | undefined>(
        () => new Date(),
    );

    const action = payments.store.form({
        tontine: tontine.slug!,
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
                                    Enregistrer un paiement
                                </DialogTitle>

                                <DialogDescription>
                                    Montant restant :{' '}
                                    {formatCurrency(
                                        contribution.remaining_amount,
                                        tontine.currency,
                                    )}
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                label="Montant payé"
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
                                label="Date du paiement"
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

                                <DateTimePicker
                                    granularity="minute"
                                    value={occurredAt}
                                    onChange={setOccurredAt}
                                />
                            </FormField>

                            <FormField
                                label="Description"
                                htmlFor="description"
                                error={errors.description}
                                optional
                            >
                                <textarea
                                    id="description"
                                    name="description"
                                    placeholder="Ex. Paiement en espèces"
                                    className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
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
