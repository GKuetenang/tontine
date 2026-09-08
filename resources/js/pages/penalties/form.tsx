import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import type { SelectOption } from '@/components/select-with-items';
import { SelectWithItems } from '@/components/select-with-items';
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
import penalties from '@/routes/groups/sessions/penalties';
import type { MemberUser } from '@/types';

type Props = {
    trigger: ReactElement;
    group: { slug: string };
    session: { slug: string };
    meetings: SelectOption[];
    rules: SelectOption[];
};

export function PenaltyForm({
    trigger,
    group,
    session,
    meetings,
    rules,
}: Props) {
    const [open, setOpen] = useState(false);
    const [member, setMember] = useState<MemberUser | null>(null);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                onInteractOutside={(event) => event.preventDefault()}
            >
                <Form
                    {...penalties.store.form({
                        group: group.slug,
                        session: session.slug,
                    })}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false);
                        setMember(null);
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Ajouter une pénalité</DialogTitle>
                                <DialogDescription>
                                    Enregistrez manuellement une pénalité pour
                                    un participant.
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                label="Membre"
                                htmlFor="membership_id"
                                error={errors.membership_id}
                                required
                            >
                                <input
                                    type="hidden"
                                    name="membership_id"
                                    id="membership_id"
                                    value={member?.id ?? ''}
                                />
                                <UserCombobox onSelect={setMember} />
                                {member && (
                                    <p className="rounded-md border bg-muted p-2 text-sm">
                                        {member.name} · {member.email}
                                    </p>
                                )}
                            </FormField>
                            <FormField
                                label="Assise"
                                htmlFor="meeting_id"
                                error={errors.meeting_id}
                                required
                            >
                                <SelectWithItems
                                    name="meeting_id"
                                    items={meetings}
                                    placeholder="Sélectionner une assise"
                                />
                            </FormField>
                            <FormField
                                label="Règle appliquée"
                                htmlFor="penalty_rule_id"
                                error={errors.penalty_rule_id}
                                required
                            >
                                <SelectWithItems
                                    name="penalty_rule_id"
                                    items={rules}
                                    placeholder="Sélectionner une règle active"
                                />
                            </FormField>
                            <FormField
                                label="Montant"
                                htmlFor="amount"
                                error={errors.amount}
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
                                label="Motif"
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
                                <Button
                                    disabled={
                                        processing ||
                                        !member ||
                                        meetings.length === 0 ||
                                        rules.length === 0
                                    }
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
