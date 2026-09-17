import { Form } from '@inertiajs/react';
import { RotateCcwIcon, SaveIcon } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import type { SelectOption } from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { TableCell, TableRow } from '@/components/ui/table';
import { useTranslation } from '@/hooks/use-translation';
import assignments from '@/routes/groups/mandates/assignments';
import type { ResultGroup } from '@/types';

export type MandateMember = {
    id: number;
    member_number: string;
    name: string;
    email: string;
    role_id: number | null;
};

export default function AssignmentRow({
    group,
    mandateId,
    member,
    roles,
    canUpdate,
}: {
    group: ResultGroup;
    mandateId: number;
    member: MandateMember;
    roles: SelectOption[];
    canUpdate: boolean;
}) {
    const { t } = useTranslation();
    const initialRole = member.role_id ? String(member.role_id) : 'none';
    const [roleId, setRoleId] = useState(initialRole);
    const changed = roleId !== initialRole;

    return (
        <TableRow className="h-14">
            <TableCell className="pl-6">
                <p className="font-medium">{member.name}</p>
                <p className="text-xs text-muted-foreground">
                    {member.member_number} · {member.email}
                </p>
            </TableCell>
            <TableCell>
                <Select
                    value={roleId}
                    onValueChange={setRoleId}
                    disabled={!canUpdate}
                >
                    <SelectTrigger className="w-full max-w-xs">
                        <SelectValue placeholder={t('Aucune responsabilité')} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">
                            {t('Aucune responsabilité')}
                        </SelectItem>
                        {roles.map((role) => (
                            <SelectItem
                                key={role.value}
                                value={String(role.value)}
                            >
                                {role.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </TableCell>
            <TableCell className="pr-6">
                {canUpdate && (
                    <Form
                        {...assignments.update.form({
                            group: group.slug!,
                            mandate: mandateId,
                            membership: member.id,
                        })}
                        onError={(errors) => {
                            const firstError = Object.values(errors)[0];

                            if (firstError) {
                                toast.error(firstError);
                            }
                        }}
                    >
                        {({ processing }) => (
                            <div className="flex justify-end gap-1">
                                <input
                                    type="hidden"
                                    name="role_id"
                                    value={roleId === 'none' ? '' : roleId}
                                />
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    disabled={!changed || processing}
                                    onClick={() => setRoleId(initialRole)}
                                >
                                    <RotateCcwIcon /> {t('Annuler')}
                                </Button>
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={!changed || processing}
                                >
                                    {processing ? <Spinner /> : <SaveIcon />}{' '}
                                    {t('Enregistrer')}
                                </Button>
                            </div>
                        )}
                    </Form>
                )}
            </TableCell>
        </TableRow>
    );
}
