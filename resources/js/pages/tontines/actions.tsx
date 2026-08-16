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
import tontines from '@/routes/tontines';
import type { Tontine } from '@/types';

type Props = {
    tontine: Tontine;
};

export function Actions({ tontine }: Props) {
    const hasActions = tontine.can?.update || tontine.can?.delete;

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
                        aria-label="Actions de la tontine"
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
                            href={tontines.edit({
                                tontine: tontine.slug!,
                            })}
                        >
                            <EditIcon size={16} />
                            Modifier
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                        <Link
                            className="w-full"
                            href={tontines.destroy({
                                tontine: tontine.slug!,
                            })}
                            onBefore={() =>
                                confirm(
                                    'Voulez-vous vraiment supprimer cette tontine?',
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
