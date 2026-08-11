import { Form } from "@inertiajs/react";
import { SaveIcon } from "lucide-react";
import type { ReactElement } from "react";
import { useState } from "react";
import { FormField } from "@/components/form-field";
import type { SelectOption } from "@/components/select-with-items";
import { SelectWithItems } from "@/components/select-with-items";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Spinner } from "@/components/ui/spinner";
import { UserCombobox } from "@/components/user-combobox";
import { cn } from "@/lib/utils";
import memberships from "@/routes/tontines/memberships";
import type { Membership, MemberUser } from "@/types";
import type { ResultTontine } from ".";

type Props = {
    trigger: ReactElement,
    tontine: ResultTontine,
    roles: SelectOption[],
    membership: Membership;
    statuses: SelectOption[];
}

export function EditMembershipForm({ trigger, tontine, roles, membership, statuses }: Props) {
    const defaultUser = membership.id ? membership.user : null;

    const [selectedUser, setSelectedUser] = useState<MemberUser | null | undefined>(defaultUser);
    const [open, setOpen] = useState(false);

    const handleOpenChange = (value: boolean) => {
        setOpen(value);

        if (value) {
            setSelectedUser(defaultUser);
        }
    }

    const action = membership.id ?
        memberships.update.form({ tontine: tontine.slug, membership: membership.id }) :
        memberships.store.form({ tontine: tontine.slug })


    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                {trigger}
            </DialogTrigger>
            <DialogContent
                className="sm:max-w-md"
                onInteractOutside={(e) => e.preventDefault()}
                onEscapeKeyDown={(e) => e.preventDefault()}
            >
                <Form {...action}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false)

                        if (!membership.id) {
                            setSelectedUser(null);
                        }
                    }}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Ajouter un membre</DialogTitle>
                                <DialogDescription>
                                    Choisir le rôle à attribuer au membre
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                error={errors['user_id']}
                                label="Membre"
                                htmlFor="user_id"
                            >
                                <input
                                    type="hidden"
                                    name='user_id'
                                    value={selectedUser?.id}
                                />

                                <UserCombobox
                                    onSelect={setSelectedUser}
                                />
                                {selectedUser && (
                                    <div className={cn("relative flex-col bg-accent  text-shadow-accent-foreground border cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm",
                                        errors['user_id'] && 'bg-destructive/20 border-destructive'
                                    )}>
                                        <p className="text-sm">{selectedUser.name}</p>
                                        <p className="text-xs">{selectedUser.email}</p>
                                    </div>
                                )}

                            </FormField>

                            <FormField
                                error={errors['role']}
                                label="Rôle"
                                htmlFor="role"
                            >
                                <SelectWithItems
                                    items={roles}
                                    id="role"
                                    name="role"
                                    placeholder="Selectionner un rôle"
                                    defaultValue={membership?.role?.name}
                                    aria-invalid={!!errors['role']}
                                />
                            </FormField>

                            {membership.id && <FormField
                                error={errors['status']}
                                label="Statut"
                                htmlFor="status"
                            >
                                <SelectWithItems
                                    items={statuses}
                                    id="status"
                                    name="status"
                                    placeholder="Selectionner un statut"
                                    defaultValue={membership?.status}
                                    aria-invalid={!!errors['status']}
                                />
                            </FormField>}

                            <DialogFooter>
                                <DialogClose asChild><Button variant="outline">Cancel</Button></DialogClose>
                                <Button
                                    type="submit"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
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
    )
}
