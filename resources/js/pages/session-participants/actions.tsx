import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuthorization } from '@/hooks/use-authorization';
import sessionParticipants from '@/routes/tontines/sessions/participants';
import type {
    ResultTontine,
    Session,
    SessionParticipant,
} from '@/types';
import { Link } from '@inertiajs/react';
import {
    EllipsisIcon,
    Pencil,
    RotateCcwIcon,
    TrashIcon,
} from 'lucide-react';
import { toast } from 'sonner';

import { EditSessionParticipantForm } from './form';

type Props = {
    tontine: ResultTontine;
    session: Session;
    participant: SessionParticipant;
};

export function Actions({
    tontine,
    session,
    participant,
}: Props) {
    const { can, canAny } = useAuthorization();

    const hasActions = canAny(
        'session-participants.update',
        'session-participants.remove',
        'session-participants.reactivate',
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
                        aria-label="Actions du participant"
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end">
                    {participant.is_active &&
                        can('session-participants.update') && (
                            <EditSessionParticipantForm
                                tontine={tontine}
                                session={session}
                                participant={participant}
                                trigger={
                                    <DropdownMenuItem
                                        onSelect={(event) =>
                                            event.preventDefault()
                                        }
                                    >
                                        <Pencil className="size-4" />
                                        Modifier
                                    </DropdownMenuItem>
                                }
                            />
                        )}

                    {participant.is_active &&
                        can('session-participants.remove') && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={sessionParticipants.destroy({
                                        tontine: tontine.slug,
                                        session: session.slug,
                                        participant: participant.id,
                                    })}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment retirer ce participant de la session ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(errors)[0];

                                        toast.error(firstError);
                                    }}
                                >
                                    <TrashIcon className="size-4" />
                                    Retirer
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {!participant.is_active &&
                        can('session-participants.reactivate') && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={sessionParticipants.reactivate({
                                        tontine: tontine.slug,
                                        session: session.slug,
                                        participant: participant.id,
                                    })}
                                    method="patch"
                                    as="button"
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous réactiver ce participant dans la session ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(errors)[0];

                                        toast.error(firstError);
                                    }}
                                >
                                    <RotateCcwIcon className="size-4" />
                                    Réactiver
                                </Link>
                            </DropdownMenuItem>
                        )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}