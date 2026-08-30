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

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import { Spinner } from '@/components/ui/spinner';

import payouts from '@/routes/tontines/sessions/meetings/payouts';

import type {
    Meeting,
    MeetingPayoutContext,
    PayoutCandidate,
    Session,
    Tontine
} from '@/types';

import {
    Form,
} from '@inertiajs/react';

import {
    SaveIcon,
} from 'lucide-react';

import type {
    ReactElement,
} from 'react';

import {
    useState,
} from 'react';

type Props = {
    trigger: ReactElement;

    tontine: Tontine;
    session: Session;
    meeting: Meeting;

    context: MeetingPayoutContext;

    defaultCandidate?: PayoutCandidate;
};

export function CreatePayoutForm({
    trigger,
    tontine,
    session,
    meeting,
    context,
    defaultCandidate,
}: Props) {
    const [open, setOpen] =
        useState(false);

    const [
        drawEntryId,
        setDrawEntryId,
    ] = useState(
        defaultCandidate
            ? String(
                defaultCandidate
                    .draw_entry_id,
            )
            : '',
    );

    const handleOpenChange = (
        value: boolean,
    ) => {
        if (value) {
            setDrawEntryId(
                defaultCandidate
                    ? String(
                        defaultCandidate
                            .draw_entry_id,
                    )
                    : '',
            );
        }

        setOpen(value);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={
                handleOpenChange
            }
        >
            <DialogTrigger asChild>
                {trigger}
            </DialogTrigger>

            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        Préparer un versement
                    </DialogTitle>

                    <DialogDescription>
                        Sélectionnez le bénéficiaire
                        et saisissez le montant à
                        verser.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...payouts.store.form({
                        tontine:
                            tontine.slug!,

                        session:
                            session.slug,

                        meeting:
                            meeting.slug,
                    })}
                    resetOnSuccess
                    options={{
                        preserveScroll:
                            true,
                    }}
                    onSuccess={() =>
                        setOpen(false)
                    }
                >
                    {({
                        errors,
                        processing,
                    }) => (
                        <div className="space-y-4">
                            <FormField
                                label="Bénéficiaire"
                                htmlFor="draw_entry_id"
                                error={
                                    errors.draw_entry_id
                                }
                                required
                            >
                                <input
                                    type="hidden"
                                    name="draw_entry_id"
                                    value={
                                        drawEntryId
                                    }
                                />

                                <Select
                                    value={
                                        drawEntryId
                                    }
                                    onValueChange={
                                        setDrawEntryId
                                    }
                                >
                                    <SelectTrigger id="draw_entry_id">
                                        <SelectValue placeholder="Sélectionner un bénéficiaire" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        {context.available.map(
                                            (
                                                candidate,
                                            ) => (
                                                <SelectItem
                                                    key={
                                                        candidate.draw_entry_id
                                                    }
                                                    value={String(
                                                        candidate.draw_entry_id,
                                                    )}
                                                >
                                                    {
                                                        candidate.member_name
                                                    }{' '}
                                                    — Position{' '}
                                                    {
                                                        candidate.position
                                                    }
                                                    {candidate.entry_number
                                                        > 1
                                                        ? ` — Part ${candidate.entry_number}`
                                                        : ''}
                                                    {candidate.expected
                                                        ? ' — Prévu'
                                                        : ''}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                            </FormField>

                            <FormField
                                label="Montant"
                                htmlFor="amount"
                                error={
                                    errors.amount
                                }
                                required
                            >
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                            </FormField>

                            <DialogFooter>
                                <DialogClose
                                    asChild
                                >
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
                                        || !drawEntryId
                                    }
                                >
                                    {processing
                                        ? (
                                            <Spinner />
                                        )
                                        : (
                                            <SaveIcon className="size-4" />
                                        )}

                                    Préparer
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}