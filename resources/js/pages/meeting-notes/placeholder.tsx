import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { NotebookPenIcon } from 'lucide-react';

export function MeetingNotesPlaceholder() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    Notes
                </CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <NotebookPenIcon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            La réunion n’est pas encore ouverte
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            Les notes pourront être ajoutées
                            lorsque la réunion sera en cours.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}