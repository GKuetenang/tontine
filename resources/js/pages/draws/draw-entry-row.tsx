import {
    TableCell,
    TableRow,
} from '@/components/ui/table';

import { formatDate } from '@/lib';

import type {
    Draw,
} from '@/types';

import {
    useDraggable,
    useDroppable,
} from '@dnd-kit/core';

import {
    GripVerticalIcon,
} from 'lucide-react';

type DrawEntry =
    NonNullable<
        Draw['entries']
    >[number];

type Props = {
    entry: DrawEntry;
    canDrag: boolean;
};

export function DrawEntryRow({
    entry,
    canDrag,
}: Props) {
    const {
        attributes,
        listeners,
        setNodeRef:
        setDraggableRef,
        isDragging,
    } = useDraggable({
        id: entry.id,
        disabled: !canDrag,
    });

    const {
        setNodeRef:
        setDroppableRef,
        isOver,
    } = useDroppable({
        id: entry.id,
        disabled: !canDrag,
    });

    const setNodeRef = (
        node:
            HTMLTableRowElement
            | null,
    ) => {
        setDraggableRef(node);
        setDroppableRef(node);
    };

    return (
        <TableRow
            ref={setNodeRef}
            className={[
                isDragging
                    ? 'opacity-40'
                    : '',

                isOver
                    && !isDragging
                    ? 'bg-primary/10 ring-1 ring-inset ring-primary'
                    : '',
            ].join(' ')}
        >
            {canDrag && (
                <TableCell className="w-12">
                    <button
                        type="button"
                        className="flex cursor-grab touch-none items-center justify-center text-muted-foreground transition-colors hover:text-foreground active:cursor-grabbing"
                        title="Permuter cette position"
                        {...attributes}
                        {...listeners}
                    >
                        <GripVerticalIcon className="size-4" />
                    </button>
                </TableCell>
            )}

            <TableCell>
                <div className="flex size-8 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                    {entry.position}
                </div>
            </TableCell>

            <TableCell>
                {entry
                    .session_participant
                    ?.membership
                    ?.user
                    ?.name ?? '—'}
            </TableCell>

            <TableCell>
                {entry.entry_number}
            </TableCell>

            <TableCell>
                {entry.expected_meeting ? (
                    <div className="space-y-0.5">
                        <p className="font-medium">
                            {formatDate(
                                entry
                                    .expected_meeting
                                    .scheduled_at,
                            )}
                        </p>

                        <p className="text-xs text-muted-foreground">
                            Réunion #
                            {
                                entry
                                    .expected_meeting
                                    .number
                            }
                        </p>
                    </div>
                ) : (
                    <span className="text-muted-foreground">
                        Non planifiée
                    </span>
                )}
            </TableCell>
        </TableRow>
    );
}