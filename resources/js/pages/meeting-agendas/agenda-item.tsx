import { Button } from "@/components/ui/button";
import { useAuthorization } from "@/hooks/use-authorization";
import agenda from "@/routes/tontines/sessions/meetings/agenda";
import { Meeting, MeetingAgendaItem, Session, Tontine } from "@/types";
import { Link } from "@inertiajs/react";
import { PencilIcon, TrashIcon } from "lucide-react";
import { toast } from "sonner";
import { EditAgendaItemForm } from "./form";

export function AgendaItem({
    tontine,
    session,
    meeting,
    item,
    canEdit,
    dragHandle,
}: {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    item: MeetingAgendaItem;
    canEdit: boolean;
    dragHandle?: React.ReactNode;
}) {
    const { can } =
        useAuthorization();

    return (
        <div className="flex items-start gap-3 py-4">
            {dragHandle}

            <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-medium">
                {item.position}
            </div>

            <div className="min-w-0 flex-1">
                <p className="font-medium">
                    {item.title}
                </p>

                {item.description && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {
                            item.description
                        }
                    </p>
                )}
            </div>

            {canEdit && (
                <div className="flex items-center gap-1">
                    {can(
                        'meeting-agenda.update',
                    ) && (
                            <EditAgendaItemForm
                                tontine={
                                    tontine
                                }
                                session={
                                    session
                                }
                                meeting={
                                    meeting
                                }
                                agendaItem={
                                    item
                                }
                                trigger={
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="rounded-full"
                                    >
                                        <PencilIcon className="size-4" />
                                    </Button>
                                }
                            />
                        )}

                    {can(
                        'meeting-agenda.delete',
                    ) && (
                            <Button
                                asChild
                                variant="ghost"
                                size="icon"
                                className="rounded-full text-destructive hover:text-destructive"
                            >
                                <Link
                                    href={agenda.destroy(
                                        {
                                            tontine:
                                                tontine.slug!,
                                            session:
                                                session.slug,
                                            meeting:
                                                meeting.slug,
                                            agendaItem:
                                                item.id,
                                        },
                                    )}
                                    onBefore={() =>
                                        confirm(
                                            'Supprimer ce point de l’ordre du jour ?',
                                        )
                                    }
                                    onError={(
                                        errors,
                                    ) => {
                                        const firstError =
                                            Object.values(
                                                errors,
                                            )[0];

                                        if (
                                            firstError
                                        ) {
                                            toast.error(
                                                firstError,
                                            );
                                        }
                                    }}
                                >
                                    <TrashIcon className="size-4" />
                                </Link>
                            </Button>
                        )}
                </div>
            )}
        </div>
    );
}