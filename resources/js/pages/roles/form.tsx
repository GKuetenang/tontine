import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import roles from '@/routes/tontines/roles';
import type { Tontine } from '@/types';

export type RoleItem = {
    id: number;
    name: string;
    label: string;
    permissions: string[];
    permissions_count: number;
    users_count: number;
    editable: boolean;
};

export type PermissionOption = {
    value: string;
    label: string;
    group: string;
    group_label: string;
};

type Props = {
    tontine: Tontine;
    permissions: PermissionOption[];
    trigger: ReactElement;
    role?: RoleItem;
};

export function RoleForm({ tontine, permissions, trigger, role }: Props) {
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<string[]>(role?.permissions ?? []);
    const groups = Map.groupBy(permissions, (permission) => permission.group);
    const action = role
        ? roles.update.form({ tontine: tontine.slug!, role: role.id })
        : roles.store.form({ tontine: tontine.slug! });

    const toggle = (permission: string, checked: boolean) => {
        setSelected((current) =>
            checked
                ? [...new Set([...current, permission])]
                : current.filter((value) => value !== permission),
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                <Form {...action} onSuccess={() => setOpen(false)}>
                    {({ errors, processing }) => (
                        <div className="space-y-5">
                            <DialogHeader>
                                <DialogTitle>
                                    {role
                                        ? 'Modifier le rôle'
                                        : 'Créer un rôle'}
                                </DialogTitle>
                                <DialogDescription>
                                    Sélectionnez uniquement les responsabilités
                                    nécessaires à ce rôle.
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                label="Nom"
                                htmlFor="role-name"
                                error={errors.name}
                                required
                            >
                                <Input
                                    id="role-name"
                                    name="name"
                                    defaultValue={role?.name}
                                />
                            </FormField>
                            {selected.map((permission) => (
                                <input
                                    key={permission}
                                    type="hidden"
                                    name="permissions[]"
                                    value={permission}
                                />
                            ))}
                            <div className="grid gap-4 md:grid-cols-2">
                                {[...groups.entries()].map(
                                    ([group, options]) => (
                                        <section
                                            key={group}
                                            className="space-y-3 rounded-lg border p-4"
                                        >
                                            <h3 className="font-medium">
                                                {options[0].group_label}
                                            </h3>
                                            {options.map((permission) => (
                                                <Label
                                                    key={permission.value}
                                                    className="flex items-start gap-3 font-normal"
                                                >
                                                    <Checkbox
                                                        checked={selected.includes(
                                                            permission.value,
                                                        )}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            toggle(
                                                                permission.value,
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                    />
                                                    <span>
                                                        {permission.label}
                                                    </span>
                                                </Label>
                                            ))}
                                        </section>
                                    ),
                                )}
                            </div>
                            {errors.permissions && (
                                <p className="text-sm text-destructive">
                                    {errors.permissions}
                                </p>
                            )}
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Annuler
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
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
