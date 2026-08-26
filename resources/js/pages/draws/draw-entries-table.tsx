import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import drawRoutes from '@/routes/tontines/sessions/draw';

import type {
    Draw,
    ResultTontine,
    Session,
} from '@/types';

import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
    type DragEndEvent,
    type DragStartEvent,
} from '@dnd-kit/core';

import {
    router,
} from '@inertiajs/react';

import {
    GripVerticalIcon,
} from 'lucide-react';

import {
    useState,
} from 'react';

import {
    toast,
} from 'sonner';

import {
    DrawEntryRow,
} from './draw-entry-row';

type DrawEntry =
    NonNullable<
        Draw['entries']
    >[number];

type Props = {
    tontine: ResultTontine;
    session: Session;
    draw: Draw;
    canSwap: boolean;
};

export function DrawEntriesTable({
    tontine,
    session,
    draw,
    canSwap,
}: Props) {
    const [
        entries,
        setEntries,
    ] = useState<DrawEntry[]>(
        () =>
            [
                ...(draw.entries ??
                    []),
            ].sort(
                (a, b) =>
                    a.position
                    - b.position,
            ),
    );

    const [
        activeId,
        setActiveId,
    ] = useState<
        number | null
    >(null);

    const sensors =
        useSensors(
            useSensor(
                PointerSensor,
                {
                    activationConstraint:
                    {
                        distance: 6,
                    },
                },
            ),

            useSensor(
                KeyboardSensor,
            ),
        );

    const handleDragStart = (
        event: DragStartEvent,
    ) => {
        setActiveId(
            Number(
                event.active.id,
            ),
        );
    };

    const handleDragEnd = (
        event: DragEndEvent,
    ) => {
        setActiveId(null);

        const {
            active,
            over,
        } = event;

        if (
            !over
            || active.id === over.id
        ) {
            return;
        }

        const source =
            entries.find(
                (entry) =>
                    entry.id
                    === active.id,
            );

        const target =
            entries.find(
                (entry) =>
                    entry.id
                    === over.id,
            );

        if (
            !source
            || !target
        ) {
            return;
        }

        const previousEntries =
            entries;

        const swapped =
            swapEntries(
                entries,
                source.id,
                target.id,
            );

        /*
         * Optimistic UI.
         */
        setEntries(swapped);

        router.patch(
            drawRoutes.swap({
                tontine:
                    tontine.slug,

                session:
                    session.slug,
            }).url,

            {
                source_entry_id:
                    source.id,

                target_entry_id:
                    target.id,
            },

            {
                preserveScroll:
                    true,

                preserveState:
                    true,

                onError: (
                    errors,
                ) => {
                    setEntries(
                        previousEntries,
                    );

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
                },
            },
        );
    };

    const activeEntry =
        entries.find(
            (entry) =>
                entry.id
                === activeId,
        );

    return (
        <div className="space-y-3">

            <DndContext
                sensors={sensors}
                collisionDetection={
                    closestCenter
                }
                onDragStart={
                    handleDragStart
                }
                onDragCancel={() =>
                    setActiveId(null)
                }
                onDragEnd={
                    handleDragEnd
                }
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            {canSwap && (
                                <TableHead className="w-12" />
                            )}

                            <TableHead className="w-24">
                                Position
                            </TableHead>

                            <TableHead>
                                Participant
                            </TableHead>

                            <TableHead>
                                Numéro du tour
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {entries.map(
                            (entry) => (
                                <DrawEntryRow
                                    key={
                                        entry.id
                                    }
                                    entry={
                                        entry
                                    }
                                    canDrag={
                                        canSwap
                                    }
                                />
                            ),
                        )}
                    </TableBody>
                </Table>

                <DragOverlay>
                    {activeEntry ? (
                        <div className="min-w-72 rounded-md border bg-background p-3 shadow-lg">
                            <div className="flex items-center gap-3">
                                <GripVerticalIcon className="size-4 text-muted-foreground" />

                                <div>
                                    <p className="font-medium">
                                        Position{' '}
                                        {
                                            activeEntry.position
                                        }
                                    </p>

                                    <p className="text-sm text-muted-foreground">
                                        {activeEntry
                                            .session_participant
                                            ?.membership
                                            ?.user
                                            ?.name ??
                                            '—'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : null}
                </DragOverlay>
            </DndContext>
        </div>
    );
}

function swapEntries(
    entries: DrawEntry[],
    sourceId: number,
    targetId: number,
): DrawEntry[] {
    const source =
        entries.find(
            (entry) =>
                entry.id
                === sourceId,
        );

    const target =
        entries.find(
            (entry) =>
                entry.id
                === targetId,
        );

    if (
        !source
        || !target
    ) {
        return entries;
    }

    return entries
        .map((entry) => {
            if (
                entry.id
                === source.id
            ) {
                return {
                    ...entry,

                    position:
                        target.position,
                };
            }

            if (
                entry.id
                === target.id
            ) {
                return {
                    ...entry,

                    position:
                        source.position,
                };
            }

            return entry;
        })
        .sort(
            (a, b) =>
                a.position
                - b.position,
        );
}