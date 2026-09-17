import { Link } from '@inertiajs/react';
import {
    CheckCircle2Icon,
    EllipsisIcon,
    LockKeyholeIcon,
    Pencil,
    RotateCcwIcon,
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
import { useTranslation } from '@/hooks/use-translation';
import sessions from '@/routes/groups/sessions';
import type { ResultGroup, Session } from '@/types';
import { EditSessionForm } from './form';

type Props = {
    group: ResultGroup;
    session: Session;
    draw_allocation_modes: SelectOption[];
};

export function Actions({ group, session, draw_allocation_modes }: Props) {
    const { t } = useTranslation();
    const { can, canAny } = useAuthorization();

    const hasActions = canAny(
        'sessions.view',
        'sessions.create',
        'sessions.update',
        'sessions.activate',
        'sessions.prepare',
        'sessions.close',
        'sessions.delete',
    );

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
                        aria-label={t('Actions de la session')}
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
                                    {t('Modifier')}
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
                                    {t('Activer')}
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('sessions.prepare') && session.status === 'active' && (
                        <DropdownMenuItem asChild>
                            <Link
                                className="w-full"
                                href={sessions.prepare({
                                    group: group.slug,
                                    session: session.slug,
                                })}
                                onBefore={() =>
                                    confirm(
                                        'Voulez-vous vraiment remettre cette session en préparation ?',
                                    )
                                }
                                onError={(errors) =>
                                    toast.error(Object.values(errors)[0])
                                }
                            >
                                <RotateCcwIcon size={16} />
                                {t('Remettre en préparation')}
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
                                    {t('Fermer')}
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
                                {t('Supprimer')}
                            </Link>
                        </DropdownMenuItem>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
