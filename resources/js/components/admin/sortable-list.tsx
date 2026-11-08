import {
    closestCenter,
    DndContext,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type { DragEndEvent } from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslations } from '@/lib/i18n';

type Props<T extends { id: number }> = {
    items: T[];
    onReorder: (ids: number[]) => void;
    renderItem: (item: T, handle: ReactNode) => ReactNode;
    disabled?: boolean;
};

/** R-ORD-01 : réordonner par glisser-déposer (souris, doigt ou clavier : Espace puis flèches). */
export function SortableList<T extends { id: number }>({
    items,
    onReorder,
    renderItem,
    disabled = false,
}: Props<T>) {
    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 4 } }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const onDragEnd = ({ active, over }: DragEndEvent) => {
        if (!over || active.id === over.id) {
            return;
        }

        const from = items.findIndex((i) => i.id === active.id);
        const to = items.findIndex((i) => i.id === over.id);
        onReorder(arrayMove(items, from, to).map((i) => i.id));
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={onDragEnd}
        >
            <SortableContext
                items={items.map((i) => i.id)}
                strategy={verticalListSortingStrategy}
                disabled={disabled}
            >
                {items.map((item) => (
                    <SortableRow key={item.id} id={item.id} disabled={disabled}>
                        {(handle) => renderItem(item, handle)}
                    </SortableRow>
                ))}
            </SortableContext>
        </DndContext>
    );
}

function SortableRow({
    id,
    disabled,
    children,
}: {
    id: number;
    disabled: boolean;
    children: (handle: ReactNode) => ReactNode;
}) {
    const t = useTranslations();
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id, disabled });

    const handle = disabled ? null : (
        <button
            type="button"
            {...attributes}
            {...listeners}
            className="cursor-grab touch-none rounded p-1 text-muted-foreground hover:bg-muted"
            aria-label={t('catalog.drag')}
        >
            <GripVertical className="size-4" />
        </button>
    );

    return (
        <div
            ref={setNodeRef}
            style={{ transform: CSS.Transform.toString(transform), transition }}
            className={isDragging ? 'relative z-10 opacity-80' : undefined}
        >
            {children(handle)}
        </div>
    );
}
