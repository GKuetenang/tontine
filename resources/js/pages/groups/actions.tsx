import { Link } from '@inertiajs/react';
import { EditIcon, EllipsisIcon, TrashIcon } from 'lucide-react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import groups from '@/routes/groups';
import type { Group } from '@/types';

type Props = {
    group: Group;
};

export function Actions({ group }: Props) {
    const hasActions = group.can?.update || group.can?.delete;

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
                        aria-label="Actions de la réunion"
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem
                        asChild
                        onSelect={(event) => event.preventDefault()}
                    >
                        <Link
                            className="w-full"
                            href={groups.edit({
                                group: group.slug!,
                            })}
                        >
                            <EditIcon size={16} />
                            Modifier
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                        <Link
                            className="w-full"
                            href={groups.destroy({
                                group: group.slug!,
                            })}
                            onBefore={() =>
                                confirm(
                                    'Voulez-vous vraiment supprimer cette réunion?',
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
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
