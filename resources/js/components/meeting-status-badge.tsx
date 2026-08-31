import { Badge } from '@/components/ui/badge';

import type { Meeting } from '@/types';

export function MeetingStatusBadge({ meeting }: { meeting: Meeting }) {
    switch (meeting.status) {
        case 'scheduled':
            return (
                <Badge
                    variant="outline"
                    className="rounded-full border-blue-200 bg-blue-50 text-blue-700"
                >
                    Prévue
                </Badge>
            );

        case 'in_progress':
            return (
                <Badge
                    variant="outline"
                    className="rounded-full border-green-200 bg-green-50 text-green-700"
                >
                    En cours
                </Badge>
            );

        case 'completed':
            return (
                <Badge
                    variant="outline"
                    className="rounded-full border-zinc-200 bg-zinc-50 text-zinc-700"
                >
                    Terminée
                </Badge>
            );

        case 'cancelled':
            return (
                <Badge
                    variant="outline"
                    className="rounded-full border-red-200 bg-red-50 text-red-700"
                >
                    Annulée
                </Badge>
            );
    }
}
