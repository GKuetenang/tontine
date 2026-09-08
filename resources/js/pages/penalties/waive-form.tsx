import { Form } from '@inertiajs/react';
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
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import penalties from '@/routes/groups/sessions/penalties';

export function WaivePenaltyForm({
    trigger,
    group,
    session,
    penalty,
}: {
    trigger: ReactElement;
    group: string;
    session: string;
    penalty: number;
}) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <Form
                    {...penalties.waive.form({ group, session, penalty })}
                    onSuccess={() => setOpen(false)}
                    resetOnSuccess
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Exempter le membre</DialogTitle>
                                <DialogDescription>
                                    La pénalité restera visible dans
                                    l’historique et ne sera plus exigible.
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                label="Motif de l’exemption"
                                htmlFor="reason"
                                error={errors.reason}
                                required
                            >
                                <Textarea id="reason" name="reason" required />
                            </FormField>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">Annuler</Button>
                                </DialogClose>
                                <Button disabled={processing}>
                                    {processing && <Spinner />} Confirmer
                                    l’exemption
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
