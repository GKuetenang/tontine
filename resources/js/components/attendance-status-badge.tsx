import {
    CheckCircle2Icon,
    CircleDashedIcon,
    Clock3Icon,
    UserRoundCheckIcon,
    UserRoundXIcon,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';

import type { AttendanceStatus } from '@/types';

const attendanceStatusConfig = {
    pending: {
        label: 'En attente',
        icon: CircleDashedIcon,
        className:
            'rounded-full border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
    },

    present: {
        label: 'Présent',
        icon: UserRoundCheckIcon,
        className:
            'rounded-full border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
    },

    late: {
        label: 'En retard',
        icon: Clock3Icon,
        className:
            'rounded-full border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
    },

    absent: {
        label: 'Absent',
        icon: UserRoundXIcon,
        className:
            'rounded-full border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300',
    },

    excused: {
        label: 'Justifié',
        icon: CheckCircle2Icon,
        className:
            'rounded-full border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300',
    },
} satisfies Record<
    AttendanceStatus,
    {
        label: string;
        icon: typeof CircleDashedIcon;
        className: string;
    }
>;

export function AttendanceStatusBadge({
    status,
}: {
    status: AttendanceStatus;
}) {
    const config = attendanceStatusConfig[status];

    const Icon = config.icon;

    return (
        <Badge
            variant="outline"
            className={`gap-1.5 rounded-full px-2.5 py-1 font-medium ${config.className}`}
        >
            <Icon className="size-3.5" />

            {config.label}
        </Badge>
    );
}
