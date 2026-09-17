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
import { Spinner } from '@/components/ui/spinner';
import { UserCombobox } from '@/components/user-combobox';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import memberships from '@/routes/groups/memberships';
import type { Membership, MemberUser, ResultGroup } from '@/types';

type Props = {
    trigger: ReactElement;
    group: ResultGroup;
    membership: Membership;
    statuses: SelectOption[];
};

export function EditMembershipForm({
    trigger,
    group,
    membership,
    statuses,
}: Props) {
    const { t } = useTranslation();
    const defaultUser = membership.id ? membership.user : null;

    const [selectedUser, setSelectedUser] = useState<
        MemberUser | null | undefined
    >(defaultUser);
    const [open, setOpen] = useState(false);

    const handleOpenChange = (value: boolean) => {
        setOpen(value);

        if (value) {
            setSelectedUser(defaultUser);
        }
    };

    const action = membership.id
        ? memberships.update.form({
              group: group.slug!,
              membership: membership.id,
          })
        : memberships.store.form({ group: group.slug! });

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                className="sm:max-w-md"
                onInteractOutside={(e) => e.preventDefault()}
                onEscapeKeyDown={(e) => e.preventDefault()}
            >
                <Form
                    {...action}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false);

                        if (!membership.id) {
                            setSelectedUser(null);
                        }
                    }}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {t('Ajouter un membre')} {group.name}
                                </DialogTitle>
                                <DialogDescription>
                                    {t('Rechercher par nom ou adresse e-mail.')}
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                error={errors['user_id']}
                                label={t('Membre')}
                                htmlFor="user_id"
                            >
                                <input
                                    type="hidden"
                                    name="user_id"
                                    value={selectedUser?.id}
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

                            {membership.id && (
                                <FormField
                                    error={errors['status']}
                                    label={t('Statut')}
                                    htmlFor="status"
                                >
                                    <SelectWithItems
                                        items={statuses}
                                        id="status"
                                        name="status"
                                        placeholder={t(
                                            'Selectionner un statut',
                                        )}
                                        defaultValue={membership?.status}
                                        aria-invalid={!!errors['status']}
                                    />
                                </FormField>
                            )}

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">
                                        {t('Cancel')}
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                >
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
