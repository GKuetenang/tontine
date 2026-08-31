import {
    CheckCircle2Icon,
    CircleDashedIcon,
    CircleDollarSignIcon,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { ContributionStatus } from '@/types';

const config = {
    unpaid: {
        label: 'Non payée',
        icon: CircleDashedIcon,
        className:
            'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300',
    },

    partial: {
        label: 'Partielle',
        icon: CircleDollarSignIcon,
        className:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
    },

    paid: {
        label: 'Payée',
        icon: CheckCircle2Icon,
        className:
            'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
    },
} satisfies Record<
    ContributionStatus,
    {
        label: string;
        icon: typeof CheckCircle2Icon;
        className: string;
    }
>;

export function ContributionStatusBadge({
    status,
}: {
    status: ContributionStatus;
}) {
    const item = config[status];
    const Icon = item.icon;

    return (
        <Badge
            variant="outline"
            className={`gap-1.5 rounded-full px-2.5 py-1 ${item.className}`}
        >
            <Icon className="size-3.5" />

            {item.label}
        </Badge>
    );
}
