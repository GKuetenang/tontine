import { Button } from "@/components/ui/button";
import { Meeting, MeetingAgendaItem, Session, Tontine } from "@/types";
import { useSortable } from "@dnd-kit/sortable";
import { GripVerticalIcon } from "lucide-react";
import { AgendaItem } from "./agenda-item";

export function SortableAgendaItem({
    tontine,
    session,
    meeting,
    item,
}: {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
    item: MeetingAgendaItem;
}) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: item.id,
    });

    const style: React.CSSProperties = {
        transform: transform
            ? `translate3d(${transform.x}px, ${transform.y}px, 0) scaleX(${transform.scaleX ?? 1}) scaleY(${transform.scaleY ?? 1})`
            : undefined,
        transition,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={
                isDragging
                    ? 'relative z-10 bg-background shadow-sm'
                    : undefined
            }
        >
            <AgendaItem
                tontine={tontine}
                session={session}
                meeting={meeting}
                item={item}
                canEdit
                dragHandle={
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="cursor-grab rounded-full text-muted-foreground active:cursor-grabbing"
                        {...attributes}
                        {...listeners}
                        aria-label="Déplacer ce point"
                    >
                        <GripVerticalIcon className="size-4" />
                    </Button>
                }
            />
        </div>
    );
}