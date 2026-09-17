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
import { useTranslation } from '@/hooks/use-translation';
import groups from '@/routes/groups';
import type { Group } from '@/types';

type Props = {
    group: Group;
};

export function Actions({ group }: Props) {
    const { t } = useTranslation();
    const hasActions = group.can?.update || group.can?.delete;

    if (!hasActions) {
        return (
            <span
                className="text-muted-foreground"
                aria-label={t('Aucune action disponible')}
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
                        aria-label={t('Actions de la réunion')}
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    {group.can?.update && (
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
                                {t('Modifier')}
                            </Link>
                        </DropdownMenuItem>
                    )}

                    {group.can?.delete && (
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
                                {t('Supprimer')}
                            </Link>
                        </DropdownMenuItem>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
