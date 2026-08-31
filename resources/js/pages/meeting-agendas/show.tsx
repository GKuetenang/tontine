import {
    DndContext,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type { DragEndEvent } from '@dnd-kit/core';

import {
    SortableContext,
    arrayMove,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';

import { router } from '@inertiajs/react';

import { PlusIcon } from 'lucide-react';

import { useState } from 'react';

import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuthorization } from '@/hooks/use-authorization';
import agenda from '@/routes/tontines/sessions/meetings/agenda';
import type { Meeting, MeetingAgendaItem, Session, Tontine } from '@/types';

import { AgendaItem } from './agenda-item';
import { EmptyAgenda } from './empty-agenda';
import { EditAgendaItemForm } from './form';
import { SortableAgendaItem } from './sortable-agenda-item';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function MeetingAgenda(props: Props) {
    const agendaKey =
        props.meeting.agenda_items
            ?.map((item) => `${item.id}: ${item.position}:${item.updated_at}`)
            .join('|') ?? 'empty';

    return <MeetingAgendaContent key={agendaKey} {...props} />;
}

function MeetingAgendaContent({ tontine, session, meeting }: Props) {
    const { can } = useAuthorization();

    const [items, setItems] = useState<MeetingAgendaItem[]>(() =>
        [...(meeting.agenda_items ?? [])].sort(
            (a, b) => a.position - b.position,
        ),
    );

    const canEdit = meeting.status === 'scheduled';

    const canReorder = canEdit && can('meeting-agenda.update');

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 6,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        const previousItems = items;

        const oldIndex = items.findIndex((item) => item.id === active.id);

        const newIndex = items.findIndex((item) => item.id === over.id);

        if (oldIndex === -1 || newIndex === -1) {
            return;
        }

        const reorderedItems = arrayMove(items, oldIndex, newIndex).map(
            (item, index) => ({
                ...item,
                position: index + 1,
            }),
        );

        setItems(reorderedItems);

        router.patch(
            agenda.reorder({
                tontine: tontine.slug!,
                session: session.slug,
                meeting: meeting.slug,
            }).url,
            {
                items: reorderedItems.map((item) => item.id),
            },
            {
                preserveScroll: true,
                preserveState: true,

                onError: (errors) => {
                    setItems(previousItems);

                    const firstError = Object.values(errors)[0];

                    if (firstError) {
                        toast.error(firstError);
                    }
                },
            },
        );
    };

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Ordre du jour</CardTitle>

                {canEdit && can('meeting-agenda.create') && (
                    <EditAgendaItemForm
                        tontine={tontine}
                        session={session}
                        meeting={meeting}
                        trigger={
                            <Button variant="outline" size="sm">
                                <PlusIcon className="size-4" />
                                Ajouter un point
                            </Button>
                        }
                    />
                )}
            </CardHeader>

            <CardContent>
                {items.length === 0 ? (
                    <EmptyAgenda />
                ) : canReorder ? (
                    <DndContext
                        sensors={sensors}
                        collisionDetection={closestCenter}
                        onDragEnd={handleDragEnd}
                    >
                        <SortableContext
                            items={items.map((item) => item.id)}
                            strategy={verticalListSortingStrategy}
                        >
                            <div className="divide-y">
                                {items.map((item) => (
                                    <SortableAgendaItem
                                        key={item.id}
                                        tontine={tontine}
                                        session={session}
                                        meeting={meeting}
                                        item={item}
                                    />
                                ))}
                            </div>
                        </SortableContext>
                    </DndContext>
                ) : (
                    <div className="divide-y">
                        {items.map((item) => (
                            <AgendaItem
                                key={item.id}
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                                item={item}
                                canEdit={canEdit}
                            />
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
