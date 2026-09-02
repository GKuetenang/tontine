import { Link } from '@inertiajs/react';
import {
    CheckCircle2Icon,
    EllipsisIcon,
    LockKeyholeIcon,
    Pencil,
    TrashIcon,
} from 'lucide-react';
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
import sessions from '@/routes/groups/sessions';
import type { ResultGroup, Session } from '@/types';
import { EditSessionForm } from './form';

type Props = {
    group: ResultGroup;
    session: Session;
    draw_allocation_modes: SelectOption[];
};

export function Actions({ group, session, draw_allocation_modes }: Props) {
    const { can, canAny } = useAuthorization();

    const hasActions = canAny(
        'sessions.view',
        'sessions.create',
        'sessions.update',
        'sessions.activate',
        'sessions.close',
        'sessions.delete',
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
                        aria-label="Actions de la session"
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end">
                    {can('sessions.update') && (
                        <EditSessionForm
                            draw_allocation_modes={draw_allocation_modes}
                            group={group}
                            session={session}
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

                    {can('sessions.activate') &&
                        !(session.status === 'active') && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={sessions.activate({
                                        group: group.slug,
                                        session: session.slug,
                                    })}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment activer cette session?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(errors)[0];
                                        toast.error(firstError);
                                    }}
                                >
                                    <CheckCircle2Icon size={16} />
                                    Activer
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('sessions.close') &&
                        !(session.status === 'closed') && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={sessions.close({
                                        group: group.slug,
                                        session: session.slug,
                                    })}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment fermer cette session?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(errors)[0];
                                        toast.error(firstError);
                                    }}
                                >
                                    <LockKeyholeIcon size={16} />
                                    Fermer
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('sessions.delete') && (
                        <DropdownMenuItem asChild>
                            <Link
                                className="w-full"
                                href={sessions.destroy({
                                    group: group.slug,
                                    session: session.slug,
                                })}
                                onBefore={() =>
                                    confirm(
                                        'Voulez-vous vraiment supprimer cette session?',
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
