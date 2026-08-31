import { Link } from '@inertiajs/react';
import { EllipsisIcon, Pencil, TrashIcon } from 'lucide-react';
import { toast } from 'sonner';
import type { SelectOption } from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuthorization } from '@/hooks/use-authorization';
import memberships from '@/routes/tontines/memberships';
import type { Membership, ResultTontine } from '@/types';
import { EditMembershipForm } from './form';

type Props = {
    tontine: ResultTontine;
    membership: Membership;
    roles: SelectOption[];
    statuses: SelectOption[];
};

export function Actions({ tontine, membership, roles, statuses }: Props) {
    console.log({ membership });

    const { can, canAny } = useAuthorization();

    const hasActions = canAny(
        'memberships.view',
        'memberships.create',
        'memberships.update',
        'memberships.delete',
    );

    if (!hasActions) {
        return (
            <span
                className="text-muted-foreground"
                aria-label="Aucune action disponible"
            >
                —
            </span>
        );
    }

    return (
        <div className="flex items-end">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        className="ml-auto"
                        variant="ghost"
                        size="icon"
                        aria-label="Actions de la membership"
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end">
                    {can('memberships.update') && (
                        <EditMembershipForm
                            membership={membership}
                            roles={roles}
                            tontine={tontine}
                            statuses={statuses}
                            trigger={
                                <DropdownMenuItem
                                    onSelect={(event) => event.preventDefault()}
                                >
                                    <Pencil className="size-4" />
                                    Modifier
                                </DropdownMenuItem>
                            }
                        />
                    )}

                    {can('memberships.delete') && (
                        <DropdownMenuItem asChild>
                            <Link
                                className="w-full"
                                href={memberships.destroy({
                                    tontine: tontine.slug,
                                    membership: membership.id,
                                })}
                                onBefore={() =>
                                    confirm(
                                        'Voulez-vous vraiment supprimer ce membre?',
                                    )
                                }
                                onError={(errors) => {
                                    const firstError = Object.values(errors)[0];
                                    toast.error(firstError);
                                }}
                            >
                                <TrashIcon size={16} />
                                Supprimer
                            </Link>
                        </DropdownMenuItem>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
